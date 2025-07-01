<?php
require_once 'functions.php';

session_start();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'request_unsubscribe') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Please enter a valid email address.';
                $messageType = 'error';
            } elseif (!isEmailRegistered($email)) {
                $message = 'This email is not registered for XKCD comics.';
                $messageType = 'error';
            } else {
                $code = generateVerificationCode();
                
                if (storeVerificationCode($email, $code)) {
                    // Send unsubscribe verification email
                    $subject = "XKCD Comic Unsubscription - Verification Code";
                    $unsubscribeMessage = "Hello!\n\n";
                    $unsubscribeMessage .= "You have requested to unsubscribe from our XKCD comic service.\n\n";
                    $unsubscribeMessage .= "Your verification code is: " . $code . "\n\n";
                    $unsubscribeMessage .= "Please enter this code on our website to complete your unsubscription.\n\n";
                    $unsubscribeMessage .= "If you didn't request this unsubscription, please ignore this email.\n\n";
                    $unsubscribeMessage .= "Best regards,\nXKCD Comic Team";
                    
                    // Load configuration
                    $configFile = __DIR__ . '/config.php';
                    if (file_exists($configFile)) {
                        require_once $configFile;
                    }
                    
                    // Try Gmail SMTP if enabled
                    $emailSent = false;
                    if (defined('GMAIL_ENABLED') && GMAIL_ENABLED && defined('GMAIL_USERNAME') && defined('GMAIL_PASSWORD')) {
                        // Use PHPMailer for reliable Gmail SMTP
                        if (sendEmailViaPHPMailer($email, $subject, $unsubscribeMessage, GMAIL_HOST, GMAIL_PORT, GMAIL_USERNAME, GMAIL_PASSWORD)) {
                            $emailSent = true;
                        }
                    }
                    
                    // Fallback to basic mail() function
                    if (!$emailSent) {
                        $headers = "From: noreply@xkcd-comics.com\r\n";
                        $headers .= "Reply-To: noreply@xkcd-comics.com\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        
                        // For testing purposes, log the email
                        $logFile = __DIR__ . '/email_log.txt';
                        $logEntry = "[" . date('Y-m-d H:i:s') . "] Unsubscribe email would be sent to: $email\n";
                        $logEntry .= "Subject: $subject\n";
                        $logEntry .= "Code: $code\n";
                        $logEntry .= "Message: $unsubscribeMessage\n";
                        $logEntry .= str_repeat("-", 50) . "\n";
                        
                        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
                        
                        // Try to send actual email, but don't fail if mail server is not configured
                        $mailResult = @mail($email, $subject, $unsubscribeMessage, $headers);
                        
                        if ($mailResult) {
                            $emailSent = true;
                        } else {
                            // Log that mail server is not configured
                            $errorLog = "[" . date('Y-m-d H:i:s') . "] Mail server not configured. Unsubscribe email logged to email_log.txt\n";
                            file_put_contents($logFile, $errorLog, FILE_APPEND | LOCK_EX);
                        }
                    }
                    
                    if ($emailSent) {
                        $_SESSION['unsubscribe_email'] = $email;
                        $message = 'Verification code sent to your email. Please check your inbox and enter the code below to unsubscribe.';
                        $messageType = 'success';
                    } else {
                        $_SESSION['unsubscribe_email'] = $email;
                        $message = 'Verification code generated and logged. Check email_log.txt for the code.';
                        $messageType = 'success';
                    }
                } else {
                    $message = 'Failed to process unsubscription request. Please try again.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'confirm_unsubscribe') {
            $email = trim($_POST['email'] ?? '');
            $code = trim($_POST['code'] ?? '');
            
            if (empty($email) || empty($code)) {
                $message = 'Please enter both email and verification code.';
                $messageType = 'error';
            } elseif (verifyCode($email, $code)) {
                if (unsubscribeEmail($email)) {
                    $message = 'Successfully unsubscribed! You will no longer receive XKCD comics.';
                    $messageType = 'success';
                    unset($_SESSION['unsubscribe_email']);
                } else {
                    $message = 'Failed to unsubscribe. Please try again.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Invalid verification code. Please check your email and try again.';
                $messageType = 'error';
            }
        }
    }
}

$unsubscribeEmail = $_SESSION['unsubscribe_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - XKCD Comic Subscription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff6b6b;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .verification-section {
            display: <?php echo $unsubscribeEmail ? 'block' : 'none'; ?>;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e1e5e9;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚫 Unsubscribe</h1>
            <p>We're sorry to see you go!</p>
        </div>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> This action will permanently remove your email from our subscription list.
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Unsubscribe Request Form -->
        <form method="POST" action="">
            <input type="hidden" name="action" value="request_unsubscribe">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($unsubscribeEmail); ?>" 
                       placeholder="Enter your registered email address" required>
            </div>
            <button type="submit" class="btn">Request Unsubscription</button>
        </form>
        
        <!-- Verification Form -->
        <div class="verification-section" id="verificationSection">
            <h3 style="color: #333; margin-bottom: 20px; text-align: center;">Confirm Unsubscription</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="confirm_unsubscribe">
                <div class="form-group">
                    <label for="verify_email">Email Address:</label>
                    <input type="email" id="verify_email" name="email" 
                           value="<?php echo htmlspecialchars($unsubscribeEmail); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="code">Verification Code:</label>
                    <input type="text" id="code" name="code" 
                           placeholder="Enter the 6-digit code from your email" 
                           maxlength="6" pattern="[0-9]{6}" required>
                </div>
                <button type="submit" class="btn">Confirm Unsubscription</button>
            </form>
        </div>
        
        <div class="footer">
            <p>Changed your mind? <a href="index.php">Subscribe again</a></p>
        </div>
    </div>
    
    <script>
        // Show verification section if there's a pending unsubscribe email
        <?php if ($unsubscribeEmail): ?>
        document.getElementById('verificationSection').style.display = 'block';
        <?php endif; ?>
        
        // Auto-focus on verification code input
        document.getElementById('code')?.focus();
    </script>
</body>
</html> 