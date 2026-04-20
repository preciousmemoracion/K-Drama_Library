<?php include "db.php"; ?>

<?php
$limit = 5;
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
} else {
    $sql = "SELECT * FROM dramas LIMIT $start, $limit";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>K-Drama Library</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

body {
    min-height: 100vh;
    background: #0f0c1a;
    background-image:
        radial-gradient(ellipse at 20% 20%, rgba(100, 60, 180, 0.25) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(0, 180, 150, 0.15) 0%, transparent 50%);
    color: #e8e0f0;
    padding: 30px 20px;
}

/* HEADER */
.header {
    text-align: center;
    margin-bottom: 32px;
}

.header h2 {
    font-size: 28px;
    font-weight: 600;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #c084fc, #00c9a7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header p {
    font-size: 13px;
    color: #8878a8;
    margin-top: 4px;
}

/* CONTAINER */
.container { width: 95%; max-width: 1100px; margin: auto; }

/* TOP BAR */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 12px;
    flex-wrap: wrap;
}

/* SEARCH */
.search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 50px;
    padding: 6px 6px 6px 16px;
    flex: 1;
    max-width: 420px;
}

.search-form input {
    background: transparent;
    border: none;
    outline: none;
    color: #e8e0f0;
    font-size: 14px;
    width: 100%;
}

.search-form input::placeholder { color: #6a5a8a; }

.search-form button {
    background: linear-gradient(135deg, #7c3aed, #00c9a7);
    border: none;
    border-radius: 50px;
    color: white;
    padding: 7px 18px;
    font-size: 13px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    white-space: nowrap;
}

.search-form button:hover { opacity: 0.85; }

.back-btn {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    color: #c084fc;
    padding: 7px 14px;
    border-radius: 50px;
    text-decoration: none;
    font-size: 13px;
    white-space: nowrap;
}

.back-btn:hover { background: rgba(255,255,255,0.13); }

/* ADD BUTTON */
a.add-btn {
    background: linear-gradient(135deg, #7c3aed, #00c9a7);
    color: white;
    padding: 9px 20px;
    text-decoration: none;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    letter-spacing: 0.3px;
}

a.add-btn:hover { opacity: 0.88; }

/* TABLE WRAPPER */
.table-wrapper {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 15px;
    overflow: hidden;
}

table { width: 100%; border-collapse: collapse; }

thead tr {
    background: rgba(124, 58, 237, 0.15);
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

th {
    padding: 14px 12px;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #9d7fc7;
}

td {
    padding: 14px 12px;
    text-align: center;
    font-size: 13px;
    color: #d0c4e8;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
}

tbody tr:last-child td { border-bottom: none; }

tbody tr {
    transition: background 0.2s;
}

tbody tr:hover {
    background: rgba(124, 58, 237, 0.08);
}

/* POSTER */
img.poster {
    width: 48px;
    height: 68px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid rgba(255,255,255,0.08);
    transition: transform 0.2s ease, border-color 0.2s ease;
    display: block;
    margin: auto;
}

img.poster:hover {
    transform: scale(1.1) translateY(-2px);
    border-color: #00c9a7;
}

/* GENRE BADGE */
.badge {
    display: inline-block;
    background: rgba(124, 58, 237, 0.2);
    border: 1px solid rgba(124, 58, 237, 0.35);
    color: #c084fc;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 500;
}

/* RATING */
.rating {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(0, 201, 167, 0.12);
    border: 1px solid rgba(0, 201, 167, 0.25);
    color: #00c9a7;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
}

/* ACTION BUTTONS */
.action-btn {
    padding: 5px 12px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
    transition: opacity 0.2s, transform 0.15s;
}

.action-btn:hover { opacity: 0.82; transform: scale(0.97); }

.edit {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    margin-right: 4px;
}

.delete { background: linear-gradient(135deg, #ef4444, #b91c1c); }

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #6a5a8a;
}

.empty-state .icon { font-size: 40px; margin-bottom: 12px; }
.empty-state p { font-size: 14px; }

/* PAGINATION */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 28px;
    flex-wrap: wrap;
}

.pagination a, .pagination span {
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.2s;
}

.page-num {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    color: #c0b0d8;
}

.page-num:hover { background: rgba(255,255,255,0.12); }

.active-page {
    background: linear-gradient(135deg, #7c3aed, #00c9a7);
    color: white;
    border: none;
}

.nav-btn {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #a090c8;
}

.nav-btn:hover { background: rgba(255,255,255,0.12); color: white; }

.disabled {
    opacity: 0.25 !important;
    pointer-events: none;
}

/* MODAL */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 1000;
    inset: 0;
    background: rgba(0,0,0,0.88);
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(6px);
}

.modal-overlay.active { display: flex; }

.modal-box {
    position: relative;
    text-align: center;
    animation: popIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
    from { transform: scale(0.75); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.modal-box img {
    max-width: 280px;
    max-height: 420px;
    border-radius: 14px;
    border: 2px solid rgba(0, 201, 167, 0.4);
    display: block;
}

.modal-title {
    margin-top: 14px;
    font-size: 15px;
    font-weight: 500;
    color: #e8e0f0;
}

.modal-close {
    position: absolute;
    top: -14px;
    right: -14px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #3b1a6e;
    border: 1px solid rgba(255,255,255,0.15);
    color: white;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: background 0.2s;
}

.modal-close:hover { background: #e74c3c; }

/* RESULTS COUNT */
.results-info {
    font-size: 12px;
    color: #6a5a8a;
    margin-bottom: 12px;
}

/* ===== NO SCROLL FIX (MERGED) ===== */

/* stop full page scrolling */
html, body {
    height: 100%;
    overflow: hidden;
}

/* make layout fit screen vertically */
body {
    display: flex;
    flex-direction: column;
}

/* allow main container to take available height */
.container {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* prevent table section from expanding beyond screen */
.table-wrapper {
    flex: 1;
    overflow: hidden;
}

/* compress table so it fits screen */
th, td {
    padding: 8px 6px;
    font-size: 12px;
}

/* make poster smaller for tighter rows */
img.poster {
    width: 40px;
    height: 55px;
}

/* push pagination to bottom nicely */
.pagination {
    margin-top: auto;
}

</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>K-Drama Verse</h2>
        <p>Browse and manage your drama collection</p>
    </div>

    <div class="top-bar">

        <form method="GET" class="search-form">
            <input type="text" name="search"
                placeholder="Search title, genre, year..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="back-btn">✕ Clear</a>
            <?php endif; ?>
        </form>

        <a href="add.php" class="add-btn">+ Add Drama</a>

    </div>

    <?php if (!empty($search)): ?>
        <p class="results-info">Showing results for: <strong style="color:#c084fc">"<?php echo htmlspecialchars($search); ?>"</strong></p>
    <?php endif; ?>

    <div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>Poster</th>
            <th>Title</th>
            <th>Genre</th>
            <th>Episodes</th>
            <th>Year</th>
            <th>Rating</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>

        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {

                $image = !empty($row['image']) ? basename($row['image']) : 'default.jpg';
                $imagePath = "img/" . $image;

                if (!file_exists(__DIR__ . "/img/" . $image)) {
                    $imagePath = "img/default.jpg";
                }
        ?>

        <tr>
            <td>
                <img class="poster"
                    src="<?php echo htmlspecialchars($imagePath); ?>"
                    alt="<?php echo htmlspecialchars($row['title']); ?>"
                    onclick="openModal('<?php echo htmlspecialchars($imagePath); ?>', '<?php echo htmlspecialchars($row['title']); ?>')">
            </td>
            <td style="font-weight:500; color:#e8e0f0;"><?php echo $row['title']; ?></td>
            <td><span class="badge"><?php echo $row['genre']; ?></span></td>
            <td><?php echo $row['episodes']; ?> eps</td>
            <td><?php echo $row['released_year']; ?></td>
            <td><span class="rating">⭐ <?php echo $row['rating']; ?></span></td>
            <td>
                <a class="action-btn edit" href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a class="action-btn delete"
                    href="delete.php?id=<?php echo $row['id']; ?>"
                    onclick="return confirmDelete()">Delete</a>
            </td>
        </tr>

        <?php
            }
        } else {
            echo "<tr><td colspan='7'>
                    <div class='empty-state'>
                        <div class='icon'>🎭</div>
                        <p>No dramas found. Try a different search.</p>
                    </div>
                  </td></tr>";
        }
        ?>

        </tbody>
    </table>
    </div>

    <!-- PAGINATION -->
    <div class="pagination">

    <?php
    $query = !empty($search) ? "&search=" . urlencode($search) : '';

    if (!empty($search)) {
        $count_sql = "SELECT COUNT(*) as total FROM dramas 
                      WHERE title LIKE '%$search%' 
                      OR genre LIKE '%$search%' 
                      OR episodes LIKE '%$search%'
                      OR released_year LIKE '%$search%'
                      OR rating LIKE '%$search%'";
    } else {
        $count_sql = "SELECT COUNT(*) as total FROM dramas";
    }

    $total_result = $conn->query($count_sql);
    $total_row    = $total_result->fetch_assoc();
    $total_pages  = ceil($total_row['total'] / $limit);

    echo "<a class='nav-btn" . ($page == 1 ? " disabled" : "") . "' href='?page=1$query'>« First</a>";

    if ($page > 1)
        echo "<a class='nav-btn' href='?page=".($page-1)."$query'>‹ Prev</a>";

    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page)
            echo "<span class='active-page'>$i</span>";
        else
            echo "<a class='page-num' href='?page=$i$query'>$i</a>";
    }

    if ($page < $total_pages)
        echo "<a class='nav-btn' href='?page=".($page+1)."$query'>Next ›</a>";

    echo "<a class='nav-btn" . ($page == $total_pages ? " disabled" : "") . "' href='?page=$total_pages$query'>Last »</a>";
    ?>

    </div>

</div>

<!-- MODAL -->
<div id="imageModal" class="modal-overlay" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="closeModal()">×</button>
        <img id="modalImg" src="" alt="">
        <p class="modal-title" id="modalTitle"></p>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this drama?");
}

function openModal(src, title) {
    document.getElementById("modalImg").src    = src;
    document.getElementById("modalImg").alt    = title;
    document.getElementById("modalTitle").textContent = title;
    document.getElementById("imageModal").classList.add("active");
}

function closeModal() {
    document.getElementById("imageModal").classList.remove("active");
}

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") closeModal();
});
</script>

</body>
</html>