<?php include "db.php"; ?>
<?php
// --- Handle form actions ---
$action  = $_POST['action']  ?? '';
$ptw_id  = (int)($_POST['ptw_id'] ?? 0);
$msg     = '';
$updated_status = '';
$updated_drama_title = '';

if ($action === 'add') {
    $title    = trim($_POST['drama_title'] ?? '');
    $genre    = trim($_POST['genre']       ?? '');
    $year     = (int)($_POST['year']       ?? date('Y'));
    $priority = trim($_POST['priority']    ?? 'Normal');
    $notes    = trim($_POST['notes']       ?? '');
    if ($title) {
        $stmt = $conn->prepare("INSERT INTO plan_to_watch 
        (drama_title, genre, year, priority, notes, status, added_date) 
        VALUES (?, ?, ?, ?, ?, 'Plan to Watch', NOW())");
        $stmt->bind_param("ssiss", $title, $genre, $year, $priority, $notes);
        $stmt->execute();
        $msg = "added";
    }
}

if ($action === 'status' && $ptw_id) {
    $new_status = trim($_POST['new_status'] ?? '');
    $allowed    = ['Plan to Watch', 'Currently Watching', 'Completed', 'Dropped'];
    
    // Get drama title for success message
    $title_result = $conn->query("SELECT drama_title FROM plan_to_watch WHERE id = $ptw_id");
    if ($title_result && $title_result->num_rows > 0) {
        $updated_drama_title = $title_result->fetch_assoc()['drama_title'];
    }
    
    if (in_array($new_status, $allowed)) {
        $finish = ($new_status === 'Completed') ? ', finished_date = NOW()' : '';
        $conn->query("UPDATE plan_to_watch SET status = '$new_status'$finish WHERE id = $ptw_id");
        $updated_status = $new_status;
        $msg = "status_updated";
    }
}

if ($action === 'delete' && $ptw_id) {
    $conn->query("DELETE FROM plan_to_watch WHERE id = $ptw_id");
    $msg = "deleted";
}

if ($action === 'rate' && $ptw_id) {
    $rating = min(10, max(0, (float)($_POST['rating'] ?? 0)));
    $conn->query("UPDATE plan_to_watch SET rating = $rating WHERE id = $ptw_id");
    $msg = "rated";
}

// --- Read data ---
$filter_status = $_GET['status'] ?? 'All';
$allowed_filters = ['All', 'Plan to Watch', 'Currently Watching', 'Completed', 'Dropped'];
if (!in_array($filter_status, $allowed_filters)) $filter_status = 'All';

$where = ($filter_status !== 'All') ? "WHERE status = '" . $conn->real_escape_string($filter_status) . "'" : '';
$dramas = $conn->query("SELECT * FROM plan_to_watch $where ORDER BY FIELD(priority,'High','Normal','Low'), added_date DESC");

$stats_q = $conn->query("
  SELECT
    COUNT(*) AS total,
    SUM(status = 'Plan to Watch')      AS planning,
    SUM(status = 'Currently Watching') AS watching,
    SUM(status = 'Completed')          AS completed,
    SUM(status = 'Dropped')            AS dropped
  FROM plan_to_watch
");
$stats = $stats_q->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Plan to Watch — KDramaVerse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
  --red:#E8173A; --red2:#B50F2B; --redglow:rgba(232,23,58,.18);
  --gold:#F0B429; --gold-dim:rgba(240,180,41,.15);
  --ink:#070709; --bg:#0D0D12; --s1:#131318; --s2:#1A1A22; --s3:#22222D;
  --t0:#F2F0FF; --t1:#8E8CAA; --t2:#48475E;
  --border:rgba(255,255,255,.06); --bhi:rgba(255,255,255,.11);
  --glass:rgba(13,13,18,.88);
  --green:#00C853; --green-dim:rgba(0,200,83,.15);
  --cyan:#00B4D8; --cyan-dim:rgba(0,180,216,.15);
  --purple:#9B5DE5; --purple-dim:rgba(155,93,229,.15);
  --orange:#FF6B35; --orange-dim:rgba(255,107,53,.15);
}

*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--ink); color:var(--t0);
  min-height:100vh;
  background-image:
    radial-gradient(ellipse 900px 500px at 80% -10%, rgba(232,23,58,.07) 0%, transparent 70%),
    radial-gradient(ellipse 600px 400px at -5% 60%,  rgba(155,93,229,.05) 0%, transparent 70%);
}

.nav{
  position:fixed;top:0;left:0;right:0;z-index:1000;
  height:68px; padding:0 5%;
  display:flex; align-items:center; justify-content:space-between;
  background:var(--glass); backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);
}
.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-pill{
  height:32px;padding:0 13px;border-radius:8px;background:var(--red);
  display:flex;align-items:center;font-size:13px;font-weight:700;color:#fff;letter-spacing:.5px;
}
.nav-logo-name{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--t0)}
.nav-logo-name em{color:var(--red);font-style:italic}
.nav-links{display:flex;gap:4px}
.nav-links a{
  color:var(--t1);text-decoration:none;font-size:14px;padding:7px 16px;
  border-radius:10px;transition:all .2s;display:flex;align-items:center;gap:7px;
}
.nav-links a:hover,.nav-links a.active{color:var(--t0);background:rgba(255,255,255,.05)}
.nav-cta{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--red);color:#fff;padding:8px 18px;
  border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;
  transition:background .2s;cursor:pointer;border:none;font-family:'DM Sans',sans-serif;
}
.nav-cta:hover{background:var(--red2)}

.container{max-width:1380px;margin:0 auto;padding:90px 5% 60px}

.page-header{
  display:flex;align-items:flex-start;justify-content:space-between;
  flex-wrap:wrap;gap:20px;margin-bottom:36px;
}
.page-header-left h1{
  font-family:'Cormorant Garamond',serif;
  font-size:48px;line-height:1;margin-bottom:8px;
}
.page-header-left h1 span{color:var(--red)}
.page-header-left p{color:var(--t2);font-size:15px}

.stats-row{
  display:flex;gap:16px;margin-bottom:32px;flex-wrap:wrap;
}
.stat-pill{
  flex:1;min-width:150px;
  background:var(--s1);border:1px solid var(--border);border-radius:14px;
  padding:16px 20px;display:flex;align-items:center;gap:14px;
  transition:border-color .2s;
}
.stat-pill:hover{border-color:var(--bhi)}
.stat-icon{
  width:42px;height:42px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;
}
.si-all    {background:var(--purple-dim); color:var(--purple)}
.si-plan   {background:var(--cyan-dim);   color:var(--cyan)}
.si-watch  {background:var(--gold-dim);   color:var(--gold)}
.si-done   {background:var(--green-dim);  color:var(--green)}
.si-drop   {background:var(--orange-dim); color:var(--orange)}
.stat-info{}
.stat-num{font-size:26px;font-weight:700;line-height:1}
.stat-lbl{font-size:11px;color:var(--t2);margin-top:2px;text-transform:uppercase;letter-spacing:.7px}

.flash{
  background:var(--green-dim);border:1px solid var(--green);
  color:var(--green);border-radius:10px;padding:12px 18px;
  margin-bottom:24px;font-size:14px;display:flex;align-items:center;gap:10px;
  animation: slideDown 0.4s ease-out;
}
.flash-warning{
  background:var(--gold-dim);border-color:var(--gold);
  color:var(--gold);
}
@keyframes slideDown{
  from{opacity:0;transform:translateY(-10px)}
  to{opacity:1;transform:translateY(0)}
}

.filter-bar{
  display:flex;gap:8px;margin-bottom:28px;flex-wrap:wrap;
}
.filter-btn{
  padding:8px 18px;border-radius:9px;border:1px solid var(--border);
  background:transparent;color:var(--t1);font-size:13px;font-weight:500;
  cursor:pointer;text-decoration:none;transition:all .2s;
  font-family:'DM Sans',sans-serif;
}
.filter-btn:hover{border-color:var(--bhi);color:var(--t0)}
.filter-btn.active{background:var(--red);border-color:var(--red);color:#fff}

.modal-overlay{
  display:none;position:fixed;inset:0;z-index:2000;
  background:rgba(7,7,9,.75);backdrop-filter:blur(8px);
  align-items:center;justify-content:center;
}
.modal-overlay.open{display:flex}
.modal{
  background:var(--s1);border:1px solid var(--bhi);border-radius:20px;
  padding:36px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;
  animation:modalIn .25s ease;
}
@keyframes modalIn{from{opacity:0;transform:scale(.94) translateY(10px)}to{opacity:1;transform:none}}
.modal h2{
  font-family:'Cormorant Garamond',serif;font-size:30px;margin-bottom:24px;
  display:flex;align-items:center;gap:12px;
}
.modal h2 i{color:var(--red)}
.form-group{margin-bottom:18px}
.form-group label{
  display:block;font-size:12px;font-weight:600;color:var(--t2);
  text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;
}
.form-group input,
.form-group select,
.form-group textarea{
  width:100%;background:var(--s2);border:1px solid var(--border);
  border-radius:10px;padding:11px 14px;color:var(--t0);
  font-family:'DM Sans',sans-serif;font-size:14px;
  transition:border-color .2s;outline:none;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{border-color:var(--red)}
.form-group textarea{resize:vertical;min-height:80px}
.form-group select option{background:var(--s2)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.modal-actions{display:flex;gap:10px;margin-top:24px}
.btn-submit{
  flex:1;background:var(--red);color:#fff;border:none;border-radius:10px;
  padding:12px;font-size:14px;font-weight:600;cursor:pointer;
  font-family:'DM Sans',sans-serif;transition:background .2s;
}
.btn-submit:hover{background:var(--red2)}
.btn-cancel{
  padding:12px 24px;background:var(--s2);color:var(--t1);border:1px solid var(--border);
  border-radius:10px;font-size:14px;cursor:pointer;font-family:'DM Sans',sans-serif;
  transition:all .2s;
}
.btn-cancel:hover{color:var(--t0);border-color:var(--bhi)}

.drama-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
  gap:20px;
}
.drama-card{
  background:var(--s1);border:1px solid var(--border);border-radius:18px;
  padding:22px;transition:all .22s;position:relative;
  animation: cardFadeIn 0.3s ease-out;
}
@keyframes cardFadeIn{
  from{opacity:0;transform:translateY(10px)}
  to{opacity:1;transform:translateY(0)}
}
.drama-card:hover{border-color:var(--bhi);transform:translateY(-2px)}

.drama-card[data-status="Plan to Watch"]     {border-left:3px solid var(--cyan)}
.drama-card[data-status="Currently Watching"]{border-left:3px solid var(--gold)}
.drama-card[data-status="Completed"]         {border-left:3px solid var(--green)}
.drama-card[data-status="Dropped"]           {border-left:3px solid var(--orange)}

.card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
.card-title-block{}
.card-title{
  font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;
  line-height:1.2;margin-bottom:4px;
}
.card-meta{font-size:12px;color:var(--t2);display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.card-meta span{display:flex;align-items:center;gap:4px}

.card-actions{display:flex;gap:6px;flex-shrink:0}
.icon-btn{
  width:32px;height:32px;border-radius:8px;border:1px solid var(--border);
  background:var(--s2);color:var(--t1);cursor:pointer;
  display:flex;align-items:center;justify-content:center;font-size:13px;
  transition:all .18s;
}
.icon-btn:hover{border-color:var(--bhi);color:var(--t0)}
.icon-btn.del:hover{border-color:var(--red);color:var(--red);background:var(--redglow)}

.badge-row{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.badge{
  display:inline-flex;align-items:center;gap:5px;
  padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;
}
.badge-status-plan   {background:var(--cyan-dim);  color:var(--cyan)}
.badge-status-watch  {background:var(--gold-dim);  color:var(--gold)}
.badge-status-done   {background:var(--green-dim); color:var(--green)}
.badge-status-drop   {background:var(--orange-dim);color:var(--orange)}
.badge-prio-high  {background:rgba(232,23,58,.18); color:var(--red)}
.badge-prio-normal{background:rgba(255,255,255,.07);color:var(--t1)}
.badge-prio-low   {background:rgba(0,180,216,.1); color:var(--cyan)}

.status-switcher{
  display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;
}
.status-btn{
  padding:5px 12px;border-radius:7px;border:1px solid var(--border);
  background:transparent;color:var(--t2);font-size:11px;font-weight:600;
  cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s;
}
.status-btn:hover{color:var(--t1);border-color:var(--bhi);background:rgba(255,255,255,.03)}
.status-btn.active{
  border-color:currentColor;
  background:rgba(255,255,255,.08);
}
.status-btn[data-s="Plan to Watch"]:hover,
.status-btn[data-s="Plan to Watch"].active{color:var(--cyan);border-color:var(--cyan)}
.status-btn[data-s="Currently Watching"]:hover,
.status-btn[data-s="Currently Watching"].active{color:var(--gold);border-color:var(--gold)}
.status-btn[data-s="Completed"]:hover,
.status-btn[data-s="Completed"].active{color:var(--green);border-color:var(--green)}
.status-btn[data-s="Dropped"]:hover,
.status-btn[data-s="Dropped"].active{color:var(--orange);border-color:var(--orange)}

.star-row{display:flex;align-items:center;gap:6px}
.stars{display:flex;gap:2px}
.star{
  font-size:16px;cursor:pointer;color:var(--t2);
  transition:color .15s;
}
.star.lit{color:var(--gold)}
.rating-val{font-size:12px;color:var(--t2)}

.card-notes{
  font-size:13px;color:var(--t2);line-height:1.55;
  border-top:1px solid var(--border);padding-top:12px;margin-top:4px;
}

.empty{
  grid-column:1/-1;text-align:center;padding:80px 20px;color:var(--t2);
}
.empty i{font-size:64px;margin-bottom:20px;opacity:.3;display:block}
.empty h3{font-size:24px;color:var(--t1);margin-bottom:8px}
.empty p{font-size:14px}

@media(max-width:768px){
  .page-header{flex-direction:column}
  .drama-grid{grid-template-columns:1fr}
  .form-row{grid-template-columns:1fr}
  .modal{padding:24px;margin:16px}
  .stats-row{gap:10px}
  .stat-pill{min-width:calc(50% - 10px)}
}

/* Loading state for status buttons */
.status-btn.loading {
  opacity: 0.6;
  cursor: wait;
  pointer-events: none;
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
    <a href="plan-to-watch.php" class="active"><i class="fas fa-bookmark"></i> Watchlist</a>
  </div>
</nav>

<div class="container">

  <?php if($msg === 'added'): ?>
  <div class="flash"><i class="fas fa-check-circle"></i> ✨ Drama added to your watchlist!</div>
  <?php elseif($msg === 'status_updated'): ?>
  <div class="flash"><i class="fas fa-sync-alt"></i> "<?php echo htmlspecialchars($updated_drama_title); ?>" marked as 
    <strong>
      <?php 
      $display_status = match($updated_status) {
        'Plan to Watch' => '📋 Plan to Watch',
        'Currently Watching' => '🎬 Watching Now',
        'Completed' => '✅ Completed',
        'Dropped' => '❌ Dropped',
        default => $updated_status
      };
      echo $display_status;
      ?>
    </strong>!
  </div>
  <?php elseif($msg === 'deleted'): ?>
  <div class="flash flash-warning"><i class="fas fa-trash"></i> 🗑️ Drama removed from watchlist.</div>
  <?php elseif($msg === 'rated'): ?>
  <div class="flash"><i class="fas fa-star"></i> ⭐ Rating saved!</div>
  <?php endif; ?>

  <div class="page-header">
    <div class="page-header-left">
      <h1><i class="fas fa-bookmark" style="color:var(--red);font-size:.85em"></i> My <span>Watchlist</span></h1>
      <p>Track, rate, and manage every K-Drama you want to watch</p>
    </div>
  </div>

  <div class="stats-row">
    <div class="stat-pill">
      <div class="stat-icon si-all"><i class="fas fa-layer-group"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= number_format($stats['total'] ?? 0) ?></div>
        <div class="stat-lbl">TOTAL</div>
      </div>
    </div>
    <div class="stat-pill">
      <div class="stat-icon si-plan"><i class="fas fa-clock"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= number_format($stats['planning'] ?? 0) ?></div>
        <div class="stat-lbl">PLAN TO WATCH</div>
      </div>
    </div>
    <div class="stat-pill">
      <div class="stat-icon si-watch"><i class="fas fa-play-circle"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= number_format($stats['watching'] ?? 0) ?></div>
        <div class="stat-lbl">WATCHING NOW</div>
      </div>
    </div>
    <div class="stat-pill">
      <div class="stat-icon si-done"><i class="fas fa-check-circle"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= number_format($stats['completed'] ?? 0) ?></div>
        <div class="stat-lbl">COMPLETED</div>
      </div>
    </div>
    <div class="stat-pill">
      <div class="stat-icon si-drop"><i class="fas fa-times-circle"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= number_format($stats['dropped'] ?? 0) ?></div>
        <div class="stat-lbl">DROPPED</div>
      </div>
    </div>
  </div>

  <div class="filter-bar">
    <?php
    $filter_display = [
      'All' => 'All',
      'Plan to Watch' => 'Plan to Watch',
      'Currently Watching' => 'Watching Now',
      'Completed' => 'Completed',
      'Dropped' => 'Dropped'
    ];
    foreach($filter_display as $filter_value => $display_name):
      $active = ($filter_status === $filter_value) ? 'active' : '';
      $url = 'plan-to-watch.php?status=' . urlencode($filter_value);
    ?>
    <a href="<?= $url ?>" class="filter-btn <?= $active ?>"><?= $display_name ?></a>
    <?php endforeach; ?>
  </div>

  <div class="drama-grid">
    <?php if($dramas && $dramas->num_rows > 0): ?>
      <?php while($d = $dramas->fetch_assoc()):
        $s = $d['status'];
        $statusClass = match($s) {
          'Plan to Watch'      => 'badge-status-plan',
          'Currently Watching' => 'badge-status-watch',
          'Completed'          => 'badge-status-done',
          'Dropped'            => 'badge-status-drop',
          default              => 'badge-status-plan'
        };
        $statusIcon = match($s) {
          'Plan to Watch'      => 'fa-clock',
          'Currently Watching' => 'fa-play',
          'Completed'          => 'fa-check',
          'Dropped'            => 'fa-times',
          default              => 'fa-clock'
        };
        $statusDisplay = match($s) {
          'Plan to Watch'      => '📋 Plan to Watch',
          'Currently Watching' => '🎬 Watching Now',
          'Completed'          => '✅ Completed',
          'Dropped'            => '❌ Dropped',
          default              => $s
        };
        $prioClass = 'badge-prio-' . strtolower($d['priority'] ?? 'normal');
        $rating    = (float)($d['rating'] ?? 0);
      ?>
      <div class="drama-card" data-status="<?= htmlspecialchars($s) ?>" data-id="<?= $d['id'] ?>">

        <div class="card-top">
          <div class="card-title-block">
            <div class="card-title"><?= htmlspecialchars($d['drama_title']) ?></div>
            <div class="card-meta">
              <?php if($d['genre']): ?><span><i class="fas fa-tag"></i> <?= htmlspecialchars($d['genre']) ?></span><?php endif; ?>
              <?php if($d['year']): ?><span><i class="fas fa-calendar"></i> <?= $d['year'] ?></span><?php endif; ?>
              <span><i class="fas fa-clock"></i> Added <?= date('M d', strtotime($d['added_date'])) ?></span>
            </div>
          </div>
          <div class="card-actions">
            <form method="POST" style="display:contents" onsubmit="return confirm('Remove from watchlist?')">
              <input type="hidden" name="action"  value="delete">
              <input type="hidden" name="ptw_id"  value="<?= $d['id'] ?>">
              <button type="submit" class="icon-btn del" title="Remove"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>

        <div class="badge-row">
          <span class="badge <?= $statusClass ?>"><i class="fas <?= $statusIcon ?>"></i> <?= $statusDisplay ?></span>
          <span class="badge <?= $prioClass ?>"><i class="fas fa-flag"></i> <?= htmlspecialchars($d['priority'] ?? 'Normal') ?></span>
        </div>

        <!-- Quick Status Switcher - User-friendly button labels -->
        <div class="status-switcher">
          <form method="POST" style="display:contents" class="status-form-<?= $d['id'] ?>">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="ptw_id" value="<?= $d['id'] ?>">
            <input type="hidden" name="new_status" value="Plan to Watch">
            <button type="submit" class="status-btn <?= ($s === 'Plan to Watch') ? 'active' : '' ?>" data-s="Plan to Watch">
              📋 Plan
            </button>
          </form>
          <form method="POST" style="display:contents" class="status-form-<?= $d['id'] ?>">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="ptw_id" value="<?= $d['id'] ?>">
            <input type="hidden" name="new_status" value="Currently Watching">
            <button type="submit" class="status-btn <?= ($s === 'Currently Watching') ? 'active' : '' ?>" data-s="Currently Watching">
              🎬 Watching Now
            </button>
          </form>
          <form method="POST" style="display:contents" class="status-form-<?= $d['id'] ?>">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="ptw_id" value="<?= $d['id'] ?>">
            <input type="hidden" name="new_status" value="Completed">
            <button type="submit" class="status-btn <?= ($s === 'Completed') ? 'active' : '' ?>" data-s="Completed">
              ✅ Completed
            </button>
          </form>
          <form method="POST" style="display:contents" class="status-form-<?= $d['id'] ?>">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="ptw_id" value="<?= $d['id'] ?>">
            <input type="hidden" name="new_status" value="Dropped">
            <button type="submit" class="status-btn <?= ($s === 'Dropped') ? 'active' : '' ?>" data-s="Dropped">
              ❌ Dropped
            </button>
          </form>
        </div>

        <!-- Star Rating -->
        <div class="star-row">
          <div class="stars" data-id="<?= $d['id'] ?>">
            <?php for($i=1;$i<=10;$i++): ?>
            <span class="star <?= ($i <= $rating) ? 'lit' : '' ?>"
                  onclick="rateCard(<?= $d['id'] ?>, <?= $i ?>)"
                  title="<?= $i ?>/10">★</span>
            <?php endfor; ?>
          </div>
          <span class="rating-val"><?= $rating > 0 ? number_format($rating,1).'/10' : 'Not rated' ?></span>
        </div>

        <?php if(!empty($d['notes'])): ?>
        <div class="card-notes"><i class="fas fa-sticky-note" style="color:var(--gold);margin-right:6px"></i><?= htmlspecialchars($d['notes']) ?></div>
        <?php endif; ?>

      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty">
        <i class="fas fa-bookmark"></i>
        <h3>Your watchlist is empty</h3>
        <p>Click <strong>Add Drama</strong> above to start tracking K-Dramas you want to watch.</p>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Add Drama Modal -->
<div class="modal-overlay" id="addModal" onclick="closeOnBg(event)">
  <div class="modal">
    <h2><i class="fas fa-plus-circle"></i> Add to Watchlist</h2>
    <form method="POST" id="addDramaForm">
      <input type="hidden" name="action" value="add">

      <div class="form-group">
        <label>Drama Title *</label>
        <input type="text" name="drama_title" placeholder="e.g. Crash Landing on You" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Genre</label>
          <input type="text" name="genre" placeholder="e.g. Romance, Thriller">
        </div>
        <div class="form-group">
          <label>Year</label>
          <input type="number" name="year" value="<?= date('Y') ?>" min="1990" max="<?= date('Y')+2 ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Priority</label>
        <select name="priority">
          <option value="High">🔴 High — Watch ASAP</option>
          <option value="Normal" selected>🟡 Normal — Whenever</option>
          <option value="Low">🔵 Low — Someday</option>
        </select>
      </div>

      <div class="form-group">
        <label>Notes (optional)</label>
        <textarea name="notes" placeholder="Why you want to watch it, where you heard about it…"></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-submit"><i class="fas fa-bookmark"></i> Add to Watchlist</button>
      </div>
    </form>
  </div>
</div>

<!-- Hidden rating forms -->
<div id="rating-forms" style="display:none">
  <?php
  $all_ids = $conn->query("SELECT id FROM plan_to_watch");
  while($r = $all_ids->fetch_assoc()): ?>
  <form method="POST" id="rate-form-<?= $r['id'] ?>">
    <input type="hidden" name="action" value="rate">
    <input type="hidden" name="ptw_id" value="<?= $r['id'] ?>">
    <input type="hidden" name="rating" id="rate-val-<?= $r['id'] ?>" value="0">
  </form>
  <?php endwhile; ?>
</div>

<script>
function openModal() { document.getElementById('addModal').classList.add('open'); }
function closeModal() { document.getElementById('addModal').classList.remove('open'); }
function closeOnBg(e) { if(e.target === e.currentTarget) closeModal(); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

function rateCard(id, val) {
  const form = document.getElementById('rate-form-'+id);
  if(!form) return;
  document.getElementById('rate-val-'+id).value = val;
  form.submit();
}
// Add loading state to status buttons
document.querySelectorAll('.status-form-PTW_ID').forEach(form => {
  // This is handled by the individual forms     