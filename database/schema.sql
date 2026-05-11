-- ============================================================
--  Belgium Campus iTversity — Hackathon Platform
--  MySQL Database Schema
--  Run this entire file in phpMyAdmin → SQL tab
-- ============================================================

-- ============================================================
--  TABLE 1: hackathons
--  Stores every hackathon event. Matches data.js hackathonsData
-- ============================================================
CREATE TABLE IF NOT EXISTS `hackathons` (
    `id`                    VARCHAR(100)    NOT NULL,           -- e.g. "stellenbosch-2025"
    `title`                 VARCHAR(255)    NOT NULL,
    `description`           TEXT            NOT NULL,
    `event_date`            VARCHAR(150)    NOT NULL,           -- stored as display string e.g. "11 October, 08:00 – 20:00"
    `status`                ENUM('upcoming','active','past','cancelled')
                                            NOT NULL DEFAULT 'upcoming',
    `theme`                 VARCHAR(255)    NULL,               -- e.g. "Senses"
    `venue`                 VARCHAR(255)    NULL,               -- e.g. "Stellenbosch Campus"
    `participants_format`   VARCHAR(100)    NULL,               -- e.g. "Teams of 3"
    `max_participants`      INT             NULL,               -- NULL = no cap
    `current_participants`  INT             NOT NULL DEFAULT 0,
    `registration_deadline` DATE            NULL,
    `prize_description`     VARCHAR(255)    NULL,
    `rules`                 TEXT            NULL,               -- JSON array stored as text
    `categories`            TEXT            NULL,               -- JSON array stored as text
    `requirements`          TEXT            NULL,               -- JSON array stored as text
    `details_doc`           VARCHAR(255)    NULL,               -- filename of attached doc
    `created_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 2: users
--  Stores all registered students. Mirrors data_store/users.json
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`            VARCHAR(50)     NOT NULL,           -- uniqid() from PHP
    `first_name`    VARCHAR(100)    NOT NULL,
    `last_name`     VARCHAR(100)    NOT NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `student_id`    VARCHAR(10)     NOT NULL,           -- exactly 6 chars per validation
    `password_hash` VARCHAR(255)    NOT NULL,           -- bcrypt hash from password_hash()
    `role`          ENUM('student','admin')
                                    NOT NULL DEFAULT 'student',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email`      (`email`),
    UNIQUE KEY `uq_users_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 3: hackathon_registrations
--  Links a user to a hackathon (many-to-many with metadata)
-- ============================================================
CREATE TABLE IF NOT EXISTS `hackathon_registrations` (
    `id`                VARCHAR(50)     NOT NULL,       -- "reg_<timestamp>" from JS
    `hackathon_id`      VARCHAR(100)    NOT NULL,
    `user_email`        VARCHAR(255)    NOT NULL,
    `status`            ENUM('registered','unregistered','attended','disqualified')
                                        NOT NULL DEFAULT 'registered',
    `team_name`         VARCHAR(255)    NULL,
    `team_members`      TEXT            NULL,           -- JSON array of team member emails
    `registration_date` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reg_hackathon_user` (`hackathon_id`, `user_email`),
    CONSTRAINT `fk_reg_hackathon`
        FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_user_email`
        FOREIGN KEY (`user_email`)   REFERENCES `users`      (`email`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 4: submissions
--  Stores project submissions. Mirrors data_store/all_submissions.json
-- ============================================================
CREATE TABLE IF NOT EXISTS `submissions` (
    `id`            BIGINT          NOT NULL,           -- microtime(true)*1000 from PHP
    `user_email`    VARCHAR(255)    NOT NULL,
    `hackathon_id`  VARCHAR(100)    NULL,               -- optional link to a hackathon
    `title`         VARCHAR(255)    NOT NULL,
    `description`   TEXT            NOT NULL,
    `project_link`  VARCHAR(1000)   NULL,               -- GitHub / Google Drive URL
    `category`      ENUM('iot','drone','3d-printing','vr-ar','software','other')
                                    NOT NULL,
    `status`        ENUM('submitted','under_review','accepted','rejected','winner')
                                    NOT NULL DEFAULT 'submitted',
    `score`         DECIMAL(5,2)    NULL,               -- judge score 0-100
    `judge_notes`   TEXT            NULL,
    `ip_address`    VARCHAR(45)     NULL,               -- IPv4 or IPv6
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sub_user_email`
        FOREIGN KEY (`user_email`)  REFERENCES `users`      (`email`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sub_hackathon`
        FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 5: submission_files
--  Each row = one uploaded file attached to a submission
-- ============================================================
CREATE TABLE IF NOT EXISTS `submission_files` (
    `id`            INT             NOT NULL AUTO_INCREMENT,
    `submission_id` BIGINT          NOT NULL,
    `original_name` VARCHAR(255)    NOT NULL,
    `stored_name`   VARCHAR(255)    NOT NULL,
    `file_path`     VARCHAR(500)    NOT NULL,           -- relative to site root
    `file_size`     INT             NOT NULL,           -- bytes
    `file_type`     ENUM('zip','rar','7z') NOT NULL,
    `uploaded_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_file_submission`
        FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  SEED DATA — Hackathons from data.js
-- ============================================================
INSERT INTO `hackathons`
    (`id`, `title`, `description`, `event_date`, `status`,
     `venue`, `participants_format`, `max_participants`,
     `registration_deadline`, `prize_description`,
     `rules`, `categories`, `requirements`, `details_doc`)
VALUES
(
    'stellenbosch-2025',
    'Stellenbosch Campus Hackathon',
    'Join us for an exciting hackathon at the Stellenbosch campus. Build innovative solutions and compete for amazing prizes!',
    '11 October 2025, 08:00 – 20:00',
    'upcoming',
    'Belgium Campus — Stellenbosch Campus',
    'Teams of 3',
    NULL,
    '2025-12-31',
    'Top 3 Prizes + Certificates for all participants',
    '["Teams of 3.","AI may not be used.","Moderators will ensure fair play.","Technolab resources may be used."]',
    '["Web Development","Mobile Apps","AI/ML","IoT","Game Development"]',
    '["Stellenbosch students only","Valid student ID","Team formation allowed"]',
    'First hackathon full details.docx'
),
(
    'hackathon-2026-senses',
    'Hackathon 2026 — Senses',
    'Theme: Senses. Teams choose one sense (taste, sight, hearing, smell, touch) and build a project where that sense triggers a real action.',
    '11 July 2026, 08:00 – 12 July 2026, 08:00 (24 hours)',
    'upcoming',
    'Belgium Campus iTversity',
    'Teams of up to 4 (solo allowed)',
    NULL,
    '2026-07-10',
    '24-Hour Senses Challenge Prizes',
    '["Choose one sense: taste, sight, hearing, smell, or touch","Sense input must trigger a measurable outcome","AI use allowed only during scheduled research timeslots","No AI use outside allowed timeslots"]',
    '["Taste","Sight","Hearing","Smell","Touch"]',
    '["Choose one sense: taste, sight, hearing, smell, or touch","Sense input must trigger a measurable outcome","AI use allowed only during scheduled research timeslots","No AI use outside allowed timeslots","Students may arrive with a pre-formed team or create one onsite","Maximum 4 students per team; solo entries are allowed but disadvantaged"]',
    NULL
);


-- ============================================================
--  SEED DATA — Existing test user from data_store/users.json
-- ============================================================
INSERT INTO `users`
    (`id`, `first_name`, `last_name`, `email`, `student_id`, `password_hash`, `role`, `created_at`)
VALUES
(
    '68bb10b671a2d',
    'Test',
    'Help',
    'Test@gmail.com',
    '345678',
    '$2y$10$IABwzS1ikXu53LqUN4RLWOEpdXW2KDwBSuen8Q4TKW4cbHdNDSZZK',
    'student',
    '2025-09-05 18:32:54'
);


-- ============================================================
--  NOTE: CREATE VIEW is not available on InfinityFree free tier.
--  The equivalent queries are written directly in PHP instead.
--
--  Equivalent query for submissions with student details:
--    SELECT s.*, u.first_name, u.last_name, u.student_id, h.title AS hackathon_title
--    FROM submissions s
--    JOIN users u ON u.email = s.user_email
--    LEFT JOIN hackathons h ON h.id = s.hackathon_id;
--
--  Equivalent query for hackathon registration stats:
--    SELECT h.id, h.title, h.status, h.event_date,
--           COUNT(r.id) AS total_registrations,
--           SUM(r.status = 'registered') AS active_registrations,
--           COUNT(DISTINCT s.user_email) AS total_submissions
--    FROM hackathons h
--    LEFT JOIN hackathon_registrations r ON r.hackathon_id = h.id
--    LEFT JOIN submissions s ON s.hackathon_id = h.id
--    GROUP BY h.id;
-- ============================================================
