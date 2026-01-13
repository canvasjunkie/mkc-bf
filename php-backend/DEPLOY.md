# PHP Backend Deployment Guide

## Files to Upload

Upload the entire `php-backend` folder contents to `pay.memorykeep.cloud`:

```
pay.memorykeep.cloud/
├── .htaccess
├── index.php
├── config.php
├── signup.php
├── login.php
├── logout.php
├── payment.php
├── activate.php
├── dashboard.php
└── api/
    ├── status.php
    └── use-message.php
```

## Database Setup

1. Open phpMyAdmin for `u649168233_bot_members`
2. Run the SQL from `schema.sql`:

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    tier ENUM('free', 'starter', 'pro') DEFAULT 'free',
    paypal_subscription_id VARCHAR(100) NULL,
    subscription_status ENUM('active', 'cancelled', 'expired', 'pending') DEFAULT 'active',
    messages_used INT DEFAULT 0,
    messages_reset_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Landing Page Links

Update your memorykeep.cloud CTAs:

| Button | Link To |
|--------|---------|
| Try Free | `https://pay.memorykeep.cloud/signup.php?plan=free` |
| Get Starter | `https://pay.memorykeep.cloud/signup.php?plan=starter` |
| Get Pro | `https://pay.memorykeep.cloud/signup.php?plan=pro` |

## API Endpoints

Bot Factory can use these APIs:

- **Check Status**: `GET https://pay.memorykeep.cloud/api/status.php?email=user@example.com`
- **Use Message**: `POST https://pay.memorykeep.cloud/api/use-message.php` with `{"email": "..."}`
