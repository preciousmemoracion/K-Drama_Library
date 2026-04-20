<?php include "db.php";

if(isset($_POST['submit'])){

    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $episodes = $_POST['episodes'];
    $year = $_POST['released_year'];
    $rating = $_POST['rating'];

    // IMAGE UPLOAD
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    $folder = "uploads/" . $image;

    // move image to uploads folder
    move_uploaded_file($tmp, $folder);

    // insert into database
    $conn->query("INSERT INTO dramas (title, genre, episodes, released_year, rating, image)
                  VALUES ('$title','$genre','$episodes','$year','$rating','$image')");

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add K-Drama</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(to right, #1f1c2c, #928dab);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .form-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            width: 350px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 8px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background: #00c9a7;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
        }

        .btn:hover {
            background: #00a389;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #ddd;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="form-container">

    <h2>➕ Add K-Drama</h2>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="title" placeholder="Title" required>
        <input type="text" name="genre" placeholder="Genre">
        <input type="number" name="episodes" placeholder="Episodes">
        <input type="number" name="released_year" placeholder="Release Year">
        <input type="number" step="0.1" name="rating" placeholder="Rating (e.g. 8.5)">

        <!-- NEW IMAGE INPUT -->
        <input type="file" name="image" required>

        <button class="btn" name="submit">Add Drama</button>

    </form>

    <a href="index.php" class="back">⬅ Back to Library</a>

</div>

</body>
</html>