# Domain Modules

## Purpose

Detailed specification of each domain module: boundaries, entities, relationships, and APIs.

---

## Module Catalog

| Module | Domain | Status | Sprint | Document |
|--------|--------|--------|--------|----------|
| **Academic** | Curriculum Structure | Designed | 1-2 | [domain-academic.md](domain-academic.md) |
| **Content** | Theory & Media | Designed | 3-4 | [domain-content.md](domain-content.md) |
| **Assessment** | Questions & Exams | Designed | 7-8 | [domain-assessment.md](domain-assessment.md) |
| **Practice** | Spaced Repetition | Designed | 9-11 | [domain-practice.md](domain-practice.md) |
| **Progress** | Mastery & Analytics | Partly implemented (Sprint 3.3) | 13-16 | [domain-progress.md](domain-progress.md) |
| **User** | Auth & Authorization | Designed | 1 | [domain-user.md](domain-user.md) |
| **Media** | File Management | Designed | 3 | [domain-media.md](domain-media.md) |
| **Settings** | Configuration | Designed | 5 | [domain-settings.md](domain-settings.md) |

---

## Academic Module

**Full specification:** [domain-academic.md](domain-academic.md)

### Responsibility
Manage the curriculum hierarchy: Grades → Units → Topics → Learning Objectives, aligned with MEB standards.

### Entities

| Entity | Description | Key Attributes |
|--------|-------------|----------------|
| `Grade` | School year (9, 10, 11, 12) | `number`, `name`, `order`, `is_active` |
| `Unit` | Major curriculum unit | `grade_id`, `name`, `code`, `order`, `description` |
| `Topic` | Specific topic within unit | `unit_id`, `name`, `slug`, `order`, `description`, `status` |
| `LearningObjective` | Measurable learning outcome | `topic_id`, `code` (MEB), `description`, `bloom_level`, `difficulty` |

### Relationships
```
Grade (1) ──────< (N) Unit
Unit  (1) ──────< (N) Topic
Topic (1) ──────< (N) LearningObjective
```

### Key Business Rules
- Topic slug unique within Unit
- LearningObjective code follows MEB format (e.g., `KIM.9.1.1`), unique within Topic
- Bloom's taxonomy levels: Remember, Understand, Apply, Analyze, Evaluate, Create
- Status: Draft → Published → Archived

### API
```
GET    /api/grades
GET    /api/grades/{grade}/units
GET    /api/units/{unit}/topics
GET    /api/topics/{topic}/objectives
```

### Filament Resources
- `GradeResource` (with Units RelationManager)
- `UnitResource` (with Topics RelationManager)
- `TopicResource` (with Objectives RelationManager)
- `LearningObjectiveResource`

---

### Responsibility
Manage the curriculum hierarchy: Grades → Units → Topics → Learning Objectives, aligned with MEB standards.

### Entities

| Entity | Description | Key Attributes |
|--------|-------------|----------------|
| `Grade` | School year (9, 10, 11, 12) | `number`, `name`, `order`, `is_active` |
| `Unit` | Major curriculum unit | `grade_id`, `name`, `code`, `order`, `description` |
| `Topic` | Specific topic within unit | `unit_id`, `name`, `slug`, `order`, `description`, `status` |
| `LearningObjective` | Measurable learning outcome | `topic_id`, `code` (MEB), `description`, `bloom_level`, `difficulty` |

### Relationships
```
Grade (1) ──────< (N) Unit
Unit  (1) ──────< (N) Topic
Topic (1) ──────< (N) LearningObjective
```

### Key Business Rules
- Topic slug unique within Unit
- LearningObjective code follows MEB format (e.g., `KIM.9.1.1`), unique within Topic
- Bloom's taxonomy levels: Remember, Understand, Apply, Analyze, Evaluate, Create
- Status: Draft → Published → Archived

### API
```
GET    /api/grades
GET    /api/grades/{grade}/units
GET    /api/units/{unit}/topics
GET    /api/topics/{topic}/objectives
```

### Filament Resources
- `GradeResource` (with Units RelationManager)
- `UnitResource` (with Topics RelationManager)
- `TopicResource` (with Objectives RelationManager)
- `LearningObjectiveResource`

---

## Content Module

### Responsibility
Manage educational content: theory articles, rich text, media attachments, versioning.

### Entities

| Entity | Description |
|--------|-------------|
| `TheoryArticle` | Main content piece linked to Topic |
| `TheoryVersion` | Immutable content versions |
| `Media` | Images, videos, simulations, PDFs |
| `Tag` | Cross-cutting content tags |

### Relationships
```
Topic (1) ──────< (N) TheoryArticle
TheoryArticle (1) ──────< (N) TheoryVersion
TheoryArticle (N) ─────> (N) Media (morph)
TheoryArticle (N) ─────> (N) Tag (morph)
```

### Key Business Rules
- Only Published versions visible to students
- Version history immutable
- Media stored in S3, referenced by ID in DB
- Rich text via TipTap (Filament)

### API
```
GET    /api/topics/{topic}/articles
GET    /api/articles/{article}?version=latest
GET    /api/articles/{article}/versions
```

### Filament Resources
- `TheoryArticleResource` (with Versions RelationManager)
- `MediaResource` (Filament Media Library integration)

---

## Assessment Module

### Responsibility
Question bank, exam definitions, and exam attempts.

### Entities

| Entity | Description |
|--------|-------------|
| `Question` | Single question with type, content, options, explanation |
| `QuestionOption` | Options for MCQ, matching, ordering |
| `Exam` | Full exam definition (YKS simulation, quiz) |
| `ExamSection` | Timed section within exam |
| `ExamAttempt` | Student's exam session |

### Question Types (Enum)
| Type | Code | Description |
|------|------|-------------|
| Multiple Choice | `multiple_choice` | 4-5 options, one correct |
| True/False | `true_false` | Binary choice |
| Short Answer | `short_answer` | Text input, auto-grade via keywords |
| Fill in Blank | `fill_blank` | Drag-drop or typed blanks |
| Matching | `matching` | Match left to right columns |
| Ordering | `ordering` | Sequence items correctly |

### Relationships
```
Topic (1) ──────────< (N) Question
Question (1) ───────< (N) QuestionOption
Exam (1) ───────────< (N) ExamSection
ExamSection (1) ────< (N) Question (via pivot with order)
User (1) ───────────< (N) ExamAttempt
ExamAttempt (1) ────< (N) QuestionAttempt
```

### Key Business Rules
- Questions tagged with: Topic, Difficulty (1-5), Bloom Level, YKS Year
- Versioning for question updates (preserve attempt integrity)
- Exam sections have time limits, question counts
- YKS format: 40 questions, 80 minutes, specific distribution

### API
```
GET    /api/questions?topic=&difficulty=&type=
POST   /api/exams/{exam}/attempts
GET    /api/exam-attempts/{attempt}
POST   /api/exam-attempts/{attempt}/answers
POST   /api/exam-attempts/{attempt}/submit
```

### Filament Resources
- `QuestionResource` (complex form with dynamic options)
- `ExamResource` (with Sections RelationManager)
- `ExamAttemptResource` (read-only, analytics)

---

## Practice Module

### Responsibility
Spaced repetition scheduling, practice sessions, attempt tracking.

### Entities

| Entity | Description |
|--------|-------------|
| `PracticeSession` | Student practice session (topic, mixed, timed) |
| `QuestionAttempt` | Single question attempt within session |
| `TopicMastery` | Aggregated mastery per topic per student |
| `SpacedRepetitionSchedule` | SM-2 scheduling per question per student |

### Relationships
```
User (1) ───────────────< (N) PracticeSession
PracticeSession (1) ────< (N) QuestionAttempt
QuestionAttempt (N) ───> (1) Question
User (1) ───────────────< (N) TopicMastery
TopicMastery (N) ───────> (1) Topic
User (1) ───────────────< (N) SpacedRepetitionSchedule
SpacedRepetitionSchedule > (1) Question
```

### SM-2 Algorithm Parameters
| Parameter | Default | Configurable |
|-----------|---------|--------------|
| Initial Interval | 1 day | Yes |
| Easy Bonus | 1.3x | Yes |
| Hard Penalty | 0.8x | Yes |
| Lapse Reset | 1 day | Yes |
| Min Interval | 1 day | Yes |
| Max Interval | 365 days | Yes |

### Key Business Rules
- Mastery = accuracy on the student's last 20 answers in the topic, at least 5 answers (Sprint 3.3, design § 06; `dd-topic-masteries.md` v1.1)
- Due queue prioritized by: overdue → due today → new
- Session types: Topic Practice, Mixed Review, Weak Areas, Exam Sim
- A session starts with as many approved questions as the topic has, up to the chosen size (PM decision 16.11.2026; the former 5-question minimum is removed)

---

## Progress Module

### Responsibility
Mastery tracking, analytics, reporting, intervention alerts.

### Entities

| Entity | Description |
|--------|-------------|
| `StudentProgress` | Snapshot of student state at point in time |
| `WeakAreaReport` | Detected knowledge gaps with recommendations |
| `MasterySnapshot` | Daily mastery per topic for trend analysis |
| `ClassReport` | Aggregated class-level metrics |

### Key Metrics
| Metric | Calculation |
|--------|-------------|
| Topic Mastery | correct / answered over the last 20 answers in the topic (min. 5) |
| Overall Progress | Average of topic masteries in curriculum |
| Study Streak | Consecutive days with ≥1 session |
| Weak Areas | Objectives with ≥ 5 answers and < 50% correct over the last 20 (Sprint 3.3); topic-level weak areas come with the "Bugün" page |
| YKS Readiness | Simulated score based on current mastery |

### API
```
GET    /api/students/{student}/progress
GET    /api/students/{student}/weak-areas
GET    /api/students/{student}/mastery-trend?topic=
GET    /api/classes/{class}/report
GET    /api/teachers/{teacher}/dashboard
```

### Filament Widgets
- `StudentProgressWidget` (on student view)
- `ClassOverviewWidget` (teacher dashboard)
- `CurriculumCoverageWidget` (admin)

---

## User Module

### Responsibility
Authentication, authorization, profiles, class management.

### Entities

| Entity | Description |
|--------|-------------|
| `User` | Students, Teachers, Admins |
| `Role` | student, teacher, admin, content_editor |
| `Permission` | Granular permissions (spatie/laravel-permission) |
| `Class` | Teacher's class with students |
| `ClassStudent` | Pivot with enrollment date, status |

### Roles & Permissions

| Permission | Student | Teacher | Admin | Content Editor |
|------------|---------|---------|-------|----------------|
| Practice questions | ✓ | ✓ | ✓ | ✓ |
| View own progress | ✓ | ✓ | ✓ | ✓ |
| Create class | | ✓ | ✓ | |
| Manage class students | | ✓ | ✓ | |
| View class reports | | ✓ | ✓ | |
| Manage curriculum | | | ✓ | ✓ |
| Manage questions | | | ✓ | ✓ |
| Manage users | | | ✓ | |
| System settings | | | ✓ | |

---

## Media Module

### Responsibility
File upload, storage, transformation, CDN delivery.

### Stack
- **Filament Media Library** (spatie/laravel-media-library)
- **Storage**: S3-compatible (AWS, MinIO, Cloudflare R2)
- **Transformations**: Intervention Image (thumbnails, WebP)
- **Video**: Direct S3 streaming, no transcoding

### Collections
| Collection | Models | Conversions |
|------------|--------|-------------|
| `theory_images` | TheoryArticle | thumb (300w), webp |
| `question_media` | Question, QuestionOption | thumb, webp |
| `simulations` | TheoryArticle | original |
| `avatars` | User | thumb (100x100), circle |
| `exports` | Reports | pdf, xlsx |

---

## Settings Module

### Responsibility
System configuration, feature flags, UI customization.

### Entities

| Entity | Description |
|--------|-------------|
| `Setting` | Key-value pairs with type casting |
| `FeatureFlag` | Boolean toggles with rollout % |

### Key Settings
| Key | Type | Description |
|-----|------|-------------|
| `app.name` | string | Platform name |
| `practice.min_questions` | int | Minimum per session |
| `spaced_repetition.easy_bonus` | float | SM-2 parameter |
| `yks.simulation.enabled` | bool | Feature flag |
| `maintenance.mode` | bool | Global maintenance |

---

## Module Dependencies

```
                    ┌─────────────┐
                    │   User      │
                    └──────┬──────┘
                           │
        ┌──────────────────┼──────────────────┐
        ▼                  ▼                  ▼
┌───────────────┐ ┌───────────────┐ ┌───────────────┐
│   Academic    │ │    Media      │ │   Settings    │
└───────┬───────┘ └───────┬───────┘ └───────┬───────┘
        │                 │                 │
        ▼                 │                 │
┌───────────────┐         │                 │
│   Content     │         │                 │
│ (depends on   │         │                 │
│ Academic)     │         │                 │
└───────┬───────┘         │                 │
        │                 │                 │
        ▼                 │                 │
┌───────────────┐         │                 │
│  Assessment   │         │                 │
│ (depends on   │         │                 │
│ Academic +    │         │                 │
│ Content)      │         │                 │
└───────┬───────┘         │                 │
        │                 │                 │
        ▼                 ▼                 ▼
┌───────────────┐ ┌───────────────┐ ┌───────────────┐
│   Practice    │ │   Progress    │ │   (All)       │
│ (depends on   │ │ (depends on   │ │               │
│ Assessment)   │ │  Practice)    │ │               │
└───────────────┘ └───────────────┘ └───────────────┘
```

---

*Each module is developed independently following the Task Workflow. Cross-module changes require Architect approval.*