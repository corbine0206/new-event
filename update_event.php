<?php
include 'connection.php';
$connection = openConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $eventTitle = $_POST['eventTitle'];
    $sessionTitles = $_POST['sessionTitle'];
    $technologies = $_POST['technology'];
    $technologyLines = $_POST['technologyLine'];
    $productNames = $_POST['productName'];
    $speakers = $_POST['speaker'];
    $speakerSpecials = $_POST['speakerSpecial'];
    $dates = $_POST['date'];
    $timeams = $_POST['timeam'];
    $timepms = $_POST['timepm'];

    // Update event title
    $eventUpdateQuery = "UPDATE events SET event_title = ? WHERE event_id = ?";
    $stmt = $connection->prepare($eventUpdateQuery);
    $stmt->bind_param("si", $eventTitle, $event_id);
    $stmt->execute();
    $stmt->close();

    // Update each session
    foreach ($sessionTitles as $sessionId => $sessionTitle) {
        $technology = $technologies[$sessionId];
        $technologyLine = $technologyLines[$sessionId];
        $productName = $productNames[$sessionId];
        $speaker = $speakers[$sessionId];
        $speakerSpecial = $speakerSpecials[$sessionId];
        $date = $dates[$sessionId];
        $timeam = $timeams[$sessionId];
        $timepm = $timepms[$sessionId];

        $sessionUpdateQuery = "UPDATE event_sessions SET session_title = ?, technology = ?, technology_line = ?, product_name = ?, speaker = ?, speaker_special = ?, date = ?, timeam = ?, timepm = ? WHERE session_id = ?";
        $stmt = $connection->prepare($sessionUpdateQuery);
        $stmt->bind_param("sssssssssi", $sessionTitle, $technology, $technologyLine, $productName, $speaker, $speakerSpecial, $date, $timeam, $timepm, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    
    header("location: event.php");
}
?>
