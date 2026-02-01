<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../envreader.php';
loadEnv();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "contact.php");
    exit();
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$title   = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '' || mb_strlen($name) < 2) $errors[] = "A név túl rövid.";
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adj meg érvényes email címet.";
if ($title === '' || mb_strlen($title) < 3) $errors[] = "A tárgy túl rövid.";
if (mb_strlen($title) > 120) $errors[] = "A tárgy túl hosszú.";
if ($message === '' || mb_strlen($message) < 10) $errors[] = "Az üzenet túl rövid.";

if (!empty($errors)) {
    header("Location: " . BASE_URL . "contact.php?status=error&msg=" . urlencode(implode(" ", $errors)));
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_EMAIL');
    $mail->Password = getenv('SMTP_EMAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('SMTP_PORT');
    $mail->CharSet = 'UTF-8';

    // Küldő (az SMTP fiók)
    $mail->setFrom(getenv('SMTP_EMAIL'), 'Techoázis Support');

    // Címzett (support)
    $mail->addAddress(getenv('SMTP_EMAIL'), 'Techoázis Support');

    // Reply-To legyen a felhasználó emailje
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);

    // SUBJECT: legyen benne a title
    $mail->Subject = $title;

    $safeName  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMsg   = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    $mail->Body = "
        <h2>Új support üzenet</h2>
        <p><strong>Név:</strong> {$safeName}</p>
        <p><strong>Email:</strong> {$safeEmail}</p>
        <p><strong>Tárgy:</strong> {$safeTitle}</p>
        <hr>
        <p><strong>Üzenet:</strong><br>{$safeMsg}</p>
    ";

    $mail->AltBody =
        "Név: {$name}\n" .
        "Email: {$email}\n" .
        "Tárgy: {$title}\n\n" .
        "Üzenet:\n{$message}\n";

    $mail->send();

    header("Location: " . BASE_URL . "contact.php?status=success");
    exit();

} catch (Exception $e) {
    // Ne szivárogjon ki túl sok infó, de fejlesztés alatt oké lehet
    $err = $mail->ErrorInfo ?: $e->getMessage();
    header("Location: " . BASE_URL . "contact.php?status=error&msg=" . urlencode("Küldési hiba: " . $err));
    exit();
}
