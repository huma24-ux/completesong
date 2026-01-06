<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';


// Initialize message
$message = "";

// CREATE / UPDATE Operation
if (isset($_POST['save_artist'])) {
    $artist_id   = $_POST['artist_id'] ?? '';
    $artist_name = trim($_POST['artist_name']);
    $country     = trim($_POST['country']);
    $description = trim($_POST['description']);

    // Handle Image
    $fileName = $_FILES['artist_image']['name'] ?? '';
    $tmpName  = $_FILES['artist_image']['tmp_name'] ?? '';
    $fileType = $_FILES['artist_image']['type'] ?? '';
    $fileSize = $_FILES['artist_image']['size'] ?? 0;

    $allowed = ['image/jpg', 'image/png', 'image/jpeg'];
    $imageFolder = __DIR__ . "/images/"; // full absolute path

    // Create folder if missing
    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0777, true);
    }

    $newFileName = "";
    if (!empty($fileName)) {
        $newFileName = time() . "_" . basename($fileName);
        $targetFile = $imageFolder . $newFileName;

        if (!in_array(strtolower($fileType), $allowed)) {
            $message = "<div class='alert alert-danger'>Invalid file type!</div>";
        } elseif ($fileSize > 1000000) {
            $message = "<div class='alert alert-danger'>File too large! (Max 1MB)</div>";
        } elseif (!move_uploaded_file($tmpName, $targetFile)) {
            $message = "<div class='alert alert-danger'>Image upload failed!</div>";
        }
    }

    if (empty($message)) {
        if ($artist_id == "") {
            // INSERT
            $sql = "INSERT INTO artist (artist_name, artist_image, country, description)
                    VALUES ('$artist_name', '$newFileName', '$country', '$description')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Artist added successfully!</div>";
            }
        } else {
            // UPDATE
            $update = "UPDATE artist SET artist_name='$artist_name', country='$country', description='$description'";
            if (!empty($newFileName)) {
                $update .= ", artist_image='$newFileName'";
            }
            $update .= " WHERE artist_id=$artist_id";

            if (mysqli_query($conn, $update)) {
                $message = "<div class='alert alert-warning'>Artist updated successfully!</div>";
            }
        }
    }
}

// DELETE Operation
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM artist WHERE artist_id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Artist deleted successfully!');
                window.location.href='artist_view.php';
              </script>";
        exit;
    }
}



// Fetch All Artists
$result = mysqli_query($conn, "SELECT * FROM artist ORDER BY artist_id ASC");
?>

<div class="container bg-white p-4 rounded shadow-sm mx-auto">
    <h3 class="mb-3 text-center">Artist Management</h3>
    <?= $message; ?>

    <!-- Add Artist Button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#artistModal"
                onclick="openAddModal()">+ Add Artist</button>
    </div>

    <!-- Artist Table -->
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Artist Name</th>
                <th>Image</th>
                <th>Country</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['artist_id']; ?></td>
                <td><?= htmlspecialchars($row['artist_name']); ?></td>
                <td>
                    <?php if (!empty($row['artist_image'])) { ?>
                        <img src="images/<?= htmlspecialchars($row['artist_image']); ?>" width="80" height="80" class="rounded-circle">
                    <?php } else { echo "No Image"; } ?>
                </td>
                <td><?= htmlspecialchars($row['country']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning me-1"
                        onclick="openEditModal(<?= $row['artist_id']; ?>, '<?= htmlspecialchars($row['artist_name']); ?>', '<?= htmlspecialchars($row['country']); ?>', '<?= htmlspecialchars($row['description']); ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger"
                        onclick="confirmDelete(<?= $row['artist_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="artistModal" tabindex="-1" aria-labelledby="artistModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Artist</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="artist_id" id="artist_id">
            <div class="mb-3">
                <label for="artist_name" class="form-label">Artist Name</label>
                <input type="text" class="form-control" id="artist_name" name="artist_name" required>
            </div>
            <div class="mb-3">
                <label for="artist_image" class="form-label">Upload Image</label>
                <input type="file" class="form-control" id="artist_image" name="artist_image">
            </div>
            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_artist" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Artist";
    document.getElementById("artist_id").value = "";
    document.getElementById("artist_name").value = "";
    document.getElementById("country").value = "";
    document.getElementById("description").value = "";
}

function openEditModal(id, name, country, desc) {
    document.getElementById("modalTitle").innerText = "Edit Artist";
    document.getElementById("artist_id").value = id;
    document.getElementById("artist_name").value = name;
    document.getElementById("country").value = country;
    document.getElementById("description").value = desc;
    var modal = new bootstrap.Modal(document.getElementById('artistModal'));
    modal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this artist?")) {
        window.location.href = 'artist_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
