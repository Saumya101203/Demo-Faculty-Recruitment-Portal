<?php
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$category = $_POST['category'];
$password = $_POST['password'];

// Establishing connection
$conn = new mysqli("localhost", "root", "", "user_data");

// Checking connection
if ($conn->connect_error) {
    die('Connection Failed : ' . $conn->connect_error);
}

// Prepared statement for insertion
$stmt = $conn->prepare("INSERT INTO registration_data (firstname, lastname, email, category, password) 
                        VALUES (?, ?, ?, ?, ?)");

// Binding parameters and executing statement
$stmt->bind_param("sssss", $firstname, $lastname, $email, $category, $password);

if ($stmt->execute()) {
    echo "Registration successful";
} else {
    echo "Error: " . $stmt->error;
}

// Closing statement and connection
$stmt->close();
$conn->close();
?>

