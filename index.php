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
<!-- Hero section -->
<section class="hero-section">
    <div class="hero-slider owl-carousel">
        <div class="hs-item">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="hs-text">
                            <h2><span>Music</span> for everyone.</h2>
                            <p>Welcome to SOUND Group's entertainment portal. Enjoy the latest and classic music and videos in multiple languages!</p>
                            <!-- <a href="#" class="site-btn">Download Now</a> -->
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hr-img">
                            <img src="img/hero-bg.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- You can add more slides if needed -->
    </div>
</section>
<!-- Hero section end -->

<!-- myyyyyyyyy -->

    <section class="hero-layout">
        <div class="wave-animation"></div>
        <div class="container hero-content">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="hero-title">SoundWave</h1>
                    
                    <p class="mb-4">
                        Music is more than just sound — it's an emotion that connects hearts, tells stories, and creates memories. 
                        Every beat, rhythm, and melody carries the power to heal, inspire, and express what words cannot. 
                        From soothing acoustic tones to electrifying bass drops, the world of sound invites you to feel, dream, and live through music.
                    </p>

                    <p class="hero-subtitle">Immerse yourself in the world of music</p>
                    
                    <div class="d-flex flex-wrap justify-content-center mb-5">
                        <button class="btn music-btn" data-bs-toggle="collapse" href="#discoverMusic" role="button" aria-expanded="false" aria-controls="discoverMusic">
                            <i class="bi bi-search"></i>  Music
                        </button>
                        <button class="btn music-btn" type="button" data-bs-toggle="collapse" data-bs-target="#createPlaylists" aria-expanded="false" aria-controls="createPlaylists">
                            <i class="bi bi-music-note-list"></i> Create Playlists
                        </button>
                        <button class="btn music-btn" type="button" id="exploreAllBtn" aria-expanded="false">
                            <i class="bi bi-grid-3x3-gap"></i> Explore All
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="collapse music-collapse" id="discoverMusic">
                        <div class="music-card">
                            <h3><i class="bi bi-compass"></i>  Music</h3>
                            <p>Explore our vast collection of tracks from artists around the world. Find your next favorite song with our personalized recommendations.</p>
                            
                            <ul class="music-features">
                                <li><i class="bi bi-check-circle-fill"></i> Personalized recommendations based on your taste</li>
                                <li><i class="bi bi-check-circle-fill"></i> New releases every Friday</li>
                                <li><i class="bi bi-check-circle-fill"></i> Curated playlists for every mood</li>
                                <li><i class="bi bi-check-circle-fill"></i> Exclusive content from top artists</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="collapse music-collapse" id="createPlaylists">
                        <div class="music-card">
                            <h3><i class="bi bi-collection-play"></i>  Playlists</h3>
                            <p>Organize your favorite tracks into custom playlists. Share them with friends or keep them private for your personal enjoyment.</p>
                            
                            <ul class="music-features">
                                <li><i class="bi bi-check-circle-fill"></i> Unlimited playlists with unlimited tracks</li>
                                <li><i class="bi bi-check-circle-fill"></i> Collaborative playlists with friends</li>
                                <li><i class="bi bi-check-circle-fill"></i> Smart playlist suggestions</li>
                                <li><i class="bi bi-check-circle-fill"></i> Offline listening for premium users</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle the "Explore All" button functionality
        document.getElementById('exploreAllBtn').addEventListener('click', function() {
            const collapses = document.querySelectorAll('.music-collapse');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle all collapses
            collapses.forEach(collapse => {
                const bsCollapse = new bootstrap.Collapse(collapse, {
                    toggle: false
                });
                
                if (isExpanded) {
                    bsCollapse.hide();
                } else {
                    bsCollapse.show();
                }
            });
            
            // Update the button's aria-expanded attribute
            this.setAttribute('aria-expanded', !isExpanded);
        });
        
        // Simple audio player functionality
        document.querySelectorAll('.play-btn').forEach(button => {
            button.addEventListener('click', function() {
                const icon = this.querySelector('i');
                
                if (icon.classList.contains('bi-play-fill')) {
                    icon.classList.remove('bi-play-fill');
                    icon.classList.add('bi-pause-fill');
                    
                    // Simulate progress
                    const progressBar = this.parentElement.querySelector('.progress-bar');
                    let width = 40;
                    const interval = setInterval(() => {
                        if (width >= 100) {
                            clearInterval(interval);
                            icon.classList.remove('bi-pause-fill');
                            icon.classList.add('bi-play-fill');
                            progressBar.style.width = '0%';
                        } else {
                            width += 1;
                            progressBar.style.width = width + '%';
                        }
                    }, 500);
                } else {
                    icon.classList.remove('bi-pause-fill');
                    icon.classList.add('bi-play-fill');
                }
            });
        });
    </script>


<!-- section layout -->







<!-- Intro section -->
<section class="intro-section spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="section-title">
                    <h2>Unlimited Access to Music & Videos</h2>
                </div>
            </div>
            <div class="col-lg-6">
                <p>Browse, listen, and watch your favorite songs and videos by album, artist, year, genre, and language. Register to rate and review your favorites!</p>
                <a href="account.php" class="site-btn">Register Now</a>
            </div>
        </div>
    </div>
</section>
<!-- Intro section end -->






	<!-- Intro section -->
    <section style="padding: 100px 20px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #ffffff; overflow: hidden;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; flex-wrap: wrap;">

            <!-- Text Column -->
            <div style="flex: 1; min-width: 300px; padding-right: 40px; box-sizing: border-box; animation: slideInFromLeft 1.2s ease-out;">
                <div style="margin-bottom: 20px;">
                    <h2 style="font-size: 3rem; font-weight: 700; line-height: 1.2; margin: 0 0 20px 0;">
                        Your Sonic Journey Begins Here
                    </h2>
                </div>
                <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px; color: #e0e0e0;">
                    Dive into our vast library of over 100,000 high-quality tracks, spanning every genre imaginable. From cinematic scores that elevate your visuals to ambient soundscapes that inspire creativity, find the perfect audio for any project. All our music is royalty-free, ready for instant download.
                </p>
                <!-- <a href="#"
                   style="display: inline-block; background-color: #ff6b6b; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);"
                   onmouseover="this.style.backgroundColor='#ff5252'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 107, 0.6)';"
                   onmouseout="this.style.backgroundColor='#ff6b6b'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 107, 0.4)';">
                    
                </a> -->
            </div>

            <!-- Image Column -->
            <div style="flex: 1; min-width: 300px; text-align: center; animation: slideInFromRight 1.2s ease-out;">
                <!-- Replace the src with your desired image URL -->
			<img src="https://t3.ftcdn.net/jpg/05/99/17/30/360_F_599173089_foA2mPZ2Ija1z25NWjHWQwB4Ujpezdii.jpg" alt="Intro Image">


                     <!-- style="max-width: 90%; height: auto; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); animation: gentle-float 6s ease-in-out infinite;"> -->
            </div>

        </div>
    </section>


			</div>
			



<!-- Latest Music  --><section class="latest-section spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title">
                    <div class="section-title text-center" style="margin-bottom:40px;">
                        <h1 style="font-size:36px; font-weight:800; color:black;">
                            Latest <span style="color:#ff0057;">Music</span>
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $music_sql = "SELECT m.*, a.artist_name, a.artist_image, al.album_name, al.cover_image AS album_cover_image FROM music m 
                                  LEFT JOIN artist a ON m.artist_id = a.artist_id 
                                  LEFT JOIN album al ON m.album_id = al.album_id 
                                  ORDER BY m.created_at DESC LIMIT 5";
                    $music_res = mysqli_query($conn, $music_sql);
                    while($music = mysqli_fetch_assoc($music_res)) {
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="music-card clickable-card"
                             style="transform: scale(0.85);
                                    margin: 15px auto;
                                    padding: 15px;
                                    border: 2px solid #ff0057;
                                    border-radius: 12px;
                                    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                                    background-color: #fff;
                                    width: 90%;
                                    transition: all 0.3s ease;
                                    cursor: pointer;"
                             onclick="window.location.href='music.php'">
                            <div class="media align-items-center">
                                <!-- Album Cover Thumbnail -->
                                <div class="position-relative">
                                    <img src="<?php echo !empty($music['album_cover_image']) ? $music['album_cover_image'] : 'https://picsum.photos/seed/album/80/80.jpg'; ?>" class="album-cover mr-3" alt="Album Cover"style="width:70px; height:70px; border-radius:10px; margin-right:10px; border:2px solid #ff0057;">
                                    <div class="play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                                
                                <!-- Artist Thumbnail -->
                                <img src="<?php echo !empty($music['artist_image']) ? $music['artist_image'] : 'https://picsum.photos/seed/artist/60/60.jpg'; ?>" class="artist-avatar mr-3" alt="Artist"style="width:55px; height:55px; border-radius:50%; border:2px solid #ff0057; margin-right:10px;">
                                
                                <div class="media-body">
                                    <h5 class="music-title">
                                        <?php echo htmlspecialchars($music['title']); ?>
                                        <?php if($music['is_new']) { ?>
                                            <span class="new-badge">NEW</span>
                                        <?php } ?>
                                    </h5>
                                    <div class="music-meta">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($music['artist_name']); ?></span>
                                        <span><i class="fas fa-compact-disc"></i> <?php echo htmlspecialchars($music['album_name']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($music['year']); ?></span>
                                    </div>
                                    <p class="music-description"><?php echo htmlspecialchars($music['description']); ?></p>
                                    <audio controls class="audio-player" onclick="event.stopPropagation();">
                                        <source src="<?php echo htmlspecialchars($music['file_path']); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.clickable-card:hover {
    transform: scale(0.85) translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
</style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event to play buttons
            const playButtons = document.querySelectorAll('.play-button');
            const audioPlayers = document.querySelectorAll('.audio-player');
            
            playButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    // Pause all other audio players
                    audioPlayers.forEach((player, i) => {
                        if (i !== index) {
                            player.pause();
                            playButtons[i].innerHTML = '<i class="fas fa-play"></i>';
                        }
                    });
                    
                    // Toggle play/pause for current audio
                    const audio = audioPlayers[index];
                    if (audio.paused) {
                        audio.play();
                        button.innerHTML = '<i class="fas fa-pause"></i>';
                    } else {
                        audio.pause();
                        button.innerHTML = '<i class="fas fa-play"></i>';
                    }
                });
            });
            
            // Update play button icon when audio ends
            audioPlayers.forEach((audio, index) => {
                audio.addEventListener('ended', function() {
                    playButtons[index].innerHTML = '<i class="fas fa-play"></i>';
                });
            });
        });
    </script>



<!-- Remove Subscription and Premium sections as per requirements -->
 <!-- video -->
<section class="latest-section spad" style="padding:80px 0; background:#fff; color:#111;">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="section-title text-center" style="margin-bottom:50px;">
          <h2 style="font-size:38px; color:#111;">Latest <span style="color:#ff0057;">Music</span> Videos</h2>
        </div>
        <div class="row" id="videos-container">
          <?php
          include 'db.php';
          $video_sql = "
            SELECT v.*, a.artist_name, al.album_name 
            FROM video v
            LEFT JOIN artist a ON v.artist_id=a.artist_id
            LEFT JOIN album al ON v.album_id=al.album_id
            ORDER BY v.created_at DESC
            LIMIT 5";
          $video_res = mysqli_query($conn, $video_sql);
          while($video = mysqli_fetch_assoc($video_res)):
          ?>
            <div class="col-lg-4 col-md-6 mb-5">
              <div class="video-card" 
                   style="background:#f9f9f9; border-radius:16px; padding:25px; min-height:520px; box-shadow:0 6px 15px rgba(0,0,0,0.1); transition:all 0.4s ease; cursor: pointer;"
                   onclick="window.location.href='video.php'">
                <div style="position:relative; overflow:hidden; border-radius:12px;">
                  <img src="<?= htmlspecialchars($video['thumbnail_img'] ?? 'img/default_video.jpg'); ?>" 
                       alt="<?= htmlspecialchars($video['title']); ?>" 
                       style="width:100%; height:300px; object-fit:cover; transition:transform 0.3s;">
                  <?php if (!empty($video['is_new'])): ?>
                    <span style="position:absolute; top:12px; left:12px; background:#ff0057; color:#fff; padding:6px 12px; font-size:13px; border-radius:5px;">NEW</span>
                  <?php endif; ?>
                </div>

                <h5 style="margin-top:20px; color:#111; font-weight:700; font-size:20px;">
                  <?= htmlspecialchars($video['title']); ?>
                </h5>
                <div style="font-size:15px; color:#555; margin-top:5px;">
                  <?= htmlspecialchars($video['artist_name'] ?? 'Unknown Artist'); ?> | <?= htmlspecialchars($video['album_name'] ?? 'No Album'); ?>
                </div>
                <p style="font-size:14px; color:#777; margin-top:10px; line-height:1.5;">
                  <?= htmlspecialchars($video['description'] ?? 'No description available'); ?>
                </p>

                <div style="text-align:center; margin-top:20px;">
                  <a href="<?= htmlspecialchars($video['file_path']); ?>" target="_blank" 
                     class="btn" 
                     style="background:#ff0057; color:#fff; padding:10px 28px; border-radius:8px; font-weight:600; text-decoration:none; font-size:15px; transition:all 0.3s;"
                     onclick="event.stopPropagation();">
                    ▶ Watch
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        
        <!-- View More Button -->
        <!-- <div class="text-center mt-4">
          <a href="video.php" class="btn" style="background:#ff0057; color:#fff; padding:12px 35px; border-radius:8px; font-weight:600; text-decoration:none; font-size:16px; transition:all 0.3s; display:inline-block; margin-top:20px;">
            View More Videos
          </a>
        </div> -->
      </div>
    </div>
  </div>
</section>

<style>
.video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}
</style>
<?php include 'footer.php' ?>