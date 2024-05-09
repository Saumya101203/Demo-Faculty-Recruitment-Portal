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

            $check_stmt = $conn->prepare("SELECT user_id FROM publicationsummary WHERE user_id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                // Update existing record
                $stmt_summary_update = $conn->prepare("UPDATE publicationsummary SET InternationalJournalPapers = ?, InternationalConferencePapers = ?, Patents = ?, BookChapters = ?, NationalJournalPapers = ?, NationalConferencePapers = ?, Books = ?, google_scholar_link = ? WHERE user_id = ?");
                $stmt_summary_update->bind_param("iiiiiiiis", $user_id, $_POST["InternationalJournalPapers"], $_POST["InternationalConferencePapers"], $_POST["Patents"], $_POST["BookChapters"], $_POST["NationalJournalPapers"], $_POST["NationalConferencePapers"], $_POST["Books"], $_POST["google_scholar_link"]);
                $stmt_summary_update->execute();
                $stmt_summary_update->close();
            } else {
                // Insert new record
                $stmt_summary_insert = $conn->prepare("INSERT INTO publicationsummary (user_id, InternationalJournalPapers, InternationalConferencePapers, Patents, BookChapters, NationalJournalPapers, NationalConferencePapers, Books, google_scholar_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_summary_insert->bind_param("iiiiiiiis", $user_id, $_POST["InternationalJournalPapers"], $_POST["InternationalConferencePapers"], $_POST["Patents"], $_POST["BookChapters"], $_POST["NationalJournalPapers"], $_POST["NationalConferencePapers"], $_POST["Books"], $_POST["google_scholar_link"]);
                $stmt_summary_insert->execute();
                $stmt_summary_insert->close();
            }

            // Prepare and bind SQL statement for bestpublications
            $stmt_best = $conn->prepare("INSERT INTO bestpublications (user_id, Author, Title, JournalConferenceName, YearVolPage, ImpactFactor, DOI, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for research experience
            $stmt_patent = $conn->prepare("INSERT INTO patents (user_id, Inventor, PatentTitle, CountryOfPatent, PatentNumber, DateOfFiling, DateOfPublication, PatentStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // Prepare and bind SQL statement for industrial experience
            $stmt_book = $conn->prepare("INSERT INTO books (user_id, Authors, Title, YearOfPublication, ISBN) VALUES (?, ?, ?, ?, ?)");

            $stmt_bookchapters = $conn->prepare("INSERT INTO bookchapters(user_id, Authors, Title, YearOfPublication, ISBN) VALUES (?, ?, ?, ?, ?)");


            // Loop through each row of teaching experience data
            foreach ($_POST["Author"] as $key => $value) {
                // Assign values from form data
                $Author = $_POST["Author"][$key];
                $Title = $_POST["Title"][$key];
                $JournalConferenceName = $_POST["JournalConferenceName"][$key];
                $YearVolPage = $_POST["YearVolPage"][$key];
                $ImpactFactor = $_POST["ImpactFactor"][$key];
                $DOI = $_POST["DOI"][$key];
                $Status = $_POST["Status"][$key];

                // Execute the SQL statement for teaching experience
                $stmt_best->bind_param("isssssss", $user_id, $Author, $Title, $JournalConferenceName, $YearVolPage, $ImpactFactor, $DOI, $Status);
                $stmt_best->execute();
            }

            // Loop through each row of research experience data
            foreach ($_POST["Inventor"] as $key => $value) {
                // Assign values from form data
                $Inventor = $_POST["Inventor"][$key];
                $PatentTitle = $_POST["PatentTitle"][$key];
                $CountryOfPatent = $_POST["CountryOfPatent"][$key];
                $PatentNumber = $_POST["PatentNumber"][$key];
                $DateOfFiling = $_POST["DateOfFiling"][$key];
                $DateOfPublication = $_POST["DateOfPublication"][$key];
                $PatentStatus = $_POST["PatentStatus"][$key];
                // Execute the SQL statement for research experience
                $stmt_patent->bind_param("isssssss", $user_id,  $Inventor, $PatentTitle, $CountryOfPatent, $PatentNumber, $DateOfFiling, $DateOfPublication, $PatentStatus);
                $stmt_patent->execute();
            }

            // Loop through each row of industrial experience data
            foreach ($_POST["AuthorsB"] as $key => $value) {
                // Assign values from form data
                $Authors = $_POST["AuthorsB"][$key];
                $Title = $_POST["Title"][$key];
                $YearOfPublication = $_POST["YearOfPublication"][$key];
                $ISBN = $_POST["ISBN"][$key];

                // Execute the SQL statement for industrial experience
                $stmt_book->bind_param("issss", $user_id, $Authors, $Title, $YearOfPublication, $ISBN);
                $stmt_book->execute();
            }

            foreach ($_POST["AuthorsBC"] as $key => $value) {
                // Assign values from form data
                $Authors = $_POST["AuthorsBC"][$key];
                $Title = $_POST["Title"][$key];
                $YearOfPublication = $_POST["YearOfPublication"][$key];
                $ISBN = $_POST["ISBN"][$key];

                // Execute the SQL statement for industrial experience
                $stmt_bookchapters->bind_param("issss", $user_id, $Authors, $Title, $YearOfPublication, $ISBN);
                $stmt_bookchapters->execute();
            }

            // Close prepared statements
            $stmt_best->close();
            $stmt_patent->close();
            $stmt_book->close();
            $stmt_bookchapters->close();
            $stmt_summary->close();



            // Redirect to the next page
            header("Location: page5.html");
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
