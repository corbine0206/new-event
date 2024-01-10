<?php
include 'connection.php';
include'script.php';
$connection = openConnection();
// Fetch records from the database
$event_id = $_GET['eventID'];
$strSql = "SELECT * FROM attendance where event_id = '$event_id'";
$result = getRecord($connection, $strSql);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60); // Increase the time limit to 60 seconds (adjust as needed)

// Set the HELO name
$from = "event@laundryandwash.com"; // Use a valid email address from your domain

// SMTP authentication credentials
$username = "event@laundryandwash.com"; // Use your actual email address from cPanel
$password = "GhZ%3SiW]x=Z"; // Use your cPanel password

// Use PHPMailer
require 'vendor/autoload.php'; // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'mail.laundryandwash.com';
$mail->Port = 587; // Use the correct SMTP port for TLS
$mail->SMTPSecure = 'tls'; // Use 'tls' for TLS encryption
$mail->SMTPAuth = true;
$mail->Username = $username;
$mail->Password = $password;

try {
    foreach ($result as $row) {
        // Email parameters for each record
        $to = $row['email'];
        $event_id = $row['event_id'];
        $subject = "POST SURVEY EVENT";
        $message = '<a href="http://localhost/EVENTCSV/post-survey.php?eventID=' . $event_id . '&email=' . $to . '">click to access post survey event</a>';
        
        $mail->setFrom($from);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->msgHTML($message);
        
        $mail->send();
    }
} catch (Exception $e) {
    echo "Email sending failed. Error: " . $mail->ErrorInfo;
}
echo '<script type="text/javascript">
        swal({
            title: "Success",
            text: "Redirecting in 2 seconds.\nSuccessfully Send an email",
            icon: "success",
            timer: 2000,
            showConfirmButton: false
        }).then(function() {
            window.location.href = "./event.php";
        });
    </script>';
?>
