<?php
ob_start();
include 'db.php';      // must set $conn (mysqli)
include 'auth_check.php';
include 'header.php';

// Admin check (keeps your original behavior)
if (!isset($_SESSION['role'])) {
    header("Location: register.php"); exit();
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php"); exit();
}

// Helper: run query and return single value
function single_value($conn, $sql) {
    $res = $conn->query($sql);
    $row = $res->fetch_array();
    return $row ? $row[0] : 0;
}

// STAT CARDS
$totalUsers   = single_value($conn, "SELECT COUNT(*) FROM users");
$totalArtists = single_value($conn, "SELECT COUNT(*) FROM artist");
$totalMusic   = single_value($conn, "SELECT COUNT(*) FROM music");
$totalVideos  = single_value($conn, "SELECT COUNT(*) FROM video");
$avgRatingRaw = single_value($conn, "SELECT AVG(rating_value) FROM rating");
$avgRating    = $avgRatingRaw ? round($avgRatingRaw, 2) : 'N/A';
$new7days     = single_value($conn, "SELECT SUM(cnt) FROM (
                    SELECT COUNT(*) as cnt FROM music WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    UNION ALL
                    SELECT COUNT(*) as cnt FROM video WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ) t");

// GENRE distribution (music only)
$genreLabels = [];
$genreData   = [];
$gq = "SELECT g.genre_name, COUNT(m.music_id) AS cnt
       FROM genre g
       LEFT JOIN music m ON g.genre_id = m.genre_id
       GROUP BY g.genre_id
       ORDER BY cnt DESC";
$res = $conn->query($gq);
while ($r = $res->fetch_assoc()) {
    $genreLabels[] = $r['genre_name'];
    $genreData[]   = (int)$r['cnt'];
}

// LANGUAGE distribution (music + video)
$langLabels = [];
$langData   = [];
$lq = "SELECT l.language_name, 
             (SELECT COUNT(*) FROM music m WHERE m.language_id = l.language_id)
           + (SELECT COUNT(*) FROM video v WHERE v.language_id = l.language_id) AS cnt
       FROM language l
       ORDER BY cnt DESC";
$res = $conn->query($lq);
while ($r = $res->fetch_assoc()) {
    $langLabels[] = $r['language_name'];
    $langData[]   = (int)$r['cnt'];
}

// Releases per year (music + video)
$yearLabels = [];
$yearData   = [];
$yq = "SELECT t.year, COUNT(*) AS cnt FROM (
         SELECT year FROM music
         UNION ALL
         SELECT year FROM video
       ) t
       GROUP BY t.year
       ORDER BY t.year";
$res = $conn->query($yq);
while ($r = $res->fetch_assoc()) {
    $yearLabels[] = $r['year'];
    $yearData[]   = (int)$r['cnt'];
}

// New content by month (last 6 months combined music+video)
$monthLabels = [];
$monthData   = [];
$mq = "SELECT DATE_FORMAT(months.month_start,'%b %Y') AS label, 
              COALESCE(SUM(cnt),0) AS cnt
       FROM (
         SELECT DATE_FORMAT(DATE_SUB(LAST_DAY(NOW()), INTERVAL (a.a + (10 * b.a)) MONTH), '%Y-%m-01') AS month_start
         FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) a
         CROSS JOIN (SELECT 0 AS a) b
       ) months
       LEFT JOIN (
         SELECT DATE_FORMAT(created_at,'%Y-%m-01') AS mstart, COUNT(*) AS cnt FROM music GROUP BY mstart
         UNION ALL
         SELECT DATE_FORMAT(created_at,'%Y-%m-01') AS mstart, COUNT(*) AS cnt FROM video GROUP BY mstart
       ) t ON t.mstart = months.month_start
       GROUP BY months.month_start
       ORDER BY months.month_start DESC
       LIMIT 6";
$res = $conn->query($mq);
$rows = [];
while ($r = $res->fetch_assoc()) {
    array_unshift($monthLabels, $r['label']);       // reverse order - oldest first
    array_unshift($monthData, (int)$r['cnt']);
}

// fallbacks if empty
if (empty($genreLabels)) { $genreLabels = ['No Data']; $genreData = [0]; }
if (empty($langLabels))  { $langLabels = ['No Data']; $langData = [0]; }
if (empty($yearLabels))  { $yearLabels = ['No Data']; $yearData = [0]; }
if (empty($monthLabels)) { $monthLabels = ['No Data']; $monthData = [0]; }

?>



<style>
  body {
      background-color: #f5f8fb;
      font-family: 'Poppins', sans-serif;
    }
    .sidebar {
      width: 240px;
      height: 100vh;
      background: #f3f7fb;
      position: fixed;
      left: 0;
      top: 0;
      padding: 20px;
    }
    .sidebar h4 {
      color: #007bff;
      font-weight: 700;
    }
    .sidebar a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: #333;
      padding: 10px 0;
    }
    .sidebar a.active {
      background: #e9f3ff;
      border-radius: 8px;
      color: #007bff;
    }
    .main-content {
      margin-left: 260px;
      padding: 20px;
    }
    .stat-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 20px;
      text-align: center;
    }
    canvas {
      width: 100% !important;
      height: 350px !important;
    }
</style>

<div class="container-fluid pt-4 px-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="text-primary">Welcome to Dashboard</h2>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-4">
        <div class="col-sm-6 col-xl-3" data-aos="fade-up">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-users fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">Total Users</p>
                    <h6 class="mb-0"><?= number_format($totalUsers); ?></h6>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="50">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-user-tie fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">Total Artists</p>
                    <h6 class="mb-0"><?= number_format($totalArtists); ?></h6>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="100">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-music fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">Total Music</p>
                    <h6 class="mb-0"><?= number_format($totalMusic); ?></h6>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="150">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-video fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">Total Videos</p>
                    <h6 class="mb-0"><?= number_format($totalVideos); ?></h6>
                </div>
            </div>
        </div>

        <!-- second row of stats -->
        <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-star fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">Avg Rating</p>
                    <h6 class="mb-0"><?= $avgRating; ?></h6>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3" data-aos="fade-up" data-aos-delay="250">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4 stat-card">
                <i class="fa fa-bolt fa-3x text-primary"></i>
                <div class="ms-3 text-end">
                    <p class="mb-2">New (7 days)</p>
                    <h6 class="mb-0"><?= number_format($new7days); ?></h6>
                </div>
            </div>
        </div>

    </div>

    <!-- CHARTS -->
    <div class="row g-4 mt-3">
        <div class="col-lg-6" data-aos="fade-right">
            <div class="card chart-card p-3">
                <div class="card-title mb-2">Genre Distribution (Music)</div>
                <canvas id="genreDonut"></canvas>
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-left">
            <div class="card chart-card p-3">
                <div class="card-title mb-2">Language Distribution (Music + Video)</div>
                <canvas id="langPie"></canvas>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up">
            <div class="card chart-card p-3">
                <div class="card-title mb-2">Releases per Year (Music + Video)</div>
                <canvas id="yearBar"></canvas>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card chart-card p-3">
                <div class="card-title mb-2">Recent Activity (Last 6 months)</div>
                <canvas id="recentLine"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
  // Init AOS
  AOS.init({
    duration: 700,
    easing: 'ease-out-cubic',
    once: true
  });

  // Chart data from PHP
  const genreLabels = <?= json_encode($genreLabels); ?>;
  const genreData   = <?= json_encode($genreData); ?>;

  const langLabels = <?= json_encode($langLabels); ?>;
  const langData   = <?= json_encode($langData); ?>;

  const yearLabels = <?= json_encode($yearLabels); ?>;
  const yearData   = <?= json_encode($yearData); ?>;

  const monthLabels = <?= json_encode($monthLabels); ?>;
  const monthData   = <?= json_encode($monthData); ?>;

  // Donut - Genre
  new Chart(document.getElementById('genreDonut'), {
    type: 'doughnut',
    data: {
      labels: genreLabels,
      datasets: [{
        data: genreData,
        // Chart.js auto generates colors if not provided, but we keep config minimal
        hoverOffset: 8
      }]
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  // Pie - Language
  new Chart(document.getElementById('langPie'), {
    type: 'pie',
    data: {
      labels: langLabels,
      datasets: [{
        data: langData,
        hoverOffset: 6
      }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });

  // Bar - Releases per year
  new Chart(document.getElementById('yearBar'), {
    type: 'bar',
    data: {
      labels: yearLabels,
      datasets: [{
        label: 'Releases',
        data: yearData,
        borderRadius: 6,
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });

  // Line - Recent months
  new Chart(document.getElementById('recentLine'), {
    type: 'line',
    data: {
      labels: monthLabels,
      datasets: [{
        label: 'New items',
        data: monthData,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
      plugins: { legend: { display: false } }
    }
  });
</script>

<?php require_once 'footer.php'; ?>
