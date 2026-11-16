# Domain: User

## 1. Overview

| Attribute | Value |
|-----------|-------|
| **Domain Name** | User |
| **Bounded Context** | Identity, Access Control, Profiles, Enrollment |
| **Core Responsibility** | Authentication, authorization, roles, profiles, classes |
| **Owner** | User Domain Lead |
| **Status** | Designed |

## 2. Purpose

The User domain manages all identity and access concerns:
- **Authentication** - Login, registration, password reset, email verification
- **Authorization** - Roles, permissions, policies
- **Profiles** - Student/Teacher/Admin profiles with metadata
- **Enrollment** - Class membership, program enrollment
- **Audit** - Activity tracking, impersonation logs

## 3. Core Entities

### 3.1 User (Extended)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `uuid` | CHAR(36) | NOT NULL, UNIQUE | Public identifier |
| `name` | VARCHAR(100) | NOT NULL | Full name |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Login |
| `email_verified_at` | TIMESTAMP | NULLABLE | |
| `password` | VARCHAR(255) | NOT NULL | Bcrypt hash |
| `phone` | VARCHAR(20) | NULLABLE | |
| `avatar_path` | VARCHAR(500) | NULLABLE | MediaLibrary path |
| `role` | VARCHAR(30) | NOT NULL DEFAULT 'student' | Enum: student, teacher, content_editor, admin |
| `status` | TINYINT UNSIGNED | NOT NULL DEFAULT 1 | 1=Pending, 2=Active, 3=Suspended, 4=Deleted |
| `grade_id` | BIGINT UNSIGNED | NULLABLE, FK → grades.id, SET NULL | Student only |
| `school` | VARCHAR(200) | NULLABLE | |
| `city` | VARCHAR(100) | NULLABLE | |
| `birth_year` | SMALLINT UNSIGNED | NULLABLE | |
| `parent_email` | VARCHAR(255) | NULLABLE | For students < 18 |
| `last_active_at` | TIMESTAMP | NULLABLE | |
| `preferences` | JSON | NOT NULL DEFAULT '{}' | UI/settings |
| `two_factor_secret` | VARCHAR(100) | NULLABLE | 2FA TOTP |
| `two_factor_recovery_codes` | JSON | NULLABLE | |
| `remember_token` | VARCHAR(100) | NULLABLE | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |
| `deleted_at` | TIMESTAMP | NULLABLE | Soft delete |

**Preferences JSON Schema:**
```json
{
  "theme": "system",           // light, dark, system
  "language": "tr",            // tr, en
  "notifications": {
    "email": true,
    "push": true,
    "due_reminders": true,
    "weekly_summary": false,
    "achievement_alerts": true
  },
  "practice": {
    "auto_advance": true,
    "show_explanation": true,
    "show_confidence": true,
    "sound_effects": false
  },
  "accessibility": {
    "high_contrast": false,
    "large_text": false,
    "reduce_motion": false
  }
}
```

### 3.2 Role (spatie/laravel-permission)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `name` | VARCHAR(255) | NOT NULL, UNIQUE | student, teacher, content_editor, admin |
| `guard_name` | VARCHAR(255) | NOT NULL | web |
| `description` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

### 3.3 Permission (spatie/laravel-permission)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `name` | VARCHAR(255) | NOT NULL, UNIQUE | e.g., view curriculum, manage questions |
| `guard_name` | VARCHAR(255) | NOT NULL | web |
| `group` | VARCHAR(100) | NULLABLE | curriculum, practice, assessment, admin |
| `description` | TEXT | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

### 3.4 Class

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `uuid` | CHAR(36) | NOT NULL, UNIQUE | |
| `name` | VARCHAR(100) | NOT NULL | e.g., "10-A Kimya" |
| `code` | VARCHAR(20) | NOT NULL, UNIQUE | Join code |
| `teacher_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, SET NULL | |
| `grade_id` | BIGINT UNSIGNED | NOT NULL, FK → grades.id | |
| `program_id` | BIGINT UNSIGNED | NOT NULL, FK → programs.id | |
| `academic_year` | VARCHAR(9) | NOT NULL | e.g., "2024-2025" |
| `max_students` | SMALLINT UNSIGNED | NOT NULL DEFAULT 50 | |
| `is_active` | BOOLEAN | NOT NULL DEFAULT TRUE | |
| `settings` | JSON | NOT NULL DEFAULT '{}' | Class-specific config |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |
| `deleted_at` | TIMESTAMP | NULLABLE | |

**Settings JSON:**
```json
{
  "allow_self_enroll": true,
  "require_approval": false,
  "default_practice_mode": "mixed_review",
  "homework_deadline_days": 7,
  "parent_notifications": true
}
```

### 3.5 ClassStudent (Pivot)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `class_id` | BIGINT UNSIGNED | NOT NULL, FK → classes.id, CASCADE | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `enrolled_at` | TIMESTAMP | NOT NULL | |
| `status` | TINYINT UNSIGNED | NOT NULL DEFAULT 1 | 1=Active, 2=Pending, 3=Removed |
| `removed_at` | TIMESTAMP | NULLABLE | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(class_id, user_id)`

### 3.6 UserProgram (Pivot - Program Enrollment)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `program_id` | BIGINT UNSIGNED | NOT NULL, FK → programs.id, CASCADE | |
| `enrolled_at` | TIMESTAMP | NOT NULL | |
| `status` | TINYINT UNSIGNED | NOT NULL DEFAULT 1 | 1=Active, 2=Completed, 3=Dropped |
| `completed_at` | TIMESTAMP | NULLABLE | |
| `progress_percentage` | DECIMAL(5,2) | NOT NULL DEFAULT 0.00 | Denormalized |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

---

## 4. Relationships

```mermaid
erDiagram
    USER ||--o{ CLASS : teaches
    USER }|--o{ CLASS_STUDENT : enrolls
    CLASS ||--o{ CLASS_STUDENT : has
    USER }|--o{ USER_PROGRAM : enrolls
    PROGRAM ||--o{ USER_PROGRAM : has
    USER }|--o{ ROLE : has
    ROLE }|--o{ PERMISSION : has
```

### Detailed

| Entity | Relationship | Target | Pivot | Cascade |
|--------|--------------|--------|-------|---------|
| User | BelongsTo | Grade | - | SET NULL |
| User | BelongsToMany | Class | class_students | DETACH |
| User | BelongsToMany | Program | user_programs | DETACH |
| User | BelongsToMany | Role | model_has_roles | DETACH |
| User | BelongsToMany | Permission | model_has_permissions | DETACH |
| User | HasMany | PracticeSession | - | CASCADE |
| User | HasMany | QuestionAttempt | - | CASCADE |
| User | HasMany | TopicMastery | - | CASCADE |
| User | HasMany | SpacedRepetitionSchedule | - | CASCADE |
| User | HasMany | Class (teacher) | - | SET NULL |
| User | MorphMany | Media | - | CASCADE |
| Class | BelongsTo | Teacher (User) | - | SET NULL |
| Class | BelongsToMany | User (students) | class_students | DETACH |
| Class | BelongsTo | Grade | - | RESTRICT |
| Class | BelongsTo | Program | - | RESTRICT |
| Program | BelongsToMany | User | user_programs | DETACH |

---

## 3. Roles & Permissions

### Role Hierarchy

```
admin
  └── All permissions
  
content_editor
  ├── Curriculum: view, create, edit, publish, archive
  ├── Content: view, create, edit, publish, archive
  ├── Questions: view, create, edit, approve, deprecate
  ├── Media: upload, manage
  └── Student: view_progress
  
teacher
  ├── Curriculum: view
  ├── Classes: create, edit, manage_students, view_reports
  ├── Assignments: create, grade
  ├── Practice: assign, view_class_progress
  ├── Exams: create, assign, grade
  └── Students: view, manage_enrollment
  
student
  ├── Practice: all modes
  ├── Exams: take, review
  ├── Progress: view_own
  ├── Content: view_published
  └── Profile: edit_own
```

### Permission Matrix

| Permission | Student | Teacher | Content Editor | Admin |
|------------|---------|---------|----------------|-------|
| **Curriculum** | | | | |
| view curriculum | ✓ | ✓ | ✓ | ✓ |
| manage grades | | | ✓ | ✓ |
| manage units | | | ✓ | ✓ |
| manage topics | | | ✓ | ✓ |
| manage objectives | | | ✓ | ✓ |
| **Content** | | | | |
| view theory | ✓ | ✓ | ✓ | ✓ |
| create theory | | | ✓ | ✓ |
| edit theory | | | ✓ | ✓ |
| publish theory | | | ✓ | ✓ |
| manage media | | | ✓ | ✓ |
| **Questions** | | | | |
| view questions | ✓ | ✓ | ✓ | ✓ |
| create questions | | | ✓ | ✓ |
| edit questions | | | ✓ | ✓ |
| approve questions | | | ✓ | ✓ |
| deprecate questions | | | ✓ | ✓ |
| **Practice** | | | | |
| practice questions | ✓ | ✓ | ✓ | ✓ |
| view own progress | ✓ | ✓ | ✓ | ✓ |
| assign practice | | ✓ | ✓ | ✓ |
| view class progress | | ✓ | ✓ | ✓ |
| **Exams** | | | | |
| take exam | ✓ | ✓ | ✓ | ✓ |
| create exam | | ✓ | ✓ | ✓ |
| grade exam | | ✓ | | ✓ |
| view exam analytics | | ✓ | ✓ | ✓ |
| **Users** | | | | |
| view own profile | ✓ | ✓ | ✓ | ✓ |
| edit own profile | ✓ | ✓ | ✓ | ✓ |
| view users | | ✓ | | ✓ |
| manage users | | | | ✓ |
| manage roles | | | | ✓ |
| **Classes** | | | | |
| create class | | ✓ | | ✓ |
| manage class students | | ✓ | | ✓ |
| view class reports | | ✓ | | ✓ |
| **System** | | | | |
| manage settings | | | | ✓ |
| view analytics | | ✓ | ✓ | ✓ |
| manage feature flags | | | | ✓ |

---

## 4. Authentication & Security

### Login Flow

```
POST /api/v1/auth/login
{
  "email": "student@school.edu.tr",
  "password": "securePass123",
  "remember": true,
  "two_factor_code": "123456"  // if 2FA enabled
}

Response:
{
  "user": { ... },
  "token": "sanctum_token",
  "expires_at": "2024-12-31 23:59:59"
}
```

### Registration Flow

```
POST /api/v1/auth/register
{
  "name": "Ahmet Yılmaz",
  "email": "ahmet@ogrenci.edu.tr",
  "password": "securePass123",
  "password_confirmation": "securePass123",
  "role": "student",
  "grade_id": 10,
  "school": "Ankara Fen Lisesi",
  "city": "Ankara",
  "birth_year": 2008,
  "parent_email": "anne@email.com"
}
```

### Password Reset

```
POST /api/v1/auth/forgot-password
{ "email": "user@email.com" }

POST /api/v1/auth/reset-password
{
  "token": "reset_token_from_email",
  "email": "user@email.com",
  "password": "newSecurePass123",
  "password_confirmation": "newSecurePass123"
}
```

### Email Verification

```
POST /api/v1/auth/verification-notification  // Resend
GET  /api/v1/auth/verify-email/{id}/{hash}   // Verify link
```

### Two-Factor Authentication

```
POST /api/v1/user/two-factor/enable
{ "password": "current_password" }
→ Returns QR code + recovery codes

POST /api/v1/user/two-factor/confirm
{ "code": "123456", "password": "current_password" }

POST /api/v1/user/two-factor/disable
{ "password": "current_password" }
```

### Password Policy

| Rule | Value |
|------|-------|
| Minimum Length | 8 characters |
| Require Uppercase | No (PM decision 16.11.2026 — follows design § 08) |
| Require Lowercase | No (PM decision 16.11.2026) |
| Require Numbers | Yes |
| Require Symbols | No (optional) |
| Max Login Attempts | 5 per 15 minutes |
| Lockout Duration | 15 minutes |
| Password History | Last 5 passwords |

### Session & Token

| Setting | Value |
|---------|-------|
| Session Lifetime | 120 minutes |
| Remember Me Lifetime | 30 days |
| Sanctum Token Expiry | 30 days (web), 1 year (mobile) |
| Concurrent Sessions | 5 max |
| CSRF Protection | Enabled (web) |

---

## 4. Authorization Policies

### Policy Classes

```php
// UserPolicy
class UserPolicy
{
    public function viewAny(User $user): bool { return $user->can('view users'); }
    public function view(User $user, User $target): bool { return $user->id === $target->id || $user->can('view users'); }
    public function create(User $user): bool { return $user->can('manage users'); }
    public function update(User $user, User $target): bool { return $user->id === $target->id || $user->can('manage users'); }
    public function delete(User $user, User $target): bool { return $user->can('manage users') && $user->id !== $target->id; }
    public function impersonate(User $user, User $target): bool { return $user->can('impersonate users'); }
}

// ClassPolicy
class ClassPolicy
{
    public function view(User $user, Class $class): bool {
        return $user->id === $class->teacher_id 
            || $user->classes()->where('class_id', $class->id)->exists()
            || $user->can('view classes');
    }
    public function manageStudents(User $user, Class $class): bool {
        return $user->id === $class->teacher_id || $user->can('manage classes');
    }
}

// ProgramPolicy
class ProgramPolicy
{
    public function enroll(User $user, Program $program): bool {
        return $program->is_active 
            && $program->grades->contains($user->grade_id)
            && !$user->programs()->where('program_id', $program->id)->exists();
    }
}
```

---

## 4. API Contracts

```
# Auth
POST   /api/v1/auth/login
POST   /api/v1/auth/register
POST   /api/v1/auth/logout
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
POST   /api/v1/auth/verification-notification
GET    /api/v1/auth/verify-email/{id}/{hash}

# 2FA
POST   /api/v1/user/two-factor/enable
POST   /api/v1/user/two-factor/confirm
POST   /api/v1/user/two-factor/disable

# User Profile
GET    /api/v1/user/profile
PATCH  /api/v1/user/profile
POST   /api/v1/user/avatar
DELETE /api/v1/user/avatar
GET    /api/v1/user/preferences
PATCH  /api/v1/user/preferences

# Enrollment
GET    /api/v1/user/programs
POST   /api/v1/user/programs/{program}/enroll
DELETE /api/v1/user/programs/{program}
GET    /api/v1/user/classes
POST   /api/v1/classes/{class}/join
DELETE /api/v1/classes/{class}/leave

# Teacher Class Management
GET    /api/v1/teacher/classes
POST   /api/v1/teacher/classes
GET    /api/v1/teacher/classes/{class}
PATCH  /api/v1/teacher/classes/{class}
DELETE /api/v1/teacher/classes/{class}
GET    /api/v1/teacher/classes/{class}/students
POST   /api/v1/teacher/classes/{class}/students
DELETE /api/v1/teacher/classes/{class}/students/{student}
POST   /api/v1/teacher/classes/{class}/invite
```

---

## 5. Filament Resources

```
User/
├── UserResource
│   ├── Pages: List, Create, Edit, View
│   ├── RelationManagers:
│   │   ├── RolesRelationManager
│   │   ├── ClassesRelationManager
│   │   ├── EnrollmentsRelationManager
│   │   └── ActivityRelationManager
│   └── Widgets: UserStatsWidget
├── ClassResource
│   ├── Pages: List, Create, Edit, View
│   ├── RelationManagers:
│   │   ├── StudentsRelationManager
│   │   └── ReportsRelationManager
│   └── Widgets: ClassStatsWidget
└── RoleResource
    ├── Pages: List, Create, Edit
    └── Features: Permission assignment
```

---

## 5. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| **Account Takeover** | Critical | Rate limiting, 2FA, device tracking, suspicious login alerts |
| **Student Data Privacy** | Critical | KVKK/GDPR compliance; data minimization; encryption at rest |
| **Role Escalation** | Critical | Policy-based checks; no role checks in controllers; audit trail |
| **Impersonation Abuse** | High | Audit log; requires admin; time-limited; notification to user |
| **Class Code Sharing** | Medium | Regeneratable codes; expiry; max uses |
| **Password Reuse** | Medium | History check (5); breach detection (HaveIBeenPwned API) |
| **Session Hijacking** | High | Secure cookies; SameSite=Strict; short token expiry |
| **Account Enumeration** | Medium | Generic error messages; rate limiting |

---

*End of User Domain Design*