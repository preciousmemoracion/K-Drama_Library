<?php include "db.php"; ?>

<?php
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if (!empty($search)) {
    $sql = "SELECT * FROM dramas 
            WHERE title LIKE '%$search%' 
            OR genre LIKE '%$search%' 
            OR episodes LIKE '%$search%'
            OR released_year LIKE '%$search%'
            OR rating LIKE '%$search%'
            LIMIT $start, $limit";
    $count_sql = "SELECT COUNT(*) as total FROM dramas 
                  WHERE title LIKE '%$search%' 
                  OR genre LIKE '%$search%' 
                  OR episodes LIKE '%$search%'
                  OR released_year LIKE '%$search%'
                  OR rating LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM dramas ORDER BY rating DESC LIMIT $start, $limit";
    $count_sql = "SELECT COUNT(*) as total FROM dramas";
}

$result = $conn->query($sql);

$featured_sql = "SELECT * FROM dramas ORDER BY rating DESC LIMIT 1";
$featured_result = $conn->query($featured_sql);
$featured = $featured_result ? $featured_result->fetch_assoc() : null;

$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RiCious K-Drama Verse</title>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">

<style>
:root {
    --red: #E50914;
    --red-dark: #b20710;
    --red-glow: rgba(229,9,20,0.35);
    --black: #0a0a0a;
    --dark: #141414;
    --dark2: #1c1c1c;
    --surface: #2a2a2a;
    --surface2: #333;
    --text: #ffffff;
    --text-muted: #a3a3a3;
    --text-dim: #555;
    --gold: #f5c518;
    --gold-dim: rgba(245,197,24,0.15);
    --radius: 6px;
    --radius-lg: 12px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    background: var(--black);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ─── NAVBAR ─────────────────────────────────────────────── */
.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    padding: 0 4%;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(to bottom, rgba(10,10,10,0.98) 0%, transparent 100%);
    transition: background 0.3s, box-shadow 0.3s;
}

.navbar.scrolled {
    background: rgba(10,10,10,0.98);
    box-shadow: 0 1px 0 rgba(255,255,255,0.06);
}

.nav-left { display: flex; align-items: center; gap: 32px; }

.logo {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--red);
    letter-spacing: 1.5px;
    text-decoration: none;
    text-transform: uppercase;
}

.nav-links { display: flex; gap: 20px; }

.nav-links a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: color 0.2s;
    letter-spacing: 0.2px;
}

.nav-links a:hover, .nav-links a.active { color: var(--text); }

.nav-right { display: flex; align-items: center; gap: 12px; }

/* ── ENHANCED NAV SEARCH ── */
.nav-search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-toggle-btn {
    background: none;
    border: 1px solid rgba(255,255,255,0.12);
    color: var(--text-muted);
    cursor: pointer;
    padding: 7px 12px;
    border-radius: var(--radius);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.search-toggle-btn:hover {
    border-color: rgba(255,255,255,0.3);
    color: var(--text);
    background: var(--dark2);
}

.search-toggle-btn .kbd {
    font-size: 10px;
    padding: 1px 5px;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 3px;
    color: var(--text-dim);
    font-family: monospace;
}

/* Search overlay */
.search-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    align-items: flex-start;
    justify-content: center;
    padding-top: 80px;
}

.search-overlay.active { display: flex; }

.search-overlay-inner {
    width: 100%;
    max-width: 640px;
    padding: 0 20px;
    animation: searchDrop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes searchDrop {
    from { opacity: 0; transform: translateY(-20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.search-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--dark2);
    border: 1.5px solid rgba(255,255,255,0.15);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input-wrap:focus-within {
    border-color: var(--red);
    box-shadow: 0 0 0 3px var(--red-glow), 0 8px 32px rgba(0,0,0,0.6);
}

.search-icon-left {
    padding: 0 16px;
    color: var(--text-dim);
    font-size: 18px;
    flex-shrink: 0;
    pointer-events: none;
}

.search-main-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: var(--text);
    font-size: 18px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 400;
    padding: 16px 0;
    letter-spacing: 0.2px;
}

.search-main-input::placeholder { color: var(--text-dim); }

.search-input-actions {
    display: flex;
    align-items: center;
    padding: 0 12px;
    gap: 8px;
}

.search-clear-btn {
    background: var(--surface);
    border: none;
    color: var(--text-muted);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.search-clear-btn.visible { display: flex; }
.search-clear-btn:hover { background: var(--surface2); color: white; }

.search-submit-btn {
    background: var(--red);
    border: none;
    color: white;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    white-space: nowrap;
}

.search-submit-btn:hover { background: var(--red-dark); }
.search-submit-btn:active { transform: scale(0.97); }

/* Search quick filters */
.search-quick-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    flex-wrap: wrap;
}

.search-filter-label {
    font-size: 11px;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}

.search-chip {
    background: var(--dark2);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--text-muted);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
}

.search-chip:hover {
    background: var(--surface);
    border-color: rgba(255,255,255,0.25);
    color: white;
}

.search-chip.active {
    background: var(--red);
    border-color: var(--red);
    color: white;
}

/* Search close hint */
.search-esc-hint {
    text-align: center;
    margin-top: 20px;
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.3px;
}

.search-esc-hint kbd {
    padding: 2px 8px;
    background: var(--surface);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
    color: var(--text-muted);
}

.add-drama-btn {
    background: var(--red);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    text-decoration: none;
    letter-spacing: 0.3px;
    transition: background 0.2s, transform 0.15s;
    white-space: nowrap;
}

.add-drama-btn:hover { background: var(--red-dark); transform: scale(0.98); }

/* ─── HERO ───────────────────────────────────────────────── */
.hero {
    position: relative;
    height: 85vh;
    min-height: 500px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to bottom, rgba(10,10,10,0.2) 0%, rgba(10,10,10,0.05) 30%, rgba(10,10,10,0.8) 70%, rgba(10,10,10,1) 100%),
        linear-gradient(to right, rgba(10,10,10,0.9) 0%, rgba(10,10,10,0.4) 55%, transparent 100%);
    z-index: 1;
}

.hero-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    filter: brightness(0.65);
}

.hero-placeholder {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #1a0a2e 0%, #0d1a2e 50%, #1a0d1a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 100px;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 0 4% 7%;
    max-width: 620px;
}

.hero-genre-tags { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }

.hero-tag {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.8);
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    backdrop-filter: blur(6px);
}

.hero-title {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(38px, 6vw, 68px);
    font-weight: 600;
    line-height: 1.0;
    letter-spacing: 1px;
    text-shadow: 2px 4px 24px rgba(0,0,0,0.9);
    margin-bottom: 14px;
}

.hero-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.hero-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    color: var(--gold);
    font-weight: 700;
    font-size: 16px;
}

.hero-meta-item {
    color: var(--text-muted);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.hero-meta-dot {
    width: 3px; height: 3px;
    background: var(--text-dim);
    border-radius: 50%;
}

.hero-desc {
    font-size: 14px;
    line-height: 1.65;
    color: rgba(255,255,255,0.7);
    margin-bottom: 26px;
    max-width: 480px;
}

.hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.btn-play {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    color: black;
    padding: 11px 26px;
    border-radius: var(--radius);
    font-size: 15px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: background 0.2s, transform 0.15s;
}

.btn-play:hover { background: rgba(255,255,255,0.85); transform: scale(0.98); }

.btn-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(109,109,110,0.65);
    color: white;
    padding: 11px 26px;
    border-radius: var(--radius);
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    backdrop-filter: blur(6px);
    transition: background 0.2s;
}

.btn-info:hover { background: rgba(109,109,110,0.45); }

/* ─── MAIN CONTENT ───────────────────────────────────────── */
.main-content {
    padding: 0 4% 60px;
    margin-top: -40px;
    position: relative;
    z-index: 10;
}

/* ─── SECTION HEADER ─────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-top: 40px;
}

.section-title {
    font-family: 'Oswald', sans-serif;
    font-size: 22px;
    font-weight: 500;
    letter-spacing: 0.5px;
    color: var(--text);
}

.section-title span { color: var(--red); }

.section-count {
    font-size: 12px;
    color: var(--text-dim);
    background: var(--dark2);
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.06);
}

/* ─── SEARCH RESULT BANNER ───────────────────────────────── */
.search-result-banner {
    margin: 88px 0 28px;
    padding: 20px 24px;
    background: var(--dark2);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.search-result-info h2 {
    font-family: 'Oswald', sans-serif;
    font-size: 20px;
    font-weight: 400;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.search-result-info h2 strong {
    color: var(--text);
    font-weight: 600;
}

.search-result-info p {
    font-size: 12px;
    color: var(--text-dim);
}

.search-result-actions { display: flex; gap: 8px; align-items: center; }

.search-again-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: var(--surface);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius);
    color: var(--text-muted);
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
}

.search-again-btn:hover { background: var(--surface2); color: white; }

.clear-search-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius);
    color: var(--text-dim);
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
    transition: all 0.2s;
}

.clear-search-btn:hover { border-color: rgba(255,255,255,0.2); color: var(--text-muted); }

/* ─── DRAMA GRID ─────────────────────────────────────────── */
.drama-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 10px;
}

/* ─── DRAMA CARD ─────────────────────────────────────────── */
.drama-card {
    position: relative;
    border-radius: 6px;
    overflow: visible;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), z-index 0s 0.3s;
}

.drama-card:hover {
    transform: scale(1.08);
    z-index: 100;
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94), z-index 0s;
}

.card-poster-wrap {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 6px;
    overflow: hidden;
    background: var(--dark2);
}

.card-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: filter 0.3s, transform 0.4s;
}

.drama-card:hover .card-poster { filter: brightness(0.4); transform: scale(1.04); }

.card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    background: linear-gradient(135deg, #1a1a2e, #16213e);
}

.card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.97) 0%, rgba(0,0,0,0.5) 55%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 12px;
    border-radius: 6px;
}

.drama-card:hover .card-overlay { opacity: 1; }

.card-title-hover {
    font-size: 12px;
    font-weight: 600;
    color: white;
    line-height: 1.3;
    margin-bottom: 5px;
}

.card-meta-hover {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}

.card-rating-hover {
    color: var(--gold);
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 2px;
}

.card-year-hover { color: var(--text-muted); font-size: 10px; }
.card-eps-hover { color: var(--text-muted); font-size: 10px; }

.card-actions-hover {
    display: flex;
    gap: 5px;
}

.card-btn {
    flex: 1;
    padding: 6px 6px;
    border: none;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: opacity 0.2s, transform 0.1s;
    white-space: nowrap;
}

.card-btn:hover { opacity: 0.8; transform: scale(0.97); }

.card-btn-edit {
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(4px);
}

.card-btn-delete {
    background: rgba(229,9,20,0.85);
    color: white;
}

.card-genre-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(229,9,20,0.92);
    color: white;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 3px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drama-card:hover .card-genre-badge { opacity: 1; }

.card-rating-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,0.8);
    color: var(--gold);
    font-size: 10px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 2px;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(245,197,24,0.15);
}

.card-title-below {
    padding: 8px 2px 4px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    line-height: 1.3;
    transition: color 0.2s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.drama-card:hover .card-title-below { color: var(--text); }

/* ─── EMPTY STATE ────────────────────────────────────────── */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
}

.empty-icon { font-size: 64px; margin-bottom: 16px; }

.empty-title {
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    color: var(--text-muted);
    margin-bottom: 8px;
}

.empty-sub { font-size: 14px; color: var(--text-dim); }

/* ─── PAGINATION ─────────────────────────────────────────── */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 48px;
    flex-wrap: wrap;
}

.pg-btn {
    padding: 8px 16px;
    border-radius: var(--radius);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.2s;
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    display: inline-block;
}

.pg-num { background: transparent; color: var(--text-muted); }
.pg-num:hover { background: var(--surface); color: white; }
.pg-active { background: var(--red); color: white; border-color: var(--red); }
.pg-nav { background: var(--dark2); color: var(--text-muted); }
.pg-nav:hover { background: var(--surface); color: white; }
.pg-disabled { opacity: 0.25; pointer-events: none; }

/* ─── ENHANCED MODAL ─────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(0,0,0,0.88);
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 20px;
}

.modal-overlay.active { display: flex; }

.modal-card {
    position: relative;
    background: var(--dark);
    border-radius: 14px;
    overflow: hidden;
    width: 100%;
    max-width: 780px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalIn 0.32s cubic-bezier(0.34, 1.45, 0.64, 1);
    box-shadow: 0 32px 80px rgba(0,0,0,0.9), 0 0 0 1px rgba(255,255,255,0.06);
    display: flex;
    flex-direction: column;
}

/* hide scrollbar inside modal */
.modal-card::-webkit-scrollbar { width: 0; }

@keyframes modalIn {
    from { transform: scale(0.88) translateY(24px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

/* Modal top section: landscape image + quick info side by side */
.modal-top {
    display: flex;
    min-height: 280px;
    flex-shrink: 0;
}

.modal-poster-col {
    position: relative;
    width: 200px;
    flex-shrink: 0;
    overflow: hidden;
}

.modal-poster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

.modal-poster-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a0a2e, #16213e);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}

.modal-poster-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, transparent 60%, var(--dark) 100%);
}

.modal-info-col {
    flex: 1;
    padding: 28px 28px 20px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
}

/* Rating ring */
.modal-rating-ring {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.rating-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--gold-dim);
    border: 2.5px solid var(--gold);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rating-circle .rating-num {
    font-size: 16px;
    font-weight: 700;
    color: var(--gold);
    line-height: 1;
}

.rating-circle .rating-label {
    font-size: 8px;
    color: var(--gold);
    opacity: 0.7;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.modal-title {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(20px, 3vw, 28px);
    font-weight: 600;
    line-height: 1.1;
    letter-spacing: 0.5px;
    margin-bottom: 14px;
}

.modal-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 18px;
}

.modal-stat {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 8px;
    padding: 10px 14px;
}

.modal-stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-dim);
    font-weight: 600;
    margin-bottom: 4px;
}

.modal-stat-value {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}

.modal-genres { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 20px; }

.modal-genre-tag {
    background: rgba(229,9,20,0.12);
    border: 1px solid rgba(229,9,20,0.3);
    color: #ff6b6b;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.4px;
}

/* Modal action buttons */
.modal-actions {
    display: flex;
    gap: 10px;
}

.modal-btn {
    flex: 1;
    padding: 11px 16px;
    border-radius: var(--radius);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: opacity 0.2s, transform 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    letter-spacing: 0.3px;
}

.modal-btn:hover { opacity: 0.85; transform: scale(0.98); }
.modal-btn-edit { background: #2563eb; color: white; }
.modal-btn-delete { background: var(--red); color: white; }

/* Drama description area */
.modal-bottom {
    padding: 0 28px 28px;
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 20px;
    flex-shrink: 0;
}

.modal-bottom-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-dim);
    font-weight: 600;
    margin-bottom: 10px;
}

.modal-about-text {
    font-size: 14px;
    line-height: 1.65;
    color: var(--text-muted);
}

/* Stars display */
.star-row {
    display: flex;
    gap: 2px;
    margin-bottom: 6px;
}

.star { font-size: 14px; }
.star.filled { color: var(--gold); }
.star.empty { color: var(--surface2); }

/* Modal close */
.modal-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(0,0,0,0.7);
    border: 1px solid rgba(255,255,255,0.15);
    color: white;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: background 0.2s;
    line-height: 1;
    backdrop-filter: blur(4px);
}

.modal-close:hover { background: var(--red); }

/* ─── SCROLLBAR ──────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: var(--black); }
::-webkit-scrollbar-thumb { background: var(--surface); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #555; }

/* ─── FOOTER ─────────────────────────────────────────────── */
footer {
    text-align: center;
    padding: 32px 4%;
    border-top: 1px solid rgba(255,255,255,0.05);
    color: var(--text-dim);
    font-size: 12px;
}

footer span { color: var(--red); }

/* ─── RESPONSIVE ─────────────────────────────────────────── */
@media (max-width: 600px) {
    .modal-top { flex-direction: column; min-height: unset; }
    .modal-poster-col { width: 100%; height: 200px; }
    .modal-poster-overlay { background: linear-gradient(to bottom, transparent 60%, var(--dark) 100%); }
    .modal-info-col { padding: 20px; }
    .modal-stats-grid { grid-template-columns: 1fr 1fr; }
    .nav-links { display: none; }
    .drama-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="nav-left">
        <a href="?" class="logo">🎬 KDramaVerse</a>
        <div class="nav-links">
            <a href="?" class="active">Home</a>
            <a href="?sort=rating">Top Rated</a>
            <a href="?search=romance">Romance</a>
            <a href="?search=thriller">Thriller</a>
        </div>
    </div>
    <div class="nav-right">
        <button class="search-toggle-btn" id="searchToggleBtn" onclick="openSearchOverlay()">
            <span>🔍</span>
            <span>Search</span>
            <span class="kbd">/</span>
        </button>
        <a href="add.php" class="add-drama-btn">+ Add Drama</a>
    </div>
</nav>

<!-- SEARCH OVERLAY -->
<div class="search-overlay" id="searchOverlay" onclick="handleOverlayClick(event)">
    <div class="search-overlay-inner">
        <form method="GET" id="searchForm">
            <div class="search-input-wrap">
                <span class="search-icon-left">🔍</span>
                <input
                    type="text"
                    name="search"
                    class="search-main-input"
                    id="searchMainInput"
                    placeholder="Search dramas, genres, year, rating…"
                    value="<?php echo htmlspecialchars($search); ?>"
                    autocomplete="off"
                    spellcheck="false">
                <div class="search-input-actions">
                    <button type="button" class="search-clear-btn" id="searchClearBtn" onclick="clearSearch()">✕</button>
                    <button type="submit" class="search-submit-btn">Search</button>
                </div>
            </div>
        </form>

        <div class="search-quick-filters">
            <span class="search-filter-label">Quick:</span>
            <?php
            $genres = ['Romance', 'Thriller', 'Fantasy', 'Comedy', 'Mystery', 'Historical'];
            foreach ($genres as $g):
                $isActive = strtolower($search) === strtolower($g);
            ?>
            <a href="?search=<?php echo urlencode($g); ?>" 
               class="search-chip <?php echo $isActive ? 'active' : ''; ?>"
               onclick="closeSearchOverlay()">
                <?php echo $g; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <p class="search-esc-hint">Press <kbd>Esc</kbd> to close</p>
    </div>
</div>

<?php if (empty($search)): ?>

<!-- HERO SECTION -->
<section class="hero">
    <?php if ($featured): ?>
        <?php
        $img = !empty($featured['image']) ? basename($featured['image']) : 'default.jpg';
        $imgPath = "img/" . $img;
        if (!file_exists(__DIR__ . "/img/" . $img)) $imgPath = "img/default.jpg";
        ?>
        <?php if (file_exists(__DIR__ . "/" . $imgPath)): ?>
            <img class="hero-img" src="<?php echo htmlspecialchars($imgPath); ?>"
                alt="<?php echo htmlspecialchars($featured['title']); ?>">
        <?php else: ?>
            <div class="hero-placeholder">🎭</div>
        <?php endif; ?>

        <div class="hero-bg"></div>

        <div class="hero-content">
            <div class="hero-genre-tags">
                <?php
                $genres = explode(',', $featured['genre']);
                foreach ($genres as $g): ?>
                    <span class="hero-tag"><?php echo trim(htmlspecialchars($g)); ?></span>
                <?php endforeach; ?>
            </div>

            <h1 class="hero-title"><?php echo htmlspecialchars($featured['title']); ?></h1>

            <div class="hero-meta">
                <div class="hero-rating">⭐ <?php echo $featured['rating']; ?></div>
                <div class="hero-meta-dot"></div>
                <div class="hero-meta-item"><?php echo $featured['released_year']; ?></div>
                <div class="hero-meta-dot"></div>
                <div class="hero-meta-item"><?php echo $featured['episodes']; ?> Episodes</div>
            </div>

            <p class="hero-desc">
                The highest-rated drama in your collection. A must-watch for any K-Drama enthusiast.
            </p>

            <div class="hero-actions">
                <button class="btn-play"
                    onclick="openModal(
                        '<?php echo addslashes(htmlspecialchars($imgPath)); ?>',
                        '<?php echo addslashes(htmlspecialchars($featured['title'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($featured['genre'])); ?>',
                        '<?php echo $featured['rating']; ?>',
                        '<?php echo $featured['episodes']; ?>',
                        '<?php echo $featured['released_year']; ?>',
                        '<?php echo $featured['id']; ?>')">
                    ▶ View Details
                </button>
                <a href="edit.php?id=<?php echo $featured['id']; ?>" class="btn-info">✏ Edit</a>
            </div>
        </div>
    <?php else: ?>
        <div class="hero-placeholder">🎭</div>
        <div class="hero-bg"></div>
        <div class="hero-content">
            <h1 class="hero-title">Welcome to<br>KDramaVerse</h1>
            <p class="hero-desc">Your personal K-Drama library. Start by adding your first drama!</p>
            <div class="hero-actions">
                <a href="add.php" class="btn-play">+ Add First Drama</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <?php if (!empty($search)): ?>
    <!-- Search result banner -->
    <div class="search-result-banner">
        <div class="search-result-info">
            <h2>Results for <strong>"<?php echo htmlspecialchars($search); ?>"</strong></h2>
            <p><?php echo $total_row['total']; ?> drama<?php echo $total_row['total'] != 1 ? 's' : ''; ?> found</p>
        </div>
        <div class="search-result-actions">
            <button class="search-again-btn" onclick="openSearchOverlay()">🔍 Search Again</button>
            <a href="?" class="clear-search-btn">✕ Clear</a>
        </div>
    </div>
    <?php else: ?>

    <div class="section-header">
        <h2 class="section-title">All <span>Dramas</span></h2>
        <span class="section-count"><?php echo $total_row['total']; ?> titles</span>
    </div>

    <?php endif; ?>

    <!-- DRAMA GRID -->
    <div class="drama-grid">

    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $img = !empty($row['image']) ? basename($row['image']) : 'default.jpg';
            $imgPath = "img/" . $img;
            if (!file_exists(__DIR__ . "/img/" . $img)) $imgPath = "img/default.jpg";
            $hasImg = file_exists(__DIR__ . "/" . $imgPath);

            $title_js   = addslashes(htmlspecialchars($row['title']));
            $genre_js   = addslashes(htmlspecialchars($row['genre']));
            $imgPath_js = addslashes(htmlspecialchars($imgPath));

            // First genre only for badge
            $firstGenre = trim(explode(',', $row['genre'])[0]);
    ?>

        <div class="drama-card"
            onclick="openModal('<?php echo $imgPath_js; ?>',
                '<?php echo $title_js; ?>',
                '<?php echo $genre_js; ?>',
                '<?php echo $row['rating']; ?>',
                '<?php echo $row['episodes']; ?>',
                '<?php echo $row['released_year']; ?>',
                '<?php echo $row['id']; ?>')">

            <div class="card-poster-wrap">
                <?php if ($hasImg): ?>
                    <img class="card-poster"
                        src="<?php echo htmlspecialchars($imgPath); ?>"
                        alt="<?php echo htmlspecialchars($row['title']); ?>"
                        loading="lazy">
                <?php else: ?>
                    <div class="card-placeholder">🎭</div>
                <?php endif; ?>

                <div class="card-rating-badge">⭐ <?php echo $row['rating']; ?></div>
                <div class="card-genre-badge"><?php echo htmlspecialchars($firstGenre); ?></div>

                <div class="card-overlay">
                    <div class="card-title-hover"><?php echo htmlspecialchars($row['title']); ?></div>
                    <div class="card-meta-hover">
                        <span class="card-rating-hover">⭐ <?php echo $row['rating']; ?></span>
                        <span class="card-year-hover"><?php echo $row['released_year']; ?></span>
                        <span class="card-eps-hover">· <?php echo $row['episodes']; ?> eps</span>
                    </div>
                    <div class="card-actions-hover" onclick="event.stopPropagation()">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="card-btn card-btn-edit">✏ Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                            class="card-btn card-btn-delete"
                            onclick="return confirmDelete()">🗑 Delete</a>
                    </div>
                </div>
            </div>

            <div class="card-title-below"><?php echo htmlspecialchars($row['title']); ?></div>
        </div>

    <?php
        }
    } else {
        echo '<div class="empty-state">
                <div class="empty-icon">🔍</div>
                <div class="empty-title">No dramas found</div>
                <p class="empty-sub">Try a different keyword or browse all dramas.</p>
              </div>';
    }
    ?>

    </div><!-- end drama-grid -->

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        $query = !empty($search) ? "&search=" . urlencode($search) : '';
        $isFirst = ($page == 1);
        $isLast  = ($page == $total_pages);

        echo "<a class='pg-btn pg-nav" . ($isFirst ? " pg-disabled" : "") . "' href='?page=1$query'>« First</a>";
        if ($page > 1) echo "<a class='pg-btn pg-nav' href='?page=".($page-1)."$query'>‹ Prev</a>";

        $start_pg = max(1, $page - 2);
        $end_pg   = min($total_pages, $page + 2);
        if ($start_pg > 1) echo "<span class='pg-btn pg-num' style='opacity:0.4'>…</span>";
        for ($i = $start_pg; $i <= $end_pg; $i++) {
            if ($i == $page)
                echo "<span class='pg-btn pg-active'>$i</span>";
            else
                echo "<a class='pg-btn pg-num' href='?page=$i$query'>$i</a>";
        }
        if ($end_pg < $total_pages) echo "<span class='pg-btn pg-num' style='opacity:0.4'>…</span>";

        if ($page < $total_pages) echo "<a class='pg-btn pg-nav' href='?page=".($page+1)."$query'>Next ›</a>";
        echo "<a class='pg-btn pg-nav" . ($isLast ? " pg-disabled" : "") . "' href='?page=$total_pages$query'>Last »</a>";
        ?>
    </div>
    <?php endif; ?>

</div><!-- end main-content -->

<footer>
    <p>© <?php echo date('Y'); ?> <span>RiCious KDramaVerse</span> — Your Personal Drama Collection</p>
</footer>

<!-- ─── ENHANCED MODAL ──────────────────────────────────── -->
<div id="imageModal" class="modal-overlay" onclick="closeModal()">
    <div class="modal-card" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="closeModal()">×</button>

        <!-- Top: poster + info side by side -->
        <div class="modal-top">
            <div class="modal-poster-col" id="modalPosterCol">
                <img id="modalImg" src="" alt="" class="modal-poster-img">
                <div class="modal-poster-overlay"></div>
            </div>
            <div class="modal-info-col">
                <!-- Rating -->
                <div>
                    <div class="modal-rating-ring">
                        <div class="rating-circle" id="modalRatingCircle">
                            <span class="rating-num" id="modalRatingNum"></span>
                            <span class="rating-label">Rating</span>
                        </div>
                        <div>
                            <div class="star-row" id="modalStars"></div>
                            <div style="font-size:12px; color:var(--text-dim);">IMDb-style score</div>
                        </div>
                    </div>

                    <h2 class="modal-title" id="modalTitle"></h2>

                    <div class="modal-genres" id="modalGenres"></div>

                    <div class="modal-stats-grid">
                        <div class="modal-stat">
                            <div class="modal-stat-label">📅 Year</div>
                            <div class="modal-stat-value" id="modalYear"></div>
                        </div>
                        <div class="modal-stat">
                            <div class="modal-stat-label">🎬 Episodes</div>
                            <div class="modal-stat-value" id="modalEps"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions" id="modalActions"></div>
            </div>
        </div>

        <!-- Bottom: about -->
        <div class="modal-bottom">
            <div class="modal-bottom-label">About this Drama</div>
            <p class="modal-about-text" id="modalAbout"></p>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm("Remove this drama from your library?");
}

function buildStars(rating) {
    const filled = Math.round(parseFloat(rating) / 2);
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<span class="star ${i <= filled ? 'filled' : 'empty'}">★</span>`;
    }
    return html;
}

function openModal(src, title, genre, rating, eps, year, id) {
    // Poster
    const img = document.getElementById("modalImg");
    img.src = src;
    img.alt = title;

    // Title
    document.getElementById("modalTitle").textContent = title;

    // Rating circle
    document.getElementById("modalRatingNum").textContent = rating;
    document.getElementById("modalStars").innerHTML = buildStars(rating);

    // Year / episodes
    document.getElementById("modalYear").textContent = year;
    document.getElementById("modalEps").textContent = eps;

    // Genres as chips
    const genreContainer = document.getElementById("modalGenres");
    genreContainer.innerHTML = '';
    genre.split(',').forEach(g => {
        const chip = document.createElement('span');
        chip.className = 'modal-genre-tag';
        chip.textContent = g.trim();
        genreContainer.appendChild(chip);
    });

    // About text (placeholder — replace with real synopsis field if available)
    document.getElementById("modalAbout").textContent =
        `"${title}" is a ${genre.split(',')[0].trim().toLowerCase()} K-Drama from ${year} with ${eps} episodes. Rated ${rating}/10 in your collection.`;

    // Action buttons
    document.getElementById("modalActions").innerHTML =
        `<a href="edit.php?id=${id}" class="modal-btn modal-btn-edit">✏ Edit Drama</a>
         <a href="delete.php?id=${id}" class="modal-btn modal-btn-delete" onclick="return confirmDelete()">🗑 Delete</a>`;

    document.getElementById("imageModal").classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    document.getElementById("imageModal").classList.remove("active");
    document.body.style.overflow = "";
}

// ── SEARCH OVERLAY ──
function openSearchOverlay() {
    const overlay = document.getElementById("searchOverlay");
    overlay.classList.add("active");
    document.body.style.overflow = "hidden";
    setTimeout(() => {
        const input = document.getElementById("searchMainInput");
        input.focus();
        input.select();
        updateClearBtn();
    }, 60);
}

function closeSearchOverlay() {
    document.getElementById("searchOverlay").classList.remove("active");
    document.body.style.overflow = "";
}

function handleOverlayClick(e) {
    if (e.target === document.getElementById("searchOverlay")) {
        closeSearchOverlay();
    }
}

function clearSearch() {
    const input = document.getElementById("searchMainInput");
    input.value = '';
    input.focus();
    document.getElementById("searchClearBtn").classList.remove("visible");
}

function updateClearBtn() {
    const input = document.getElementById("searchMainInput");
    const btn = document.getElementById("searchClearBtn");
    btn.classList.toggle("visible", input.value.length > 0);
}

document.getElementById("searchMainInput").addEventListener("input", updateClearBtn);

// Keyboard shortcuts
document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
        closeModal();
        closeSearchOverlay();
    }
    // Press "/" to open search (when not typing)sedftghjkm
    if (e.key === "/" && !document.getElementById("searchOverlay").classList.contains("active")) {
        const tag = document.activeElement.tagName.toLowerCase();
        if (tag !== "input" && tag !== "textarea") {
            e.preventDefault();
            openSearchOverlay();
        }
    }
});

// Navbar scroll
window.addEventListener("scroll", () => {
    document.getElementById("navbar").classList.toggle("scrolled", window.scrollY > 60);
});

// Auto-open search overlay if there's a search term on load
<?php if (!empty($search)): ?>
// Search was performed, keep overlay closed (results shown in page)
<?php endif; ?>
</script>

</body>
</html>