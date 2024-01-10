<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventTitle = $_POST["eventTitle"];
    $topics = $_POST["topic"];
    $subtopics = $_POST["subtopic"];

    echo "Event Title: " . $eventTitle . "<br>";

    foreach ($topics as $topicIndex => $topic) {
        echo "Topic " . ($topicIndex + 1) . ": " . $topic . "<br>";

        if (isset($subtopics[$topicIndex]) && is_array($subtopics[$topicIndex])) {
            foreach ($subtopics[$topicIndex] as $subtopicIndex => $subtopic) {
                echo "Subtopic " . ($subtopicIndex + 1) . " (Topic " . ($topicIndex + 1) . "): " . $subtopic . "<br>";
            }
        }
    }
}
?>
