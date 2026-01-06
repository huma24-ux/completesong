<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

// ✅ Step 1: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Please log in first']);
  exit;
}

// ✅ Step 2: Handle POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  $user_id = $_SESSION['user_id'];
  $video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
  $rating_value = isset($_POST['rating_value']) ? intval($_POST['rating_value']) : 0;
  $content_type = 'video';

  // ✅ Validate input
  if ($video_id <= 0 || $rating_value < 1 || $rating_value > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value']);
    exit;
  }

  // ✅ Check if already rated
  $check_sql = "SELECT rating_id FROM rating WHERE user_id = ? AND content_id = ? AND content_type = ?";
  $check_stmt = $conn->prepare($check_sql);

  if (!$check_stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error (check): ' . $conn->error]);
    exit;
  }

  $check_stmt->bind_param("iis", $user_id, $video_id, $content_type);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result && $check_result->num_rows > 0) {
    // ✅ Update existing rating
    $update_sql = "UPDATE rating SET rating_value = ?, created_at = NOW() WHERE user_id = ? AND content_id = ? AND content_type = ?";
    $update_stmt = $conn->prepare($update_sql);

    if (!$update_stmt) {
      echo json_encode(['success' => false, 'message' => 'SQL Error (update): ' . $conn->error]);
      exit;
    }

    $update_stmt->bind_param("iiis", $rating_value, $user_id, $video_id, $content_type);
    $update_stmt->execute();

  } else {
    // ✅ Insert new rating
    $insert_sql = "INSERT INTO rating (user_id, content_type, content_id, rating_value, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);

    if (!$insert_stmt) {
      echo json_encode(['success' => false, 'message' => 'SQL Error (insert): ' . $conn->error]);
      exit;
    }

    $insert_stmt->bind_param("isii", $user_id, $content_type, $video_id, $rating_value);
    $insert_stmt->execute();
  }

  // ✅ Fetch updated average and count
  $avg_sql = "SELECT ROUND(AVG(rating_value),1) AS avg_rating, COUNT(*) AS rating_count 
              FROM rating 
              WHERE content_type = ? AND content_id = ?";
  $avg_stmt = $conn->prepare($avg_sql);

  if (!$avg_stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error (avg): ' . $conn->error]);
    exit;
  }

  $avg_stmt->bind_param("si", $content_type, $video_id);
  $avg_stmt->execute();
  $avg_result = $avg_stmt->get_result()->fetch_assoc();

  // ✅ Return success JSON
  echo json_encode([
    'success' => true,
    'message' => 'Rating saved successfully!',
    'new_rating' => floatval($avg_result['avg_rating']),
    'rating_count' => intval($avg_result['rating_count'])
  ]);
  exit;

} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>