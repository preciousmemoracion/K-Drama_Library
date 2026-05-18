<?php include "db.php"; ?>
<?php
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'rating';

$sort_col = in_array($sort, ['rating','released_year','episodes','title']) ? $sort : 'rating';
$sort_dir = $sort_col === 'title' ? 'ASC' : 'DESC';

if (!empty($search)) {
    $sql = "SELECT * FROM dramas WHERE title LIKE '%$search%' OR genre LIKE '%$search%' OR episodes LIKE '%$search%' OR released_year LIKE '%$search%' OR rating LIKE '%$search%' ORDER BY $sort_col $sort_dir LIMIT $start, $limit";
    $count_sql = "SELECT COUNT(*) as total FROM dramas WHERE title LIKE '%$search%' OR genre LIKE '%$search%' OR episodes LIKE '%$search%' OR released_year LIKE '%$search%' OR rating LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM dramas ORDER BY $sort_col $sort_dir LIMIT $start, $limit";
    $count_sql = "SELECT COUNT(*) as total FROM dramas";
}

$result = $conn->query($sql);
$featured_result = $conn->query("SELECT * FROM dramas ORDER BY rating DESC LIMIT 1");
$featured = $featured_result ? $featured_result->fetch_assoc() : null;
$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);

$avg_result = $conn->query("SELECT ROUND(AVG(rating),1) as avg_r, COUNT(*) as total, MAX(released_year) as latest FROM dramas");
$stats = $avg_result ? $avg_result->fetch_assoc() : ['avg_r'=>0,'total'=>0,'latest'=>'—'];

$genre_result = $conn->query("SELECT COUNT(DISTINCT genre) as c FROM dramas");
$gc = $genre_result ? $genre_result->fetch_assoc()['c'] : '—';

// Fetch download counts for each drama
$download_counts = [];
$dc = $conn->query("SELECT drama_id, COUNT(*) as count FROM downloads GROUP BY drama_id");
if($dc) while($row = $dc->fetch_assoc()) $download_counts[$row['drama_id']] = $row['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KDramaVerse — RiCious Collection</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --red:#E8173A;--red2:#B50F2B;--redglow:rgba(232,23,58,.18);--redsoft:rgba(232,23,58,.08);
  --gold:#F0B429;--goldsoft:rgba(240,180,41,.12);
  --ink:#070709;--bg:#0D0D12;--s1:#131318;--s2:#1A1A22;--s3:#22222D;
  --t0:#F2F0FF;--t1:#8E8CAA;--t2:#48475E;
  --border:rgba(255,255,255,.06);--bhi:rgba(255,255,255,.11);
  --glass:rgba(13,13,18,.82);
  --ease:cubic-bezier(.22,1,.36,1);
  --spring:cubic-bezier(.34,1.56,.64,1);
  --rad:12px;
  --green:#00C853;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--ink);color:var(--t0);
  min-height:100vh;overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;
  opacity:.025;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  background-size:180px;
}

/* Toast Notifications - NEW */
.toast-container{
  position:fixed;bottom:20px;right:20px;z-index:10000;
  display:flex;flex-direction:column;gap:10px;
}
.toast{
  background:var(--s2);border-left:4px solid var(--red);border-radius:10px;
  padding:14px 20px;box-shadow:0 8px 24px rgba(0,0,0,.4);
  animation: slideInRight .3s var(--ease);
  min-width:260px;backdrop-filter:blur(12px);
}
.toast.success{border-left-color:var(--green)}
.toast.info{border-left-color:var(--gold)}
@keyframes slideInRight{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}

/* ══ NAV ══ */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:64px;padding:0 4%;
  display:flex;align-items:center;justify-content:space-between;
  transition:all .4s var(--ease);
}
.nav.scrolled{
  background:var(--glass);
  backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);
}
.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-pill{
  height:32px;padding:0 14px;border-radius:6px;
  background:var(--red);
  display:flex;align-items:center;
  font-size:13px;font-weight:600;color:#fff;letter-spacing:.5px;
  box-shadow:0 4px 18px var(--redglow);
}
.nav-logo-name{
  font-family:'Cormorant Garamond',serif;
  font-size:20px;font-weight:700;color:var(--t0);letter-spacing:.3px;
}
.nav-logo-name em{color:var(--red);font-style:italic}
.nav-links{display:flex;gap:2px}
.nav-links a{
  color:var(--t1);text-decoration:none;font-size:13px;font-weight:400;
  padding:6px 14px;border-radius:8px;transition:all .2s;
}
.nav-links a:hover,.nav-links a.active{color:var(--t0);background:rgba(255,255,255,.05)}
.nav-right{display:flex;align-items:center;gap:8px}
.nav-search-btn{
  display:flex;align-items:center;gap:8px;
  background:var(--s1);border:1px solid var(--bhi);
  color:var(--t1);padding:7px 14px;border-radius:8px;
  font-size:12.5px;font-family:'DM Sans',sans-serif;cursor:pointer;
  transition:all .2s;
}
.nav-search-btn:hover{background:var(--s2);color:var(--t0)}
.nav-search-btn kbd{
  font-size:10px;padding:1px 6px;background:var(--s3);
  border:1px solid var(--bhi);border-radius:3px;font-family:monospace;
}
.nav-add{
  display:inline-flex;align-items:center;gap:6px;
  background:var(--red);color:#fff;padding:8px 18px;
  border-radius:8px;font-size:13px;font-weight:600;
  text-decoration:none;letter-spacing:.2px;
  transition:all .2s;box-shadow:0 4px 18px var(--redglow);
}
.nav-add:hover{background:var(--red2);transform:translateY(-1px)}

/* ══ SEARCH OVERLAY ══ */
.sov{
  display:none;position:fixed;inset:0;z-index:6000;
  background:rgba(7,7,9,.95);
  backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);
  align-items:flex-start;justify-content:center;padding-top:88px;
}
.sov.on{display:flex}
.sov-inner{width:100%;max-width:600px;padding:0 20px;animation:sovIn .26s var(--spring)}
@keyframes sovIn{from{opacity:0;transform:translateY(-18px) scale(.96)}to{opacity:1;transform:none}}
.sov-wrap{
  display:flex;align-items:center;
  background:var(--s2);border:1.5px solid var(--bhi);border-radius:14px;
  overflow:hidden;transition:border-color .2s,box-shadow .2s;
}
.sov-wrap:focus-within{border-color:var(--red);box-shadow:0 0 0 4px var(--redglow),0 24px 60px rgba(0,0,0,.8)}
.sov-icon{padding:0 16px;font-size:16px;color:var(--t2);pointer-events:none}
.sov-input{
  flex:1;background:none;border:none;outline:none;
  color:var(--t0);font-size:17px;font-family:'DM Sans',sans-serif;
  padding:16px 0;
}
.sov-input::placeholder{color:var(--t2)}
.sov-actions{display:flex;align-items:center;padding:0 12px;gap:6px}
.sov-clear{
  background:var(--s3);border:none;color:var(--t1);
  width:22px;height:22px;border-radius:50%;font-size:10px;cursor:pointer;
  display:none;align-items:center;justify-content:center;transition:background .2s;
}
.sov-clear.on{display:flex}
.sov-submit{
  background:var(--red);border:none;color:#fff;
  padding:8px 18px;border-radius:8px;
  font-size:12px;font-weight:600;font-family:'DM Sans',sans-serif;
  cursor:pointer;transition:background .2s;
}
.sov-submit:hover{background:var(--red2)}
.sov-chips{display:flex;align-items:center;gap:8px;margin-top:16px;flex-wrap:wrap}
.sov-chip-label{font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--t2);font-weight:600}
.sov-chip{
  padding:5px 14px;background:var(--s2);border:1px solid var(--border);
  border-radius:999px;color:var(--t1);font-size:12px;cursor:pointer;
  transition:all .2s;text-decoration:none;
}
.sov-chip:hover{background:var(--s3);border-color:var(--bhi);color:var(--t0)}
.sov-chip.on{background:var(--redsoft);border-color:rgba(232,23,58,.3);color:#ff6070}
.sov-esc{text-align:center;margin-top:18px;font-size:11.5px;color:var(--t2)}
.sov-esc kbd{padding:2px 7px;background:var(--s2);border:1px solid var(--bhi);border-radius:4px;font-family:monospace;font-size:11px}

/* ══ HERO ══ */
.hero{
  position:relative;height:100vh;min-height:620px;
  overflow:hidden;display:flex;align-items:flex-end;
}
.hero-bg{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center 18%;
  filter:brightness(.45) saturate(1.2);
  transform:scale(1.06);
  animation:hzoom 16s var(--ease) forwards;
}
@keyframes hzoom{to{transform:scale(1)}}
.hero-grad{
  position:absolute;inset:0;z-index:1;
  background:
    linear-gradient(to bottom,
      rgba(7,7,9,.85) 0%,
      rgba(7,7,9,0)   16%,
      rgba(7,7,9,0)   40%,
      rgba(7,7,9,.7)  65%,
      rgba(7,7,9,.97) 87%,
      var(--ink) 100%),
    linear-gradient(110deg,
      rgba(7,7,9,.96) 0%,
      rgba(7,7,9,.55) 40%,
      rgba(7,7,9,0)   62%);
}
.hero-placeholder{
  position:absolute;inset:0;
  background:radial-gradient(ellipse at 60% 30%,#1c0a34,#07070e);
  display:flex;align-items:center;justify-content:center;font-size:120px;opacity:.35;
}
.particles{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.p{
  position:absolute;border-radius:50%;
  background:rgba(255,255,255,.45);
  animation:pfloat linear infinite;
}
@keyframes pfloat{
  0%{transform:translateY(100vh) scale(0);opacity:0}
  10%{opacity:.5}90%{opacity:.2}
  100%{transform:translateY(-10vh) scale(1);opacity:0}
}
.hero-content{
  position:relative;z-index:3;padding:0 5% 90px;max-width:600px;
  animation:hcIn .9s var(--ease) .1s both;
}
@keyframes hcIn{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:none}}
.hero-badge-row{display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.hero-badge{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--red);color:#fff;
  padding:4px 12px;border-radius:4px;
  font-size:9.5px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;
  box-shadow:0 4px 14px var(--redglow);
}
.hero-tag{
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
  color:rgba(255,255,255,.72);padding:4px 11px;border-radius:4px;
  font-size:9.5px;font-weight:500;letter-spacing:.8px;text-transform:uppercase;
}
.hero-title{
  font-family:'Cormorant Garamond',serif;
  font-size:clamp(52px,9vw,106px);font-weight:700;
  line-height:.92;letter-spacing:-2px;
  text-shadow:0 2px 40px rgba(0,0,0,.65);
  margin-bottom:18px;
}
.hero-title em{color:var(--red);font-style:italic;display:block}
.hero-meta{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.hero-rating{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--goldsoft);border:1px solid rgba(240,180,41,.2);
  color:var(--gold);padding:5px 13px;border-radius:5px;
  font-size:13px;font-weight:700;
}
.hero-dot{color:var(--t2);font-size:11px}
.hero-info{font-size:13px;color:var(--t1)}
.hero-desc{
  font-size:14px;line-height:1.75;
  color:rgba(242,240,255,.55);max-width:440px;
  margin-bottom:28px;font-weight:300;
}
.hero-cta{display:flex;gap:10px;flex-wrap:wrap}
.btn-main{
  display:inline-flex;align-items:center;gap:8px;
  background:#fff;color:#07070E;
  padding:13px 28px;border-radius:9px;
  font-size:13.5px;font-weight:600;letter-spacing:.2px;
  font-family:'DM Sans',sans-serif;
  text-decoration:none;border:none;cursor:pointer;
  transition:all .22s;
}
.btn-main:hover{background:rgba(255,255,255,.88);transform:translateY(-1px);box-shadow:0 10px 30px rgba(255,255,255,.1)}
.btn-ghost{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(255,255,255,.07);color:#fff;
  padding:13px 26px;border-radius:9px;
  font-size:13.5px;font-weight:500;
  font-family:'DM Sans',sans-serif;
  text-decoration:none;border:1px solid rgba(255,255,255,.14);
  cursor:pointer;backdrop-filter:blur(8px);transition:all .22s;
}
.btn-ghost:hover{background:rgba(255,255,255,.12);transform:translateY(-1px)}
.btn-download{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--redsoft);border:1px solid rgba(232,23,58,.25);color:var(--red);
  padding:13px 22px;border-radius:9px;
  font-size:13.5px;font-weight:600;
  font-family:'DM Sans',sans-serif;
  text-decoration:none;cursor:pointer;transition:all .22s;
}
.btn-download:hover{background:var(--red);color:#fff;transform:translateY(-1px);box-shadow:0 8px 22px var(--redglow)}

.hero-scroll{
  position:absolute;bottom:64px;left:50%;transform:translateX(-50%);
  z-index:3;display:flex;flex-direction:column;align-items:center;gap:5px;
  font-size:9.5px;letter-spacing:2px;text-transform:uppercase;color:var(--t2);
  animation:bob 2.6s ease-in-out infinite;
}
@keyframes bob{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(-5px)}}
.scroll-track{width:1px;height:32px;background:linear-gradient(to bottom,transparent,var(--t2))}

/* ══ STATS RIBBON ══ */
.stats-ribbon{
  display:grid;grid-template-columns:repeat(4,1fr);
  background:var(--s1);
  border-top:1px solid var(--border);border-bottom:1px solid var(--border);
}
.stat-block{
  padding:22px 0;text-align:center;
  border-right:1px solid var(--border);
  position:relative;overflow:hidden;
  transition:background .25s;cursor:default;
}
.stat-block:last-child{border-right:none}
.stat-block::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,var(--redsoft) 0%,transparent 60%);
  opacity:0;transition:opacity .3s;
}
.stat-block:hover{background:var(--s2)}
.stat-block:hover::before{opacity:1}
.stat-n{
  font-family:'Cormorant Garamond',serif;
  font-size:34px;font-weight:700;
  color:var(--red);line-height:1;margin-bottom:4px;
}
.stat-l{font-size:9.5px;text-transform:uppercase;letter-spacing:1.6px;color:var(--t2);font-weight:600}

/* ══ MAIN CONTENT ══ */
.main{
  padding:0 4% 80px;
  background:linear-gradient(to bottom,var(--ink) 0%,var(--bg) 100%);
}

/* Section header */
.sec-head{
  display:flex;align-items:flex-end;justify-content:space-between;
  padding:52px 0 24px;
  border-bottom:1px solid var(--border);margin-bottom:28px;
  flex-wrap:wrap;gap:12px;
}
.sec-title{
  font-family:'Cormorant Garamond',serif;
  font-size:40px;font-weight:700;letter-spacing:-1px;line-height:1;
}
.sec-eyebrow{font-size:9.5px;letter-spacing:2px;text-transform:uppercase;color:var(--red);font-weight:600;margin-bottom:7px}
.sec-sub{font-size:12px;color:var(--t2);margin-top:5px}

/* Sort controls */
.sort-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.sort-label{font-size:11.5px;color:var(--t2)}
.sort-btn{
  padding:6px 14px;border-radius:7px;
  font-size:12px;font-family:'DM Sans',sans-serif;
  border:1px solid var(--border);
  background:transparent;color:var(--t1);cursor:pointer;
  transition:all .2s;text-decoration:none;display:inline-block;
}
.sort-btn:hover{background:var(--s2);border-color:var(--bhi);color:var(--t0)}
.sort-btn.active{background:var(--red);border-color:var(--red);color:#fff}

/* Search result banner */
.srb{
  margin:88px 0 32px;
  border-radius:var(--rad);overflow:hidden;
}
.srb-inner{
  background:var(--s1);border:1px solid var(--bhi);
  border-radius:var(--rad);padding:24px 28px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
}
.srb-h2{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--t1);margin-bottom:4px}
.srb-h2 strong{color:var(--t0)}
.srb-p{font-size:12px;color:var(--t2)}
.srb-btns{display:flex;gap:8px}
.srb-btn{
  padding:8px 16px;border-radius:8px;font-size:12px;
  font-family:'DM Sans',sans-serif;cursor:pointer;
  transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px;
}
.srb-btn-a{background:var(--s3);border:1px solid var(--bhi);color:var(--t1)}
.srb-btn-a:hover{background:rgba(255,255,255,.07);color:var(--t0)}
.srb-btn-b{background:transparent;border:1px solid var(--border);color:var(--t2)}
.srb-btn-b:hover{border-color:var(--bhi);color:var(--t1)}

/* ══ CARD GRID ══ */
.grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(165px,1fr));
  gap:18px;
}

/* ══ DRAMA CARD ══ */
.card{
  position:relative;cursor:pointer;border-radius:var(--rad);
  overflow:visible;
  transition:transform .36s var(--ease),z-index 0s .36s;
  animation:cin .45s var(--ease) both;
}
@keyframes cin{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:none}}
.card:nth-child(1){animation-delay:.04s}.card:nth-child(2){animation-delay:.07s}
.card:nth-child(3){animation-delay:.10s}.card:nth-child(4){animation-delay:.13s}
.card:nth-child(5){animation-delay:.16s}.card:nth-child(6){animation-delay:.19s}
.card:nth-child(7){animation-delay:.22s}.card:nth-child(8){animation-delay:.25s}
.card:nth-child(9){animation-delay:.28s}.card:nth-child(10){animation-delay:.31s}
.card:nth-child(11){animation-delay:.34s}.card:nth-child(12){animation-delay:.37s}

.card:hover{
  transform:scale(1.08) translateY(-8px);z-index:200;
  transition:transform .36s var(--ease),z-index 0s;
}
.card:hover .cpw{
  box-shadow:0 28px 60px rgba(0,0,0,.95),0 0 0 1px var(--bhi),0 0 40px var(--redglow);
}
.cpw{
  position:relative;aspect-ratio:2/3;border-radius:var(--rad);
  overflow:hidden;background:var(--s2);
  transition:box-shadow .36s;
}
.cposter{width:100%;height:100%;object-fit:cover;display:block;
  transition:filter .36s,transform .5s var(--ease)}
.card:hover .cposter{filter:brightness(.22) saturate(.6);transform:scale(1.05)}
.cph{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  font-size:52px;background:linear-gradient(145deg,var(--s2),var(--s3));
}

/* Badges */
.cbadge-rating{
  position:absolute;top:8px;right:8px;z-index:2;
  background:rgba(7,7,9,.85);color:var(--gold);
  font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:5px;
  border:1px solid rgba(240,180,41,.12);
  backdrop-filter:blur(6px);display:flex;align-items:center;gap:2px;
  transition:opacity .3s,transform .3s;
}
.card:hover .cbadge-rating{opacity:0;transform:scale(.75)}
.cbadge-genre{
  position:absolute;top:8px;left:8px;z-index:2;
  background:var(--red);color:#fff;
  font-size:7.5px;font-weight:700;padding:3px 9px;border-radius:4px;
  letter-spacing:.9px;text-transform:uppercase;
  opacity:0;transform:translateX(-4px);
  transition:opacity .3s,transform .3s;pointer-events:none;
  max-width:88px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.card:hover .cbadge-genre{opacity:1;transform:none}
.download-count-badge{
  position:absolute;bottom:8px;right:8px;z-index:2;
  background:rgba(0,200,83,.9);color:#000;
  font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;
  display:flex;align-items:center;gap:3px;
  backdrop-filter:blur(4px);
}

/* Card overlay */
.cover{
  position:absolute;inset:0;
  background:linear-gradient(to top,
    rgba(7,7,9,1) 0%,rgba(7,7,9,.86) 42%,rgba(7,7,9,0) 100%);
  opacity:0;transition:opacity .3s;
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:12px;border-radius:var(--rad);
}
.card:hover .cover{opacity:1}
.cov-title{font-size:11.5px;font-weight:600;color:#fff;line-height:1.3;margin-bottom:5px}
.cov-meta{display:flex;align-items:center;gap:5px;flex-wrap:wrap;margin-bottom:10px}
.cov-rating{color:var(--gold);font-size:10px;font-weight:700;display:flex;align-items:center;gap:2px}
.cov-yr,.cov-eps{color:var(--t1);font-size:9.5px}
.cov-btns{display:flex;gap:5px}
.cov-btn{
  flex:1;padding:7px 4px;border-radius:6px;
  font-size:9.5px;font-weight:600;font-family:'DM Sans',sans-serif;
  cursor:pointer;text-decoration:none;text-align:center;
  display:flex;align-items:center;justify-content:center;gap:2px;
  letter-spacing:.3px;transition:opacity .2s,transform .1s;border:none;
}
.cov-btn:hover{opacity:.8;transform:scale(.97)}
.cov-edit{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.18)}
.cov-dl{background:var(--red);color:#fff}
.cov-del{background:rgba(232,23,58,.15);color:var(--red);border:1px solid rgba(232,23,58,.2)}

.clabel{
  padding:8px 2px 2px;font-size:12px;font-weight:400;color:var(--t1);
  line-height:1.3;transition:color .2s;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  display:flex;align-items:center;justify-content:space-between;
}
.card:hover .clabel{color:var(--t0)}
.dl-count-small{
  font-size:9px;background:var(--greensoft);padding:2px 6px;border-radius:10px;
  color:var(--green);margin-left:6px;
}

/* Empty */
.empty{grid-column:1/-1;text-align:center;padding:90px 20px}
.empty-i{font-size:70px;opacity:.3;margin-bottom:16px}
.empty-t{font-family:'Cormorant Garamond',serif;font-size:28px;color:var(--t1);margin-bottom:6px}
.empty-s{font-size:13px;color:var(--t2)}

/* Pagination */
.pgn{display:flex;justify-content:center;align-items:center;gap:4px;margin-top:56px;flex-wrap:wrap}
.pgi{
  padding:9px 16px;border-radius:8px;text-decoration:none;
  font-size:13px;font-weight:500;font-family:'DM Sans',sans-serif;
  transition:all .2s;border:1px solid var(--border);cursor:pointer;display:inline-flex;align-items:center;
}
.pgi-n{background:transparent;color:var(--t1)}
.pgi-n:hover{background:var(--s2);color:var(--t0);border-color:var(--bhi)}
.pgi-a{background:var(--red);color:#fff;border-color:var(--red)}
.pgi-nav{background:var(--s1);color:var(--t1)}
.pgi-nav:hover{background:var(--s2);color:var(--t0)}
.pgi-off{opacity:.2;pointer-events:none}

/* ══ DETAIL MODAL ══ */
.modal-bg{
  display:none;position:fixed;inset:0;z-index:4000;
  background:rgba(7,7,9,.94);
  backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);
  justify-content:center;align-items:center;padding:20px;
}
.modal-bg.on{display:flex}
.modal{
  position:relative;background:var(--s1);
  border:1px solid var(--bhi);border-radius:18px;
  width:100%;max-width:860px;
  animation:mIn .34s var(--spring);
  box-shadow:0 60px 120px rgba(0,0,0,.95),0 0 80px rgba(232,23,58,.05);
  max-height:92vh;overflow-y:auto;scrollbar-width:none;
}
.modal::-webkit-scrollbar{display:none}
@keyframes mIn{from{transform:scale(.87) translateY(28px);opacity:0}to{transform:none;opacity:1}}

/* Modal banner */
.mbanner{position:relative;height:320px;overflow:hidden;flex-shrink:0}
.mbanner-img{
  width:100%;height:100%;object-fit:cover;object-position:center 18%;
  display:block;filter:brightness(.42) saturate(1.1);
}
.mbanner-ph{
  width:100%;height:100%;
  background:radial-gradient(ellipse at 60% 40%,#1a083a,#070710);
  display:flex;align-items:center;justify-content:center;font-size:80px;opacity:.4;
}
.mbanner-grad{
  position:absolute;inset:0;
  background:
    linear-gradient(to bottom,transparent 20%,rgba(19,19,24,.75) 68%,var(--s1) 100%),
    linear-gradient(to right,rgba(19,19,24,.72) 0%,transparent 52%);
}
.mbanner-content{
  position:absolute;bottom:0;left:0;right:0;
  padding:0 30px 26px;z-index:2;
}
.mbanner-eyebrow{display:flex;align-items:center;gap:8px;margin-bottom:9px;flex-wrap:wrap}
.mbanner-badge{
  background:var(--red);color:#fff;
  padding:3px 11px;border-radius:4px;
  font-size:9px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;
}
.mtag{
  background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);
  color:rgba(255,255,255,.72);padding:3px 10px;border-radius:4px;
  font-size:9px;font-weight:500;letter-spacing:.8px;text-transform:uppercase;
}
.mbanner-title{
  font-family:'Cormorant Garamond',serif;
  font-size:clamp(24px,4vw,42px);font-weight:700;
  letter-spacing:-1px;line-height:.98;
  text-shadow:0 2px 18px rgba(0,0,0,.55);
}
.mrating-corner{
  position:absolute;top:16px;right:16px;z-index:3;
  background:rgba(7,7,9,.88);
  border:1px solid rgba(240,180,41,.18);
  border-radius:12px;padding:10px 14px;
  backdrop-filter:blur(10px);
  display:flex;align-items:center;gap:10px;
}
.mrating-num{
  font-family:'Cormorant Garamond',serif;
  font-size:30px;font-weight:700;color:var(--gold);line-height:1;
}
.mstars{display:flex;gap:2px;margin-bottom:3px}
.mstar{font-size:11px}
.mstar.on{color:var(--gold)}.mstar.off{color:var(--t2)}
.mrating-label{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:var(--t2)}

/* Modal body */
.mbody{padding:22px 30px 28px}
.mstats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px}
.mstat{
  background:var(--s2);border:1px solid var(--border);
  border-radius:10px;padding:14px 16px;
  display:flex;align-items:center;gap:11px;
  transition:border-color .2s;
}
.mstat:hover{border-color:var(--bhi)}
.mstat-icon{font-size:20px;flex-shrink:0}
.mstat-label{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:var(--t2);font-weight:600;margin-bottom:3px}
.mstat-val{font-size:16px;font-weight:600;color:var(--t0)}
.mdivider{height:1px;background:var(--border);margin-bottom:16px}
.mlabel{font-size:9.5px;text-transform:uppercase;letter-spacing:1.5px;color:var(--t2);font-weight:600;margin-bottom:9px}
.mabout{font-size:14px;line-height:1.74;color:var(--t1);font-weight:300}
.mactions{display:flex;gap:8px;margin-top:24px;flex-wrap:wrap}
.maction{
  flex:1;min-width:120px;padding:13px 16px;border-radius:9px;
  font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;
  text-align:center;text-decoration:none;cursor:pointer;border:none;
  transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px;
  letter-spacing:.2px;
}
.maction:hover{opacity:.85;transform:translateY(-1px)}
.maction-edit{background:#1654DB;color:#fff}
.maction-dl{background:var(--red);color:#fff;box-shadow:0 4px 16px var(--redglow)}
.maction-del{background:rgba(232,23,58,.1);color:var(--red);border:1px solid rgba(232,23,58,.2)}
.maction-del:hover{background:var(--red);color:#fff}
.maction-downloads{
  background:rgba(0,200,83,.1);color:var(--green);border:1px solid rgba(0,200,83,.2);
}
.maction-downloads:hover{background:var(--green);color:#fff;border-color:var(--green)}
.mclose{
  position:absolute;top:14px;left:14px;
  width:36px;height:36px;border-radius:50%;
  background:rgba(7,7,9,.8);border:1px solid var(--bhi);
  color:var(--t1);font-size:18px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  z-index:10;transition:all .2s;backdrop-filter:blur(8px);
}
.mclose:hover{background:var(--red);color:#fff;border-color:var(--red)}

/* ══ SCROLLBAR ══ */
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-track{background:var(--ink)}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px}
::-webkit-scrollbar-thumb:hover{background:var(--bhi)}

/* ══ FOOTER ══ */
footer{
  text-align:center;padding:38px 5%;
  border-top:1px solid var(--border);background:var(--s1);
  display:flex;flex-direction:column;align-items:center;gap:10px;
}
.footer-logo{font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:var(--t1)}
.footer-logo em{color:var(--red);font-style:italic}
.footer-copy{font-size:11px;color:var(--t2)}

@media(max-width:640px){
  .nav-links{display:none}
  .stats-ribbon{grid-template-columns:repeat(2,1fr)}
  .grid{grid-template-columns:repeat(auto-fill,minmax(130px,1fr))}
  .mstats{grid-template-columns:1fr 1fr}
  .mbody{padding:16px 18px 22px}
  .mbanner{height:240px}
  .mbanner-content{padding:0 18px 20px}
}
</style>
</head>
<body>

<!-- Toast Container - NEW -->
<div class="toast-container" id="toastContainer"></div>

<!-- NAV -->
<nav class="nav" id="nav">
  <a href="?" class="nav-brand">
    <div class="nav-logo-pill">KV</div>
    <span class="nav-logo-name">Drama<em>Verse</em></span>
  </a>
  <div class="nav-links">
    <a href="?" class="active">Home</a>
    <a href="?sort=rating">Top Rated</a>
    <a href="?search=romance">Romance</a>
    <a href="?search=thriller">Thriller</a>
    <a href="download.php">Downloads</a>
  </div>
  <div class="nav-right">
    <button class="nav-search-btn" onclick="openSov()">🔍 Search <kbd>/</kbd></button>
    <a href="add.php" class="nav-add">＋ Add Drama</a>
  </div>
</nav>

<!-- SEARCH OVERLAY -->
<div class="sov" id="sov" onclick="sovBgClick(event)">
  <div class="sov-inner">
    <form method="GET">
      <div class="sov-wrap">
        <span class="sov-icon">🔍</span>
        <input type="text" name="search" class="sov-input" id="sovInput"
          placeholder="Search dramas, genres, year…"
          value="<?php echo htmlspecialchars($search); ?>"
          autocomplete="off" spellcheck="false">
        <div class="sov-actions">
          <button type="button" class="sov-clear" id="sovClear" onclick="clearSov()">✕</button>
          <button type="submit" class="sov-submit">Search</button>
        </div>
      </div>
    </form>
    <div class="sov-chips">
      <span class="sov-chip-label">Quick:</span>
      <?php foreach(['Romance','Thriller','Fantasy','Comedy','Mystery','Historical'] as $g):
        $a = strtolower($search)===strtolower($g); ?>
      <a href="?search=<?php echo urlencode($g);?>" class="sov-chip <?php echo $a?'on':'';?>" onclick="closeSov()"><?php echo $g;?></a>
      <?php endforeach; ?>
    </div>
    <p class="sov-esc">Press <kbd>Esc</kbd> to close</p>
  </div>
</div>

<?php if(empty($search)): ?>
<!-- HERO -->
<section class="hero">
  <?php if($featured):
    $img = !empty($featured['image']) ? basename($featured['image']) : '';
    $imgP = $img ? "img/".$img : "";
    $hasHero = $imgP && file_exists(__DIR__."/".$imgP);
  ?>
    <?php if($hasHero): ?>
      <img class="hero-bg" src="<?php echo htmlspecialchars($imgP);?>" alt="">
    <?php else: ?>
      <div class="hero-placeholder">🎭</div>
    <?php endif; ?>
    <div class="particles" id="particles"></div>
    <div class="hero-grad"></div>
    <div class="hero-content">
      <div class="hero-badge-row">
        <span class="hero-badge">★ Top Rated</span>
        <?php foreach(array_slice(explode(',',$featured['genre']),0,2) as $g): ?>
        <span class="hero-tag"><?php echo trim(htmlspecialchars($g));?></span>
        <?php endforeach; ?>
      </div>
      <?php
        $words = explode(' ', $featured['title']);
        $mid = ceil(count($words)/2);
        $line1 = implode(' ', array_slice($words,0,$mid));
        $line2 = implode(' ', array_slice($words,$mid));
      ?>
      <h1 class="hero-title">
        <?php echo htmlspecialchars($line1);?>
        <?php if($line2): ?><em><?php echo htmlspecialchars($line2);?></em><?php endif; ?>
      </h1>
      <div class="hero-meta">
        <span class="hero-rating">⭐ <?php echo $featured['rating'];?>/10</span>
        <span class="hero-dot">·</span>
        <span class="hero-info"><?php echo $featured['released_year'];?></span>
        <span class="hero-dot">·</span>
        <span class="hero-info"><?php echo $featured['episodes'];?> Episodes</span>
      </div>
      <p class="hero-desc">The crown jewel of your collection — this drama commands your attention from the very first scene.</p>
      <div class="hero-cta">
        <button class="btn-main"
          onclick="openModal('<?php echo addslashes(htmlspecialchars($imgP));?>','<?php echo addslashes(htmlspecialchars($featured['title']));?>','<?php echo addslashes(htmlspecialchars($featured['genre']));?>','<?php echo $featured['rating'];?>','<?php echo $featured['episodes'];?>','<?php echo $featured['released_year'];?>','<?php echo $featured['id'];?>')">
          ▶ View Details
        </button>
        <button class="btn-download" onclick="startSmartDownload(<?php echo $featured['id'];?>, '<?php echo addslashes($featured['title']);?>')">⬇ Download</button>
        <a href="edit.php?id=<?php echo $featured['id'];?>" class="btn-ghost">✏ Edit</a>
      </div>
    </div>
    <div class="hero-scroll">
      <span>Scroll</span>
      <div class="scroll-track"></div>
    </div>
  <?php else: ?>
    <div class="hero-placeholder">🎭</div>
    <div class="hero-grad"></div>
    <div class="hero-content">
      <h1 class="hero-title">Your Drama<br><em>Library</em></h1>
      <p class="hero-desc">Start building your personal K-Drama collection today.</p>
      <div class="hero-cta"><a href="add.php" class="btn-main">＋ Add First Drama</a></div>
    </div>
  <?php endif; ?>
</section>

<!-- STATS RIBBON -->
<div class="stats-ribbon">
  <div class="stat-block"><div class="stat-n"><?php echo $total_row['total'];?></div><div class="stat-l">Titles</div></div>
  <div class="stat-block"><div class="stat-n"><?php echo $stats['avg_r']?:'—';?></div><div class="stat-l">Avg Rating</div></div>
  <div class="stat-block"><div class="stat-n"><?php echo $gc;?></div><div class="stat-l">Genres</div></div>
  <div class="stat-block">
    <?php $lr=$conn->query("SELECT MAX(released_year) as y FROM dramas");$ly=$lr?$lr->fetch_assoc()['y']:'—'; ?>
    <div class="stat-n"><?php echo $ly?:'—';?></div><div class="stat-l">Latest Year</div>
  </div>
</div>
<?php endif; ?>

<!-- MAIN -->
<div class="main">
  <?php if(!empty($search)): ?>
  <div class="srb">
    <div class="srb-inner">
      <div>
        <div class="srb-h2">Results for <strong>"<?php echo htmlspecialchars($search);?>"</strong></div>
        <div class="srb-p"><?php echo $total_row['total'];?> drama<?php echo $total_row['total']!=1?'s':'';?> found</div>
      </div>
      <div class="srb-btns">
        <button class="srb-btn srb-btn-a" onclick="openSov()">🔍 New Search</button>
        <a href="?" class="srb-btn srb-btn-b">✕ Clear</a>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="sec-head">
    <div>
      <div class="sec-eyebrow">Your Collection</div>
      <div class="sec-title">All Dramas</div>
      <div class="sec-sub"><?php echo $total_row['total'];?> titles</div>
    </div>
    <div class="sort-row">
      <span class="sort-label">Sort by:</span>
      <?php $sorts=['rating'=>'⭐ Rating','released_year'=>'📅 Year','episodes'=>'🎬 Episodes','title'=>'🔤 Title'];
      foreach($sorts as $key=>$label): ?>
      <a href="?sort=<?php echo $key;?>" class="sort-btn <?php echo $sort===$key?'active':'';?>"><?php echo $label;?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- GRID -->
  <div class="grid">
  <?php if($result && $result->num_rows>0){
    while($row=$result->fetch_assoc()){
      $img=!empty($row['image'])?basename($row['image']):'';
      $imgP=$img?"img/".$img:"";
      $hasImg=$imgP&&file_exists(__DIR__."/".$imgP);
      $tj=addslashes(htmlspecialchars($row['title']));
      $gj=addslashes(htmlspecialchars($row['genre']));
      $ij=addslashes(htmlspecialchars($imgP));
      $fg=trim(explode(',',$row['genre'])[0]);
      $dlCount = isset($download_counts[$row['id']]) ? $download_counts[$row['id']] : 0;
  ?>
    <div class="card"
      onclick="openModal('<?php echo $ij;?>','<?php echo $tj;?>','<?php echo $gj;?>','<?php echo $row['rating'];?>','<?php echo $row['episodes'];?>','<?php echo $row['released_year'];?>','<?php echo $row['id'];?>')">
      <div class="cpw">
        <?php if($hasImg): ?>
          <img class="cposter" src="<?php echo htmlspecialchars($imgP);?>" alt="<?php echo htmlspecialchars($row['title']);?>" loading="lazy">
        <?php else: ?>
          <div class="cph">🎭</div>
        <?php endif; ?>
        <div class="cbadge-rating">⭐ <?php echo $row['rating'];?></div>
        <div class="cbadge-genre"><?php echo htmlspecialchars($fg);?></div>
        <?php if($dlCount > 0): ?>
        <div class="download-count-badge"><i class="fas fa-download"></i> <?php echo $dlCount; ?></div>
        <?php endif; ?>
        <div class="cover">
          <div class="cov-title"><?php echo htmlspecialchars($row['title']);?></div>
          <div class="cov-meta">
            <span class="cov-rating">⭐ <?php echo $row['rating'];?></span>
            <span class="cov-yr"><?php echo $row['released_year'];?></span>
            <span class="cov-eps">· <?php echo $row['episodes'];?> eps</span>
          </div>
          <div class="cov-btns" onclick="event.stopPropagation()">
            <a href="edit.php?id=<?php echo $row['id'];?>" class="cov-btn cov-edit">✏ Edit</a>
            <button class="cov-btn cov-dl" onclick="startSmartDownload(<?php echo $row['id'];?>, '<?php echo addslashes($row['title']);?>')">⬇ DL</button>
            <a href="delete.php?id=<?php echo $row['id'];?>" class="cov-btn cov-del" onclick="return confirm('Remove?')">🗑</a>
          </div>
        </div>
      </div>
      <div class="clabel">
        <?php echo htmlspecialchars($row['title']);?>
        <?php if($dlCount > 0): ?>
        <span class="dl-count-small"><i class="fas fa-download"></i> <?php echo $dlCount; ?></span>
        <?php endif; ?>
      </div>
    </div>
  <?php }}else{ ?>
    <div class="empty">
      <div class="empty-i">🔍</div>
      <div class="empty-t">No Dramas Found</div>
      <div class="empty-s">Try a different keyword or browse all dramas.</div>
    </div>
  <?php } ?>
  </div>

  <!-- PAGINATION -->
  <?php if($total_pages>1):
    $q=!empty($search)?"&search=".urlencode($search):'';
    $q.=$sort!='rating'?"&sort=".urlencode($sort):'';
  ?>
  <div class="pgn">
    <?php
    echo"<a class='pgi pgi-nav".($page==1?" pgi-off":"")."' href='?page=1$q'>«</a>";
    if($page>1) echo"<a class='pgi pgi-nav' href='?page=".($page-1)."$q'>‹</a>";
    $sp=max(1,$page-2);$ep=min($total_pages,$page+2);
    if($sp>1) echo"<span class='pgi pgi-n' style='opacity:.3'>…</span>";
    for($i=$sp;$i<=$ep;$i++){
      echo $i==$page?"<span class='pgi pgi-a'>$i</span>":"<a class='pgi pgi-n' href='?page=$i$q'>$i</a>";
    }
    if($ep<$total_pages) echo"<span class='pgi pgi-n' style='opacity:.3'>…</span>";
    if($page<$total_pages) echo"<a class='pgi pgi-nav' href='?page=".($page+1)."$q'>›</a>";
    echo"<a class='pgi pgi-nav".($page==$total_pages?" pgi-off":"")."' href='?page=$total_pages$q'>»</a>";
    ?>
  </div>
  <?php endif; ?>
</div>

<footer>
  <div class="footer-logo">Drama<em>Verse</em></div>
  <div class="footer-copy">© <?php echo date('Y');?> RiCious KDramaVerse — Your Personal Drama Collection</div>
</footer>

<!-- DETAIL MODAL -->
<div class="modal-bg" id="modal-bg" onclick="closeModal()">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="mclose" onclick="closeModal()">×</button>
    <div class="mbanner">
      <img id="mbannerImg" src="" alt="" class="mbanner-img" style="display:none">
      <div id="mbannerPh" class="mbanner-ph">🎭</div>
      <div class="mbanner-grad"></div>
      <div class="mrating-corner">
        <div class="mrating-num" id="mRatingNum"></div>
        <div>
          <div class="mstars" id="mStars"></div>
          <div class="mrating-label">out of 10</div>
        </div>
      </div>
      <div class="mbanner-content">
        <div class="mbanner-eyebrow">
          <span class="mbanner-badge">★ Drama</span>
          <span class="mtag" id="mGenre1"></span>
        </div>
        <div class="mbanner-title" id="mTitle"></div>
      </div>
    </div>
    <div class="mbody">
      <div class="mstats">
        <div class="mstat"><div class="mstat-icon">📅</div><div><div class="mstat-label">Released</div><div class="mstat-val" id="mYear"></div></div></div>
        <div class="mstat"><div class="mstat-icon">🎬</div><div><div class="mstat-label">Episodes</div><div class="mstat-val" id="mEps"></div></div></div>
        <div class="mstat"><div class="mstat-icon">⭐</div><div><div class="mstat-label">Rating</div><div class="mstat-val" id="mRatingStat"></div></div></div>
      </div>
      <div class="mdivider"></div>
      <div class="mlabel">Synopsis</div>
      <p class="mabout" id="mAbout"></p>
      <div class="mactions" id="mActions"></div>
    </div>
  </div>
</div>

<script>
(function(){
  const c=document.getElementById('particles');
  if(!c)return;
  for(let i=0;i<16;i++){
    const p=document.createElement('div');p.className='p';
    const s=Math.random()*2.5+.8;
    p.style.cssText=`width:${s}px;height:${s}px;left:${Math.random()*100}%;animation-duration:${Math.random()*12+10}s;animation-delay:${Math.random()*14}s`;
    c.appendChild(p);
  }
})();

window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('scrolled',scrollY>50);
},{passive:true});

function stars(r){
  return Array.from({length:5},(_,i)=>`<span class="mstar ${i<Math.round(r/2)?'on':'off'}">★</span>`).join('');
}

function showToast(message, type = 'success'){
  const container = document.getElementById('toastContainer');
  if(!container) return;
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

// Quality selection modal for download
let selectedQuality = '1080p';
let selectedLang = 'English';

function showQualityModal(dramaId, dramaTitle){
  const qualityOptions = `
    <div style="position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.95);backdrop-filter:blur(12px);display:flex;align-items:center;justify-content:center;padding:20px" id="qualityModal">
      <div style="background:var(--s1);border:1px solid var(--bhi);border-radius:20px;max-width:400px;width:100%;padding:28px;animation:fadeInUp .3s">
        <h3 style="margin-bottom:20px;font-size:24px">⬇ Download Options</h3>
        <p style="color:var(--t2);margin-bottom:20px">${dramaTitle}</p>
        <div style="margin-bottom:20px">
          <label style="display:block;margin-bottom:10px;color:var(--t2);font-size:12px">Video Quality</label>
          <div style="display:flex;gap:10px">
            <button onclick="setQuality('4K', '~18 GB')" class="quality-opt" data-quality="4K" style="flex:1;padding:12px;background:var(--s2);border:1px solid var(--border);border-radius:10px;cursor:pointer;color:var(--t0)">4K<br><span style="font-size:10px;color:var(--gold)">Best</span></button>
            <button onclick="setQuality('1080p', '~8 GB')" class="quality-opt active-quality" data-quality="1080p" style="flex:1;padding:12px;background:var(--red);border:1px solid var(--red);border-radius:10px;cursor:pointer;color:#fff">1080p<br><span style="font-size:10px">Full HD</span></button>
            <button onclick="setQuality('720p', '~4 GB')" class="quality-opt" data-quality="720p" style="flex:1;padding:12px;background:var(--s2);border:1px solid var(--border);border-radius:10px;cursor:pointer;color:var(--t0)">720p<br><span style="font-size:10px">HD</span></button>
          </div>
        </div>
        <div style="margin-bottom:24px">
          <label style="display:block;margin-bottom:10px;color:var(--t2);font-size:12px">Subtitles</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button onclick="setLang(this, 'English')" class="lang-opt active-lang" style="padding:8px 16px;background:var(--red);border:none;border-radius:8px;cursor:pointer;color:#fff">🇺🇸 English</button>
            <button onclick="setLang(this, 'Filipino')" class="lang-opt" style="padding:8px 16px;background:var(--s2);border:1px solid var(--border);border-radius:8px;cursor:pointer;color:var(--t1)">🇵🇭 Filipino</button>
            <button onclick="setLang(this, 'Korean')" class="lang-opt" style="padding:8px 16px;background:var(--s2);border:1px solid var(--border);border-radius:8px;cursor:pointer;color:var(--t1)">🇰🇷 Korean</button>
            <button onclick="setLang(this, 'Chinese')" class="lang-opt" style="padding:8px 16px;background:var(--s2);border:1px solid var(--border);border-radius:8px;cursor:pointer;color:var(--t1)">🇨🇳 Chinese</button>
            <button onclick="setLang(this, 'Japanese')" class="lang-opt" style="padding:8px 16px;background:var(--s2);border:1px solid var(--border);border-radius:8px;cursor:pointer;color:var(--t1)">🇯🇵 Japanese</button>
          </div>
        </div>
        <div style="display:flex;gap:10px">
          <button onclick="startDownloadWithOptions(${dramaId}, '${dramaTitle}')" style="flex:1;padding:14px;background:var(--red);border:none;border-radius:10px;color:#fff;font-weight:600;cursor:pointer"><i class="fas fa-download"></i> Download</button>
          <button onclick="closeQualityModal()" style="padding:14px 20px;background:var(--s2);border:1px solid var(--border);border-radius:10px;color:var(--t1);cursor:pointer">Cancel</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', qualityOptions);
  document.body.style.overflow = 'hidden';
}

function setQuality(quality, size){
  selectedQuality = quality;
  document.querySelectorAll('.quality-opt').forEach(btn => {
    btn.style.background = 'var(--s2)';
    btn.style.borderColor = 'var(--border)';
    btn.style.color = 'var(--t0)';
  });
  const active = document.querySelector(`.quality-opt[data-quality="${quality}"]`);
  if(active){
    active.style.background = 'var(--red)';
    active.style.borderColor = 'var(--red)';
    active.style.color = '#fff';
  }
}

function setLang(btn, lang){
  selectedLang = lang;
  document.querySelectorAll('.lang-opt').forEach(b => {
    b.style.background = 'var(--s2)';
    b.style.borderColor = 'var(--border)';
    b.style.color = 'var(--t1)';
  });
  btn.style.background = 'var(--red)';
  btn.style.borderColor = 'var(--red)';
  btn.style.color = '#fff';
}

function closeQualityModal(){
  const modal = document.getElementById('qualityModal');
  if(modal) modal.remove();
  document.body.style.overflow = '';
}

function startDownloadWithOptions(dramaId, dramaTitle){
  closeQualityModal();
  
  // Create form and submit to download.php
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'download.php';
  form.target = '_blank';
  
  const fields = {
    drama_id: dramaId,
    quality: selectedQuality,
    language: selectedLang,
    size: selectedQuality === '4K' ? '~18 GB' : (selectedQuality === '1080p' ? '~8 GB' : '~4 GB')
  };
  
  for(let key in fields){
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = fields[key];
    form.appendChild(input);
  }
  
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
  
  showToast(`Downloading ${dramaTitle} ${selectedQuality} with ${selectedLang} subtitles!`, 'success');
  setTimeout(() => location.reload(), 1500);
}

function startSmartDownload(dramaId, dramaTitle){
  showQualityModal(dramaId, dramaTitle);
}

function openModal(src,title,genre,rating,eps,year,id){
  const bi=document.getElementById('mbannerImg'),bp=document.getElementById('mbannerPh');
  if(src&&src!=='img/'&&src!==''){bi.src=src;bi.style.display='block';bp.style.display='none';}
  else{bi.style.display='none';bp.style.display='flex';}
  document.getElementById('mTitle').textContent=title;
  document.getElementById('mRatingNum').textContent=rating;
  document.getElementById('mRatingStat').textContent=rating+'/10';
  document.getElementById('mStars').innerHTML=stars(rating);
  document.getElementById('mYear').textContent=year;
  document.getElementById('mEps').textContent=eps+' eps';
  const g=genre.split(',');
  document.getElementById('mGenre1').textContent=g[0]?.trim()||'Drama';
  document.getElementById('mAbout').textContent=`"${title}" is a ${g[0]?.trim()?.toLowerCase()||'K-Drama'} series from ${year} featuring ${eps} episode${eps!=1?'s':''}, rated ${rating}/10 in your collection. Click the download button below to save this drama to your device.`;
  document.getElementById('mActions').innerHTML=
    `<a href="edit.php?id=${id}" class="maction maction-edit">✏ Edit Drama</a>
     <button class="maction maction-dl" onclick="startSmartDownload(${id}, '${title}')">⬇ Download</button>
     <a href="download.php" class="maction maction-downloads"><i class="fas fa-history"></i> Download History</a>
     <a href="delete.php?id=${id}" class="maction maction-del" onclick="return confirm('Remove this drama?')">🗑 Delete</a>`;
  document.getElementById('modal-bg').classList.add('on');
  document.body.style.overflow='hidden';
}

function closeModal(){
  document.getElementById('modal-bg').classList.remove('on');
  document.body.style.overflow='';
}

function openSov(){
  document.getElementById('sov').classList.add('on');
  document.body.style.overflow='hidden';
  setTimeout(()=>{const i=document.getElementById('sovInput');i.focus();i.select();syncClear();},60);
}
function closeSov(){document.getElementById('sov').classList.remove('on');document.body.style.overflow='';}
function sovBgClick(e){if(e.target===document.getElementById('sov'))closeSov();}
function clearSov(){const i=document.getElementById('sovInput');i.value='';i.focus();syncClear();}
function syncClear(){document.getElementById('sovClear').classList.toggle('on',document.getElementById('sovInput').value.length>0);}
document.getElementById('sovInput').addEventListener('input',syncClear);
document.addEventListener('keydown',e=>{
  if(e.key==='Escape'){closeModal();closeSov();closeQualityModal();}
  if(e.key==='/'&&!document.getElementById('sov').classList.contains('on')){
    const t=document.activeElement.tagName.toLowerCase();
    if(t!=='input'&&t!=='textarea'){e.preventDefault();openSov();}
  }
});
</script>
</body>
</html>