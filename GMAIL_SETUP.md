# Gmail SMTP Setup Guide

## 🎯 **How to Send OTP to Gmail Instead of verification_codes.txt**

### **Step 1: Enable 2-Factor Authentication on Gmail**

1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Click on **Security** in the left sidebar
3. Under **Signing in to Google**, click on **2-Step Verification**
4. Follow the steps to enable 2-Factor Authentication

### **Step 2: Generate App Password**

1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Click on **Security** in the left sidebar
3. Under **Signing in to Google**, click on **App passwords**
4. Select **Mail** from the dropdown
5. Click **Generate**
6. Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)

### **Step 3: Configure the System**

1. Open `src/config.php` in your text editor
2. Update the following settings:

```php
// Gmail SMTP Settings
define('GMAIL_ENABLED', true); // Change to true
define('GMAIL_USERNAME', 'your-actual-email@gmail.com'); // Your Gmail
define('GMAIL_PASSWORD', 'your-16-char-app-password'); // App password from Step 2
```

### **Step 4: Test the Configuration**

1. Restart your PHP server
2. Go to http://localhost:8000/index.php
3. Enter your email address
4. Check your Gmail inbox for the verification code

## 🔧 **Alternative Methods**

### **Method 1: Using PHPMailer (Recommended for Production)**

```bash
# Install PHPMailer via Composer
composer require phpmailer/phpmailer
```

Then update `functions.php` to use PHPMailer instead of basic SMTP.

### **Method 2: Using SendGrid**

1. Sign up for [SendGrid](https://sendgrid.com/)
2. Get your API key
3. Update the configuration to use SendGrid API

### **Method 3: Using Mailgun**

1. Sign up for [Mailgun](https://mailgun.com/)
2. Get your API key
3. Update the configuration to use Mailgun API

## 🚨 **Security Notes**

- **Never use your regular Gmail password**
- **Always use App Passwords for applications**
- **Keep your App Password secure**
- **Enable 2-Factor Authentication**

## 📧 **Email Templates**

The system will send emails with:
- **Subject**: "XKCD Comic Subscription - Verification Code"
- **Content**: 6-digit verification code
- **From**: Your configured Gmail address

## 🔍 **Troubleshooting**

### **Common Issues:**

1. **"Authentication failed"**
   - Check your App Password is correct
   - Ensure 2-Factor Authentication is enabled

2. **"Connection refused"**
   - Check your internet connection
   - Verify Gmail SMTP settings

3. **"Email not received"**
   - Check spam folder
   - Verify email address is correct

### **Testing:**

Run the test script to verify configuration:
```bash
php test_system.php
```

## ✅ **Success Indicators**

When configured correctly, you should see:
- ✅ Emails delivered to Gmail inbox
- ✅ No errors in email_log.txt
- ✅ Verification codes working on website
- ✅ Successful registration flow

---

**Need Help?** Check the email_log.txt file for detailed error messages. 