<?php include "db.php"; ?>
<?php
// Start session for user preferences
session_start();

// Handle download request from index.php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drama_id'])) {
    $drama_id = (int)$_POST['drama_id'];
    $quality = $conn->real_escape_string($_POST['quality'] ?? '1080p');
    $language = $conn->real_escape_string($_POST['language'] ?? 'English');
    $size = $conn->real_escape_string($_POST['size'] ?? '~8 GB');
    
    // Fetch drama details
    $drama_query = $conn->query("SELECT * FROM dramas WHERE id = $drama_id");
    $drama = $drama_query->fetch_assoc();
    
    if($drama) {
        // Record download in database
        $ip = $_SERVER['REMOTE_ADDR'];
        $download_date = date('Y-m-d H:i:s');
        
        $insert = $conn->query("INSERT INTO downloads (drama_id, drama_title, quality, size, language, download_date, ip_address, status) 
                                VALUES ($drama_id, '{$conn->real_escape_string($drama['title'])}', '$quality', '$size', '$language', '$download_date', '$ip', 'completed')");
        
        if($insert) {
            // Redirect back to index with success message
            header("Location: index.php?download_success=1&title=" . urlencode($drama['title']));
            exit;
        } else {
            // Error recording download
            header("Location: index.php?download_error=1");
            exit;
        }
    } else {
        header("Location: index.php?download_error=1");
        exit;
    }
}

// Handle delete download record
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM downloads WHERE id = $delete_id");
    header("Location: download.php?deleted=1");
    exit;
}

// Get all downloads with filters
$filter_quality = isset($_GET['quality']) ? $conn->real_escape_string($_GET['quality']) : '';
$filter_language = isset($_GET['lang']) ? $conn->real_escape_string($_GET['lang']) : '';
$search_drama = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = [];
if($filter_quality) $where[] = "quality = '$filter_quality'";
if($filter_language) $where[] = "language = '$filter_language'";
if($search_drama) $where[] = "drama_title LIKE '%$search_drama%'";

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$count_query = $conn->query("SELECT COUNT(*) as total FROM downloads $where_clause");
$total_downloads = $count_query ? $count_query->fetch_assoc()['total'] : 0;
$total_pages = $total_downloads > 0 ? ceil($total_downloads / $limit) : 1;

$downloads_query = $conn->query("SELECT * FROM downloads $where_clause ORDER BY download_date DESC LIMIT $offset, $limit");

// Get statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    COUNT(DISTINCT drama_id) as unique_dramas,
    SUM(CASE WHEN DATE(download_date) = CURDATE() THEN 1 ELSE 0 END) as today,
    SUM(CASE WHEN WEEK(download_date) = WEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
    SUM(CASE WHEN MONTH(download_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month
    FROM downloads");
    
$stats_data = $stats ? $stats->fetch_assoc() : ['total'=>0, 'unique_dramas'=>0, 'today'=>0, 'this_week'=>0, 'this_month'=>0];

$quality_stats = $conn->query("SELECT quality, COUNT(*) as count FROM downloads GROUP BY quality ORDER BY count DESC");
$language_stats = $conn->query("SELECT language, COUNT(*) as count FROM downloads GROUP BY language ORDER BY count DESC");
$top_dramas = $conn->query("SELECT drama_title, COUNT(*) as count FROM downloads GROUP BY drama_title ORDER BY count DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Downloads — KDramaVerse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600;1,700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
:root{
  --red:#E8173A;--red2:#B50F2B;--redglow:rgba(232,23,58,.18);--redsoft:rgba(232,23,58,.08);
  --gold:#F0B429;--goldsoft:rgba(240,180,41,.1);
  --ink:#070709;--bg:#0D0D12;--s1:#131318;--s2:#1A1A22;--s3:#22222D;
  --t0:#F2F0FF;--t1:#8E8CAA;--t2:#48475E;
  --border:rgba(255,255,255,.06);--bhi:rgba(255,255,255,.11);
  --glass:rgba(13,13,18,.85);
  --green:#00C853;--greensoft:rgba(0,200,83,.1);
  --blue:#2196F3;--bluesoft:rgba(33,150,243,.1);
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
.container{max-width:1400px;margin:0 auto;padding:90px 5% 40px}
.page-header{margin-bottom:32px}
.page-header h1{font-family:'Cormorant Garamond',serif;font-size:42px;margin-bottom:8px}
.page-header p{color:var(--t2)}
.stats-grid{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;
  margin-bottom:40px;
}
.stat-card{
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);border-radius:20px;
  padding:22px;text-align:center;transition:all .3s;
}
.stat-card:hover{transform:translateY(-4px);border-color:var(--bhi)}
.stat-card i{font-size:36px;color:var(--red);margin-bottom:12px;display:block}
.stat-number{font-size:36px;font-weight:700;color:var(--t0)}
.stat-label{font-size:12px;color:var(--t2);margin-top:6px}
.filters-bar{
  background:var(--s1);border:1px solid var(--border);border-radius:16px;
  padding:20px;margin-bottom:30px;
  display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:15px;
}
.search-box{
  display:flex;align-items:center;flex:1;min-width:200px;
  background:var(--s2);border:1px solid var(--border);border-radius:10px;
  overflow:hidden;
}
.search-box input{
  flex:1;background:none;border:none;outline:none;
  padding:12px 16px;color:var(--t0);font-size:14px;
}
.search-box button{
  background:var(--red);border:none;color:#fff;
  padding:12px 20px;cursor:pointer;transition:background .2s;
}
.filter-group{display:flex;gap:10px;flex-wrap:wrap}
.filter-select{
  background:var(--s2);border:1px solid var(--border);
  padding:10px 16px;border-radius:10px;color:var(--t0);
  font-family:'DM Sans',sans-serif;cursor:pointer;
}
.filter-select:focus{outline:none;border-color:var(--red)}
.clear-filters{
  background:rgba(255,255,255,.05);border:1px solid var(--border);
  padding:10px 18px;border-radius:10px;color:var(--t1);
  text-decoration:none;transition:all .2s;display:inline-flex;align-items:center;gap:6px;
}
.clear-filters:hover{background:var(--redsoft);border-color:var(--red);color:var(--t0)}
.downloads-table{
  background:var(--s1);border:1px solid var(--border);border-radius:20px;
  overflow-x:auto;
}
table{width:100%;border-collapse:collapse}
th,td{padding:18px 16px;text-align:left;border-bottom:1px solid var(--border)}
th{color:var(--t2);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:1px}
td{color:var(--t1);vertical-align:middle}
tr:hover{background:rgba(255,255,255,.02)}
.quality-badge{
  display:inline-block;padding:4px 12px;border-radius:20px;
  font-size:11px;font-weight:700;
}
.quality-4k{background:rgba(240,180,41,.2);color:var(--gold)}
.quality-1080p{background:rgba(232,23,58,.2);color:var(--red)}
.quality-720p{background:rgba(255,255,255,.1);color:var(--t1)}
.status-completed{color:var(--green)}
.drama-link{
  color:var(--t0);text-decoration:none;font-weight:600;
  transition:color .2s;
}
.drama-link:hover{color:var(--red)}
.delete-btn{
  background:rgba(232,23,58,.1);border:1px solid rgba(232,23,58,.2);
  color:var(--red);padding:6px 12px;border-radius:8px;
  cursor:pointer;font-size:12px;transition:all .2s;
  display:inline-flex;align-items:center;gap:4px;
}
.delete-btn:hover{background:var(--red);color:#fff;border-color:var(--red)}
.empty-state{
  text-align:center;padding:80px 20px;
}
.empty-state i{font-size:80px;margin-bottom:24px;opacity:.3}
.empty-state h3{font-size:28px;margin-bottom:12px;color:var(--t1);font-family:'Cormorant Garamond',serif}
.empty-state p{color:var(--t2);margin-bottom:24px}
.btn-primary{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--red);color:#fff;padding:12px 28px;
  border-radius:12px;text-decoration:none;font-weight:600;
  transition:all .2s;
}
.btn-primary:hover{background:var(--red2);transform:translateY(-2px)}
.pagination{
  display:flex;justify-content:center;align-items:center;
  gap:6px;margin-top:32px;flex-wrap:wrap;
}
.page-link{
  padding:10px 16px;border-radius:8px;text-decoration:none;
  color:var(--t1);background:var(--s2);border:1px solid var(--border);
  transition:all .2s;font-size:13px;
}
.page-link:hover{background:var(--s3);color:var(--t0);border-color:var(--bhi)}
.page-link.active{background:var(--red);color:#fff;border-color:var(--red)}
.top-dramas{
  background:linear-gradient(135deg, var(--s1), var(--s2));
  border:1px solid var(--border);border-radius:16px;
  padding:20px;margin-bottom:30px;
}
.top-dramas h3{margin-bottom:16px;font-size:18px;display:flex;align-items:center;gap:8px}
.top-list{display:flex;flex-wrap:wrap;gap:12px}
.top-item{
  background:var(--s3);padding:10px 18px;border-radius:10px;
  display:flex;align-items:center;gap:12px;
  transition:transform .2s;
}
.top-item:hover{transform:translateX(4px)}
.top-rank{color:var(--gold);font-weight:700;font-size:18px}
.top-title{font-weight:500}
.top-count{color:var(--green);font-size:12px}
.stats-charts{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:20px;margin-bottom:30px;
}
.chart-card{
  background:var(--s1);border:1px solid var(--border);border-radius:16px;
  padding:20px;
}
.chart-card h4{margin-bottom:16px;font-size:14px;color:var(--t2)}
.chart-item{
  display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid var(--border);
}
.chart-label{display:flex;align-items:center;gap:8px}
.chart-bar{
  background:var(--red);height:4px;border-radius:4px;
  transition:width .3s;
}
@media(max-width:768px){
  th,td{padding:12px 10px;font-size:12px}
  .stats-grid{grid-template-columns:1fr 1fr}
  .filters-bar{flex-direction:column}
  .filter-group{width:100%}
  .filter-select{flex:1}
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
    <a href="download.php" class="active"><i class="fas fa-download"></i> My Downloads</a>
  </div>
  <a href="add.php" class="nav-add" style="background:var(--red);color:#fff;padding:8px 20px;border-radius:10px;text-decoration:none;font-size:13px"><i class="fas fa-plus"></i> Add Drama</a>
</nav>

<div class="container">
  <div class="page-header">
    <h1><i class="fas fa-download" style="color:var(--red)"></i> My Download History</h1>
    <p>Track and manage all your downloaded K-Dramas</p>
  </div>

  <!-- Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <i class="fas fa-database"></i>
      <div class="stat-number"><?php echo number_format($stats_data['total'] ?? 0); ?></div>
      <div class="stat-label">Total Downloads</div>
    </div>
    <div class="stat-card">
      <i class="fas fa-film"></i>
      <div class="stat-number"><?php echo number_format($stats_data['unique_dramas'] ?? 0); ?></div>
      <div class="stat-label">Unique Dramas</div>
    </div>
    <div class="stat-card">
      <i class="fas fa-calendar-day"></i>
      <div class="stat-number"><?php echo number_format($stats_data['today'] ?? 0); ?></div>
      <div class="stat-label">Today</div>
    </div>
    <div class="stat-card">
      <i class="fas fa-chart-line"></i>
      <div class="stat-number"><?php echo number_format($stats_data['this_week'] ?? 0); ?></div>
      <div class="stat-label">This Week</div>
    </div>
  </div>

  <!-- Top Dramas -->
  <?php if($top_dramas && $top_dramas->num_rows > 0): ?>
  <div class="top-dramas">
    <h3><i class="fas fa-trophy" style="color:var(--gold)"></i> Most Downloaded Dramas</h3>
    <div class="top-list">
      <?php $rank = 1; while($top = $top_dramas->fetch_assoc()): ?>
      <div class="top-item">
        <span class="top-rank">#<?php echo $rank++; ?></span>
        <span class="top-title"><?php echo htmlspecialchars($top['drama_title']); ?></span>
        <span class="top-count">(<?php echo $top['count']; ?> downloads)</span>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Quality & Language Stats -->
  <div class="stats-charts">
    <div class="chart-card">
      <h4><i class="fas fa-chart-pie"></i> By Quality</h4>
      <?php if($quality_stats && $quality_stats->num_rows > 0): 
        $qual_data = $quality_stats->fetch_all(MYSQLI_ASSOC);
        $total_qual = array_sum(array_column($qual_data, 'count'));
      ?>
        <?php foreach($qual_data as $q): ?>
          <?php $percent = $total_qual > 0 ? ($q['count'] / $total_qual) * 100 : 0; ?>
          <div class="chart-item">
            <div class="chart-label">
              <span class="quality-badge quality-<?php echo strtolower($q['quality']); ?>"><?php echo $q['quality']; ?></span>
            </div>
            <div style="flex:1;margin:0 12px">
              <div class="chart-bar" style="width:<?php echo $percent; ?>%;"></div>
            </div>
            <span><?php echo $q['count']; ?> (<?php echo round($percent); ?>%)</span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="chart-item">No data available</div>
      <?php endif; ?>
    </div>
    <div class="chart-card">
      <h4><i class="fas fa-language"></i> By Subtitle Language</h4>
      <?php if($language_stats && $language_stats->num_rows > 0):
        $lang_data = $language_stats->fetch_all(MYSQLI_ASSOC);
        $total_lang = array_sum(array_column($lang_data, 'count'));
      ?>
        <?php foreach($lang_data as $l): ?>
          <?php $percent = $total_lang > 0 ? ($l['count'] / $total_lang) * 100 : 0; ?>
          <div class="chart-item">
            <div class="chart-label">
              <i class="fas fa-flag"></i> <?php echo $l['language']; ?>
            </div>
            <div style="flex:1;margin:0 12px">
              <div class="chart-bar" style="width:<?php echo $percent; ?>%;background:var(--blue)"></div>
            </div>
            <span><?php echo $l['count']; ?> (<?php echo round($percent); ?>%)</span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="chart-item">No data available</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filters -->
  <div class="filters-bar">
    <form method="GET" class="search-box" style="display:flex;flex:1">
      <input type="text" name="search" placeholder="Search by drama title..." value="<?php echo htmlspecialchars($search_drama); ?>">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div class="filter-group">
      <select name="quality" class="filter-select" onchange="this.form.submit()" form="filterForm">
        <option value="">All Qualities</option>
        <option value="4K" <?php echo $filter_quality=='4K'?'selected':''; ?>>4K Ultra HD</option>
        <option value="1080p" <?php echo $filter_quality=='1080p'?'selected':''; ?>>1080p Full HD</option>
        <option value="720p" <?php echo $filter_quality=='720p'?'selected':''; ?>>720p HD</option>
      </select>
      <select name="lang" class="filter-select" onchange="this.form.submit()" form="filterForm">
        <option value="">All Languages</option>
        <option value="English" <?php echo $filter_language=='English'?'selected':''; ?>>English</option>
        <option value="Filipino" <?php echo $filter_language=='Filipino'?'selected':''; ?>>Filipino</option>
        <option value="Korean" <?php echo $filter_language=='Korean'?'selected':''; ?>>Korean</option>
        <option value="Chinese" <?php echo $filter_language=='Chinese'?'selected':''; ?>>Chinese</option>
        <option value="Japanese" <?php echo $filter_language=='Japanese'?'selected':''; ?>>Japanese</option>
      </select>
      <?php if($filter_quality || $filter_language || $search_drama): ?>
      <a href="download.php" class="clear-filters"><i class="fas fa-times"></i> Clear Filters</a>
      <?php endif; ?>
    </div>
    <form id="filterForm" method="GET" style="display:none"></form>
  </div>

  <!-- Downloads Table -->
  <div class="downloads-table">
    <table>
      <thead>
        <tr>
          <th>Drama Title</th>
          <th>Quality</th>
          <th>Size</th>
          <th>Subtitles</th>
          <th>Download Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if($downloads_query && $downloads_query->num_rows > 0): ?>
          <?php while($dl = $downloads_query->fetch_assoc()): ?>
          <tr>
            <td>
              <a href="index.php?id=<?php echo $dl['drama_id']; ?>" class="drama-link">
                <i class="fas fa-play-circle"></i> <?php echo htmlspecialchars($dl['drama_title']); ?>
              </a>
            </td>
            <td>
              <span class="quality-badge quality-<?php echo strtolower($dl['quality']); ?>">
                <?php echo htmlspecialchars($dl['quality']); ?>
              </span>
            </td>
            <td><?php echo htmlspecialchars($dl['size']); ?></td>
            <td><i class="fas fa-language"></i> <?php echo htmlspecialchars($dl['language']); ?></td>
            <td>
              <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($dl['download_date'])); ?><br>
              <small style="color:var(--t2)"><?php echo date('h:i A', strtotime($dl['download_date'])); ?></small>
            </td>
            <td>
              <span class="status-completed">
                <i class="fas fa-check-circle"></i> Completed
              </span>
            </td>
            <td>
              <button class="delete-btn" onclick="deleteDownload(<?php echo $dl['id']; ?>, '<?php echo addslashes($dl['drama_title']); ?>')">
                <i class="fas fa-trash-alt"></i> Remove
              </button>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <i class="fas fa-download"></i>
                <h3>No Downloads Yet</h3>
                <p>You haven't downloaded any dramas yet. Start building your collection!</p>
                <a href="index.php" class="btn-primary"><i class="fas fa-film"></i> Browse Dramas</a>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($total_pages > 1): ?>
  <div class="pagination">
    <?php if($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search_drama); ?>&quality=<?php echo urlencode($filter_quality); ?>&lang=<?php echo urlencode($filter_language); ?>" class="page-link"><i class="fas fa-chevron-left"></i> Previous</a>
    <?php endif; ?>
    
    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for($i = $start; $i <= $end; $i++):
    ?>
      <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_drama); ?>&quality=<?php echo urlencode($filter_quality); ?>&lang=<?php echo urlencode($filter_language); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if($page < $total_pages): ?>
      <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search_drama); ?>&quality=<?php echo urlencode($filter_quality); ?>&lang=<?php echo urlencode($filter_language); ?>" class="page-link">Next <i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function deleteDownload(id, title) {
    if(confirm(`Are you sure you want to remove "${title}" from your download history?`)) {
        window.location.href = `download.php?delete=${id}`;
    }
}

// Auto-submit filters when select changes
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set(this.name, this.value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    });
});

// Show success message if download was recorded
<?php if(isset($_GET['deleted'])): ?>
alert('Download record removed successfully!');
<?php endif; ?>
</script>
</body>
</html>