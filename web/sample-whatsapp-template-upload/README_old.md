# WhatsApp Template Bulk Messaging Feature

## Overview
This feature allows administrators and sub-administrators to upload Excel/CSV files containing mobile numbers and send WhatsApp messages using existing templates with default values. Enhanced with anti-spam protection and humanized timing.

## 🚀 Bulk WhatsApp Messaging

### How to Send Bulk Messages
1. Navigate to WhatsApp Templates → View Template (e.g., admin/whatsapp-templates/view?id=1)
2. In the "Bulk WhatsApp Messaging" section (yellow card):
   - **Enter Parameters**: Fill in BODY parameter values in the form (will be used for all contacts)
   - **Upload Contact File**: Click "Download Contact Format" to get the correct CSV format
   - **Prepare File**: Create CSV with mobile numbers only
   - **Send**: Upload the file and click "Send Bulk WhatsApp Messages"

### 📝 Parameter Handling (NEW)
- **BODY Parameters**: Enter values manually in the UI form - same values used for all contacts
- **HEADER Media (IMAGE/VIDEO/DOCUMENT)**: Include URLs in Excel columns or leave empty for defaults
- **HEADER Text**: Automatically uses default values from template configuration  
- **Mobile Numbers**: Only mobile numbers needed in mobile_number column
- **URL Validation**: Media URLs are validated before sending (must be valid URLs)
- **Flexible Format**: Excel columns are optional - system adapts based on template requirements

### 🤖 Anti-Spam Features (NEW)
- **Humanized Timing**: 2-3.5 second random delays between messages
- **Progressive Delays**: Extra 1-2 seconds for batches over 50 messages  
- **Batch Breaks**: 5-second pauses every 25 messages
- **Duplicate Detection**: Automatically skips duplicate numbers
- **Session Tracking**: Unique ID for each bulk send operation

### Contact File Format
Your CSV file must include:
- **mobile_number** (required): Phone number in format 1234567890 (10-15 digits)
- **Media URLs** (optional): If template has HEADER components with IMAGE/VIDEO/DOCUMENT

**Basic CSV format (no media):**
```csv
mobile_number
9876543210
9876543211
9876543212
```

**Extended CSV format (with media):**
```csv
mobile_number,image_url,video_url
9876543210,https://example.com/image1.jpg,https://example.com/video1.mp4
9876543211,https://example.com/image2.jpg,https://example.com/video2.mp4
9876543212,,https://example.com/video3.mp4
```

### 📋 Template Parameters
- **BODY Parameters**: Enter manually in the UI form before uploading contact file
- **HEADER Media**: Include URLs in Excel columns (image_url, video_url, document_url)
- **HEADER Defaults**: Empty cells in Excel will use template's default values
- **Parameter Values**: Same BODY parameter values will be used for all contacts in the batch
```csv
mobile_number
1234567890
1234567891
1234567892
1234567893
1234567894
```

### Features
- ✅ **Simple Format**: Only mobile numbers required
- ✅ **Default Values**: Uses template's default parameter values automatically
- ✅ **Rate Limiting**: Built-in delays to prevent API throttling
- ✅ **Error Handling**: Detailed reporting of successful vs failed messages
- ✅ **Validation**: Mobile number format validation
- ✅ **Progress Tracking**: Real-time feedback during bulk sending

## � Template Integration
- Template ID is automatically detected from the URL (e.g., view?id=1)
- All template parameters use their configured default values
- No need to specify parameter values in the Excel file

## 🔧 WhatsApp API Configuration

For bulk messaging to work, ensure these settings are configured in your system:
- **whatsapp_api_url**: Your WhatsApp Business API endpoint
- **whatsapp_api_token**: Your API authentication token

## 📱 Message Sending Process

1. **File Upload**: System reads your contact file (mobile numbers only)
2. **Validation**: Checks mobile number format (10-15 digits)
3. **Parameter Assignment**: Uses template's default values for all parameters
4. **Message Construction**: Builds WhatsApp template message
5. **API Calls**: Sends individual messages via WhatsApp Business API
6. **Result Tracking**: Reports success/failure for each contact

## ⚠️ Important Notes

### Enhanced Rate Limiting & Anti-Spam (NEW)
- **Base Timing**: 2-3.5 second delays between messages (randomized)
- **Progressive Delays**: Extra 1-2 seconds for batches over 50 messages
- **Batch Breaks**: 5-second pauses every 25 messages
- **Processing Time**: 
  - Small batches (1-25): ~2-4 minutes
  - Medium batches (26-50): ~5-8 minutes  
  - Large batches (51-100): ~10-15 minutes
- **Session Tracking**: Each bulk send gets unique session ID for monitoring

### Mobile Number Format
- Use international format without "+" or country code symbols
- Example: 1234567890 (not +1-234-567-890)
- 10-15 digits only
- Duplicate numbers in same batch are automatically skipped

### Template Parameters
- All parameters automatically use template's default values
- No need to specify parameter values in CSV file
- Ensure your template has proper default values configured
- Template must be in 'APPROVED' status for bulk sending

## 🔐 Security & Access
- Available to Admins and Sub-admins only
- All file uploads are temporary and automatically cleaned
- Input validation prevents malicious data
- Template ID validation ensures security

## 📊 Example Use Cases

### Marketing Campaign
Upload list of customer mobile numbers, template sends with default marketing message

### Order Notifications
Upload list of customer mobile numbers, template sends with default order notification

### Service Announcements
Upload list of customer mobile numbers, template sends with default announcement

## 🛠️ Troubleshooting

### Common Issues

1. **"Missing required column: mobile_number" error**
   - Ensure your CSV has "mobile_number" as the first column header
   - Download the example format to see correct structure

2. **"Invalid mobile number format" error**
   - Use only digits (10-15 characters)
   - Remove spaces, dashes, or country codes

3. **"WhatsApp API configuration not found" error**
   - Configure whatsapp_api_url and whatsapp_api_token in settings

4. **Messages not sending**
   - Check WhatsApp API credentials
   - Verify template is approved in WhatsApp Business
   - Ensure template has proper default values
   - Monitor API rate limits

### File Format Requirements
- CSV format preferred
- First row must be header: mobile_number
- Each subsequent row contains one mobile number
- No additional columns needed
