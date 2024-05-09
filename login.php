<?php
session_start(); // Start session (if not already started)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve login credentials from form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Establish connection
    $conn = new mysqli("localhost", "root", "", "user_data");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepared statement to fetch user with matching email and password
    $stmt = $conn->prepare("SELECT email, password FROM registration_data WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, login successful
        $_SESSION["loggedin"] = true;
        $_SESSION["email"] = $email;
        // Redirect to dashboard or another protected page
        header("Location: ./page1.html");
        exit;
    } else {
        // User not found or password incorrect
        echo "Invalid email or password";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
