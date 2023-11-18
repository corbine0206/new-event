<?php
    session_start();
    include 'connection.php';
    include 'phpqrcode\phpqrcode\qrlib.php';
    require 'vendor/autoload.php'; // Include Composer's autoloader

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    $event_id = isset($_GET['eventID']) ? $_GET['eventID'] : null;
    $email = isset($_GET['email']) ? $_GET['email'] : null;
    
    try {
        if ($event_id !== null && $email !== null) {
            $connection = openConnection();
            $strSql = "SELECT * FROM participants where event_id = '$event_id' and email = '$email'";
            $result = mysqli_query($connection, $strSql);
            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                    $reqPersons = mysqli_fetch_array($result);
                    mysqli_free_result($result);
                }
                else{
                    $message = "You are not registered on this event!";
                }
            }

           
        } else {
            $message = "";
            throw new Exception('');
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
        $message = "";
    }
?>

<?php
if (isset($_POST['btnSubmit'])) {
    // Establish a database connection if needed
    $connection = openConnection();

    $email = $_GET['email'];
    $event_id = $_GET['eventID'];
    $updateParticipantSql = "UPDATE participants set status = 1 where event_id = '$event_id' and email = '$email'";
    if (mysqli_query($connection, $updateParticipantSql)) {
        echo 'Successfully update participants';
    }
    $updateEventSql = "UPDATE events set event_status = 2 where event_id = '$event_id'";
    if (mysqli_query($connection, $updateEventSql)) {
        echo "successfully event updated to 2";
    }
    $selected_data = [];
    if (isset($_POST["technology"])) {
        $selectedTechnologies = $_POST["technology"];

        foreach ($selectedTechnologies as $selectedTechId) {
            $selectedDropdownValue = $_POST["dropdown_" . $selectedTechId];
            // Insert the selected technology_id and dropdown value into your database
             $insertSql = "INSERT INTO response (event_id, email, response) VALUES ('$event_id','$email', '$selectedDropdownValue')";
             $selected_data[] = $selectedDropdownValue;
             if (mysqli_query($connection, $insertSql)) {
                 echo 'Data inserted successfully for technology ID ' . $selectedDropdownValue . '<br>';
             } else {
                 echo 'Error inserting data for technology ID ' . $selectedDropdownValue . ': ' . mysqli_error($con) . '<br>';
             }
        }
    }
    // Retrieve the dataset
    $datasetSql = "SELECT * from event_sessions where event_id = '$event_id'";
    
    // Execute the SQL query and fetch the data
    $result = mysqli_query($connection, $datasetSql);
    
    // Initialize an array to store dataset entries
    $dataset = array();

    // Fetch rows and add them to the dataset array
    while ($row = mysqli_fetch_assoc($result)) {
        $dataset[] = $row;
    }
    /// Combine the data into an associative array
    $dataToPass = array(
        "user_preferences" => $selected_data,
        "dataset" => $dataset
    );

    // Encode the data as JSON
    $jsonData = json_encode($dataToPass);
    // Create a temporary file to store the JSON data
    $tempFile = tempnam(sys_get_temp_dir(), 'json_data');
    file_put_contents($tempFile, $jsonData);

    // Use escapeshellarg to properly escape the file path for the command line
    $fileArg = escapeshellarg($tempFile);

    // Call your Python script with the file path as an argument
    $command = "python new-recommendation.py $fileArg";
    exec($command, $output, $returnCode);

    // Remove the temporary file
    unlink($tempFile);

    $outputFileName = "$email.png";
    $textToEncode = $email;
    // Generate the QR code
    QRcode::png($textToEncode, $outputFileName, QR_ECLEVEL_L, 3);
    if ($returnCode === 0) {
        // The Python script executed successfully
        $emailContent = '<h1>Session Information:</h1><br>'; // Initialize email content
        $printed_session_ids = [];
        $reserved_time1 = [];
        $reserved_time2 = [];
    
        foreach ($output as $line) {
            // Parse the JSON data sent by the Python script
            $json_data = json_decode($line, true);
    
            if ($json_data) {
                foreach ($json_data as $result) {
                    $session_id = $result['Session ID'];
                    $session_title = $result['Session Title'];
                    $date1 = $result['Date'];
                    $time1 = $result['Timeam'];
                    $time2 = $result['Timepm'];
                    $speaker = $result['Speaker'];
                    // Create a unique identifier for the time slot based on Date1 and the time
                    $time_slot_identifier = "$date1-$time1";
    
                    // Check if the current session_id is different from the previous one
                    if (!in_array($session_title, $printed_session_ids)) {
                        // Check if Time1 is available and Date1/Time1 doesn't conflict with previous recommendations
                        if (!empty($time1) && !in_array($time_slot_identifier, $reserved_time1)) {
                            // Add session information to the email content with Time1
                            $emailContent .= "<p>Session Title: " . $result['Session Title'] . "</p>";
                            $emailContent .= "<p>Speaker: ". $speaker ."</p>";
                            $emailContent .= "<p>Date: $date1</p>";
                            $emailContent .= "<p>Time Morning: $time1</p>";
                            $emailContent .= "<hr>";
                            // Reserve Time1
                            $reserved_time1[] = $time_slot_identifier;
                        } elseif (!empty($time2) && !in_array($time_slot_identifier, $reserved_time2)) {
                            // Add session information to the email content with Time2
                            $emailContent .= "<p>Session Title: " . $result['Session Title'] . "</p>";
                            $emailContent .= "<p>Speaker: ". $speaker ."</p>";
                            $emailContent .= "<p>Date: $date1</p>";
                            $emailContent .= "<p>Time Afternoon: $time2</p>";
                            $emailContent .= "<hr>";
                            // Reserve Time2
                            $reserved_time2[] = $time_slot_identifier;
                        }
                        // Add the current session_id to the printed_session_ids array
                        $printed_session_ids[] = $session_title;
                    }
                }
            } else {
                echo 'Invalid JSON data received from Python<br>';
            }
        }
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
        $mail->setFrom('event@laundryandwash.com', 'Event Organizer');
    
        $mail->addAddress($email); // Recipient's email address
        $mail->isHTML(true);
        $mail->Subject = "SESSION RECOMMENDED";
    
        // Add the QR code image as an attachment
        $mail->addAttachment($outputFileName);
    
        // Embed the QR code image in the email body
        $emailContent .= '<br><img src="cid:' . $outputFileName . '>';
        $mail->Body = $emailContent;
    
        // Send the email
        if ($mail->send()) {
            echo "Email sent successfully.";
        } else {
            echo "Email sending failed: " . $mail->ErrorInfo;
        }
    } else {
        // There was an error executing the Python script
        echo "Error executing Python script. Return code: $returnCode";
    }
    header("Refresh:0");
    
}
?>
<script src="https://cdn.tailwindcss.com"></script>
<script src="http://localhost/js/tailwind.config.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        /* Style for the dropdown menus */

        .body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
    </style>
<body id="page-top">
<section class="bg-cover" style="background-image: url('https://media-s3-us-east-1.ceros.com/g3-communications/images/2021/04/21/bf088fa43296be6d4cee5685a37e6a30/untitled.gif');">
    <!-- Page Wrapper -->
    <div id="wrapper">


        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column body">

            <div id="content" class="container">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700">         
                        <?php
                        if (isset($message)) {
                            echo '<h5 class="mb-3 text-base font-semibold text-gray-900 md:text-l dark:text-white">Sorry, you are not registered in this event!</h5>';
                            echo '<p class="mb-3 text-sm font-normal text-gray-500 dark:text-gray-400">Please check your email again or contact the event organizer for more details. Thank you!</p>'; 
                            echo '<img class="h-auto max-w-full mx-auto" src="https://media4.giphy.com/media/Bc4oup2pdP5iKFAYiF/200w.gif?cid=6c09b9520ewqcrypuassw0qj1nck4jcukefjjr5322adfum0&ep=v1_gifs_search&rid=200w.gif&ct=g" alt="image description">';
                        } else {
                            if ($reqPersons['status'] == 1) {
                                echo '<h5 class="mb-3 text-base font-semibold text-gray-900 md:text-l dark:text-white">Thank you for sharing your Interest with us!!</h5>';
                                echo '<p class="mb-3 text-sm font-normal text-gray-500 dark:text-gray-400">We will send you a journey map for the event based on your interest.</p>'; 
                                echo '<img class="h-auto max-w-full mx-auto" src="https://assets-global.website-files.com/6091b7081a1d7e13ccd7603a/63f018f7c54c02f7cdc1c256_giphy-3.gif" alt="image description">';
                            } elseif (isset($message)) {
                                echo "No email and event ID found";
                            } else {
                        ?>
                        <h5 class="mb-3 text-base font-semibold text-gray-900 md:text-xl dark:text-white">
                        Interest Survey Form
                        </h5>
                        <p class="mb-3 text-sm font-normal text-gray-500 dark:text-gray-400">Tell us your interest and we'll take care of the rest!!</p>

                        <form method="post">
                            <input type="hidden" value="<?php echo $_GET['eventID'] ?>" name="event_id">
                            <input type="hidden" value="<?php echo $_GET['email'] ?>" name="email">
                            <div class="form-group">
                                <?php
                                $con = openConnection();

                                // Retrieve distinct technologies from the event_session table
                                $strSql = "SELECT DISTINCT technology FROM event_sessions where event_id = '$event_id'";
                                $result = getRecord($con, $strSql);

                                // Loop through the distinct technologies and create a dropdown for each
                                foreach ($result as $key => $value) {
                                    $techName = $value['technology'];
                                    $techNameFormatted = str_replace(' ', '_', $techName);
                                    
                                    // Query the event_session table for technology lines related to the current technology
                                    $dropdownSql = "SELECT DISTINCT technology_line FROM event_sessions WHERE technology = '$techName'";
                                    $dropdownResult = getRecord($con, $dropdownSql);
                                    echo '<div class="mb-3 flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">';
                                    echo '<label for="technology[]" class="ms-2 mb-0 text-sm font-medium text-gray-900 dark:text-gray-300">';
                                    echo '<input type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" name="technology[]" id="technology_' . $techNameFormatted . '" value="' . $techNameFormatted . '">';
                                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $techName;
                                    echo '</label>';
                                    echo '</div>';

                                    // Create the dropdown menu
                                    echo '<select class="mb-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 hidden ..." name="dropdown_' . $techNameFormatted  . '">';
                                    foreach ($dropdownResult as $dropdownKey => $dropdownValue) {
                                        $optionValue = $dropdownValue['technology_line'];
                                        echo '<option value="' . $optionValue . '">' . $optionValue . '</option>';
                                    }
                                    echo '</select>';
                                    
                                }
                                ?>
                                <button type="submit"  class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800" name="btnSubmit">Submit Form</button>
                            </form>
                        </div>
                    </div>
                <?php } } ?>
            </div>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    </section>
    <?php //include 'script.php'; ?>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        var maxChecked = 3; // Set the maximum number of checkboxes allowed

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                var checkedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');

                if (checkedCheckboxes.length >= maxChecked) {
                    checkboxes.forEach(function (cb) {
                        if (!cb.checked) {
                            cb.disabled = true;
                        }
                    });
                } else {
                    checkboxes.forEach(function (cb) {
                        cb.disabled = false;
                    });
                }

                var techName = this.value;
                var dropdown = document.querySelector('select[name="dropdown_' + techName + '"]');

                if (this.checked) {
                    dropdown.style.display = "block";
                    // Print the name of the checked technology to the console
                    console.log("Checked technology: " + techName);
                } else {
                    dropdown.style.display = "none";
                }
            });
        });
    });

</script>
