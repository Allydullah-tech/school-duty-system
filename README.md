# SchoolDuty Management System

A modern, full-stack school management system built with PHP (backend) and vanilla HTML/CSS/JS (frontend).

---

## Project Structure

```
school-duty-system/
├── frontend/
│   ├── index.html              Landing page
│   ├── login.html              Login (Admin / Staff / Student)
│   ├── reset-password.html     Admin password recovery
│   ├── admin.html              Admin dashboard
│   ├── academician.html        Academician dashboard
│   ├── teacher.html            Teacher dashboard
│   ├── student.html            Student dashboard
│   └── assets/
│       ├── css/style.css       Shared styles (dark theme)
│       └── js/api.js           API helper + shared utilities
│
└── backend/
    ├── schema.sql              Full database schema — run this first
    ├── config/
    │   └── db.php              DB connection, JWT helpers, utility functions
    ├── auth/
    │   ├── login.php
    │   ├── logout.php
    │   ├── change_password.php
    │   ├── admin_create_user.php
    │   ├── admin_reset_user_password.php
    │   ├── admin_set_security.php
    │   └── reset_password.php  (3-step: check email → verify answer → reset)
    ├── term_duties/            ★ NEW — 3-month auto-roster
    │   ├── generate.php        POST — academician generates full term schedule
    │   ├── get_all.php         GET  — full schedule (all roles)
    │   ├── get_mine.php        GET  — teacher's own weeks
    │   ├── reassign.php        POST — academician manually edits a week
    │   └── update_status.php   POST — mark week pending/ongoing/completed
    ├── swap/                   ★ UPDATED — teacher → academician workflow
    │   ├── request.php         POST — teacher sends swap request
    │   ├── get.php             GET  — list requests (role-filtered)
    │   ├── respond_academician.php  POST — academician approves + reassigns
    │   └── respond.php         POST — teacher-to-teacher (legacy)
    ├── duties/                 Individual ad-hoc duties
    │   ├── create.php
    │   ├── delete.php
    │   ├── get_all.php
    │   ├── get_mine.php
    │   └── update_status.php
    ├── special_tasks/          Academician → teacher special assignments
    │   ├── create.php
    │   ├── delete.php
    │   ├── get_all.php
    │   ├── get_mine.php
    │   └── update_status.php
    ├── tasks/                  Teacher → all students task board
    │   ├── create.php
    │   ├── get_all.php
    │   ├── submit.php
    │   ├── grade.php
    │   ├── submissions.php
    │   ├── submissions_all.php
    │   └── my_submissions.php
    ├── timetable/
    │   ├── create.php
    │   ├── get.php
    │   └── delete.php
    ├── roster/
    │   ├── submit.php
    │   └── get_all.php
    ├── users/
    │   ├── get_all.php
    │   ├── get_teachers.php
    │   ├── get_students.php
    │   ├── get_security_status.php
    │   ├── toggle.php
    │   └── delete.php
    └── teacher_positions/
        ├── assign.php
        └── get.php
```

---

## Setup

### 1. Database
```sql
-- Import the schema
mysql -u root -p < backend/schema.sql
```

### 2. Web Server
- Place in `htdocs/school-duty-system/` (XAMPP) or equivalent
- Apache/Nginx with PHP 7.4+
- MySQL 5.7+ or MariaDB 10.4+

### 3. Default Admin Credentials
```
Email:    admin@school.com
Password: Admin@1234
```
⚠️ Change immediately after first login.

---

## Key Features

### ★ Auto Term Duty Roster (NEW)
- Academician clicks **Generate** with a start date and duty title
- System auto-creates 13 weeks (3 months) of weekly duties
- Teachers are assigned **equally** — pure round-robin with random starting offset
- Each week shows: week number, date range, assigned teacher, status, swap badge

### ★ Swap Request Workflow (UPDATED)
1. Teacher sees full term in **Term Duties** tab
2. Teacher selects their week → writes a reason → sends request **to Academician**
3. Academician reviews in **Swap Requests** tab
4. Academician selects a replacement teacher, adds a note, approves or rejects
5. If approved → the `term_duties` row is updated: new teacher + `swapped=true` + note
6. All teachers see the updated schedule with "🔄 Transferred" badge

### Other Features
- JWT authentication (8h expiry), role-based access control
- Admin: create/disable/delete users, reset passwords, assign positions
- Academician: timetable management, special tasks, roster book view
- Teacher: post tasks for students, grade submissions, roster book, timetable view
- Student: view & submit tasks, see grades, view timetable
- Admin password recovery via 3-step security question flow

---

## Roles & Permissions

| Feature                  | Admin | Academician | Teacher | Student |
|--------------------------|:-----:|:-----------:|:-------:|:-------:|
| Create users             | ✅    |             |         |         |
| Generate term duties     | ✅    | ✅          |         |         |
| Edit week assignment     | ✅    | ✅          |         |         |
| View full term schedule  | ✅    | ✅          | ✅      |         |
| Request duty swap        |       |             | ✅      |         |
| Approve/reject swap      | ✅    | ✅          |         |         |
| Assign special tasks     | ✅    | ✅          |         |         |
| Post student tasks       |       |             | ✅      |         |
| Submit task              |       |             |         | ✅      |
| Grade submission         |       |             | ✅      |         |
| Manage timetable         | ✅    | ✅          |         |         |
| Submit roster book       |       |             | ✅      |         |
| View roster book         | ✅    | ✅          |         |         |
