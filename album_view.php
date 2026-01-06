<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';
?>

<?php
// Initialize message
$message = "";

// CREATE / UPDATE Album
if (isset($_POST['save_album'])) {
    $album_id = $_POST['album_id'] ?? '';
    $album_name = trim($_POST['album_name']);
    $release_year = trim($_POST['release_year']);
    $description = trim($_POST['description']);

    // Validate album name
    if (!preg_match("/^[A-Za-z0-9\s]+$/", $album_name)) {
        $message = "<div class='alert alert-danger'>Only letters, numbers, and spaces allowed!</div>";
    } else {
        // Handle Image Upload
        $fileName = $_FILES['cover_image']['name'];
        $tmpName  = $_FILES['cover_image']['tmp_name'];

        // Create images folder if not exists
        $targetDir = __DIR__ . "/images/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . basename($fileName);

        if (!empty($fileName) && move_uploaded_file($tmpName, $targetFile)) {
            // Image uploaded successfully
        } elseif ($album_id == "") {
            $message = "<div class='alert alert-danger'>Image upload failed or not selected!</div>";
        }

        // Save filename for DB
        $imgNameToSave = !empty($fileName) ? $fileName : $_POST['old_image'] ?? '';

        // Insert or Update
        if ($album_id == "") {
            $sql = "INSERT INTO album (album_name, release_year, description, cover_image)
                    VALUES ('$album_name', '$release_year', '$description', '$imgNameToSave')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Album added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $sql = "UPDATE album 
                    SET album_name='$album_name', release_year='$release_year', description='$description', cover_image='$imgNameToSave' 
                    WHERE album_id=$album_id";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-warning'>Album updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// DELETE Album
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM album WHERE album_id=$id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Album deleted successfully!'); window.location.href='album_view.php';</script>";
        exit;
    } else {
        echo "<script>alert('Delete failed: " . mysqli_error($conn) . "');</script>";
    }
}

// FETCH ALL ALBUMS (FIXED)
$query = "SELECT * FROM album ORDER BY album_id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h3 class="text-center mb-3">Album Management</h3>

    <?= $message; ?>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#albumModal" onclick="openAddModal()">+ Add Album</button>
    </div>

    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Album Name</th>
                <th>Release Year</th>
                <th>Description</th>
                <th>Cover</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['album_id']; ?></td>
                <td><?= htmlspecialchars($row['album_name']); ?></td>
                <td><?= htmlspecialchars($row['release_year']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td>
                    <?php if (!empty($row['cover_image'])) { ?>
                        <img src="images/<?= htmlspecialchars($row['cover_image']); ?>" width="70" height="70" style="object-fit:cover;">
                    <?php } else { echo "No Image"; } ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" 
                        onclick="openEditModal('<?= $row['album_id']; ?>', '<?= htmlspecialchars($row['album_name']); ?>', '<?= $row['release_year']; ?>', '<?= htmlspecialchars($row['description']); ?>', '<?= $row['cover_image']; ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['album_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="albumModal" tabindex="-1" aria-labelledby="albumModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data" onsubmit="return validateAlbumForm()">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Album</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="album_id" id="album_id">
            <input type="hidden" name="old_image" id="old_image">

            <div class="mb-3">
                <label class="form-label">Album Name</label>
                <input type="text" name="album_name" id="album_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Release Year</label>
                <input type="number" name="release_year" id="release_year" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Cover Image</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control">
                <img id="previewImg" src="" width="100" height="100" class="mt-2 d-none">
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_album" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function validateAlbumForm() {
    const name = document.getElementById("album_name").value.trim();
    if (!/^[A-Za-z0-9\s]+$/.test(name)) {
        alert("Only letters, numbers, and spaces allowed in album name!");
        return false;
    }
    return true;
}

// Add Album
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Album";
    document.getElementById("album_id").value = "";
    document.getElementById("album_name").value = "";
    document.getElementById("release_year").value = "";
    document.getElementById("description").value = "";
    document.getElementById("cover_image").value = "";
    document.getElementById("previewImg").classList.add("d-none");
}

// Edit Album
function openEditModal(id, name, year, desc, img) {
    document.getElementById("modalTitle").innerText = "Edit Album";
    document.getElementById("album_id").value = id;
    document.getElementById("album_name").value = name;
    document.getElementById("release_year").value = year;
    document.getElementById("description").value = desc;
    document.getElementById("old_image").value = img;

    if (img) {
        document.getElementById("previewImg").src = "images/" + img;
        document.getElementById("previewImg").classList.remove("d-none");
    } else {
        document.getElementById("previewImg").classList.add("d-none");
    }

    var modal = new bootstrap.Modal(document.getElementById('albumModal'));
    modal.show();
}

// Confirm Delete
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this album?")) {
        window.location.href = 'album_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
