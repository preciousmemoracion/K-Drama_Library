<?php include "db.php";

$id = $_GET['id'];
$data = $conn->query("SELECT * FROM dramas WHERE id=$id")->fetch_assoc();

if(isset($_POST['update'])){
    $conn->query("UPDATE dramas SET
        title='{$_POST['title']}',
        genre='{$_POST['genre']}',
        episodes='{$_POST['episodes']}',
        released_year='{$_POST['released_year']}',
        rating='{$_POST['rating']}'
        WHERE id=$id");

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Drama</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background: #1f2937;
            padding: 30px;
            border-radius: 15px;
            width: 350px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            display: block;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 8px;
            border: none;
            outline: none;
        }

        input:focus {
            box-shadow: 0 0 5px #6366f1;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background: #6366f1;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #4f46e5;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #9ca3af;
            text-decoration: none;
        }

        .back:hover {
            color: white;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>🎬 Update Drama</h2>

    <form method="POST">
        <label>Title</label>
        <input type="text" name="title" value="<?= $data['title'] ?>" required>

        <label>Genre</label>
        <input type="text" name="genre" value="<?= $data['genre'] ?>" required>

        <label>Episodes</label>
        <input type="number" name="episodes" value="<?= $data['episodes'] ?>" required>

        <label>Year</label>
        <input type="number" name="released_year" value="<?= $data['released_year'] ?>" required>

        <label>Rating</label>
        <input type="number" step="0.1" name="rating" value="<?= $data['rating'] ?>" required>

        <button name="update">Update Drama</button>
    </form>

    <a href="index.php" class="back">← Back to List</a>
</div>

</body>
</html>