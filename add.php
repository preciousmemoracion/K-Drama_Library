<?php include "db.php";

// Add error reporting for debugging (optional, remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Function to check if drama title already exists and return existing drama info
function checkDuplicateDrama($conn, $title) {
    $check_stmt = $conn->prepare("SELECT id, title, image FROM dramas WHERE title = ?");
    $check_stmt->bind_param("s", $title);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    $check_stmt->close();
    return null;
}

$error_message = '';
$warning_message = '';
$existing_drama = null;
$old_input = []; // Store old input for repopulation

if(isset($_POST['submit'])){

    // FIXED
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $episodes = intval($_POST['episodes']);
    $year = intval($_POST['released_year']);
    $rating = floatval($_POST['rating']);
    
    // Store for repopulation in case of error
    $old_input = [
        'title' => $title,
        'genre' => $genre,
        'episodes' => $episodes,
        'year' => $year,
        'rating' => $rating
    ];

    // Check for duplicate title
    $existing_drama = checkDuplicateDrama($conn, $title);
    
    if ($existing_drama !== null) {
        // Set warning message instead of error - more friendly
        $warning_message = true; // Just flag to show modal
    } else {
        // No duplicate found - proceed with insert
        // IMAGE UPLOAD
        $image = basename($_FILES['image']['name']);
        $tmp = $_FILES['image']['tmp_name'];
        
        // Create uploads directory if it doesn't exist
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }
        
        // Generate unique filename to prevent overwriting (optional but good practice)
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $image_name = pathinfo($image, PATHINFO_FILENAME);
        $unique_image = $image_name . '_' . time() . '.' . $image_ext;
        $folder = "uploads/" . $unique_image;
        
        // Validate file upload
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Check file size (max 5MB)
            if ($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                // Allowed image types
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                $file_type = mime_content_type($tmp);
                
                if (in_array($file_type, $allowed_types)) {
                    if (move_uploaded_file($tmp, $folder)) {
                        // INSERT using unique image name
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
                            $unique_image  // Save unique filename
                        );

                        if ($stmt->execute()) {
                            header("Location: index.php?success=added");
                            exit;
                        } else {
                            $error_message = "❌ Database error: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error_message = "❌ Failed to upload image. Please check folder permissions.";
                    }
                } else {
                    $error_message = "❌ Invalid file type. Please upload JPG, PNG, or WEBP images only.";
                }
            } else {
                $error_message = "❌ File too large. Maximum size is 5MB.";
            }
        } else {
            $error_message = "❌ Please select an image file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add K-Drama | Watchlist Manager</title>
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
            --error-bg:  rgba(232,66,106,0.12);
            --error-border: rgba(232,66,106,0.3);
            --success-bg: rgba(68, 189, 108, 0.12);
            --success-border: rgba(68, 189, 108, 0.3);
            --warning-bg: rgba(244, 196, 106, 0.12);
            --warning-border: rgba(244, 196, 106, 0.3);
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

        /* ── ALERT MESSAGES ── */
        .alert {
            margin: 0 36px 20px 36px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.4s ease-out;
        }

        .alert-error {
            background: var(--error-bg);
            border-left: 3px solid var(--accent);
            color: var(--accent2);
        }

        .alert-success {
            background: var(--success-bg);
            border-left: 3px solid #44bd6c;
            color: #44bd6c;
        }

        .alert-warning {
            background: var(--warning-bg);
            border-left: 3px solid var(--gold);
            color: var(--gold);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-icon {
            font-size: 18px;
        }

        /* ── MODAL STYLES ── */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            max-width: 400px;
            width: 90%;
            overflow: hidden;
            animation: modalSlide 0.35s cubic-bezier(0.34, 1.2, 0.64, 1);
        }

        @keyframes modalSlide {
            from { transform: scale(0.95) translateY(-20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .modal-header {
            background: linear-gradient(135deg, #1e1828 0%, #16121f 100%);
            padding: 24px 28px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .modal-header h2 {
            font-family: 'Noto Serif KR', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--gold);
        }

        .modal-body {
            padding: 28px;
            text-align: center;
        }

        .existing-drama-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 16px;
            margin: 16px 0;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid var(--border);
        }

        .existing-drama-img {
            width: 60px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            background: var(--bg);
        }

        .existing-drama-info {
            flex: 1;
            text-align: left;
        }

        .existing-drama-info h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }

        .existing-drama-info p {
            font-size: 12px;
            color: var(--muted);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 40px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .modal-btn-cancel {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .modal-btn-cancel:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text);
        }

        .modal-btn-continue {
            background: linear-gradient(135deg, #e8426a 0%, #c42d52 100%);
            color: white;
        }

        .modal-btn-continue:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(232,66,106,0.4);
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

        /* error state for inputs */
        input.error {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(244,196,106,0.15);
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
            .alert { margin-left: 24px; margin-right: 24px; }
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

    <!-- ERROR / SUCCESS MESSAGES -->
    <?php if(isset($error_message) && !empty($error_message)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">⚠️</span>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <!-- BODY -->
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" id="dramaForm">
        <div class="form-grid">

          <!-- Title -->
          <div class="field full">
            <label>Title <span class="req">*</span></label>
            <input type="text" name="title" id="titleInput" placeholder="e.g. Crash Landing on You" 
                   value="<?php echo isset($old_input['title']) ? htmlspecialchars($old_input['title']) : ''; ?>"
                   required>
          </div>

          <!-- Genre -->
          <div class="field">
            <label>Genre</label>
            <input type="text" name="genre" placeholder="e.g. Romance, Thriller, Comedy"
                   value="<?php echo isset($old_input['genre']) ? htmlspecialchars($old_input['genre']) : ''; ?>">
          </div>

          <!-- Episodes -->
          <div class="field">
            <label>Episodes</label>
            <input type="number" name="episodes" placeholder="16" min="1"
                   value="<?php echo isset($old_input['episodes']) ? $old_input['episodes'] : ''; ?>">
          </div>

          <!-- Year -->
          <div class="field">
            <label>Release Year</label>
            <input type="number" name="released_year" placeholder="2024" min="1990" max="2099"
                   value="<?php echo isset($old_input['year']) ? $old_input['year'] : ''; ?>">
          </div>

          <!-- Rating -->
          <div class="field">
            <label>Rating</label>
            <div class="rating-wrap">
                <input type="number" name="rating" id="ratingInput"
                       placeholder="8.5" min="0" max="10" step="0.1"
                       value="<?php echo isset($old_input['rating']) ? $old_input['rating'] : ''; ?>">
            </div>
          </div>

          <div class="divider"></div>

          <!-- Image Upload -->
          <div class="field full">
            <label>Poster Image <span class="req">*</span></label>
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="image" id="fileInput" accept="image/jpeg,image/png,image/webp,image/jpg" required>
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
          <button class="btn-submit" name="submit" type="submit" id="submitBtn">
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

<!-- DUPLICATE WARNING MODAL -->
<div id="duplicateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon">⚠️</div>
            <h2>Already in Library</h2>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 8px;">This drama already exists in your collection:</p>
            
            <div class="existing-drama-card" id="existingDramaCard">
                <img id="existingDramaImg" class="existing-drama-img" src="" alt="Existing drama poster">
                <div class="existing-drama-info">
                    <h3 id="existingDramaTitle"></h3>
                    <p id="existingDramaDetails"></p>
                </div>
            </div>
            
            <p style="font-size: 13px; color: var(--muted);">Do you still want to add it as a new entry?</p>
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" id="cancelDuplicateBtn">Cancel</button>
                <button class="modal-btn modal-btn-continue" id="continueDuplicateBtn">Add Anyway</button>
            </div>
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
    const titleInput  = document.getElementById('titleInput');
    const submitBtn   = document.getElementById('submitBtn');
    const dramaForm   = document.getElementById('dramaForm');
    
    // Modal elements
    const modal = document.getElementById('duplicateModal');
    const cancelBtn = document.getElementById('cancelDuplicateBtn');
    const continueBtn = document.getElementById('continueDuplicateBtn');
    const existingDramaImg = document.getElementById('existingDramaImg');
    const existingDramaTitle = document.getElementById('existingDramaTitle');
    const existingDramaDetails = document.getElementById('existingDramaDetails');

    // Flag to track if we're overriding duplicate check
    let forceSubmit = false;
    let duplicateData = null;

    // File preview handler
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            uploadInner.style.opacity = '1';
            uploadPreview.style.display = 'none';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Invalid file type. Please upload JPG, PNG, or WEBP images only.');
            this.value = ''; // Clear the file input
            uploadInner.style.opacity = '1';
            uploadPreview.style.display = 'none';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('❌ File too large. Maximum size is 5MB.');
            this.value = '';
            uploadInner.style.opacity = '1';
            uploadPreview.style.display = 'none';
            return;
        }
        
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

    // Real-time duplicate title check with modal trigger
    let checkTimeout;
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            clearTimeout(checkTimeout);
            const title = this.value.trim();
            
            // Remove existing error styling
            this.classList.remove('error');
            const existingError = document.getElementById('live-title-error');
            if (existingError) existingError.remove();
            
            if (title.length < 2) return;
            
            // Debounced AJAX check
            checkTimeout = setTimeout(() => {
                fetch('check_duplicate.php?title=' + encodeURIComponent(title))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            titleInput.classList.add('error');
                            const errorSpan = document.createElement('small');
                            errorSpan.id = 'live-title-error';
                            errorSpan.style.color = '#f4c46a';
                            errorSpan.style.fontSize = '10px';
                            errorSpan.style.marginTop = '4px';
                            errorSpan.style.display = 'block';
                            errorSpan.textContent = '⚠️ This title already exists in your library';
                            if (!titleInput.parentNode.querySelector('#live-title-error')) {
                                titleInput.parentNode.appendChild(errorSpan);
                            }
                        } else {
                            titleInput.classList.remove('error');
                            const existing = document.getElementById('live-title-error');
                            if (existing) existing.remove();
                        }
                    })
                    .catch(err => console.log('Live check unavailable'));
            }, 500);
        });
    }

    // Form submission validation with duplicate check via AJAX
    dramaForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Always prevent default first
        
        const title = titleInput.value.trim();
        if (title === '') {
            alert('Please enter a drama title.');
            titleInput.focus();
            return;
        }
        
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a poster image.');
            return;
        }
        
        // Double-check file type before submit
        const file = fileInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Invalid file type. Please upload JPG, PNG, or WEBP images only.');
            return;
       