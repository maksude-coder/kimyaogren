# Architecture Overview

## Purpose

High-level system architecture for KimyaÖğren. Defines structural principles, domain boundaries, and technical strategy.

---

## Architectural Principles

| Principle | Application |
|-----------|-------------|
| **Documentation-First** | No code without approved docs (ADR, Data Dictionary) |
| **Architecture-First** | Domain boundaries defined before implementation |
| **Database-First** | Schema is source of truth; code derives from it |
| **Domain-Driven Design** | Bounded contexts align with business capabilities |
| **Laravel Convention First** | Use framework defaults before custom patterns |
| **Filament Convention First** | Admin panel uses Filament patterns |
| **Single Source of Truth** | Documentation = specification |
| **Explicit over Implicit** | No magic; configuration over convention |

---

## System Context

```
┌─────────────────────────────────────────────────────────────────┐
│                        KİMYAÖĞREN PLATFORM                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌──────────────────┐   │
│  │   STUDENT   │    │   TEACHER   │    │  CONTENT EDITOR  │   │
│  │   (PWA)     │    │   (Admin)   │    │    (Admin)       │   │
│  └──────┬──────┘    └──────┬──────┘    └────────┬─────────┘   │
│         │                  │                      │            │
│         └──────────────────┼──────────────────────┘            │
│                            ▼                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    LARAVEL APPLICATION                     │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐   │   │
│  │  │ Academic │ │ Content  │ │Assessment│ │  Progress  │   │   │
│  │  │ Domain   │ │ Domain   │ │ Domain   │ │  Domain    │   │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └────────────┘   │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐   │   │
│  │  │  Media   │ │  User    │ │ Settings │ │   Blog     │   │   │
│  │  │ Domain   │ │ Domain   │ │ Domain   │ │  Domain    │   │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └────────────┘   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                            │                                   │
│                            ▼                                   │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                      MYSQL 8.0 DATABASE                    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Domain Architecture

### Bounded Contexts

| Domain | Responsibility | Key Models | Filament Resources |
|--------|---------------|------------|-------------------|
| **Academic** | Curriculum structure | Grade, Unit, Topic, LearningObjective | GradeResource, UnitResource, TopicResource, ObjectiveResource |
| **Content** | Theory articles, media | TheoryArticle, Media, Tag | TheoryArticleResource, MediaResource |
| **Assessment** | Questions, exams | Question, QuestionOption, Exam, ExamSection | QuestionResource, ExamResource |
| **Practice** | Spaced repetition, sessions | PracticeSession, QuestionAttempt, TopicMastery | (Internal - no admin) |
| **Progress** | Mastery tracking, analytics | TopicMastery, StudentProgress, WeakAreaReport | ProgressDashboard (Widget) |
| **User** | Auth, roles, profiles | User, Role, Permission, Class | UserResource, ClassResource |
| **Media** | File upload, transformation | Media, MediaConversion | MediaResource (Filament Media Library) |
| **Settings** | System configuration | Setting, FeatureFlag | SettingsPage |

### Domain Interaction Rules

```
Academic ◄──── Content    (Content belongs to Topic)
Academic ◄──── Assessment (Questions tagged to Topic/Objective)
Assessment ◄── Practice  (Practice uses Questions)
Practice ───► Progress   (Attempts update Mastery)
User ──────► All         (Users interact with all domains)
```

**No circular dependencies between domains.**

---

## Technical Stack

| Layer | Technology | Version | Rationale |
|-------|------------|---------|-----------|
| Framework | Laravel | 13.x | Modern PHP, excellent ecosystem |
| Admin Panel | Filament | 5.x | Best Laravel admin, React-free |
| Language | PHP | 8.3+ | Performance, type safety |
| Database | MySQL | 8.0+ | ACID, JSON, CTE, mature |
| Frontend | Livewire + Alpine | Latest | Server-driven, minimal JS |
| Testing | Pest | Latest | Expressive, parallel |
| Code Style | Laravel Pint | Latest | Zero-config, Laravel-aligned |
| Queue | Database/Redis | - | Jobs, scheduling |
| Cache | Redis | - | Sessions, cache |
| Search | MySQL FTS / Meilisearch | - | Full-text search |
| Storage | S3-compatible | - | Media files |

---

## Data Flow Patterns

### Command (Write)
```
HTTP Request
    ▼
Form Request (Validation)
    ▼
Controller / Action
    ▼
Domain Service / Model
    ▼
Database Transaction
    ▼
Event Dispatched
    ▼
Response
```

### Query (Read)
```
HTTP Request / Livewire
    ▼
Query Builder / Eloquent
    ▼
Database (with indexes)
    ▼
Resource / API Response
    ▼
View / Component
```

---

## Security Architecture

| Layer | Implementation |
|-------|---------------|
| Authentication | Laravel Sanctum (SPA + API tokens) |
| Authorization | Policies + Gates (per-domain) |
| Input Validation | Form Requests (every endpoint) |
| SQL Injection | Eloquent/Query Builder (parameterized) |
| XSS | Blade auto-escape, Filament sanitization |
| CSRF | Built-in (web), Sanctum (API) |
| Rate Limiting | Laravel Rate Limiter (login, API) |
| Secrets | Environment variables, never in code |

---

## Deployment Architecture

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   GitHub    │────►│  GitHub     │────►│  Staging    │
│   (main)    │     │  Actions    │     │  (Render/   │
└─────────────┘     │  (CI/CD)    │     │  Laravel    │
                    └──────┬──────┘     │  Cloud)     │
                           │            └──────┬──────┘
                           ▼                   │
                    ┌─────────────┐            │
                    │  Docker     │            ▼
                    │  Build      │     ┌─────────────┐
                    └─────────────┘     │ Production  │
                           │            │  (AWS/      │
                           ▼            │  Laravel    │
                    ┌─────────────┐     │  Cloud)     │
                    │  Registry   │     └─────────────┘
                    │  (GHCR)     │
                    └─────────────┘
```

---

## Non-Functional Requirements

| Requirement | Target | Measurement |
|-------------|--------|-------------|
| **Availability** | 99.9% | Uptime monitor |
| **Response Time (p95)** | < 200ms | APM (Laravel Telescope/Blackfire) |
| **Database Query (p95)** | < 50ms | Slow query log |
| **Concurrent Users** | 10,000 | Load test |
| **Data Retention** | 7 years (legal) | Backup policy |
| **Backup RPO** | 1 hour | Point-in-time recovery |
| **Backup RTO** | 4 hours | Restore test quarterly |

---

## Related Documents

- [Folder Structure](folder-structure.md) - Code organization
- [Modules](modules.md) - Domain module specifications
- [Dependencies](dependencies.md) - External integrations
- [ADR 0001](adr-0001-documentation-first.md) - Documentation-First
- [ADR 0002](adr-0002-laravel-filament-stack.md) - Stack Decision
- [ADR 0003](adr-0003-domain-driven-design.md) - DDD Structure
- [Data Dictionary](../02-data/data-dictionary.md) - Schema source of truth

---

*Architecture evolves through ADRs. This document reflects accepted decisions only.*