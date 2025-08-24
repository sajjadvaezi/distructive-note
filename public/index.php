<?php
require_once '../src/config.php';
require_once '../src/NoteService.php';

session_start();

$noteService = new NoteService();
$error = '';
$success = '';
$noteId = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $content = $_POST['content'] ?? '';
                    $password = !empty($_POST['password']) ? $_POST['password'] : null;
                    $maxViews = (int)($_POST['max_views'] ?? DEFAULT_MAX_VIEWS);
                    
                    $noteId = $noteService->createNote($content, $password, $maxViews);
                    $success = 'Note created successfully!';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;
                
            case 'view':
                try {
                    $id = $_POST['note_id'] ?? '';
                    $password = $_POST['view_password'] ?? null;
                    
                    $note = $noteService->getNote($id, $password);
                    $_SESSION['viewed_note'] = $note;
                    header('Location: view.php?id=' . $id);
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;
        }
    }
}

// Cleanup expired notes periodically
if (rand(1, 100) <= 10) { // 10% chance to run cleanup
    $noteService->cleanupExpiredNotes();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
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
        .btn-secondary {
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-secondary:hover {
            background-color: #059669;
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
        .copy-code {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            color: #475569;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .copy-code:hover {
            background-color: #e2e8f0;
        }
    </style>
    <script>
        function copyToClipboard(elementId, label) {
            const element = document.getElementById(elementId);
            const text = element.querySelector('span') ? element.querySelector('span').textContent : element.textContent;
            
            // Try to use the modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopySuccess(label);
                }).catch(() => {
                    fallbackCopyTextToClipboard(text, label);
                });
            } else {
                fallbackCopyTextToClipboard(text, label);
            }
        }
        
        function fallbackCopyTextToClipboard(text, label) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess(label);
                } else {
                    showCopyError(label);
                }
            } catch (err) {
                showCopyError(label);
            }
            
            document.body.removeChild(textArea);
        }
        
        function showCopySuccess(label) {
            // Create a temporary success message
            const message = document.createElement('div');
            message.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
            message.innerHTML = `<i class="fas fa-check"></i> ${label} copied to clipboard!`;
            document.body.appendChild(message);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 3000);
        }
        
        function showCopyError(label) {
            // Create a temporary error message
            const message = document.createElement('div');
            message.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
            message.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Failed to copy ${label}`;
            document.body.appendChild(message);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 3000);
        }
    </script>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">
                    <?= SITE_NAME ?>
                </h1>
                <p class="text-gray-600 text-lg">Create self-destructing notes with optional password protection</p>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    <?php if ($noteId): ?>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Note ID</label>
                                <div class="flex items-center gap-2">
                                    <code id="noteId" class="copy-code flex-1 flex items-center justify-between group" onclick="copyToClipboard('noteId', 'Note ID')" title="Click to copy">
                                        <span><?= $noteId ?></span>
                                        <i class="fas fa-copy text-gray-400 group-hover:text-blue-600"></i>
                                    </code>
                                    <button type="button" onclick="copyToClipboard('noteId', 'Note ID')" class="btn-primary text-sm px-3 py-2">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Note URL</label>
                                <div class="flex items-center gap-2">
                                    <code id="noteUrl" class="copy-code flex-1 flex items-center justify-between group" onclick="copyToClipboard('noteUrl', 'Note URL')" title="Click to copy">
                                        <span class="break-all"><?= SITE_URL ?>/view.php?id=<?= $noteId ?></span>
                                        <i class="fas fa-copy text-gray-400 group-hover:text-green-600 flex-shrink-0"></i>
                                    </code>
                                    <button type="button" onclick="copyToClipboard('noteUrl', 'Note URL')" class="btn-secondary text-sm px-3 py-2">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Create Note Form -->
                <div class="card p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Create New Note</h2>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="create">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Note Content</label>
                            <textarea 
                                name="content" 
                                required 
                                rows="6"
                                class="form-input"
                                placeholder="Enter your secret message here..."
                            ></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password (Optional)</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-input"
                                placeholder="Leave empty for no password protection"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Views</label>
                            <input 
                                type="number" 
                                name="max_views" 
                                min="1" 
                                max="<?= MAX_VIEWS_LIMIT ?>" 
                                value="<?= DEFAULT_MAX_VIEWS ?>"
                                class="form-input"
                            >
                            <p class="text-gray-500 text-sm mt-1">Note will self-destruct after this many views</p>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="w-full btn-primary"
                        >
                            Create Note
                        </button>
                    </form>
                </div>

                <!-- View Note Form -->
                <div class="card p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">View Note</h2>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="view">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Note ID</label>
                            <input 
                                type="text" 
                                name="note_id" 
                                required 
                                class="form-input"
                                placeholder="Enter the note ID"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password (if required)</label>
                            <input 
                                type="password" 
                                name="view_password" 
                                class="form-input"
                                placeholder="Enter password if note is protected"
                            >
                        </div>
                        
                        <button 
                            type="submit" 
                            class="w-full btn-secondary"
                        >
                            View Note
                        </button>
                    </form>
                </div>
            </div>

            <!-- Features -->
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-semibold text-gray-900 mb-8">Features</h3>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="card p-6 text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="text-gray-900 font-semibold mb-2">Password Protection</h4>
                        <p class="text-gray-600">Secure your notes with optional passwords</p>
                    </div>
                    <div class="card p-6 text-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-fire text-red-600 text-xl"></i>
                        </div>
                        <h4 class="text-gray-900 font-semibold mb-2">Self-Destruct</h4>
                        <p class="text-gray-600">Notes automatically destroy after viewing</p>
                    </div>
                    <div class="card p-6 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <h4 class="text-gray-900 font-semibold mb-2">Configurable Views</h4>
                        <p class="text-gray-600">Set how many times a note can be viewed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
