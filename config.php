<?php
/**
 * Gmail SMTP Configuration
 * 
 * To send emails to Gmail, you need to:
 * 1. Enable 2-Factor Authentication on your Gmail account
 * 2. Generate an App Password
 * 3. Update the settings below
 */

// Gmail SMTP Settings
define('GMAIL_ENABLED', true); // Set to true to enable Gmail SMTP
define('GMAIL_USERNAME', 'adityapratapsingh2406@gmail.com'); // Your Gmail address
define('GMAIL_PASSWORD', 'gepk bonw twsm pfjs'); // Your Gmail App Password
define('GMAIL_HOST', 'smtp.gmail.com');
define('GMAIL_PORT', 587);

// Email Settings
define('FROM_EMAIL', 'adityapratapsingh2406@gmail.com'); // Email address to send from
define('FROM_NAME', 'XKCD Comic Team'); // Name to display as sender

/**
 * How to get Gmail App Password:
 * 
 * 1. Go to your Google Account settings
 * 2. Enable 2-Factor Authentication if not already enabled
 * 3. Go to Security → App passwords
 * 4. Generate a new app password for "Mail"
 * 5. Use that password in GMAIL_PASSWORD above
 * 
 * Note: Never use your regular Gmail password!
 */
?> 