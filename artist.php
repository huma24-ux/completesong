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

<?php
// Fetch all artists
 $artist_sql = "SELECT * FROM artist";
 $artist_res = mysqli_query($conn, $artist_sql);
?>

<?php
// ✅ If user clicks a song, show that one, otherwise show latest
 $music_id = isset($_GET['music_id']) ? intval($_GET['music_id']) : null;

if ($music_id) {
  $sql = "SELECT m.*, a.artist_name, a.artist_image 
          FROM music m 
          LEFT JOIN artist a ON m.artist_id = a.artist_id 
          WHERE m.music_id = $music_id LIMIT 1";
} else {
  $sql = "SELECT m.*, a.artist_name, a.artist_image 
          FROM music m 
          LEFT JOIN artist a ON m.artist_id = a.artist_id 
          ORDER BY m.created_at DESC LIMIT 1";
}

 $result = mysqli_query($conn, $sql);
 $row = mysqli_fetch_assoc($result);

// ✅ Get next song for auto-play
 $next_query = mysqli_query($conn, "SELECT music_id FROM music WHERE music_id > {$row['music_id']} ORDER BY music_id ASC LIMIT 1");
 $next_row = mysqli_fetch_assoc($next_query);
 $next_id = $next_row ? $next_row['music_id'] : null;

// ✅ Get previous song for previous button
 $prev_query = mysqli_query($conn, "SELECT music_id FROM music WHERE music_id < {$row['music_id']} ORDER BY music_id DESC LIMIT 1");
 $prev_row = mysqli_fetch_assoc($prev_query);
 $prev_id = $prev_row ? $prev_row['music_id'] : null;
?>

<!-- 🎧 Player Section - Professional Layout -->
<section class="player-section" style="padding: 80px 0; background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 249, 250, 0.98)), url('https://picsum.photos/seed/music-player/1920/1080.jpg'); background-size: cover; background-position: center; background-attachment: fixed; position: relative;">
  
  <!-- Animated Background Elements -->
  <div class="music-notes">
    <div class="music-note" style="left: 10%; animation-delay: 0s;">♪</div>
    <div class="music-note" style="left: 20%; animation-delay: 1s;">♫</div>
    <div class="music-note" style="left: 30%; animation-delay: 2s;">♬</div>
    <div class="music-note" style="left: 40%; animation-delay: 3s;">♭</div>
    <div class="music-note" style="left: 50%; animation-delay: 4s;">♮</div>
    <div class="music-note" style="left: 60%; animation-delay: 5s;">♯</div>
    <div class="music-note" style="left: 70%; animation-delay: 6s;">♪</div>
    <div class="music-note" style="left: 80%; animation-delay: 7s;">♫</div>
    <div class="music-note" style="left: 90%; animation-delay: 8s;">♬</div>
  </div>
  
  <!-- 🎵 MAIN PLAYER CONTAINER -->
  <div class="container-fluid px-4">
    <div class="row justify-content-center">
      <div class="col-xxl-10 col-xl-11 col-lg-12">
        <div class="player-main-box" style="background: rgba(255, 255, 255, 0.95); border-radius: 25px; box-shadow: 0 25px 70px rgba(0,0,0,0.12); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.25); position: relative; z-index: 2; overflow: hidden;">
          
          <!-- 🎵 PLAYER GRID LAYOUT -->
          <div class="row g-0 align-items-stretch">
            
            <!-- LEFT COLUMN - ARTIST & ALBUM INFO -->
            <div class="col-lg-5 col-md-6">
              <div class="player-info-section h-100 p-5" style="background: linear-gradient(160deg, #ff0057 0%, #ff4081 100%); color: white; position: relative;">
                
                <!-- Background Pattern -->
                <div class="bg-pattern" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
                
                <div class="position-relative z-1 h-100 d-flex flex-column justify-content-between">
                  
                  <!-- Now Playing Header -->
                  <div class="text-center mb-4">
                    <h2 class="display-5 fw-bold mb-3"> Now Playing</h2>
                    <div class="playing-indicator d-flex justify-content-center align-items-center mb-3">
                      <span class="playing-dot me-2"></span>
                      <small class="text-white-50">LIVE</small>
                    </div>
                  </div>

                  <!-- Artist Image & Info -->
                  <div class="text-center flex-grow-1 d-flex flex-column justify-content-center">
                    <div class="artist-image-container mb-4 mx-auto">
                      <?php if (!empty($row['artist_image'])): ?>
                        <img src="<?= htmlspecialchars($row['artist_image']) ?>" 
                             alt="<?= htmlspecialchars($row['artist_name']) ?>" 
                             class="artist-img rounded-circle shadow-lg">
                      <?php else: ?>
                        <img src="img/wave-thumb.jpg" alt="Artist" 
                             class="artist-img rounded-circle shadow-lg">
                      <?php endif; ?>
                    </div>

                    <?php if ($row): ?>
                      <h1 class="song-title fw-bold mb-3" style="font-size: 2.2rem; line-height: 1.2;">
                        <?= htmlspecialchars($row['title']) ?>
                      </h1>
                      
                      <?php if (!empty($row['artist_name'])): ?>
                        <div class="artist-name mb-4">
                          <span class="h5 fw-normal text-white-50">By </span>
                          <span class="h4 fw-bold"><?= htmlspecialchars($row['artist_name']) ?></span>
                        </div>
                      <?php endif; ?>

                      <div class="song-description mb-4">
                        <p class="lead mb-3" style="opacity: 0.9; line-height: 1.6;">
                          <?= htmlspecialchars($row['description']) ?>
                        </p>
                        <div class="song-meta d-flex justify-content-center align-items-center gap-4">
                          <span class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?= htmlspecialchars($row['year']) ?>
                          </span>
                          <span class="d-flex align-items-center">
                            <i class="fas fa-clock me-2"></i>
                            <span id="currentDuration">0:00</span>
                          </span>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- Audio Wave Visualization -->
                  <div class="audio-wave mt-4">
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RIGHT COLUMN - PLAYER CONTROLS -->
            <div class="col-lg-7 col-md-6">
              <div class="player-controls-section h-100 p-5 d-flex flex-column justify-content-between">
                
                <!-- Top Section - Progress & Time -->
                <div class="progress-section mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small" id="currentTime">0:00</span>
                    <span class="text-muted small" id="totalTime">0:00</span>
                  </div>
                  <div class="progress" style="height: 6px; background: #e9ecef; border-radius: 10px; cursor: pointer;" id="progressBar">
                    <div class="progress-bar" style="width: 0%; background: linear-gradient(90deg, #ff0057, #ff4081); border-radius: 10px;" id="progressBarFill"></div>
                  </div>
                </div>

                <!-- Main Audio Player -->
                <div class="audio-player-main mb-5">
                  <audio id="musicPlayer" preload="metadata">
                    <source src="<?= htmlspecialchars($row['file_path']) ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                  </audio>
                </div>

                <!-- Control Buttons -->
                <div class="player-controls-grid mb-5">
                  <div class="row g-3 justify-content-center text-center">
                    <div class="col-4 col-sm-3">
                      <button class="control-btn btn-previous rounded-circle" id="previousBtn">
                        <i class="fas fa-step-backward"></i>
                      </button>
                      <small class="d-block mt-2 text-muted">Previous</small>
                    </div>
                    <div class="col-4 col-sm-3">
                      <button id="playPauseBtn" class="control-btn btn-play-pause rounded-circle">
                        <i class="fas fa-play"></i>
                      </button>
                      <small class="d-block mt-2 text-muted">Play/Pause</small>
                    </div>
                    <div class="col-4 col-sm-3">
                      <button class="control-btn btn-next rounded-circle" id="nextBtn">
                        <i class="fas fa-step-forward"></i>
                      </button>
                      <small class="d-block mt-2 text-muted">Next</small>
                    </div>
                    <div class="col-6 col-sm-3 mt-3 mt-sm-0">
                      <button class="control-btn btn-volume rounded-circle" id="volumeBtn">
                        <i class="fas fa-volume-up"></i>
                      </button>
                      <small class="d-block mt-2 text-muted">Volume</small>
                    </div>
                  </div>
                </div>

                <!-- Volume Slider (Hidden by default) -->
                <div class="volume-slider-container mb-4" id="volumeSliderContainer" style="display: none;">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-volume-down me-2"></i>
                    <input type="range" class="form-range" min="0" max="1" step="0.1" value="0.7" id="volumeSlider">
                    <i class="fas fa-volume-up ms-2"></i>
                  </div>
                </div>

                <!-- Secondary Controls -->
                <div class="secondary-controls mb-4">
                  <div class="row g-2 justify-content-center">
                    <div class="col-auto">
                      <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="likeBtn">
                        <i class="fas fa-heart me-1"></i> Like
                      </button>
                    </div>
                    <div class="col-auto">
                      <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="shareBtn">
                        <i class="fas fa-share-alt me-1"></i> Share
                      </button>
                    </div>
                    <div class="col-auto">
                      <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="downloadBtn">
                        <i class="fas fa-download me-1"></i> Download
                      </button>
                    </div>
                  </div>
                </div>

                <!-- More Songs Section -->
                <div class="more-songs-section">
                  <h6 class="text-center mb-3 fw-bold text-uppercase" style="color: #ff0057; letter-spacing: 1px;">
                    <i class="fas fa-music me-2"></i>More Songs
                  </h6>
                  <div class="songs-grid">
                    <div class="row g-2 justify-content-center">
                      <?php
                      $allSongs = mysqli_query($conn, "SELECT * FROM music ORDER BY created_at DESC LIMIT 6");
                      while ($song = mysqli_fetch_assoc($allSongs)) {
                        $isActive = ($song['music_id'] == $row['music_id']);
                        echo '
                        <div class="col-6 col-sm-4">
                          <a href="?music_id='.$song['music_id'].'" class="song-item btn w-100 text-start '.($isActive ? 'active' : '').'">
                            <div class="d-flex align-items-center">
                              <div class="song-icon me-2">
                                <i class="fas fa-music '.($isActive ? 'text-white' : 'text-primary').'"></i>
                              </div>
                              <span class="song-name truncate '.($isActive ? 'text-white' : 'text-dark').'">'
                                .htmlspecialchars($song['title']).
                              '</span>
                            </div>
                          </a>
                        </div>';
                      }
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 🎧 JavaScript for Player Controls -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const player = document.getElementById('musicPlayer');
  const playPauseBtn = document.getElementById('playPauseBtn');
  const previousBtn = document.getElementById('previousBtn');
  const nextBtn = document.getElementById('nextBtn');
  const volumeBtn = document.getElementById('volumeBtn');
  const volumeSlider = document.getElementById('volumeSlider');
  const volumeSliderContainer = document.getElementById('volumeSliderContainer');
  const progressBar = document.getElementById('progressBar');
  const progressBarFill = document.getElementById('progressBarFill');
  const currentTimeEl = document.getElementById('currentTime');
  const totalTimeEl = document.getElementById('totalTime');
  const currentDurationEl = document.getElementById('currentDuration');
  
  // Current song data
  const currentSongId = <?= $row['music_id'] ?>;
  const nextSongId = <?= $next_id ? $next_id : 'null' ?>;
  const prevSongId = <?= $prev_id ? $prev_id : 'null' ?>;
  
  // Initialize player volume
  player.volume = 0.7;
  
  // Play/Pause functionality
  playPauseBtn.addEventListener('click', function() {
    if (player.paused) {
      player.play();
      playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
      playPauseBtn.style.background = 'linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%)';
    } else {
      player.pause();
      playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
      playPauseBtn.style.background = 'linear-gradient(135deg, #ff0057 0%, #ff4081 100%)';
    }
  });

  // Previous song functionality
  previousBtn.addEventListener('click', function() {
    if (prevSongId) {
      window.location.href = "?music_id=" + prevSongId;
    } else {
      // If no previous song, show a notification
      showNotification("This is the first song in the list", "info");
    }
  });

  // Next song functionality
  nextBtn.addEventListener('click', function() {
    if (nextSongId) {
      window.location.href = "?music_id=" + nextSongId;
    } else {
      // If no next song, show a notification
      showNotification("This is the last song in the list", "info");
    }
  });

  // Volume control functionality
  volumeBtn.addEventListener('click', function() {
    // Toggle volume slider visibility
    if (volumeSliderContainer.style.display === 'none') {
      volumeSliderContainer.style.display = 'block';
    } else {
      volumeSliderContainer.style.display = 'none';
    }
  });

  // Volume slider change event
  volumeSlider.addEventListener('input', function() {
    player.volume = this.value;
    
    // Update volume icon based on volume level
    const volumeIcon = volumeBtn.querySelector('i');
    if (this.value == 0) {
      volumeIcon.className = 'fas fa-volume-mute';
    } else if (this.value < 0.5) {
      volumeIcon.className = 'fas fa-volume-down';
    } else {
      volumeIcon.className = 'fas fa-volume-up';
    }
  });

  // Progress bar click to seek
  progressBar.addEventListener('click', function(e) {
    const rect = progressBar.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    player.currentTime = pos * player.duration;
  });

  // Update progress bar as the song plays
  player.addEventListener('timeupdate', function() {
    if (player.duration) {
      const percent = (player.currentTime / player.duration) * 100;
      progressBarFill.style.width = percent + '%';
      
      // Update time displays
      currentTimeEl.textContent = formatTime(player.currentTime);
      totalTimeEl.textContent = formatTime(player.duration);
      currentDurationEl.textContent = formatTime(player.currentTime);
    }
  });

  // Update total time when metadata is loaded
  player.addEventListener('loadedmetadata', function() {
    totalTimeEl.textContent = formatTime(player.duration);
  });

  // Auto-play next song
  player.addEventListener('ended', function() {
    if (nextSongId) {
      setTimeout(() => {
        window.location.href = "?music_id=" + nextSongId;
      }, 1500);
    } else {
      // If no next song, reset player
      playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
      playPauseBtn.style.background = 'linear-gradient(135deg, #ff0057 0%, #ff4081 100%)';
      progressBarFill.style.width = '0%';
      currentTimeEl.textContent = '0:00';
    }
  });

  // Like button functionality
  document.getElementById('likeBtn').addEventListener('click', function() {
    const icon = this.querySelector('i');
    if (icon.classList.contains('far')) {
      icon.classList.remove('far');
      icon.classList.add('fas');
      this.classList.remove('btn-outline-primary');
      this.classList.add('btn-primary');
      showNotification("You liked this song!", "success");
    } else {
      icon.classList.remove('fas');
      icon.classList.add('far');
      this.classList.remove('btn-primary');
      this.classList.add('btn-outline-primary');
      showNotification("You removed your like", "info");
    }
  });

  // Share button functionality
  document.getElementById('shareBtn').addEventListener('click', function() {
    if (navigator.share) {
      navigator.share({
        title: '<?= htmlspecialchars($row['title']) ?>',
        text: 'Check out this song: <?= htmlspecialchars($row['title']) ?> by <?= htmlspecialchars($row['artist_name']) ?>',
        url: window.location.href
      }).then(() => {
        showNotification("Thanks for sharing!", "success");
      }).catch((error) => {
        console.log('Error sharing:', error);
      });
    } else {
      // Fallback - copy link to clipboard
      navigator.clipboard.writeText(window.location.href).then(() => {
        showNotification("Link copied to clipboard!", "success");
      });
    }
  });

  // Download button functionality
  document.getElementById('downloadBtn').addEventListener('click', function() {
    const link = document.createElement('a');
    link.href = '<?= htmlspecialchars($row['file_path']) ?>';
    link.download = '<?= htmlspecialchars($row['title']) ?>.mp3';
    link.click();
    showNotification("Download started!", "success");
  });

  // Helper function to format time
  function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
  }

  // Helper function to show notifications
  function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.textContent = message;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Add hover effects to control buttons
  document.querySelectorAll('.control-btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-3px) scale(1.05)';
    });
    
    btn.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
  });

  // Animate audio wave bars
  function animateWaveBars() {
    const bars = document.querySelectorAll('.wave-bar');
    bars.forEach((bar, index) => {
      const randomHeight = Math.random() * 20 + 5;
      bar.style.height = `${randomHeight}px`;
    });
  }
  
  // Start animation when music is playing
  player.addEventListener('play', function() {
    window.waveInterval = setInterval(animateWaveBars, 200);
  });
  
  // Stop animation when music is paused
  player.addEventListener('pause', function() {
    clearInterval(window.waveInterval);
  });
  
  // Initial animation
  setInterval(animateWaveBars, 200);
});
</script>

<!-- 🎨 Custom CSS Styling -->

<!-- Player section end -->

<!-- Rest of your existing code remains unchanged -->
<!-- Songs details section -->
<section class="songs-details-section" style="padding: 40px 20px; margin: 20px 0;">
  <div class="container-fluid" style="padding: 20px;">
    <div class="col-lg-9" style="margin: auto;">
      <div class="row" style="margin-top: 20px;">
        <?php
        if (mysqli_num_rows($artist_res) > 0) {
            while ($artist = mysqli_fetch_assoc($artist_res)) {
        ?>
          <div class="col-lg-6 mb-4" style="padding: 10px;">
            <div class="song-details-box" style="padding: 20px; margin: 10px 0; border: 1px solid #ddd; border-radius: 10px; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <h3 style="margin-bottom: 15px;">About the Artist</h3>
              <div class="artist-details d-flex align-items-start" style="gap: 15px;">
                <img 
                  src="<?= $artist['artist_image']; ?>" 
                  alt="<?= $artist['artist_name']; ?>" 
                  style="width:120px; height:120px; object-fit:cover; border-radius:10px; margin-right:15px;"
                >
                <div class="ad-text" style="line-height: 1.6;">
                  <h5 style="margin-bottom: 5px;"><?= $artist['artist_name']; ?></h5>
                  <span style="display:block; color:#888; margin-bottom:10px;"><?= $artist['country']; ?></span>
                  <p style="margin: 0;"><?= $artist['description']; ?></p>
                </div>
              </div>
            </div>
          </div>
        <?php
            }
        } else {
            echo "<p class='text-center' style='padding:20px;'>No artists found in the database.</p>";
        }
        ?>
      </div>
    </div>
  </div>
</section>

<!-- Similar Songs section -->
<section class="similar-songs-section">
  <div class="container-fluid">
  <div class="section-title text-center" style="margin-bottom:50px;">
          <h2 style="font-size:38px; color:#111;"> <span style="color:#ff0057;">Music</span> Videos</h2>
        </div>
    <div class="video-grid-container row g-2 justify-content-center">
      <?php
      // ✅ Fetch only Arijit Singh songs (limit 4)
      $similar_sql = "
        SELECT v.*, g.genre_name, a.artist_name 
        FROM video v
        LEFT JOIN genre g ON v.genre_id = g.genre_id
        LEFT JOIN artist a ON v.artist_id = a.artist_id
        WHERE a.artist_name = 'Arijit Singh'
        ORDER BY v.created_at DESC
        LIMIT 4
      ";

      $similar_res = mysqli_query($conn, $similar_sql);

      if ($similar_res && mysqli_num_rows($similar_res) > 0):
        while ($row = mysqli_fetch_assoc($similar_res)):
      ?>
        <div class="col-md-3 col-sm-6 d-flex py-2">
          <div class="video-card shadow-sm border rounded-3 p-2 flex-fill"
               data-id="<?= $row['video_id']; ?>"
               data-video-path="<?= htmlspecialchars($row['file_path']); ?>"
               onclick="enlargeCard(this)">

            <!-- ✅ Thumbnail -->
            <div class="video-thumbnail position-relative">
              <img src="<?= htmlspecialchars($row['thumbnail_img']); ?>"
                   alt="<?= htmlspecialchars($row['title']); ?>"
                   class="img-fluid rounded-3">
              <?php if ($row['is_new']): ?>
                <span class="video-badge">New</span>
              <?php endif; ?>
              <div class="play-button">
                <i class="fas fa-play"></i>
              </div>
            </div>

            <!-- ✅ Info -->
            <div class="video-info mt-3 px-2 pb-2 text-center">
              <h5 class="video-title mb-1"><?= htmlspecialchars($row['title']); ?></h5>
              <div class="video-meta small text-muted mb-2">
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($row['artist_name']); ?></span> |
                <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($row['year']); ?></span>
              </div>
              <p class="video-description small text-secondary">
                <?= htmlspecialchars($row['description']); ?>
              </p>
              <div class="video-stats d-flex justify-content-between align-items-center mt-2">
                <span class="badge bg-primary"><?= htmlspecialchars($row['genre_name']); ?></span>
                <a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-pink-watch">
                  <i class="fas fa-play"></i> Watch
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo '<p class="text-center text-muted">No songs by Arijit Singh found.</p>';
      endif;
      ?>
    </div>
  </div>
</section>



<?php 
include 'footer.php';
?>