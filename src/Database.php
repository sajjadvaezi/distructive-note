<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function createNote($id, $content, $password = null, $maxViews = 1) {
        $passwordHash = $password ? password_hash($password, PASSWORD_BCRYPT, ['cost' => SALT_ROUNDS]) : null;
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . NOTE_EXPIRY_DAYS . ' days'));

        $stmt = $this->pdo->prepare("
            INSERT INTO notes (id, content, password_hash, max_views, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$id, $content, $passwordHash, $maxViews, $expiresAt]);
    }

    public function getNote($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notes
            WHERE id = ? AND is_destroyed = FALSE AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("
            UPDATE notes
            SET current_views = current_views + 1
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function destroyNote($id) {
        $stmt = $this->pdo->prepare("
            UPDATE notes
            SET is_destroyed = TRUE
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function verifyPassword($id, $password) {
        $note = $this->getNote($id);
        if (!$note || !$note['password_hash']) {
            return false;
        }
        return password_verify($password, $note['password_hash']);
    }

    public function cleanupExpiredNotes() {
        $stmt = $this->pdo->prepare("
            UPDATE notes
            SET is_destroyed = TRUE
            WHERE expires_at < NOW() AND is_destroyed = FALSE
        ");
        return $stmt->execute();
    }

    public function checkNoteExists($id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM notes
            WHERE id = ? AND is_destroyed = FALSE AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function noteRequiresPassword($id) {
        $stmt = $this->pdo->prepare("
            SELECT password_hash FROM notes
            WHERE id = ? AND is_destroyed = FALSE AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result && $result['password_hash'] !== null;
    }
}
