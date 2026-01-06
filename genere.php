<?php 
// 1. Start session and include database connection
session_start();
include 'db.php';

// 2. Check if user is logged in (needed for both the page and the AJAX handler)
 $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
 $userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

?>
<?php
include 'header.php'?>

<!-- Playlist section -->
<section class="playlist-section spad">
  <div class="container-fluid">
    <div class="section-title">
      <h2>Playlists</h2>
    </div>

    <div class="container">
      <ul class="playlist-filter controls">
        <li class="control" data-filter=".genres">Romantic</li>
        <li class="control" data-filter=".artists">Pop</li>
        <li class="control" data-filter=".movies">Rock</li>
        <!-- <li class="control" data-filter=".labels">Others</li> -->
        <li class="control" data-filter="all">All Playlist</li>
      </ul>
    </div>

    <div class="clearfix"></div>
    <div class="row playlist-area">
      <?php
    // ✅ Fetch videos from your table dynamically
      $sql = "
        SELECT v.*, 
               IFNULL(ROUND(AVG(r.rating_value),1), 0) AS avg_rating,
               IFNULL(COUNT(r.rating_id), 0) AS rating_count
        FROM video v
        LEFT JOIN rating r 
          ON r.content_type = 'video' AND r.content_id = v.video_id
        WHERE v.is_new = 1
        GROUP BY v.video_id
        ORDER BY v.created_at DESC
      ";
      $result = mysqli_query($conn, $sql);

      if ($result && mysqli_num_rows($result) > 0):
        while ($row = mysqli_fetch_assoc($result)):

          // Assign filter class based on genre_id
          $genreClass = '';
          switch ($row['genre_id']) {
            case 1: $genreClass = 'genres'; break;   // Romantic
            case 2: $genreClass = 'artists'; break;  // Pop
            case 3: $genreClass = 'movies'; break;   // Rock
            case 4: $genreClass = 'labels'; break;   // Others
            default: $genreClass = 'genres';
          }

          // Star rating icons
          $rating = floatval($row['avg_rating']);
          $stars = '';
          for ($i = 1; $i <= 5; $i++) {
            $stars .= ($i <= $rating) 
              ? '<i class="fa fa-star text-warning"></i>' 
              : '<i class="fa fa-star-o text-muted"></i>';
          }
          
          // Get video file extension to determine format
          $videoPath = htmlspecialchars($row['file_path']);
          $videoExt = pathinfo($videoPath, PATHINFO_EXTENSION);
          $mimeType = '';
          
          switch(strtolower($videoExt)) {
            case 'mp4':
              $mimeType = 'video/mp4';
              break;
            case 'webm':
              $mimeType = 'video/webm';
              break;
            case 'ogg':
            case 'ogv':
              $mimeType = 'video/ogg';
              break;
            default:
              $mimeType = 'video/mp4'; // Default to mp4
          }
          
          // Check if it's a YouTube URL
          $youtubeUrl = '';
          if (strpos($videoPath, 'youtube.com') !== false || strpos($videoPath, 'youtu.be') !== false) {
            $youtubeUrl = $videoPath;
          } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoPath)) {
            // If it's just a YouTube video ID
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoPath;
          }
      ?>
        <div class="mix col-lg-3 col-md-4 col-sm-6 <?= $genreClass ?>">
          <div class="playlist-item shadow-sm p-2 rounded-3">
            <!-- Video Thumbnail with Play Button -->
            <div class="video-container position-relative">
              <img src="<?= htmlspecialchars($row['thumbnail_img']) ?>" 
                   alt="<?= htmlspecialchars($row['title']) ?>" 
                   class="img-fluid rounded video-thumbnail">
              <div class="play-button-overlay position-absolute top-50 start-50 translate-middle">
                <button class="btn btn-danger rounded-circle play-video-btn" 
                        data-video-src="<?= $videoPath ?>"
                        data-video-type="<?= $mimeType ?>"
                        data-bs-toggle="modal" 
                        data-bs-target="#videoModal">
                  <i class="fa fa-play"></i>
                </button>
              </div>
              <!-- Watch Link Button for YouTube -->
              <?php if (!empty($youtubeUrl)): ?>
              <div class="watch-button-overlay position-absolute top-50 start-50 translate-middle">
                <button class="btn btn-primary rounded-circle watch-youtube-btn" 
                        data-video-id="<?= $row['video_id'] ?>"
                        data-youtube-url="<?= $youtubeUrl ?>">
                  <i class="fa fa-youtube-play"></i>
                </button>
              </div>
              <?php endif; ?>
            </div>
            
            <h5 class="mt-2 mb-1"><?= htmlspecialchars($row['title']) ?></h5>
            <p class="small text-muted mb-1"><?= htmlspecialchars($row['description']) ?></p>
            
            <!-- Watch Now Link for YouTube -->
            <?php if (!empty($youtubeUrl)): ?>
            <div class="mb-2">
              <button class="btn btn-sm btn-outline-primary watch-now-youtube-btn"
                      data-video-id="<?= $row['video_id'] ?>"
                      data-youtube-url="<?= $youtubeUrl ?>">
                <i class="fa fa-youtube"></i> Watch 
              </button>
            </div>
            <?php endif; ?>
            
            <!-- Dynamic Rating System -->
            <div class="rating mb-2" data-video-id="<?= $row['video_id'] ?>">
              <div class="stars-container">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fa fa-star star-rating 
                    <?= ($i <= $rating) ? 'text-warning' : 'text-muted' ?>" 
                    data-rating="<?= $i ?>"></i>
                <?php endfor; ?>
              </div>
              <div class="rating-info mt-1">
                <span class="rating-value"><?= $rating ?></span>/5 
                <span class="rating-count">(<?= $row['rating_count'] ?> votes)</span>
                <span class="rating-message ms-2"></span>
              </div>
            </div>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo "<p class='text-center'>No playlists found.</p>";
      endif;
      ?>
    </div>
  </div>
</section>
<!-- Playlist section end -->

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="ratio ratio-16x9">
          <video id="modalVideo" controls autoplay class="w-100">
            <source id="videoSource" src="" type="">
            Your browser does not support the video tag.
          </video>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Check if user is logged in (from PHP session)
var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function() {
  // Handle video play button clicks
  const playButtons = document.querySelectorAll('.play-video-btn');
  
  playButtons.forEach(button => {
    button.addEventListener('click', function() {
      const videoSrc = this.dataset.videoSrc;
      const videoType = this.dataset.videoType;
      
      // Set the video source and type in the modal
      document.getElementById('videoSource').src = videoSrc;
      document.getElementById('videoSource').type = videoType;
      
      // Load the video
      const modalVideo = document.getElementById('modalVideo');
      modalVideo.load();
      
      // Play the video when modal is shown
      const videoModal = document.getElementById('videoModal');
      videoModal.addEventListener('shown.bs.modal', function () {
        modalVideo.play();
      });
      
      // Pause the video when modal is hidden
      videoModal.addEventListener('hidden.bs.modal', function () {
        modalVideo.pause();
        modalVideo.currentTime = 0;
      });
    });
  });
  
  // Handle YouTube watch button clicks
  const youtubeButtons = document.querySelectorAll('.watch-youtube-btn, .watch-now-youtube-btn');
  
  youtubeButtons.forEach(button => {
    button.addEventListener('click', function() {
      const videoId = this.dataset.videoId;
      const youtubeUrl = this.dataset.youtubeUrl;
      
      // Show loading state
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
      this.disabled = true;
      
      // Send AJAX request to update database
      fetch('update_watch_count.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `video_id=${videoId}`
      })
      .then(response => response.json())
      .then(data => {
        // Open YouTube in new tab
        window.open(youtubeUrl, '_blank');
        
        // Restore button state
        this.innerHTML = originalText;
        this.disabled = false;
      })
      .catch(error => {
        console.error('Error:', error);
        
        // Still open YouTube even if update fails
        window.open(youtubeUrl, '_blank');
        
        // Restore button state
        this.innerHTML = originalText;
        this.disabled = false;
      });
    });
  });
  
  // Handle star rating clicks
  const starRatings = document.querySelectorAll('.star-rating');
  
  starRatings.forEach(star => {
    star.addEventListener('click', function() {
      // Check if user is logged in
      if (!isLoggedIn) {
        alert('Please log in to rate videos');
        return;
      }
      
      const videoId = this.closest('.rating').dataset.videoId;
      const rating = this.dataset.rating;
      
      // Send AJAX request to save rating
      fetch('save_rating.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `video_id=${videoId}&rating_value=${rating}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update the display
          const ratingContainer = this.closest('.rating');
          const stars = ratingContainer.querySelectorAll('.star-rating');
          const ratingValue = ratingContainer.querySelector('.rating-value');
          const ratingCount = ratingContainer.querySelector('.rating-count');
          const ratingMessage = ratingContainer.querySelector('.rating-message');
          
          // Update stars
          stars.forEach((star, index) => {
            if (index < data.new_rating) {
              star.classList.remove('fa-star-o', 'text-muted');
              star.classList.add('fa-star', 'text-warning');
            } else {
              star.classList.remove('fa-star', 'text-warning');
              star.classList.add('fa-star-o', 'text-muted');
            }
          });
          
          // Update rating value and count
          ratingValue.textContent = data.new_rating.toFixed(1);
          ratingCount.textContent = `(${data.rating_count} votes)`;
          
          // Show success message
          ratingMessage.textContent = 'Thanks for rating!';
          ratingMessage.classList.add('text-success');
          
          // Hide message after 3 seconds
          setTimeout(() => {
            ratingMessage.textContent = '';
            ratingMessage.classList.remove('text-success');
          }, 3000);
        } else {
          // Show error message
          const ratingMessage = this.closest('.rating').querySelector('.rating-message');
          ratingMessage.textContent = data.message || 'Error saving rating';
          ratingMessage.classList.add('text-danger');
          
          // Hide message after 3 seconds
          setTimeout(() => {
            ratingMessage.textContent = '';
            ratingMessage.classList.remove('text-danger');
          }, 3000);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        const ratingMessage = this.closest('.rating').querySelector('.rating-message');
        ratingMessage.textContent = 'Error saving rating';
        ratingMessage.classList.add('text-danger');
        
        // Hide message after 3 seconds
        setTimeout(() => {
          ratingMessage.textContent = '';
          ratingMessage.classList.remove('text-danger');
        }, 3000);
      });
    });
    
    // Add hover effect
    star.addEventListener('mouseenter', function() {
      const rating = parseInt(this.dataset.rating);
      const stars = this.closest('.stars-container').querySelectorAll('.star-rating');
      
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.remove('text-muted');
          star.classList.add('text-warning');
        } else {
          star.classList.remove('text-warning');
          star.classList.add('text-muted');
        }
      });
    });
  });
  
  // Reset to original rating when mouse leaves the stars container
  document.querySelectorAll('.stars-container').forEach(container => {
    container.addEventListener('mouseleave', function() {
      const ratingContainer = this.closest('.rating');
      const ratingValue = parseFloat(ratingContainer.querySelector('.rating-value').textContent);
      const stars = this.querySelectorAll('.star-rating');
      
      stars.forEach((star, index) => {
        if (index < ratingValue) {
          star.classList.remove('fa-star-o', 'text-muted');
          star.classList.add('fa-star', 'text-warning');
        } else {
          star.classList.remove('fa-star', 'text-warning');
          star.classList.add('fa-star-o', 'text-muted');
        }
      });
    });
  });
});
</script>


<?php 
include 'footer.php';
?>