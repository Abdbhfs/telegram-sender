<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Telegram Channel Sender</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width:900px;">
      <div class="card-body p-4">
        <h3 class="card-title mb-3">Telegram Channel Sender</h3>
        <p class="text-muted small">Provide your bot token and target channel; the bot must be admin in the channel.</p>

        <form id="sendForm" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Bot Token</label>
            <input id="token" name="token" class="form-control" placeholder="123456:ABC-DEF" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Channel (chat id or @username)</label>
            <input id="chat" name="chat" class="form-control" placeholder="@yourchannel or -1001234567890" required>
          </div>

          <div class="col-12">
            <label class="form-label">Message</label>
            <textarea id="text" name="text" class="form-control" rows="4" placeholder="Type your message..."></textarea>
          </div>

          <div class="col-md-8">
            <label class="form-label">File (optional)</label>
            <input id="file" type="file" name="file" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">File Type (optional)</label>
            <select id="fileType" class="form-select">
              <option value="auto" selected>Auto-detect</option>
              <option value="photo">Photo</option>
              <option value="document">Document</option>
              <option value="video">Video</option>
              <option value="audio">Audio</option>
            </select>
          </div>

          <div class="col-12 d-flex gap-2">
            <button id="sendMessageBtn" type="button" class="btn btn-primary">Send Message</button>
            <button id="uploadFileBtn" type="button" class="btn btn-outline-primary">Upload File</button>
            <button id="clearBtn" type="button" class="btn btn-light">Clear</button>
            <div id="spinner" class="spinner-border text-primary ms-auto d-none" role="status"><span class="visually-hidden">Loading...</span></div>
          </div>
        </form>

        <hr>
        <div>
          <h6>Result</h6>
          <pre id="result" class="p-3 bg-light rounded small">No actions yet.</pre>
          <div class="progress mt-2 d-none" id="uploadProgress"><div class="progress-bar" role="progressbar" style="width:0%"></div></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/app.js"></script>
</body>
</html>