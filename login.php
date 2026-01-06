<?php
include 'db.php';
session_start();
include 'header.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ======== ADMIN LOGIN CHECK ========
    $admin_sql = "SELECT * FROM admin WHERE email = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin_result = $stmt->get_result();

    if ($admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();

        // Verify admin password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = $admin['name'];

            echo "<script>alert('Welcome Admin!'); window.location='dashboard.php';</script>";
            exit();
        }
    }

    // ======== USER LOGIN CHECK ========
    $user_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();

        // Verify user password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = $user['name'];

            echo "<script>alert('Welcome User!'); window.location='../frontend/index.php';</script>";
            exit();
        }
    }

    // ======== INVALID CREDENTIALS ========
    echo "<script>alert('Invalid Email or Password');</script>";
}
?>

<div class="bg-light rounded my-5 p-4 col-md-6 offset-md-3">
  <h6 class="text-center text-primary mb-4">Login</h6>
  <form method="POST">
    <div class="form-floating mb-3">
      <input type="email" name="email" class="form-control" id="loginEmail" placeholder="Email" required>
      <label for="loginEmail">Email</label>
    </div>

    <div class="form-floating mb-3">
      <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Password" required>
      <label for="loginPassword">Password</label>
    </div>

    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    <p class="text-center mt-3 mb-0">
      No account? <a href="register.php">Register here</a>
    </p>
  </form>
</div>


  <?php include 'footer.php'; ?>

