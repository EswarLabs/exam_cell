# NBKRIST Exam Cell Notification System

Simple PHP + MySQL project for:

- Admin login and notification management
- Public exam notification board with search
- Optional PDF attachments per notification

## Current Project Structure

```text
exam_cell/
├── dashboard.php                # Admin panel (post / toggle / delete)
├── database_setup.sql           # DB schema + default admin seed
├── index.php                    # Redirects to dashboard
├── login.php                    # Admin login
├── logout.php                   # Session logout
├── notification_board.php       # Public notification board
├── includes/
│   ├── .htaccess                # Blocks direct includes access
│   ├── auth.php                 # Auth/session helpers
│   └── db_config.php            # DB + upload config
├── uploads/
│   ├── .gitkeep
│   └── notifications/           # Uploaded PDFs
├── .gitignore
└── README.md
```

## Feature Summary

- Secure admin login with hashed password verification
- Post new notifications with optional PDF upload
- Toggle active/inactive status
- Delete notification + remove uploaded PDF file
- Public board lists only active notifications
- Live search on public notification board

## Setup (Local / XAMPP)

1. Place project in `c:\xampp\htdocs\exam_cell`
2. Create MySQL database (default name used in config: `examcell`)
3. Import `database_setup.sql`
4. Update credentials in `includes/db_config.php`:
   - `DB_HOST`
   - `DB_USER`
   - `DB_PASS`
   - `DB_NAME`
5. Ensure upload path exists and is writable:
   - `uploads/notifications/`

## Default Admin Login

- Username: `examcell`
- Password: `password`

Change this immediately after first login in production.

## Routes

- `login.php` → Admin login page
- `dashboard.php` → Admin dashboard (requires login)
- `notification_board.php` → Public board
- `logout.php` → Logout
- `index.php` → Redirects to `dashboard.php`

## Embed Public Board

Use this include where you want the board to appear:

```php
<?php include 'exam_cell/notification_board.php'; ?>
```

## Deployment Checklist

- Update DB credentials in `includes/db_config.php`
- Update `UPLOAD_URL` in `includes/db_config.php` to match your domain path
- Set strong admin password (replace default)
- Confirm `uploads/notifications/` write permission
- Verify HTTPS and session behavior on production
- Confirm `includes/.htaccess` is active on server

## Troubleshooting

- **Database connection failed** → verify DB constants in `includes/db_config.php`
- **PDF upload fails** → check folder permission and `UPLOAD_DIR` path
- **Login fails** → verify admin row exists in `exam_cell_admins`
- **PDF links broken** → verify `UPLOAD_URL` matches deployed path

## Security Notes

- Passwords are verified via `password_verify` (bcrypt hash in DB)
- Inputs are sanitized/escaped before output
- DB inserts use prepared statements
- Uploads are restricted to PDF and size-limited

## Final Cleanup Notes

- Removed outdated environment-template based instructions from docs
- README now matches current file structure and active configuration model
- Keep credentials out of public repositories before production deployment
