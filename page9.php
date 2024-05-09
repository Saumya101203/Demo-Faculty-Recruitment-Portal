<?php
require_once('TCPDF-main/tcpdf.php'); // Include TCPDF library

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

// Function to fetch user_id based on the provided email
function get_user_id($conn, $email) {
    $email = sanitize_input($email);
    $sql = "SELECT id FROM registration_data WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        return null; // Return null if email not found
    }
}

// Function to fetch user data from personal_details table
function get_personal_details($conn, $registration_id) {
    $registration_id = sanitize_input($registration_id);
    $sql = "SELECT * FROM personal_details WHERE registration_id = '$registration_id'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Function to fetch user data from contact_details table
function get_contact_details($conn, $registration_id) {
    $registration_id = sanitize_input($registration_id);
    $sql = "SELECT * FROM contact_details WHERE registration_id = '$registration_id'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Function to fetch user data from publicationsummary table
function get_publication_summary($conn, $user_id) {
    $user_id = sanitize_input($user_id);
    $sql = "SELECT * FROM publicationsummary WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user's email from the form
    $email = isset($_POST["email"]) ? $_POST["email"] : "";

    if (!empty($email)) {
        // Fetch user_id based on the provided email
        $user_id = get_user_id($conn, $email);

        if ($user_id !== null) {
            // Fetch user data from the tables
            $personal_details = get_personal_details($conn, $user_id);
            $contact_details = get_contact_details($conn, $user_id);
            $publication_summary = get_publication_summary($conn, $user_id);

            // Create a new PDF instance
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Name');
            $pdf->SetTitle('User Data');
            $pdf->SetSubject('User Data');
            $pdf->SetKeywords('User, Data, PDF');

            // Set margins
            $pdf->SetMargins(10, 10, 10);

            // Add a page
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', '', 12);

            // Add header
            $pdf->SetHeaderData('', 0, 'INDIAN INSTITUTE OF TECHNOLOGY PATNA', 'भारतीय प्रौद्योगिकी संस्थान पटना', array(0,0,0), array(255,255,255));

            // Add footer
            $pdf->setFooterData(array(0,0,0), array(255,255,255));

            // Add user data from personal_details table to the PDF
            foreach ($personal_details as $key => $value) {
                $pdf->Cell(0, 10, $key . ': ' . $value, 0, 1);
            }

            // Add user data from contact_details table to the PDF
            foreach ($contact_details as $key => $value) {
                $pdf->Cell(0, 10, $key . ': ' . $value, 0, 1);
            }

            // Add user data from publicationsummary table to the PDF
            foreach ($publication_summary as $key => $value) {
                $pdf->Cell(0, 10, $key . ': ' . $value, 0, 1);
            }

            // Output PDF to the browser
            $pdf->Output('user_data.pdf', 'D'); // D for download, I for inline display
        } else {
            // Handle case where user email is not found
            echo "Error: User email not found in the database.";
        }
    } else {
        // Handle case where required fields are not set
        echo "Error: Email field is empty.";
    }
}

// Close the database connection
$conn->close();
?>
