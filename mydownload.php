<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Downloads - KDramaVerse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
:root{
  --red:#E8173A;--red2:#B50F2B;--redglow:rgba(232,23,58,.18);
  --gold:#F0B429;--ink:#070709;--bg:#0D0D12;--s1:#131318;--s2:#1A1A22;--s3:#22222D;
  --t0:#F2F0FF;--t1:#8E8CAA;--t2:#48475E;
  --border:rgba(255,255,255,.06);--bhi:rgba(255,255,255,.11);
  --glass:rgba(13,13,18,.85);
  --green:#00C853;
}
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--ink);color:var(--t0);
  min-height:100vh;
}
.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:70px;padding:0 5%;
  display:flex;align-items:center;justify-content:space-between;
  background:var(--glass);
  backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);
}
.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-pill{
  height:34px;padding:0 14px;border-radius:8px;background:var(--red);
  display:flex;align-items:center;font-size:14px;font-weight:700;color:#fff;
}
.nav-logo-name{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--t0)}
.nav-logo-name em{color:var(--red);font-style:italic}
.nav-links{display:flex;gap:4px}
.nav-links a{
  color:var(--t1);text-decoration:none;font-size:14px;padding:8px 18px;
  border-radius:10px;transition:all .2s;
}
.nav-links a:hover,.nav-links a.active{color:var(--t0);background:rgba(255,255,255,.05)}
.nav-add{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--red);color:#fff;padding:8px 20px;
  border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;
}
.container{max-width:1400px;margin:0 auto;padding:90px 5% 40px}
.page-header{margin-bottom:32px}
.page-header h1{font-family:'Cormorant Garamond',serif;font-size:42px;margin-bottom:8px}
.page-header p{color:var(--t2)}
.stats-grid{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;
  margin-bottom:40px;
}
.stat-card{
  background:var(--s1);border:1px solid var(--border);border-radius:16px;
  padding:20px;text-align:center;
}
.stat-card i{font-size:32px;color:var(--red);margin-bottom:12px;display:block}
.stat-number{font-size:32px;font-weight:700;color:var(--t0)}
.stat-label{font-size:12px;color:var(--t2);margin-top:6px}
.downloads-table{
  background:var(--s1);border:1px solid var(--border);border-radius:16px;
  overflow-x:auto;
}
table{width:100%;border-collapse:collapse}
th,td{padding:16px;text-align:left;border-bottom:1px solid var(--border)}
th{color:var(--t2);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:1px}
td{color:var(--t1)}
tr:hover{background:rgba(255,255,255,.02)}
.quality-badge{
  display:inline-block;padding:4px 10px;border-radius:6px;
  font-size:11px;font-weight:600;
}
.quality-4k{background:rgba(240,180,41,.2);color:var(--gold)}
.quality-1080p{background:rgba(232,23,58,.2);color:var(--red)}
.quality-720p{background:rgba(255,255,255,.1);color:var(--t1)}
.empty-state{
  text-align:center;padding:60px;color:var(--t2);
}
.empty-state i{font-size:64px;margin-bottom:20px;opacity:.5}
.empty-state h3{font-size:24px;margin-bottom:10px;color:var(--t1)}
.btn-back{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--red);color:#fff;padding:10px 20px;
  border-radius:10px;text-decoration:none;margin-bottom:20px;
  font-size:14px;font-weight:500;
}
.btn-back:hover{background:var(--red2)}
.top-dramas{
  background:var(--s1);border:1px solid var(--border);border-radius:16px;
  padding:20px;margin-bottom:30px;
}
.top-dramas h3{margin-bottom:16px;font-size:18px}
.top-list{display:flex;flex-wrap:wrap;gap:12px}
.top-item{
  background:var(--s2);padding:8px 16px;border-radius:8px;
  display:flex;align-items:center;gap:10px;
}
.top-rank{color:var(--gold);font-weight:700}
@media(max-width:768px){
  th,td{padding:12px 8px;font-size:12px}
  .stats-grid{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<nav class="nav">
  <a href="index.php" class="nav-brand">
    <div class="nav-logo-pill">KV</div>
    <span class="nav-logo-name">Drama<em>Verse</em></span>
  </a>
  <div class="nav-links">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <a href="my-downloads.php" class="active"><i class="fas fa-download"></i> My Downloads</a>
  </div>
  <a href="add.php" class="nav-add"><i class="fas fa-plus"></i> Add Drama</a>
</nav>

<div class="container">
  <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dramas</a>
  
  <?php
  $user_downloads = $conn->query("SELECT * FROM downloads ORDER BY download_date DESC LIMIT 50");
  $download_stats = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN DATE(download_date) = CURDATE() THEN 1 ELSE 0 END) as today FROM downloads")->fetch_assoc();
  $top_dramas = $conn->query("SELECT drama_title, COUNT(*) as count FROM downloads GROUP BY drama_title ORDER BY count DESC LIMIT 5");
  ?>
  
  <div class="page-header">
    <h1><i class="fas fa-download" style="color:var(--red)"></i> My Download History</h1>
    <p>Track all the K-Dramas you've downloaded</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <i class="fas fa-database"></i>
      <div class="stat-number"><?php echo number_format($download_stats['total'] ?? 0); ?></div>
      <div class="stat-label">Total Downloads</div>
    </div>
    <div class="stat-card">
      <i class="fas fa-calendar-day"></i>
      <div class="stat-number"><?php echo number_format($download_stats['today'] ?? 0); ?></div>
      <div class="stat-label">Today's Downloads</div>
    </div>
    <div class="stat-card">
      <i class="fas fa-film"></i>
      <div class="stat-number"><?php echo number_format($user_downloads->num_rows ?? 0); ?></div>
      <div class="stat-label">Files Downloaded</div>
    </div>
  </div>

  <?php if($top_dramas && $top_dramas->num_rows > 0): ?>
  <div class="top-dramas">
    <h3><i class="fas fa-trophy" style="color:var(--gold)"></i> Most Downloaded Dramas</h3>
    <div class="top-list">
      <?php $rank = 1; while($top = $top_dramas->fetch_assoc()): ?>
      <div class="top-item">
        <span class="top-rank">#<?php echo $rank++; ?></span>
        <span><?php echo htmlspecialchars($top['drama_title']); ?></span>
        <span style="color:var(--green)">(<?php echo $top['count']; ?> downloads)</span>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="downloads-table">
    <table>
      <thead>
        <tr><th>Drama Title</th><th>Quality</th><th>Size</th><th>Subtitles</th><th>Download Date</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if($user_downloads && $user_downloads->num_rows > 0): ?>
          <?php while($dl = $user_downloads->fetch_assoc()): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($dl['drama_title']); ?></strong></td>
            <td><span class="quality-badge quality-<?php echo strtolower($dl['quality']); ?>"><?php echo htmlspecialchars($dl['quality']); ?></span></td>
            <td><?php echo htmlspecialchars($dl['size']); ?></td>
            <td><i class="fas fa-language"></i> <?php echo htmlspecialchars($dl['language']); ?></td>
            <td><?php echo date('M d, Y h:i A', strtotime($dl['download_date'])); ?></td>
            <td><span style="color:var(--green)"><i class="fas fa-check-circle"></i> Completed</span></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fas fa-download"></i><h3>No downloads yet</h3><p>Go to the <a href="index.php" style="color:var(--red)">homepage</a> and download your first K-Drama!</p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>