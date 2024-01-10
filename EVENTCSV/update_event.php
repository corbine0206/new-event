<?php
session_start();
include 'connection.php'; // Include your database connection script
$connection = openConnection();
$user_id = $_SESSION['user_id'];
echo "user id" . $user_id;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['eventTitle'])) {
        $eventTitle = mysqli_real_escape_string($connection, $_POST['eventTitle']);

        // Insert the event title into the events table
        $eventInsertSql = "INSERT INTO events (event_title, user_id, event_status) VALUES ('$eventTitle', '$user_id', 1)";

        if (mysqli_query($connection, $eventInsertSql)) {
            $event_id = mysqli_insert_id($connection); // Get the event_id of the newly inserted event
            $oldEventID  = $_POST['event_id'];
            $deleteEvent = "DELETE from events where event_id = '$oldEventID'";

            $deleteSession = "DELETE from event_sessions where event_id = '$oldEventID'";
            $deleteTechnology = "DELETE FROM technologies where event_id = '$oldEventID'";
            $deleteProductTechnologyLine = "DELETE FROM product_technology_lines where event_id = '$oldEventID'";
            mysqli_query($connection, $deleteEvent);
            mysqli_query($connection, $deleteSession);
            mysqli_query($connection, $deleteTechnology);
            mysqli_query($connection, $deleteProductTechnologyLine);

            if (isset($_POST['session']) && is_array($_POST['session'])) {
                foreach ($_POST['session'] as $sessionIndex => $sessionTitle) {
                    $sessionTitle = mysqli_real_escape_string($connection, $sessionTitle);
                    $date = mysqli_real_escape_string($connection, $_POST['date'][$sessionIndex]);
                    $time = mysqli_real_escape_string($connection, $_POST['time'][$sessionIndex]);
                    $time2 = mysqli_real_escape_string($connection, $_POST['time2'][$sessionIndex]);

                    // Insert the session into the event_sessions table with the event_id
                    $sessionInsertSql = "INSERT INTO event_sessions (event_id, session_title, date1, time1, time2) VALUES ('$event_id', '$sessionTitle', '$date', '$time', '$time2')";
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
                                    echo "Error inserting technology: " . mysqli_error($connection);
                                }
                            }
                        }
                    } else {
                        echo "Error inserting session: " . mysqli_error($connection);
                    }
                }
            }

            echo '<script type="text/javascript">
                    swal({
                        title: "Success",
                        text: "Redirecting in 2 seconds.\nSuccessfully update event",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = "./event.php";
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

        // Display the values from the first column
        echo "<h2>Values from the First Column of the Uploaded CSV</h2>";

        // Remove trailing comma
        $firstColumnValues = rtrim($firstColumnValues, ',');

        // Now, $firstColumnValues should be a string
        //echo "<p>$firstColumnValues</p>";

        // Split the concatenated values into an array
        $data = explode(',', $firstColumnValues);

        foreach ($data as $value) {
            $to = $value;
            $subject = 'the subject';
            $message = '<a href="http://localhost/event/event-add.php?eventID='.$event_id.'&email='.$to.'">Click here to access the event</a>';
            $headers = "From: webmaster@example.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($to, $subject, $message, $headers)) {
                echo 'Email sent to ' . $to . '<br>';
                
                // Assuming you have established a valid database connection
                $participantsSql = "INSERT INTO participants(event_id, email, status) VALUES('$event_id', '$value', 0)";
                mysqli_query($connection, $participantsSql);
            } else {
                echo 'Email sending failed to ' . $to . '<br>';
            }
        }
    } else {
        echo "Error uploading the file.";
    }

    mysqli_close($connection);
}
?>
