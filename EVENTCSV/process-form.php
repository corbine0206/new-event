<?php
include 'connection.php';
require_once 'phpqrcode\phpqrcode\qrlib.php';
if (isset($_POST['btnSubmit'])) {

    // Get selected technology and product choices from the form
    $event_id = $_POST['event_id'];
    $email = $_POST['email'];
    $outputFileName = "$email.png";
    $textToEncode = $email;
    // Generate the QR code
    QRcode::png($textToEncode, $outputFileName, QR_ECLEVEL_L, 3);
    echo "<img src='$outputFileName' alt='QR Code'>";
    $email_subject = "Recommended Sessions";
    $email_message = "<html><body> <h2>Recommended Sessions</h2> <img src='laundryandwash.com/'.$email.'.png'> </body></html>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers = "From: webmaster@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $to = $email;
    mail($to, $email_subject, $email_message, $headers);
}
?>
