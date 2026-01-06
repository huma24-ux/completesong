<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';

?>

<?php
// Initialize message
$message = "";

/// CREATE / UPDATE Operation
if (isset($_POST['save_genre'])) {
    $genre_name = trim($_POST['genre_name']);
    $genre_id = $_POST['genre_id'] ?? '';

    // Validate: only letters & spaces allowed
    if (!preg_match("/^[a-zA-Z\s]+$/", $genre_name)) {
        $message = "<div class='alert alert-danger alert-dismissible fade show'>
                        Only letters and spaces allowed!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        if ($genre_id == "") {
            // Insert
            $sql = "INSERT INTO genre (genre_name) VALUES ('$genre_name')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success alert-dismissible fade show'>
                                Genre added successfully!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            }
        } else {
            // Update
            $sql = "UPDATE genre SET genre_name='$genre_name' WHERE genre_id=$genre_id";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-warning alert-dismissible fade show'>
                                Genre updated successfully!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            }
        }
    }
}
// DELETE Operation
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM genre WHERE genre_id = $id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Genre deleted successfully!'); window.location.href='genre_view.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error deleting: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch All Genres
$result = mysqli_query($conn, "SELECT * FROM genre ORDER BY genre_id ASC");
?>


<div class="container bg-white p-4 rounded shadow-sm mx-auto">
    <h3 class="mb-3 text-center">Genre Management</h3>

    <?= $message; ?>

    <!-- Add Genre Button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#genreModal"
                onclick="openAddModal()">+ Add Genre</button>
    </div>

    <!-- Table -->
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Genre Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['genre_id']; ?></td>
                <td><?= htmlspecialchars($row['genre_name']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning me-1"
                            onclick="openEditModal(<?= $row['genre_id']; ?>, '<?= htmlspecialchars($row['genre_name']); ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="confirmDelete(<?= $row['genre_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="genreModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" onsubmit="return validateForm()">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Genre</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="genre_id" id="genre_id">
            <div class="mb-3">
                <label for="genre_name" class="form-label">Genre Name</label>
                <input type="text" class="form-control" id="genre_name" name="genre_name" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_genre" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Validate input: only letters and spaces
function validateForm() {
    const genre = document.getElementById("genre_name").value;
    const pattern = /^[A-Za-z\s]+$/;
    if (!pattern.test(genre)) {
        alert("Only letters and spaces are allowed!");
        return false;
    }
    return true;
}

// Open Add Modal
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Genre";
    document.getElementById("genre_id").value = "";
    document.getElementById("genre_name").value = "";
}

// Open Edit Modal with pre-filled values
function openEditModal(id, name) {
    document.getElementById("modalTitle").innerText = "Edit Genre";
    document.getElementById("genre_id").value = id;
    document.getElementById("genre_name").value = name;
    var modal = new bootstrap.Modal(document.getElementById('genreModal'));
    modal.show();
}

// Confirm Delete
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this genre?")) {
        window.location.href = 'genre_view.php?delete=' + id;
    }
}
</script>

<?php include 'footer.php'; ?>
