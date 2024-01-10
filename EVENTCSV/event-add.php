<?php
    session_start();
    include 'connection.php';
    $user_id = $_SESSION['user_id'];
    include 'phpqrcode\phpqrcode\qrlib.php';
    require 'vendor/autoload.php'; // Include Composer's autoloader
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Add Event</title>


    <?php include'link.php'; ?>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fw fa-calendar"></i> Add Event</h6>
                        </div>
                        <div class="card-body">
                            <form id="eventForm" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="eventTitle">Event Title:</label>
                                    <input type="text" id="eventTitle" name="eventTitle" class="form-control" required>
                                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="csvFile">Select a CSV file for participant:</label>
                                    <input type="file" class="form-control-file" name="csvFile" id="csvFile" accept=".csv" required>
                                </div>
                                <div class="form-group">
                                    <label for="csvSession">Select a CSV file for session:</label>
                                    <input type="file" class="form-control-file" name="csvSession" id="csvSession" accept=".csv" required>
                                </div>
                                <!-- Submit Button -->
                                <div class="text-center mt-3">
                                    <button type="submit" form="eventForm" name="btnSubmit" class="btn btn-success">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class=" bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright Demand &copy; Generation</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

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
</html>

<?php
    if (isset($_POST['btnSubmit'])) {
        $connection = openConnection();
        $user_id = $_POST['user_id'];   
        $eventTitle = $_POST['eventTitle'];
        // Handle the uploaded CSV file for csvSession
        $eventInsertSql = "INSERT INTO events (event_title, user_id, event_status) VALUES ('$eventTitle', '$user_id', 1)";
        if (mysqli_query($connection, $eventInsertSql)) {
            $event_id = mysqli_insert_id($connection);
            if (isset($_FILES["csvSession"])) {
                $csvSessionFile = $_FILES["csvSession"]["tmp_name"];

                if (($handle = fopen($csvSessionFile, "r")) !== FALSE) {
                    // Skip the first row (header)
                    fgetcsv($handle);

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // Check if the data row is empty, and skip it if it is
                        if (array_filter($data) == []) {
                            continue; // Skip empty rows
                        }

                        // Assuming the CSV file has at least 9 columns
                        $session_title = $data[0]; 
                        $technology = $data[1]; 
                        $technology_line = $data[2]; 
                        $product_name = $data[3];
                        $speaker = $data[4];
                        $speaker_special = $data[5];
                        $date = $data[6];
                        $timeam = $data[7];
                        $timepm = $data[8];

                        $sqlSession = "INSERT INTO event_sessions (event_id, session_title, technology, technology_line, product_name, speaker, speaker_special, date, timeam, timepm) VALUES ('$event_id','$session_title', '$technology', '$technology_line', '$product_name', '$speaker', '$speaker_special', '$date', '$timeam', '$timepm')";
                        mysqli_query($connection, $sqlSession);
                    }
                    fclose($handle);
                } else {
                    echo "Failed to open the CSV file.";
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
                
                        <a href="http://localhost/EVENTCSV/event-form.php?eventID='.$event_id.'&email='.$value.'">Click here to access the event</a>
                
                        This is your opportunity to connect, learn, and enjoy. Don\'t miss out on the chance to be part of something amazing. We look forward to seeing you there!
                
                        If you have any questions or need assistance, please feel free to contact us at email@gmail.com or 09123456789.
                
                        Thank you for being a part of this incredible journey. We can\'t wait to celebrate with you!
                        ';
                
                        $mail->addAddress($value); // Recipient's email address
                        $mail->isHTML(true);
                        $mail->Subject = "Join Us for an Exciting Event!";
                        $mail->Body = $emailContent;
                
                        if ($mail->send()) {
                            $sqlInsertParticipants = "INSERT INTO participants (event_id, email, status) VALUES('$event_id', '$value', 0)";
                            mysqli_query($connection, $sqlInsertParticipants);
                        } else {
                           echo "failed to send email to " . $value;
                        }
                    }
                
                } else {
                    echo "Error uploading the file.";
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
        }
        else{
            echo '<script type="text/javascript">
                    swal({
                        title: "Warning",
                        text: "Redirecting in 2 seconds.\Failed to add event",
                        icon: "warning",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        
                    });
                </script>';
        }
    }
?>  