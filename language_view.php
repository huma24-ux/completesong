<?php
include 'db.php';
include 'auth_check.php';
include 'header.php';

?>


<?php
// Initialize message
$message = "";

// CREATE / UPDATE Operation
if (isset($_POST['save_language'])) {
    $lang_name = trim($_POST['language_name']);
    $lang_id = $_POST['language_id'] ?? '';

    // Validate: only letters & spaces allowed
    if (!preg_match("/^[a-zA-Z\s]+$/", $lang_name)) {
        $message = "<div class='alert alert-danger alert-dismissible fade show'>
                        Only letters and spaces allowed!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        if ($lang_id == "") {
            // Insert
            $sql = "INSERT INTO language (language_name) VALUES ('$lang_name')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success alert-dismissible fade show'>
                                Language added successfully!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            }
        } else {
            // Update
            $sql = "UPDATE language SET language_name='$lang_name' WHERE language_id=$lang_id";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-warning alert-dismissible fade show'>
                                Language updated successfully!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            }
        }
    }
}

// DELETE Operation
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM language WHERE language_id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Language deleted successfully!');
                window.location.href='language_view.php';
              </script>";
        exit;
    }
}

// Fetch All Languages
$result = mysqli_query($conn, "SELECT * FROM language ORDER BY language_id ASC");
?>


<div class="container bg-white p-4 rounded shadow-sm mx-auto ">
    <h3 class="mb-3 text-center">Language Management</h3>

    <?= $message; ?>

    <!-- Add Language Button -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#languageModal" 
                onclick="openAddModal()">+ Add Language</button>
    </div>

    <!-- Table -->
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Language Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['language_id']; ?></td>
                <td><?= htmlspecialchars($row['language_name']); ?></td>
                <td>
                    <button class="btn btn-sm btn-warning me-1" 
                            onclick="openEditModal(<?= $row['language_id']; ?>, '<?= htmlspecialchars($row['language_name']); ?>')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="confirmDelete(<?= $row['language_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="languageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" onsubmit="return validateForm()">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Language</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="language_id" id="language_id">
            <div class="mb-3">
                <label for="language_name" class="form-label">Language Name</label>
                <input type="text" class="form-control" id="language_name" name="language_name" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="save_language" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Validate input: only letters and spaces
function validateForm() {
    const lang = document.getElementById("language_name").value;
    const pattern = /^[A-Za-z\s]+$/;
    if (!pattern.test(lang)) {
        alert("Only letters and spaces are allowed!");
        return false;
    }
    return true;
}

// Open Add Modal
function openAddModal() {
    document.getElementById("modalTitle").innerText = "Add Language";
    document.getElementById("language_id").value = "";
    document.getElementById("language_name").value = "";
}

// Open Edit Modal with pre-filled values
function openEditModal(id, name) {
    document.getElementById("modalTitle").innerText = "Edit Language";
    document.getElementById("language_id").value = id;
    document.getElementById("language_name").value = name;
    var modal = new bootstrap.Modal(document.getElementById('languageModal'));
    modal.show();
}

// Confirm Delete
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this language?")) {
        window.location.href = 'language_view.php?delete=' + id;
    }
}
</script>




<?php include 'footer.php'; ?>      