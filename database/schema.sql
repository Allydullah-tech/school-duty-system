-- =====================================================
-- SchoolDuty Management System — Complete Schema
-- Run this file once to set up the full database
-- =====================================================

CREATE DATABASE IF NOT EXISTS school_duty_system;
USE school_duty_system;

-- ── USERS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  full_name        VARCHAR(100) NOT NULL,
  email            VARCHAR(100) UNIQUE,
  password         VARCHAR(255) NOT NULL,
  role             ENUM('admin','academician','teacher','student') NOT NULL,
  reg_number       VARCHAR(50) UNIQUE,
  is_active        TINYINT(1) DEFAULT 1,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by       INT,
  security_question VARCHAR(255),
  security_answer  VARCHAR(255),
  reset_token      VARCHAR(255),
  reset_expiry     DATETIME,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ── DUTIES (individual ad-hoc duties) ──────────────
CREATE TABLE IF NOT EXISTS duties (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  assigned_to INT NOT NULL,
  assigned_by INT NOT NULL,
  duty_date   DATE NOT NULL,
  duty_time   VARCHAR(20),
  location    VARCHAR(100),
  status      ENUM('pending','ongoing','completed') DEFAULT 'pending',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ── TERM DUTIES (auto-generated 3-month weekly roster) ──
CREATE TABLE IF NOT EXISTS term_duties (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  term_start   DATE NOT NULL,               -- identifies which term
  week_number  INT NOT NULL,
  week_start   DATE NOT NULL,
  week_end     DATE NOT NULL,
  assigned_to  INT NOT NULL,                -- teacher for this week
  title        VARCHAR(200) NOT NULL,       -- duty name e.g. "Morning Assembly"
  status       ENUM('pending','ongoing','completed') DEFAULT 'pending',
  swapped      TINYINT(1) DEFAULT 0,        -- was this week reassigned?
  swap_note    TEXT,                        -- reason/note for the swap
  created_by   INT NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE CASCADE
);

-- ── SPECIAL TASKS (academician → teacher) ──────────
CREATE TABLE IF NOT EXISTS special_tasks (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  assigned_to INT NOT NULL,
  assigned_by INT NOT NULL,
  due_date    DATE,
  priority    ENUM('low','medium','high') DEFAULT 'medium',
  status      ENUM('pending','in_progress','completed') DEFAULT 'pending',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ── STUDENT TASKS (teacher posts for all students) ─
CREATE TABLE IF NOT EXISTS student_tasks (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  subject     VARCHAR(100),
  assigned_by INT NOT NULL,
  due_date    DATE,
  status      ENUM('active','closed') DEFAULT 'active',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ── TASK SUBMISSIONS (student submits against a task) ──
CREATE TABLE IF NOT EXISTS task_submissions (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  task_id         INT NOT NULL,
  student_id      INT NOT NULL,
  submission_text TEXT,
  submission_file VARCHAR(500),
  submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  grade           VARCHAR(20),
  grade_comment   TEXT,
  graded_date     DATETIME,
  FOREIGN KEY (task_id)    REFERENCES student_tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_submission (task_id, student_id)
);

-- ── TEACHER POSITIONS ────────────────────────────
CREATE TABLE IF NOT EXISTS teacher_positions (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id           INT NOT NULL,
  position_name        VARCHAR(100) NOT NULL,
  position_description TEXT,
  assigned_by          INT NOT NULL,
  assigned_date        DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_active            TINYINT(1) DEFAULT 1,
  FOREIGN KEY (teacher_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ── TIMETABLE ────────────────────────────────────
CREATE TABLE IF NOT EXISTS timetable (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  time_slot   VARCHAR(50) NOT NULL,
  subject     VARCHAR(100) NOT NULL,
  teacher_id  INT NOT NULL,
  class_name  VARCHAR(50) NOT NULL,
  room        VARCHAR(50),
  created_by  INT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE CASCADE
);

-- ── ROSTER BOOK ──────────────────────────────────
CREATE TABLE IF NOT EXISTS roster_book (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id       INT NOT NULL,
  date             DATE NOT NULL,
  subject          VARCHAR(100) NOT NULL,
  topic_covered    TEXT,
  students_present INT DEFAULT 0,
  homework_given   TEXT,
  remarks          TEXT,
  submitted_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_roster (teacher_id, date)
);

-- ── SWAP REQUESTS ─────────────────────────────────
-- duty_week_id links to term_duties so academician can auto-reassign
CREATE TABLE IF NOT EXISTS swap_requests (
  id                     INT AUTO_INCREMENT PRIMARY KEY,
  requesting_teacher_id  INT NOT NULL,
  target_teacher_id      INT DEFAULT 0,        -- 0 = sent to academician
  duty_date              DATE NOT NULL,
  duty_week_id           INT,                  -- term_duties.id
  duty_type              VARCHAR(50) DEFAULT 'term',
  reason                 TEXT,
  status                 ENUM('pending','approved','rejected') DEFAULT 'pending',
  requested_at           DATETIME DEFAULT CURRENT_TIMESTAMP,
  responded_at           DATETIME,
  response_comment       TEXT,
  FOREIGN KEY (requesting_teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (duty_week_id) REFERENCES term_duties(id) ON DELETE SET NULL
);

-- New: stores exam timetable
CREATE TABLE IF NOT EXISTS exam_timetable_config (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  config_json LONGTEXT NOT NULL,
  saved_by    INT NOT NULL,
  saved_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (saved_by) REFERENCES users(id) ON DELETE CASCADE
);

-- New: term duty roster
CREATE TABLE IF NOT EXISTS term_duties (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  term_start   DATE NOT NULL,
  week_number  INT NOT NULL,
  week_start   DATE NOT NULL,
  week_end     DATE NOT NULL,
  assigned_to  INT NOT NULL,
  title        VARCHAR(200) NOT NULL,
  status       ENUM('pending','ongoing','completed') DEFAULT 'pending',
  swapped      TINYINT(1) DEFAULT 0,
  swap_note    TEXT,
  created_by   INT NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE CASCADE
);