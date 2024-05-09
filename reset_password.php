<?php
// Retrieve email and new password from form
$email = $_POST['email'];
$newPassword = $_POST['password'];

// Establish connection to the database
$conn = new mysqli("localhost", "root", "", "user_data");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute SQL statement to update password
$stmt = $conn->prepare("UPDATE registration_data SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $newPassword, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Password reset successful";
} else {
    echo "Failed to reset password";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
