# Campus Events Management System - PHP Version

This is a PHP/MySQL implementation of the Campus Events Management System.

## Setup Instructions

### 1. Database Setup
1. Open PHPMyAdmin
2. Create a new database named `campus_events`
3. Import the SQL schema from `/php-version/database/schema.sql`

### 2. Configuration
- Edit `/php-version/config/database.php` if needed (default: localhost, root, no password)

### 3. Default Admin Account
- Email: `admin@campus.edu`
- Password: `Admin123!`

## File Structure

```
/php-version/
├── classes/           # PHP Classes (OOP)
│   ├── Account.php
│   ├── Participant.php
│   ├── Admin.php
│   ├── Organizer.php
│   ├── Club.php
│   └── Event.php
├── config/
│   └── database.php   # Database connection
├── database/
│   └── schema.sql     # MySQL schema
├── includes/
│   ├── session.php    # Session management
│   └── header.php     # Common header
├── api/               # AJAX endpoints
│   ├── auth.php
│   ├── events.php
│   ├── clubs.php
│   └── participants.php
├── assets/
│   ├── css/
│   └── js/
├── index.php          # Public events page
├── login.php
├── signup.php
├── profile.php
├── organizer-dashboard.php
└── admin-panel.php
```

## User Roles

### Person (Base)
- Create account (with email validation & CAPTCHA)
- Login
- View events
- Register for events (requires login)

### User (Participant)
- All Person capabilities
- Request to become organizer
- View registered events

### Organizer
- All User capabilities
- Create/Modify/Delete events
- View event participants
- Send emails to participants
- Generate participation certificates

### Admin
- Approve/Reject organizer requests
- Manage clubs (create/delete)
- Manage user accounts
- Create new admins

## Features

1. **Authentication System**
   - Secure login with password hashing (bcrypt)
   - Session management
   - Role-based access control

2. **Event Management**
   - Public event listing
   - Event registration (logged-in users)
   - Event filtering by club
   - Event details view

3. **Organizer Features**
   - Create events for assigned clubs
   - Edit/Delete own events
   - View participant list
   - Email participants
   - Generate certificates

4. **Admin Features**
   - Club management
   - Organizer request approval
   - User management
   - Admin creation

## Technology Stack

- **Backend**: PHP 7.4+ (OOP with Classes)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **AJAX**: Fetch API for dynamic interactions

## Security Features

- Password hashing with bcrypt
- SQL injection prevention (PDO prepared statements)
- XSS protection
- CSRF tokens (to be implemented)
- Session security
- Input validation

## Next Steps

To complete the implementation, create:
1. Main HTML pages (index, login, signup, etc.)
2. API endpoints for AJAX calls
3. JavaScript for dynamic interactions
4. Email integration (optional)
5. PDF generation for certificates (optional)
