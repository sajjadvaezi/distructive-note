<?php
require_once 'Database.php';

class NoteService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function generateNoteId() {
        return bin2hex(random_bytes(ID_LENGTH / 2));
    }

    public function createNote($content, $password = null, $maxViews = DEFAULT_MAX_VIEWS) {
        // Validate inputs
        if (empty(trim($content))) {
            throw new Exception('Note content cannot be empty');
        }

        if ($maxViews < 1 || $maxViews > MAX_VIEWS_LIMIT) {
            throw new Exception('Invalid number of views');
        }

        $id = $this->generateNoteId();
        
        if ($this->db->createNote($id, $content, $password, $maxViews)) {
            return $id;
        }
        
        throw new Exception('Failed to create note');
    }

    public function getNote($id, $password = null) {
        $note = $this->db->getNote($id);
        
        if (!$note) {
            throw new Exception('Note not found or has expired');
        }

        // Check if password is required
        if ($note['password_hash'] && !$password) {
            throw new Exception('Password required');
        }

        // Verify password if provided
        if ($note['password_hash'] && !$this->db->verifyPassword($id, $password)) {
            throw new Exception('Invalid password');
        }

        // Check if note has reached max views
        if ($note['current_views'] >= $note['max_views']) {
            $this->db->destroyNote($id);
            throw new Exception('Note has been destroyed (max views reached)');
        }

        // Increment view count
        $this->db->incrementViews($id);

        // Destroy note if this was the last view
        if (($note['current_views'] + 1) >= $note['max_views']) {
            $this->db->destroyNote($id);
        }

        return $note;
    }

    public function cleanupExpiredNotes() {
        return $this->db->cleanupExpiredNotes();
    }

    public function checkNoteExists($id) {
        return $this->db->checkNoteExists($id);
    }

    public function noteRequiresPassword($id) {
        return $this->db->noteRequiresPassword($id);
    }
}
