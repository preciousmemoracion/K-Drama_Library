<?php include "db.php";

if(isset($_POST['submit'])){

    // FIXED
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);

    $episodes = intval($_POST['episodes']);
    $year = intval($_POST['released_year']);
    $rating = floatval($_POST['rating']);

    // IMAGE UPLOAD
    $image = basename($_FILES['image']['name']);
    $tmp = $_FILES['image']['tmp_name'];

    $folder = "uploads/" . $image;

    move_uploaded_file($tmp, $folder);

    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO dramas
        (title, genre, episodes, released_year, rating, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssiids",
        $title,
        $genre,
        $episodes,
        $year,
        $rating,
        $image
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add K-Drama</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:        #0a0a0f;
            --surface:   #12121a;
            --card:      #1a1a26;
            --border:    rgba(255,255,255,0.07);
            --accent:    #e8426a;
            --accent2:   #ff8fab;
            --gold:      #f4c46a;
            --text:      #f0eee8;
            --muted:     #7a7890;
            --radius:    14px;
        }

        *, *::before, *::after {
            margin: 0; padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* ── CINEMATIC BACKGROUND ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(232,66,106,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(100,80,200,0.10) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 60% 30%, rgba(244,196,106,0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* film-grain overlay */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            background-size: 180px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        /* ── PAGE WRAPPER ── */
        .page {
            position: relative;
            z-index: 1;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        /* ── CARD ── */
        .card {
            width: 100%;
            max-width: 480px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.03),
                0 32px 80px rgba(0,0,0,0.6),
                0 0 80px rgba(232,66,106,0.06);
            animation: slideUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── HEADER BAND ── */
        .card-header {
            position: relative;
            padding: 32px 36px 28px;
            background: linear-gradient(135deg, #1e1828 0%, #16121f 100%);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header::before {
            content: '드라마';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            font-family: 'Noto Serif KR', serif;
            font-size: 72px;
            font-weight: 700;
            color: rgba(232,66,106,0.06);
            letter-spacing: -2px;
            pointer-events: none;
            white-space: nowrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(232,66,106,0.12);
            border: 1px solid rgba(232,66,106,0.25);
            color: var(--accent2);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 14px;
        }

        .badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.8); }
        }

        .card-header h1 {
            font-family: 'Noto Serif KR', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .card-header p {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
            font-weight: 300;
        }

        /* ── FORM BODY ── */
        .card-body {
            padding: 32px 36px 36px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--muted);
        }

        label span.req {
            color: var(--accent);
            margin-left: 2px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input[type="text"]::placeholder,
        input[type="number"]::placeholder {
            color: rgba(122,120,144,0.5);
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: rgba(232,66,106,0.5);
            box-shadow: 0 0 0 3px rgba(232,66,106,0.08);
            background: #14141e;
        }

        /* ── IMAGE UPLOAD ── */
        .upload-zone {
            grid-column: 1 / -1;
            position: relative;
            border: 1.5px dashed rgba(255,255,255,0.1);
            border-radius: var(--radius);
            background: var(--surface);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.25s, background 0.25s;
            min-height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: rgba(232,66,106,0.45);
            background: rgba(232,66,106,0.04);
        }

        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-inner {
            text-align: center;
            padding: 28px;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .upload-icon {
            width: 44px; height: 44px;
            margin: 0 auto 12px;
            background: rgba(232,66,106,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-icon svg {
            width: 20px; height: 20px;
            stroke: var(--accent2);
        }

        .upload-inner p {
            font-size: 13px;
            color: var(--muted);
        }

        .upload-inner p strong {
            color: var(--accent2);
            font-weight: 500;
        }

        .upload-inner small {
            font-size: 11px;
            color: rgba(122,120,144,0.5);
            margin-top: 4px;
            display: block;
        }

        /* preview */
        .upload-preview {
            display: none;
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-preview .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(10,10,15,0.8) 0%, transparent 60%);
        }

        .upload-preview .filename {
            position: absolute;
            bottom: 12px;
            left: 14px;
            right: 14px;
            font-size: 12px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── RATING STARS ── */
        .rating-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rating-wrap input {
            flex: 1;
        }

        .rating-display {
            font-size: 13px;
            color: var(--gold);
            font-weight: 500;
            min-width: 32px;
            text-align: right;
        }

        /* ── DIVIDER ── */
        .divider {
            grid-column: 1 / -1;
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        /* ── SUBMIT BTN ── */
        .btn-submit {
            grid-column: 1 / -1;
            position: relative;
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #e8426a 0%, #c42d52 100%);
            border: none;
            border-radius: var(--radius);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.02em;
            cursor: pointer;
            overflow: hidden;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 24px rgba(232,66,106,0.3);
            margin-top: 8px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 60%);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(232,66,106,0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── FOOTER LINK ── */
        .card-footer {
            padding: 0 36px 28px;
            display: flex;
            justify-content: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            color: var(--muted);
            font-size: 13px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--text);
        }

        .back-link svg {
            width: 14px; height: 14px;
            stroke: currentColor;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 520px) {
            .card-header, .card-body { padding-left: 24px; padding-right: 24px; }
            .card-footer { padding-left: 24px; padding-right: 24px; }
            .form-grid { grid-template-columns: 1fr; }
            .field.full, .upload-zone, .divider, .btn-submit { grid-column: auto; }
        }
    </style>
</head>
<body>

<div class="page">
  <div class="card">

    <!-- HEADER -->
    <div class="card-header">
        <div class="badge">New Entry</div>
        <h1>Add K-Drama</h1>
        <p>Expand your watchlist with a new series</p>
    </div>

    <!-- BODY -->
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">

          <!-- Title -->
          <div class="field full">
            <label>Title <span class="req">*</span></label>
            <input type="text" name="title" placeholder="e.g. Crash Landing on You" required>
          </div>

          <!-- Genre -->
          <div class="field">
            <label>Genre</label>
            <input type="text" name="genre" placeholder="e.g. Romance">
          </div>

          <!-- Episodes -->
          <div class="field">
            <label>Episodes</label>
            <input type="number" name="episodes" placeholder="16" min="1">
          </div>

          <!-- Year -->
          <div class="field">
            <label>Release Year</label>
            <input type="number" name="released_year" placeholder="2024" min="1990" max="2099">
          </div>

          <!-- Rating -->
          <div class="field">
            <label>Rating</label>
            <div class="rating-wrap">
                <input type="number" name="rating" id="ratingInput"
                       placeholder="8.5" min="0" max="10" step="0.1">
            </div>
          </div>

          <div class="divider"></div>

          <!-- Image Upload -->
          <div class="field full">
            <label>Poster Image <span class="req">*</span></label>
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="image" id="fileInput" accept="image/*" required>
                <div class="upload-inner" id="uploadInner">
                    <div class="upload-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <p><strong>Click to upload</strong> or drag & drop</p>
                    <small>JPG, PNG, WEBP · Max 5MB</small>
                </div>
                <div class="upload-preview" id="uploadPreview">
                    <img id="previewImg" src="" alt="Preview">
                    <div class="overlay"></div>
                    <div class="filename" id="previewName"></div>
                </div>
            </div>
          </div>

          <!-- Submit -->
          <button class="btn-submit" name="submit" type="submit">
              Add to Library
          </button>

        </div>
      </form>
    </div>

    <!-- FOOTER -->
    <div class="card-footer">
        <a href="index.php" class="back-link">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Library
        </a>
    </div>

  </div>
</div>

<script>
    // ── Image preview
    const fileInput   = document.getElementById('fileInput');
    const uploadZone  = document.getElementById('uploadZone');
    const uploadInner = document.getElementById('uploadInner');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewImg  = document.getElementById('previewImg');
    const previewName = document.getElementById('previewName');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewName.textContent = file.name;
            uploadInner.style.opacity = '0';
            uploadPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Drag-and-drop highlight
    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone.addEventListener('dragleave', ()  => uploadZone.classList.remove('drag-over'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
</script>

</body>
</html>