<?php
// telegram.php — simple Telegram Bot API client for sending messages and uploading media to a channel

class TelegramClient {
    private $token;
    private $apiUrl;

    public function __construct(string $token) {
        if (empty($token)) {
            throw new InvalidArgumentException('Telegram bot token is required');
        }
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    private function request(string $method, array $params = [], bool $isFile = false): array {
        $url = $this->apiUrl . $method;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // ensure we don't accidentally send JSON header for file uploads
        if ($isFile) {
            // let cURL set the Content-Type with the multipart boundary
            curl_setopt($ch, CURLOPT_HTTPHEADER, []);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $resp = curl_exec($ch);
        $info = curl_getinfo($ch) ?: [];
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $err, 'curl_info' => $info];
        }
        curl_close($ch);

        $decoded = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['ok' => false, 'error' => 'Invalid JSON response', 'http_code' => $info['http_code'] ?? null, 'response' => $resp, 'curl_info' => $info];
        }

        // include HTTP status for visibility
        if (is_array($decoded)) {
            $decoded['_http_code'] = $info['http_code'] ?? null;
        }

        return $decoded;
    }

    public function sendMessage(string|int $chatId, string $text, array $options = []): array {
        $params = array_merge(['chat_id' => $chatId, 'text' => $text], $options);
        return $this->request('sendMessage', $params, false);
    }

    private function fileParam(string $path, ?string $postName = null) {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File not found: {$path}");
        }
        $real = realpath($path);
        $filename = $postName ?? basename($real);
        if (function_exists('curl_file_create')) {
            return curl_file_create($real, mime_content_type($real) ?: null, $filename);
        }
        // Legacy @ syntax: append filename param so server sees original name
        return sprintf("@%s;filename=%s", $real, $filename);
    }

    public function sendPhoto(string|int $chatId, string $filePath, ?string $caption = null, ?string $postName = null): array {
        $params = ['chat_id' => $chatId, 'photo' => $this->fileParam($filePath, $postName)];
        if ($caption) $params['caption'] = $caption;
        return $this->request('sendPhoto', $params, true);
    }

    public function sendDocument(string|int $chatId, string $filePath, ?string $caption = null, ?string $postName = null): array {
        $params = ['chat_id' => $chatId, 'document' => $this->fileParam($filePath, $postName)];
        if ($caption) $params['caption'] = $caption;
        return $this->request('sendDocument', $params, true);
    }

    public function sendVideo(string|int $chatId, string $filePath, ?string $caption = null, ?string $postName = null): array {
        $params = ['chat_id' => $chatId, 'video' => $this->fileParam($filePath, $postName)];
        if ($caption) $params['caption'] = $caption;
        return $this->request('sendVideo', $params, true);
    }

    public function sendAudio(string|int $chatId, string $filePath, ?string $caption = null, ?string $postName = null): array {
        $params = ['chat_id' => $chatId, 'audio' => $this->fileParam($filePath, $postName)];
        if ($caption) $params['caption'] = $caption;
        return $this->request('sendAudio', $params, true);
    }
}

// CLI helper: minimal usage when run from command line
if (php_sapi_name() === 'cli' && isset($argv) && basename(__FILE__) === basename($argv[0])) {
    $usage = "Usage:\n  php telegram.php <bot_token> <chat_id_or_channel_username> <command> [args]\n\nCommands:\n  sendMessage <text>\n  sendPhoto <file_path> [caption]\n  sendDocument <file_path> [caption]\n  sendVideo <file_path> [caption]\n  sendAudio <file_path> [caption]\n\nExamples:\n  php telegram.php 123456:ABCdef @yourchannel sendMessage \"Hello channel\"\n  php telegram.php 123456:ABCdef -1001234567890 sendPhoto ./photo.jpg \"Nice shot\"\n";

    if ($argc < 4) {
        echo $usage;
        exit(1);
    }

    $token = $argv[1];
    $chat = $argv[2];
    $cmd = $argv[3];
    $client = new TelegramClient($token);

    try {
        switch ($cmd) {
            case 'sendMessage':
                $text = $argv[4] ?? '';
                $res = $client->sendMessage($chat, $text);
                break;
            case 'sendPhoto':
                $file = $argv[4] ?? null;
                $caption = $argv[5] ?? null;
                $res = $client->sendPhoto($chat, $file, $caption);
                break;
            case 'sendDocument':
                $file = $argv[4] ?? null;
                $caption = $argv[5] ?? null;
                $res = $client->sendDocument($chat, $file, $caption);
                break;
            case 'sendVideo':
                $file = $argv[4] ?? null;
                $caption = $argv[5] ?? null;
                $res = $client->sendVideo($chat, $file, $caption);
                break;
            case 'sendAudio':
                $file = $argv[4] ?? null;
                $caption = $argv[5] ?? null;
                $res = $client->sendAudio($chat, $file, $caption);
                break;
            default:
                echo "Unknown command: {$cmd}\n\n";
                echo $usage;
                exit(1);
        }

        echo json_encode($res, JSON_PRETTY_PRINT) . PHP_EOL;
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT) . PHP_EOL;
        exit(1);
    }
}
