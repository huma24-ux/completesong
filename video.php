<?php
// 1. Start session and include database connection
session_start();
include 'db.php';

// 2. Check if user is logged in (needed for both the page and the AJAX handler)
 $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
 $userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

 


// 3. HANDLE REVIEW SUBMISSION - This MUST be at the top before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    
    // Set the content type to JSON immediately
    header('Content-Type: application/json');
    
    // Check if user is logged in for this action
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
        exit; // Stop script execution
    }
    
    // Sanitize and validate input
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
            // Send a more detailed error message
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
        
        $stmt->close();
        $check->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    }
    exit; // IMPORTANT: Stop script execution so HTML is not sent with the JSON
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
     body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--light-color);
            color: var(--text-dark);
            line-height: 1.6;
        }
         
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 15px;
        }
        
        .music-hero { 
            text-align: center; 
            color: var(--primary-color);
            margin: 20px 0 30px; /* Reduced top and bottom margins */
            padding: 15px 0; /* Added padding */
        }
        
        .music-hero h1 { 
            font-size: 2.2rem; /* Reduced from 3rem */
            font-weight: 700;
            margin-bottom: 8px; /* Reduced margin */
        }
        
        .music-hero h1 span { 
            color: var(--secondary-color); 
        }
        
        .music-hero p { 
            font-size: 1rem; /* Reduced from 1.2rem */
            color: var(--text-light);
        }

        /* Stats Section */
        .stats-section, .stats-container {
            display: flex;
            justify-content: space-between;
            margin: 30px 0; /* Reduced from 50px */
            flex-wrap: wrap;
            gap: 20px; /* Reduced from 25px */
        }

        .stat-item, .stat-card {
            flex: 1;
            min-width: 220px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px; /* Reduced from 30px */
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before, .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--gradient);
        }

        .stat-item:hover, .stat-card:hover {
            transform: translateY(-8px); /* Reduced from -12px */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); /* Reduced shadow */
        }

        .stat-number {
            font-size: 2.2rem; /* Reduced from 2.8rem */
            font-weight: 700;
            color: var(--dark-color);
            display: block;
            margin-bottom: 6px; /* Reduced margin */
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.95rem; /* Reduced from 1.1rem */
        }

        /* Filter Section */
        .filter-section {
            margin: 25px 0; /* Reduced from 40px */
            padding: 15px 0; /* Reduced from 20px */
        }

        .filter-container {
            display: flex;
            gap: 12px; /* Reduced from 15px */
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            flex: 1;
            padding: 10px 12px; /* Reduced padding */
            border: 2px solid #eee;
            border-radius: 25px 0 0 25px;
            font-size: 0.95rem; /* Reduced from 1rem */
        }

        .search-box button {
            background: var(--gradient);
            border: none;
            color: white;
            padding: 0 12px; /* Reduced from 15px */
            border-radius: 0 25px 25px 0;
            cursor: pointer;
        }

        .filter-dropdown select {
            padding: 10px 12px; /* Reduced padding */
            border: 2px solid #eee;
            border-radius: 25px;
            background: white;
            font-size: 0.95rem; /* Reduced from 1rem */
        }

        /* Video Gallery */
        .video-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Reduced from 300px */
            gap: 20px; /* Reduced from 25px */
            margin-top: 25px; /* Reduced from 30px */
        }

        .video-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .video-thumbnail {
            position: relative;
            padding-top: 56.25%; /* 16:9 aspect ratio */
            overflow: hidden;
        }

        .video-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .video-card:hover .video-thumbnail img {
            transform: scale(1.05);
        }

        .play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-card:hover .play-overlay {
            opacity: 1;
        }

        .play-button {
            width: 50px; /* Reduced from 60px */
            height: 50px; /* Reduced from 60px */
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .play-button:hover {
            transform: scale(1.1);
            background: white;
        }

        .play-button i {
            color: var(--primary-color);
            font-size: 20px; /* Reduced from 24px */
            margin-left: 3px;
        }

        .video-info {
            padding: 15px; /* Reduced from 20px */
        }

        .video-info h3 {
            font-size: 1.1rem; /* Reduced from 1.3rem */
            margin-bottom: 8px; /* Reduced from 10px */
            color: var(--text-dark);
        }

        .video-info p {
            color: var(--text-light);
            margin-bottom: 12px; /* Reduced from 15px */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.9rem; /* Added smaller font size */
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

        .modal-content, .review-content {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 800px;
            position: relative;
        }

        .review-content {
            max-width: 500px;
            padding: 25px; /* Reduced from 30px */
        }

        .modal-header {
            padding: 15px; /* Reduced from 20px */
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            color: var(--text-dark);
            font-size: 1.4rem; /* Reduced size */
        }

        .modal-body {
            padding: 0;
            position: relative; /* Added for positioning the close button */
        }

        .video-player {
            position: relative;
            padding-top: 56.25%; /* 16:9 aspect ratio */
        }

        .video-player iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        .modal-footer {
            padding: 15px; /* Reduced from 20px */
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Rating System */
        .rating-container {
            margin-top: 8px; /* Reduced from 10px */
        }

        .stars-container {
            display: flex;
            gap: 5px;
            margin-bottom: 5px;
        }

        .star-rating {
            font-size: 18px; /* Reduced from 20px */
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
            font-size: 0.85em; /* Reduced from 0.9em */
            color: var(--text-light);
            margin-bottom: 8px; /* Reduced from 10px */
        }

        .rating-message {
            font-size: 0.75em; /* Reduced from 0.8em */
            margin-left: 5px;
        }

        .review-btn {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 6px 12px; /* Reduced from 8px 15px */
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.85em; /* Reduced from 0.9em */
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px; /* Reduced from 8px */
        }

        .review-btn:hover {
            transform: scale(1.05);
        }

        /* Review Form */
        .review-header {
            margin-bottom: 15px; /* Reduced from 20px */
        }

        .review-header h3 {
            color: var(--text-dark);
            margin-bottom: 4px; /* Reduced from 5px */
            font-size: 1.2rem; /* Reduced size */
        }

        .review-header p {
            color: var(--text-light);
            font-size: 0.9rem; /* Reduced size */
        }

        .form-group {
            margin-bottom: 15px; /* Reduced from 20px */
        }

        .form-group label {
            display: block;
            margin-bottom: 6px; /* Reduced from 8px */
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem; /* Reduced size */
        }

        .form-group textarea {
            width: 100%;
            padding: 10px; /* Reduced from 12px */
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 0.95rem; /* Reduced from 1em */
            resize: vertical;
            min-height: 100px; /* Reduced from 120px */
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
            padding: 10px 25px; /* Reduced from 12px 30px */
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.95rem; /* Reduced from 1em */
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
            top: 12px; /* Reduced from 15px */
            right: 12px; /* Reduced from 15px */
            background: none;
            border: none;
            font-size: 20px; /* Reduced from 24px */
            cursor: pointer;
            color: #999;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--text-dark);
        }

        /* New close button above video */
        .close-video-btn {
            position: absolute;
            top: 12px; /* Reduced from 15px */
            right: 12px; /* Reduced from 15px */
            width: 35px; /* Reduced from 40px */
            height: 35px; /* Reduced from 40px */
            background: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 16px; /* Reduced from 18px */
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-video-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 25px; /* Reduced from 30px */
            right: 25px; /* Reduced from 30px */
            background: var(--card-bg);
            padding: 12px 16px; /* Reduced from 15px 20px */
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: none;
            align-items: center;
            gap: 8px; /* Reduced from 10px */
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
            font-size: 18px; /* Reduced from 20px */
        }

        .toast.success .toast-icon {
            color: #28a745;
        }

        .toast.error .toast-icon {
            color: #dc3545;
        }

        .no-results {
            text-align: center;
            padding: 30px; /* Reduced from 40px */
            background: var(--card-bg);
            border-radius: var(--border-radius);
            color: var(--text-light);
            grid-column: 1 / -1;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 10% 20%, rgba(255, 51, 102, 0.05) 0%, transparent 20%),
                            radial-gradient(circle at 80% 80%, rgba(30, 144, 255, 0.05) 0%, transparent 20%),
                            radial-gradient(circle at 40% 40%, rgba(255, 215, 0, 0.05) 0%, transparent 20%);
            z-index: -1;
        }

        @media (max-width: 768px) {
            .video-gallery {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); /* Reduced from 250px */
            }

            .music-hero h1 {
                font-size: 1.8em; /* Reduced from 2em */
            }
            
            .stats-section, .stats-container {
                flex-direction: column;
            }

            .stat-item, .stat-card {
                min-width: 100%;
            }
            
            .filter-container {
                flex-direction: column;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <header data-aos="fade-down">
            <h1>Music <span>Video</span> Gallery</h1>
            <p>Discover and enjoy the latest music hits from talented artists worldwide</p>
        </header>

        <!-- Stats Section -->
        <section class="stats-container" data-aos="fade-up">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-video"></i>
                </div>
                <span class="stat-number">
                    <?php 
                    $query = "SELECT COUNT(*) as total FROM video";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    ?>
                </span>
                <span class="stat-label">Videos Available</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="stat-number">24/7</span>
                <span class="stat-label">Video Streaming</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-high-definition"></i>
                </div>
                <span class="stat-number">HD</span>
                <span class="stat-label">Video Quality</span>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <span class="stat-number">100%</span>
                <span class="stat-label">Free Access</span>
            </div>
        </section>

        <!-- Filter Section -->
        <section class="filter-section" data-aos="fade-up">
            <div class="filter-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search videos...">
                    <button type="button" id="searchBtn"><i class="fas fa-search"></i></button>
                </div>
                <div class="filter-dropdown">
                    <select id="yearFilter">
                        <option value="">All Years</option>
                        <?php
                        // Get years from database
                        $yearQuery = "SELECT DISTINCT YEAR(created_at) as year FROM video ORDER BY year DESC";
                        $yearResult = mysqli_query($conn, $yearQuery);
                        while ($yearRow = mysqli_fetch_assoc($yearResult)) {
                            echo '<option value="' . $yearRow['year'] . '">' . $yearRow['year'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </section>

        <div class="video-gallery" id="videoGallery">
            <?php
            // Fetch videos with ratings
            $query = "SELECT v.*, 
                     IFNULL(ROUND(AVG(r.rating_value),1), 0) AS avg_rating,
                     IFNULL(COUNT(r.rating_id), 0) AS rating_count
                     FROM video v
                     LEFT JOIN rating r ON r.content_type = 'video' AND r.content_id = v.video_id
                     GROUP BY v.video_id
                     ORDER BY v.created_at DESC";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $rating = floatval($row['avg_rating']);
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= '<i class="fa fa-star star-rating ' . 
                                 (($i <= $rating) ? 'text-warning' : 'text-muted') . 
                                 '" data-rating="' . $i . '"></i>';
                    }
                    
                    $video_url = $row['file_path'];
                    $embed_url = '';
                    $video_id = '';
                    
                    if (strpos($video_url, 'watch?v=') !== false) {
                        $video_id = substr($video_url, strpos($video_url, 'v=') + 2);
                        if (strpos($video_id, '&') !== false) {
                            $video_id = substr($video_id, 0, strpos($video_id, '&'));
                        }
                        $embed_url = 'https://www.youtube.com/embed/' . $video_id;
                    } elseif (strpos($video_url, 'youtu.be/') !== false) {
                        $video_id = substr($video_url, strpos($video_url, 'be/') + 3);
                        if (strpos($video_id, '?') !== false) {
                            $video_id = substr($video_id, 0, strpos($video_id, '?'));
                        }
                        $embed_url = 'https://www.youtube.com/embed/' . $video_id;
                    }
                    
                    // Split title to add colored span
                    $title_parts = explode(' ', htmlspecialchars($row['title']));
                    $title_with_span = '';
                    if (count($title_parts) > 1) {
                        $title_with_span = $title_parts[0] . ' <span style="color: var(--secondary-color);">' . implode(' ', array_slice($title_parts, 1)) . '</span>';
                    } else {
                        $title_with_span = htmlspecialchars($row['title']);
                    }
                    
                    echo '<div class="video-card" data-video-id="' . $row['video_id'] . '" data-title="' . htmlspecialchars($row['title']) . '" data-year="' . date('Y', strtotime($row['created_at'])) . '">
                        <div class="video-thumbnail">
                            <img src="' . htmlspecialchars($row['thumbnail_img']) . '" alt="' . htmlspecialchars($row['title']) . '">
                            <div class="play-overlay">
                                <div class="play-button" data-embed-url="' . $embed_url . '" data-title="' . htmlspecialchars($row['title']) . '">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>
                        <div class="video-info">
                            <h3>' . $title_with_span . '</h3>
                            <p>' . htmlspecialchars($row['description']) . '</p>
                            <div class="rating" data-video-id="' . $row['video_id'] . '">
                                <div class="stars-container">' . $stars . '</div>
                                <div class="rating-info">
                                    <span class="rating-value">' . $rating . '</span>/5 
                                    <span class="rating-count">(' . $row['rating_count'] . ' votes)</span>
                                    <span class="rating-message"></span>
                                </div>
                            </div>';
                    
                    if ($isLoggedIn) {
                        echo '<button class="review-btn" data-video-id="' . $row['video_id'] . '" data-video-title="' . htmlspecialchars($row['title']) . '">
                            <i class="fas fa-comment"></i> Write a Review
                        </button>';
                    }
                    
                    echo '</div></div>';
                }
            } else {
                echo '<div class="no-results"><p>No videos found.</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Video Player Modal -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Video Title</h2>
                <button class="close-modal" id="closeVideoModal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <!-- Close button above video -->
                <button class="close-video-btn" id="closeVideoBtn" title="Close Video">
                    <i class="fas fa-times"></i>
                </button>
                <div class="video-player">
                    <iframe id="modalVideoPlayer" src="" allowfullscreen></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class="rating-container">
                    <div class="stars-container" id="modalStars"></div>
                    <div class="rating-info">
                        <span class="rating-value" id="modalRatingValue">0</span>/5 
                        <span id="modalRatingCount">(0)</span>
                        <span class="rating-message" id="modalRatingMessage"></span>
                    </div>
                </div>
                <?php if ($isLoggedIn): ?>
                <button class="review-btn" id="modalReviewBtn" data-video-id="0"><i class="fas fa-comment"></i> Write a Review</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal" id="reviewModal">
        <div class="review-content">
            <div class="review-header">
                <h3>Write a Review</h3>
                <p id="reviewVideoTitle">Video Title</p>
            </div>
            <form id="reviewForm">
                <input type="hidden" id="reviewVideoId" name="content_id">
                <input type="hidden" name="content_type" value="video">
                <div class="form-group">
                    <label for="reviewText">Your Review:</label>
                    <textarea id="reviewText" name="review_text" placeholder="Share your thoughts about this video..." required></textarea>
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

        // Video player functionality
        document.querySelectorAll('.play-button').forEach(button => {
            button.addEventListener('click', function() {
                const embedUrl = this.dataset.embedUrl;
                const title = this.dataset.title;
                const videoCard = this.closest('.video-card');
                const rating = videoCard.querySelector('.rating-value').textContent;
                const ratingCount = videoCard.querySelector('.rating-count').textContent;
                const stars = videoCard.querySelector('.stars-container').innerHTML;
                const videoId = videoCard.dataset.videoId;
                
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalVideoPlayer').src = embedUrl + '?autoplay=1';
                document.getElementById('modalStars').innerHTML = stars;
                document.getElementById('modalRatingValue').textContent = rating;
                document.getElementById('modalRatingCount').textContent = ratingCount;
                
                if (isLoggedIn) {
                    document.getElementById('modalReviewBtn').setAttribute('data-video-id', videoId);
                    document.getElementById('modalReviewBtn').setAttribute('data-video-title', title);
                }
                
                document.getElementById('videoModal').classList.add('active');
            });
        });

        // Close modals
        document.getElementById('closeVideoModal').addEventListener('click', function() {
            document.getElementById('videoModal').classList.remove('active');
            document.getElementById('modalVideoPlayer').src = '';
        });

        // Close video modal when clicking the new close button above video
        document.getElementById('closeVideoBtn').addEventListener('click', function() {
            document.getElementById('videoModal').classList.remove('active');
            document.getElementById('modalVideoPlayer').src = '';
        });

        document.getElementById('closeReviewModal').addEventListener('click', function() {
            document.getElementById('reviewModal').classList.remove('active');
            document.getElementById('reviewForm').reset();
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === document.getElementById('videoModal')) {
                document.getElementById('videoModal').classList.remove('active');
                document.getElementById('modalVideoPlayer').src = '';
            }
            if (e.target === document.getElementById('reviewModal')) {
                document.getElementById('reviewModal').classList.remove('active');
                document.getElementById('reviewForm').reset();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('videoModal').classList.contains('active')) {
                    document.getElementById('videoModal').classList.remove('active');
                    document.getElementById('modalVideoPlayer').src = '';
                }
                if (document.getElementById('reviewModal').classList.contains('active')) {
                    document.getElementById('reviewModal').classList.remove('active');
                    document.getElementById('reviewForm').reset();
                }
            }
        });

        // Rating functionality (for save_rating.php)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('star-rating')) {
                e.stopPropagation();
                if (!isLoggedIn) { showToast('Please log in to rate videos', 'error'); return; }
                const ratingContainer = e.target.closest('.rating');
                const videoId = ratingContainer.dataset.videoId;
                const rating = parseInt(e.target.dataset.rating);
                const ratingMessage = ratingContainer.querySelector('.rating-message');
                ratingMessage.textContent = 'Saving...';
                ratingMessage.style.color = '#007bff';
                
                fetch('save_rating.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `video_id=${videoId}&rating_value=${rating}` })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        ratingContainer.querySelector('.rating-value').textContent = data.new_rating.toFixed(1);
                        ratingContainer.querySelector('.rating-count').textContent = `(${data.rating_count} votes)`;
                        const stars = ratingContainer.querySelectorAll('.star-rating');
                        stars.forEach((star, index) => {
                            if (index < data.new_rating) { star.classList.add('text-warning'); star.classList.remove('text-muted'); }
                            else { star.classList.remove('text-warning'); star.classList.add('text-muted'); }
                        });
                        ratingMessage.textContent = 'Thanks for rating!'; ratingMessage.style.color = '#28a745';
                        setTimeout(() => { ratingMessage.textContent = ''; }, 3000);
                        showToast('Thanks for rating!', 'success');
                    } else {
                        ratingMessage.textContent = data.message || 'Error saving rating'; ratingMessage.style.color = '#dc3545';
                        setTimeout(() => { ratingMessage.textContent = ''; }, 3000);
                        showToast(data.message || 'Error saving rating', 'error');
                    }
                })
                .catch(error => { console.error('Error:', error); ratingMessage.textContent = 'Error saving rating'; ratingMessage.style.color = '#dc3545'; showToast('Error saving rating', 'error'); });
            }
        });

        // Review functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('review-btn') || e.target.closest('.review-btn')) {
                e.stopPropagation();
                if (!isLoggedIn) { showToast('Please log in to write a review', 'error'); return; }
                const reviewBtn = e.target.classList.contains('review-btn') ? e.target : e.target.closest('.review-btn');
                const videoId = reviewBtn.getAttribute('data-video-id');
                const videoTitle = reviewBtn.getAttribute('data-video-title');
                document.getElementById('reviewVideoId').value = videoId;
                document.getElementById('reviewVideoTitle').textContent = videoTitle;
                document.getElementById('reviewModal').classList.add('active');
            }
        });

        // Submit review form - KEY FIX IS HERE
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'submit_review');
            
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            // Use fetch to send the request to the same page
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Raw response:', response); // Log the raw response
                return response.json(); // This will throw an error if response is not valid JSON
            })
            .then(data => {
                console.log('Parsed JSON data:', data); // Log the parsed data
                if (data.success) {
                    showToast(data.message, 'success');
                    document.getElementById('reviewModal').classList.remove('active');
                    this.reset();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error); // Log any fetch errors
                showToast('An error occurred while submitting your review. Check console for details.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
            });
        });

        // Toast notification function
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = toast.querySelector('.toast-icon i');
            
            toastMessage.textContent = message;
            toast.className = 'toast show ' + type;
            
            if (type === 'success') { toastIcon.className = 'fas fa-check-circle'; }
            else if (type === 'error') { toastIcon.className = 'fas fa-exclamation-circle'; }
            
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        // Search and filter functionality
        document.getElementById('searchBtn').addEventListener('click', filterVideos);
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                filterVideos();
            }
        });
        document.getElementById('yearFilter').addEventListener('change', filterVideos);

        function filterVideos() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const selectedYear = document.getElementById('yearFilter').value;
            const videoCards = document.querySelectorAll('.video-card');
            let visibleCount = 0;

            videoCards.forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const year = card.dataset.year;
                
                if ((title.includes(searchTerm) || searchTerm === '') && 
                    (year === selectedYear || selectedYear === '')) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show no results message if needed
            const gallery = document.getElementById('videoGallery');
            const noResultsMsg = gallery.querySelector('.no-results');
            
            if (visibleCount === 0 && !noResultsMsg) {
                const noResults = document.createElement('div');
                noResults.className = 'no-results';
                noResults.innerHTML = '<p>No videos found matching your criteria.</p>';
                gallery.appendChild(noResults);
            } else if (visibleCount > 0 && noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>