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

<!-- Contact section -->
<section class="contact-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 p-0">
                <!-- Map -->
                <div class="map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d10784.188505644011!2d19.053119335158936!3d47.48899529453826!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sbd!4v1543907528304" style="border:0" allowfullscreen></iframe>
                </div>
            </div>
            <div class="col-lg-6 p-0">
                <div class="contact-warp">
                    <span class="music-note">♪</span>
                    <span class="music-note">♫</span>
                    <span class="music-note">♬</span>
                    <span class="music-note">♩</span>
                    
                    <div class="section-title mb-0">
                        <h2>Get in touch</h2>
                    </div>
                    <p>At SolMusic, we believe in the power of music to connect souls and create unforgettable experiences. Whether you're an artist looking to share your talent, a venue seeking performers, or a music enthusiast wanting to collaborate, we're here to help. Reach out to us and let's create something extraordinary together.</p>
                    <ul>
                        <li>Main Road , No 25/11, Music District</li>
                        <li>+34 556788 3221</li>
                        <li>contact@solmusic.com</li>
                    </ul>
                    <form class="contact-from" action="submit_contact.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="name" placeholder="Your name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" placeholder="Your e-mail" required>
                            </div>
                            <div class="col-md-12">
                                <input type="text" name="subject" placeholder="Subject">
                                <textarea name="message" placeholder="Tell us about your musical journey, collaboration ideas, or how we can help bring your music to life..." required></textarea>

                                <label>
                                    <input type="checkbox" name="interested_in_production" value="1">
                                    Interested in music production services
                                </label><br>

                                <label>
                                    <input type="checkbox" name="updates_subscription" value="1">
                                    Send me updates about new releases and events
                                </label><br>

                                <button type="submit" class="site-btn">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Contact section end -->

<?php include 'footer.php' ?>