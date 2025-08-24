<?php
require_once '../src/config.php';
require_once '../src/NoteService.php';

session_start();

$noteService = new NoteService();
$error = '';
$note = null;
$noteId = $_GET['id'] ?? '';

// If note is already in session (from form submission), use it
if (isset($_SESSION['viewed_note'])) {
    $note = $_SESSION['viewed_note'];
    unset($_SESSION['viewed_note']);
} elseif ($noteId) {
    // Handle password form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? null;
        try {
            $note = $noteService->getNote($noteId, $password);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        // Check if note exists and requires password before trying to access it
        $noteExists = $noteService->checkNoteExists($noteId);
        if (!$noteExists) {
            $error = 'Note not found or has expired';
        } else {
            $requiresPassword = $noteService->noteRequiresPassword($noteId);
            if ($requiresPassword) {
                // Note requires password, show password form immediately
                $error = 'This note is password protected';
            } else {
                // Note doesn't require password, try to access it
                try {
                    $note = $noteService->getNote($noteId);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    }
}

// If no note ID provided, redirect to home
if (!$noteId && !$note) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - View Note</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .note-content {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <a href="index.php" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Home
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">View Note</h1>
            </div>

            <?php if ($error): ?>
                <div class="card p-6 mb-6">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>

                    <?php if (strpos($error, 'Password required') !== false || strpos($error, 'Invalid password') !== false || strpos($error, 'password protected') !== false): ?>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    class="form-input"
                                    placeholder="Enter the note password"
                                    autofocus
                                >
                            </div>
                            <button
                                type="submit"
                                class="w-full btn-primary"
                            >
                                Unlock Note
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="index.php" class="inline-block btn-primary">
                            Go Home
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($note): ?>
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Note Content</h2>
                        <div class="text-gray-500 text-sm">
                            Views: <?= $note['current_views'] ?>/<?= $note['max_views'] ?>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="note-content text-gray-800"><?= htmlspecialchars($note['content']) ?></div>
                    </div>

                    <div class="text-center text-gray-600 text-sm mb-6">
                        <?php if ($note['current_views'] >= $note['max_views']): ?>
                            <i class="fas fa-fire text-red-500"></i> This note has been destroyed
                        <?php else: ?>
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <?php if ($note['max_views'] - $note['current_views'] == 1): ?>
                                This note will be destroyed after this view
                            <?php else: ?>
                                This note can be viewed <?= $note['max_views'] - $note['current_views'] ?> more time(s)
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="text-center">
                        <a href="index.php" class="inline-block btn-primary">
                            Create New Note
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
