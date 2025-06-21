# XKCD Comic Subscription System

A PHP-based email verification system that allows users to subscribe to daily XKCD comics. Users register with their email, receive a verification code, and get a random XKCD comic delivered to their inbox every 24 hours.

## Features

- ✅ Email registration with verification
- ✅ 6-digit verification codes
- ✅ Daily XKCD comic delivery via CRON
- ✅ Unsubscribe functionality with verification
- ✅ Beautiful, responsive UI
- ✅ Comprehensive logging
- ✅ File-based data storage

## File Structure

```
src/
├── functions.php          # Core functionality and helper functions
├── index.php             # Main registration page
├── unsubscribe.php       # Unsubscribe page
├── cron.php              # CRON job for daily comic distribution
├── setup_cron.sh         # CRON setup script
├── registered_emails.txt  # Database of registered emails
├── verification_codes.txt # Temporary verification codes
├── cron.log              # CRON job logs
└── README.md             # This file
```

## Installation & Setup

### 1. Prerequisites

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Mail server configured (for sending emails)
- CRON access

### 2. Setup Steps

1. **Upload files** to your web server's `src/` directory
2. **Set permissions** for write access:
   ```bash
   chmod 644 src/registered_emails.txt
   chmod 644 src/verification_codes.txt
   chmod 644 src/cron.log
   ```

3. **Configure CRON job**:
   ```bash
   cd src/
   ./setup_cron.sh
   ```

4. **Test the system**:
   - Visit `index.php` to test registration
   - Run `php cron.php` to test comic distribution

## Usage

### For Users

1. **Subscribe**: Visit the main page and enter your email
2. **Verify**: Check your email for a 6-digit code and enter it
3. **Receive**: Get daily XKCD comics automatically
4. **Unsubscribe**: Visit the unsubscribe page to opt out

### For Administrators

- **View registered emails**: Check `registered_emails.txt`
- **Monitor CRON logs**: Check `cron.log`
- **Manual comic distribution**: Run `php cron.php`
- **Manage CRON jobs**: Use `crontab -l` or `crontab -e`

## API Functions

### Core Functions

- `generateVerificationCode()`: Creates 6-digit codes
- `sendVerificationEmail($email, $code)`: Sends verification emails
- `registerEmail($email)`: Adds email to subscription list
- `unsubscribeEmail($email)`: Removes email from subscription list
- `fetchAndFormatXKCDData()`: Fetches and formats XKCD comics
- `sendXKCDUpdatesToSubscribers()`: Sends comics to all subscribers

### Helper Functions

- `isEmailRegistered($email)`: Checks if email is subscribed
- `getRegisteredEmails()`: Returns all registered emails
- `storeVerificationCode($email, $code)`: Stores temporary codes
- `verifyCode($email, $code)`: Validates verification codes

## Security Features

- Email validation and sanitization
- Verification codes expire after 10 minutes
- Automatic cleanup of old verification codes
- File locking for concurrent access
- XSS protection with `htmlspecialchars()`

## Email Templates

### Registration Email
- Subject: "XKCD Comic Subscription - Verification Code"
- Contains 6-digit verification code
- Instructions for completing subscription

### Unsubscribe Email
- Subject: "XKCD Comic Unsubscription - Verification Code"
- Contains 6-digit verification code
- Instructions for completing unsubscription

### Daily Comic Email
- Subject: "Your Daily XKCD Comic - [Date]"
- HTML formatted comic with image
- Unsubscribe link included

## CRON Configuration

The system uses a CRON job that runs daily at 9:00 AM:

```bash
0 9 * * * /usr/bin/php /path/to/src/cron.php >> /path/to/src/cron.log 2>&1
```

## Troubleshooting

### Common Issues

1. **Emails not sending**:
   - Check mail server configuration
   - Verify PHP mail() function works
   - Check server logs

2. **CRON not running**:
   - Verify CRON service is active
   - Check CRON job exists: `crontab -l`
   - Check log file: `tail -f cron.log`

3. **Verification codes not working**:
   - Codes expire after 10 minutes
   - Check system time is correct
   - Verify file permissions

### Log Files

- `cron.log`: CRON job execution logs
- Web server logs: For web interface issues
- Mail server logs: For email delivery issues

## Customization

### Styling
- Modify CSS in `index.php` and `unsubscribe.php`
- Update color schemes and layouts
- Add custom branding

### Email Content
- Edit email templates in `functions.php`
- Customize subject lines and message content
- Add company branding

### Comic Selection
- Modify `fetchAndFormatXKCDData()` for different comic sources
- Change comic selection logic (currently random)
- Add comic categories or themes

## License

This project is open source and available under the MIT License.

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review log files for errors
3. Test individual components
4. Verify server configuration

---

**Note**: This system requires a properly configured mail server to send verification emails and daily comics. Make sure your hosting provider supports PHP mail() function or configure an SMTP server. 