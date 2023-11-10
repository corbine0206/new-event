<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

        // Initialize an array to store the data
        $data = array();

        // Read and process the CSV data
        while (($row = fgetcsv($csvFile)) !== false) {
            $data[] = $row;
        }

        // Close the CSV file
        fclose($csvFile);

        // Display the data
        echo "<h2>Uploaded CSV Data</h2>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "Error uploading the file.";
    }
}
?>
