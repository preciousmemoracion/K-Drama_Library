<?php include "db.php"; ?>

<?php
// Set active nav for about page
$navAbout = true;

// Fetch system stats for the about page
$total_dramas = $conn->query("SELECT COUNT(*) as total FROM dramas")->fetch_assoc()['total'] ?? 0;
$total_watchlist = $conn->query("SELECT COUNT(*) as total FROM plan_to_watch")->fetch_assoc()['total'] ?? 0;
$avg_rating = $conn->query("SELECT ROUND(AVG(rating),1) as avg FROM dramas")->fetch_assoc()['avg'] ?? 0;
$unique_genres = $conn->query("SELECT COUNT(DISTINCT genre) as c FROM dramas")->fetch_assoc()['c'] ?? 0;

// Get latest drama added
$latest_drama = $conn->query("SELECT title, released_year FROM dramas ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About — KDramaVerse | The Creators</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
  --red:#E8173A;--red2:#B50F2B;--redglow:rgba(232,23,58,.25);--redsoft:rgba(232,23,58,.1);
  --gold:#F0B429;--goldglow:rgba(240,180,41,.2);--goldsoft:rgba(240,180,41,.1);
  --ink:#050508;--bg:#0A0A0F;--s1:#0F0F15;--s2:#15151D;--s3:#1C1C26;
  --t0:#FFFFFF;--t1:#A0A0B0;--t2:#6A6A7A;
  --border:rgba(255,255,255,.05);--bhi:rgba(255,255,255,.1);
  --glass:rgba(10,10,15,.75);
  --ease:cubic-bezier(.25,.46,.45,.94);
  --spring:cubic-bezier(.34,1.2,.64,1);
  --rad:16px;
  --green:#00E676;
  --cyan:#00D4FF;
  --purple:#B44CFF;
  --orange:#FF6B35;
  --pink:#FF4785;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--ink);color:var(--t0);
  min-height:100vh;overflow-x:hidden;
}

/* Custom Cursor */
.cursor-glow{
  position:fixed;
  width:400px;height:400px;
  background:radial-gradient(circle, var(--redglow) 0%, transparent 70%);
  pointer-events:none;
  z-index:9999;
  opacity:0;
  transition:opacity 0.3s;
  border-radius:50%;
}
body:hover .cursor-glow{opacity:0.6}

/* Navigation */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:70px;padding:0 5%;
  display:flex;align-items:center;justify-content:space-between;
  transition:all .4s var(--ease);
  background:transparent;
}
.nav.scrolled{
  background:var(--glass);
  backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);
}
.nav-brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.nav-logo-pill{
  height:36px;padding:0 16px;
  background:linear-gradient(135deg, var(--red), var(--purple));
  border-radius:8px;
  display:flex;align-items:center;
  font-size:14px;font-weight:700;color:#fff;
  box-shadow:0 4px 15px var(--redglow);
}
.nav-logo-name{
  font-family:'Cormorant Garamond',serif;
  font-size:22px;font-weight:700;
  background:linear-gradient(135deg, #fff, var(--t1));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}
.nav-logo-name em{color:var(--red);font-style:italic;background:none;-webkit-background-clip:unset;color:var(--red)}
.nav-links{display:flex;gap:4px}
.nav-links a{
  color:var(--t1);text-decoration:none;font-size:13px;font-weight:500;
  padding:8px 18px;border-radius:40px;transition:all .3s;
  display:flex;align-items:center;gap:8px;
  position:relative;
}
.nav-links a:hover{
  color:var(--t0);
  background:rgba(255,255,255,.06);
  transform:translateY(-1px);
}
.nav-links a.active{
  background:linear-gradient(135deg, var(--redsoft), var(--purple) 100%);
  color:var(--t0);
  border:1px solid rgba(232,23,58,.3);
}
.nav-right{display:flex;align-items:center;gap:12px}
.nav-add{
  display:inline-flex;align-items:center;gap:8px;
  background:linear-gradient(135deg, var(--red), var(--red2));
  color:#fff;
  padding:9px 22px;
  border-radius:40px;
  font-size:13px;font-weight:600;
  text-decoration:none;
  transition:all .3s var(--spring);
  box-shadow:0 4px 15px var(--redglow);
}
.nav-add:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 25px var(--redglow);
}

/* Hero Section Enhanced */
.about-hero{
  position:relative;
  min-height:85vh;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:140px 5% 80px;
  overflow:hidden;
}
.about-hero-bg{
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse at 20% 30%, rgba(232,23,58,.15) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 70%, rgba(180,76,255,.1) 0%, transparent 50%),
    radial-gradient(ellipse at 50% 50%, rgba(0,212,255,.05) 0%, transparent 60%);
  z-index:0;
}
.hero-particles{
  position:absolute;inset:0;
  overflow:hidden;
  z-index:0;
}
.hero-particle{
  position:absolute;
  width:2px;height:2px;
  background:var(--red);
  border-radius:50%;
  opacity:0;
  animation:floatParticle 8s infinite;
}
@keyframes floatParticle{
  0%{transform:translateY(100vh) scale(0);opacity:0}
  10%{opacity:0.8}
  90%{opacity:0.3}
  100%{transform:translateY(-10vh) scale(1);opacity:0}
}
.about-hero-content{
  position:relative;z-index:2;
  max-width:900px;
  animation:heroReveal 1s var(--spring) both;
}
@keyframes heroReveal{
  from{opacity:0;transform:scale(0.96) translateY(30px)}
  to{opacity:1;transform:scale(1) translateY(0)}
}
.about-badge{
  display:inline-block;
  background:linear-gradient(135deg, var(--red), var(--purple));
  color:#fff;
  padding:6px 18px;
  border-radius:40px;
  font-size:11px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  margin-bottom:25px;
  box-shadow:0 4px 20px var(--redglow);
  animation:badgePulse 2s infinite;
}
@keyframes badgePulse{
  0%,100%{box-shadow:0 4px 20px var(--redglow)}
  50%{box-shadow:0 6px 30px rgba(232,23,58,.5)}
}
.about-hero h1{
  font-family:'Cormorant Garamond',serif;
  font-size:clamp(52px,10vw,110px);
  font-weight:700;
  letter-spacing:-3px;
  line-height:0.92;
  margin-bottom:25px;
  background:linear-gradient(135deg, #fff, var(--t1));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}
.about-hero h1 em{
  background:linear-gradient(135deg, var(--red), var(--pink));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
  font-style:italic;
}
.hero-subtitle{
  font-size:18px;
  color:var(--t1);
  max-width:650px;
  margin:0 auto;
  line-height:1.6;
}
.hero-scroll-indicator{
  position:absolute;
  bottom:40px;
  left:50%;
  transform:translateX(-50%);
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:8px;
  cursor:pointer;
  z-index:3;
  animation:bounce 2s infinite;
}
@keyframes bounce{
  0%,100%{transform:translateX(-50%) translateY(0)}
  50%{transform:translateX(-50%) translateY(-8px)}
}
.scroll-text{
  font-size:10px;
  letter-spacing:2px;
  color:var(--t2);
  text-transform:uppercase;
}
.scroll-line{
  width:1px;
  height:40px;
  background:linear-gradient(to bottom, var(--red), transparent);
}

/* Main Container */
.about-container{
  max-width:1300px;
  margin:0 auto;
  padding:0 5% 80px;
}

/* Section Styles Enhanced */
.about-section{
  margin-bottom:90px;
  opacity:0;
  transform:translateY(30px);
  transition:all 0.7s var(--ease);
}
.about-section.visible{
  opacity:1;
  transform:translateY(0);
}
.section-header{
  display:flex;
  align-items:center;
  gap:15px;
  margin-bottom:40px;
  position:relative;
}
.section-icon{
  width:56px;height:56px;
  background:linear-gradient(135deg, var(--redsoft), var(--purple) 100%);
  border-radius:18px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:26px;
  color:var(--red);
  position:relative;
  overflow:hidden;
}
.section-icon::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(135deg, transparent, rgba(255,255,255,.1));
  transform:translateX(-100%);
  transition:transform .5s;
}
.about-section.visible .section-icon::before{
  transform:translateX(0);
}
.section-header h2{
  font-family:'Cormorant Garamond',serif;
  font-size:38px;
  font-weight:700;
  letter-spacing:-1px;
}
.section-header p{
  color:var(--t2);
  font-size:13px;
  margin-top:5px;
  letter-spacing:0.5px;
}

/* System Info Card Enhanced */
.system-info-card{
  background:linear-gradient(135deg, rgba(15,15,21,.9), rgba(21,21,29,.95));
  backdrop-filter:blur(10px);
  border:1px solid var(--border);
  border-radius:28px;
  padding:40px;
  transition:all .4s;
  position:relative;
  overflow:hidden;
}
.system-info-card::before{
  content:'';
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:1px;
  background:linear-gradient(90deg, transparent, var(--red), var(--purple), transparent);
}
.system-name-large{
  font-family:'Cormorant Garamond',serif;
  font-size:56px;
  font-weight:700;
  text-align:center;
  margin-bottom:40px;
  position:relative;
}
.system-name-large span{
  background:linear-gradient(135deg, var(--red), var(--purple));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}
.info-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  gap:20px;
}
.info-item{
  display:flex;
  align-items:flex-start;
  gap:16px;
  padding:20px;
  background:rgba(255,255,255,.02);
  border-radius:20px;
  transition:all .3s;
  border:1px solid transparent;
}
.info-item:hover{
  border-color:rgba(232,23,58,.2);
  background:rgba(255,255,255,.04);
  transform:translateX(6px);
}
.info-icon{
  width:48px;height:48px;
  background:linear-gradient(135deg, var(--redsoft), var(--purple));
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:20px;
  color:var(--red);
}
.info-content h4{
  font-size:11px;
  color:var(--red);
  letter-spacing:1.5px;
  text-transform:uppercase;
  margin-bottom:8px;
  font-weight:700;
}
.info-content p{
  font-size:16px;
  font-weight:600;
  color:var(--t0);
  margin-bottom:4px;
}
.info-content small{
  font-size:11px;
  color:var(--t2);
}

/* Developer Cards Enhanced */
.dev-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(360px,1fr));
  gap:35px;
}
.dev-card{
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);
  border-radius:28px;
  overflow:hidden;
  transition:all .4s var(--spring);
  position:relative;
}
.dev-card:hover{
  transform:translateY(-12px);
  border-color:rgba(232,23,58,.3);
  box-shadow:0 30px 50px rgba(0,0,0,.6);
}
.dev-card::after{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(135deg, transparent, rgba(232,23,58,.05));
  opacity:0;
  transition:opacity .4s;
  pointer-events:none;
}
.dev-card:hover::after{opacity:1}
.dev-header{
  background:linear-gradient(135deg, var(--redsoft) 0%, transparent 70%);
  padding:35px 30px 25px;
  text-align:center;
  position:relative;
}
.dev-avatar{
  width:130px;height:130px;
  background:linear-gradient(135deg, var(--red), var(--purple));
  border-radius:50%;
  margin:0 auto 20px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:52px;
  font-weight:700;
  color:#fff;
  box-shadow:0 15px 35px var(--redglow);
  border:3px solid rgba(255,255,255,.15);
  transition:transform .3s;
}
.dev-card:hover .dev-avatar{
  transform:scale(1.05);
}
.dev-name{
  font-size:28px;
  font-weight:700;
  margin-bottom:6px;
  background:linear-gradient(135deg, #fff, var(--t1));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}
.dev-role{
  color:var(--red);
  font-size:12px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
}
.dev-body{
  padding:28px;
}
.dev-bio{
  color:var(--t1);
  font-size:14px;
  line-height:1.7;
  margin-bottom:24px;
  text-align:center;
}
.dev-details{
  background:rgba(255,255,255,.03);
  border-radius:18px;
  padding:18px;
  margin:16px 0;
}
.dev-detail-item{
  display:flex;
  justify-content:space-between;
  padding:10px 0;
  border-bottom:1px solid var(--border);
  font-size:13px;
}
.dev-detail-item:last-child{border-bottom:none}
.dev-detail-label{
  color:var(--t2);
  font-weight:500;
  display:flex;
  align-items:center;
  gap:8px;
}
.dev-detail-value{
  color:var(--t0);
  font-weight:600;
}
.dev-social{
  display:flex;
  justify-content:center;
  gap:14px;
  margin-top:24px;
}
.dev-social a{
  width:42px;height:42px;
  background:rgba(255,255,255,.05);
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--t1);
  font-size:18px;
  transition:all .3s var(--spring);
  text-decoration:none;
}
.dev-social a:hover{
  background:linear-gradient(135deg, var(--red), var(--purple));
  color:#fff;
  transform:translateY(-4px) rotate(360deg);
}

/* Stats Grid Enhanced */
.stats-about-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:20px;
}
.stat-about-card{
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);
  border-radius:24px;
  padding:35px 20px;
  text-align:center;
  transition:all .3s;
  position:relative;
  overflow:hidden;
}
.stat-about-card::before{
  content:'';
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  height:3px;
  background:linear-gradient(90deg, var(--red), var(--purple));
  transform:scaleX(0);
  transition:transform .3s;
}
.stat-about-card:hover::before{
  transform:scaleX(1);
}
.stat-about-card:hover{
  transform:translateY(-6px);
  border-color:rgba(232,23,58,.2);
}
.stat-about-icon{
  font-size:36px;
  color:var(--red);
  margin-bottom:15px;
}
.stat-about-number{
  font-family:'Cormorant Garamond',serif;
  font-size:52px;
  font-weight:700;
  background:linear-gradient(135deg, var(--red), var(--purple));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
  line-height:1;
  margin-bottom:8px;
}
.stat-about-label{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:1.5px;
  color:var(--t2);
  font-weight:600;
}

/* Tech Stack Enhanced */
.tech-grid{
  display:flex;
  flex-wrap:wrap;
  gap:14px;
}
.tech-badge{
  background:linear-gradient(135deg, var(--s2), var(--s3));
  border:1px solid var(--border);
  padding:12px 24px;
  border-radius:60px;
  font-size:13px;
  font-weight:600;
  display:inline-flex;
  align-items:center;
  gap:10px;
  transition:all .3s var(--spring);
  cursor:default;
}
.tech-badge:hover{
  border-color:var(--red);
  background:linear-gradient(135deg, var(--redsoft), transparent);
  transform:translateY(-3px) scale(1.02);
  box-shadow:0 8px 20px rgba(232,23,58,.2);
}
.tech-badge i{
  color:var(--red);
  font-size:16px;
}

/* Version Timeline Enhanced */
.version-timeline{
  display:flex;
  flex-direction:column;
  gap:20px;
}
.version-item{
  display:flex;
  gap:25px;
  padding:25px;
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);
  border-radius:20px;
  transition:all .3s;
  position:relative;
  overflow:hidden;
}
.version-item:hover{
  border-color:rgba(232,23,58,.3);
  transform:translateX(8px);
}
.version-tag{
  min-width:110px;
  font-family:'Cormorant Garamond',serif;
  font-size:32px;
  font-weight:700;
  background:linear-gradient(135deg, var(--red), var(--purple));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}
.version-content h4{
  font-size:18px;
  margin-bottom:8px;
  color:var(--t0);
}
.version-content p{
  color:var(--t1);
  font-size:13px;
  line-height:1.6;
}

/* Acknowledgments Section Enhanced */
.acknowledgments-card{
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);
  border-radius:32px;
  padding:45px 40px;
  text-align:center;
  position:relative;
  overflow:hidden;
  transition:all .4s;
}
.acknowledgments-card:hover{
  border-color:rgba(232,23,58,.3);
  transform:scale(1.01);
}
.acknowledgments-card::before{
  content:'';
  position:absolute;
  top:-50%;
  left:-50%;
  width:200%;
  height:200%;
  background:radial-gradient(circle, rgba(232,23,58,.05) 0%, transparent 70%);
  opacity:0;
  transition:opacity .5s;
}
.acknowledgments-card:hover::before{
  opacity:1;
}
.gratitude-grid{
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:30px;
  margin-bottom:35px;
  position:relative;
  z-index:1;
}
.gratitude-item{
  text-align:center;
  min-width:90px;
  transition:all .3s;
}
.gratitude-item:hover{
  transform:translateY(-6px);
}
.gratitude-icon{
  width:70px;height:70px;
  background:linear-gradient(135deg, var(--redsoft), var(--purple));
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  margin:0 auto 12px;
  font-size:30px;
  transition:all .3s;
}
.gratitude-item:hover .gratitude-icon{
  transform:scale(1.1);
  box-shadow:0 10px 30px var(--redglow);
}
.gratitude-icon.instructors{color:var(--red)}
.gratitude-icon.classmates{color:var(--purple)}
.gratitude-icon.friends{color:var(--cyan)}
.gratitude-icon.family{color:var(--gold)}
.gratitude-label{
  font-size:12px;
  font-weight:600;
  color:var(--t1);
}
.gratitude-text{
  font-size:15px;
  line-height:1.9;
  color:var(--t1);
  margin-bottom:25px;
  position:relative;
  z-index:1;
}
.gratitude-quote{
  font-size:13px;
  color:var(--t2);
  font-style:italic;
  margin-bottom:20px;
}
.gratitude-signature{
  margin-top:25px;
  padding-top:20px;
  border-top:1px solid var(--border);
}
.gratitude-signature strong{
  background:linear-gradient(135deg, var(--red), var(--purple));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
  font-size:18px;
}

/* Footer Enhanced */
footer{
  text-align:center;
  padding:50px 5% 40px;
  border-top:1px solid var(--border);
  background:linear-gradient(135deg, var(--s1), var(--ink));
  position:relative;
}
.footer-logo{
  font-family:'Cormorant Garamond',serif;
  font-size:24px;
  font-weight:700;
  margin-bottom:12px;
}
.footer-logo em{
  background:linear-gradient(135deg, var(--red), var(--purple));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
  font-style:italic;
}
.footer-copy{
  font-size:12px;
  color:var(--t2);
  margin-top:10px;
}
.footer-heart{
  color:var(--red);
  animation:heartbeat 1.5s infinite;
}
@keyframes heartbeat{
  0%,100%{transform:scale(1)}
  50%{transform:scale(1.2)}
}

/* Responsive */
@media (max-width:768px){
  .stats-about-grid{grid-template-columns:repeat(2,1fr);gap:15px}
  .version-item{flex-direction:column;gap:15px}
  .version-tag{min-width:auto}
  .nav-links{display:none}
  .about-hero{padding-top:120px;min-height:70vh}
  .system-name-large{font-size:36px}
  .dev-grid{grid-template-columns:1fr}
  .info-grid{grid-template-columns:1fr}
  .section-header h2{font-size:28px}
  .system-info-card{padding:25px}
  .acknowledgments-card{padding:30px 25px}
  .gratitude-grid{gap:20px}
  .gratitude-icon{width:55px;height:55px;font-size:24px}
}
</style>
</head>
<body>

<div class="cursor-glow" id="cursorGlow"></div>

<!-- NAVIGATION -->
<nav class="nav" id="nav">
  <a href="index.php" class="nav-brand">
    <div class="nav-logo-pill">KV</div>
    <span class="nav-logo-name">Drama<em>Verse</em></span>
  </a>
  <div class="nav-links">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <a href="?search=romance"><i class="fas fa-heart"></i> Romance</a>
    <a href="?search=thriller"><i class="fas fa-bolt"></i> Thriller</a>
    <a href="plan-to-watch.php"><i class="fas fa-bookmark"></i> Watchlist</a>
    <a href="about.php" class="active"><i class="fas fa-info-circle"></i> About</a>
  </div>
  <div class="nav-right">
    <a href="add.php" class="nav-add"><i class="fas fa-plus"></i> Add Drama</a>
  </div>
</nav>

<!-- HERO SECTION ENHANCED -->
<section class="about-hero">
  <div class="about-hero-bg"></div>
  <div class="hero-particles" id="heroParticles"></div>
  <div class="about-hero-content">
    
    <h1>About the <em>System</em></h1>
    <p class="hero-subtitle">Meet the developers behind KDramaVerse — Precious A. Memoracion & Riza Mae M. Rosacena</p>
  </div>
  <div class="hero-scroll-indicator" onclick="scrollToContent()">
    <span class="scroll-text">SCROLL</span>
    <div class="scroll-line"></div>
  </div>
</section>

<div class="about-container">

  <!-- SECTION 1: SYSTEM INFORMATION -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">🖥️</div>
      <div>
        <h2>System Information</h2>
        <p>KDramaVerse — Your Personal Drama Collection Manager</p>
      </div>
    </div>
    
    <div class="system-info-card">
      <div class="system-name-large">
        K<span>Drama</span>Verse
      </div>
      <div class="info-grid">
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-tag"></i></div>
          <div class="info-content">
            <h4>System Name</h4>
            <p>KDramaVerse</p>
            <small>K-Drama Collection Management System</small>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-code-branch"></i></div>
          <div class="info-content">
            <h4>Version</h4>
            <p>2.1.0</p>
            <small>Stable Release</small>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
          <div class="info-content">
            <h4>Release Date</h4>
            <p>May 2026</p>
            <small>Latest Update: <?php echo date('F Y'); ?></small>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-database"></i></div>
          <div class="info-content">
            <h4>Database Engine</h4>
            <p>MySQL / MariaDB</p>
            <small><?php echo $total_dramas; ?> Dramas Indexed</small>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-globe"></i></div>
          <div class="info-content">
            <h4>Platform</h4>
            <p>Web-Based (PHP)</p>
            <small>Cross-Platform Compatible</small>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
          <div class="info-content">
            <h4>License</h4>
            <p>Educational / Internal Use</p>
            <small>A Project</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SECTION 2: THE DEVELOPERS -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">👩‍💻</div>
      <div>
        <h2>The Developers</h2>
        <p>The creative minds who built KDramaVerse</p>
      </div>
    </div>
    
    <div class="dev-grid">
      <!-- Developer 1: Precious A. Memoracion -->
      <div class="dev-card">
        <div class="dev-header">
          <div class="dev-avatar">PM</div>
          <div class="dev-name">Precious A. Memoracion</div>
          <div class="dev-role">✦ Lead Developer & System Architect ✦</div>
        </div>
        <div class="dev-body">
          <div class="dev-bio">
            Passionate developer with a love for clean code and seamless user experiences. 
            Responsible for the core architecture, database design, and front-end magic.
          </div>
          <div class="dev-details">
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-graduation-cap"></i> Program</span>
              <span class="dev-detail-value">Bachelor of Science in Information System</span>
            </div>
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-tasks"></i> Role</span>
              <span class="dev-detail-value">Full-Stack Developer, UI/UX Designer</span>
            </div>
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-code"></i> Contributions</span>
              <span class="dev-detail-value">System Logic, Database, UI Design, CRUD Operations</span>
            </div>
          </div>
          <div class="dev-social">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
            <a href="#"><i class="fas fa-envelope"></i></a>
          </div>
        </div>
      </div>

      <!-- Developer 2: Riza Mae M. Rosacena -->
      <div class="dev-card">
        <div class="dev-header">
          <div class="dev-avatar">RR</div>
          <div class="dev-name">Riza Mae M. Rosacena</div>
          <div class="dev-role">✦ Front-End & Database Specialist ✦</div>
        </div>
        <div class="dev-body">
          <div class="dev-bio">
            Detail-oriented developer focused on creating intuitive interfaces and efficient data management.
            Ensures every user interaction feels natural and responsive.
          </div>
          <div class="dev-details">
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-graduation-cap"></i> Program</span>
              <span class="dev-detail-value">Bachelor of Science in Information System</span>
            </div>
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-tasks"></i> Role</span>
              <span class="dev-detail-value">Front-End Developer, Database Analyst</span>
            </div>
            <div class="dev-detail-item">
              <span class="dev-detail-label"><i class="fas fa-code"></i> Contributions</span>
              <span class="dev-detail-value">Front-End Logic, Search Features, Watchlist Module</span>
            </div>
          </div>
          <div class="dev-social">
            <a href="#"><i class="fab fa-github"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
            <a href="#"><i class="fas fa-envelope"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SECTION 3: SYSTEM STATS -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">📊</div>
      <div>
        <h2>System Statistics</h2>
        <p>Live data from your drama collection</p>
      </div>
    </div>
    <div class="stats-about-grid">
      <div class="stat-about-card">
        <div class="stat-about-icon">🎬</div>
        <div class="stat-about-number"><?php echo $total_dramas; ?></div>
        <div class="stat-about-label">Total Dramas</div>
      </div>
      <div class="stat-about-card">
        <div class="stat-about-icon">⭐</div>
        <div class="stat-about-number"><?php echo $avg_rating ?: '0'; ?></div>
        <div class="stat-about-label">Average Rating</div>
      </div>
      <div class="stat-about-card">
        <div class="stat-about-icon">📚</div>
        <div class="stat-about-number"><?php echo $unique_genres ?: '0'; ?></div>
        <div class="stat-about-label">Unique Genres</div>
      </div>
      <div class="stat-about-card">
        <div class="stat-about-icon">⏳</div>
        <div class="stat-about-number"><?php echo $total_watchlist; ?></div>
        <div class="stat-about-label">Watchlist Items</div>
      </div>
    </div>
  </div>

  <!-- SECTION 4: VERSION HISTORY -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">📜</div>
      <div>
        <h2>Version History</h2>
        <p>The evolution of KDramaVerse</p>
      </div>
    </div>
    <div class="version-timeline">
      <div class="version-item">
        <div class="version-tag">v2.1</div>
        <div class="version-content">
          <h4>Current Release — Cinematic UI</h4>
          <p>Poster lightbox, advanced search overlay, watchlist badges, responsive grid, and performance improvements.</p>
        </div>
      </div>
      <div class="version-item">
        <div class="version-tag">v2.0</div>
        <div class="version-content">
          <h4>Major Overhaul</h4>
          <p>Complete redesign with dark theme, genre filtering, sort options, and plan-to-watch functionality.</p>
        </div>
      </div>
      <div class="version-item">
        <div class="version-tag">v1.0</div>
        <div class="version-content">
          <h4>Initial Launch</h4>
          <p>Basic CRUD operations, image uploads, and simple listing page.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- SECTION 5: TECHNOLOGY STACK -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">⚙️</div>
      <div>
        <h2>Built With</h2>
        <p>Technologies powering KDramaVerse</p>
      </div>
    </div>
    <div class="tech-grid">
      <div class="tech-badge"><i class="fab fa-php"></i> PHP 8+</div>
      <div class="tech-badge"><i class="fas fa-database"></i> MySQL</div>
      <div class="tech-badge"><i class="fab fa-html5"></i> HTML5</div>
      <div class="tech-badge"><i class="fab fa-css3-alt"></i> CSS3</div>
      <div class="tech-badge"><i class="fab fa-js"></i> JavaScript (Vanilla)</div>
      <div class="tech-badge"><i class="fas fa-cloud-upload-alt"></i> Native File Uploads</div>
      <div class="tech-badge"><i class="fas fa-palette"></i> Custom CSS Variables</div>
      <div class="tech-badge"><i class="fas fa-font"></i> Google Fonts</div>
      <div class="tech-badge"><i class="fas fa-chart-line"></i> Font Awesome Icons</div>
    </div>
  </div>

  <!-- SECTION 6: ACKNOWLEDGMENTS ENHANCED -->
  <div class="about-section">
    <div class="section-header">
      <div class="section-icon">🙏</div>
      <div>
        <h2>Acknowledgments</h2>
        <p>Special thanks to our instructors, classmates, friends, and family</p>
      </div>
    </div>
    
    <div class="acknowledgments-card">
      <div class="gratitude-grid">
        <div class="gratitude-item">
          <div class="gratitude-icon instructors">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <div class="gratitude-label">Instructors</div>
        </div>
        <div class="gratitude-item">
          <div class="gratitude-icon classmates">
            <i class="fas fa-users"></i>
          </div>
          <div class="gratitude-label">Classmates</div>
        </div>
        <div class="gratitude-item">
          <div class="gratitude-icon friends">
            <i class="fas fa-user-friends"></i>
          </div>
          <div class="gratitude-label">Friends</div>
        </div>
        <div class="gratitude-item">
          <div class="gratitude-icon family">
            <i class="fas fa-home"></i>
          </div>
          <div class="gratitude-label">Family</div>
        </div>
      </div>
      
      <div class="gratitude-text">
        To our dedicated <strong style="color:var(--red);">instructors</strong> for sharing their knowledge and guiding us through every challenge.<br>
        To our <strong style="color:var(--purple);">classmates</strong> for the collaboration, late-night coding sessions, and moral support.<br>
        To our <strong style="color:var(--cyan);">friends</strong> for understanding our busy schedule and cheering us on.<br>
        To our <strong style="color:var(--gold);">family</strong> for their endless love, patience, and belief in our dreams.
      </div>
      
      <div class="gratitude-quote">
        <i class="fas fa-quote-left" style="color:var(--red); margin-right: 8px;"></i>
        This project is dedicated to everyone who supported us along the way.
        <i class="fas fa-quote-right" style="color:var(--red); margin-left: 8px;"></i>
      </div>
      
      <div class="gratitude-signature">
        <strong>"Gratitude turns what we have into enough."</strong><br>
        <span style="font-size: 12px; color: var(--t2);">— Precious & Riza</span>
      </div>
    </div>
  </div>

</div>

<footer>
  <div class="footer-logo">KDrama<em>Verse</em></div>
  <div class="footer-copy">© <?php echo date('Y');?> KDramaVerse | Developed by Precious A. Memoracion & Riza Mae M. Rosacena</div>
  <div class="footer-copy">Bachelor of Science in Information Technology — Capstone Project <span class="footer-heart">❤️</span></div>
</footer>

<script>
// Sticky nav detection
window.addEventListener('scroll', () => {
  const nav = document.getElementById('nav');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
}, {passive: true});

// Scroll to content function
function scrollToContent() {
  const container = document.querySelector('.about-container');
  if (container) {
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

// Intersection Observer for sections
const observerOptions = { threshold: 0.15, rootMargin: '0px 0px -50px 0px' };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

document.querySelectorAll('.about-section').forEach(section => observer.observe(section));

// Cursor glow effect
const cursorGlow = document.getElementById('cursorGlow');
document.addEventListener('mousemove', (e) => {
  cursorGlow.style.left = e.clientX - 200 + 'px';
  cursorGlow.style.top = e.clientY - 200 + 'px';
  cursorGlow.style.opacity = '0.5';
});

// Hero particles
function createHeroParticles() {
  const container = document.getElementById('heroParticles');
  if (!container) return;
  
  for (let i = 0; i < 50; i++) {
    const particle = document.createElement('div');
    particle.className = 'hero-particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.animationDuration = 6 + Math.random() * 10 + 's';
    particle.style.animationDelay = Math.random() * 5 + 's';
    particle.style.background = `rgba(232, 23, 58, ${0.2 + Math.random() * 0.5})`;
    particle.style.width = 1 + Math.random() * 3 + 'px';
    particle.style.height = particle.style.width;
    container.appendChild(particle);
  }
}
createHeroParticles();

// Animate stat numbers on scroll
const statNumbers = document.querySelectorAll('.stat-about-number');
const numberObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const element = entry.target;
      const target = parseInt(element.innerText);
      if (isNaN(target)) return;
      let current = 0;
      const increment = target / 50;
      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          element.innerText = target;
          clearInterval(timer);
        } else {
          element.innerText = Math.floor(current);
        }
      }, 20);
      numberObserver.unobserve(element);
    }
  });
}, { threshold: 0.5 });

statNumbers.forEach(num => numberObserver.observe(num));
</script>

</body>
</html>