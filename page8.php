<?php
// Establish a connection to the database
$conn = new mysqli("localhost", "root", "", "user_data");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data); // Sanitize data to prevent SQL injection
}

// Function to fetch user id based on email
function get_user_id($conn, $email) {
    $email = sanitize_input($email);
    $sql = "SELECT id FROM registration_data WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["id"];
    } else {
        return null;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email from the form
    $email = isset($_POST["email"]) ? $_POST["email"] : "";

    if (!empty($email)) {
        // Get the user ID based on the email provided in the form
        $user_id = get_user_id($conn, $email);

        if ($user_id) {
            // Debugging
            echo "User ID: $user_id";

            // Get the Google Drive link from the form
            $drive_link = isset($_POST["drive_link"]) ? sanitize_input($_POST["drive_link"]) : "";

            if (!empty($drive_link)) {
                // Insert the drive link into the uploadeddocuments table
                $stmt = $conn->prepare("INSERT INTO uploadeddocuments (registration_id, drive_link) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $drive_link);
                if ($stmt->execute()) {
                    // Redirect to the next page
                    header("Location: page9.html");
                    exit();
                } else {
                    echo "Error inserting data into uploadeddocuments table: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error: Drive link is required.";
            }
        } else {
            // Handle case where user does not exist
            echo "Error: User with email $email not found.";
        }
    } else {
        // Handle case where required fields are not set
        echo "Error: Email field is not set.";
    }
}

// Close database connection
$conn->close();
?>
