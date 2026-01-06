<?php
// Start session and include database connection at the very top
session_start();
include 'db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// 1. HANDLE RATING SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate') {
    header('Content-Type: application/json');
    
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Please log in to rate songs.']);
        exit;
    }
    
    $songId = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    
    if ($songId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid song ID']);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating value. Please rate between 1 and 5.']);
        exit;
    }
    
    try {
        // Check if user has already rated this song
        $checkSql = "SELECT rating_id FROM rating 
                     WHERE content_type = 'music' AND content_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $songId, $userId);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        
        if ($checkResult && $checkResult->num_rows > 0) {
            // Update existing rating
            $updateSql = "UPDATE rating SET rating_value = ?, created_at = NOW()
                          WHERE content_type = 'music' AND content_id = ? AND user_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("iii", $rating, $songId, $userId);
            $stmt->execute();
            $message = 'Your rating has been updated!';
        } else {
            // Insert new rating
            $insertSql = "INSERT INTO rating (rating_value, user_id, content_type, content_id, created_at) 
                          VALUES (?, ?, 'music', ?, NOW())";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("iii", $rating, $userId, $songId);
            $stmt->execute();
            $message = 'Thank you for rating!';
        }
        
        // Get updated rating data
        $ratingSql = "SELECT AVG(rating_value) as avg_rating, COUNT(*) as rating_count 
                      FROM rating 
                      WHERE content_type = 'music' AND content_id = ?";
        $stmt = $conn->prepare($ratingSql);
        $stmt->bind_param("i", $songId);
        $stmt->execute();
        $ratingResult = $stmt->get_result();
        $ratingData = $ratingResult->fetch_assoc();
        
        $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
        $ratingCount = $ratingData['rating_count'];
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'avg_rating' => $avgRating,
            'rating_count' => $ratingCount,
            'user_rating' => $rating
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log("Rating submission error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while submitting your rating. Please try again.'
        ]);
        exit;
    }
}

// 2. HANDLE REVIEW SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    header('Content-Type: application/json');
    
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
        exit;
    }
    
    $content_type = isset($_POST['content_type']) ? $_POST['content_type'] : '';
    $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    
    if (!empty($content_type) && $content_id > 0 && !empty($review_text)) {
        // Check if user already reviewed
        $check = $conn->prepare("SELECT review_id FROM review WHERE user_id = ? AND content_type = ? AND content_id = ?");
        $check->bind_param("isi", $userId, $content_type, $content_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing review
            $stmt = $conn->prepare("UPDATE review SET review_text = ?, updated_at = NOW() 
                                   WHERE user_id = ? AND content_type = ? AND content_id = ?");
            $stmt->bind_param("sisi", $review_text, $userId, $content_type, $content_id);
        } else {
            // Insert new review
            $stmt = $conn->prepare("INSERT INTO review (user_id, content_type, content_id, review_text, created_at, updated_at)
                                   VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("isis", $userId, $content_type, $content_id, $review_text);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
        $check->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    }
    exit;
}

// Fetch all songs from your table
$sql = "SELECT * FROM music ORDER BY created_at DESC";
$result = $conn->query($sql);

// Store songs in array for JavaScript access
$songs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get average rating for each song
        $ratingSql = "SELECT AVG(rating_value) as avg_rating, COUNT(*) as rating_count 
                      FROM rating 
                      WHERE content_type = 'music' AND content_id = ?";
        $stmt = $conn->prepare($ratingSql);
        $stmt->bind_param("i", $row['music_id']);
        $stmt->execute();
        $ratingResult = $stmt->get_result();
        $ratingData = $ratingResult->fetch_assoc();
        
        // Get user's rating for this song
        $userRating = 0;
        if ($isLoggedIn) {
            $userRatingSql = "SELECT rating_value FROM rating 
                             WHERE content_type = 'music' AND content_id = ? AND user_id = ?";
            $stmt = $conn->prepare($userRatingSql);
            $stmt->bind_param("ii", $row['music_id'], $userId);
            $stmt->execute();
            $userRatingResult = $stmt->get_result();
            if ($userRatingResult && $userRatingResult->num_rows > 0) {
                $userRatingData = $userRatingResult->fetch_assoc();
                $userRating = $userRatingData['rating_value'];
            }
        }
        
        $row['avg_rating'] = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
        $row['rating_count'] = $ratingData['rating_count'];
        $row['user_rating'] = $userRating;
        $songs[] = $row;
    }
}
?>

    <?php include 'header.php'; ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <style>
            /* Your existing CSS styles from the music page */
        :root {
            --primary-color: #FF3366;
            --secondary-color: #1E90FF;
            --accent-color: #FFD700;
            --dark-color: #2C3E50;
            --light-color: #F8F9FA;
            --card-bg: #FFFFFF;
            --text-dark: #2C3E50;
            --text-light: #7F8C8D;
            --gradient: linear-gradient(135deg, #FF3366 0%, #1E90FF 100%);
            --star-color: #FFD700;
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        } */
        
     body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--light-color);
            color: var(--text-dark);
            line-height: 1.6;
        }
         
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
        }
        
        .music-hero { 
            text-align: center; 
            color: var(--primary-color);
            margin-bottom: 40px; 
        }
        
        .music-hero h1 { 
            font-size: 3rem; 
            font-weight: 700;
            margin-bottom: 10px; 
        }
        
        .music-hero h1 span { 
            color: var(--secondary-color); 
        }
        
        .music-hero p { 
            font-size: 1.2rem; 
            color: var(--text-light);
        }

        /* Stats Section */
        .stats-section {
            display: flex;
            justify-content: space-between;
            margin: 50px 0;
            flex-wrap: wrap;
            gap: 25px;
        }

        .stat-item {
            flex: 1;
            min-width: 220px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--gradient);
        }

        .stat-item:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--dark-color);
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        /* Player Container */
        .player-container {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 40px;
            transition: var(--transition);
        }

        .player-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .now-playing {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .now-playing-img {
            width: 100px;
            height: 100px;
            border-radius: 15px;
            object-fit: cover;
            margin-right: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .now-playing-info h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .now-playing-info p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .player-controls {
            margin-top: 20px;
        }

        .progress-container {
            margin-bottom: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 8px;
        }

        .progress {
            height: 100%;
            background: var(--gradient);
            border-radius: 3px;
            width: 0%;
            transition: width 0.1s linear;
        }

        .time-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .control-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .control-btn {
            background: var(--gradient);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .control-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .play-pause {
            width: 60px;
            height: 60px;
        }

        .volume-control {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .volume-slider {
            width: 100px;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            cursor: pointer;
            position: relative;
        }

        .volume-progress {
            height: 100%;
            background: var(--gradient);
            border-radius: 3px;
            width: 70%;
        }
        
        .song-item {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .song-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .song-item.playing {
            background: rgba(255, 51, 102, 0.05);
            border-left: 5px solid var(--primary-color);
        }

        .song-info-box {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .play-indicator {
            color: var(--primary-color);
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .song-info-box img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 20px;
        }

        .song-info h4 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .song-info p {
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .song-year {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Rating System */
        .rating-container {
            margin-top: 10px;
        }

        .stars-container {
            display: flex;
            gap: 5px;
            margin-bottom: 5px;
        }

        .star-rating {
            font-size: 20px;
            cursor: pointer;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .star-rating:hover {
            transform: scale(1.1);
        }

        .text-warning {
            color: var(--star-color);
        }

        .text-muted {
            color: #ddd;
        }

        .rating-info {
            font-size: 0.9em;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .rating-message {
            font-size: 0.8em;
            margin-left: 5px;
        }

        .review-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9em;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .review-btn:hover {
            transform: scale(1.05);
        }

        .songs-links {
            display: flex;
            gap: 15px;
        }

        .songs-links a {
            color: var(--text-light);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .songs-links a:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .review-content {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            padding: 30px;
            position: relative;
        }

        .review-header {
            margin-bottom: 20px;
        }

        .review-header h3 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .review-header p {
            color: var(--text-light);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1em;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.3s ease;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .submit-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            transition: var(--transition);
        }

        .submit-btn:hover {
            transform: scale(1.05);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--text-dark);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--card-bg);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 2000;
        }

        .toast.show {
            display: flex;
        }

        .toast.success {
            border-left: 4px solid #28a745;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
        }

        .toast-icon {
            font-size: 20px;
        }

        .toast.success .toast-icon {
            color: #28a745;
        }

        .toast.error .toast-icon {
            color: #dc3545;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .song-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .song-info-box {
                width: 100%;
                margin-bottom: 15px;
            }

            .songs-links {
                align-self: flex-end;
            }

            .music-hero h1 {
                font-size: 2em;
            }
            
            .stats-section {
                flex-direction: column;
            }

            .stat-item {
                min-width: 100%;
            }
            
            .now-playing {
                flex-direction: column;
                text-align: center;
            }
            
            .now-playing-img {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
</style>
    
    <div class="container">
        <div class="music-hero">
            <h1>Music <span>Player</span></h1>
            <p>Discover and enjoy the latest music hits from talented artists worldwide</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($songs); ?></span>
                <span class="stat-label">Songs Available</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Music Streaming</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">HD</span>
                <span class="stat-label">Audio Quality</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">Free Access</span>
            </div>
        </div>

        <div class="player-container">
            <div class="now-playing">
                <img id="now-playing-img" src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                     alt="Now Playing" class="now-playing-img">
                <div class="now-playing-info">
                    <h3 id="now-playing-title">Select a song to play</h3>
                    <p id="now-playing-artist">Artist information will appear here</p>
                </div>
            </div>

            <div class="player-controls">
                <div class="progress-container">
                    <div class="progress-bar" id="progress-bar">
                        <div class="progress" id="progress"></div>
                    </div>
                    <div class="time-info">
                        <span id="current-time">0:00</span>
                        <span id="duration">0:00</span>
                    </div>
                </div>

                <div class="control-buttons">
                    <button class="control-btn" id="prev-btn">
                        <i class="fas fa-step-backward"></i>
                    </button>
                    <button class="control-btn play-pause" id="play-pause-btn">
                        <i class="fas fa-play" id="play-pause-icon"></i>
                    </button>
                    <button class="control-btn" id="next-btn">
                        <i class="fas fa-step-forward"></i>
                    </button>
                </div>

                <div class="volume-control">
                    <i class="fas fa-volume-up"></i>
                    <div class="volume-slider" id="volume-slider">
                        <div class="volume-progress" id="volume-progress"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="songs-section">
            <?php if (!empty($songs)): ?>
                <?php foreach ($songs as $song): 
                    $title = htmlspecialchars($song['title']);
                    $file_path = htmlspecialchars($song['file_path']);
                    $description = htmlspecialchars($song['description']);
                    $year = htmlspecialchars($song['year']);
                    $avgRating = $song['avg_rating'];
                    $ratingCount = $song['rating_count'];
                    $userRating = $song['user_rating'];
                    
                    // Handle thumbnail image
                    if (isset($song['thumbnail_img']) && !empty($song['thumbnail_img']) && $song['thumbnail_img'] !== 'NULL') {
                        $thumbnail_img = htmlspecialchars($song['thumbnail_img']);
                        $image_path = $thumbnail_img;
                    } else {
                        $image_path = 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                    }
                    
                    // Generate stars for display
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= '<i class="fa fa-star star-rating ' . 
                                 (($i <= $avgRating) ? 'text-warning' : 'text-muted') . 
                                 '" data-rating="' . $i . '"></i>';
                    }
                ?>
                
                <div class="song-item" data-song-id="<?= $song['music_id'] ?>" data-title="<?= $title ?>" data-description="<?= $description ?>" data-image="<?= $image_path ?>">
                    <div class="song-info-box">
                        <i class="fas fa-play play-indicator"></i>
                        <img src="<?= $image_path ?>" alt="<?= $title ?>" 
                             onerror="this.src='https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">
                        <div class="song-info">
                            <h4><?= $title ?></h4>
                            <p><?= $description ?></p>
                            <span class="song-year"><?= $year ?></span>
                            
                            <!-- Rating System -->
                            <div class="rating-container" data-song-id="<?= $song['music_id'] ?>">
                                <div class="stars-container"><?= $stars ?></div>
                                <div class="rating-info">
                                    <span class="rating-value"><?= $avgRating > 0 ? $avgRating : '0' ?></span>/5 
                                    <span class="rating-count">(<?= $ratingCount ?> votes)</span>
                                    <span class="rating-message"></span>
                                </div>
                                
                                <?php if ($isLoggedIn): ?>
                                    <button class="review-btn" data-song-id="<?= $song['music_id'] ?>" data-song-title="<?= $title ?>">
                                        <i class="fas fa-comment"></i> Write a Review
                                    </button>
                                <?php else: ?>
                                    <div class="user-rating-label">
                                        <a href="account.php" class="login-prompt">Login to rate and review</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="songs-links">
                        <a href="<?= $file_path ?>" download="<?= $title ?>.mp3" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="#"><i class="fas fa-share-alt" title="Share"></i></a>
                    </div>
                    <audio class="audio-element" src="<?= $file_path ?>" preload="metadata"></audio>
                </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No songs found in the database.</h3>
                    <p>Please add some music to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal" id="reviewModal">
        <div class="review-content">
            <div class="review-header">
                <h3>Write a Review</h3>
                <p id="reviewSongTitle">Song Title</p>
            </div>
            <form id="reviewForm">
                <input type="hidden" id="reviewSongId" name="content_id">
                <input type="hidden" name="content_type" value="music">
                <div class="form-group">
                    <label for="reviewText">Your Review:</label>
                    <textarea id="reviewText" name="review_text" placeholder="Share your thoughts about this song..." required></textarea>
                </div>
                <button type="submit" class="submit-btn">Submit Review</button>
            </form>
            <button class="close-modal" id="closeReviewModal"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-icon"><i class="fas fa-check-circle"></i></div>
        <div class="toast-message" id="toastMessage">Operation successful!</div>
    </div>

    <script>
        // Check if user is logged in
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Music player functionality
            const audio = new Audio();
            let currentSongIndex = 0;
            const songs = <?php echo json_encode($songs); ?>;
            const songItems = document.querySelectorAll('.song-item');
            const playPauseBtn = document.getElementById('play-pause-btn');
            const playPauseIcon = document.getElementById('play-pause-icon');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const progressBar = document.getElementById('progress-bar');
            const progress = document.getElementById('progress');
            const currentTimeEl = document.getElementById('current-time');
            const durationEl = document.getElementById('duration');
            const volumeSlider = document.getElementById('volume-slider');
            const volumeProgress = document.getElementById('volume-progress');
            const nowPlayingImg = document.getElementById('now-playing-img');
            const nowPlayingTitle = document.getElementById('now-playing-title');
            const nowPlayingArtist = document.getElementById('now-playing-artist');
            
            // Review modal elements
            const reviewModal = document.getElementById('reviewModal');
            const closeReviewModal = document.getElementById('closeReviewModal');
            const reviewForm = document.getElementById('reviewForm');
            const reviewSongId = document.getElementById('reviewSongId');
            const reviewSongTitle = document.getElementById('reviewSongTitle');
            
            // Toast notification
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            // Set initial volume
            audio.volume = 0.7;
            updateVolumeProgress();
            
            // Format time from seconds to MM:SS
            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
            }
            
            // Update progress bar
            function updateProgress() {
                if (audio.duration) {
                    const progressPercent = (audio.currentTime / audio.duration) * 100;
                    progress.style.width = `${progressPercent}%`;
                    currentTimeEl.textContent = formatTime(audio.currentTime);
                }
            }
            
            // Update volume progress display
            function updateVolumeProgress() {
                volumeProgress.style.width = `${audio.volume * 100}%`;
            }
            
            // Set progress bar on click
            function setProgress(e) {
                const width = this.clientWidth;
                const clickX = e.offsetX;
                const duration = audio.duration;
                
                audio.currentTime = (clickX / width) * duration;
            }
            
            // Set volume on click
            function setVolume(e) {
                const width = this.clientWidth;
                const clickX = e.offsetX;
                const volume = clickX / width;
                
                audio.volume = volume;
                updateVolumeProgress();
            }
            
            // Play/Pause toggle
            function togglePlayPause() {
                if (audio.paused) {
                    audio.play();
                    playPauseIcon.classList.remove('fa-play');
                    playPauseIcon.classList.add('fa-pause');
                } else {
                    audio.pause();
                    playPauseIcon.classList.remove('fa-pause');
                    playPauseIcon.classList.add('fa-play');
                }
            }
            
            // Play song by index
            function playSong(index) {
                if (index < 0 || index >= songs.length) return;
                
                currentSongIndex = index;
                const song = songs[index];
                
                // Update audio source
                audio.src = song.file_path;
                
                // Update now playing info
                nowPlayingTitle.textContent = song.title;
                nowPlayingArtist.textContent = song.description;
                
                // Update thumbnail
                if (song.thumbnail_img && song.thumbnail_img !== 'NULL') {
                    nowPlayingImg.src = song.thumbnail_img;
                } else {
                    nowPlayingImg.src = 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
                }
                
                // Update playing indicator
                songItems.forEach((item, i) => {
                    if (i === index) {
                        item.classList.add('playing');
                    } else {
                        item.classList.remove('playing');
                    }
                });
                
                // Play the song
                audio.play();
                playPauseIcon.classList.remove('fa-play');
                playPauseIcon.classList.add('fa-pause');
            }
            
            // Event listeners for player controls
            playPauseBtn.addEventListener('click', togglePlayPause);
            prevBtn.addEventListener('click', () => playSong(currentSongIndex - 1));
            nextBtn.addEventListener('click', () => playSong(currentSongIndex + 1));
            progressBar.addEventListener('click', setProgress);
            volumeSlider.addEventListener('click', setVolume);
            
            // Update progress bar and time
            audio.addEventListener('timeupdate', updateProgress);
            
            // Update duration when metadata is loaded
            audio.addEventListener('loadedmetadata', () => {
                durationEl.textContent = formatTime(audio.duration);
            });
            
            // Play next song when current song ends
            audio.addEventListener('ended', () => {
                playSong(currentSongIndex + 1);
            });
            
            // Song item click to play
            songItems.forEach((item, index) => {
                item.addEventListener('click', function(e) {
                    // Don't trigger if clicking on rating stars or review button
                    if (e.target.classList.contains('star-rating') || 
                        e.target.classList.contains('review-btn') ||
                        e.target.closest('.review-btn')) {
                        return;
                    }
                    
                    playSong(index);
                });
            });
            
            // Toast notification function
            function showToast(message, type) {
                const toastIcon = toast.querySelector('.toast-icon i');
                
                toastMessage.textContent = message;
                toast.className = 'toast show ' + type;
                
                if (type === 'success') { 
                    toastIcon.className = 'fas fa-check-circle'; 
                } else if (type === 'error') { 
                    toastIcon.className = 'fas fa-exclamation-circle'; 
                }
                
                setTimeout(() => { toast.classList.remove('show'); }, 3000);
            }
            
            // Rating functionality
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('star-rating')) {
                    e.stopPropagation();
                    if (!isLoggedIn) { 
                        showToast('Please log in to rate songs', 'error'); 
                        return; 
                    }
                    
                    const ratingContainer = e.target.closest('.rating-container');
                    const songId = ratingContainer.dataset.songId;
                    const rating = parseInt(e.target.dataset.rating);
                    const ratingMessage = ratingContainer.querySelector('.rating-message');
                    ratingMessage.textContent = 'Saving...';
                    ratingMessage.style.color = '#007bff';
                    
                    const formData = new FormData();
                    formData.append('action', 'rate');
                    formData.append('song_id', songId);
                    formData.append('rating', rating);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            ratingContainer.querySelector('.rating-value').textContent = data.avg_rating;
                            ratingContainer.querySelector('.rating-count').textContent = `(${data.rating_count} votes)`;
                            const stars = ratingContainer.querySelectorAll('.star-rating');
                            stars.forEach((star, index) => {
                                if (index < data.avg_rating) { 
                                    star.classList.add('text-warning'); 
                                    star.classList.remove('text-muted'); 
                                } else { 
                                    star.classList.remove('text-warning'); 
                                    star.classList.add('text-muted'); 
                                }
                            });
                            ratingMessage.textContent = 'Thanks for rating!'; 
                            ratingMessage.style.color = '#28a745';
                            setTimeout(() => { ratingMessage.textContent = ''; }, 3000);
                            showToast('Thanks for rating!', 'success');
                        } else {
                            ratingMessage.textContent = data.message || 'Error saving rating'; 
                            ratingMessage.style.color = '#dc3545';
                            setTimeout(() => { ratingMessage.textContent = ''; }, 3000);
                            showToast(data.message || 'Error saving rating', 'error');
                        }
                    })
                    .catch(error => { 
                        console.error('Error:', error); 
                        ratingMessage.textContent = 'Error saving rating'; 
                        ratingMessage.style.color = '#dc3545'; 
                        showToast('Error saving rating', 'error'); 
                    });
                }
            });
            
            // Review functionality
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('review-btn') || e.target.closest('.review-btn')) {
                    e.stopPropagation();
                    if (!isLoggedIn) { 
                        showToast('Please log in to write a review', 'error'); 
                        return; 
                    }
                    const reviewBtn = e.target.classList.contains('review-btn') ? e.target : e.target.closest('.review-btn');
                    const songId = reviewBtn.getAttribute('data-song-id');
                    const songTitle = reviewBtn.getAttribute('data-song-title');
                    reviewSongId.value = songId;
                    reviewSongTitle.textContent = songTitle;
                    reviewModal.classList.add('active');
                }
            });
            
            // Close review modal
            closeReviewModal.addEventListener('click', function() {
                reviewModal.classList.remove('active');
                reviewForm.reset();
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === reviewModal) {
                    reviewModal.classList.remove('active');
                    reviewForm.reset();
                }
            });
            
            // Submit review form
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'submit_review');
                
                const submitBtn = this.querySelector('.submit-btn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        reviewModal.classList.remove('active');
                        this.reset();
                    } else {
                        showToast(data.message, 'error');
                    submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Review';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while submitting your review', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                });
            });
        });
    </script>
     </body>
    </html>

    <?php include 'footer.php'; ?>
