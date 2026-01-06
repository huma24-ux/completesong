<?php
// ====================== FIX 1: SET LIMITS AT THE TOP ======================
// Set PHP limits BEFORE any output - this is CRITICAL
@ini_set('upload_max_filesize', '500M');
@ini_set('post_max_size', '500M');
@ini_set('max_execution_time', '600');
@ini_set('max_input_time', '600');
@ini_set('memory_limit', '512M');

// Start output buffering to ensure no output before headers
ob_start();

// ====================== FIX 2: CHECK AND HANDLE LARGE UPLOADS ======================
// Check if this is a POST request with large content
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH'])) {
    $contentLength = $_SERVER['CONTENT_LENGTH'];
    $maxAllowed = 100 * 1024 * 1024; // 100MB limit for safety
    
    if ($contentLength > $maxAllowed) {
        // Clean buffer and show error
        ob_end_clean();
        die("<script>
            alert('File is too large! Maximum size is 100MB. Your file is ' + 
                Math.round($contentLength/(1024*1024)) + 'MB');
            window.location.href = 'video_view.php';
        </script>");
    }
}

// Now include other files
include 'db.php';
include 'auth_check.php';
include 'header.php';

$message = "";

// ====================== FIX 3: SIMPLIFIED UPLOAD HANDLER ======================
if (isset($_POST['save_video'])) {
    // Get form data
    $video_id     = intval($_POST['video_id'] ?? 0);
    $title        = trim($_POST['title']);
    $artist_id    = intval($_POST['artist_id'] ?? 0);
    $album_id     = intval($_POST['album_id'] ?? 0);
    $genre_id     = intval($_POST['genre_id'] ?? 0);
    $language_id  = intval($_POST['language_id'] ?? 0);
    $year         = trim($_POST['year']);
    $description  = trim($_POST['description']);
    $is_new       = isset($_POST['is_new']) ? 1 : 0;
    
    // SIMPLE VALIDATION - Allow more characters in title
    if (empty($title)) {
        $message = "<div class='alert alert-danger'>Title is required!</div>";
    } else {
        // File Uploads Directory
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Handle Video File - SIMPLIFIED
        $fileToSave = $_POST['old_file'] ?? '';
        if (!empty($_FILES['file_path']['name']) && $_FILES['file_path']['error'] == 0) {
            // Check file size (50MB max)
            if ($_FILES['file_path']['size'] > 50 * 1024 * 1024) {
                $message = "<div class='alert alert-danger'>Video file is too large! Maximum size is 50MB.</div>";
            } else {
                // Generate unique filename
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $_FILES['file_path']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['file_path']['tmp_name'], $targetPath)) {
                    $fileToSave = $fileName;
                    // Delete old file if exists and is different
                    if (!empty($_POST['old_file']) && $_POST['old_file'] != $fileName) {
                        $oldFile = $uploadDir . $_POST['old_file'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Failed to upload video file. Error: " . $_FILES['file_path']['error'] . "</div>";
                }
            }
        }
        
        // Handle Thumbnail - SIMPLIFIED
        $thumbToSave = $_POST['old_thumb'] ?? '';
        if (!empty($_FILES['thumbnail_img']['name']) && $_FILES['thumbnail_img']['error'] == 0 && empty($message)) {
            // Check if it's an image
            $imageInfo = getimagesize($_FILES['thumbnail_img']['tmp_name']);
            if ($imageInfo !== false) {
                // Generate unique filename
                $thumbName = time() . '_thumb_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $_FILES['thumbnail_img']['name']);
                $thumbPath = $uploadDir . $thumbName;
                
                if (move_uploaded_file($_FILES['thumbnail_img']['tmp_name'], $thumbPath)) {
                    $thumbToSave = $thumbName;
                    // Delete old thumbnail if exists and is different
                    if (!empty($_POST['old_thumb']) && $_POST['old_thumb'] != $thumbName) {
                        $oldThumb = $uploadDir . $_POST['old_thumb'];
                        if (file_exists($oldThumb)) {
                            unlink($oldThumb);
                        }
                    }
                }
            }
        }
        
        // If no error messages, save to database
        if (empty($message)) {
            // Use NULL for empty foreign keys
            $artist_val   = $artist_id > 0 ? $artist_id : "NULL";
            $album_val    = $album_id > 0 ? $album_id : "NULL";
            $genre_val    = $genre_id > 0 ? $genre_id : "NULL";
            $language_val = $language_id > 0 ? $language_id : "NULL";
            
            // Escape strings for safety
            $title = mysqli_real_escape_string($conn, $title);
            $year = mysqli_real_escape_string($conn, $year);
            $description = mysqli_real_escape_string($conn, $description);
            $fileToSave = mysqli_real_escape_string($conn, $fileToSave);
            $thumbToSave = mysqli_real_escape_string($conn, $thumbToSave);
            
            if ($video_id == 0) {
                // INSERT
                $sql = "INSERT INTO video 
                        (title, artist_id, album_id, genre_id, language_id, year, file_path, description, thumbnail_img, is_new, created_at)
                        VALUES (
                            '$title',
                            $artist_val,
                            $album_val,
                            $genre_val,
                            $language_val,
                            '$year',
                            '$fileToSave',
                            '$description',
                            '$thumbToSave',
                            '$is_new',
                            NOW()
                        )";
                $successMsg = "✅ Video added successfully!";
            } else {
                // UPDATE
                $sql = "UPDATE video SET 
                            title = '$title',
                            artist_id = $artist_val,
                            album_id = $album_val,
                            genre_id = $genre_val,
                            language_id = $language_val,
                            year = '$year',
                            file_path = '$fileToSave',
                            description = '$description',
                            thumbnail_img = '$thumbToSave',
                            is_new = '$is_new'
                        WHERE video_id = $video_id";
                $successMsg = "✏️ Video updated successfully!";
            }
            
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>$successMsg</div>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Database Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// ====================== FIX 4: DELETE HANDLER ======================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get file info before deleting
    $query = "SELECT file_path, thumbnail_img FROM video WHERE video_id = $id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        // Delete files
        if (!empty($row['file_path'])) {
            $filePath = "uploads/" . $row['file_path'];
            if (file_exists($filePath)) unlink($filePath);
        }
        if (!empty($row['thumbnail_img'])) {
            $thumbPath = "uploads/" . $row['thumbnail_img'];
            if (file_exists($thumbPath)) unlink($thumbPath);
        }
    }
    
    // Delete from database
    $deleteQuery = "DELETE FROM video WHERE video_id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Video deleted successfully!'); window.location.href='video_view.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error deleting video.'); window.location.href='video_view.php';</script>";
        exit;
    }
}

// ====================== FETCH DATA ======================
$result = mysqli_query($conn, "SELECT * FROM video ORDER BY video_id DESC");

// For dropdowns
$artists   = mysqli_query($conn, "SELECT artist_id, artist_name FROM artist ORDER BY artist_name");
$albums    = mysqli_query($conn, "SELECT album_id, album_name FROM album ORDER BY album_name");
$genres    = mysqli_query($conn, "SELECT genre_id, genre_name FROM genre ORDER BY genre_name");
$languages = mysqli_query($conn, "SELECT language_id, language_name FROM language ORDER BY language_name");

// End output buffering
ob_end_flush();
?>

<!-- ====================== FIX 5: HTML FORM WITH SIZE CHECK ====================== -->
<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h3 class="text-center mb-3">🎬 Video Management</h3>

    <?= $message; ?>

    <!-- File Size Warning -->
    <div class="alert alert-info mb-3">
        <strong>📁 File Upload Info:</strong><br>
        • Maximum file size: <strong id="maxSize"><?php echo ini_get('upload_max_filesize'); ?></strong><br>
        • Allowed video formats: MP4, AVI, MOV, WMV<br>
        • For files larger than 50MB, upload via FTP to 'uploads/' folder
    </div>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add Video
        </button>
    </div>

    <!-- Video Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Title</th><th>Artist</th><th>Album</th><th>Genre</th>
                    <th>Language</th><th>Year</th><th>Video</th><th>Thumbnail</th><th>New</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { 
            ?>
                <tr>
                    <td><?= $row['video_id']; ?></td>
                    <td class="text-start"><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= $row['artist_id']; ?></td>
                    <td><?= $row['album_id']; ?></td>
                    <td><?= $row['genre_id']; ?></td>
                    <td><?= $row['language_id']; ?></td>
                    <td><?= htmlspecialchars($row['year']); ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])) { ?>
                            <a href="uploads/<?= htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-play"></i> Play
                            </a>
                        <?php } else { echo "-"; } ?>
                    </td>
                    <td>
                        <?php if (!empty($row['thumbnail_img'])) { ?>
                            <img src="uploads/<?= htmlspecialchars($row['thumbnail_img']); ?>" width="60" height="60" class="rounded border" style="object-fit:cover;">
                        <?php } else { echo "-"; } ?>
                    </td>
                    <td>
                        <?= $row['is_new'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning mb-1" onclick="openEditModal(
                            '<?= $row['video_id']; ?>',
                            '<?= addslashes($row['title']); ?>',
                            '<?= $row['artist_id']; ?>',
                            '<?= $row['album_id']; ?>',
                            '<?= $row['genre_id']; ?>',
                            '<?= $row['language_id']; ?>',
                            '<?= $row['year']; ?>',
                            `<?= addslashes($row['description']); ?>`,
                            '<?= addslashes($row['file_path']); ?>',
                            '<?= addslashes($row['thumbnail_img']); ?>',
                            '<?= $row['is_new']; ?>'
                        )" title="Edit">
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class="btn btn-sm btn-danger mb-1" onclick="confirmDelete(<?= $row['video_id']; ?>)" title="Delete">
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="11" class="text-center py-4">No videos found. Add your first video!</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data" id="videoForm" onsubmit="return validateVideoForm()">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Video</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="video_id" id="video_id" value="0">
            <input type="hidden" name="old_file" id="old_file">
            <input type="hidden" name="old_thumb" id="old_thumb">
            
            <div class="row g-3">
                <!-- Required Fields -->
                <div class="col-md-12">
                    <label class="form-label">Video Title *</label>
                    <input type="text" name="title" id="title" class="form-control" required 
                           placeholder="Enter video title" maxlength="200">
                </div>
                
                <!-- Optional Fields -->
                <div class="col-md-6">
                    <label class="form-label">Artist</label>
                    <select name="artist_id" id="artist_id" class="form-select">
                        <option value="">-- Select Artist --</option>
                        <?php 
                        mysqli_data_seek($artists, 0); 
                        while ($a = mysqli_fetch_assoc($artists)) { 
                        ?>
                            <option value="<?= $a['artist_id']; ?>"><?= htmlspecialchars($a['artist_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Album</label>
                    <select name="album_id" id="album_id" class="form-select">
                        <option value="">-- Select Album --</option>
                        <?php 
                        mysqli_data_seek($albums, 0); 
                        while ($al = mysqli_fetch_assoc($albums)) { 
                        ?>
                            <option value="<?= $al['album_id']; ?>"><?= htmlspecialchars($al['album_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Genre</label>
                    <select name="genre_id" id="genre_id" class="form-select">
                        <option value="">-- Select Genre --</option>
                        <?php 
                        mysqli_data_seek($genres, 0); 
                        while ($g = mysqli_fetch_assoc($genres)) { 
                        ?>
                            <option value="<?= $g['genre_id']; ?>"><?= htmlspecialchars($g['genre_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Language</label>
                    <select name="language_id" id="language_id" class="form-select">
                        <option value="">-- Select Language --</option>
                        <?php 
                        mysqli_data_seek($languages, 0); 
                        while ($l = mysqli_fetch_assoc($languages)) { 
                        ?>
                            <option value="<?= $l['language_id']; ?>"><?= htmlspecialchars($l['language_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" id="year" class="form-control" 
                           min="1900" max="<?= date('Y'); ?>" placeholder="2024">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" 
                              rows="3" placeholder="Enter video description..."></textarea>
                </div>
                
                <!-- File Uploads -->
                <div class="col-md-6">
                    <label class="form-label">Video File *</label>
                    <input type="file" name="file_path" id="file_path" class="form-control" 
                           accept=".mp4,.avi,.mov,.wmv,.flv,.mkv" required>
                    <div class="form-text">
                        Max 50MB. MP4, AVI, MOV, WMV, FLV, MKV
                        <div id="fileSizeInfo" class="text-danger small"></div>
                        <div id="currentFile" class="text-info small mt-1"></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Thumbnail Image</label>
                    <input type="file" name="thumbnail_img" id="thumbnail_img" class="form-control" 
                           accept="image/*">
                    <div class="form-text">
                        JPG, PNG, GIF, WebP (Recommended: 300x300px)
                        <div id="currentThumb" class="text-info small mt-1"></div>
                        <div id="thumbPreview" class="mt-2"></div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_new" id="is_new" class="form-check-input" value="1">
                        <label for="is_new" class="form-check-label">
                            <i class="fas fa-star text-warning"></i> Mark as New Release
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_video" class="btn btn-success">
            <i class="fas fa-save"></i> Save Video
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ====================== FIX 6: JAVASCRIPT VALIDATION ====================== -->
<script>
// Maximum file size from PHP
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes

// Open Add Modal
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add New Video";
    document.getElementById("video_id").value = "0";
    document.getElementById("old_file").value = "";
    document.getElementById("old_thumb").value = "";
    document.getElementById("currentFile").innerHTML = "";
    document.getElementById("currentThumb").innerHTML = "";
    document.getElementById("thumbPreview").innerHTML = "";
    document.getElementById("file_path").required = true;
    
    // Reset form
    const form = document.getElementById('videoForm');
    form.reset();
}

// Open Edit Modal
function openEditModal(id, title, artist, album, genre, lang, year, desc, file, thumb, isNew) {
    document.getElementById("modalTitle").innerText = "Edit Video";
    document.getElementById("video_id").value = id;
    document.getElementById("old_file").value = file;
    document.getElementById("old_thumb").value = thumb;
    document.getElementById("title").value = title;
    document.getElementById("artist_id").value = artist;
    document.getElementById("album_id").value = album;
    document.getElementById("genre_id").value = genre;
    document.getElementById("language_id").value = lang;
    document.getElementById("year").value = year;
    document.getElementById("description").value = desc;
    document.getElementById("is_new").checked = (isNew == 1);
    document.getElementById("file_path").required = false;
    
    // Show current files
    if (file) {
        document.getElementById("currentFile").innerHTML = 
            `<i class="fas fa-file-video"></i> Current: ${file}`;
    }
    if (thumb) {
        document.getElementById("currentThumb").innerHTML = 
            `<i class="fas fa-image"></i> Current: ${thumb}`;
        document.getElementById("thumbPreview").innerHTML = 
            `<img src="uploads/${thumb}" width="100" class="img-thumbnail">`;
    }
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('videoModal'));
    modal.show();
}

// Delete Confirmation
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this video? This action cannot be undone!")) {
        window.location.href = 'video_view.php?delete=' + id;
    }
}

// Validate Form Before Submit
function validateVideoForm() {
    // Check title
    const title = document.getElementById('title').value.trim();
    if (title.length < 2) {
        alert('Please enter a valid title (at least 2 characters)');
        return false;
    }
    
    // Check if video file is required (for new videos only)
    const videoId = document.getElementById('video_id').value;
    const fileInput = document.getElementById('file_path');
    
    if (videoId == 0 && fileInput.files.length === 0) {
        alert('Please select a video file for new videos');
        return false;
    }
    
    // Check file size if file is selected
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        if (fileSize > MAX_FILE_SIZE) {
            const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);
            alert(`File is too large! ${sizeMB}MB exceeds maximum 50MB limit.`);
            return false;
        }
    }
    
    // Check thumbnail size if selected
    const thumbInput = document.getElementById('thumbnail_img');
    if (thumbInput.files.length > 0) {
        const thumbSize = thumbInput.files[0].size;
        if (thumbSize > 5 * 1024 * 1024) { // 5MB limit for thumbnails
            alert('Thumbnail image is too large! Maximum size is 5MB.');
            return false;
        }
    }
    
    return true; // All validation passed
}

// Real-time file size validation
document.getElementById('file_path').addEventListener('change', function(e) {
    const fileSizeInfo = document.getElementById('fileSizeInfo');
    fileSizeInfo.innerHTML = '';
    
    if (this.files.length > 0) {
        const file = this.files[0];
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        if (file.size > MAX_FILE_SIZE) {
            fileSizeInfo.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Too large! ${sizeMB}MB (Max: 50MB)`;
            fileSizeInfo.className = 'text-danger small';
            this.value = '';
        } else {
            fileSizeInfo.innerHTML = `<i class="fas fa-check-circle"></i> File size: ${sizeMB}MB`;
            fileSizeInfo.className = 'text-success small';
        }
    }
});

// Thumbnail preview
document.getElementById('thumbnail_img').addEventListener('change', function(e) {
    const thumbPreview = document.getElementById('thumbPreview');
    thumbPreview.innerHTML = '';
    
    if (this.files.length > 0) {
        const file = this.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            thumbPreview.innerHTML = `<img src="${e.target.result}" width="100" class="img-thumbnail mt-2">`;
        }
        
        reader.readAsDataURL(file);
    }
});

// Show current PHP limits
document.addEventListener('DOMContentLoaded', function() {
    console.log('Maximum upload size:', '<?php echo ini_get("upload_max_filesize"); ?>');
    console.log('Maximum post size:', '<?php echo ini_get("post_max_size"); ?>');
});
</script>

<?php include 'footer.php'; ?>