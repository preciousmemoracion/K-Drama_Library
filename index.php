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
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Archivo:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">

<style>
/* ─── DESIGN TOKENS ────────────────────────────────────────────── */
:root {
    --crimson:      #e8001d;
    --crimson-deep: #a00015;
    --crimson-glow: rgba(232, 0, 29, 0.28);
    --crimson-soft: rgba(232, 0, 29, 0.10);

    --void:    #050508;
    --bg:      #0c0c10;
    --surface: #121217;
    --lift:    #1a1a22;
    --glass:   rgba(18, 18, 23, 0.82);

    --text:       #f0eff5;
    --text-muted: #8a8898;
    --text-dim:   #45444e;

    --gold:     #f0c040;
    --gold-glow:rgba(240, 192, 64, 0.18);

    --border:   rgba(255,255,255,0.06);
    --border-hi:rgba(255,255,255,0.12);

    --r-sm: 4px;
    --r-md: 8px;
    --r-lg: 14px;
    --r-xl: 20px;

    --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
    --ease-out:    cubic-bezier(0.16, 1, 0.3, 1);
}

/* ─── RESET & BASE ─────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
    background: var(--void);
    color: var(--text);
    font-family: 'Archivo', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

/* Grain overlay */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 9999;
    pointer-events: none;
    opacity: 0.025;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    background-size: 180px;
}

/* ─── NAVBAR ───────────────────────────────────────────────────── */
.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    height: 64px;
    padding: 0 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background 0.4s, backdrop-filter 0.4s;
}

.navbar::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(5,5,8,0.95) 0%, transparent 100%);
    z-index: -1;
    transition: opacity 0.4s;
}

.navbar.scrolled {
    background: var(--glass);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid var(--border);
}

.navbar.scrolled::after { opacity: 0; }

.nav-left { display: flex; align-items: center; gap: 36px; }

.logo {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 22px;
    letter-spacing: 3px;
    color: var(--text);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.logo-mark {
    width: 28px; height: 28px;
    background: var(--crimson);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.nav-links { display: flex; gap: 2px; }

.nav-links a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 500;
    letter-spacing: 0.5px;
    padding: 6px 14px;
    border-radius: var(--r-sm);
    transition: color 0.2s, background 0.2s;
}

.nav-links a:hover { color: var(--text); background: var(--lift); }
.nav-links a.active { color: var(--text); }

.nav-right { display: flex; align-items: center; gap: 10px; }

/* ── NAV SEARCH BUTTON ── */
.search-toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--surface);
    border: 1px solid var(--border-hi);
    color: var(--text-muted);
    padding: 7px 14px;
    border-radius: var(--r-md);
    font-size: 12.5px;
    font-family: 'Archivo', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.3px;
}

.search-toggle-btn:hover {
    background: var(--lift);
    border-color: rgba(255,255,255,0.18);
    color: var(--text);
}

.search-toggle-btn .slash-key {
    font-size: 10px;
    padding: 1px 6px;
    background: var(--lift);
    border: 1px solid var(--border-hi);
    border-radius: 3px;
    color: var(--text-dim);
    font-family: monospace;
    margin-left: 2px;
}

.add-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--crimson);
    color: white;
    padding: 8px 18px;
    border-radius: var(--r-md);
    font-size: 12.5px;
    font-weight: 600;
    font-family: 'Archivo', sans-serif;
    text-decoration: none;
    letter-spacing: 0.4px;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    white-space: nowrap;
    border: none;
    cursor: pointer;
}

.add-btn:hover {
    background: var(--crimson-deep);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px var(--crimson-glow);
}

/* ─── SEARCH OVERLAY ───────────────────────────────────────────── */
.search-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 5000;
    background: rgba(5, 5, 8, 0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    align-items: flex-start;
    justify-content: center;
    padding-top: 90px;
}

.search-overlay.active { display: flex; }

.search-overlay-inner {
    width: 100%;
    max-width: 600px;
    padding: 0 20px;
    animation: searchReveal 0.28s var(--ease-spring);
}

@keyframes searchReveal {
    from { opacity: 0; transform: translateY(-18px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.search-field-wrap {
    display: flex;
    align-items: center;
    background: var(--surface);
    border: 1.5px solid var(--border-hi);
    border-radius: var(--r-xl);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-field-wrap:focus-within {
    border-color: var(--crimson);
    box-shadow: 0 0 0 3px var(--crimson-glow), 0 16px 48px rgba(0,0,0,0.7);
}

.search-field-icon {
    padding: 0 18px;
    color: var(--text-dim);
    font-size: 17px;
    pointer-events: none;
    flex-shrink: 0;
}

.search-field-input {
    flex: 1;
    background: none;
    border: none;
    outline: none;
    color: var(--text);
    font-size: 17px;
    font-family: 'Archivo', sans-serif;
    font-weight: 400;
    padding: 17px 0;
}

.search-field-input::placeholder { color: var(--text-dim); }

.search-field-right {
    display: flex;
    align-items: center;
    padding: 0 14px;
    gap: 8px;
}

.search-clear {
    background: var(--lift);
    border: none;
    color: var(--text-muted);
    width: 22px; height: 22px;
    border-radius: 50%;
    font-size: 11px;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.search-clear.on { display: flex; }
.search-clear:hover { background: var(--border-hi); }

.search-go {
    background: var(--crimson);
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 700;
    font-family: 'Archivo', sans-serif;
    cursor: pointer;
    transition: background 0.2s;
    letter-spacing: 0.5px;
}

.search-go:hover { background: var(--crimson-deep); }

.search-chips-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.chips-label {
    font-size: 10px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--text-dim);
    font-weight: 700;
}

.genre-chip {
    padding: 5px 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    color: var(--text-muted);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Archivo', sans-serif;
    text-decoration: none;
}

.genre-chip:hover {
    background: var(--lift);
    border-color: var(--border-hi);
    color: var(--text);
}

.genre-chip.active {
    background: var(--crimson-soft);
    border-color: rgba(232,0,29,0.4);
    color: #ff5566;
}

.search-esc {
    text-align: center;
    margin-top: 22px;
    font-size: 11.5px;
    color: var(--text-dim);
    letter-spacing: 0.3px;
}

.search-esc kbd {
    padding: 2px 8px;
    background: var(--surface);
    border: 1px solid var(--border-hi);
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
}

/* ─── HERO ─────────────────────────────────────────────────────── */
.hero {
    position: relative;
    height: 92vh;
    min-height: 560px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
}

.hero-bg-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center 15%;
    filter: brightness(0.55) saturate(1.1);
    transform: scale(1.04);
    animation: heroZoom 12s ease-out forwards;
}

@keyframes heroZoom {
    from { transform: scale(1.08); }
    to   { transform: scale(1.0); }
}

.hero-placeholder {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 60% 30%, #1a0a2e 0%, #050508 60%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 120px;
}

/* Layered gradient vignette */
.hero-vignette {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to bottom,
            rgba(5,5,8,0.55) 0%,
            rgba(5,5,8,0.0) 25%,
            rgba(5,5,8,0.0) 45%,
            rgba(5,5,8,0.75) 72%,
            rgba(5,5,8,1)    100%),
        linear-gradient(to right,
            rgba(5,5,8,0.95) 0%,
            rgba(5,5,8,0.6)  40%,
            rgba(5,5,8,0.0)  70%);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 0 5% 8%;
    max-width: 580px;
    animation: heroContentIn 0.8s var(--ease-out) 0.2s both;
}

@keyframes heroContentIn {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

.hero-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.hero-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    background: var(--crimson);
    color: white;
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.hero-genre-pill {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    backdrop-filter: blur(6px);
}

.hero-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(52px, 8vw, 96px);
    line-height: 0.95;
    letter-spacing: 3px;
    text-shadow: 0 4px 40px rgba(0,0,0,0.8);
    margin-bottom: 16px;
    text-transform: uppercase;
}

.hero-meta-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.hero-rating {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--gold-glow);
    border: 1px solid rgba(240,192,64,0.2);
    color: var(--gold);
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
}

.hero-sep { color: var(--text-dim); font-size: 12px; }

.hero-info-item {
    font-size: 12.5px;
    color: var(--text-muted);
    font-weight: 400;
}

.hero-desc {
    font-size: 14px;
    line-height: 1.7;
    color: rgba(240,239,245,0.65);
    max-width: 440px;
    margin-bottom: 28px;
    font-weight: 300;
}

.hero-cta { display: flex; gap: 10px; flex-wrap: wrap; }

.cta-primary, .cta-secondary {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 13px 28px;
    border-radius: var(--r-md);
    font-size: 14px;
    font-weight: 700;
    font-family: 'Archivo', sans-serif;
    letter-spacing: 0.4px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.cta-primary {
    background: white;
    color: #0c0c10;
}

.cta-primary:hover {
    background: rgba(255,255,255,0.88);
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(255,255,255,0.12);
}

.cta-secondary {
    background: rgba(255,255,255,0.10);
    color: white;
    border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
}

.cta-secondary:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-1px);
}

/* Bottom scroll hint */
.hero-scroll-hint {
    position: absolute;
    bottom: 28px;
    right: 5%;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: var(--text-dim);
    letter-spacing: 1.5px;
    text-transform: uppercase;
    animation: floatY 2.5s ease-in-out infinite;
}

@keyframes floatY {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-5px); }
}

.scroll-line {
    width: 1px;
    height: 32px;
    background: linear-gradient(to bottom, transparent, var(--text-dim));
}

/* ─── STAT BAR (below hero) ─────────────────────────────────────── */
.stat-bar {
    display: flex;
    align-items: stretch;
    background: var(--surface);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    position: relative;
    z-index: 5;
}

.stat-item {
    flex: 1;
    padding: 18px 24px;
    text-align: center;
    border-right: 1px solid var(--border);
    transition: background 0.2s;
}

.stat-item:last-child { border-right: none; }
.stat-item:hover { background: var(--lift); }

.stat-num {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px;
    letter-spacing: 2px;
    color: var(--crimson);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: var(--text-dim);
    font-weight: 600;
}

/* ─── MAIN CONTENT ──────────────────────────────────────────────── */
.main-content {
    padding: 0 5% 80px;
    background: linear-gradient(to bottom, var(--void) 0%, var(--bg) 100%);
    position: relative;
}

/* ─── SECTION HEADER ────────────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 52px 0 22px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 28px;
}

.section-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 36px;
    letter-spacing: 3px;
    text-transform: uppercase;
    line-height: 1;
}

.section-title em {
    color: var(--crimson);
    font-style: normal;
}

.section-subtitle {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.3px;
    margin-top: 5px;
}

.section-count-pill {
    font-size: 11px;
    color: var(--text-muted);
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 5px 14px;
    border-radius: 20px;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

/* ─── SEARCH RESULT BANNER ──────────────────────────────────────── */
.search-result-banner {
    margin: 88px 0 32px;
    padding: 24px 28px;
    background: var(--surface);
    border: 1px solid var(--border-hi);
    border-radius: var(--r-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.search-result-banner::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--crimson);
    border-radius: 3px 0 0 3px;
}

.search-result-info h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 22px;
    letter-spacing: 1.5px;
    color: var(--text-muted);
    margin-bottom: 5px;
}

.search-result-info h2 strong {
    color: var(--text);
}

.search-result-info p {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.3px;
}

.search-result-actions { display: flex; gap: 8px; }

.srb-btn {
    padding: 8px 16px;
    border-radius: var(--r-md);
    font-size: 12.5px;
    font-family: 'Archivo', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.3px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.srb-btn-search {
    background: var(--lift);
    border: 1px solid var(--border-hi);
    color: var(--text-muted);
}

.srb-btn-search:hover { background: rgba(255,255,255,0.08); color: var(--text); }

.srb-btn-clear {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-dim);
}

.srb-btn-clear:hover { border-color: var(--border-hi); color: var(--text-muted); }

/* ─── DRAMA GRID ────────────────────────────────────────────────── */
.drama-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(158px, 1fr));
    gap: 14px;
}

/* ─── DRAMA CARD ────────────────────────────────────────────────── */
.drama-card {
    position: relative;
    cursor: pointer;
    border-radius: var(--r-md);
    overflow: visible;
    transition: transform 0.35s var(--ease-out), z-index 0s 0.35s;
    animation: cardFadeIn 0.4s var(--ease-out) both;
}

@keyframes cardFadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.drama-card:nth-child(1)  { animation-delay: 0.04s; }
.drama-card:nth-child(2)  { animation-delay: 0.07s; }
.drama-card:nth-child(3)  { animation-delay: 0.10s; }
.drama-card:nth-child(4)  { animation-delay: 0.13s; }
.drama-card:nth-child(5)  { animation-delay: 0.16s; }
.drama-card:nth-child(6)  { animation-delay: 0.19s; }
.drama-card:nth-child(7)  { animation-delay: 0.22s; }
.drama-card:nth-child(8)  { animation-delay: 0.25s; }
.drama-card:nth-child(9)  { animation-delay: 0.28s; }
.drama-card:nth-child(10) { animation-delay: 0.31s; }
.drama-card:nth-child(11) { animation-delay: 0.34s; }
.drama-card:nth-child(12) { animation-delay: 0.37s; }

.drama-card:hover {
    transform: scale(1.09) translateY(-4px);
    z-index: 100;
    transition: transform 0.35s var(--ease-out), z-index 0s;
}

/* Shadow on hover */
.drama-card:hover .card-poster-wrap {
    box-shadow: 0 18px 48px rgba(0,0,0,0.8), 0 0 0 1px var(--border-hi);
}

.card-poster-wrap {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: var(--r-md);
    overflow: hidden;
    background: var(--surface);
    transition: box-shadow 0.35s;
}

.card-poster {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: filter 0.35s, transform 0.45s var(--ease-out);
}

.drama-card:hover .card-poster {
    filter: brightness(0.35) saturate(0.8);
    transform: scale(1.05);
}

.card-placeholder {
    width: 100%; height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 52px;
    background: linear-gradient(145deg, var(--lift), var(--surface));
}

/* Rating badge – always visible */
.card-rating-badge {
    position: absolute;
    top: 8px; right: 8px;
    background: rgba(5,5,8,0.85);
    color: var(--gold);
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid rgba(240,192,64,0.12);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    gap: 3px;
    transition: opacity 0.3s;
}

.drama-card:hover .card-rating-badge { opacity: 0; }

/* Genre badge */
.card-genre-badge {
    position: absolute;
    top: 8px; left: 8px;
    background: var(--crimson);
    color: white;
    font-size: 8.5px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 3px;
    letter-spacing: 0.8px;
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

/* Card overlay */
.card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top,
        rgba(5,5,8,1)    0%,
        rgba(5,5,8,0.7)  50%,
        rgba(5,5,8,0.0) 100%);
    opacity: 0;
    transition: opacity 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 14px;
    border-radius: var(--r-md);
}

.drama-card:hover .card-overlay { opacity: 1; }

.card-ov-title {
    font-size: 11.5px;
    font-weight: 700;
    color: white;
    line-height: 1.3;
    margin-bottom: 5px;
}

.card-ov-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.card-ov-rating {
    color: var(--gold);
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 2px;
}

.card-ov-year,
.card-ov-eps {
    color: var(--text-muted);
    font-size: 9.5px;
}

.card-ov-actions {
    display: flex;
    gap: 6px;
}

.card-ov-btn {
    flex: 1;
    padding: 7px 6px;
    border-radius: var(--r-sm);
    font-size: 10px;
    font-weight: 700;
    font-family: 'Archivo', sans-serif;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    letter-spacing: 0.4px;
    transition: opacity 0.2s, transform 0.1s;
    border: none;
}

.card-ov-btn:hover { opacity: 0.82; transform: scale(0.97); }
.card-ov-btn-edit   { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.18); }
.card-ov-btn-delete { background: var(--crimson); color: white; }

/* Card title below */
.card-title-below {
    padding: 8px 2px 4px;
    font-size: 11.5px;
    font-weight: 500;
    color: var(--text-muted);
    line-height: 1.3;
    transition: color 0.2s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: 0.1px;
}

.drama-card:hover .card-title-below { color: var(--text); }

/* ─── EMPTY STATE ───────────────────────────────────────────────── */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 100px 20px;
}

.empty-icon { font-size: 72px; margin-bottom: 20px; opacity: 0.5; }

.empty-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 32px;
    letter-spacing: 2px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

.empty-sub { font-size: 13px; color: var(--text-dim); }

/* ─── PAGINATION ────────────────────────────────────────────────── */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    margin-top: 56px;
    flex-wrap: wrap;
}

.pg {
    padding: 8px 16px;
    border-radius: var(--r-md);
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 600;
    font-family: 'Archivo', sans-serif;
    transition: all 0.2s;
    border: 1px solid var(--border);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.3px;
}

.pg-num   { background: transparent; color: var(--text-muted); }
.pg-num:hover { background: var(--lift); color: var(--text); border-color: var(--border-hi); }
.pg-active { background: var(--crimson); color: white; border-color: var(--crimson); }
.pg-nav   { background: var(--surface); color: var(--text-muted); }
.pg-nav:hover { background: var(--lift); color: var(--text); }
.pg-disabled { opacity: 0.2; pointer-events: none; }

/* ─── MODAL ─────────────────────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 4000;
    background: rgba(5,5,8,0.90);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.modal-overlay.active { display: flex; }

.modal-card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border-hi);
    border-radius: var(--r-xl);
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalSpring 0.35s var(--ease-spring);
    box-shadow: 0 48px 100px rgba(0,0,0,0.9);
    scrollbar-width: none;
}

.modal-card::-webkit-scrollbar { display: none; }

@keyframes modalSpring {
    from { transform: scale(0.88) translateY(28px); opacity: 0; }
    to   { transform: scale(1) translateY(0); opacity: 1; }
}

/* Modal close */
.modal-x {
    position: absolute;
    top: 16px; right: 16px;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
    border: 1px solid var(--border-hi);
    color: var(--text-muted);
    font-size: 19px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: background 0.2s, color 0.2s;
    backdrop-filter: blur(6px);
    line-height: 1;
}

.modal-x:hover { background: var(--crimson); color: white; border-color: var(--crimson); }

/* Modal top: banner image */
.modal-banner {
    position: relative;
    height: 260px;
    overflow: hidden;
    border-radius: var(--r-xl) var(--r-xl) 0 0;
    flex-shrink: 0;
}

.modal-banner-img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center 20%;
    display: block;
    filter: brightness(0.55) saturate(1.1);
}

.modal-banner-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, #1a0a2e, #0d1a2e);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
}

.modal-banner-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
        transparent 30%,
        rgba(18,18,23,0.6) 70%,
        var(--surface) 100%);
}

/* Rating ring on banner */
.modal-rating-pill {
    position: absolute;
    bottom: 20px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(5,5,8,0.85);
    border: 1px solid rgba(240,192,64,0.2);
    padding: 8px 16px;
    border-radius: 30px;
    backdrop-filter: blur(8px);
}

.modal-rating-val {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px;
    letter-spacing: 1px;
    color: var(--gold);
    line-height: 1;
}

.modal-rating-sub {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-dim);
}

.modal-rating-stars { display: flex; gap: 2px; }
.star { font-size: 12px; }
.star.on  { color: var(--gold); }
.star.off { color: var(--text-dim); }

/* Modal body */
.modal-body {
    padding: 24px 28px 28px;
}

.modal-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(26px, 4vw, 38px);
    letter-spacing: 2px;
    line-height: 1;
    margin-bottom: 14px;
}

.modal-tags {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.modal-tag {
    background: var(--crimson-soft);
    border: 1px solid rgba(232,0,29,0.25);
    color: #ff6677;
    padding: 4px 13px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Stats row */
.modal-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 22px;
}

.modal-stat {
    background: var(--lift);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-stat-icon {
    font-size: 20px;
    flex-shrink: 0;
}

.modal-stat-text {}

.modal-stat-label {
    font-size: 9.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-dim);
    font-weight: 700;
    margin-bottom: 3px;
}

.modal-stat-val {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
}

/* About */
.modal-divider {
    height: 1px;
    background: var(--border);
    margin-bottom: 18px;
}

.modal-section-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--text-dim);
    font-weight: 700;
    margin-bottom: 10px;
}

.modal-about {
    font-size: 13.5px;
    line-height: 1.7;
    color: var(--text-muted);
    font-weight: 300;
}

/* Modal actions */
.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}

.modal-btn {
    flex: 1;
    padding: 13px 20px;
    border-radius: var(--r-md);
    font-family: 'Archivo', sans-serif;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    letter-spacing: 0.4px;
}

.modal-btn:hover { opacity: 0.85; transform: translateY(-1px); }

.modal-btn-edit   { background: #1d4ed8; color: white; }
.modal-btn-delete { background: var(--crimson); color: white; }

/* ─── SCROLLBAR ─────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: var(--void); }
::-webkit-scrollbar-thumb { background: var(--lift); border-radius: 2px; }
::-webkit-scrollbar-thumb:hover { background: var(--border-hi); }

/* ─── FOOTER ────────────────────────────────────────────────────── */
footer {
    text-align: center;
    padding: 36px 5%;
    border-top: 1px solid var(--border);
    color: var(--text-dim);
    font-size: 11.5px;
    letter-spacing: 0.3px;
    background: var(--surface);
}

footer em { color: var(--crimson); font-style: normal; }

/* ─── RESPONSIVE ────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .modal-stats       { grid-template-columns: 1fr 1fr; }
    .modal-body        { padding: 20px; }
    .modal-banner      { height: 200px; }
    .nav-links         { display: none; }
    .hero-title        { font-size: clamp(44px, 14vw, 72px); }
    .drama-grid        { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
    .stat-bar          { display: none; }
}
</style>
</head>
<body>

<!-- ─── NAVBAR ─────────────────────────────────────────────────── -->
<nav class="navbar" id="navbar">
    <div class="nav-left">
        <a href="?" class="logo">
            <div class="logo-mark">▶</div>
            KDRAMAVERSE
        </a>
        <div class="nav-links">
            <a href="?" class="active">Home</a>
            <a href="?sort=rating">Top Rated</a>
            <a href="?search=romance">Romance</a>
            <a href="?search=thriller">Thriller</a>
        </div>
    </div>
    <div class="nav-right">
        <button class="search-toggle-btn" onclick="openSearch()">
            🔍 Search <span class="slash-key">/</span>
        </button>
        <a href="add.php" class="add-btn">＋ Add Drama</a>
    </div>
</nav>

<!-- ─── SEARCH OVERLAY ─────────────────────────────────────────── -->
<div class="search-overlay" id="searchOverlay" onclick="handleOverlayBg(event)">
    <div class="search-overlay-inner">
        <form method="GET" id="searchForm">
            <div class="search-field-wrap">
                <span class="search-field-icon">🔍</span>
                <input
                    type="text"
                    name="search"
                    class="search-field-input"
                    id="searchInput"
                    placeholder="Search titles, genres, year, rating…"
                    value="<?php echo htmlspecialchars($search); ?>"
                    autocomplete="off"
                    spellcheck="false">
                <div class="search-field-right">
                    <button type="button" class="search-clear" id="clearBtn" onclick="clearSearchField()">✕</button>
                    <button type="submit" class="search-go">Search</button>
                </div>
            </div>
        </form>

        <div class="search-chips-row">
            <span class="chips-label">Quick:</span>
            <?php
            $genres = ['Romance', 'Thriller', 'Fantasy', 'Comedy', 'Mystery', 'Historical'];
            foreach ($genres as $g):
                $active = strtolower($search) === strtolower($g);
            ?>
            <a href="?search=<?php echo urlencode($g); ?>"
               class="genre-chip <?php echo $active ? 'active' : ''; ?>"
               onclick="closeSearch()">
                <?php echo $g; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <p class="search-esc">Press <kbd>Esc</kbd> to dismiss</p>
    </div>
</div>

<?php if (empty($search)): ?>
<!-- ─── HERO ───────────────────────────────────────────────────── -->
<section class="hero">
    <?php if ($featured):
        $img = !empty($featured['image']) ? basename($featured['image']) : 'default.jpg';
        $imgPath = "img/" . $img;
        if (!file_exists(__DIR__ . "/img/" . $img)) $imgPath = "img/default.jpg";
    ?>
        <?php if (file_exists(__DIR__ . "/" . $imgPath)): ?>
            <img class="hero-bg-img" src="<?php echo htmlspecialchars($imgPath); ?>"
                 alt="<?php echo htmlspecialchars($featured['title']); ?>">
        <?php else: ?>
            <div class="hero-placeholder">🎭</div>
        <?php endif; ?>

        <div class="hero-vignette"></div>

        <div class="hero-content">
            <div class="hero-eyebrow">
                <span class="hero-badge">★ Top Rated</span>
                <?php
                $genres = explode(',', $featured['genre']);
                foreach (array_slice($genres, 0, 2) as $g):
                ?>
                    <span class="hero-genre-pill"><?php echo trim(htmlspecialchars($g)); ?></span>
                <?php endforeach; ?>
            </div>

            <h1 class="hero-title"><?php echo htmlspecialchars($featured['title']); ?></h1>

            <div class="hero-meta-row">
                <span class="hero-rating">⭐ <?php echo $featured['rating']; ?>/10</span>
                <span class="hero-sep">·</span>
                <span class="hero-info-item"><?php echo $featured['released_year']; ?></span>
                <span class="hero-sep">·</span>
                <span class="hero-info-item"><?php echo $featured['episodes']; ?> Episodes</span>
            </div>

            <p class="hero-desc">
                The highest-rated drama in your collection — a must-watch masterpiece that defines the genre.
            </p>

            <div class="hero-cta">
                <button class="cta-primary"
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
                <a href="edit.php?id=<?php echo $featured['id']; ?>" class="cta-secondary">✏ Edit Entry</a>
            </div>
        </div>

        <div class="hero-scroll-hint">
            <span>Scroll</span>
            <div class="scroll-line"></div>
        </div>

    <?php else: ?>
        <div class="hero-placeholder">🎭</div>
        <div class="hero-vignette"></div>
        <div class="hero-content">
            <h1 class="hero-title">Your Drama<br>Library</h1>
            <p class="hero-desc">Start building your personal K-Drama collection.</p>
            <div class="hero-cta">
                <a href="add.php" class="cta-primary">＋ Add First Drama</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- ─── STAT BAR ───────────────────────────────────────────────── -->
<div class="stat-bar">
    <div class="stat-item">
        <div class="stat-num"><?php echo $total_row['total']; ?></div>
        <div class="stat-label">Titles</div>
    </div>
    <?php
    $genres_result = $conn->query("SELECT COUNT(DISTINCT genre) as c FROM dramas");
    $g_row = $genres_result ? $genres_result->fetch_assoc() : ['c' => 0];
    $avg_result = $conn->query("SELECT ROUND(AVG(rating),1) as avg_r FROM dramas");
    $avg_row = $avg_result ? $avg_result->fetch_assoc() : ['avg_r' => 0];
    $yr_result = $conn->query("SELECT MIN(released_year) as oldest FROM dramas");
    $yr_row = $yr_result ? $yr_result->fetch_assoc() : ['oldest' => '—'];
    ?>
    <div class="stat-item">
        <div class="stat-num"><?php echo $avg_row['avg_r'] ?: '—'; ?></div>
        <div class="stat-label">Avg Rating</div>
    </div>
    <div class="stat-item">
        <div class="stat-num"><?php echo $g_row['c'] ?: '—'; ?></div>
        <div class="stat-label">Genres</div>
    </div>
    <div class="stat-item">
        <div class="stat-num"><?php echo $yr_row['oldest'] ?: '—'; ?></div>
        <div class="stat-label">Oldest Year</div>
    </div>
</div>

<?php endif; ?>

<!-- ─── MAIN CONTENT ───────────────────────────────────────────── -->
<div class="main-content">

    <?php if (!empty($search)): ?>
    <div class="search-result-banner">
        <div class="search-result-info">
            <h2>Results for <strong>"<?php echo htmlspecialchars($search); ?>"</strong></h2>
            <p><?php echo $total_row['total']; ?> drama<?php echo $total_row['total'] != 1 ? 's' : ''; ?> found</p>
        </div>
        <div class="search-result-actions">
            <button class="srb-btn srb-btn-search" onclick="openSearch()">🔍 Search Again</button>
            <a href="?" class="srb-btn srb-btn-clear">✕ Clear</a>
        </div>
    </div>
    <?php else: ?>
    <div class="section-header">
        <div>
            <h2 class="section-title">All <em>Dramas</em></h2>
            <div class="section-subtitle">Sorted by rating · Your collection</div>
        </div>
        <span class="section-count-pill"><?php echo $total_row['total']; ?> titles</span>
    </div>
    <?php endif; ?>

    <!-- GRID -->
    <div class="drama-grid">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $img = !empty($row['image']) ? basename($row['image']) : 'default.jpg';
            $imgPath = "img/" . $img;
            if (!file_exists(__DIR__ . "/img/" . $img)) $imgPath = "img/default.jpg";
            $hasImg = file_exists(__DIR__ . "/" . $imgPath);

            $tj = addslashes(htmlspecialchars($row['title']));
            $gj = addslashes(htmlspecialchars($row['genre']));
            $ij = addslashes(htmlspecialchars($imgPath));
            $firstGenre = trim(explode(',', $row['genre'])[0]);
    ?>
        <div class="drama-card"
            onclick="openModal('<?php echo $ij; ?>','<?php echo $tj; ?>','<?php echo $gj; ?>','<?php echo $row['rating']; ?>','<?php echo $row['episodes']; ?>','<?php echo $row['released_year']; ?>','<?php echo $row['id']; ?>')">

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
                    <div class="card-ov-title"><?php echo htmlspecialchars($row['title']); ?></div>
                    <div class="card-ov-meta">
                        <span class="card-ov-rating">⭐ <?php echo $row['rating']; ?></span>
                        <span class="card-ov-year"><?php echo $row['released_year']; ?></span>
                        <span class="card-ov-eps">· <?php echo $row['episodes']; ?> eps</span>
                    </div>
                    <div class="card-ov-actions" onclick="event.stopPropagation()">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="card-ov-btn card-ov-btn-edit">✏ Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                           class="card-ov-btn card-ov-btn-delete"
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
                <div class="empty-title">No Dramas Found</div>
                <p class="empty-sub">Try a different keyword or browse all dramas.</p>
              </div>';
    }
    ?>
    </div><!-- /drama-grid -->

    <!-- PAGINATION -->
    <?php if ($total_pages > 1):
        $q = !empty($search) ? "&search=" . urlencode($search) : '';
    ?>
    <div class="pagination">
        <?php
        echo "<a class='pg pg-nav" . ($page==1?" pg-disabled":"") . "' href='?page=1$q'>«</a>";
        if ($page > 1) echo "<a class='pg pg-nav' href='?page=".($page-1)."$q'>‹</a>";

        $sp = max(1, $page-2);
        $ep = min($total_pages, $page+2);
        if ($sp > 1) echo "<span class='pg pg-num' style='opacity:.35'>…</span>";
        for ($i = $sp; $i <= $ep; $i++) {
            if ($i == $page) echo "<span class='pg pg-active'>$i</span>";
            else echo "<a class='pg pg-num' href='?page=$i$q'>$i</a>";
        }
        if ($ep < $total_pages) echo "<span class='pg pg-num' style='opacity:.35'>…</span>";

        if ($page < $total_pages) echo "<a class='pg pg-nav' href='?page=".($page+1)."$q'>›</a>";
        echo "<a class='pg pg-nav" . ($page==$total_pages?" pg-disabled":"") . "' href='?page=$total_pages$q'>»</a>";
        ?>
    </div>
    <?php endif; ?>

</div><!-- /main-content -->

<footer>
    © <?php echo date('Y'); ?> <em>RiCious KDramaVerse</em> — Your Personal Drama Collection
</footer>

<!-- ─── MODAL ──────────────────────────────────────────────────── -->
<div id="imageModal" class="modal-overlay" onclick="closeModal()">
    <div class="modal-card" onclick="event.stopPropagation()">
        <button class="modal-x" onclick="closeModal()">×</button>

        <div class="modal-banner">
            <img id="modalBannerImg" src="" alt="" class="modal-banner-img">
            <div id="modalBannerPlaceholder" class="modal-banner-placeholder" style="display:none">🎭</div>
            <div class="modal-banner-gradient"></div>
            <div class="modal-rating-pill">
                <div>
                    <div class="modal-rating-val" id="modalRatingVal"></div>
                    <div class="modal-rating-sub">Rating</div>
                </div>
                <div>
                    <div class="modal-rating-stars" id="modalStars"></div>
                    <div style="font-size:9px; color:var(--text-dim); margin-top:2px; letter-spacing:0.5px; text-transform:uppercase;">Out of 10</div>
                </div>
            </div>
        </div>

        <div class="modal-body">
            <h2 class="modal-title" id="modalTitle"></h2>
            <div class="modal-tags" id="modalTags"></div>

            <div class="modal-stats">
                <div class="modal-stat">
                    <div class="modal-stat-icon">📅</div>
                    <div class="modal-stat-text">
                        <div class="modal-stat-label">Released</div>
                        <div class="modal-stat-val" id="modalYear"></div>
                    </div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-icon">🎬</div>
                    <div class="modal-stat-text">
                        <div class="modal-stat-label">Episodes</div>
                        <div class="modal-stat-val" id="modalEps"></div>
                    </div>
                </div>
            </div>

            <div class="modal-divider"></div>
            <div class="modal-section-label">About this Drama</div>
            <p class="modal-about" id="modalAbout"></p>

            <div class="modal-actions" id="modalActions"></div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm("Remove this drama from your library?");
}

function buildStars(rating) {
    const filled = Math.round(parseFloat(rating) / 2);
    return Array.from({length: 5}, (_, i) =>
        `<span class="star ${i < filled ? 'on' : 'off'}">★</span>`
    ).join('');
}

function openModal(src, title, genre, rating, eps, year, id) {
    const bannerImg = document.getElementById("modalBannerImg");
    const bannerPh  = document.getElementById("modalBannerPlaceholder");

    if (src && src !== 'img/default.jpg') {
        bannerImg.src = src;
        bannerImg.style.display = 'block';
        bannerPh.style.display  = 'none';
    } else {
        bannerImg.style.display = 'none';
        bannerPh.style.display  = 'flex';
    }

    document.getElementById("modalTitle").textContent = title;
    document.getElementById("modalRatingVal").textContent = rating;
    document.getElementById("modalStars").innerHTML = buildStars(rating);
    document.getElementById("modalYear").textContent = year;
    document.getElementById("modalEps").textContent = eps;

    const tags = document.getElementById("modalTags");
    tags.innerHTML = '';
    genre.split(',').forEach(g => {
        const s = document.createElement('span');
        s.className = 'modal-tag';
        s.textContent = g.trim();
        tags.appendChild(s);
    });

    document.getElementById("modalAbout").textContent =
        `"${title}" is a ${genre.split(',')[0].trim().toLowerCase()} K-Drama released in ${year}, spanning ${eps} episode${eps != 1 ? 's' : ''}. It holds a ${rating}/10 rating in your personal collection.`;

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

/* Search overlay */
function openSearch() {
    document.getElementById("searchOverlay").classList.add("active");
    document.body.style.overflow = "hidden";
    setTimeout(() => {
        const inp = document.getElementById("searchInput");
        inp.focus(); inp.select();
        syncClearBtn();
    }, 60);
}

function closeSearch() {
    document.getElementById("searchOverlay").classList.remove("active");
    document.body.style.overflow = "";
}

function handleOverlayBg(e) {
    if (e.target === document.getElementById("searchOverlay")) closeSearch();
}

function clearSearchField() {
    const inp = document.getElementById("searchInput");
    inp.value = '';
    inp.focus();
    syncClearBtn();
}

function syncClearBtn() {
    const inp = document.getElementById("searchInput");
    document.getElementById("clearBtn").classList.toggle("on", inp.value.length > 0);
}

document.getElementById("searchInput").addEventListener("input", syncClearBtn);

/* Keyboard shortcuts */
document.addEventListener("keydown", e => {
    if (e.key === "Escape") { closeModal(); closeSearch(); }
    if (e.key === "/" && !document.getElementById("searchOverlay").classList.contains("active")) {
        const tag = document.activeElement.tagName.toLowerCase();
        if (tag !== "input" && tag !== "textarea") { e.preventDefault(); openSearch(); }
    }
});

/* Navbar scroll state */
window.addEventListener("scroll", () => {
    document.getElementById("navbar").classList.toggle("scrolled", window.scrollY > 50);
}, { passive: true });
</script>
</body>
</html>