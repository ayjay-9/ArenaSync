<?php
require_once '../db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

define('ADMIN_EMAIL',      'ayobanjoemmanuel1@gmail.com'); // support tickets go HERE
define('MAIL_FROM_EMAIL',  'no-reply@arenasync.com');      // displayed as sender
define('MAIL_FROM_NAME',   'ArenaSync');
define('GMAIL_USER',       'ayobanjoemmanuel1@gmail.com');
define('GMAIL_APP_PASS',   $GMAIL_APP_PASS);               // set in db_config.php

function send_email(string $to, string $subject, string $html_body, string $reply_to = ''): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USER;
        $mail->Password   = GMAIL_APP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->Sender = GMAIL_USER;                               // SMTP envelope (must match auth)
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);          // display sender
        $mail->addAddress($to);
        if ($reply_to) {
            $mail->addReplyTo($reply_to);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("ArenaSync mailer error to {$to}: " . $mail->ErrorInfo);
        return false;
    }
}

function send_welcome_email(string $to, string $name, string $role): bool {
    $label = ($role === 'organiser') ? 'Organizer' : 'Attendee';
    $body  = "
        <h2>Welcome to ArenaSync, {$name}!</h2>
        <p>Your {$label} account has been created successfully.</p>
        <p>You can now log in and enjoy everything ArenaSync has to offer.</p>
        <p>See you in the arena!</p>
        <p>-- The ArenaSync Team</p>
    ";
    return send_email($to, 'Welcome to ArenaSync!', $body);
}

function send_login_notification(string $to, string $name): bool {
    $time = date('F j, Y \a\t g:i A');
    $body = "
        <h2>Hello, {$name}!</h2>
        <p>A new login was recorded on your ArenaSync account on <strong>{$time}</strong>.</p>
        <p>If this wasn't you, please contact support immediately.</p>
        <p>-- The ArenaSync Team</p>
    ";
    return send_email($to, 'ArenaSync - New Login Detected', $body);
}

function send_booking_confirmation(string $to, string $first_name, string $game_name, string $date_time, string $organizer): bool {
    $body = "
        <h2>You're in, {$first_name}!</h2>
        <p>Your booking for <strong>{$game_name}</strong> has been confirmed.</p>
        <p><strong>Date &amp; Time:</strong> {$date_time}</p>
        <p><strong>Organizer:</strong> {$organizer}</p>
        <p>We look forward to seeing you there!</p>
        <p>-- The ArenaSync Team</p>
    ";
    return send_email($to, "Booking Confirmed - {$game_name}", $body);
}

function send_event_created_email(string $to, string $company, string $game_name, string $date_time): bool {
    $body = "
        <h2>Your event is live!</h2>
        <p>Hi <strong>{$company}</strong>, your event has been published on ArenaSync.</p>
        <p><strong>Game:</strong> {$game_name}</p>
        <p><strong>Date &amp; Time:</strong> {$date_time}</p>
        <p>Good luck with your event!</p>
        <p>-- The ArenaSync Team</p>
    ";
    return send_email($to, "Event Published - {$game_name}", $body);
}

function send_support_email(string $from_name, string $from_email, string $ticket, string $message): bool {
    $safe_message = nl2br(htmlspecialchars($message));
    $body = "
        <h2>New Support Request</h2>
        <p><strong>From:</strong> {$from_name} ({$from_email})</p>
        <p><strong>Ticket Type:</strong> {$ticket}</p>
        <p><strong>Message:</strong><br>{$safe_message}</p>
    ";
    return send_email(ADMIN_EMAIL, "Support [{$ticket}] from {$from_name}", $body, $from_email);
}
