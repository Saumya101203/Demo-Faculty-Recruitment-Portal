<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['email'])) {
    // Fetch the email from the session
    $userEmail = $_SESSION['email'];
} else {
    // If user is not logged in, set the email to "Guest"
    $userEmail = "Guest";
}

// Return the email as JSON response
echo json_encode(array('email' => $userEmail));
?>
