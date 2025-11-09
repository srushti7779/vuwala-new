# WhatsApp Template Bulk Messaging

## Overview
Simple bulk WhatsApp messaging system where all parameters are entered via UI and Excel contains only mobile numbers.

## Key Features

1. **Simple Excel Format**: Only mobile numbers required in Excel
2. **UI Parameter Input**: All template parameters (BODY + HEADER) entered via web form  
3. **Anti-Spam Protection**: Humanized timing with delays between messages
4. **Universal Parameter Application**: Same parameter values used for all contacts

## CSV Format

### Required Column
- `mobile_number` - Phone numbers for message recipients (with country code, e.g., 919876543210)

### Example CSV Content:
```csv
mobile_number
9876543210
9876543211
9876543212
9876543213
9876543214
```

## Usage Instructions

1. **Navigate to Template**: Go to WhatsApp Templates view (e.g., `/admin/whatsapp-templates/view?id=1`)
2. **Enter All Parameters**: In the "Bulk Message Sending" section, fill in ALL template parameters:
   - **BODY Parameters**: Text content, names, numbers, etc.
   - **HEADER Parameters**: Images, videos, documents (URLs), text headers
3. **Upload Contact File**: Upload CSV with only mobile numbers  
4. **Send Messages**: Click "Send Bulk Messages"

## Parameter Types

### BODY Parameters
- Text content that appears in message body
- Names, numbers, dates, custom text
- Entered manually in UI form

### HEADER Parameters  
- **IMAGE**: Direct URL to image file (JPG, PNG, GIF)
- **VIDEO**: Direct URL to video file (MP4, MOV, AVI) 
- **DOCUMENT**: Direct URL to document (PDF, DOC, XLS)
- **TEXT**: Header text content

## Media Requirements

- **URLs must be publicly accessible and valid**
- **Supported formats**:
  - **Images**: JPG, JPEG, PNG, GIF (max 5MB)
  - **Videos**: MP4, MOV, AVI (max 16MB)  
  - **Documents**: PDF, DOC, DOCX, XLS, XLSX, TXT (max 100MB)
- **HTTPS Recommended**: Secure URLs preferred

## Anti-Spam Features

- **Randomized Delays**: 2-3.5 seconds between messages
- **Progressive Delays**: Longer delays for large batches
- **Automatic Breaks**: 30-60 second pause every 50 messages
- **Session Tracking**: Prevents duplicate sends
- **Batch Processing**: Processes contacts in manageable chunks

## Error Handling

- **Parameter Validation**: All required parameters checked before sending
- **Mobile Number Validation**: Format validation (10-15 digits)
- **URL Validation**: Media URLs validated before sending
- **Comprehensive Logging**: Detailed success/failure tracking
- **Real-time Progress**: Live updates during sending

## Example Use Cases

### Marketing Campaign with Image
1. Enter BODY parameters: "Hi {{name}}, check our new offer!"
2. Enter HEADER IMAGE: "https://yoursite.com/offer-banner.jpg" 
3. Upload CSV with customer mobile numbers
4. Send to all customers with same message + image

### Order Notifications with Document
1. Enter BODY parameters: "Your order {{order_id}} is ready"
2. Enter HEADER DOCUMENT: "https://yoursite.com/invoice.pdf"
3. Upload CSV with customer mobile numbers  
4. Send order confirmations with invoice attached

### Video Announcements
1. Enter BODY parameters: "New product launch - watch now!"
2. Enter HEADER VIDEO: "https://yoursite.com/product-video.mp4"
3. Upload CSV with subscriber mobile numbers
4. Send announcement with embedded video

## Benefits of This Approach

✅ **Simple Excel Format**: No complex columns, just mobile numbers
✅ **Centralized Control**: All parameters in one place via UI
✅ **Consistent Messaging**: Same parameters for all recipients  
✅ **Easy Management**: Edit parameters without touching Excel file
✅ **Quick Setup**: Download template, add numbers, upload, send
✅ **Error Prevention**: UI validation prevents parameter mistakes

## Notes

- **Excel Simplicity**: Only `mobile_number` column required
- **Parameter Universality**: All UI parameters applied to every contact
- **Media Flexibility**: Support for images, videos, documents via URLs
- **Template Compatibility**: Works with any approved WhatsApp template
- **Real-time Feedback**: Progress tracking and error reporting during send process
