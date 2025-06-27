#!/bin/bash

# XKCD Comic Service CRON Setup Script
# This script sets up a CRON job to run cron.php every 24 hours

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🎨 XKCD Comic Service - CRON Setup${NC}"
echo "=================================="

# Get the current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CRON_PHP="$SCRIPT_DIR/cron.php"

# Check if cron.php exists
if [ ! -f "$CRON_PHP" ]; then
    echo -e "${RED}❌ Error: cron.php not found at $CRON_PHP${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Found cron.php at: $CRON_PHP${NC}"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ Error: PHP is not installed or not in PATH${NC}"
    echo "Please install PHP and try again."
    exit 1
fi

echo -e "${GREEN}✅ PHP is available: $(php --version | head -n1)${NC}"

# Create the CRON job entry
CRON_JOB="0 9 * * * /usr/bin/php $CRON_PHP >> $SCRIPT_DIR/cron.log 2>&1"

echo -e "${YELLOW}📅 Setting up CRON job to run daily at 9:00 AM...${NC}"

# Check if the CRON job already exists
if crontab -l 2>/dev/null | grep -q "$CRON_PHP"; then
    echo -e "${YELLOW}⚠️  CRON job already exists. Removing old entry...${NC}"
    # Remove existing CRON job
    crontab -l 2>/dev/null | grep -v "$CRON_PHP" | crontab -
fi

# Add the new CRON job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ CRON job successfully added!${NC}"
    echo ""
    echo -e "${BLUE}📋 CRON Job Details:${NC}"
    echo "   • Command: $CRON_JOB"
    echo "   • Runs: Daily at 9:00 AM"
    echo "   • Log file: $SCRIPT_DIR/cron.log"
    echo ""
    echo -e "${BLUE}🔧 Useful Commands:${NC}"
    echo "   • View CRON jobs: crontab -l"
    echo "   • Edit CRON jobs: crontab -e"
    echo "   • View logs: tail -f $SCRIPT_DIR/cron.log"
    echo "   • Test manually: php $CRON_PHP"
    echo ""
    echo -e "${GREEN}🎉 Setup complete! The XKCD comic service will now run automatically every day.${NC}"
else
    echo -e "${RED}❌ Failed to add CRON job. Please check your permissions.${NC}"
    echo "You may need to run this script with sudo or as a user with CRON access."
    exit 1
fi

# Set proper permissions for log file
touch "$SCRIPT_DIR/cron.log"
chmod 644 "$SCRIPT_DIR/cron.log"

echo -e "${GREEN}✅ Log file permissions set${NC}"

# Optional: Test the CRON job
echo ""
read -p "Would you like to test the CRON job now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}🧪 Testing CRON job...${NC}"
    if php "$CRON_PHP"; then
        echo -e "${GREEN}✅ Test completed successfully!${NC}"
        echo -e "${BLUE}📝 Check the log file for details: tail -f $SCRIPT_DIR/cron.log${NC}"
    else
        echo -e "${RED}❌ Test failed. Check the log file for errors.${NC}"
    fi
fi

echo ""
echo -e "${GREEN}🎨 XKCD Comic Service is ready!${NC}"
