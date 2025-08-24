<?php
require_once '../src/config.php';
require_once '../src/NoteService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$noteService = new NoteService();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }
            
            $content = $input['content'] ?? '';
            $password = $input['password'] ?? null;
            $maxViews = (int)($input['max_views'] ?? DEFAULT_MAX_VIEWS);
            
            $noteId = $noteService->createNote($content, $password, $maxViews);
            
            echo json_encode([
                'success' => true,
                'note_id' => $noteId,
                'url' => SITE_URL . '/view.php?id=' . $noteId
            ]);
            break;
            
        case 'GET':
            $noteId = $_GET['id'] ?? '';
            $password = $_GET['password'] ?? null;
            
            if (!$noteId) {
                throw new Exception('Note ID is required');
            }
            
            $note = $noteService->getNote($noteId, $password);
            
            echo json_encode([
                'success' => true,
                'note' => [
                    'content' => $note['content'],
                    'current_views' => $note['current_views'],
                    'max_views' => $note['max_views'],
                    'created_at' => $note['created_at'],
                    'expires_at' => $note['expires_at']
                ]
            ]);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
