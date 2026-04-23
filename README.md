# 🎓 EventsHub — Campus Events Management System

A full-featured **PHP / MySQL** web application for managing campus events across university clubs. Students can discover and register for events, organizers can create and manage them, and administrators oversee the entire platform — all with email verification, CAPTCHA protection, PDF attendance certificates, and a CI/CD deployment pipeline.

---

## ✨ Key Features

### 🔐 Authentication & Security
- **Email-verified signup** — new accounts receive a tokenized verification link before activation (via `pending_signups` staging table).
- **Bcrypt password hashing** — all passwords stored with `PASSWORD_BCRYPT`.
- **hCaptcha integration** — login and signup forms are protected against bots (configurable site/secret keys).
- **Session-based role system** — helper functions (`isLoggedIn`, `isAdmin`, `isOrganizer`, `isUser`) gate every action.
- **Input validation** — server-side regex checks on names, student IDs (8 digits), passwords (uppercase + digit), and emails.

### 📅 Event Management
- **Create / Edit / Delete events** — organizers manage events scoped to the clubs they belong to.
- **Image uploads** — event banners (JPG/PNG/WebP, max 5 MB) stored in `storage/event_images/`.
- **Capacity tracking** — real-time registered count with progress bar and "spots left" indicator.
- **Status badges** — events automatically display as *Upcoming*, *Ongoing*, or *Completed* based on current time.
- **Search & filter** — live client-side filtering by keyword, club, and status on the homepage.
- **Event detail modal** — full event info in a rich modal overlay with date/time, location, capacity, and registration button.

### 👥 Role-Based Access Control
| Role | Capabilities |
|---|---|
| **Participant** (user) | Browse events, register, view profile, request organizer access |
| **Organizer** | All participant actions + create/edit/delete events for assigned clubs, view participants, send emails & attestations |
| **Admin** | Full platform control — manage users/clubs/events, approve/reject organizer requests, create admin accounts |

### 🏛️ Club & Organizer System
- Admin-managed clubs with name, description, and organizer/event counts.
- Users request organizer access for specific clubs from their profile page.
- Admins approve or reject requests with a full audit trail (decided_by, decided_at).
- Organizers can manage multiple clubs and all events within them.

### 📧 Email & Communications
- **SMTP via PHPMailer** — configurable Gmail / custom SMTP with TLS support.
- **Custom emails** — organizers compose and send personalized emails to selected participants with template variables (`{name}`, `{event_title}`, `{event_date}`, `{event_time}`, `{event_location}`).
- **File attachments** — upload and attach files (PDF, DOCX, TXT, JPG, PNG; max 10 MB each) to outgoing emails.
- **Email history** — every email (custom or attestation) is logged in the `email_history` table with recipient/sent/failed counts.
- **Verification emails** — tokenized signup confirmation emails.

### 📜 PDF Attendance Certificates (Attestations)
- **Dompdf-generated** landscape A4 certificates with a dark-blue formal design.
- Personalized with participant name, event title, date/time, location, organizer signature, and issue date.
- Stored under `storage/attestations/{event_id}/` and recorded in the `attestations` table.
- Emailed to selected participants as PDF attachments.

### 🛠️ Admin Panel
- **User management** — view all users, change roles, delete accounts, toggle active status.
- **Club management** — create, edit, delete clubs; view organizers per club.
- **Event oversight** — edit or delete any event, view event participants.
- **Organizer requests** — approve/reject pending requests with decision history.
- **Admin creation** — create additional admin accounts.

### 📱 Responsive Design
- TailwindCSS-powered responsive layout with mobile hamburger menu.
- Sticky header with dynamic navigation links based on user role.
- Card-based event grid (1 / 2 / 3 columns) adapting to screen size.

### 🚀 CI/CD Deployment
- GitHub Actions workflow auto-deploys to InfinityFree via FTP on push to `main`.
- Excludes sensitive directories (`config/`, `vendor/`, `.github/`) from deployment.

---

## 📁 Project Structure

```
EventsHub/
│
├── index.php                          # Homepage — event listing with search/filter/modal
│
├── public/                            # User-facing pages
│   ├── login.php                      # Login form with hCaptcha
│   ├── signup.php                     # Registration form with email verification
│   ├── email-verified.php             # Post-verification success page
│   ├── profile.php                    # User profile + organizer request form
│   ├── organizer-dashboard.php        # Organizer event management dashboard
│   ├── admin-panel.php                # Admin control panel
│   └── about.php                      # About / features / contact page
│
├── api/                               # JSON REST-like endpoints (POST only)
│   ├── auth.php                       # login, signup, request_signup, verify_signup, logout
│   ├── events.php                     # CRUD events, register, send emails/attestations, email history
│   ├── admin.php                      # Club/user/event admin actions, organizer request handling
│   └── upload.php                     # File upload for email attachments
│
├── classes/                           # OOP data-access layer
│   ├── Account.php                    # Base account (register, login, getByEmail)
│   ├── Participant.php                # extends Account — student profile, event registration, club requests
│   ├── Organizer.php                  # extends Participant — event CRUD, participant views, club management
│   ├── Admin.php                      # extends Account — full admin operations
│   ├── Event.php                      # Event queries (getAll, getById, getByClub)
│   └── Club.php                       # Club CRUD and organizer assignment
│
├── services/                          # Business-logic services
│   ├── Mailer.php                     # PHPMailer SMTP wrapper with mail() fallback
│   ├── EmailVerification.php          # Token-based email verification + send verification link
│   └── AttestationPdfService.php      # Dompdf PDF certificate generator
│
├── config/                            # Configuration files (excluded from deployment)
│   ├── database.php                   # MySQL PDO connection (host, db, user, pass)
│   ├── mail.php                       # SMTP settings (host, port, credentials, from)
│   └── captcha.php                    # hCaptcha site_key and secret_key
│
├── includes/                          # Shared PHP partials
│   ├── header.php                     # Sticky navbar with role-based links + mobile menu
│   ├── footer.php                     # Page footer
│   └── session.php                    # Session helpers (isLoggedIn, isAdmin, isOrganizer, etc.)
│
├── database/
│   └── schema.sql                     # Full database schema + seed data (admin + sample clubs)
│
├── assets/
│   ├── images/                        # Logo SVGs and image placeholders
│   │   ├── logo.svg
│   │   ├── logo-all_caps.svg
│   │   ├── no_image_placeholder.png
│   │   └── no_image_placeholder-white_background.png
│   └── js/
│       ├── admin-panel.js             # Admin panel client-side logic
│       └── organizer-dashboard.js     # Organizer dashboard client-side logic
│
├── storage/                           # Runtime file storage (gitignored contents)
│   ├── event_images/                  # Uploaded event banner images
│   ├── attestations/                  # Generated PDF certificates
│   └── email_attachments/             # Uploaded email attachment files
│
├── .github/
│   └── workflows/
│       └── deploy.yml                 # GitHub Actions FTP deployment to InfinityFree
│
├── vendor/                            # Composer dependencies (auto-generated)
├── composer.json
├── composer.lock
└── README.md
```

---

## 🗄️ Database Schema

The application uses a MySQL database named `campus_events` with the following tables:

| Table | Purpose |
|---|---|
| `accounts` | Base user accounts (id, name, email, hashed password) |
| `participants` | Student profiles linked to accounts (student ID, year, department, phone, role) |
| `admins` | Admin records linked to accounts |
| `clubs` | Campus clubs created by admins |
| `organizers` | Many-to-many link between participants and clubs |
| `organizer_requests` | Organizer role requests with status tracking (pending/approved/rejected) |
| `events` | Events with title, description, date/time, location, capacity, image, club link |
| `registered` | Event registrations (participant ↔ event) |
| `attestations` | Generated PDF certificate records per registration |
| `pending_signups` | Temporary storage for unverified signups with verification tokens |
| `email_history` | Log of all sent emails (custom + attestation) with delivery stats |

All tables use **InnoDB** with **utf8mb4** charset and proper foreign key cascades.

---

## ⚙️ Setup & Installation

### Prerequisites
- **PHP 8.0+** with PDO, cURL, and mbstring extensions
- **MySQL 5.7+** / MariaDB
- **Composer** (for dependency management)
- **XAMPP** / WAMP / LAMP or similar local server stack

### 1. Clone the Repository
```bash
git clone https://github.com/Taha7486/Mini-Projet.git
cd Mini-Projet
```

### 2. Install Dependencies
```bash
composer install
```
This installs:
- `phpmailer/phpmailer` (^6.11) — SMTP email sending
- `dompdf/dompdf` (^3.1) — PDF certificate generation
- `google/recaptcha` (^1.3) — reCAPTCHA library (available but hCaptcha is currently used)

### 3. Create the Database
1. Create a MySQL database named `campus_events`.
2. Import the schema:
   ```bash
   mysql -u root -p campus_events < database/schema.sql
   ```
   Or import `database/schema.sql` via phpMyAdmin.

### 4. Configure the Application

**Database** — Edit `config/database.php`:
```php
private $host = "localhost";
private $db_name = "campus_events";
private $username = "root";
private $password = "";
```

**Email (SMTP)** — Edit `config/mail.php`:
```php
'host' => 'smtp.gmail.com',
'port' => 587,
'sMTPSecure' => 'tls',
'sMTPAuth' => true,
'username' => 'your-email@gmail.com',
'password' => 'your-app-password',
'from_email' => 'your-email@gmail.com',
'from_name' => 'Campus Events',
```

**CAPTCHA** — Edit `config/captcha.php`:
```php
'site_key' => 'YOUR_HCAPTCHA_SITE_KEY',
'secret_key' => 'YOUR_HCAPTCHA_SECRET_KEY',
```

### 5. Run the Application
Start your Apache and MySQL servers (e.g., via XAMPP), then navigate to:
```
http://localhost/Mini-Projet/
```

### 6. Default Admin Account
| Field | Value |
|---|---|
| Email | `admin@campus.edu` |
| Password | `Admin123!` |

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8 (vanilla, OOP) |
| **Database** | MySQL / MariaDB with PDO |
| **Frontend** | HTML5, TailwindCSS (CDN), JavaScript (vanilla) |
| **Icons** | Font Awesome 6 |
| **Email** | PHPMailer (SMTP) |
| **PDF Generation** | Dompdf |
| **CAPTCHA** | hCaptcha |
| **Deployment** | GitHub Actions → FTP (InfinityFree) |
| **Dependencies** | Composer |

---

## 📡 API Endpoints

All API endpoints accept **POST** requests with JSON body (except file uploads which use `multipart/form-data`).

### `api/auth.php`
| Action | Description |
|---|---|
| `login` | Authenticate user (email + password + hCaptcha) |
| `request_signup` | Create pending signup + send verification email |
| `verify_signup` | Verify email token and create account |
| `signup` | Direct signup (without email verification) |
| `logout` | Destroy session and redirect |

### `api/events.php`
| Action | Description |
|---|---|
| `create` | Create event (organizer only) |
| `update` | Update event (organizer or admin) |
| `delete` | Delete event (organizer or admin) |
| `register` | Register for an event (participant only) |
| `get_participants` | List event participants (organizer/admin) |
| `send_emails` | Send attendance emails to participants |
| `send_attestations` | Generate & email PDF certificates |
| `send_custom_email` | Send custom email with template variables |
| `get_email_history` | Retrieve sent email history |

### `api/admin.php`
| Action | Description |
|---|---|
| `request_organizer` | User requests organizer access |
| `approve_organizer_request` | Admin approves request |
| `reject_organizer_request` | Admin rejects request |
| `create_club` / `update_club` / `delete_club` | Club CRUD |
| `change_user_role` | Change participant role |
| `create_admin` | Create new admin account |
| `delete_user` | Delete user account |
| `toggle_user_status` | Activate/deactivate user |
| `update_event` / `delete_event` | Admin event management |
| `get_event_participants` | View participants for any event |

### `api/upload.php`
| Action | Description |
|---|---|
| POST with `attachments[]` | Upload files for email attachments (PDF, DOCX, TXT, JPG, PNG; max 10 MB) |
