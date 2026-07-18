# Folder Structure

## Purpose

Defines the standard directory structure for the KimyaÖğren Laravel application. Enforces consistency and discoverability.

---

## Root Structure

```
kimyaogren/
├── app/                    # Application code
├── bootstrap/              # Framework bootstrap
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── docs/                   # Project documentation (this folder)
├── lang/                   # Translation files
├── public/                 # Public entry point
├── resources/              # Views, CSS, JS
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploads
├── tests/                  # Test suite
├── vendor/                 # Composer dependencies
├── .github/                # GitHub Actions workflows
├── .editorconfig
├── .env.example
├── .gitattributes
├── .gitignore
├── AGENTS.md               # AI onboarding entry point
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
├── README.md
├── vite.config.js
└── vite.config.ts (if TS)
```

---

## app/ Directory

```
app/
├── Actions/                # Single-responsibility action classes
│   ├── Academic/
│   ├── Assessment/
│   ├── Auth/
│   ├── Content/
│   ├── Media/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Broadcasting/           # Broadcast channels
├── Console/                # Artisan commands
│   └── Commands/
├── Data/                   # Data transfer objects, API resources
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Enums/                  # PHP 8.1+ backed enums
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Events/                 # Event classes
│   ├── Academic/
│   ├── Assessment/
│   ├── Auth/
│   ├── Content/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Exceptions/             # Custom exceptions
│   ├── Academic/
│   ├── Assessment/
│   └── ...
├── Filament/               # Filament admin panel
│   ├── Resources/
│   │   ├── Academic/
│   │   ├── Assessment/
│   │   ├── Content/
│   │   ├── Media/
│   │   ├── Practice/
│   │   ├── Progress/
│   │   └── User/
│   ├── Pages/
│   │   ├── Dashboard.php
│   │   └── Auth/
│   ├── Widgets/
│   │   ├── StatsOverview.php
│   │   └── ...
│   ├── Clusters/           # Navigation groups (optional)
│   └── Panels/
│       └── AdminPanelProvider.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Academic/
│   │   │   ├── Assessment/
│   │   │   ├── Auth/
│   │   │   ├── Content/
│   │   │   ├── Practice/
│   │   │   ├── Progress/
│   │   │   └── User/
│   │   ├── Web/
│   │   └── Auth/
│   ├── Middleware/
│   ├── Requests/
│   │   ├── Academic/
│   │   ├── Assessment/
│   │   ├── Auth/
│   │   ├── Content/
│   │   ├── Practice/
│   │   ├── Progress/
│   │   └── User/
│   └── Resources/          # API Resources (JSON)
│       ├── Academic/
│       ├── Assessment/
│       ├── Content/
│       ├── Practice/
│       ├── Progress/
│       └── User/
├── Listeners/              # Event listeners
├── Models/                 # Eloquent models
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Media/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Notifications/          # Notification classes
├── Observers/              # Model observers
├── Policies/               # Authorization policies
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Providers/              # Service providers
├── Rules/                  # Custom validation rules
│   ├── Academic/
│   ├── Assessment/
│   └── ...
├── Services/               # Domain services (rare, explicit approval)
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── Traits/                 # Reusable model traits
│   ├── HasUuid.php
│   ├── HasSlug.php
│   ├── HasMedia.php
│   ├── HasSettings.php
│   └── ...
└── View/
    └── Components/         # Blade components
```

---

## database/ Directory

```
database/
├── factories/
│   ├── Academic/
│   ├── Assessment/
│   ├── Content/
│   ├── Media/
│   ├── Practice/
│   ├── Progress/
│   └── User/
├── migrations/
│   ├── 0000_00_00_000000_create_users_table.php
│   ├── 0000_00_00_000001_create_password_reset_tokens_table.php
│   ├── 2024_01_000000_create_academic_tables.php
│   ├── 2024_01_000001_create_content_tables.php
│   ├── 2024_01_000002_create_assessment_tables.php
│   ├── 2024_01_000003_create_practice_tables.php
│   ├── 2024_01_000004_create_progress_tables.php
│   ├── 2024_01_000005_create_user_tables.php
│   ├── 2024_01_000006_create_media_tables.php
│   └── 2024_01_000007_create_settings_tables.php
└── seeders/
    ├── Academic/
    │   ├── GradeSeeder.php
    │   ├── UnitSeeder.php
    │   ├── TopicSeeder.php
    │   └── LearningObjectiveSeeder.php
    ├── Content/
    │   └── TheoryArticleSeeder.php
    ├── Assessment/
    │   └── QuestionSeeder.php
    ├── User/
    │   ├── RolePermissionSeeder.php
    │   └── AdminUserSeeder.php
    ├── DatabaseSeeder.php
    └── DevelopmentSeeder.php (for local dev)
```

---

## resources/ Directory

```
resources/
├── css/
│   ├── app.css
│   └── filament/
│       ├── admin.css
│       └── custom-fields.css
├── js/
│   ├── app.js
│   ├── bootstrap.js
│   └── components/
│       └── (Vue/Alpine components if any)
├── lang/
│   ├── en/
│   │   ├── validation.php
│   │   ├── academic.php
│   │   ├── assessment.php
│   │   └── ...
│   └── tr/
│       ├── validation.php
│       ├── academic.php
│       ├── assessment.php
│       └── ...
├── views/
│   ├── components/
│   │   ├── layouts/
│   │   ├── forms/
│   │   └── ui/
│   ├── filament/
│   │   ├── resources/
│   │   └── pages/
│   ├── emails/
│   │   ├── auth/
│   │   └── notifications/
│   ├── livewire/
│   └── vendor/
│       └── filament/ (published views)
```

---

## routes/ Directory

```
routes/
├── web.php              # Web routes (Filament, auth)
├── api.php              # API routes (student app)
├── console.php          # Artisan commands
├── channels.php         # Broadcasting channels
└── filaments/
    └── admin.php        # Filament panel routes
```

---

## tests/ Directory

```
tests/
├── Feature/
│   ├── Academic/
│   │   ├── GradeTest.php
│   │   ├── UnitTest.php
│   │   ├── TopicTest.php
│   │   └── ObjectiveTest.php
│   ├── Assessment/
│   │   ├── QuestionTest.php
│   │   ├── ExamTest.php
│   │   └── ExamAttemptTest.php
│   ├── Content/
│   │   └── TheoryArticleTest.php
│   ├── Practice/
│   │   ├── PracticeSessionTest.php
│   │   ├── SpacedRepetitionTest.php
│   │   └── MasteryTest.php
│   ├── Progress/
│   │   └── ProgressReportTest.php
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegistrationTest.php
│   │   └── PasswordResetTest.php
│   ├── Filament/
│   │   ├── Academic/
│   │   ├── Assessment/
│   │   └── ...
│   └── Api/
│       └── ...
├── Unit/
│   ├── Enums/
│   │   ├── QuestionTypeTest.php
│   │   └── DifficultyTest.php
│   ├── Services/
│   │   ├── MasteryCalculatorTest.php
│   │   └── SpacedRepetitionSchedulerTest.php
│   └── Rules/
├── Pest.php
├── TestCase.php
└── CreatesApplication.php
```

---

## Filament Resource Structure (per resource)

```
app/Filament/Resources/
└── TopicResource/
    ├── TopicResource.php
    ├── Pages/
    │   ├── ListTopics.php
    │   ├── CreateTopic.php
    │   ├── EditTopic.php
    │   └── ViewTopic.php
    ├── RelationManagers/
    │   ├── QuestionsRelationManager.php
    │   ├── LearningObjectivesRelationManager.php
    │   └── TheoryArticlesRelationManager.php
    ├── Widgets/
    │   └── TopicStatsWidget.php
    └── TopicResource.php (main class)
```

---

## Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Directories | kebab-case | `app/Filament/Resources/TopicResource/` |
| PHP Classes | PascalCase | `TopicResource`, `StoreTopicRequest` |
| Methods | camelCase | `getTableColumns()`, `create()` |
| Variables | camelCase | `$topic`, `$questionCount` |
| Constants | UPPER_SNAKE_CASE | `MAX_QUESTIONS_PER_SESSION` |
| Config Keys | snake_case | `practice.min_questions` |
| Routes | kebab-case | `topics.index`, `practice-sessions.store` |
| Views | kebab-case | `filament.resources.topics.list.blade.php` |
| Migrations | snake_case, descriptive | `create_topics_table`, `add_order_to_topics` |
| Tables | snake_case, plural | `topics`, `question_attempts` |
| Columns | snake_case | `topic_id`, `created_at` |
| Indexes | `{table}_{columns}_index` | `topics_unit_id_order_index` |
| Foreign Keys | `{table}_{column}_foreign` | `topics_unit_id_foreign` |

---

*This structure is enforced by code review. Deviations require Architect approval.*