<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';

?>

<?php
$message = "";

// ADD or UPDATE Rating
if (isset($_POST['save_rating'])) {
    $rating_id    = $_POST['rating_id'] ?? '';
    $user_id      = $_POST['user_id'];
    $content_type = trim($_POST['content_type']);
    $content_id   = $_POST['content_id'];
    $rating_value = $_POST['rating_value'];
    $created_at   = date('Y-m-d H:i:s');

    // Validation
    if ($rating_value < 1 || $rating_value > 5) {
        $message = "<div class='alert alert-danger'>Rating value must be between 1 and 5!</div>";
    } elseif (!in_array($content_type, ['video', 'music', 'album'])) {
        $message = "<div class='alert alert-danger'>Invalid content type!</div>";
    } else {
        if ($rating_id == "") {
            // Insert
            $sql = "INSERT INTO rating (user_id, content_type, content_id, rating_value, created_at)
                    VALUES ('$user_id', '$content_type', '$content_id', '$rating_value', '$created_at')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Rating added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            // Update
            $sql = "UPDATE rating 
                    SET user_id='$user_id', content_type='$content_type', content_id='$content_id', rating_value='$rating_value'
                    WHERE rating_id=$rating_id";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-warning'>Rating updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// DELETE Rating
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM rating WHERE rating_id=$id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Rating deleted successfully!'); window.location.href='ratings_view.php';</script>";
        exit;
    }
}

// FETCH ALL RATINGS
$result = mysqli_query($conn, "SELECT * FROM rating ORDER BY rating_id DESC");
?>

<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h3 class="text-center mb-3">Ratings Management</h3>
    <?= $message; ?>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ratingModal" onclick="openAddModal()">+ Add Rating</button>
    </div>

    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Content Type</th>
                <th>Content ID</th>
                <th>Rating Value</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['rating_id']; ?></td>
                <td><?= $row['user_id']; ?></td>
                <td><?= ucfirst($row['content_type']); ?></td>
                <td><?= $row['content_id']; ?></td>
                <td><?= str_repeat("⭐", $row['rating_value']); ?> (<?= $row['rating_value']; ?>/5)</td>
                <td><?= $row['created_at']; ?></td>
                <td>
                    <button class="btn btn-sm btn-warning"
                        onclick="openEditModal('<?= $row['rating_id']; ?>','<?= $row['user_id']; ?>','<?= $row['content_type']; ?>','<?= $row['content_id']; ?>','<?= $row['rating_value']; ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['rating_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Rating</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="rating_id" id="rating_id">

            <div class="mb-3">
                <label class="form-label">User ID</label>
                <input type="number" name="user_id" id="user_id" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Content Type</label>
                <select name="content_type" id="content_type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="video">Video</option>
                    <option value="music">Music</option>
                    <option value="album">Album</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Content ID</label>
                <input type="number" name="content_id" id="content_id" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Rating Value (1–5)</label>
                <input type="number" name="rating_value" id="rating_value" min="1" max="5" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_rating" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Rating";
    document.getElementById("rating_id").value = "";
    document.getElementById("user_id").value = "";
    document.getElementById("content_type").value = "";
    document.getElementById("content_id").value = "";
    document.getElementById("rating_value").value = "";
}

function openEditModal(id, user, type, cid, value) {
    document.getElementById("modalTitle").innerText = "Edit Rating";
    document.getElementById("rating_id").value = id;
    document.getElementById("user_id").value = user;
    document.getElementById("content_type").value = type;
    document.getElementById("content_id").value = cid;
    document.getElementById("rating_value").value = value;
    var modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this rating?")) {
        window.location.href = 'ratings_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
