<?php
// 1. Config behívása a konstansok miatt
require_once __DIR__ . '/../config.php';
// 2. ROOT_PATH használata a biztos eléréshez
require_once ROOT_PATH . '/envreader.php';
loadEnv();

// Ellenőrizzük a metódust
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // JAVÍTÁS: Szép URL (.php nélkül)
    header("Location: " . BASE_URL . "/contact");
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
    // JAVÍTÁS: Szép URL (.php nélkül)
    header("Location: " . BASE_URL . "/contact?status=error&msg=" . urlencode(implode(" ", $errors)));
    exit();
}

// 3. Vendor behívása ROOT_PATH-al
require_once ROOT_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // ... (SMTP beállítások változatlanok) ...
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_SUP_EMAIL');
    $mail->Password = getenv('SMTP_SUP_EMAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('SMTP_PORT');
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(getenv('SMTP_SUP_EMAIL'), 'Techoázis Támogatás');
    $mail->addAddress(getenv('SMTP_SUP_EMAIL'), 'Techoázis Támogatás');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
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

    $mail->AltBody = "Név: {$name}\nEmail: {$email}\nTárgy: {$title}\n\nÜzenet:\n{$message}\n";

    $mail->send();

    // JAVÍTÁS: Szép URL (.php nélkül)
    header("Location: " . BASE_URL . "/contact?status=success");
    exit();

} catch (Exception $e) {
    $err = $mail->ErrorInfo ?: $e->getMessage();
    // JAVÍTÁS: Szép URL (.php nélkül)
    header("Location: " . BASE_URL . "/contact?status=error&msg=" . urlencode("Küldési hiba: " . $err));
    exit();
}