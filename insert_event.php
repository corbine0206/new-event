<?php
session_start();
include 'session.php';
include 'connection.php'; // Include your database connection script
$user_id = $_SESSION['user_id'];
$connection = openConnection();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['eventTitle'])) {
        $eventTitle = mysqli_real_escape_string($connection, $_POST['eventTitle']);

        // Insert the event title into the events table
        $eventInsertSql = "INSERT INTO events (event_title, user_id) VALUES ('$eventTitle', '$user_id')";

        if (mysqli_query($connection, $eventInsertSql)) {
            $event_id = mysqli_insert_id($connection); // Get the event_id of the newly inserted event

            if (isset($_POST['topic']) && is_array($_POST['topic'])) {
                foreach ($_POST['topic'] as $topicIndex => $topicTitle) {
                    $topicTitle = mysqli_real_escape_string($connection, $topicTitle);

                    // Insert the topic into the topics table with the event_id
                    $topicInsertSql = "INSERT INTO topics (event_id, topic_title) VALUES ('$event_id', '$topicTitle')";

                    if (mysqli_query($connection, $topicInsertSql)) {
                        $topic_id = mysqli_insert_id($connection); // Get the topic_id of the newly inserted topic

                        // Check if subtopics were submitted for this topic
                        if (isset($_POST['subtopic']) && is_array($_POST['subtopic'])) {
                            foreach ($_POST['subtopic'] as $topicIndex => $subtopicArray) {
                                foreach ($subtopicArray as $subtopicIndex => $subtopicTitle) {
                                    $subtopicTitle = mysqli_real_escape_string($connection, $subtopicTitle);
                                    $product = mysqli_real_escape_string($connection, $_POST['product'][$topicIndex][$subtopicIndex]);
                                    $date = mysqli_real_escape_string($connection, $_POST['date'][$topicIndex][$subtopicIndex]);
                                    $time = mysqli_real_escape_string($connection, $_POST['time'][$topicIndex][$subtopicIndex]);
                                    $time2 = mysqli_real_escape_string($connection, $_POST['time2'][$topicIndex][$subtopicIndex]);
                                    $technologyLine = mysqli_real_escape_string($connection, $_POST['TechnologyLine'][$topicIndex][$subtopicIndex]);

                                    // Insert the subtopic into the subtopics table with additional fields
                                    $subtopicInsertSql = "INSERT INTO subtopics (event_id,topic_id, subtopic_title, product, date1, time1, technology_line) VALUES ('$event_id','$topic_id', '$subtopicTitle', '$product', '$date', '$time', '$technologyLine')";
                                    mysqli_query($connection, $subtopicInsertSql);
                                }
                            }
                        }
                    }
                }
            }

            echo "Event, topics, and subtopics inserted into the database.";

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
                    $message = '<a href="http:laundryandwash.comt/event/event-add.php?eventID='.$event_id.'&email='.$to.'">Click here to access the event</a>';
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

        } else {
            echo "Error: " . mysqli_error($connection);
        }
    } else {
        echo "Event title not provided.";
    }

    mysqli_close($connection);
}
?>
