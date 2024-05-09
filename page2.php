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
            // Process and insert/update data for PhD details
            $phd_data = array(
                "university" => sanitize_input($_POST["university"]),
                "department" => sanitize_input($_POST["department"]),
                "supervisor" => sanitize_input($_POST["supervisor"]),
                "year_of_joining" => sanitize_input($_POST["year_of_joining_phd"]),
                "date_of_thesis_defense" => sanitize_input($_POST["date_of_thesis_defence"]),
                "date_of_award" => sanitize_input($_POST["date_of_award"]),
                "thesis_name" => sanitize_input($_POST["thesis_name"])
            );

            // Insert or update PhD data
            $phd_updated = insert_or_update_data($conn, "phd_details", $phd_data, $user_id);

            // Process and insert/update data for academic details
            $academic_data = array(
                "degree_certificate" => sanitize_input($_POST["degree_certificate"]),
                "university_institute" => sanitize_input($_POST["university_institute"]),
                "branch_stream" => sanitize_input($_POST["branch_stream"]),
                "year_of_joining" => sanitize_input($_POST["year_of_joining_academic"]),
                "year_of_completion" => sanitize_input($_POST["year_of_completion"]),
                "duration" => sanitize_input($_POST["duration"]),
                "percentage_cgpa" => sanitize_input($_POST["percentage_cgpa"]),
                "division_class" => sanitize_input($_POST["division_class"])
            );

            // Insert or update academic data
            $academic_updated = insert_or_update_data($conn, "academic_details", $academic_data, $user_id);
// Process and insert/update data for school details
if (isset($_POST['school'])) {
    foreach ($_POST['school'] as $school_data) {
        $sanitized_school_data = array(
            "exam_type" => sanitize_input($school_data["exam_type"]),
            "school_name" => sanitize_input($school_data["school_name"]),
            "year_of_passing" => sanitize_input($school_data["year_of_passing"]),
            "percentage_grade" => sanitize_input($school_data["percentage_grade"]),
            "division_class" => sanitize_input($school_data["division_class_school"])
        );
        $school_updated = insert_or_update_data($conn, "school_details", $sanitized_school_data, $user_id);
        if (!$school_updated) {
            echo "Error updating school data.";
            // Handle errors as needed
        }
    }
}

// Process and insert/update data for additional qualifications
if (isset($_POST['degree_certificate_additional'])) {
    for ($i = 0; $i < count($_POST['degree_certificate_additional']); $i++) {
        $qualification_data = array(
            "degree_certificate" => sanitize_input($_POST["degree_certificate_additional"][$i]),
            "university_institute" => sanitize_input($_POST["university_institute_additional"][$i]),
            "branch_stream" => sanitize_input($_POST["branch_stream_additional"][$i]),
            "year_of_joining" => sanitize_input($_POST["year_of_joining_additional"][$i]),
            "year_of_completion" => sanitize_input($_POST["year_of_completion_additional"][$i]),
            "duration" => sanitize_input($_POST["duration_additional"][$i]),
            "percentage_cgpa" => sanitize_input($_POST["percentage_cgpa_additional"][$i]),
            "division_class" => sanitize_input($_POST["division_class_additional"][$i])
        );
        $sanitized_qualification_data = array_map('sanitize_input', $qualification_data);
        $qualification_updated = insert_or_update_data($conn, "additional_qualification", $sanitized_qualification_data, $user_id);
        if (!$qualification_updated) {
            echo "Error updating qualification data.";
            // Handle errors as needed
        }else {
                        echo "Qualification data updated successfully.<br>";
                    }
                }
            } else {
                echo "No additional qualifications found.<br>";
            }        } else {
            echo "User not found!";
        }
    } else {
        echo "Email is required!";
    }
}

// Close the database connection
$conn->close();
?>