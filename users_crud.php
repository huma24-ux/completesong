<?php
include 'dbconnect.php';
include 'auth_check.php';


// Example: Fetch users
$result = $conn->query("SELECT * FROM users");
?>
<h2>User Management (Admin Only)</h2>
<table border="1">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= $row['user_id'] ?></td>
  <td><?= htmlspecialchars($row['name']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= htmlspecialchars($row['role']) ?></td>
</tr>
<?php endwhile; ?>
</table>
<a href="dashboard.php">Back to Dashboard</a>
