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

// Function to insert or update data in a table
function insert_or_update_data($conn, $table, $data, $user_id) {
    // Check if data exists for the user
    $check_sql = "SELECT * FROM $table WHERE registration_id = '$user_id'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        // Data exists for the user, perform an UPDATE operation
        $set_clause = "";
        foreach ($data as $key => $value) {
            $set_clause .= "$key = '$value', ";
        }
        $set_clause = rtrim($set_clause, ", ");

        $sql = "UPDATE $table SET $set_clause WHERE registration_id = '$user_id'";

        if ($conn->query($sql) === TRUE) {
            return true; // Return true if record is updated successfully
        } else {
            return false; // Return false if there's an error updating record
        }
    } else {
        // No data exists for the user, perform an INSERT operation
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($columns, registration_id) VALUES ($values, '$user_id')";

        if ($conn->query($sql) === TRUE) {
            return true; // Return true if new record is created successfully
        } else {
            return false; // Return false if there's an error creating new record
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the email from the form
    $email = isset($_POST["email"]) ? $_POST["email"] : "";

    if (!empty($email)) {
        // Get the user ID based on the email provided in the form
        $user_id = get_user_id($conn, $email);

        if ($user_id !== null) {


            // Prepare and bind SQL statement for employment history
            $stmt_history = $conn->prepare("INSERT INTO employment_history (position, organization, date_of_joining, date_of_leaving, user_id) VALUES (?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for teaching experience
            $stmt_teaching = $conn->prepare("INSERT INTO teaching_experience (position, employer, course_taught, ug_pg, number_of_students, date_of_joining, date_of_leaving, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for research experience
            $stmt_research = $conn->prepare("INSERT INTO research_experience (position, institute, supervisor, date_of_joining, date_of_leaving, user_id) VALUES (?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for industrial experience
            $stmt_industrial = $conn->prepare("INSERT INTO industrial_experience (organization, work_profile, date_of_joining, date_of_leaving, user_id) VALUES (?, ?, ?, ?, ?)");



            // Loop through each row of employment history data
            foreach ($_POST["position_history"] as $key => $value) {
                // Assign values from form data
                $position_history = $_POST["position_history"][$key];
                $organization_history = $_POST["organization_history"][$key];
                $date_of_joining_history = $_POST["date_of_joining_history"][$key];
                $date_of_leaving_history = $_POST["date_of_leaving_history"][$key];

                // Execute the SQL statement for employment history
                $stmt_history->bind_param("ssssi", $position_history, $organization_history, $date_of_joining_history, $date_of_leaving_history, $user_id);
                $stmt_history->execute();
            }

            // Loop through each row of teaching experience data
            foreach ($_POST["position_teaching"] as $key => $value) {
                // Assign values from form data
                $position_teaching = $_POST["position_teaching"][$key];
                $employer = $_POST["employer"][$key];
                $course_taught = $_POST["course_taught"][$key];
                $ug_pg = $_POST["ug_pg"][$key];
                $number_of_students = $_POST["number_of_students"][$key];
                $date_of_joining_teaching = $_POST["date_of_joining_teaching"][$key];
                $date_of_leaving_teaching = $_POST["date_of_leaving_teaching"][$key];

                // Execute the SQL statement for teaching experience
                $stmt_teaching->bind_param("ssssissi", $position_teaching, $employer, $course_taught, $ug_pg, $number_of_students, $date_of_joining_teaching, $date_of_leaving_teaching, $user_id);
                $stmt_teaching->execute();
            }

            // Loop through each row of research experience data
            foreach ($_POST["position_research"] as $key => $value) {
                // Assign values from form data
                $position_research = $_POST["position_research"][$key];
                $institute_research = $_POST["institute_research"][$key];
                $supervisor_research = $_POST["supervisor_research"][$key];
                $date_of_joining_research = $_POST["date_of_joining_research"][$key];
                $date_of_leaving_research = $_POST["date_of_leaving_research"][$key];

                // Execute the SQL statement for research experience
                $stmt_research->bind_param("sssssi", $position_research, $institute_research, $supervisor_research, $date_of_joining_research, $date_of_leaving_research, $user_id);
                $stmt_research->execute();
            }

            // Loop through each row of industrial experience data
            foreach ($_POST["organization_industrial"] as $key => $value) {
                // Assign values from form data
                $organization_industrial = $_POST["organization_industrial"][$key];
                $work_profile_industrial = $_POST["work_profile_industrial"][$key];
                $date_of_joining_industrial = $_POST["date_of_joining_industrial"][$key];
                $date_of_leaving_industrial = $_POST["date_of_leaving_industrial"][$key];

                // Execute the SQL statement for industrial experience
                $stmt_industrial->bind_param("ssssi", $organization_industrial, $work_profile_industrial, $date_of_joining_industrial, $date_of_leaving_industrial, $user_id);
                $stmt_industrial->execute();
            }

            // Close prepared statements
            $stmt_present->close();
            $stmt_history->close();
            $stmt_teaching->close();
            $stmt_research->close();
            $stmt_industrial->close();

            // Redirect to the next page
            header("Location: page4.html");
            exit();
        } else {
            // Handle case where user does not exist
            echo "Error: User with email $email not found.";
        }

        // Close prepared statement
        $stmt_get_user_id->close();

        // Close database connection
        $conn->close();
    } else {
        // Handle case where required fields are not set
        echo "Error: Email field is not set.";
    }
}
?>
