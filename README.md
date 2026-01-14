Telegram Channel Sender — Quick Start

What this app does
This small app lets you send messages and upload files (photos, PDFs, videos, audio, etc.) to a Telegram channel using a bot.

Prerequisites
- PHP 7.4 or newer with the `curl` extension enabled
- `file_uploads` enabled in `php.ini` for the web UI

Clone & quick start
1. Clone the repo and change directory:

```bash
git clone <REPO_URL> telegram-sender
cd telegram-sender
```

2. Copy the example env file and add your bot token (or set the environment variable directly):

```powershell
Copy-Item .env.example .env
# Edit .env and add your TELEGRAM_BOT_TOKEN and optionally DEFAULT_CHAT_ID
```

3. Start the built-in PHP server for local testing:

```powershell
php -S localhost:8000
```

4. Open http://localhost:8000 in your browser.

How to use
- If you set `TELEGRAM_BOT_TOKEN` in `.env`, you can leave the `Bot Token` field empty in the web UI; the server will use the server-side token.
- Enter the channel username (e.g. @yourchannel) or numeric id (e.g. -1001234567890). You may set `DEFAULT_CHAT_ID` in `.env` to skip this step.
- Type a message and click `Send Message`, or pick a file and click `Upload File`.

Notes about file uploads
- Uploaded files keep their original filename and extension (PDFs will remain .pdf).
- If an upload fails, the UI shows a JSON result with error details in the `Result` box — copy that when asking for help.

Security
- Do not commit your `.env` file (it's included in `.gitignore`).
- For production, keep the bot token server-side and secure access to the web UI.

Troubleshooting
- "Missing required parameters": ensure `chat` is provided or `DEFAULT_CHAT_ID` is set.
- "No file uploaded" or upload error: check PHP limits (`upload_max_filesize`, `post_max_size`) and the `$_FILES` error code.
- Bot cannot post: ensure the bot is an admin of the channel.

Support
If you need help, copy the JSON in the UI `Result` box and share it — it includes HTTP and cURL info useful for debugging.

Enjoy!

