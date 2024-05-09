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

// Function to handle file upload and return file path
function uploadFile($file) {
    $targetDir = "uploads/"; // Directory where files will be uploaded
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    // Allow certain file formats
    $allowTypes = array('pdf');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $targetFilePath;
        } else {
            return null;
        }
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

            // Process file uploads and insert data into the UploadedDocuments table
            foreach ($_FILES as $fileKey => $file) {
                $filePath = uploadFile($file);
                if ($filePath === null) {
                    echo "Error: Failed to upload file for $fileKey";
                    exit();
                }
                // Insert the file path into the UploadedDocuments table
                $stmt = $conn->prepare("INSERT INTO UploadedDocuments (user_id, file_key, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $fileKey, $filePath);
                if (!$stmt->execute()) {
                    echo "Error inserting data into UploadedDocuments table: " . $stmt->error;
                    exit();
                }
                $stmt->close();
            }

            // Insert data into the Referees table
            foreach ($_POST["name"] as $key => $value) {
                // Fetch values from form arrays
                $name = sanitize_input($_POST["name"][$key]);
                $position = sanitize_input($_POST["position"][$key]);
                $association = sanitize_input($_POST["association"][$key]);
                $institution = sanitize_input($_POST["institution"][$key]);
                $email = sanitize_input($_POST["email"][$key]);
                $contact_no = sanitize_input($_POST["contact_no"][$key]);

                // Insert data into the Referees table
                $stmt = $conn->prepare("INSERT INTO referees (user_id, name, position, association, institution, email, contact_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $user_id, $name, $position, $association, $institution, $email, $contact_no);
                if (!$stmt->execute()) {
                    echo "Error inserting data into Referees table: " . $stmt->error;
                    exit();
                }
                $stmt->close();
            }

            // Redirect to the next page
            header("Location: page9.html");
            exit();
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
