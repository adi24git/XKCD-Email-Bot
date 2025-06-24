<?php
require_once 'functions.php';
// This script should send XKCD updates to all registered emails.
// You need to implement this functionality.

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for CRON job
$logFile = __DIR__ . '/cron.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Start logging
logMessage("CRON job started - XKCD comic distribution");

try {
    // Get all registered emails
    $emails = getRegisteredEmails();
    
    if (empty($emails)) {
        logMessage("No registered emails found. Exiting.");
        exit(0);
    }
    
    logMessage("Found " . count($emails) . " registered emails");
    
    // Fetch and format XKCD comic
    $htmlContent = fetchAndFormatXKCDData();
    
    if (strpos($htmlContent, 'Sorry, unable to fetch') !== false) {
        logMessage("ERROR: Failed to fetch XKCD comic data");
        exit(1);
    }
    
    // Email headers
    $subject = "Your Daily XKCD Comic - " . date('F j, Y');
    
    $headers = "From: noreply@xkcd-comics.com\r\n";
    $headers .= "Reply-To: noreply@xkcd-comics.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: XKCD Comic Service\r\n";
    
    $successCount = 0;
    $failureCount = 0;
    
    // Send emails to all registered users
    foreach ($emails as $email) {
        $email = trim($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logMessage("WARNING: Invalid email format: $email");
            $failureCount++;
            continue;
        }
        
        // Send the email
        if (mail($email, $subject, $htmlContent, $headers)) {
            $successCount++;
            logMessage("SUCCESS: Email sent to $email");
        } else {
            $failureCount++;
            logMessage("ERROR: Failed to send email to $email");
        }
        
        // Small delay to prevent overwhelming the mail server
        usleep(100000); // 0.1 second delay
    }
    
    // Log summary
    logMessage("CRON job completed - Success: $successCount, Failures: $failureCount");
    
    // Clean up old verification codes (older than 1 hour)
    cleanupOldVerificationCodes();
    
} catch (Exception $e) {
    logMessage("CRON job failed with exception: " . $e->getMessage());
    exit(1);
}

/**
 * Clean up old verification codes
 */
function cleanupOldVerificationCodes() {
    $verificationFile = __DIR__ . '/verification_codes.txt';
    
    if (!file_exists($verificationFile)) {
        return;
    }
    
    $lines = file($verificationFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newLines = [];
    $currentTime = time();
    
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            $timestamp = (int)$parts[2];
            
            // Keep codes that are less than 1 hour old
            if (($currentTime - $timestamp) < 3600) {
                $newLines[] = $line;
            }
        }
    }
    
    $content = implode("\n", $newLines) . "\n";
    file_put_contents($verificationFile, $content, LOCK_EX);
    
    $removedCount = count($lines) - count($newLines);
    if ($removedCount > 0) {
        logMessage("Cleaned up $removedCount old verification codes");
    }
}

logMessage("CRON job finished successfully");
exit(0);
