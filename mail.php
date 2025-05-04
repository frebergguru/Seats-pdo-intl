<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    //Replace with your actual SMTP server
    $mail->Host = 'smtp.protonmail.ch';
    $mail->SMTPAuth = true;
    //Replace with your actual email address
    $mail->Username = 'morten@freberg.guru';
    //This is just a example password, replace it with your actual password.
    $mail->Password = 'DTL7URVXHDRW74KN';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    //Replace with your actual email address and name
    //This is the email address that will appear in the "From" field
    $mail->setFrom('morten@freberg.guru', 'Your Name');
    //This is the email address that you want to send the email to
    $mail->addAddress('canislupusfamiliaris@gmail.com');
    $mail->Subject = 'Test email';
    $mail->Body = 'This is a test email';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

