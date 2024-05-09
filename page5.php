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
            $stmt_membership = $conn->prepare("INSERT INTO professional_society_membership(user_id, society_name, membership_status) VALUES (?, ?, ?)");

            // Prepare and bind SQL statement for research experience
            $stmt_training = $conn->prepare("INSERT INTO professionaltraining(user_id, TrainingType, Organization, Year) VALUES (?, ?, ?, ?)");

            // Prepare and bind SQL statement for industrial experience
            $stmt_award = $conn->prepare("INSERT INTO awardsandrecognitions (user_id, AwardName, AwardedBy, Year) VALUES (?, ?, ?, ?)");

            $stmt_sponser = $conn->prepare("INSERT INTO sponsoredprojects(user_id, SponsoringAgency, ProjectTitle, SanctionedAmount, Period, Role, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt_consultancy = $conn->prepare("INSERT INTO consultancyprojects(user_id, Organisation, ProjectTitle, GrantAmount, Period, Role, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");

            // Loop through each row of teaching experience data
            foreach ($_POST["society_name"] as $key => $value) {
                // Assign values from form data
                $society_name = $_POST["society_name"][$key];
                $membership_status = $_POST["membership_status"][$key];

                // Execute the SQL statement for teaching experience
                $stmt_membership->bind_param("iss", $user_id, $society_name, $membership_status);
                $stmt_membership->execute();
            }

            // Loop through each row of research experience data
            foreach ($_POST["TrainingType"] as $key => $value) {
                // Assign values from form data
                $TrainingType = $_POST["TrainingType"][$key];
                $Organization = $_POST["Organization"][$key];
                $Year = $_POST["Year"][$key];
                // Execute the SQL statement for research experience
                $stmt_training->bind_param("issi", $user_id,  $TrainingType, $Organization, $Year);
                $stmt_training->execute();
    
            }

            // Loop through each row of industrial experience data
            foreach ($_POST["AwardName"] as $key => $value) {
                // Assign values from form data
                $AwardName = $_POST["AwardName"][$key];
                $AwardedBy = $_POST["AwardedBy"][$key];
                $Year = $_POST["Year"][$key];

                // Execute the SQL statement for industrial experience
                $stmt_award->bind_param("issi", $user_id, $AwardName, $AwardedBy, $Year);
                $stmt_award->execute();
            }

            foreach ($_POST["SponsoringAgency"] as $key => $value) {
                // Assign values from form data
                $SponsoringAgency = $_POST["SponsoringAgency"][$key];
                $ProjectTitle = $_POST["ProjectTitle"][$key];
                $SanctionedAmount = $_POST["SanctionedAmount"][$key];
                $Period = $_POST["Period"][$key];
                $Role = $_POST["Role"][$key];
                $Status = $_POST["Status"][$key];
                // Execute the SQL statement for industrial experience
                $stmt_sponser->bind_param("issssss", $user_id, $SponsoringAgency, $ProjectTitle, $SanctionedAmount, $Period, $Role, $Status);
                $stmt_sponser->execute();
            }

            foreach ($_POST["Organisation"] as $key => $value) {
                // Assign values from form data
                $Organisation = $_POST["Organisation"][$key];
                $ProjectTitle = $_POST["ProjectTitle"][$key];
                $GrantAmount = $_POST["GrantAmount"][$key];
                $Period = $_POST["Period"][$key];
                $Role = $_POST["Role"][$key];
                $Status = $_POST["Status"][$key];
                // Execute the SQL statement for industrial experience
                $stmt_consultancy->bind_param("issssss", $user_id, $Organisation, $ProjectTitle, $GrantAmount, $Period, $Role, $Status);
                $stmt_consultancy->execute();
            }

            // Close prepared statements
            $stmt_membership->close();
            $stmt_training->close();
            $stmt_award->close();
            $stmt_sponser->close();
            $stmt_consultancy->close();



            // Redirect to the next page
            header("Location: page6.html");
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
