# Database Setup Guide

## Overview
This directory contains a comprehensive test database with **117 essential tables** (out of 131 total) and realistic dummy data for your Yii2 application. All core functionality tables are included with sample data.

## Files Generated
1. **`complete_test_database.sql`** - Complete database dump with schema + data
2. **`complete_db_generator.py`** - Python script used to generate the database
3. **`DATABASE_SETUP_README.md`** - This file

## Database Structure

### Total Tables: 117 (Core + Essential Features)

#### Core Tables (8)
- `user` - User accounts (5 sample users: 1 admin, 2 vendors, 2 customers)
- `auth` - Authentication providers
- `auth_session` - User sessions
- `roles` - User roles (Admin, Vendor, Customer, Staff)
- `user_roles` - User-role mappings
- `city` - Cities (5 major Indian cities)
- `main_category` - Service categories (Beauty, Salon, Massage, etc.)
- `vendor_details` - Vendor/business information (2 sample vendors)

#### Orders & Services (20+)
- `orders` - Service orders (2 sample orders)
- `order_details` - Order line items
- `order_status` - Order status definitions
- `order_transaction_details` - Payment transactions
- `services` - Services offered (4 sample services)
- `sub_category` - Service subcategories
- `staff` - Vendor staff members (3 sample staff)
- And more...

#### Products & Inventory (10+)
- `products` - Product catalog (3 sample products)
- `product_categories` - Product categories
- `product_types` - Product types
- `product_orders` - Product orders
- `sku` - Stock keeping units
- And more...

#### Coupons & Discounts (5)
- `coupon` - Discount coupons (2 sample coupons)
- `coupon_vendor` - Coupon-vendor mappings
- `coupons_applied` - Applied coupons history
- `coupon_has_days` - Day-based coupon restrictions
- `coupon_has_time_slots` - Time-based coupon restrictions

#### WhatsApp & AI Integration (14)
- `whatsapp_*` tables - WhatsApp integration (7 tables)
- `aisensy_*` tables - AiSensy integration (7 tables)
- `bot_sessions` - Bot conversation sessions

#### Reviews & Social (5)
- `shop_review` - Customer reviews (2 sample reviews)
- `shop_likes` - Favorite shops (3 sample likes)
- `reels` - Video reels
- `reels_likes`, `reels_view_counts`, etc.

#### Banners & Marketing (5)
- `banner` - Marketing banners (2 sample banners)
- `banner_timings`, `banner_logs`, etc.

#### Payment & Wallet (2)
- `wallet` - User wallets (2 with balances)
- `guest_user_deposits` - Guest deposits

#### Support & Help (4)
- `support_category` - Help categories (3 categories)
- `support_solution` - Help articles (2 solutions)
- `support_tickets` - Support tickets
- `support_tickets_has_files` - Ticket attachments

#### And 60+ More Tables for:
- Store management
- Staff scheduling
- Notifications
- Quiz/Surveys
- Memberships
- Vendor management
- Analytics
- And more...

## Sample Data Included

### Users (5)
1. **Admin** - admin@example.com / admin (Password: Use Yii2 hash)
2. **Vendor 1** - vendor1@example.com / Glamour Beauty Salon
3. **Vendor 2** - vendor2@example.com / Style Studio  
4. **Customer 1** - customer1@example.com
5. **Customer 2** - customer2@example.com

### Vendors (2)
1. **Glamour Beauty Salon** - Mumbai (Rating: 4.5)
2. **Style Studio** - Delhi (Rating: 4.7)

### Orders (2)
1. Order #ORD20240001 - ₹3,407.50 (Completed)
2. Order #ORD20240002 - ₹761.50 (Completed)

### Services (4)
- Facial Treatment - ₹1,500
- Hair Spa - ₹2,000
- Haircut (Men) - ₹300
- Haircut (Women) - ₹500

## Installation Instructions

### Step 1: Create Database
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
EXIT;
```

### Step 2: Import SQL File
```bash
# Import the complete database
mysql -u root -p your_database_name < complete_test_database.sql
```

### Step 3: Configure Yii2
Update your `/config/db.php`:

```php
return [
    'class' => 'yii\\db\\Connection',
    'dsn' => 'mysql:host=localhost;dbname=your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
];
```

### Step 4: Verify Installation
```bash
# Count tables
mysql -u root -p your_database_name -e "SHOW TABLES;" | wc -l

# Should show 118 (117 tables + 1 header line)
```

## Test Credentials

### Admin Login
- **Username**: admin
- **Email**: admin@example.com
- **Password Hash**: $2y$13$ZqHfgZ1g8k7N8JHw9J7fCe6bvF8qTz2b9v8N3j1Y2hH7dF5qR4tZe
  *(You may need to reset this using Yii2's password hash generator)*

### Vendor Login
- **Username**: vendor1 or vendor2  
- **Emails**: vendor1@example.com, vendor2@example.com

### Customer Login
- **Username**: customer1 or customer2
- **Emails**: customer1@example.com, customer2@example.com

## Sample Data Features

✓ **5 Users** across different roles (Admin, Vendors, Customers)  
✓ **2 Vendors** with complete business profiles  
✓ **4 Services** ready for booking  
✓ **2 Completed Orders** with payment details  
✓ **3 Staff Members** assigned to vendors  
✓ **2 Active Coupons** with vendor mappings  
✓ **2 Customer Reviews** with ratings  
✓ **3 Favorite Shops** (likes)  
✓ **2 Banners** for marketing  
✓ **5 Cities** for location services  
✓ **3 Support Categories** with 2 solutions  
✓ **User Wallets** with balances  
✓ **Store Timings** configured  
✓ **Days of Week** reference data  
✓ **Order Status** definitions  

## Customization

### Adding More Data
You can add more dummy data by:

1. **Manual INSERT statements**:
```sql
INSERT INTO `user` (full_name, email, username, password_hash, status, created_on) 
VALUES ('New User', 'newuser@example.com', 'newuser', 'hash', 10, NOW());
```

2. **Using PHP scripts** with Faker library
3. **Through the Yii2 admin panel** once the app is running

### Modifying Tables
If you need to add/modify tables:

1. Update the model files in `/modules/admin/models/base/`
2. Run migrations if available
3. Or manually ALTER tables

## Troubleshooting

### Foreign Key Errors
If you get foreign key errors during import:
```sql
SET FOREIGN_KEY_CHECKS=0;
-- Your SQL operations
SET FOREIGN_KEY_CHECKS=1;
```

### Character Encoding Issues
Ensure your MySQL connection uses UTF-8:
```bash
mysql -u root -p --default-character-set=utf8mb4
```

### Large File Import
If the file is too large:
```bash
# Increase max_allowed_packet
mysql -u root -p -e "SET GLOBAL max_allowed_packet=1073741824;"

# Then import
mysql -u root -p your_database_name < complete_test_database.sql
```

## Database Relationships

### Key Foreign Keys:
- `user_id` → References `user.id` (in most tables)
- `vendor_details_id` → References `vendor_details.id` (in vendor-related tables)
- `order_id` → References `orders.id` (in order-related tables)
- `service_id` → References `services.id` (in service-related tables)
- `create_user_id` → References `user.id` (audit trail)

## Next Steps

1. ✓ Import the database
2. ✓ Configure Yii2 db.php
3. ✓ Test admin login
4. ✓ Browse vendor listings
5. ✓ Create test orders
6. ✓ Test payment flows
7. ✓ Review order management
8. ✓ Test customer features

## Support

If you encounter any issues:
1. Check MySQL error logs
2. Verify table creation: `SHOW TABLES;`
3. Check foreign keys: `SHOW CREATE TABLE table_name;`
4. Verify data: `SELECT COUNT(*) FROM table_name;`

## Notes

- All passwords use Yii2's `password_hash()` format
- Sample auth_key values are for testing only
- Replace with real data before production
- Some tables may be empty (waiting for user actions)
- Foreign key constraints are enabled
- UTF-8 encoding is used throughout

---

**Generated**: 2025-11-11  
**Total Tables**: 117  
**With Dummy Data**: 20+ tables  
**Sample Records**: 50+  
**Ready for Testing**: ✓  
**File Size**: 78KB

Enjoy testing your application! 🚀

