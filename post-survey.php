<?php
    session_start();
    include 'connection.php';
    require 'vendor/autoload.php'; // Include Composer's autoloader

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    $event_id = isset($_GET['eventID']) ? $_GET['eventID'] : null;
    $email = isset($_GET['email']) ? $_GET['email'] : null;
    
    // try {
    //     if ($event_id !== null && $email !== null) {
    //         $connection = openConnection();
    //         $strSql = "SELECT * FROM participants where event_id = '$event_id' and email = '$email'";
    //         $result = mysqli_query($connection, $strSql);
    //         if ($result) {
    //             if (mysqli_num_rows($result) > 0) {
    //                 $reqPersons = mysqli_fetch_array($result);
    //                 mysqli_free_result($result);
    //             }
    //         }
           
    //     } else {
    //         throw new Exception('No event ID or email');
    //     }
    // } catch (Exception $e) {
    //     echo 'Error: ' . $e->getMessage();
    // }

    function getAttendance($connection, $email, $event_id){
        $sessionsInfo = array();
        $sqlSelectAttendance = "SELECT * FROM attendance as a join event_sessions as es on a.session_title = es.session_title  where a.event_id = '$event_id' and a.email = '$email'";
        $result = mysqli_query($connection, $sqlSelectAttendance);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $sessionsInfo[] = $row;
            }
        }
        return $sessionsInfo;
    }
    function getTechnologiesLine($connection, $session_title){
        $technologiesArray = array();
        $sqlTechnologies = "SELECT * FROM event_sessions where session_title = '$session_title'";
        $result = mysqli_query($connection, $sqlTechnologies);
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                $technologiesArray[] = $row;
            }
        }
        return $technologiesArray;
    }
?>
    <?php include 'link.php'; ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<style>
    /* Define the hidden class to hide elements */
    .hidden {
        display: none;
    }
    .bg-primary {
    background-color: #C9C9C8!important;
    }
    /* Center the content both horizontally and vertically */
    .vh-100 {
      min-height: 100vh;
    }

/* Add this CSS to your stylesheet or within a <style> tag in your HTML */


</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var checkboxes = document.querySelectorAll('.session-checkbox');

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                var sessionId = this.getAttribute('data-session');
                var techCheckboxes = document.querySelectorAll('.tech-checkboxes[data-session="' + sessionId + '"] input[type="checkbox"]');
                var techLabels = document.querySelectorAll('.tech-checkboxes[data-session="' + sessionId + '"] label');

                techCheckboxes.forEach(function (techCheckbox, index) {
                    // Toggle the hidden class to show/hide the checkboxes
                    techCheckbox.classList.toggle("hidden", !this.checked);
                    // Enable/disable the checkboxes based on the session checkbox
                    techCheckbox.disabled = !this.checked;

                    // Toggle the hidden class for the associated labels
                    techLabels[index].classList.toggle("hidden", !this.checked);

                    // Log the sessionId and checkbox visibility
                    console.log("Session ID:", sessionId);
                    console.log("Checkbox Hidden:", techCheckbox.classList.contains("hidden"));
                    // Remove the "hidden" class from the .tech-checkboxes div when the session checkbox is checked
                    var techCheckboxesDiv = document.querySelector('.tech-checkboxes[data-session="' + sessionId + '"]');
                    if (this.checked) {
                        techCheckboxesDiv.classList.remove("hidden");
                    }
                }.bind(this));
            });
        });
    });
</script>


<!-- Your HTML code remains unchanged -->

<body id="page-top">

    <!-- Page Wrapper -->
    <div>

        <?php //include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div class="container mt-5 col-sm-12">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <?php
                        $connection = openConnection();
                        $attendance = getAttendance($connection, $email, $event_id);
                        if (!empty($attendance)){ ?>
                            <form method="post">
                                <div class="row card">
                                    <label for="card-body-survey">Which of the sessions did you enjoy? Why?</label>
                                    <div class="card-body" id="card-body-survey">
                                        <?php
                                        $printedSessions = []; // Create an array to track printed session titles

                                        foreach ($attendance as $value) {
                                            $session_title = $value['session_title'];

                                            // Check if the session title has already been printed
                                            if (!in_array($session_title, $printedSessions)) {
                                                // Mark the session title as printed
                                                $printedSessions[] = $session_title;
                                                ?>
                                                <label>
                                                    <input type="checkbox" class="session-checkbox" data-session="<?php echo $session_title; ?>" name="session[]" value="<?php echo $session_title; ?>"><?php echo $session_title; ?>
                                                </label>
                                                <div class="tech-checkboxes hidden" data-session="<?php echo $session_title; ?>">
                                                    <?php
                                                    $technologies = getTechnologiesLine($connection, $session_title);
                                                    foreach ($technologies as $checkboxValue) {
                                                    ?>

                                                    <label for="technology_line[]">
                                                        <input type="checkbox" name="technology_line[]" id="technology_<?php echo $checkboxValue['technology_line']; ?>" value="<?php echo $checkboxValue['technology_line']; ?>">
                                                        <?php echo $checkboxValue['technology_line']; ?>
                                                    </label>
                                                    <?php
                                                    }
                                                    ?>
                                                </div><br>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment">Comment:</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="suggestion">Suggestion:</label>
                                    <textarea class="form-control" id="suggestion" name="suggestion" rows="4" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="S_event">Would you go to a similar event in the future:</label>
                                    <input class="form-control" id="S_event" name="S_event" required></input>
                                </div>
                                <button type="submit" class="btn btn-primary" name="btnSubmit">Submit</button>
                            </form>
                        <?php
                        }
                        else{
                            echo '
                                <div class="col-md-12 d-flex justify-content-center align-items-center vh-100">
                                    <div class="card bg-primary">
                                        <div class="card-body text-center">
                                        <h5>No Session attended</h5>
                                        </div>
                                    </div>
                                </div>
                                ';
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php include'script.php'; ?>
</body>

<?php
if (isset($_POST['btnSubmit'])) {
    $connection = openConnection();
    $comment = $_POST['comment'];
    $suggestion = $_POST['suggestion'];
    $S_event = $_POST['S_event'];
    $sqlInsertComment = "INSERT INTO comment (comment, suggestion, similar_event, event_id, email) VALUES ('$comment', '$suggestion', '$S_event', '$event_id', '$email')";
    
    $dataset = array();
    $selected_data = [];
    if (mysqli_query($connection, $sqlInsertComment)) {
        $comment_id = mysqli_insert_id($connection);
        if (isset($_POST['technology_line']) && is_array($_POST['technology_line'])) {
            foreach ($_POST['technology_line'] as $technology_line) {
                $sqlInsertSurvey = "INSERT INTO survey (comment_id, technology_line) VALUES('$comment_id', '$technology_line')";
                if (mysqli_query($connection, $sqlInsertSurvey)) {
                    $selected_data[] = $technology_line;
                }
            }
        }
    }
    $strSqlGetTechnologyLine = "SELECT * FROM response where event_id = '$event_id' AND email = '$email'";
    // Execute the SQL query and fetch the data
    $result = mysqli_query($connection, $strSqlGetTechnologyLine);
    // Fetch rows and add them to the dataset array
    while ($row = mysqli_fetch_assoc($result)) {
        $selected_data[] = $row['response'];
    }
    // Retrieve the dataset
    $datasetSql = "SELECT * from event_sessions where event_id = '$event_id'";
    
    // Execute the SQL query and fetch the data
    $resultDataset = mysqli_query($connection, $datasetSql);
    
    // Initialize an array to store dataset entries
    $dataset = array();

    // Fetch rows and add them to the dataset array
    while ($row = mysqli_fetch_assoc($resultDataset)) {
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
    $command = "python new-product.py $fileArg";
    exec($command, $output, $returnCode);

    // Remove the temporary file
    unlink($tempFile);
    if ($returnCode === 0) {
        $emailContent .= "<h3>Product Recommended base on your preferences</h3>";
        $emailContent .= "<h4>Product List</h4>";
        foreach ($output as $line) {
            // Parse the JSON data sent by the Python script
            $json_data = json_decode($line, true);
            if ($json_data) {
                foreach ($json_data as $result) {
                     $product_name = $result['product_name'];
                     $emailContent .= "<br>". $product_name;
                     
                }
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
            $mail->Subject = "Product Recommendation";

            // Embed the QR code image in the email body
            $mail->Body = $emailContent;

            // Send the email
            if ($mail->send()) {
                echo '<script type="text/javascript">
                    swal({
                        title: "Success",
                        text: "Redirecting in 2 seconds.\nSuccessfully Answered survey",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = "./post-survey.php";
                    });
                </script>';
            } else {
                echo "Email sending failed: " . $mail->ErrorInfo;
            }
    }
    else {
        // There was an error executing the Python script
        echo "Error executing Python script. Return code: $returnCode";
    }
}

?>