<?php
// Establish a connection to the database
$conn = new mysqli("localhost", "root", "", "user_data");

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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

        if ($user_id !== null) {


            // Prepare and bind SQL statement for bestpublications
            $stmt_phd = $conn->prepare("INSERT INTO phd_thesis_supervision(user_id, student_name,thesis_title, role, ongoing_completed, ongoing_since_year_of_completion) VALUES (?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for research experience
            $stmt_mtech = $conn->prepare("INSERT INTO mtech_me_masters_supervision(user_id, student_name,thesis_title, role, ongoing_completed, ongoing_since_year_of_completion) VALUES (?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for industrial experience
            $stmt_btech = $conn->prepare("INSERT INTO btech_be_bachelors_supervision(user_id, student_name,thesis_title, role, ongoing_completed, ongoing_since_year_of_completion) VALUES (?, ?, ?, ?, ?, ?)");


            // Loop through each row of teaching experience data
            foreach ($_POST["student_namep"] as $key => $value) {
                // Assign values from form data
                $student_name = $_POST["student_namep"][$key];
                $thesis_title = $_POST["thesis_titlep"][$key];
                $role = $_POST["rolep"][$key];
                $ongoing_completed = $_POST["ongoing_completedp"][$key];
                $ongoing_since_year_of_completion = $_POST["ongoing_since_year_of_completionp"][$key];
                // Execute the SQL statement for teaching experience
                $stmt_phd->bind_param("issssi", $user_id, $student_name, $thesis_title, $role, $ongoing_completed, $ongoing_since_year_of_completion);
                $stmt_phd->execute();
            }

            // Loop through each row of research experience data
            foreach ($_POST["student_namem"] as $key => $value) {
                // Assign values from form data
                $student_name = $_POST["student_namem"][$key];
                $thesis_title = $_POST["thesis_titlem"][$key];
                $role = $_POST["rolem"][$key];
                $ongoing_completed = $_POST["ongoing_completedm"][$key];
                $ongoing_since_year_of_completion = $_POST["ongoing_since_year_of_completionm"][$key];
                // Execute the SQL statement for teaching experience
                $stmt_mtech->bind_param("issssi", $user_id, $student_name, $thesis_title, $role, $ongoing_completed, $ongoing_since_year_of_completion);
                $stmt_mtech->execute();
            }

            // Loop through each row of industrial experience data
            foreach ($_POST["student_nameb"] as $key => $value) {
                // Assign values from form data
                $student_name = $_POST["student_nameb"][$key];
                $thesis_title = $_POST["thesis_titleb"][$key];
                $role = $_POST["roleb"][$key];
                $ongoing_completed = $_POST["ongoing_completedb"][$key];
                $ongoing_since_year_of_completion = $_POST["ongoing_since_year_of_completionb"][$key];
                // Execute the SQL statement for teaching experience
                $stmt_btech->bind_param("issssi", $user_id, $student_name, $thesis_title, $role, $ongoing_completed, $ongoing_since_year_of_completion);
                $stmt_btech->execute();
            }


            // Close prepared statements
            $stmt_phd->close();
            $stmt_mtech->close();
            $stmt_btech->close();


            // Redirect to the next page
            header("Location: page7.html");
            exit();
        } else {
            // Handle case where user does not exist
            echo "Error: User with email $email not found.";
        }


        // Close database connection
        
    } else {
        // Handle case where required fields are not set
        echo "Error: Email field is not set.";
    }
}
// Close the database connection outside the if condition block
$conn->close();

?>
