# Campus Events Management System (PHP)

PHP/MySQL web app for managing campus events with participants, organizers, and admins. Includes email verification, event management, participant communications, and PDF attendance attestations.

## Project Structure

```
Mini-Projet/
├── api/                    # AJAX/JSON endpoints
│   ├── admin.php
│   ├── auth.php
│   └── events.php
├── assets/
│   └── js/
│       ├── admin-panel.js
│       └── organizer-dashboard.js
├── classes/                # Domain models (PDO)
│   ├── Account.php
│   ├── Admin.php
│   ├── Club.php
│   ├── Event.php
│   ├── Organizer.php
│   └── Participant.php
├── config/
│   ├── database.php        # DB connection (PDO)
│   └── mail.php            # SMTP configuration
├── database/
│   └── schema.sql          # Full schema (accounts, participants, events, registered, attestations, pending_signups,...)
├── includes/
│   └── session.php         # Session helpers and role guards
├── services/
│   ├── AttestationPdfService.php  # Dompdf-based certificate generator
│   ├── EmailVerification.php      # VerifyEmail + send verification links
│   └── Mailer.php                 # PHPMailer wrapper
├── vendor/                 # Composer dependencies (autoloaded)
├── admin-panel.php
├── index.php
├── login.php
├── organizer-dashboard.php
├── profile.php
└── signup.php
```

## Setup

1) Database
- Create MySQL DB `campus_events` (e.g., via phpMyAdmin).
- Import `database/schema.sql`.

2) PHP Dependencies
```bash
composer install
composer require phpmailer/phpmailer dompdf/dompdf verifyemail/verifyemail
```

3) Config
- `config/database.php`: adjust host/name/user/pass.
- `config/mail.php`: set SMTP host, port, security, username/password, from_email/from_name.

4) Default Admin
- Email: `admin@campus.edu`
- Password: `Admin123!`

## How It Works (Key Flows)

- Authentication and Roles
  - Accounts live in `accounts`; participants in `participants`; admins in `admins`.
  - `includes/session.php` manages sessions and role checks.

- Signup with Email Verification
  - Frontend posts `action=request_signup` to `api/auth.php`.
  - Email format/SMTP verification via `VerifyEmail` then store in `pending_signups` with token.
  - Verification email is sent using `services/Mailer.php`.
  - Clicking the link hits `api/auth.php?action=verify_signup&token=...` which creates the real `accounts` and `participants` rows, then deletes the pending record.
  - Year logic: Years 1–2 require no department/filière; Years 3–5 require filière (mapped to `department`); Graduate requires department.

- Organizer Event Management
  - Organizers create/update/delete events they own; ownership enforced via `events.created_by`.
  - Participants modal shows registrants; organizers can select attendees and send attestations.

- Emailing Attestations
  - UI selects participants and posts `action=send_attestations` to `api/events.php`.
  - API validates organizer ownership, generates a PDF per selected participant via Dompdf, stores paths in `attestations`, and emails with PHPMailer.

## Features

- Accounts and Roles
  - Login/logout, sessions, role checks (participant, organizer, admin).
  - Email verification at signup using `VerifyEmail` and tokenized links.

- Events
  - Create, edit, delete events (organizer-owned).
  - Public listing, registration, participant counts.

- Participants Management
  - Organizer-only participant list per event.
  - Select and email attendees with attached PDF attestations.

- Certificates (Attestations)
  - Dompdf-based, dark-blue theme, centered layout.
  - Stored under `storage/attestations/{event_id}/...pdf` and recorded in DB.

## Roadmap / To Add

- Admin dashboards for reports and metrics.
- ReCaptcha v2/3 integration (replace placeholder checkbox).
- Unit/integration tests.

## Development Notes

- Autoload: Composer’s `vendor/autoload.php` is included by APIs.
- Security: Prepared statements everywhere; passwords hashed with bcrypt.
- Styling: Tailwind CDN for simple pages; minimal JS with Fetch API.
