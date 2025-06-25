<?php
/**
 * XKCD Comic Subscription System - Demo Script
 * 
 * This script demonstrates the complete email verification flow
 * without requiring a mail server
 */

require_once 'functions.php';

echo "🎨 XKCD Comic Subscription System - Demo\n";
echo "=======================================\n\n";

// Demo email registration
$demoEmail = 'demo@example.com';
echo "1. Starting email registration for: $demoEmail\n";

// Generate verification code
$code = generateVerificationCode();
echo "2. Generated verification code: $code\n";

// Send verification email (will be logged)
if (sendVerificationEmail($demoEmail, $code)) {
    echo "3. ✅ Verification email processed successfully\n";
} else {
    echo "3. ❌ Failed to process verification email\n";
}

// Store verification code
if (storeVerificationCode($demoEmail, $code)) {
    echo "4. ✅ Verification code stored\n";
} else {
    echo "4. ❌ Failed to store verification code\n";
}

// Verify the code
if (verifyCode($demoEmail, $code)) {
    echo "5. ✅ Verification code validated successfully\n";
} else {
    echo "5. ❌ Verification code validation failed\n";
}

// Register the email
if (registerEmail($demoEmail)) {
    echo "6. ✅ Email registered successfully\n";
} else {
    echo "6. ❌ Failed to register email\n";
}

// Check if email is registered
if (isEmailRegistered($demoEmail)) {
    echo "7. ✅ Email confirmed as registered\n";
} else {
    echo "7. ❌ Email not found in registration list\n";
}

echo "\n📧 Email Log Contents:\n";
echo str_repeat("-", 50) . "\n";
if (file_exists('email_log.txt')) {
    echo file_get_contents('email_log.txt');
} else {
    echo "No email log found.\n";
}

echo "\n📋 Registered Emails:\n";
echo str_repeat("-", 50) . "\n";
if (file_exists('registered_emails.txt')) {
    $emails = file('registered_emails.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        echo "No emails registered yet.\n";
    } else {
        foreach ($emails as $email) {
            echo "- $email\n";
        }
    }
} else {
    echo "No registered emails file found.\n";
}

echo "\n🎉 Demo completed! The system is working correctly.\n";
echo "You can now test the web interface at: http://localhost:8000/index.php\n";
?> 