<?php
/**
 * XKCD Comic Subscription System - Test Script
 * 
 * This script tests all major components of the system
 * Run this to verify everything is working correctly
 */

require_once 'functions.php';

echo "🎨 XKCD Comic Subscription System - Test Script\n";
echo "==============================================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// Test 1: Generate verification code
echo "1. Testing verification code generation...\n";
$code = generateVerificationCode();
if (strlen($code) === 6 && is_numeric($code)) {
    echo "   ✅ PASS: Generated code: $code\n";
    $tests['verification_code'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: Invalid code generated: $code\n";
    $tests['verification_code'] = false;
    $failed++;
}

// Test 2: Check file permissions
echo "\n2. Testing file permissions...\n";
$files = ['registered_emails.txt', 'verification_codes.txt', 'cron.log'];
$fileTests = 0;

foreach ($files as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    // Create file if it doesn't exist
    if (!file_exists($filePath)) {
        file_put_contents($filePath, '');
    }
    
    if (is_writable($filePath)) {
        echo "   ✅ PASS: $file is writable\n";
        $fileTests++;
    } else {
        echo "   ❌ FAIL: $file is not writable\n";
    }
}

if ($fileTests === count($files)) {
    $tests['file_permissions'] = true;
    $passed++;
} else {
    $tests['file_permissions'] = false;
    $failed++;
}

// Test 3: Email registration functions
echo "\n3. Testing email registration functions...\n";

// Test with a sample email
$testEmail = 'test@example.com';

// Check if email is registered (should be false initially)
if (!isEmailRegistered($testEmail)) {
    echo "   ✅ PASS: Email not registered initially\n";
} else {
    echo "   ❌ FAIL: Email already registered\n";
    $tests['email_registration'] = false;
    $failed++;
    goto email_test_end;
}

// Register email
if (registerEmail($testEmail)) {
    echo "   ✅ PASS: Email registered successfully\n";
} else {
    echo "   ❌ FAIL: Failed to register email\n";
    $tests['email_registration'] = false;
    $failed++;
    goto email_test_end;
}

// Check if email is now registered
if (isEmailRegistered($testEmail)) {
    echo "   ✅ PASS: Email confirmed as registered\n";
} else {
    echo "   ❌ FAIL: Email not found after registration\n";
    $tests['email_registration'] = false;
    $failed++;
    goto email_test_end;
}

// Unsubscribe email
if (unsubscribeEmail($testEmail)) {
    echo "   ✅ PASS: Email unsubscribed successfully\n";
} else {
    echo "   ❌ FAIL: Failed to unsubscribe email\n";
    $tests['email_registration'] = false;
    $failed++;
    goto email_test_end;
}

// Check if email is unsubscribed
if (!isEmailRegistered($testEmail)) {
    echo "   ✅ PASS: Email confirmed as unsubscribed\n";
    $tests['email_registration'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: Email still registered after unsubscription\n";
    $tests['email_registration'] = false;
    $failed++;
}

email_test_end:

// Test 4: Verification code storage and validation
echo "\n4. Testing verification code system...\n";

$testEmail = 'verify@example.com';
$testCode = '123456';

// Store verification code
if (storeVerificationCode($testEmail, $testCode)) {
    echo "   ✅ PASS: Verification code stored\n";
} else {
    echo "   ❌ FAIL: Failed to store verification code\n";
    $tests['verification_system'] = false;
    $failed++;
    goto verification_test_end;
}

// Verify code
if (verifyCode($testEmail, $testCode)) {
    echo "   ✅ PASS: Verification code validated\n";
    $tests['verification_system'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: Verification code validation failed\n";
    $tests['verification_system'] = false;
    $failed++;
}

verification_test_end:

// Test 5: XKCD data fetching
echo "\n5. Testing XKCD data fetching...\n";

$xkcdData = fetchAndFormatXKCDData();

if (strpos($xkcdData, 'Sorry, unable to fetch') === false && 
    strpos($xkcdData, 'Your Daily XKCD Comic') !== false) {
    echo "   ✅ PASS: XKCD data fetched and formatted successfully\n";
    $tests['xkcd_fetching'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: Failed to fetch or format XKCD data\n";
    echo "   Response: " . substr($xkcdData, 0, 100) . "...\n";
    $tests['xkcd_fetching'] = false;
    $failed++;
}

// Test 6: Email validation
echo "\n6. Testing email validation...\n";

$validEmails = ['test@example.com', 'user.name@domain.co.uk', 'user+tag@example.org'];
$invalidEmails = ['invalid-email', '@example.com', 'test@', 'test.example.com'];

$emailValidationPassed = 0;

foreach ($validEmails as $email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailValidationPassed++;
    }
}

foreach ($invalidEmails as $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailValidationPassed++;
    }
}

$totalEmailTests = count($validEmails) + count($invalidEmails);

if ($emailValidationPassed === $totalEmailTests) {
    echo "   ✅ PASS: Email validation working correctly\n";
    $tests['email_validation'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: Email validation issues ($emailValidationPassed/$totalEmailTests)\n";
    $tests['email_validation'] = false;
    $failed++;
}

// Test 7: PHP mail function availability
echo "\n7. Testing mail function availability...\n";

if (function_exists('mail')) {
    echo "   ✅ PASS: PHP mail() function is available\n";
    $tests['mail_function'] = true;
    $passed++;
} else {
    echo "   ❌ FAIL: PHP mail() function is not available\n";
    $tests['mail_function'] = false;
    $failed++;
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Success Rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED! The system is ready to use.\n\n";
    echo "Next steps:\n";
    echo "1. Run: ./setup_cron.sh\n";
    echo "2. Test registration at: index.php\n";
    echo "3. Test unsubscription at: unsubscribe.php\n";
    echo "4. Monitor logs: tail -f cron.log\n";
} else {
    echo "⚠️  SOME TESTS FAILED. Please check the issues above.\n\n";
    echo "Common fixes:\n";
    echo "- Check file permissions\n";
    echo "- Verify PHP configuration\n";
    echo "- Ensure mail server is configured\n";
    echo "- Check internet connectivity for XKCD API\n";
}

echo "\n" . str_repeat("=", 50) . "\n"; 