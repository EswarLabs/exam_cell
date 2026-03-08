# NBKRIST Exam Cell

Admin dashboard for managing exam notifications and announcements.

## Project Structure

```
exam_cell/
├── login.php                  Admin login
├── dashboard.php              Admin dashboard
├── logout.php                 Logout handler
├── notification_board.php     Public board (embeddable)
├── database_setup.sql         Initial database schema
├── includes/
│   ├── db_config.php          Database configuration (DO NOT COMMIT)
│   ├── auth.php               Authentication helpers
│   └── .htaccess              Blocks direct access to includes/
├── uploads/
│   └── notifications/         PDF storage (auto-created)
├── .env.example               Environment template
├── .gitignore                 Git ignore rules
└── README.md                  This file
```

## Quick Start

### 1. Prerequisites

- PHP 7.4+
- MySQL 5.7+
- Apache with mod_rewrite

### 2. Installation

1. Clone/upload to `public_html/exam_cell/`
2. Copy `db_config.php.example` to `includes/db_config.php`
3. Edit `includes/db_config.php` with your database credentials
4. Import `database_setup.sql` to your MySQL database

### 3. Login

1. Update username and password(hashed) in database

## Features

- **Admin Dashboard:** Manage notifications with rich editor
- **PDF Attachments:** Upload & serve PDF files
- **Public Board:** Embeddable notification widget
- **Session Management:** Secure authentication with bcrypt
- **File Handling:** Auto-cleanup for deleted notifications

## Configuration

Create `.env` to add:

- Database credentials
- Upload directory paths
- Session name

## Deployment

See `.env.example` for configuration variables needed on production

## Step 1 — Set Folder Permissions

In cPanel File Manager, right-click the `uploads/notifications/` folder → **Change Permissions** → set to **755**.

---

## Step 2 — Access the Admin Panel

Visit: `https://yourcollegedomain.com/exam_cell/login.php`

Log in with the credentials.

---

## Step 3 — Embed the Notification Board on Your Website

Open whichever PHP page you want to show notifications on (e.g., `examinations.php`) and add this one line where you want the board to appear:

```php
<?php include 'exam_cell/notification_board.php'; ?>
```

That's it. The board will automatically show all active notifications with their PDF links.

---

## How It Works (Summary)

| Feature                | How                                                    |
| ---------------------- | ------------------------------------------------------ |
| Admin login            | Session-based, password hashed with bcrypt             |
| Post notification      | Form in dashboard → saved to MySQL                     |
| Upload PDF             | Uploaded to`/uploads/notifications/`, path saved in DB |
| Public board           | `notification_board.php` reads active rows from DB     |
| Delete notification    | Removes from DB + deletes PDF file from server         |
| Hide/show notification | Toggle`is_active` flag (PDF stays on server)           |

---

## Troubleshooting

| Problem                      | Solution                                           |
| ---------------------------- | -------------------------------------------------- |
| "Database connection failed" | Check credentials in`db_config.php`                |
| PDF upload fails             | Check`/uploads/notifications/` has 755 permissions |
| Can't access login page      | Make sure`exam_cell/` is inside `public_html/`     |
| Login always fails           | Re-run the SQL insert from`database_setup.sql`     |

---

## Security Notes

- Passwords are stored as **bcrypt hashes** — never plain text.
- The `/includes/` folder is blocked by `.htaccess`.
- File uploads are restricted to `.pdf` only, with a 5 MB limit.
- All user input is escaped with `htmlspecialchars()` and `prepared statements`.
