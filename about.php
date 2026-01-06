<?php 
// 1. Start session and include database connection
session_start();
include 'db.php';

// 2. Check if user is logged in (needed for both the page and the AJAX handler)
 $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
 $userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

?><?php
include 'header.php'?>


<!-- Preloader -->

<!-- Hero Section -->
<section class="hero-section">
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
    
    <div class="hero-content">
        <h1 class="hero-title">About <span>SolMusic</span></h1>
        <p class="hero-subtitle">Your Ultimate Music Experience</p>
        <p class="hero-description">
            We are passionate about music and dedicated to providing you with the best possible listening experience. 
            Our platform brings together millions of songs from artists around the world, all in one place.
        </p>
        <div class="hero-buttons">
            <a href="genere.php" class="hero-btn">Discover More</a>
            <a href="contact.php" class="hero-btn hero-btn-outline">Contact Us</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section section-alt" id="about">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Our <span>Story</span></h2>
            <p>Learn about our journey and what drives us to create the best music platform for you</p>
        </div>
        
        <div class="about-content">
            <div class="about-image slide-in-left">
                <img src="https://picsum.photos/seed/about-story/600/400.jpg" alt="Our Story">
            </div>
            
            <div class="about-text slide-in-right">
                <h3>Creating Musical Experiences Since 2010</h3>
                <p>
                    SolMusic was founded with a simple mission: to make music accessible to everyone, everywhere. 
                    What started as a small startup has grown into a global platform that connects millions of music lovers 
                    with the artists they love.
                </p>
                <p>
                    We believe in the power of music to transform lives, bring people together, and create unforgettable moments. 
                    Our team works tirelessly to ensure that every aspect of your experience on SolMusic is nothing short of exceptional.
                </p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <p>Over 50 million songs in our library</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <p>Available in 190+ countries worldwide</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <p>Personalized recommendations powered by AI</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <p>Supporting both established and emerging artists</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="section" id="timeline">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Our <span>Journey</span></h2>
            <p>Milestones that shaped our company and brought us to where we are today</p>
        </div>
        
        <div class="timeline">
            <div class="timeline-item left">
                <div class="timeline-content">
                    <h3>2010 - The Beginning</h3>
                    <p>SolMusic was founded by three music enthusiasts in a small garage with a big dream.</p>
                </div>
            </div>
            
            <div class="timeline-item right">
                <div class="timeline-content">
                    <h3>2012 - First Million Users</h3>
                    <p>We reached our first million users and expanded our music library to include international artists.</p>
                </div>
            </div>
            
            <div class="timeline-item left">
                <div class="timeline-content">
                    <h3>2015 - Mobile App Launch</h3>
                    <p>Our mobile app was launched, making music accessible on the go for our users.</p>
                </div>
            </div>
            
            <div class="timeline-item right">
                <div class="timeline-content">
                    <h3>2018 - Global Expansion</h3>
                    <p>SolMusic became available in 190+ countries, truly becoming a global music platform.</p>
                </div>
            </div>
            
            <div class="timeline-item left">
                <div class="timeline-content">
                    <h3>2020 - AI-Powered Recommendations</h3>
                    <p>We introduced our revolutionary AI recommendation system, transforming how users discover music.</p>
                </div>
            </div>
            
            <div class="timeline-item right">
                <div class="timeline-content">
                    <h3>2023 - The Present</h3>
                    <p>Today, we serve over 100 million users worldwide and continue to innovate in the music streaming space.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->


<section class="section section-alt" id="team">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Meet Our <span>Team</span></h2>
            <p>The talented individuals behind SolMusic who work tirelessly to bring you the best music experience</p>
        </div>

        <div class="team-grid">
            <?php
            $query = mysqli_query($conn, "SELECT * FROM admin ORDER BY admin_id ASC");

            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) {
                    // default image if none uploaded
                    $imagePath = !empty($row['profile_image']) ? $row['profile_image'] : 'images/team/default.jpg';
                    $role = !empty($row['role']) ? $row['role'] : 'Team Member';
                    ?>

                    <div class="team-member scale-in">
                        <div class="team-image">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <!-- <a href="#"><i class="fab fa-linkedin"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a> -->
                                    <!-- <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"><i class="fas fa-envelope"></i></a> -->
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo htmlspecialchars($role); ?></p>
                        </div>
                    </div>

                    <?php
                }
            } else {
                echo "<p>No team members found.</p>";
            }
            ?>
        </div>
    </div>
</section>


<!-- Stats Section -->
<?php

// Count videos
$video_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM video"))['total'];

// Count music
$music_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM music"))['total'];

// Count artists
$artist_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM artist"))['total'];

// Count genres
$genre_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM genre"))['total'];
?>

<!-- Stats Section -->
<section class="section section-alt" id="stats">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Our <span>Impact</span></h2>
            <p>Numbers that reflect our growth and creativity</p>
        </div>
        
        <div class="stats-container">
            <div class="stat-item scale-in">
                <div class="stat-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-number" data-target="<?php echo $video_count; ?>">0</div>
                <div class="stat-label">Videos</div>
            </div>
            
            <div class="stat-item scale-in">
                <div class="stat-icon">
                    <i class="fas fa-music"></i>
                </div>
                <div class="stat-number" data-target="<?php echo $music_count; ?>">0</div>
                <div class="stat-label">Music Tracks</div>
            </div>
            
            <div class="stat-item scale-in">
                <div class="stat-icon">
                    <i class="fas fa-microphone"></i>
                </div>
                <div class="stat-number" data-target="<?php echo $artist_count; ?>">0</div>
                <div class="stat-label">Artists</div>
            </div>
            
            <div class="stat-item scale-in">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-number" data-target="<?php echo $genre_count; ?>">0</div>
                <div class="stat-label">Genres</div>
            </div>
        </div>
    </div>
</section>

<!-- Counter Animation -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll(".stat-number");
    const speed = 150; // Animation speed

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute("data-target");
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
});
</script>




<!-- JavaScript -->
<script>
// Preloader
window.addEventListener('load', function() {
    setTimeout(function() {
        document.querySelector('.preloader').classList.add('fade-out');
    }, 1000);
});

// Scroll Animations
const animateOnScroll = function() {
    const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementPosition < windowHeight - 100) {
            element.classList.add('show');
        }
    });
};

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);

// Timeline Animation
const timelineItems = document.querySelectorAll('.timeline-item');
const timelineObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
        }
    });
}, { threshold: 0.1 });

timelineItems.forEach(item => {
    timelineObserver.observe(item);
});

// Skill Bars Animation
const skillBars = document.querySelectorAll('.skill-progress');
const skillObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const width = entry.target.getAttribute('data-width');
            entry.target.style.width = width;
        }
    });
}, { threshold: 0.1 });

skillBars.forEach(bar => {
    skillObserver.observe(bar);
});

// Counter Animation
const counters = document.querySelectorAll('.stat-number');
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            const target = parseInt(entry.target.getAttribute('data-target'));
            const increment = target / 100;
            let count = 0;
            
            const updateCounter = () => {
                if (count < target) {
                    count += increment;
                    entry.target.textContent = Math.ceil(count).toLocaleString();
                    setTimeout(updateCounter, 20);
                } else {
                    entry.target.textContent = target.toLocaleString();
                    entry.target.classList.add('counted');
                }
            };
            
            updateCounter();
        }
    });
}, { threshold: 0.1 });

counters.forEach(counter => {
    counterObserver.observe(counter);
});

// Testimonial Slider
const testimonials = [
    {
        text: "SolMusic has completely transformed how I discover and enjoy music. The personalized recommendations are spot-on, and I've found so many new artists I love. It's my go-to app for all things music!",
        author: "Rachel Thompson",
        role: "Music Enthusiast",
        image: "https://picsum.photos/seed/user1/100/100.jpg"
    },
    {
        text: "As an independent artist, SolMusic has given me a platform to reach millions of listeners. The analytics tools help me understand my audience better, and the support team is always there to help.",
        author: "David Martinez",
        role: "Independent Artist",
        image: "https://picsum.photos/seed/user2/100/100.jpg"
    },
    {
        text: "I've tried many music streaming services, but SolMusic stands out with its intuitive interface and incredible music library. The sound quality is unmatched, and I love the curated playlists.",
        author: "Emily Johnson",
        role: "Premium Subscriber",
        image: "https://picsum.photos/seed/user3/100/100.jpg"
    }
];

let currentTestimonial = 0;
const testimonialText = document.querySelector('.testimonial-text');
const authorImage = document.querySelector('.author-image img');
const authorName = document.querySelector('.author-info h4');
const authorRole = document.querySelector('.author-info p');

function updateTestimonial() {
    testimonialText.style.opacity = '0';
    authorImage.style.opacity = '0';
    authorName.style.opacity = '0';
    authorRole.style.opacity = '0';
    
    setTimeout(() => {
        testimonialText.textContent = testimonials[currentTestimonial].text;
        authorImage.src = testimonials[currentTestimonial].image;
        authorName.textContent = testimonials[currentTestimonial].author;
        authorRole.textContent = testimonials[currentTestimonial].role;
        
        testimonialText.style.opacity = '1';
        authorImage.style.opacity = '1';
        authorName.style.opacity = '1';
        authorRole.style.opacity = '1';
    }, 300);
}

document.querySelector('.testimonial-control.next').addEventListener('click', () => {
    currentTestimonial = (currentTestimonial + 1) % testimonials.length;
    updateTestimonial();
});

document.querySelector('.testimonial-control.prev').addEventListener('click', () => {
    currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
    updateTestimonial();
});

// Auto-rotate testimonials
setInterval(() => {
    currentTestimonial = (currentTestimonial + 1) % testimonials.length;
    updateTestimonial();
}, 8000);

// Smooth Scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Contact Form
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    // Here you would normally send the data to a server
    // For this example, we'll just show a success message
    
    // Create success message
    const successMessage = document.createElement('div');
    successMessage.className = 'alert alert-success';
    successMessage.textContent = 'Thank you for your message! We will get back to you soon.';
    successMessage.style.padding = '15px';
    successMessage.style.marginTop = '20px';
    successMessage.style.borderRadius = '5px';
    successMessage.style.backgroundColor = 'rgba(46, 204, 113, 0.2)';
    successMessage.style.color = '#2ecc71';
    successMessage.style.border = '1px solid #2ecc71';
    
    // Add success message to the form
    this.appendChild(successMessage);
    
    // Reset form
    this.reset();
    
    // Remove success message after 5 seconds
    setTimeout(() => {
        successMessage.remove();
    }, 5000);
});

// Dynamic Music Notes
function createMusicNote() {
    const musicNote = document.createElement('div');
    musicNote.className = 'music-note';
    musicNote.textContent = ['♪', '♫', '♬', '♭', '♮', '♯'][Math.floor(Math.random() * 6)];
    musicNote.style.left = Math.random() * 100 + '%';
    musicNote.style.animationDuration = (Math.random() * 10 + 10) + 's';
    musicNote.style.opacity = Math.random() * 0.3 + 0.1;
    
    document.querySelector('.music-notes').appendChild(musicNote);
    
    // Remove music note after animation completes
    setTimeout(() => {
        musicNote.remove();
    }, 20000);
}

// Create music notes periodically
setInterval(createMusicNote, 3000);

// Parallax Effect
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallaxElements = document.querySelectorAll('.hero-section');
    
    parallaxElements.forEach(element => {
        const speed = 0.5;
        element.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

// Add active class to navigation based on scroll position
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('nav a');
    
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        
        if (pageYOffset >= sectionTop - 100) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === current) {
            link.classList.add('active');
        }
    });
});
</script>

<?php include 'footer.php' ?>