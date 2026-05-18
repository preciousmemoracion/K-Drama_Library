<?php include "db.php"; ?>

<?php
$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if (!empty($search)) {
    $sql = "SELECT * FROM dramas WHERE title LIKE '%$search%' OR genre LIKE '%$search%' OR episodes LIKE '%$search%' OR released_year LIKE '%$search%' OR rating LIKE '%$search%' LIMIT $start, $limit";
    $count_sql = "SELECT COUNT(*) as total FROM dramas WHERE title LIKE '%$search%' OR genre LIKE '%$search%' OR episodes LIKE '%$search%' OR released_year LIKE '%$search%' OR rating LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM dramas ORDER BY rating DESC LIMIT $start, $limit";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KDramaVerse — RiCious Collection</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════
   TOKENS
═══════════════════════════════════════════════════ */
:root{
  --r:#FF0A2E; --r2:#C0001F; --rg:rgba(255,10,46,.22); --rs:rgba(255,10,46,.08);
  --g:#F5BF00; --gs:rgba(245,191,0,.15);
  --ink:#06060A; --bg:#0E0E15; --s1:#151520; --s2:#1C1C2A; --s3:#242435;
  --t0:#F4F3FF; --t1:#9B9AB5; --t2:#4E4D65;
  --border:rgba(255,255,255,.07); --bhi:rgba(255,255,255,.13);
  --glass:rgba(14,14,21,.78);
  --ease:cubic-bezier(.22,1,.36,1);
  --spring:cubic-bezier(.34,1.56,.64,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--ink);
  color:var(--t0);
  min-height:100vh;
  overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}

/* ── Noise texture ── */
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:9999;opacity:.03;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  background-size:200px;
}

/* ════════════════════════════════════════════════
   NAVBAR
════════════════════════════════════════════════ */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:66px;padding:0 5%;
  display:flex;align-items:center;justify-content:space-between;
  transition:all .4s var(--ease);
}
.nav::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,rgba(6,6,10,.96) 0%,transparent 100%);
  z-index:-1;transition:opacity .4s;
}
.nav.scrolled{
  background:var(--glass);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
}
.nav.scrolled::before{opacity:0}

.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-icon{
  width:34px;height:34px;border-radius:8px;
  background:var(--r);
  display:flex;align-items:center;justify-content:center;
  font-size:16px;color:#fff;font-weight:900;
  font-family:'Playfair Display',serif;
  letter-spacing:-1px;
  box-shadow:0 4px 16px var(--rg);
  flex-shrink:0;
}
.nav-logo-text{
  font-family:'Playfair Display',serif;
  font-size:18px;font-weight:700;
  letter-spacing:.5px;color:var(--t0);
}
.nav-logo-text span{color:var(--r)}

.nav-links{display:flex;gap:4px}
.nav-links a{
  color:var(--t1);text-decoration:none;font-size:13px;font-weight:500;
  padding:6px 14px;border-radius:6px;
  transition:all .2s;letter-spacing:.2px;
}
.nav-links a:hover{color:var(--t0);background:rgba(255,255,255,.06)}
.nav-links a.active{color:var(--t0)}

.nav-right{display:flex;align-items:center;gap:8px}

.nav-search-btn{
  display:flex;align-items:center;gap:7px;
  background:var(--s1);border:1px solid var(--bhi);
  color:var(--t1);padding:7px 14px;border-radius:8px;
  font-size:12.5px;font-family:'Plus Jakarta Sans',sans-serif;
  cursor:pointer;transition:all .2s;
}
.nav-search-btn:hover{background:var(--s2);color:var(--t0);border-color:rgba(255,255,255,.2)}
.nav-search-btn .kk{
  font-size:10px;padding:1px 5px;background:var(--s3);
  border:1px solid var(--bhi);border-radius:3px;color:var(--t2);
  font-family:monospace;
}

.nav-add{
  display:inline-flex;align-items:center;gap:7px;
  background:var(--r);color:#fff;padding:8px 18px;
  border-radius:8px;font-size:13px;font-weight:700;
  text-decoration:none;border:none;cursor:pointer;
  font-family:'Plus Jakarta Sans',sans-serif;letter-spacing:.3px;
  transition:all .2s;box-shadow:0 4px 20px var(--rg);
}
.nav-add:hover{background:var(--r2);transform:translateY(-1px);box-shadow:0 8px 28px rgba(255,10,46,.35)}

/* ════════════════════════════════════════════════
   SEARCH OVERLAY
════════════════════════════════════════════════ */
.sov{
  display:none;position:fixed;inset:0;z-index:6000;
  background:rgba(6,6,10,.94);
  backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  align-items:flex-start;justify-content:center;
  padding-top:100px;
}
.sov.on{display:flex}
.sov-inner{
  width:100%;max-width:620px;padding:0 20px;
  animation:sovIn .28s var(--spring);
}
@keyframes sovIn{from{opacity:0;transform:translateY(-20px) scale(.96)}to{opacity:1;transform:none}}

.sov-wrap{
  display:flex;align-items:center;
  background:var(--s2);
  border:1.5px solid var(--bhi);border-radius:999px;
  overflow:hidden;transition:border-color .2s,box-shadow .2s;
}
.sov-wrap:focus-within{
  border-color:var(--r);
  box-shadow:0 0 0 4px var(--rg),0 20px 60px rgba(0,0,0,.7);
}
.sov-icon{padding:0 18px;font-size:16px;color:var(--t2);pointer-events:none;flex-shrink:0}
.sov-input{
  flex:1;background:none;border:none;outline:none;
  color:var(--t0);font-size:17px;
  font-family:'Plus Jakarta Sans',sans-serif;font-weight:400;
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
.sov-clear:hover{background:var(--bhi)}
.sov-submit{
  background:var(--r);border:none;color:#fff;
  padding:8px 20px;border-radius:999px;
  font-size:12.5px;font-weight:700;font-family:'Plus Jakarta Sans',sans-serif;
  cursor:pointer;transition:background .2s;letter-spacing:.4px;
}
.sov-submit:hover{background:var(--r2)}

.sov-chips{display:flex;align-items:center;gap:8px;margin-top:18px;flex-wrap:wrap}
.sov-chip-label{font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:var(--t2);font-weight:700}
.sov-chip{
  padding:5px 14px;background:var(--s2);border:1px solid var(--border);
  border-radius:999px;color:var(--t1);font-size:12px;cursor:pointer;
  transition:all .2s;font-family:'Plus Jakarta Sans',sans-serif;text-decoration:none;
}
.sov-chip:hover{background:var(--s3);border-color:var(--bhi);color:var(--t0)}
.sov-chip.on{background:var(--rs);border-color:rgba(255,10,46,.35);color:#ff6070}
.sov-esc{text-align:center;margin-top:20px;font-size:11.5px;color:var(--t2)}
.sov-esc kbd{
  padding:2px 8px;background:var(--s2);
  border:1px solid var(--bhi);border-radius:4px;font-family:monospace;font-size:11px;
}

/* ════════════════════════════════════════════════
   HERO
════════════════════════════════════════════════ */
.hero{
  position:relative;height:100vh;min-height:600px;
  overflow:hidden;display:flex;align-items:flex-end;
}
.hero-bg{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center 20%;
  filter:brightness(.5) saturate(1.15);
  transform:scale(1.08);
  animation:hzoom 14s var(--ease) forwards;
  will-change:transform;
}
@keyframes hzoom{to{transform:scale(1)}}
.hero-placeholder{
  position:absolute;inset:0;
  background:radial-gradient(ellipse at 65% 25%,#1a0833 0%,#06060a 65%);
  display:flex;align-items:center;justify-content:center;font-size:140px;opacity:.4;
}
/* Cinematic letter-box bars */
.hero-letterbox-top,.hero-letterbox-bottom{
  position:absolute;left:0;right:0;z-index:2;pointer-events:none;
}
.hero-letterbox-top{top:0;height:56px;background:var(--ink)}
.hero-letterbox-bottom{bottom:0;height:56px;background:var(--ink)}

/* gradient layers */
.hero-grad{
  position:absolute;inset:0;z-index:1;
  background:
    linear-gradient(to bottom,
      rgba(6,6,10,.8)  0%,
      rgba(6,6,10,0)   18%,
      rgba(6,6,10,0)   42%,
      rgba(6,6,10,.72) 68%,
      rgba(6,6,10,.98) 88%,
      var(--ink)       100%),
    linear-gradient(105deg,
      rgba(6,6,10,.98) 0%,
      rgba(6,6,10,.62) 38%,
      rgba(6,6,10,0)   62%);
}

/* Floating particles */
.particles{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.p{
  position:absolute;border-radius:50%;
  background:rgba(255,255,255,.55);
  animation:pfloat linear infinite;
}
@keyframes pfloat{
  0%  {transform:translateY(100vh) scale(0);opacity:0}
  10% {opacity:.6}
  90% {opacity:.3}
  100%{transform:translateY(-10vh) scale(1);opacity:0}
}

.hero-content{
  position:relative;z-index:3;
  padding:0 5% 96px;
  max-width:620px;
  animation:hcIn .9s var(--ease) .15s both;
}
@keyframes hcIn{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:none}}

/* Eyebrow */
.hero-eyebrow{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.hero-badge-top{
  display:inline-flex;align-items:center;gap:6px;
  background:var(--r);color:#fff;
  padding:5px 14px;border-radius:4px;
  font-size:10px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;
  box-shadow:0 4px 16px var(--rg);
}
.hero-tag{
  background:rgba(255,255,255,.09);
  border:1px solid rgba(255,255,255,.17);
  color:rgba(255,255,255,.75);
  padding:4px 12px;border-radius:4px;
  font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;
  backdrop-filter:blur(6px);
}

/* Title */
.hero-title{
  font-family:'Playfair Display',serif;
  font-size:clamp(48px,8.5vw,100px);
  font-weight:900;line-height:.93;
  letter-spacing:-2px;
  text-shadow:0 2px 40px rgba(0,0,0,.7);
  margin-bottom:20px;
}
.hero-title-line2{
  font-style:italic;color:var(--r);
  display:block;
}

.hero-meta{display:flex;align-items:center;gap:8px;margin-bottom:22px;flex-wrap:wrap}
.hero-rating{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--gs);border:1px solid rgba(245,191,0,.22);
  color:var(--g);padding:5px 14px;border-radius:5px;
  font-size:13px;font-weight:800;
}
.hero-sep{color:var(--t2);font-size:12px}
.hero-minfo{font-size:13px;color:var(--t1);font-weight:400}

.hero-desc{
  font-size:14.5px;line-height:1.72;
  color:rgba(244,243,255,.6);max-width:450px;
  margin-bottom:30px;font-weight:300;
}

.hero-cta{display:flex;gap:10px;flex-wrap:wrap}
.btn-main{
  display:inline-flex;align-items:center;gap:9px;
  background:#fff;color:#06060A;
  padding:14px 30px;border-radius:8px;
  font-size:14px;font-weight:800;letter-spacing:.3px;
  font-family:'Plus Jakarta Sans',sans-serif;
  text-decoration:none;border:none;cursor:pointer;
  transition:all .22s;
}
.btn-main:hover{background:rgba(255,255,255,.88);transform:translateY(-2px);box-shadow:0 10px 32px rgba(255,255,255,.12)}
.btn-ghost{
  display:inline-flex;align-items:center;gap:9px;
  background:rgba(255,255,255,.08);color:#fff;
  padding:14px 28px;border-radius:8px;
  font-size:14px;font-weight:600;
  font-family:'Plus Jakarta Sans',sans-serif;
  text-decoration:none;border:1px solid rgba(255,255,255,.16);
  cursor:pointer;backdrop-filter:blur(8px);
  transition:all .22s;
}
.btn-ghost:hover{background:rgba(255,255,255,.14);transform:translateY(-2px)}

/* Hero scroll indicator */
.hero-scroll{
  position:absolute;bottom:68px;left:50%;transform:translateX(-50%);
  z-index:3;display:flex;flex-direction:column;align-items:center;gap:6px;
  font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--t2);
  animation:bob 2.4s ease-in-out infinite;
}
@keyframes bob{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(-6px)}}
.scroll-track{width:1px;height:36px;background:linear-gradient(to bottom,transparent,var(--t2))}

/* ════════════════════════════════════════════════
   STATS RIBBON
════════════════════════════════════════════════ */
.stats-ribbon{
  display:grid;grid-template-columns:repeat(4,1fr);
  background:var(--s1);
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  position:relative;z-index:5;
}
.stat-block{
  padding:20px 0;text-align:center;
  border-right:1px solid var(--border);
  position:relative;overflow:hidden;
  transition:background .25s;
  cursor:default;
}
.stat-block:last-child{border-right:none}
.stat-block::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,var(--rs) 0%,transparent 60%);
  opacity:0;transition:opacity .3s;
}
.stat-block:hover{background:var(--s2)}
.stat-block:hover::before{opacity:1}
.stat-n{
  font-family:'Playfair Display',serif;
  font-size:32px;font-weight:900;
  color:var(--r);line-height:1;margin-bottom:4px;
  letter-spacing:-1px;
}
.stat-l{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--t2);font-weight:700}

/* ════════════════════════════════════════════════
   MAIN
════════════════════════════════════════════════ */
.main{
  padding:0 5% 80px;
  background:linear-gradient(to bottom,var(--ink) 0%,var(--bg) 100%);
}

/* ── Section header ── */
.sec-head{
  display:flex;align-items:flex-end;justify-content:space-between;
  padding:52px 0 24px;
  border-bottom:1px solid var(--border);
  margin-bottom:32px;
}
.sec-head-left{}
.sec-eyebrow{
  font-size:10px;letter-spacing:2px;text-transform:uppercase;
  color:var(--r);font-weight:700;margin-bottom:8px;
}
.sec-title{
  font-family:'Playfair Display',serif;
  font-size:38px;font-weight:900;letter-spacing:-1px;line-height:1;
}
.sec-sub{font-size:12.5px;color:var(--t2);margin-top:6px;letter-spacing:.2px}
.sec-count{
  font-size:12px;color:var(--t1);
  background:var(--s2);border:1px solid var(--border);
  padding:6px 16px;border-radius:999px;
  font-weight:500;margin-bottom:3px;
}

/* ── Search result banner ── */
.srb{
  margin:88px 0 32px;
  padding:0;
  position:relative;overflow:hidden;
  border-radius:14px;
}
.srb-inner{
  background:var(--s1);border:1px solid var(--bhi);
  border-radius:14px;padding:26px 30px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
}
.srb::before{
  content:'';position:absolute;
  top:-60px;left:-60px;width:200px;height:200px;
  background:radial-gradient(circle,rgba(255,10,46,.12),transparent 70%);
  pointer-events:none;
}
.srb-h2{
  font-family:'Playfair Display',serif;
  font-size:22px;font-weight:700;letter-spacing:-.5px;
  color:var(--t1);margin-bottom:5px;
}
.srb-h2 strong{color:var(--t0)}
.srb-p{font-size:12px;color:var(--t2)}
.srb-btns{display:flex;gap:8px}
.srb-btn{
  padding:8px 18px;border-radius:8px;font-size:12.5px;
  font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;
  transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px;
}
.srb-btn-a{background:var(--s3);border:1px solid var(--bhi);color:var(--t1)}
.srb-btn-a:hover{background:rgba(255,255,255,.08);color:var(--t0)}
.srb-btn-b{background:transparent;border:1px solid var(--border);color:var(--t2)}
.srb-btn-b:hover{border-color:var(--bhi);color:var(--t1)}

/* ════════════════════════════════════════════════
   DRAMA GRID
════════════════════════════════════════════════ */
.grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(162px,1fr));
  gap:16px;
}

/* ════════════════════════════════════════════════
   DRAMA CARD
════════════════════════════════════════════════ */
.card{
  position:relative;cursor:pointer;
  border-radius:10px;overflow:visible;
  transition:transform .38s var(--ease),z-index 0s .38s;
  animation:cin .5s var(--ease) both;
}
@keyframes cin{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.card:nth-child(1){animation-delay:.03s}.card:nth-child(2){animation-delay:.06s}
.card:nth-child(3){animation-delay:.09s}.card:nth-child(4){animation-delay:.12s}
.card:nth-child(5){animation-delay:.15s}.card:nth-child(6){animation-delay:.18s}
.card:nth-child(7){animation-delay:.21s}.card:nth-child(8){animation-delay:.24s}
.card:nth-child(9){animation-delay:.27s}.card:nth-child(10){animation-delay:.30s}
.card:nth-child(11){animation-delay:.33s}.card:nth-child(12){animation-delay:.36s}

.card:hover{
  transform:scale(1.1) translateY(-6px);z-index:200;
  transition:transform .38s var(--ease),z-index 0s;
}
.card:hover .cpw{box-shadow:0 24px 60px rgba(0,0,0,.9),0 0 0 1px var(--bhi),0 0 30px var(--rg)}

.cpw{
  position:relative;aspect-ratio:2/3;border-radius:10px;overflow:hidden;
  background:var(--s2);
  transition:box-shadow .38s;
}

/* Shimmer on load */
.cpw::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.04) 50%,transparent 60%);
  transform:translateX(-100%);
  transition:transform .7s var(--ease);
  pointer-events:none;
}
.card:hover .cpw::after{transform:translateX(100%)}

.cposter{
  width:100%;height:100%;object-fit:cover;display:block;
  transition:filter .38s,transform .5s var(--ease);
}
.card:hover .cposter{filter:brightness(.28) saturate(.7);transform:scale(1.06)}
.cph{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  font-size:56px;background:linear-gradient(145deg,var(--s2),var(--s3));
}

/* Always-visible badges */
.cbadge-rating{
  position:absolute;top:9px;right:9px;z-index:2;
  background:rgba(6,6,10,.88);color:var(--g);
  font-size:10.5px;font-weight:800;padding:3px 9px;border-radius:5px;
  border:1px solid rgba(245,191,0,.14);
  backdrop-filter:blur(6px);display:flex;align-items:center;gap:2px;
  transition:opacity .3s,transform .3s;
}
.card:hover .cbadge-rating{opacity:0;transform:scale(.8)}

.cbadge-genre{
  position:absolute;top:9px;left:9px;z-index:2;
  background:var(--r);color:#fff;
  font-size:8px;font-weight:800;padding:3px 9px;border-radius:4px;
  letter-spacing:.8px;text-transform:uppercase;
  opacity:0;transform:translateX(-4px);
  transition:opacity .3s,transform .3s;pointer-events:none;
  max-width:86px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
  box-shadow:0 2px 10px var(--rg);
}
.card:hover .cbadge-genre{opacity:1;transform:none}

/* Overlay */
.cover{
  position:absolute;inset:0;
  background:linear-gradient(to top,
    rgba(6,6,10,1)  0%,
    rgba(6,6,10,.82) 45%,
    rgba(6,6,10,0)  100%);
  opacity:0;transition:opacity .3s;
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:14px;border-radius:10px;
}
.card:hover .cover{opacity:1}

.cov-title{font-size:12px;font-weight:700;color:#fff;line-height:1.3;margin-bottom:5px}
.cov-meta{display:flex;align-items:center;gap:5px;flex-wrap:wrap;margin-bottom:10px}
.cov-rating{color:var(--g);font-size:10px;font-weight:800;display:flex;align-items:center;gap:2px}
.cov-yr,.cov-eps{color:var(--t1);font-size:9.5px}

.cov-btns{display:flex;gap:6px;onclick:"event.stopPropagation()"}
.cov-btn{
  flex:1;padding:7px 6px;border-radius:6px;
  font-size:10px;font-weight:700;font-family:'Plus Jakarta Sans',sans-serif;
  cursor:pointer;text-decoration:none;text-align:center;
  display:flex;align-items:center;justify-content:center;gap:3px;
  letter-spacing:.4px;transition:opacity .2s,transform .12s;border:none;
}
.cov-btn:hover{opacity:.82;transform:scale(.97)}
.cov-edit{background:rgba(255,255,255,.13);color:#fff;border:1px solid rgba(255,255,255,.2)}
.cov-del{background:var(--r);color:#fff;box-shadow:0 2px 10px var(--rg)}

/* Card label below */
.clabel{
  padding:8px 2px 3px;font-size:12px;font-weight:500;color:var(--t1);
  line-height:1.3;transition:color .2s;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.card:hover .clabel{color:var(--t0)}

/* ── Empty ── */
.empty{grid-column:1/-1;text-align:center;padding:100px 20px}
.empty-i{font-size:80px;opacity:.35;margin-bottom:18px}
.empty-t{
  font-family:'Playfair Display',serif;
  font-size:28px;font-weight:700;color:var(--t1);margin-bottom:8px;
}
.empty-s{font-size:13px;color:var(--t2)}

/* ── Pagination ── */
.pgn{display:flex;justify-content:center;align-items:center;gap:5px;margin-top:60px;flex-wrap:wrap}
.pgi{
  padding:9px 17px;border-radius:8px;text-decoration:none;
  font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;
  transition:all .2s;border:1px solid var(--border);cursor:pointer;display:inline-flex;align-items:center;
}
.pgi-n{background:transparent;color:var(--t1)}
.pgi-n:hover{background:var(--s2);color:var(--t0);border-color:var(--bhi)}
.pgi-a{background:var(--r);color:#fff;border-color:var(--r);box-shadow:0 4px 16px var(--rg)}
.pgi-nav{background:var(--s1);color:var(--t1)}
.pgi-nav:hover{background:var(--s2);color:var(--t0)}
.pgi-off{opacity:.2;pointer-events:none}

/* ════════════════════════════════════════════════
   MODAL
════════════════════════════════════════════════ */
.modal-bg{
  display:none;position:fixed;inset:0;z-index:4000;
  background:rgba(6,6,10,.93);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  justify-content:center;align-items:center;padding:20px;
}
.modal-bg.on{display:flex}

.modal{
  position:relative;background:var(--s1);
  border:1px solid var(--bhi);border-radius:20px;
  width:100%;max-width:820px;
  overflow:hidden;
  animation:mIn .36s var(--spring);
  box-shadow:0 60px 120px rgba(0,0,0,.95),0 0 80px rgba(255,10,46,.06);
  max-height:92vh;overflow-y:auto;scrollbar-width:none;
}
.modal::-webkit-scrollbar{display:none}
@keyframes mIn{from{transform:scale(.86) translateY(32px);opacity:0}to{transform:none;opacity:1}}

/* Cinematic top banner */
.mbanner{
  position:relative;height:300px;overflow:hidden;flex-shrink:0;
}
.mbanner-img{
  width:100%;height:100%;object-fit:cover;object-position:center 18%;
  display:block;filter:brightness(.45) saturate(1.1);
}
.mbanner-ph{
  width:100%;height:100%;
  background:radial-gradient(ellipse at 60% 40%,#1a0833,#06060a);
  display:flex;align-items:center;justify-content:center;font-size:90px;opacity:.4;
}
.mbanner-grad{
  position:absolute;inset:0;
  background:
    linear-gradient(to bottom,transparent 25%,rgba(21,21,32,.8) 70%,var(--s1) 100%),
    linear-gradient(to right,rgba(21,21,32,.7) 0%,transparent 50%);
}

/* Floating title over banner */
.mbanner-title-wrap{
  position:absolute;bottom:0;left:0;right:0;
  padding:0 32px 28px;z-index:2;
}
.mbanner-eyebrow{
  display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;
}
.mbanner-badge{
  background:var(--r);color:#fff;
  padding:4px 12px;border-radius:4px;
  font-size:9.5px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;
  box-shadow:0 3px 12px var(--rg);
}
.mtag{
  background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);
  color:rgba(255,255,255,.75);padding:3px 11px;border-radius:4px;
  font-size:9.5px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;
}
.mbanner-title{
  font-family:'Playfair Display',serif;
  font-size:clamp(24px,4vw,40px);font-weight:900;
  letter-spacing:-1px;line-height:1.0;
  text-shadow:0 2px 20px rgba(0,0,0,.6);
}

/* Rating corner pill */
.mrating-corner{
  position:absolute;top:18px;right:18px;z-index:3;
  background:rgba(6,6,10,.88);
  border:1px solid rgba(245,191,0,.2);
  border-radius:12px;padding:10px 16px;
  backdrop-filter:blur(10px);
  display:flex;align-items:center;gap:10px;
}
.mrating-num{
  font-family:'Playfair Display',serif;
  font-size:30px;font-weight:900;color:var(--g);line-height:1;
}
.mrating-right{}
.mstars{display:flex;gap:2px;margin-bottom:3px}
.mstar{font-size:11px}
.mstar.on{color:var(--g)}
.mstar.off{color:var(--t2)}
.mrating-label{font-size:9px;text-transform:uppercase;letter-spacing:1px;color:var(--t2)}

/* Modal body */
.mbody{padding:24px 32px 32px}

.mstats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:24px}
.mstat{
  background:var(--s2);border:1px solid var(--border);
  border-radius:10px;padding:14px 18px;
  display:flex;align-items:center;gap:12px;
  transition:border-color .2s;
}
.mstat:hover{border-color:var(--bhi)}
.mstat-icon{font-size:22px;flex-shrink:0}
.mstat-label{font-size:9.5px;text-transform:uppercase;letter-spacing:1px;color:var(--t2);font-weight:700;margin-bottom:3px}
.mstat-val{font-size:17px;font-weight:700;color:var(--t0)}

.mdivider{height:1px;background:var(--border);margin-bottom:18px}
.mlabel{font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--t2);font-weight:700;margin-bottom:10px}
.mabout{font-size:14px;line-height:1.72;color:var(--t1);font-weight:300}

.mactions{display:flex;gap:10px;margin-top:26px}
.maction{
  flex:1;padding:14px 18px;border-radius:10px;
  font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:700;
  text-align:center;text-decoration:none;cursor:pointer;border:none;
  transition:all .2s;display:flex;align-items:center;justify-content:center;gap:7px;
  letter-spacing:.3px;
}
.maction:hover{opacity:.85;transform:translateY(-1px)}
.maction-edit{background:#1d5aef;color:#fff}
.maction-del{background:var(--r);color:#fff;box-shadow:0 4px 18px var(--rg)}

.mclose{
  position:absolute;top:16px;left:16px;
  width:38px;height:38px;border-radius:50%;
  background:rgba(6,6,10,.8);border:1px solid var(--bhi);
  color:var(--t1);font-size:20px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  z-index:10;transition:all .2s;backdrop-filter:blur(8px);line-height:1;
}
.mclose:hover{background:var(--r);color:#fff;border-color:var(--r)}

/* ════════════════════════════════════════════════
   SCROLLBAR
════════════════════════════════════════════════ */
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-track{background:var(--ink)}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px}
::-webkit-scrollbar-thumb:hover{background:var(--bhi)}

/* ════════════════════════════════════════════════
   FOOTER
════════════════════════════════════════════════ */
footer{
  text-align:center;padding:40px 5%;
  border-top:1px solid var(--border);
  background:var(--s1);
  display:flex;flex-direction:column;align-items:center;gap:12px;
}
.footer-logo{
  font-family:'Playfair Display',serif;
  font-size:20px;font-weight:700;color:var(--t1);letter-spacing:.5px;
}
.footer-logo span{color:var(--r);font-style:italic}
.footer-copy{font-size:11.5px;color:var(--t2);letter-spacing:.3px}

/* ════════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════════ */
@media(max-width:640px){
  .nav-links{display:none}
  .hero-title{font-size:clamp(44px,15vw,68px)}
  .stats-ribbon{grid-template-columns:repeat(2,1fr)}
  .grid{grid-template-columns:repeat(auto-fill,minmax(132px,1fr))}
  .mstats{grid-template-columns:1fr 1fr}
  .mbody{padding:18px 20px 24px}
  .mbanner-title-wrap{padding:0 20px 22px}
  .mbanner{height:230px}
}
</style>
</head>
<body>

<!-- ── NAVBAR ─────────────────────────────────────────────── -->
<nav class="nav" id="nav">
  <a href="?" class="nav-brand">
    <div class="nav-logo-icon">K</div>
    <span class="nav-logo-text">Drama<span>Verse</span></span>
  </a>
  <div class="nav-links">
    <a href="?" class="active">Home</a>
    <a href="?sort=rating">Top Rated</a>
    <a href="?search=romance">Romance</a>
    <a href="?search=thriller">Thriller</a>
  </div>
  <div class="nav-right">
    <button class="nav-search-btn" onclick="openSov()">
      🔍 Search <span class="kk">/</span>
    </button>
    <a href="add.php" class="nav-add">＋ Add Drama</a>
  </div>
</nav>

<!-- ── SEARCH OVERLAY ─────────────────────────────────────── -->
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
<!-- ── HERO ───────────────────────────────────────────────── -->
<section class="hero">
  <?php if($featured):
    $img = !empty($featured['image']) ? basename($featured['image']) : 'default.jpg';
    $imgP = "img/".$img;
    if(!file_exists(__DIR__."/img/".$img)) $imgP = "img/default.jpg";
  ?>
    <?php if(file_exists(__DIR__."/".$imgP)): ?>
      <img class="hero-bg" src="<?php echo htmlspecialchars($imgP);?>" alt="">
    <?php else: ?>
      <div class="hero-placeholder">🎭</div>
    <?php endif; ?>

    <!-- Particles -->
    <div class="particles" id="particles"></div>
    <div class="hero-grad"></div>
    <div class="hero-letterbox-top"></div>
    <div class="hero-letterbox-bottom"></div>

    <div class="hero-content">
      <div class="hero-eyebrow">
        <span class="hero-badge-top">★ Top Rated</span>
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
        <?php if($line2): ?><span class="hero-title-line2"><?php echo htmlspecialchars($line2);?></span><?php endif; ?>
      </h1>
      <div class="hero-meta">
        <span class="hero-rating">⭐ <?php echo $featured['rating'];?>/10</span>
        <span class="hero-sep">·</span>
        <span class="hero-minfo"><?php echo $featured['released_year'];?></span>
        <span class="hero-sep">·</span>
        <span class="hero-minfo"><?php echo $featured['episodes'];?> Episodes</span>
      </div>
      <p class="hero-desc">The crown jewel of your collection — this drama commands your attention from the very first scene.</p>
      <div class="hero-cta">
        <button class="btn-main"
          onclick="openModal(
            '<?php echo addslashes(htmlspecialchars($imgP));?>',
            '<?php echo addslashes(htmlspecialchars($featured['title']));?>',
            '<?php echo addslashes(htmlspecialchars($featured['genre']));?>',
            '<?php echo $featured['rating'];?>',
            '<?php echo $featured['episodes'];?>',
            '<?php echo $featured['released_year'];?>',
            '<?php echo $featured['id'];?>')">
          ▶ View Details
        </button>
        <a href="edit.php?id=<?php echo $featured['id'];?>" class="btn-ghost">✏ Edit Entry</a>
      </div>
    </div>

    <div class="hero-scroll">
      <span>Scroll</span>
      <div class="scroll-track"></div>
    </div>

  <?php else: ?>
    <div class="hero-placeholder">🎭</div>
    <div class="hero-grad"></div>
    <div class="hero-letterbox-top"></div>
    <div class="hero-letterbox-bottom"></div>
    <div class="hero-content">
      <h1 class="hero-title">Your Drama<br><span class="hero-title-line2">Library</span></h1>
      <p class="hero-desc">Start building your personal K-Drama collection today.</p>
      <div class="hero-cta"><a href="add.php" class="btn-main">＋ Add First Drama</a></div>
    </div>
  <?php endif; ?>
</section>

<!-- ── STATS RIBBON ───────────────────────────────────────── -->
<div class="stats-ribbon">
  <div class="stat-block">
    <div class="stat-n"><?php echo $total_row['total'];?></div>
    <div class="stat-l">Titles</div>
  </div>
  <div class="stat-block">
    <div class="stat-n"><?php echo $stats['avg_r']?:'—';?></div>
    <div class="stat-l">Avg Rating</div>
  </div>
  <?php
    $gr = $conn->query("SELECT COUNT(DISTINCT genre) as c FROM dramas");
    $gc = $gr ? $gr->fetch_assoc()['c'] : '—';
    $lr = $conn->query("SELECT MAX(released_year) as y FROM dramas");
    $ly = $lr ? $lr->fetch_assoc()['y'] : '—';
  ?>
  <div class="stat-block">
    <div class="stat-n"><?php echo $gc;?></div>
    <div class="stat-l">Genres</div>
  </div>
  <div class="stat-block">
    <div class="stat-n"><?php echo $ly?:'—';?></div>
    <div class="stat-l">Latest Year</div>
  </div>
</div>
<?php endif; ?>

<!-- ── MAIN ───────────────────────────────────────────────── -->
<div class="main">

  <?php if(!empty($search)): ?>
  <div class="srb">
    <div class="srb-inner">
      <div>
        <div class="srb-h2">Results for <strong>"<?php echo htmlspecialchars($search);?>"</strong></div>
        <div class="srb-p"><?php echo $total_row['total'];?> drama<?php echo $total_row['total']!=1?'s':'';?> found</div>
      </div>
      <div class="srb-btns">
        <button class="srb-btn srb-btn-a" onclick="openSov()">🔍 Search again</button>
        <a href="?" class="srb-btn srb-btn-b">✕ Clear</a>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="sec-head">
    <div class="sec-head-left">
      <div class="sec-eyebrow">Your Collection</div>
      <div class="sec-title">All Dramas</div>
      <div class="sec-sub">Ranked by rating · <?php echo $total_row['total'];?> titles</div>
    </div>
    <div class="sec-count"><?php echo $total_row['total'];?> titles</div>
  </div>
  <?php endif; ?>

  <!-- GRID -->
  <div class="grid">
  <?php
  if($result && $result->num_rows>0){
    while($row=$result->fetch_assoc()){
      $img=!empty($row['image'])?basename($row['image']):'default.jpg';
      $imgP="img/".$img;
      if(!file_exists(__DIR__."/img/".$img)) $imgP="img/default.jpg";
      $hasImg=file_exists(__DIR__."/".$imgP);
      $tj=addslashes(htmlspecialchars($row['title']));
      $gj=addslashes(htmlspecialchars($row['genre']));
      $ij=addslashes(htmlspecialchars($imgP));
      $fg=trim(explode(',',$row['genre'])[0]);
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
        <div class="cover">
          <div class="cov-title"><?php echo htmlspecialchars($row['title']);?></div>
          <div class="cov-meta">
            <span class="cov-rating">⭐ <?php echo $row['rating'];?></span>
            <span class="cov-yr"><?php echo $row['released_year'];?></span>
            <span class="cov-eps">· <?php echo $row['episodes'];?> eps</span>
          </div>
          <div class="cov-btns" onclick="event.stopPropagation()">
            <a href="edit.php?id=<?php echo $row['id'];?>" class="cov-btn cov-edit">✏ Edit</a>
            <a href="delete.php?id=<?php echo $row['id'];?>" class="cov-btn cov-del" onclick="return confirm('Remove this drama?')">🗑 Delete</a>
          </div>
        </div>
      </div>
      <div class="clabel"><?php echo htmlspecialchars($row['title']);?></div>
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
  ?>
  <div class="pgn">
    <?php
    echo"<a class='pgi pgi-nav".($page==1?" pgi-off":"")."' href='?page=1$q'>«</a>";
    if($page>1) echo"<a class='pgi pgi-nav' href='?page=".($page-1)."$q'>‹</a>";
    $sp=max(1,$page-2);$ep=min($total_pages,$page+2);
    if($sp>1) echo"<span class='pgi pgi-n' style='opacity:.3'>…</span>";
    for($i=$sp;$i<=$ep;$i++){
      if($i==$page) echo"<span class='pgi pgi-a'>$i</span>";
      else echo"<a class='pgi pgi-n' href='?page=$i$q'>$i</a>";
    }
    if($ep<$total_pages) echo"<span class='pgi pgi-n' style='opacity:.3'>…</span>";
    if($page<$total_pages) echo"<a class='pgi pgi-nav' href='?page=".($page+1)."$q'>›</a>";
    echo"<a class='pgi pgi-nav".($page==$total_pages?" pgi-off":"")."' href='?page=$total_pages$q'>»</a>";
    ?>
  </div>
  <?php endif; ?>

</div><!-- /main -->

<!-- ── FOOTER ─────────────────────────────────────────────── -->
<footer>
  <div class="footer-logo">Drama<span>Verse</span></div>
  <div class="footer-copy">© <?php echo date('Y');?> RiCious KDramaVerse — Your Personal Drama Collection</div>
</footer>

<!-- ── MODAL ──────────────────────────────────────────────── -->
<div class="modal-bg" id="modal-bg" onclick="closeModal()">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="mclose" onclick="closeModal()">×</button>

    <div class="mbanner">
      <img id="mbannerImg" src="" alt="" class="mbanner-img">
      <div id="mbannerPh" class="mbanner-ph" style="display:none">🎭</div>
      <div class="mbanner-grad"></div>
      <div class="mrating-corner">
        <div class="mrating-num" id="mRatingNum"></div>
        <div class="mrating-right">
          <div class="mstars" id="mStars"></div>
          <div class="mrating-label">out of 10</div>
        </div>
      </div>
      <div class="mbanner-title-wrap">
        <div class="mbanner-eyebrow">
          <span class="mbanner-badge">★ Drama</span>
          <span class="mtag" id="mGenre1"></span>
        </div>
        <div class="mbanner-title" id="mTitle"></div>
      </div>
    </div>

    <div class="mbody">
      <div class="mstats">
        <div class="mstat">
          <div class="mstat-icon">📅</div>
          <div><div class="mstat-label">Released</div><div class="mstat-val" id="mYear"></div></div>
        </div>
        <div class="mstat">
          <div class="mstat-icon">🎬</div>
          <div><div class="mstat-label">Episodes</div><div class="mstat-val" id="mEps"></div></div>
        </div>
      </div>
      <div class="mdivider"></div>
      <div class="mlabel">Synopsis</div>
      <p class="mabout" id="mAbout"></p>
      <div class="mactions" id="mActions"></div>
    </div>
  </div>
</div>

<script>
/* ── Floating particles ── */
(function(){
  const c=document.getElementById('particles');
  if(!c) return;
  for(let i=0;i<18;i++){
    const p=document.createElement('div');
    p.className='p';
    const s=Math.random()*3+1;
    p.style.cssText=`width:${s}px;height:${s}px;left:${Math.random()*100}%;animation-duration:${Math.random()*12+10}s;animation-delay:${Math.random()*12}s`;
    c.appendChild(p);
  }
})();

/* ── Navbar scroll ── */
window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('scrolled',scrollY>50);
},{passive:true});

/* ── Stars ── */
function stars(r){
  return Array.from({length:5},(_,i)=>`<span class="mstar ${i<Math.round(r/2)?'on':'off'}">★</span>`).join('');
}

/* ── Modal ── */
function openModal(src,title,genre,rating,eps,year,id){
  const bi=document.getElementById('mbannerImg');
  const bp=document.getElementById('mbannerPh');
  if(src&&src!=='img/default.jpg'){bi.src=src;bi.style.display='block';bp.style.display='none';}
  else{bi.style.display='none';bp.style.display='flex';}
  document.getElementById('mTitle').textContent=title;
  document.getElementById('mRatingNum').textContent=rating;
  document.getElementById('mStars').innerHTML=stars(rating);
  document.getElementById('mYear').textContent=year;
  document.getElementById('mEps').textContent=eps;
  const g=genre.split(',');
  document.getElementById('mGenre1').textContent=g[0]?.trim()||'Drama';
  document.getElementById('mAbout').textContent=`"${title}" is a ${g[0]?.trim()?.toLowerCase()||'K-Drama'} series from ${year} with ${eps} episode${eps!=1?'s':''}, rated ${rating}/10 in your collection.`;
  document.getElementById('mActions').innerHTML=
    `<a href="edit.php?id=${id}" class="maction maction-edit">✏ Edit Drama</a>
     <a href="delete.php?id=${id}" class="maction maction-del" onclick="return confirm('Remove this drama?')">🗑 Delete</a>`;
  document.getElementById('modal-bg').classList.add('on');
  document.body.style.overflow='hidden';
}
function closeModal(){
  document.getElementById('modal-bg').classList.remove('on');
  document.body.style.overflow='';
}

/* ── Search overlay ── */
function openSov(){
  document.getElementById('sov').classList.add('on');
  document.body.style.overflow='hidden';
  setTimeout(()=>{const i=document.getElementById('sovInput');i.focus();i.select();syncSovClear();},60);
}
function closeSov(){
  document.getElementById('sov').classList.remove('on');
  document.body.style.overflow='';
}
function sovBgClick(e){if(e.target===document.getElementById('sov'))closeSov();}
function clearSov(){const i=document.getElementById('sovInput');i.value='';i.focus();syncSovClear();}
function syncSovClear(){document.getElementById('sovClear').classList.toggle('on',document.getElementById('sovInput').value.length>0);}
document.getElementById('sovInput').addEventListener('input',syncSovClear);

/* ── Keyboard ── */
document.addEventListener('keydown',e=>{
  if(e.key==='Escape'){closeModal();closeSov();}
  if(e.key==='/'&&!document.getElementById('sov').classList.contains('on')){
    const t=document.activeElement.tagName.toLowerCase();
    if(t!=='input'&&t!=='textarea'){e.preventDefault();openSov();}
  }
});
</script>
</body>
</html>