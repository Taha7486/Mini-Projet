# Campus Events Management System (PHP)

PHP/MySQL web app for managing campus events with participants, organizers, and admins. Includes email verification, event management, participant communications, and PDF attendance attestations.

## Project Structure

```
Mini-Projet/
├── public/
│   ├── about.php
│   ├── admin-panel.php
│   ├── login.php
│   ├── organizer-dashboard.php
│   ├── profile.php
│   └── signup.php
│
├── index.php
│
├── api/
│   ├── admin.php
│   ├── auth.php
│   ├── events.php
│   └── upload.php
│
├── assets/
│   ├── images/
│   │   ├── logo.svg
│   │   └── logo-all_caps.svg
│   └── js/
│       ├── admin-panel.js
│       └── organizer-dashboard.js
│
├── classes/
│   ├── Account.php
│   ├── Admin.php
│   ├── Club.php
│   ├── Event.php
│   ├── Organizer.php
│   └── Participant.php
│
├── config/
│   ├── captcha.php
│   ├── database.php
│   └── mail.php
│
├── database/
│   └── schema.sql
│
├── includes/
│   ├── footer.php
│   ├── header.php
│   └── session.php
│
├── services/
│   ├── AttestationPdfService.php
│   ├── EmailVerification.php
│   └── Mailer.php
│
├── storage/
│   └── event_images/
│       ├── no_image_placeholder.png
│       └── no_image_placeholder-white_background.png
│
├── vendor/
│   ├── autoload.php
│   ├── dompdf/
│   ├── google/recaptcha/
│   ├── phpmailer/
│   └── ... (other dependencies)
│
├── composer.json
├── composer.lock
└── README.md
```

## Setup

1) Database
- Create MySQL DB `campus_events` (e.g., via phpMyAdmin).
- Import `database/schema.sql`.

2) Config
- `config/database.php`: adjust host/name/user/pass.

3) Default Admin
- Email: `admin@campus.edu`
- Password: `Admin123!`


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

## To Add

- Admin dashboards for reports and metrics.
- ReCaptcha v2/3 integration .
- Unit/integration tests.
- Deploy the site .

