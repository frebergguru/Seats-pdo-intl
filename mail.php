<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = 'smtp.protonmail.ch';
    $mail->SMTPAuth = true;
    $mail->Username = 'morten@freberg.guru';
    $mail->Password = 'DTL7URVXHDRW74KN';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('morten@freberg.guru', 'Your Name');
    $mail->addAddress('canislupusfamiliaris@gmail.com');
    $mail->Subject = 'Test email';
    $mail->Body = 'This is a test email';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

