<?php include 'script.php'; ?>
<?php
session_start();
include 'connection.php'; // Include your database connection script
include 'phpqrcode\phpqrcode\qrlib.php';
require 'vendor/autoload.php'; // Include Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$connection = openConnection();
$user_id = $_SESSION['user_id'];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['eventTitle'])) {
        $eventTitle = mysqli_real_escape_string($connection, $_POST['eventTitle']);
        $user_id = $_POST['user_id'];
        // Insert the event title into the events table
        $eventInsertSql = "INSERT INTO events (event_title, user_id, event_status) VALUES ('$eventTitle', '$user_id', 1)";
        if (mysqli_query($connection, $eventInsertSql)) {
            $event_id = mysqli_insert_id($connection); // Get the event_id of the newly inserted event

            if (isset($_POST['session']) && is_array($_POST['session'])) {
                foreach ($_POST['session'] as $sessionIndex => $sessionTitle) {
                    $sessionTitle = mysqli_real_escape_string($connection, $sessionTitle);
                    $date = mysqli_real_escape_string($connection, $_POST['date'][$sessionIndex]);
                    $time = mysqli_real_escape_string($connection, $_POST['time'][$sessionIndex]);
                    $time2 = mysqli_real_escape_string($connection, $_POST['time2'][$sessionIndex]);

                    // Insert the session into the event_sessions table with the event_id
                    $sessionInsertSql = "INSERT INTO event_sessions (event_id, session_title, date1, time1, time2) VALUES ('$event_id', '$sessionTitle', '$date', '$time','$time2')";
                    if (mysqli_query($connection, $sessionInsertSql)) {
                        $session_id = mysqli_insert_id($connection); // Get the session_id of the newly inserted session

                        // Check if technologies were submitted for this session
                        if (isset($_POST['technology'][$sessionIndex]) && is_array($_POST['technology'][$sessionIndex])) {
                            foreach ($_POST['technology'][$sessionIndex] as $techIndex => $technology) {
                                $technology = mysqli_real_escape_string($connection, $technology);

                                // Insert the technology into the technologies table with the event_id and session_id
                                $techInsertSql = "INSERT INTO technologies (event_id, session_id, technology_name) VALUES ('$event_id', '$session_id', '$technology')";
                                if (mysqli_query($connection, $techInsertSql)) {
                                    $tech_id = mysqli_insert_id($connection); // Get the technology_id of the newly inserted technology

                                    // Check if products and technology lines were submitted for this technology
                                    if (
                                        isset($_POST['product'][$sessionIndex][$techIndex]) &&
                                        isset($_POST['technologyLine'][$sessionIndex][$techIndex]) &&
                                        is_array($_POST['product'][$sessionIndex][$techIndex]) &&
                                        is_array($_POST['technologyLine'][$sessionIndex][$techIndex])
                                    ) {
                                        $products = $_POST['product'][$sessionIndex][$techIndex];
                                        $techLines = $_POST['technologyLine'][$sessionIndex][$techIndex];

                                        foreach ($products as $productIndex => $product) {
                                            $product = mysqli_real_escape_string($connection, $product);
                                            $techLine = mysqli_real_escape_string($connection, $techLines[$productIndex]);

                                            // Insert product and technology line into product_technology_lines table with tech_id, event_id, session_id
                                            $productTechLineInsertSql = "INSERT INTO product_technology_lines (technology_id, event_id, session_id, product_name, technology_line) VALUES ('$tech_id', '$event_id', '$session_id', '$product', '$techLine')";
                                            mysqli_query($connection, $productTechLineInsertSql);
                                        }
                                    }
                                } else {
                                    echo '<script type="text/javascript">
                                    swal({
                                        title: "Warning",
                                        text: "Redirecting in 2 seconds.\failed to insert technology",
                                        icon: "Warning",
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.href = "./event.php";
                                    });
                                </script>';
                                }
                            }
                        }
                    } else {
                        echo '<script type="text/javascript">
                            swal({
                                title: "Warning",
                                text: "Redirecting in 2 seconds.\failed to insert session",
                                icon: "Warning",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = "./event.php";
                            });
                        </script>';
                    }
                }
            }

            echo '<script type="text/javascript">
                    swal({
                        title: "Success",
                        text: "Redirecting in 2 seconds.\nSuccessfully add event",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        
                    });
                </script>';
        } else {
            echo "Error inserting event: " . mysqli_error($connection);
        }
    } else {
        echo "Event title not provided.";
    }
    // Check if a file was uploaded
    if (isset($_FILES["csvFile"]) && $_FILES["csvFile"]["error"] == UPLOAD_ERR_OK) {
        // Get the uploaded file details
        $fileName = $_FILES["csvFile"]["name"];
        $tmpName = $_FILES["csvFile"]["tmp_name"];
        $fileSize = $_FILES["csvFile"]["size"];

        // Check if the uploaded file is a CSV
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExtension != "csv") {
            echo "Please upload a CSV file.";
            exit();
        }

        // Open the uploaded CSV file
        $csvFile = fopen($tmpName, 'r');

        // Initialize a string to store the values from the first column
        $firstColumnValues = '';

        // Read and process the CSV data
        while (($row = fgetcsv($csvFile)) !== false) {
            // Check if there is a value in the first column
            if (isset($row[0])) {
                $firstColumnValues .= $row[0] . ','; // Use ',' for separation
            }
        }

        // Close the CSV file
        fclose($csvFile);
        // Remove trailing comma
        $firstColumnValues = rtrim($firstColumnValues, ',');

        // Now, $firstColumnValues should be a string
        //echo "<p>$firstColumnValues</p>";

        // Split the concatenated values into an array
        $data = explode(',', $firstColumnValues);
        $mail = new PHPMailer(true);
        // SMTP settings (you may need to configure these)
        $mail->isSMTP();
        $mail->Host = 'mail.laundryandwash.com';
        $mail->SMTPSecure = 'tls'; // Use 'tls' for TLS encryption
        $mail->SMTPAuth = true;
        $mail->Username = 'event@laundryandwash.com';
        $mail->Password = 'GhZ%3SiW]x=Z';
        $mail->Port = 587; // Change to your SMTP port
        // Set the "From" address correctly
        $mail->setFrom('event@laundryandwash.com', 'New Event');

        foreach ($data as $value) {
            $emailContent = '
            Subject: Join Us for an Exciting Event!
    
            Dear Participant,
    
            We\'re thrilled to invite you to a special event that promises to be an unforgettable experience. Your presence will make this occasion even more exceptional.
    
            **Event Details:**
            - Event Name: '.$eventTitle.'
    
            To join us, simply click on the link below, and you\'ll be whisked away to all the excitement and fun that awaits:
    
            <a href="http://localhost/event/event-form.php?eventID='.$event_id.'&email='.$value.'">Click here to access the event</a>
    
            This is your opportunity to connect, learn, and enjoy. Don\'t miss out on the chance to be part of something amazing. We look forward to seeing you there!
    
            If you have any questions or need assistance, please feel free to contact us at email@gmail.com or 09123456789.
    
            Thank you for being a part of this incredible journey. We can\'t wait to celebrate with you!
            ';
    
            $mail->addAddress($value); // Recipient's email address
            $mail->isHTML(true);
            $mail->Subject = "Join Us for an Exciting Event!";
            $mail->Body = $emailContent;
    
            if ($mail->send()) {
                // ... (your database insertion code)
            } else {
                // Handle email sending errors if needed
            }
        }
    
    } else {
        echo "Error uploading the file.";
    }
    echo "<script>
        alert('Successfully add event');
        window.location.href='event.php';
        </script>";
    mysqli_close($connection);
}
?>
