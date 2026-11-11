-- =====================================================
-- BUGTRACK DATABASE: TABLES AND SAMPLE DATA
-- Database: bugtrack_db
-- =====================================================

-- Create the bugs table
CREATE TABLE IF NOT EXISTS bugs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved')
        NOT NULL DEFAULT 'Open',
    date_reported DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_resolved DATETIME DEFAULT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- Create the bug history table
CREATE TABLE IF NOT EXISTS bug_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bug_id INT UNSIGNED NOT NULL,
    previous_status ENUM('Open', 'In Progress', 'Resolved')
        DEFAULT NULL,
    new_status ENUM('Open', 'In Progress', 'Resolved') NOT NULL,
    change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_bug_history_bug_id (bug_id),

    CONSTRAINT fk_bug_history_bug
        FOREIGN KEY (bug_id)
        REFERENCES bugs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- INSERT 10 SAMPLE BUG REPORTS
-- =====================================================

INSERT INTO bugs
    (id, title, description, priority, status, date_reported, date_resolved)
VALUES
(1,
 'Login button not responding',
 'Users cannot log in because clicking the login button does not submit the login form.',
 'High', 'Open', '2026-10-20 09:15:00', NULL),

(2,
 'Incorrect total in shopping cart',
 'The shopping cart displays an incorrect total when customers add multiple products.',
 'High', 'In Progress', '2026-10-20 10:30:00', NULL),

(3,
 'Profile image upload failure',
 'The profile page displays an error when a user tries to upload a new profile picture.',
 'Medium', 'Resolved', '2026-10-21 08:45:00', '2026-10-22 14:20:00'),

(4,
 'Dashboard loading slowly',
 'The dashboard takes more than ten seconds to load when there are many records.',
 'Medium', 'Open', '2026-10-21 11:00:00', NULL),

(5,
 'Password reset email not received',
 'Users do not receive the password reset email after submitting their registered email address.',
 'High', 'In Progress', '2026-10-22 13:10:00', NULL),

(6,
 'Navigation menu overlaps content',
 'The navigation menu overlaps the main content area on smaller screen sizes.',
 'Low', 'Resolved', '2026-10-23 09:00:00', '2026-10-24 16:30:00'),

(7,
 'Search returns duplicate results',
 'The search page sometimes displays the same product more than once.',
 'Medium', 'Open', '2026-10-24 10:15:00', NULL),

(8,
 'Incorrect date format in reports',
 'Generated reports display dates in an inconsistent format.',
 'Low', 'Resolved', '2026-10-25 14:00:00', '2026-10-26 11:45:00'),

(9,
 'Checkout page payment error',
 'The checkout page displays a payment processing error even when the payment details are valid.',
 'High', 'In Progress', '2026-10-26 08:30:00', NULL),

(10,
 'Contact form validation missing',
 'The contact form accepts submissions even when the email address field is empty.',
 'Medium', 'In Progress', '2026-10-27 15:20:00', NULL);


-- =====================================================
-- INSERT STATUS HISTORY
-- Each bug has an initial Open history event.
-- Further entries record its status transitions.
-- =====================================================

INSERT INTO bug_history
    (bug_id, previous_status, new_status, change_date)
VALUES

-- Bug 1: Open
(1, NULL, 'Open', '2026-10-20 09:15:00'),

-- Bug 2: Open -> In Progress
(2, NULL, 'Open', '2026-10-20 10:30:00'),
(2, 'Open', 'In Progress', '2026-10-21 09:00:00'),

-- Bug 3: Open -> In Progress -> Resolved
(3, NULL, 'Open', '2026-10-21 08:45:00'),
(3, 'Open', 'In Progress', '2026-10-21 13:00:00'),
(3, 'In Progress', 'Resolved', '2026-10-22 14:20:00'),

-- Bug 4: Open
(4, NULL, 'Open', '2026-10-21 11:00:00'),

-- Bug 5: Open -> In Progress
(5, NULL, 'Open', '2026-10-22 13:10:00'),
(5, 'Open', 'In Progress', '2026-10-23 10:15:00'),

-- Bug 6: Open -> In Progress -> Resolved
(6, NULL, 'Open', '2026-10-23 09:00:00'),
(6, 'Open', 'In Progress', '2026-10-23 14:30:00'),
(6, 'In Progress', 'Resolved', '2026-10-24 16:30:00'),

-- Bug 7: Open
(7, NULL, 'Open', '2026-10-24 10:15:00'),

-- Bug 8: Open -> In Progress -> Resolved
(8, NULL, 'Open', '2026-10-25 14:00:00'),
(8, 'Open', 'In Progress', '2026-10-26 08:30:00'),
(8, 'In Progress', 'Resolved', '2026-10-26 11:45:00'),

-- Bug 9: Open -> In Progress
(9, NULL, 'Open', '2026-10-26 08:30:00'),
(9, 'Open', 'In Progress', '2026-10-26 11:00:00'),

-- Bug 10: Open -> In Progress
(10, NULL, 'Open', '2026-10-27 15:20:00'),
(10, 'Open', 'In Progress', '2026-10-28 09:10:00');


-- =====================================================
-- END OF BUGTRACK SAMPLE DATABASE SCRIPT
-- =====================================================