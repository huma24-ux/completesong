<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $production = isset($_POST['interested_in_production']) ? 1 : 0;
    $updates = isset($_POST['updates_subscription']) ? 1 : 0;

    // insert query
    $sql = "INSERT INTO contact (name, email, subject, message, interested_in_production, updates_subscription)
            VALUES ('$name', '$email', '$subject', '$message', '$production', '$updates')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('✅ Message sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "<script>alert('❌ Error saving message: " . mysqli_error($conn) . "');</script>";
    }
}
?>
