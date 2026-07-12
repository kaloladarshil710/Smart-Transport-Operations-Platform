<?php
/**
 * Mail delivery wrapper. Uses an installed PHPMailer package when available and
 * falls back to PHP's configured mail transport in a standard XAMPP install.
 */
declare(strict_types=1);

function sendMail(string $to, string $subject, string $body): bool
{
    $autoload = ROOT_PATH . '/vendor/PHPMailer/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isMail(); $mail->setFrom(getenv('MAIL_FROM') ?: 'no-reply@transitops.local', APP_NAME);
            $mail->addAddress($to); $mail->Subject = $subject; $mail->isHTML(true); $mail->Body = $body;
            return $mail->send();
        } catch (Throwable $exception) { error_log('[TransitOps mail] ' . $exception->getMessage()); return false; }
    }
    return mail($to, $subject, $body, "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8");
}
