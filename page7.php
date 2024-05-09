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

            $check_stmt = $conn->prepare("SELECT user_id FROM professional_details WHERE user_id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                // Update existing record
                $stmt_summary_update = $conn->prepare("UPDATE professional_details SET research_contribution_future_plans = ?, teaching_contribution_future_plans = ?, other_relevant_information = ?, professional_service_editorship_reviewership = ?, journal_publications = ?, conference_publications = ? WHERE user_id = ?");
                $stmt_summary_update->bind_param("ssssssi", $_POST["research_contribution_future_plans"], $_POST["teaching_contribution_future_plans"], $_POST["other_relevant_information"], $_POST["professional_service_editorship_reviewership"], $_POST["journal_publications"], $_POST["conference_publications"], $user_id);
                $stmt_summary_update->execute();
                if ($stmt_summary_update->errno) {
                    // Handle error
                    echo "Update Error: " . $stmt_summary_update->error;
                } else {
                    $stmt_summary_update->close();
                }
            } else {
                // Insert new record
                $stmt_summary_insert = $conn->prepare("INSERT INTO professional_details (user_id, research_contribution_future_plans, teaching_contribution_future_plans, other_relevant_information, professional_service_editorship_reviewership, journal_publications, conference_publications) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_summary_insert->bind_param("issssss", $user_id, $_POST["research_contribution_future_plans"], $_POST["teaching_contribution_future_plans"], $_POST["other_relevant_information"], $_POST["professional_service_editorship_reviewership"], $_POST["journal_publications"], $_POST["conference_publications"]);
                $stmt_summary_insert->execute();
                if ($stmt_summary_insert->errno) {
                    // Handle error
                    echo "Insert Error: " . $stmt_summary_insert->error;
                } else {
                    $stmt_summary_insert->close();
                }
            }



            // Redirect to the next page
            header("Location: page8.html");
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
