<?php include "db.php";

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM dramas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: index.php");
    exit;
}

$success = false;

if (isset($_POST['update'])) {
    $title    = htmlspecialchars($_POST['title']);
    $genre    = htmlspecialchars($_POST['genre']);
    $episodes = intval($_POST['episodes']);
    $year     = intval($_POST['released_year']);
    $rating   = floatval($_POST['rating']);

    // Handle optional image update
    $image = $data['image']; // keep existing by default
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        $tmp   = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $image);
    }

    $upd = $conn->prepare("UPDATE dramas SET title=?, genre=?, episodes=?, released_year=?, rating=?, image=? WHERE id=?");
    $upd->bind_param("ssiidsi", $title, $genre, $episodes, $year, $rating, $image, $id);
    $upd->execute();

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update · <?= htmlspecialchars($data['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:      #0a0a0f;
            --surface: #12121a;
            --card:    #1a1a26;
            --border:  rgba(255,255,255,0.07);
            --accent:  #7c6aff;
            --accent2: #a99fff;
            --gold:    #f4c46a;
            --text:    #f0eee8;
            --muted:   #7a7890;
            --radius:  14px;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html, body { height: 100%; }

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

        /* ambient glow */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 15% 10%, rgba(124,106,255,0.13) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 85% 90%, rgba(80,60,200,0.10) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 60% 30%, rgba(244,196,106,0.04) 0%, transparent 50%);
            pointer-events: none; z-index: 0;
        }

        /* film grain */
        body::after {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            background-size: 180px;
            opacity: 0.4;
            pointer-events: none; z-index: 0;
        }

        .page {
            position: relative; z-index: 1;
            width: 100%; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 16px;
        }

        /* ── CARD ── */
        .card {
            width: 100%; max-width: 520px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.03),
                0 32px 80px rgba(0,0,0,0.6),
                0 0 80px rgba(124,106,255,0.07);
            animation: slideUp 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideUp {
            from { opacity:0; transform:translateY(28px) scale(0.98); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }

        /* ── HEADER ── */
        .card-header {
            position: relative;
            padding: 0;
            overflow: hidden;
        }

        /* Poster strip */
        .poster-strip {
            position: relative;
            height: 160px;
            background: var(--surface);
            overflow: hidden;
        }

        .poster-strip img {
            width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.55;
            filter: blur(2px) saturate(0.8);
            transform: scale(1.06);
            transition: opacity 0.4s;
        }

        .poster-strip .gradient {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom,
                rgba(10,10,15,0.2) 0%,
                rgba(26,26,38,0.95) 100%);
        }

        .poster-strip .no-image {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 48px; opacity: 0.15;
        }

        .header-content {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 18px 32px 22px;
        }

        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(124,106,255,0.14);
            border: 1px solid rgba(124,106,255,0.28);
            color: var(--accent2);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.08em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 100px;
            margin-bottom: 10px;
        }

        .badge::before {
            content: '';
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:0.5; transform:scale(0.8); }
        }

        .header-content h1 {
            font-family: 'Noto Serif KR', serif;
            font-size: 22px; font-weight: 700;
            letter-spacing: -0.3px; line-height: 1.25;
        }

        .header-content h1 .drama-title {
            color: var(--accent2);
        }

        /* ── FORM BODY ── */
        .card-body { padding: 28px 32px 32px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field { display: flex; flex-direction: column; gap: 7px; }
        .field.full { grid-column: 1 / -1; }

        label {
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.09em; text-transform: uppercase;
            color: var(--muted);
        }

        label span.req { color: var(--accent); margin-left: 2px; }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 12px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input[type="text"]::placeholder,
        input[type="number"]::placeholder { color: rgba(122,120,144,0.45); }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: rgba(124,106,255,0.55);
            box-shadow: 0 0 0 3px rgba(124,106,255,0.09);
            background: #14141e;
        }

        /* ── CHANGED INDICATOR ── */
        input.changed {
            border-color: rgba(244,196,106,0.45) !important;
            box-shadow: 0 0 0 3px rgba(244,196,106,0.07) !important;
        }

        /* ── DIVIDER ── */
        .divider {
            grid-column: 1 / -1;
            height: 1px; background: var(--border); margin: 2px 0;
        }

        /* ── IMAGE UPLOAD (optional update) ── */
        .upload-label-row {
            display: flex; align-items: center; justify-content: space-between;
        }

        .upload-label-row .opt-tag {
            font-size: 10px; background: rgba(122,120,144,0.15);
            border: 1px solid rgba(122,120,144,0.2);
            color: var(--muted); padding: 2px 8px;
            border-radius: 100px; letter-spacing: 0.05em;
        }

        .upload-zone {
            grid-column: 1 / -1;
            position: relative;
            border: 1.5px dashed rgba(255,255,255,0.09);
            border-radius: var(--radius);
            background: var(--surface);
            overflow: hidden; cursor: pointer;
            min-height: 110px;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.25s, background 0.25s;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: rgba(124,106,255,0.45);
            background: rgba(124,106,255,0.04);
        }

        .upload-zone input[type="file"] {
            position: absolute; inset: 0;
            opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }

        .upload-inner {
            text-align: center; padding: 22px;
            pointer-events: none; transition: opacity 0.2s;
        }

        .upload-icon {
            width: 40px; height: 40px;
            margin: 0 auto 10px;
            background: rgba(124,106,255,0.1);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        .upload-icon svg { width: 18px; height: 18px; stroke: var(--accent2); }

        .upload-inner p { font-size: 13px; color: var(--muted); }
        .upload-inner p strong { color: var(--accent2); font-weight: 500; }
        .upload-inner small {
            font-size: 11px; color: rgba(122,120,144,0.45);
            margin-top: 3px; display: block;
        }

        .upload-preview {
            display: none; position: absolute; inset: 0; pointer-events: none;
        }
        .upload-preview img { width:100%; height:100%; object-fit:cover; }
        .upload-preview .overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(10,10,15,0.75) 0%, transparent 60%);
        }
        .upload-preview .filename {
            position: absolute; bottom: 10px; left: 12px; right: 12px;
            font-size: 12px; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* ── ACTIONS ── */
        .actions {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-top: 8px;
        }

        .btn-update {
            position: relative;
            padding: 14px 24px;
            background: linear-gradient(135deg, #7c6aff 0%, #5b46e0 100%);
            border: none; border-radius: var(--radius);
            color: #fff; font-family: 'DM Sans', sans-serif;
            font-size: 15px; font-weight: 500;
            cursor: pointer; overflow: hidden;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 24px rgba(124,106,255,0.35);
        }

        .btn-update::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 60%);
        }

        .btn-update:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(124,106,255,0.45);
        }

        .btn-update:active { transform: translateY(0); }

        .btn-cancel {
            padding: 14px 18px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--muted);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; cursor: pointer;
            text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            transition: border-color 0.2s, color 0.2s;
            white-space: nowrap;
        }

        .btn-cancel:hover { border-color: rgba(255,255,255,0.15); color: var(--text); }

        /* ── CHANGED BADGE ── */
        .changes-pill {
            display: none;
            position: fixed;
            bottom: 24px; left: 50%; transform: translateX(-50%);
            background: rgba(244,196,106,0.15);
            border: 1px solid rgba(244,196,106,0.3);
            color: var(--gold);
            font-size: 12px; font-weight: 500;
            padding: 8px 18px; border-radius: 100px;
            backdrop-filter: blur(8px);
            animation: floatIn 0.3s ease both;
            z-index: 999;
            pointer-events: none;
        }

        @keyframes floatIn {
            from { opacity:0; transform:translateX(-50%) translateY(8px); }
            to   { opacity:1; transform:translateX(-50%) translateY(0); }
        }

        /* responsive */
        @media (max-width: 520px) {
            .card-body { padding: 24px 20px 28px; }
            .header-content { padding: 16px 20px 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .field.full, .upload-zone, .divider, .actions { grid-column: auto; }
            .actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="page">
  <div class="card">

    <!-- HEADER with blurred poster -->
    <div class="card-header">
        <div class="poster-strip">
            <?php if (!empty($data['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($data['image']) ?>" alt="">
            <?php else: ?>
                <div class="no-image">🎬</div>
            <?php endif; ?>
            <div class="gradient"></div>
        </div>
        <div class="header-content">
            <div class="badge">Editing</div>
            <h1>Update <span class="drama-title"><?= htmlspecialchars($data['title']) ?></span></h1>
        </div>
    </div>

    <!-- FORM -->
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">

          <!-- Title -->
          <div class="field full">
            <label>Title <span class="req">*</span></label>
            <input type="text" name="title"
                   value="<?= htmlspecialchars($data['title']) ?>"
                   data-original="<?= htmlspecialchars($data['title']) ?>" required>
          </div>

          <!-- Genre -->
          <div class="field full">
            <label>Genre</label>
            <input type="text" name="genre"
                   value="<?= htmlspecialchars($data['genre']) ?>"
                   data-original="<?= htmlspecialchars($data['genre']) ?>">
          </div>

          <!-- Episodes + Year -->
          <div class="field">
            <label>Episodes</label>
            <input type="number" name="episodes"
                   value="<?= intval($data['episodes']) ?>"
                   data-original="<?= intval($data['episodes']) ?>" min="1">
          </div>

          <div class="field">
            <label>Release Year</label>
            <input type="number" name="released_year"
                   value="<?= intval($data['released_year']) ?>"
                   data-original="<?= intval($data['released_year']) ?>"
                   min="1990" max="2099">
          </div>

          <!-- Rating -->
          <div class="field">
            <label>Rating <span style="color:var(--gold)">★</span></label>
            <input type="number" name="rating" step="0.1" min="0" max="10"
                   value="<?= number_format($data['rating'], 1) ?>"
                   data-original="<?= number_format($data['rating'], 1) ?>">
          </div>

          <div class="divider"></div>

          <!-- Image (optional) -->
          <div class="field full">
            <div class="upload-label-row">
                <label>Poster Image</label>
                <span class="opt-tag">optional · replaces current</span>
            </div>
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="image" id="fileInput" accept="image/*">
                <div class="upload-inner" id="uploadInner">
                    <div class="upload-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <p><strong>Upload new poster</strong> or drag & drop</p>
                    <small>Leave empty to keep the current image</small>
                </div>
                <div class="upload-preview" id="uploadPreview">
                    <img id="previewImg" src="" alt="Preview">
                    <div class="overlay"></div>
                    <div class="filename" id="previewName"></div>
                </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="actions">
            <button class="btn-update" name="update" type="submit">Save Changes</button>
            <a href="index.php" class="btn-cancel">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
            </a>
          </div>

        </div>
      </form>
    </div>

  </div>
</div>

<!-- floating "unsaved changes" pill -->
<div class="changes-pill" id="changesPill">● Unsaved changes</div>

<script>
    // ── Track changed fields
    const trackedInputs = document.querySelectorAll('input[data-original]');
    const changesPill   = document.getElementById('changesPill');
    let changedCount = 0;

    trackedInputs.forEach(input => {
        input.addEventListener('input', () => {
            const changed = input.value !== input.dataset.original;
            const wasChanged = input.classList.contains('changed');

            if (changed && !wasChanged) {
                input.classList.add('changed');
                changedCount++;
            } else if (!changed && wasChanged) {
                input.classList.remove('changed');
                changedCount--;
            }

            changesPill.style.display = changedCount > 0 ? 'block' : 'none';
        });
    });

    // ── Image preview
    const fileInput     = document.getElementById('fileInput');
    const uploadZone    = document.getElementById('uploadZone');
    const uploadInner   = document.getElementById('uploadInner');
    const uploadPreview = document.getElementById('uploadPreview');
    const previewImg    = document.getElementById('previewImg');
    const previewName   = document.getElementById('previewName');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewName.textContent = file.name;
            uploadInner.style.opacity = '0';
            uploadPreview.style.display = 'block';
            // count image as a change too
            if (!fileInput.classList.contains('counted')) {
                fileInput.classList.add('counted');
                changedCount++;
                changesPill.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    });

    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
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