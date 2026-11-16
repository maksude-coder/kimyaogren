# Data Dictionary: users

## Overview
- **Table**: `users`
- **Domain**: User
- **Purpose**: Extended user model with roles, status, and profile
- **Owner**: User Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.1

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| name | VARCHAR(100) | NOT NULL | Full name | "Ahmet Yılmaz" |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Login email | "ahmet@email.com" |
| email_verified_at | TIMESTAMP | NULLABLE | Verification timestamp | 2024-01-15 10:30:00 |
| password | VARCHAR(255) | NOT NULL | Hashed password | "$2y$10$..." |
| phone | VARCHAR(20) | NULLABLE | Phone number | "+90 555 123 4567" |
| avatar_path | VARCHAR(500) | NULLABLE | Media library path | "avatars/1/abc123.jpg" |
| role | VARCHAR(30) | NOT NULL, DEFAULT 'student' | Primary role (enum) | "student" |
| status | TINYINT UNSIGNED | NOT NULL, DEFAULT 1 | 1=Pending, 2=Active, 3=Suspended, 4=Deleted | 2 |
| grade_id | BIGINT UNSIGNED | NULLABLE, FK → grades.id, SET NULL | Student grade (a row of the program-scoped `grades` table) | 9 |
| is_graduate | BOOLEAN | NOT NULL, DEFAULT FALSE | Graduate preparing for YKS; sees all grades, has no `grade_id` | false |
| school | VARCHAR(200) | NULLABLE | School name | "Ankara Fen Lisesi" |
| city | VARCHAR(100) | NULLABLE | City | "Ankara" |
| birth_year | SMALLINT UNSIGNED | NULLABLE | Birth year | 2008 |
| parent_email | VARCHAR(255) | NULLABLE | Parent contact | "anne@email.com" |
| terms_accepted_at | TIMESTAMP | NULLABLE | When the user accepted the terms of use and KVKK notice (set at registration; NULL for accounts created by an admin) | 2026-11-16 10:30:00 |
| last_active_at | TIMESTAMP | NULLABLE | Last activity | 2024-01-15 14:32:15 |
| preferences | JSON | NULLABLE | UI/settings prefs | {"theme": "dark", "notifications": true} |
| remember_token | VARCHAR(100) | NULLABLE | Remember me token | "abc123..." |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-15 14:32:15 |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete | NULL |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| uk_users_email | email | UNIQUE | Email login |
| idx_users_role | role | BTREE | Role filter |
| idx_users_status | status | BTREE | Status filter |
| idx_users_grade | grade_id | BTREE | Grade filter |
| idx_users_active | (status, last_active_at) | BTREE | Active users |
| idx_users_school_city | (school, city) | BTREE | Location queries |

## Relationships

| Relationship | Type | Related Table | FK Column | Cascade |
|--------------|------|---------------|-----------|---------|
| grade | BelongsTo | grades | grade_id | ON DELETE SET NULL |
| classes (student) | BelongsToMany | classes | class_students | DETACH |
| taughtClasses | HasMany | classes | teacher_id | SET NULL |
| practiceSessions | HasMany | practice_sessions | user_id | CASCADE DELETE |
| questionAttempts | HasMany | question_attempts | user_id | CASCADE DELETE |
| topicMasteries | HasMany | topic_masteries | user_id | CASCADE DELETE |
| spacedRepetition | HasMany | spaced_repetition_schedules | user_id | CASCADE DELETE |
| examAttempts | HasMany | exam_attempts | user_id | CASCADE DELETE |
| creator | HasMany | * | created_by_id | SET NULL |
| updater | HasMany | * | updated_by_id | SET NULL |
| roles | BelongsToMany | roles | model_has_roles | DETACH |
| permissions | BelongsToMany | permissions | model_has_permissions | DETACH |
| media | MorphMany | media | model_id, model_type | CASCADE DELETE |

## Business Rules

1. **Email unique** - login identifier
2. **Role enum**: student, teacher, admin, content_editor
3. **Status enum**: 1=Pending, 2=Active, 3=Suspended, 4=Deleted
4. **Grade only for students** - null for teachers/admins
4a. **Graduate** - `is_graduate = true` implies `grade_id IS NULL`; choosing a grade clears `is_graduate`
4b. **Self-registration** always creates `role = student`, `status = 2 (Active)`; e-mail verification is requested but not required to use the account (PM decision 16.11.2026)
5. **Soft deletes** - preserve data on deletion
6. **Avatar via Media Library** - spatie/laravel-medialibrary
6. **Preferences JSON** - theme, notifications, language
7. **Created/Updated by** tracked via audit fields on other models

## Roles & Permissions (spatie/laravel-permission)

| Role | Permissions |
|------|-------------|
| student | practice, view_own_progress, take_exam, view_own_exam_results |
| teacher | all student + create_class, manage_students, view_class_reports, create_assignments |
| content_editor | all teacher + manage_curriculum, manage_questions, manage_content, publish_content |
| admin | all permissions, manage_users, manage_roles, system_settings |

## Sample Data

```sql
-- Admin
INSERT INTO users (name, email, password, role, status, email_verified_at, created_by_id) VALUES
('System Admin', 'admin@kimyaogren.com', '$2y$10$...', 'admin', 2, NOW(), 1);

-- Teacher
INSERT INTO users (name, email, password, role, status, email_verified_at, school, city, created_by_id) VALUES
('Ayşe Demir', 'ayse.demir@okul.edu.tr', '$2y$10$...', 'teacher', 2, NOW(), 'Ankara Fen Lisesi', 'Ankara', 1);

-- Student
INSERT INTO users (name, email, password, role, status, email_verified_at, grade_id, school, city, birth_year, parent_email, created_by_id) VALUES
('Mehmet Kaya', 'mehmet.kaya@ogrenci.edu.tr', '$2y$10$...', 'student', 2, NOW(), 10, 'İstanbul Erkek Lisesi', 'İstanbul', 2007, 'anne@email.com', 1);
```

## Migration Checklist

- [ ] Column order: PK → FK → Business → Status → Audit → Timestamps → SoftDeletes
- [ ] `email` UNIQUE
- [ ] `grade_id` FK SET NULL
- [ ] `role` VARCHAR(30) with check constraint or enum cast
- [ ] `status` TINYINT UNSIGNED default 1
- [ ] `preferences` JSON nullable
- [ ] `avatar_path` VARCHAR(500) nullable
- [ ] `grade_id` FK to grades
- [ ] `email_verified_at` TIMESTAMP nullable
- [ ] Soft deletes included
- [ ] All timestamps
- [ ] Indexes on email, role, status, grade, active
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

## Revision Notes

### v1.1 — Sprint 2.3 (PM decisions, 16.11.2026)

- Added `is_graduate` (design § 08 "Mezun · YKS hazırlık") and `terms_accepted_at` (consent record for the terms of use / KVKK notice).
- `grade_id` documented as NULLABLE (it always was, by its SET NULL rule).
- **Implemented so far:** `id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `grade_id`, `is_graduate`, `terms_accepted_at`, `remember_token`, timestamps. The remaining columns (`phone`, `avatar_path`, `school`, `city`, `birth_year`, `parent_email`, `last_active_at`, `preferences`, `deleted_at`) and the spatie role/permission tables are added by the sprints that need them.

---

*Generated from Data Dictionary v1.1*