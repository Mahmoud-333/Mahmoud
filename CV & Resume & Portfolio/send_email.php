<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token (if you implement CSRF protection)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     http_response_code(403);
    //     echo "Invalid CSRF token.";
    //     exit;
    // }

    // Get form data and sanitize
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);
    $honeypot = isset($_POST["website"]) ? $_POST["website"] : ''; // Spam trap

    // Check for empty required fields
    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo "Please fill out all required fields.";
        exit;
    }

    // Check honeypot field (should be empty)
    if (!empty($honeypot)) {
        // Log potential spam attempt
        error_log("Potential spam submission from $email");
        http_response_code(200); // Pretend it worked to spammers
        echo "Thank you! Your message has been sent.";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid email format.";
        exit;
    }

    // Set recipient email (change to your email)
    $recipient = "7odaaashour@gmail.com";
    
    // Set email subject
    $email_subject = empty($subject) ? "New contact from $name" : "Portfolio Contact: $subject";

    // Build email content
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Build email headers
    $email_headers = "From: $name <$email>\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion();

    // Use PHPMailer for more reliable delivery (recommended)
    if (file_exists('PHPMailer/PHPMailerAutoload.php')) {
        require 'PHPMailer/PHPMailerAutoload.php';
        
        $mail = new PHPMailer;
        
        // SMTP Configuration (recommended)
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-smtp-username';
        $mail->Password = 'your-smtp-password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        // Email content
        $mail->setFrom($email, $name);
        $mail->addAddress($recipient);
        $mail->Subject = $email_subject;
        $mail->Body = $email_content;
        
        if ($mail->send()) {
            http_response_code(200);
            echo "Thank you! Your message has been sent.";
        } else {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            http_response_code(500);
            echo "Oops! Something went wrong. Please try again later.";
        }
    } else {
        // Fall back to PHP mail() function if PHPMailer isn't available
        if (mail($recipient, $email_subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "Thank you! Your message has been sent.";
        } else {
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message.";
        }
    }
} else {
    // Not a POST request
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}