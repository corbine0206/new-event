<?php
    include 'connection.php';
    $connection = openConnection();

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    date_default_timezone_set('Asia/Manila');
    if (isset($_POST['scanned_data'])) {
        $scannedData = $_POST['scanned_data'];
        $event_id = $_POST['event_id'];
        $session_title = $_POST['session_title'];
        $dateIn = date("Y-m-d");
        $timeIn = date("H:i:s");

        $sqlSearch = "SELECT * FROM attendance where event_id = '$event_id' and email='$scannedData' and session_title='$session_title'";
        $result = $connection->query($sqlSearch);
        if ($result->num_rows === 0) {
            // Insert data into the database
            $sqlInsert = "INSERT INTO attendance (event_id, session_title, email, dateIn, timeIn) 
                            VALUES ('$event_id', '$session_title', '$scannedData', '$dateIn', '$timeIn')";
            if ($connection->query($sqlInsert) === TRUE) {
                header("location: scan.php?eventID=$event_id&session_title=$session_title&success=1&email=$scannedData");
            } 
            else {
            echo "Error: " . $sqlInsert . "<br>" . $conn->error;
            }
        }
        else{
            header("location: scan.php?eventID=$event_id&session_title=$session_title&success=2&email=$scannedData");
        }
        
    } 
    else {
        http_response_code(400); // Bad Request
        echo "Invalid input data";
    }
?>