<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';

?>

<?php
$message = "";

// CREATE / UPDATE MUSIC
if (isset($_POST['save_music'])) {
    $music_id     = $_POST['music_id'] ?? '';
    $title        = trim($_POST['title']);
    $artist_id    = $_POST['artist_id'];
    $album_id     = $_POST['album_id'];
    $genre_id     = $_POST['genre_id'];
    $language_id  = $_POST['language_id'];
    $year         = trim($_POST['year']);
    $description  = trim($_POST['description']);
    $is_new       = isset($_POST['is_new']) ? 1 : 0;
    $created_at   = date('Y-m-d H:i:s');

    // File upload
    $fileName = $_FILES['file_path']['name'] ?? '';
    $tmpName  = $_FILES['file_path']['tmp_name'] ?? '';

    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileTarget = $uploadDir . basename($fileName);

    if (!empty($fileName)) move_uploaded_file($tmpName, $fileTarget);

    $fileToSave = !empty($fileName) ? $fileName : ($_POST['old_file'] ?? '');

    if ($music_id == "") {
        // Insert new record
        $sql = "INSERT INTO music (title, artist_id, album_id, genre_id, language_id, year, file_path, description, is_new, created_at)
                VALUES ('$title', '$artist_id', '$album_id', '$genre_id', '$language_id', '$year', '$fileToSave', '$description', '$is_new', '$created_at')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'>Music added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        // Update existing record
        $sql = "UPDATE music SET 
                    title='$title',
                    artist_id='$artist_id',
                    album_id='$album_id',
                    genre_id='$genre_id',
                    language_id='$language_id',
                    year='$year',
                    file_path='$fileToSave',
                    description='$description',
                    is_new='$is_new'
                WHERE music_id=$music_id";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-warning'>Music updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// DELETE MUSIC
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM music WHERE music_id=$id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Music deleted successfully!'); window.location.href='music_view.php';</script>";
        exit;
    }
}

// FETCH ALL MUSIC
$result = mysqli_query($conn, "SELECT * FROM music ORDER BY music_id ASC");
?>

<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h3 class="text-center mb-3">Music Management</h3>

    <?= $message; ?>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#musicModal" onclick="openAddModal()">+ Add Music</button>
    </div>

    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Genre</th>
                <th>Language</th>
                <th>Year</th>
                <th>File</th>
                <th>Is New</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['music_id']; ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= $row['artist_id']; ?></td>
                <td><?= $row['album_id']; ?></td>
                <td><?= $row['genre_id']; ?></td>
                <td><?= $row['language_id']; ?></td>
                <td><?= htmlspecialchars($row['year']); ?></td>
                <td>
                    <?php if (!empty($row['file_path'])) { ?>
                        <a href="uploads/<?= htmlspecialchars($row['file_path']); ?>" target="_blank">Listen</a>
                    <?php } else { echo "No File"; } ?>
                </td>
                <td><?= $row['is_new'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <button class="btn btn-sm btn-warning"
                        onclick="openEditModal('<?= $row['music_id']; ?>','<?= htmlspecialchars($row['title']); ?>','<?= $row['artist_id']; ?>','<?= $row['album_id']; ?>','<?= $row['genre_id']; ?>','<?= $row['language_id']; ?>','<?= $row['year']; ?>','<?= htmlspecialchars($row['description']); ?>','<?= $row['file_path']; ?>','<?= $row['is_new']; ?>')">
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['music_id']; ?>)">
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="musicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Music</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="music_id" id="music_id">
            <input type="hidden" name="old_file" id="old_file">

            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Artist ID</label>
                    <input type="number" name="artist_id" id="artist_id" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Album ID</label>
                    <input type="number" name="album_id" id="album_id" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Genre ID</label>
                    <input type="number" name="genre_id" id="genre_id" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Language ID</label>
                    <input type="number" name="language_id" id="language_id" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" id="year" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Music File</label>
                    <input type="file" name="file_path" id="file_path" class="form-control">
                </div>
                <div class="col-md-6 mt-3">
                    <input type="checkbox" name="is_new" id="is_new">
                    <label for="is_new">Mark as New</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_music" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Music";
    document.querySelectorAll("input, textarea").forEach(el => el.value = "");
    document.getElementById("is_new").checked = false;
}

function openEditModal(id, title, artist, album, genre, lang, year, desc, file, isNew) {
    document.getElementById("modalTitle").innerText = "Edit Music";
    document.getElementById("music_id").value = id;
    document.getElementById("title").value = title;
    document.getElementById("artist_id").value = artist;
    document.getElementById("album_id").value = album;
    document.getElementById("genre_id").value = genre;
    document.getElementById("language_id").value = lang;
    document.getElementById("year").value = year;
    document.getElementById("description").value = desc;
    document.getElementById("old_file").value = file;
    document.getElementById("is_new").checked = isNew == 1;
    var modal = new bootstrap.Modal(document.getElementById('musicModal'));
    modal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this music?")) {
        window.location.href = 'music_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
