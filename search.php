<?php
include 'db.php'; // database connection

// Get search term
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search == '') {
    echo "<p style='text-align:center;color:white;'>Please enter a search term.</p>";
    exit;
}

// Escape string for security
$search = mysqli_real_escape_string($conn, $search);

// Search queries across multiple tables
$musicQuery = "
    SELECT 'music' AS type, music_id AS id, title AS name, thumbnail_img AS image, description
    FROM music 
    WHERE title LIKE '%$search%' OR description LIKE '%$search%'
";

$videoQuery = "
    SELECT 'video' AS type, video_id AS id, title AS name, thumbnail_img AS image, description
    FROM video 
    WHERE title LIKE '%$search%' OR description LIKE '%$search%'
";

$artistQuery = "
    SELECT 'artist' AS type, artist_id AS id, artist_name AS name, artist_image AS image, description
    FROM artist 
    WHERE artist_name LIKE '%$search%' OR description LIKE '%$search%'
";

$albumQuery = "
    SELECT 'album' AS type, album_id AS id, album_name AS name, cover_image AS image, description
    FROM album 
    WHERE album_name LIKE '%$search%' OR description LIKE '%$search%'
";

// Combine results using UNION
$sql = "($musicQuery) UNION ($videoQuery) UNION ($artistQuery) UNION ($albumQuery) LIMIT 50";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results - SolMusic</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: #121212;
      color: white;
      font-family: Arial, sans-serif;
    }
    .results-container {
      width: 90%;
      margin: 40px auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }
    .result-card {
      background: #1e1e1e;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      transition: 0.3s;
    }
    .result-card:hover {
      background: #272727;
      transform: translateY(-5px);
    }
    .result-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
    }
    .type {
      color: #f39c12;
      font-size: 14px;
      margin-top: 8px;
    }
    .name {
      font-size: 18px;
      margin: 10px 0;
      font-weight: bold;
    }
    .desc {
      font-size: 14px;
      color: #ccc;
    }
  </style>
</head>
<body>

<h2 style="text-align:center;">Search Results for "<span style="color:#f39c12;"><?php echo htmlspecialchars($search); ?></span>"</h2>

<div class="results-container">
<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $type = ucfirst($row['type']);
        $name = htmlspecialchars($row['name']);
        $desc = htmlspecialchars(substr($row['description'], 0, 100)) . '...';
        $image = $row['image'] ?: 'images/default.jpg';

        // Destination link based on type
        if ($row['type'] == 'music') {
            $link = "music.php?id=" . $row['id'];
        } elseif ($row['type'] == 'video') {
            $link = "video.php?id=" . $row['id'];
        } elseif ($row['type'] == 'artist') {
            $link = "artist.php?id=" . $row['id'];
        } else {
            $link = "album.php?id=" . $row['id'];
        }

        echo "
        <div class='result-card'>
            <a href='$link'><img src='$image' alt='$name'></a>
            <div class='type'>$type</div>
            <div class='name'>$name</div>
            <div class='desc'>$desc</div>
        </div>";
    }
} else {
    echo "<p style='text-align:center;color:#aaa;'>No results found.</p>";
}
?>
</div>

</body>
</html>
