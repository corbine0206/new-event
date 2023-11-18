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
<script src="https://cdn.tailwindcss.com"></script>
<script src="http://localhost/js/tailwind.config.js"></script>


<!-- Your HTML code remains unchanged -->
<!-- https://wp.technologyreview.com/wp-content/uploads/2019/04/mit-final-gif-tsjisse-talsma-10.gif-->
<body id="page-top">
    <section class="bg-white dark:bg-gray-900 p-10 flex justify-center ...  bg-cover" style="background-image: url('https://media-s3-us-east-1.ceros.com/g3-communications/images/2021/04/21/bf088fa43296be6d4cee5685a37e6a30/untitled.gif');">      
        <?php
            $connection = openConnection();
            $attendance = getAttendance($connection, $email, $event_id);
            if (!empty($attendance)){ ?>
                <div class="w-full max-w-5xl p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-center text-gray-900 dark:text-white">Thank you for attending the event!!</h2>
                    <p class="mb-8 lg:mb-16 font-light text-center text-gray-500 dark:text-gray-400 sm:text-xl">Hope you had a great time, let us know what you think about the event. Cheers!!!</p>
                    <form method="post">
                        <div class="form-group">
                            <label for="card-body-survey" class="block mb-2 text-m font-bold text-gray-900 dark:text-gray-400">Which of the sessions you've attended did you enjoy the most?</label>
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
                                        <label class="mb-2 flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                            <input type="checkbox" class="session-checkbox mx-1" data-session="<?php echo $session_title; ?>" name="session[]" value="<?php echo $session_title; ?>"><?php echo $session_title; ?>
                                        </label>
                                        <div class="tech-checkboxes hidden" data-session="<?php echo $session_title; ?>">
                                            <div class="w-full max-w mb-2 p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                                                <p class="text-m font-normal text-gray-900 dark:text-gray-400">What topics from this session do you thinks would be useful to you?</p>
                                            
                                                    <div class="columns-2 p-2">
                                                    <?php
                                                    $technologies = getTechnologiesLine($connection, $session_title);
                                                    foreach ($technologies as $checkboxValue) {
                                                    ?>
                                                    <div class="w-full flex mb-1 items-center ps-4 border border-gray-200 rounded dark:border-gray-700">
                                                        <label for="technology_line[]" class="w-full py-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" type="checkbox"  name="technology_line[]" id="technology_<?php echo $checkboxValue['technology_line']; ?>" value="<?php echo $checkboxValue['technology_line']; ?>">
                                                            <?php echo $checkboxValue['technology_line']; ?>
                                                        </label>
                                                    </div>
                                                    <?php  } ?>
                                                </div>

                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment" class="block mb-2 text-m font-bold text-gray-900 dark:text-gray-400">Comment:</label>
                            <textarea placeholder="Share us your thoughts..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg shadow-sm border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="comment" name="comment" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="suggestion" class="block mb-2 text-m font-bold text-gray-900 dark:text-gray-400">Suggestion:</label>
                            <textarea placeholder="Let us know what you think..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg shadow-sm border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="suggestion" name="suggestion" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="message" class="block mb-2 text-m font-bold text-gray-900 dark:text-gray-400">Your message</label>
                            <input class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg shadow-sm border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" id="S_event" name="S_event" required></input>
                        </div>
                        <button type="submit" class="py-3 px-5 text-m font-bold text-center text-white rounded-lg bg-primary-700 sm:w-fit hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800" name="btnSubmit">Submit</button>
                    </form>
                </div>
        
            <?php
            }
            else{
                echo '
                    <div class="col-md-12 d-flex justify-content-center align-items-center vh-100">
                        <div class="card bg-primary">
                            <div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                                <h5 class="mb-3 text-base font-semibold text-gray-900 md:text-l dark:text-white">Sorry, you did not attend this event!</h5>
                                <p class="mb-3 text-sm font-normal text-gray-500 dark:text-gray-400">Please check your email again or contact the event organizer for more details. Thank you!</p>
                                <img class="h-auto max-w-full mx-auto" src="https://media4.giphy.com/media/Bc4oup2pdP5iKFAYiF/200w.gif?cid=6c09b9520ewqcrypuassw0qj1nck4jcukefjjr5322adfum0&ep=v1_gifs_search&rid=200w.gif&ct=g" alt="image description">
                            </div>
                        </div>
                    </div>
                    ';
            }
        ?>
        <!-- Page Wrapper -->
        <!-- End of Page Wrapper -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
        <?php include'script.php'; ?>
    </section>
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