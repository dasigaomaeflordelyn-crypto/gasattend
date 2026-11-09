GASATTEND — Student Capstone Starter
====================================

FEATURES
- Login and logout with password hashing
- First administrator setup
- Employee add, edit, search, activate/deactivate
- Attendance entry by employee and date
- Statuses: Present, Late, Absent, On Leave, Rest Day
- One attendance record per employee per date (saving again updates that record)
- Dashboard counts and recent records
- Date-range reports, print, and CSV export
- Prepared statements, output escaping, and CSRF form tokens

REQUIREMENTS
- XAMPP (Apache, PHP, MySQL/MariaDB) or another PHP/MySQL environment
- Modern browser
- PHP with PDO MySQL enabled

INSTALL WITH XAMPP
1. Extract the gasattend folder into C:\xampp\htdocs\
   Expected path: C:\xampp\htdocs\gasattend\
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin/
4. Import database.sql (select Import and choose the file).
5. Check config.php. XAMPP's default MySQL user is often root with a blank password.
   If your database has a password, update DB_PASS in config.php.
6. Open http://localhost/gasattend/setup_admin.php
7. Create your first administrator account. Use a strong password of at least 10 characters.
8. Delete setup_admin.php after creating the first account.
9. Open http://localhost/gasattend/login.php and sign in.
10. Add employee profiles, then start entering attendance.

IMPORTANT NOTES
- This is a learning prototype, not a production-ready HR/payroll system.
- Use fictional/sample data while developing. Do not upload real employee data to a public host without the station's authorization and appropriate privacy/security review.
- Do not deploy publicly without HTTPS, secure server configuration, restricted database access, backups, and a review of authentication/session settings.
- The prototype allows users with accounts to access the app; it does not yet provide a full user-management screen or per-role permission matrix.
- Overnight shifts are not fully supported by the basic time validation. Agree on the station's shift rules before using real data.
- An absent status is a manual designation, not an automatic conclusion from a missing record.
- The dashboard's present count includes Present and Late statuses. Confirm whether this is how the client wants summaries calculated.
- PHP and MySQL versions may differ between environments; test all pages locally before presenting.

FILES
config.php       Database connection and shared helpers
database.sql     Database schema
setup_admin.php  One-time initial account setup (delete after use)
login.php        Login page
logout.php       Logout action
index.php        Dashboard homepage
employees.php    Employee management
attendance.php   Attendance entry and daily list
reports.php      Date-range report, print, CSV export
style.css        Responsive design
app.js           Confirm dialogs and CSV export
