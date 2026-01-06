<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';

?>

<?php
$message = "";

// ADD or UPDATE Review
if (isset($_POST['save_review'])) {
    $review_id    = $_POST['review_id'] ?? '';
    $user_id      = $_POST['user_id'];
    $content_type = trim($_POST['content_type']);
    $content_id   = $_POST['content_id'];
    $review_text  = mysqli_real_escape_string($conn, $_POST['review_text']);
    $now          = date('Y-m-d H:i:s');

    if (empty($review_text)) {
        $message = "<div class='alert alert-danger'>Review text cannot be empty!</div>";
    } elseif (!in_array($content_type, ['video', 'music', 'album'])) {
        $message = "<div class='alert alert-danger'>Invalid content type!</div>";
    } else {
        if ($review_id == "") {
            // Insert new review
            $sql = "INSERT INTO review (user_id, content_type, content_id, review_text, created_at, updated_at)
                    VALUES ('$user_id', '$content_type', '$content_id', '$review_text', '$now', '$now')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Review added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            // Update existing review
            $sql = "UPDATE review 
                    SET user_id='$user_id', content_type='$content_type', content_id='$content_id',
                        review_text='$review_text', updated_at='$now'
                    WHERE review_id=$review_id";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-warning'>Review updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
            }
        }
    }
}

// DELETE Review
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM review WHERE review_id=$id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Review deleted successfully!'); window.location.href='review_view.php';</script>";
        exit;
    }
}

// FETCH ALL REVIEWS
$result = mysqli_query($conn, "SELECT * FROM review ORDER BY review_id DESC");
?>

<div class="container bg-white p-4 rounded shadow-sm mt-4">
    <h3 class="text-center mb-3">User Reviews Management</h3>
    <?= $message; ?>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal" onclick="openAddModal()">+ Add Review</button>
    </div>

    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Content Type</th>
                <th>Content ID</th>
                <th>Review Text</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['review_id']; ?></td>
                <td><?= $row['user_id']; ?></td>
                <td><?= ucfirst($row['content_type']); ?></td>
                <td><?= $row['content_id']; ?></td>
                <td class="text-start"><?= htmlspecialchars($row['review_text']); ?></td>
                <td><?= $row['created_at']; ?></td>
                <td><?= $row['updated_at']; ?></td>
                <td>
                    <button class="btn btn-sm btn-warning"
                        onclick="openEditModal('<?= $row['review_id']; ?>','<?= $row['user_id']; ?>','<?= $row['content_type']; ?>','<?= $row['content_id']; ?>','<?= htmlspecialchars($row['review_text']); ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['review_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="review_id" id="review_id">

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
                <label class="form-label">Review Text</label>
                <textarea name="review_text" id="review_text" class="form-control" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_review" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Review";
    document.getElementById("review_id").value = "";
    document.getElementById("user_id").value = "";
    document.getElementById("content_type").value = "";
    document.getElementById("content_id").value = "";
    document.getElementById("review_text").value = "";
}

function openEditModal(id, user, type, cid, text) {
    document.getElementById("modalTitle").innerText = "Edit Review";
    document.getElementById("review_id").value = id;
    document.getElementById("user_id").value = user;
    document.getElementById("content_type").value = type;
    document.getElementById("content_id").value = cid;
    document.getElementById("review_text").value = text;
    var modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this review?")) {
        window.location.href = 'review_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
