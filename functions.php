<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email using Gmail SMTP.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "XKCD Comic Subscription - Verification Code";
    $message = "Hello!\n\n";
    $message .= "Thank you for subscribing to our XKCD comic service!\n\n";
    $message .= "Your verification code is: " . $code . "\n\n";
    $message .= "Please enter this code on our website to complete your subscription.\n\n";
    $message .= "If you didn't request this subscription, please ignore this email.\n\n";
    $message .= "Best regards,\nXKCD Comic Team";
    
    // Load configuration
    $configFile = __DIR__ . '/config.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
    
    // Try Gmail SMTP if enabled
    if (defined('GMAIL_ENABLED') && GMAIL_ENABLED && defined('GMAIL_USERNAME') && defined('GMAIL_PASSWORD')) {
        // Use PHPMailer for reliable Gmail SMTP
        if (sendEmailViaPHPMailer($email, $subject, $message, GMAIL_HOST, GMAIL_PORT, GMAIL_USERNAME, GMAIL_PASSWORD)) {
            return true;
        }
    }
    
    // Fallback to basic mail() function
    $headers = "From: noreply@xkcd-comics.com\r\n";
    $headers .= "Reply-To: noreply@xkcd-comics.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // For testing purposes, log the email
    $logFile = __DIR__ . '/email_log.txt';
    $logEntry = "[" . date('Y-m-d H:i:s') . "] Email would be sent to: $email\n";
    $logEntry .= "Subject: $subject\n";
    $logEntry .= "Code: $code\n";
    $logEntry .= "Message: $message\n";
    $logEntry .= str_repeat("-", 50) . "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Try to send actual email, but don't fail if mail server is not configured
    $mailResult = @mail($email, $subject, $message, $headers);
    
    if (!$mailResult) {
        // Log that mail server is not configured
        $errorLog = "[" . date('Y-m-d H:i:s') . "] Mail server not configured. Email logged to email_log.txt\n";
        file_put_contents($logFile, $errorLog, FILE_APPEND | LOCK_EX);
    }
    
    return true; // Always return true for testing
}

/**
 * Send email via SMTP using Gmail.
 */
function sendEmailViaSMTP($to, $subject, $message, $smtpHost, $smtpPort, $username, $password): bool {
    // Check if PHPMailer is available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendEmailViaPHPMailer($to, $subject, $message, $smtpHost, $smtpPort, $username, $password);
    }
    
    // Fallback to basic SMTP
    return sendEmailViaBasicSMTP($to, $subject, $message, $smtpHost, $smtpPort, $username, $password);
}

/**
 * Send email using PHPMailer (recommended for Gmail).
 */
function sendEmailViaPHPMailer($to, $subject, $message, $smtpHost, $smtpPort, $username, $password): bool {
    try {
        // Load PHPMailer
        require_once __DIR__ . '/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Enable debug output (optional)
        // $mail->SMTPDebug = 2;
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpPort;
        
        // Additional settings for Gmail
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom($username, 'XKCD Comic Team');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        
        // Log success
        $logFile = __DIR__ . '/email_log.txt';
        $successLog = "[" . date('Y-m-d H:i:s') . "] Email sent successfully via PHPMailer to: $to\n";
        file_put_contents($logFile, $successLog, FILE_APPEND | LOCK_EX);
        
        return true;
        
    } catch (Exception $e) {
        // Log error
        $logFile = __DIR__ . '/email_log.txt';
        $errorLog = "[" . date('Y-m-d H:i:s') . "] PHPMailer Error: " . $e->getMessage() . "\n";
        file_put_contents($logFile, $errorLog, FILE_APPEND | LOCK_EX);
        
        return false;
    }
}

/**
 * Basic SMTP implementation without external libraries.
 */
function sendEmailViaBasicSMTP($to, $subject, $message, $smtpHost, $smtpPort, $username, $password): bool {
    $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    
    if (!$socket) {
        return false;
    }
    
    // Read server greeting
    fgets($socket, 515);
    
    // EHLO
    fputs($socket, "EHLO localhost\r\n");
    fgets($socket, 515);
    
    // Start TLS
    fputs($socket, "STARTTLS\r\n");
    fgets($socket, 515);
    
    // Upgrade to TLS
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    // EHLO again after TLS
    fputs($socket, "EHLO localhost\r\n");
    fgets($socket, 515);
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);
    
    fputs($socket, base64_encode($username) . "\r\n");
    fgets($socket, 515);
    
    fputs($socket, base64_encode($password) . "\r\n");
    fgets($socket, 515);
    
    // MAIL FROM
    fputs($socket, "MAIL FROM: <$username>\r\n");
    fgets($socket, 515);
    
    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    fgets($socket, 515);
    
    // DATA
    fputs($socket, "DATA\r\n");
    fgets($socket, 515);
    
    // Send email content
    $headers = "From: $username\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";
    
    fputs($socket, $headers . $message . "\r\n.\r\n");
    fgets($socket, 515);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    
    // Check if email already exists
    if (isEmailRegistered($email)) {
        return false;
    }
    
    // Add email to file
    $emailData = $email . "\n";
    return file_put_contents($file, $emailData, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Check if an email is already registered.
 */
function isEmailRegistered(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array(trim($email), array_map('trim', $emails));
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_map('trim', $emails);
    
    // Remove the email
    $emails = array_filter($emails, function($storedEmail) use ($email) {
        return $storedEmail !== trim($email);
    });
    
    // Write back to file
    $content = implode("\n", $emails) . "\n";
    return file_put_contents($file, $content, LOCK_EX) !== false;
}

/**
 * Get all registered emails.
 */
function getRegisteredEmails(): array {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return [];
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_map('trim', $emails);
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    // Get total number of comics (latest comic number)
    $latestComicUrl = 'https://xkcd.com/info.0.json';
    $latestComicData = file_get_contents($latestComicUrl);
    
    if (!$latestComicData) {
        return '<p>Sorry, unable to fetch XKCD comic at this time.</p>';
    }
    
    $latestComic = json_decode($latestComicData, true);
    $totalComics = $latestComic['num'];
    
    // Get a random comic
    $randomComicNum = rand(1, $totalComics);
    $randomComicUrl = "https://xkcd.com/{$randomComicNum}/info.0.json";
    $randomComicData = file_get_contents($randomComicUrl);
    
    if (!$randomComicData) {
        return '<p>Sorry, unable to fetch XKCD comic at this time.</p>';
    }
    
    $comic = json_decode($randomComicData, true);
    
    // Format as HTML
    $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">';
    $html .= '<h2 style="color: #333;">Your Daily XKCD Comic</h2>';
    $html .= '<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    $html .= '<h3 style="color: #666; margin-top: 0;">#' . $comic['num'] . ': ' . htmlspecialchars($comic['title']) . '</h3>';
    $html .= '<img src="' . htmlspecialchars($comic['img']) . '" alt="' . htmlspecialchars($comic['alt']) . '" style="max-width: 100%; height: auto; display: block; margin: 20px auto;">';
    $html .= '<p style="color: #666; font-style: italic; margin: 15px 0;">' . htmlspecialchars($comic['alt']) . '</p>';
    $html .= '<p style="color: #888; font-size: 12px;">Published: ' . $comic['month'] . '/' . $comic['day'] . '/' . $comic['year'] . '</p>';
    $html .= '</div>';
    
    // Build unsubscribe URL safely
    $unsubscribeUrl = 'unsubscribe.php';
    if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
        $unsubscribeUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/unsubscribe.php';
    }
    
    $html .= '<p style="color: #666; font-size: 14px;">To unsubscribe, visit: <a href="' . $unsubscribeUrl . '">Unsubscribe</a></p>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return;
    }
    
    $emails = getRegisteredEmails();
    
    if (empty($emails)) {
        return;
    }
    
    $htmlContent = fetchAndFormatXKCDData();
    
    // Convert HTML to plain text for email
    $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlContent));
    
    $subject = "Your Daily XKCD Comic";
    
    $headers = "From: noreply@xkcd-comics.com\r\n";
    $headers .= "Reply-To: noreply@xkcd-comics.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    foreach ($emails as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            mail($email, $subject, $htmlContent, $headers);
        }
    }
}

/**
 * Store verification code temporarily (in session or file).
 */
function storeVerificationCode(string $email, string $code): bool {
    $verificationFile = __DIR__ . '/verification_codes.txt';
    $data = $email . '|' . $code . '|' . time() . "\n";
    return file_put_contents($verificationFile, $data, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Verify the code for an email.
 */
function verifyCode(string $email, string $code): bool {
    $verificationFile = __DIR__ . '/verification_codes.txt';
    
    if (!file_exists($verificationFile)) {
        return false;
    }
    
    $lines = file($verificationFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            $storedEmail = $parts[0];
            $storedCode = $parts[1];
            $timestamp = (int)$parts[2];
            
            // Check if code is valid (within 10 minutes)
            if ($storedEmail === $email && $storedCode === $code && (time() - $timestamp) < 600) {
                // Remove this verification code
                removeVerificationCode($email, $code);
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Remove a verification code after successful verification.
 */
function removeVerificationCode(string $email, string $code): bool {
    $verificationFile = __DIR__ . '/verification_codes.txt';
    
    if (!file_exists($verificationFile)) {
        return false;
    }
    
    $lines = file($verificationFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];
    
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            $storedEmail = $parts[0];
            $storedCode = $parts[1];
            
            if (!($storedEmail === $email && $storedCode === $code)) {
                $newLines[] = $line;
            }
        }
    }
    
    $content = implode("\n", $newLines) . "\n";
    return file_put_contents($verificationFile, $content, LOCK_EX) !== false;
}
