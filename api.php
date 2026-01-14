<?php
// api.php — small endpoint to call TelegramClient from the browser via AJAX
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/telegram.php';

// Load simple .env file if present (KEY=VALUE per line)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2) + [1 => '']);
        if ($k !== '') {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

$action = $_POST['action'] ?? $_REQUEST['action'] ?? null;
$token = $_POST['token'] ?? $_REQUEST['token'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: null;
$chat = $_POST['chat'] ?? $_REQUEST['chat'] ?? getenv('DEFAULT_CHAT_ID') ?: null;

if (!$action || !$token || !$chat) {
    echo json_encode(['ok' => false, 'error' => 'Missing required parameters (action, token, chat)']);
    exit;
}

$client = new TelegramClient($token);

try {
    if ($action === 'sendMessage') {
        $text = $_POST['text'] ?? '';
        $res = $client->sendMessage($chat, $text);
        echo json_encode($res);
        exit;
    }

    if ($action === 'sendFile') {
        if (!isset($_FILES['file'])) {
            echo json_encode(['ok' => false, 'error' => 'No file uploaded']);
            exit;
        }

        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $code = $file['error'];
            echo json_encode(['ok' => false, 'error' => 'File upload error', 'upload_error_code' => $code]);
            exit;
        }

        $tmp = $file['tmp_name'];
        if (!is_uploaded_file($tmp) && !file_exists($tmp)) {
            echo json_encode(['ok' => false, 'error' => 'Uploaded file not available on server']);
            exit;
        }
        $mime = $file['type'] ?? '';
        $method = $_POST['fileType'] ?? 'auto';

        if ($method === 'auto') {
            if (str_starts_with($mime, 'image/')) $method = 'photo';
            elseif (str_starts_with($mime, 'video/')) $method = 'video';
            elseif (str_starts_with($mime, 'audio/')) $method = 'audio';
            else $method = 'document';
        }

        $originalName = $file['name'] ?? basename($tmp);
        switch ($method) {
            case 'photo':
                $res = $client->sendPhoto($chat, $tmp, $_POST['caption'] ?? null, $originalName);
                break;
            case 'video':
                $res = $client->sendVideo($chat, $tmp, $_POST['caption'] ?? null, $originalName);
                break;
            case 'audio':
                $res = $client->sendAudio($chat, $tmp, $_POST['caption'] ?? null, $originalName);
                break;
            default:
                $res = $client->sendDocument($chat, $tmp, $_POST['caption'] ?? null, $originalName);
                break;
        }

        echo json_encode($res);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
