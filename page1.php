<?php

$conn = new mysqli("localhost", "root", "", "user_data");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract form data
    $email = $_POST["email"];
    $department = $_POST["department"];
    $post_applied_for = $_POST["post_applied_for"];
    $firstname = $_POST["first_name"];
    $middlename = $_POST["middle_name"];
    $lastname = $_POST["last_name"];
    $nationality = $_POST["nationality"];
    $gender = $_POST["gender"];
    $dob = $_POST["date_of_birth"];
    $id_proof = $_POST["id_proof"];
    $father_name = $_POST["father_name"];
    $marital_status = $_POST["marital_status"];
    $category = $_POST["category"];
    $id_proof_image_tmp = $_FILES["id_proof_upload"]["tmp_name"]; // Temporary path of ID proof image
    $profile_image_tmp = $_FILES["image_upload"]["tmp_name"]; // Temporary path of profile image    

    // Extract additional form fields
    $correspondence_address = $_POST["correspondence_address"];
    $permanent_address = $_POST["permanent_address"];
    $mobile_number = $_POST["mobile_number"];
    $alternate_mobile_number = $_POST["alternate_mobile_number"];
    $alternate_email = $_POST["alternate_email"];

    // Check if the user exists in registration_data table based on email
    $check_query = "SELECT id FROM registration_data WHERE email = '$email'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        // User exists, get registration_id
        $row = $result->fetch_assoc();
        $registration_id = $row["id"];

        // Check if personal details already exist for this user
        $personal_check_query = "SELECT * FROM personal_details WHERE registration_id = '$registration_id'";
        $personal_result = $conn->query($personal_check_query);

        if ($personal_result->num_rows > 0) {
            // Personal details already exist, update them
            $personal_update_query = "UPDATE personal_details SET department='$department', post_applied_for='$post_applied_for', firstname='$firstname', middlename='$middlename', lastname='$lastname', nationality='$nationality', gender='$gender', date_of_birth='$dob', id_proof='$id_proof', father_name='$father_name', marital_status='$marital_status', category='$category' WHERE registration_id='$registration_id'";
            if ($conn->query($personal_update_query) === TRUE) {
                echo "Personal details updated successfully<br>";
            } else {
                echo "Error updating personal details: " . $conn->error . "<br>";
            }
        } else {
            // Personal details do not exist, insert them
            $personal_insert_query = "INSERT INTO personal_details (registration_id, department, post_applied_for, firstname, middlename, lastname, nationality, gender, date_of_birth, id_proof, father_name, marital_status, category) VALUES ('$registration_id', '$department', '$post_applied_for', '$firstname', '$middlename', '$lastname', '$nationality', '$gender', '$dob', '$id_proof', '$father_name', '$marital_status', '$category')";
            if ($conn->query($personal_insert_query) === TRUE) {
                echo "Personal details saved successfully<br>";
            } else {
                echo "Error saving personal details: " . $conn->error . "<br>";
            }
        }

        // Check if contact details already exist for this user
        $contact_check_query = "SELECT * FROM contact_details WHERE registration_id = '$registration_id'";
        $contact_result = $conn->query($contact_check_query);

        if ($contact_result->num_rows > 0) {
            // Contact details already exist, update them
            $contact_update_query = "UPDATE contact_details SET correspondence_address='$correspondence_address', permanent_address='$permanent_address', mobile_number='$mobile_number', alternate_mobile_number='$alternate_mobile_number', email='$email', alternate_email='$alternate_email' WHERE registration_id='$registration_id'";
            if ($conn->query($contact_update_query) === TRUE) {
                echo "Contact details updated successfully<br>";
            } else {
                echo "Error updating contact details: " . $conn->error . "<br>";
            }
        } else {
            // Contact details do not exist, insert them
            $contact_insert_query = "INSERT INTO contact_details (registration_id, correspondence_address, permanent_address, mobile_number, alternate_mobile_number, email, alternate_email) VALUES ('$registration_id', '$correspondence_address', '$permanent_address', '$mobile_number', '$alternate_mobile_number', '$email', '$alternate_email')";
            if ($conn->query($contact_insert_query) === TRUE) {
                echo "Contact details saved successfully<br>";
            } else {
                echo "Error saving contact details: " . $conn->error . "<br>";
            }
        }

        // Save images
        $id_proof_image = addslashes(file_get_contents($id_proof_image_tmp)); // Convert image to MEDIUMBLOB
        $profile_image = addslashes(file_get_contents($profile_image_tmp)); // Convert image to MEDIUMBLOB

        // Check if images already exist for this user
        $images_check_query = "SELECT * FROM images WHERE registration_id = '$registration_id'";
        $images_result = $conn->query($images_check_query);

        if ($images_result->num_rows > 0) {
            // Images already exist, update them
            $images_update_query = "UPDATE images SET id_proof_image='$id_proof_image', profile_image='$profile_image' WHERE registration_id='$registration_id'";
            if ($conn->query($images_update_query) === TRUE) {
                echo "Images updated successfully<br>";
            } else {
                echo "Error updating images: " . $conn->error . "<br>";
            }
        } else {
            // Images do not exist, insert them
            $images_insert_query = "INSERT INTO images (registration_id, id_proof_image, profile_image) VALUES ('$registration_id', '$id_proof_image', '$profile_image')";
            if ($conn->query($images_insert_query) === TRUE) {
                echo "Images saved successfully<br>";
            } else {
                echo "Error saving images: " . $conn->error . "<br>";
            }
        }

        // Redirect to page2.html regardless of errors
        header("Location: ./page2.html");
        exit();
    } else {
        echo "User with email '$email' not found in registration_data table<br>";
    }
}

// Redirect to page2.html if accessed without form submission or via back button
header("Location: ./page2.html");
exit();

// Close database connection
$conn->close();
?>
