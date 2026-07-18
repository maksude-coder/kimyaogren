# Project Brief

## Project Overview

**Name:** KimyaÖğren (Chemistry Learn)  
**Type:** Chemistry Learning Platform for Turkish High School Students  
**Target:** 9th-12th grade students preparing for YKS (Yükseköğretim Kurumları Sınavı)  
**Language:** Turkish (primary), English (technical)  

---

## Vision

Create the most effective, curriculum-aligned chemistry learning platform for Turkish high school students, combining:
- Interactive theory with real-world examples
- Adaptive practice with spaced repetition
- YKS-aligned question bank with detailed explanations
- Progress tracking with actionable insights

---

## Technology Stack

| Layer | Technology | Version | Rationale |
|-------|------------|---------|-----------|
| Framework | Laravel | 13.x | Modern PHP, excellent ecosystem |
| Admin Panel | Filament | 5.x | Best Laravel admin panel |
| Language | PHP | 8.3+ | Performance, type safety |
| Database | MySQL | 8.0+ | ACID, JSON support, mature |
| Frontend | Livewire + Alpine.js | Latest | Server-driven reactivity |
| Testing | Pest PHP | Latest | Modern, expressive testing |
| CI/CD | GitHub Actions | - | Integrated with repo |

---

## Project Philosophy

### Documentation-First

> No code is written before documentation exists and is approved.

- Every feature has a Data Dictionary before migrations
- Every task has a specification before implementation
- Architecture decisions are documented before code

### Architecture-First

> The system is designed before it is built.

- Domain boundaries defined by Architect
- Database schema designed before migrations
- API contracts defined before implementation

### Database-First

> The database is the source of truth.

- Migrations are generated from Data Dictionaries
- Foreign keys and constraints are mandatory
- Indexes planned before creation
- No `nullable()` without justification

### Sprint-Based Delivery

> Work is organized in time-boxed sprints with clear goals.

- 2-week sprints
- Definition of Done enforced
- Retrospectives drive improvement

---

## Core Domains

| Domain | Description | Status |
|--------|-------------|--------|
| **User Management** | Authentication, roles, profiles | Planned |
| **Curriculum** | Units, topics, learning objectives (MEB aligned) | Planned |
| **Content** | Theory articles, videos, interactive simulations | Planned |
| **Practice** | Questions, quizzes, spaced repetition | Planned |
| **Assessment** | Mock exams, YKS simulations, analytics | Planned |
| **Progress** | Learning paths, mastery tracking, reports | Planned |
| **Admin** | Content management, user management, analytics | Planned |

---

## Key Features (MVP)

### Phase 1: Foundation (Sprints 1-3)
- User authentication (Sanctum)
- Role-based access (Student, Teacher, Admin)
- Curriculum structure (Grade → Unit → Topic → Objective)
- Basic admin panel with Filament

### Phase 2: Content & Practice (Sprints 4-7)
- Theory content management (rich text, media)
- Question bank (multiple choice, open-ended)
- Practice modes (topic, mixed, timed)
- Spaced repetition algorithm

### Phase 3: Assessment & Analytics (Sprints 8-11)
- Mock YKS exams
- Progress dashboards
- Weakness identification
- Teacher reports

### Phase 4: Polish & Scale (Sprints 12+)
- Mobile responsiveness
- Offline support (PWA)
- Performance optimization
- Advanced analytics

---

## Success Metrics

| Metric | Target |
|--------|--------|
| Active monthly students | 10,000+ |
| Question completion rate | > 70% |
| Return rate (7-day) | > 40% |
| YKS score improvement | Measurable |
| Content coverage (MEB) | 100% |

---

## Constraints & Non-Goals

### Constraints
- Turkish language first (content, UI)
- MEB curriculum alignment mandatory
- YKS question format compliance
- Mobile-first responsive design
- GDPR/KVKK compliance

### Non-Goals (MVP)
- AI-generated content
- Social features (forums, chat)
- Video streaming platform
- Mobile native apps
- Multi-language support
- Gamification (badges, leaderboards)

---

## Team Structure

| Role | Responsibility |
|------|----------------|
| Project Manager | Requirements, sprint planning, acceptance |
| Architect | Architecture, data dictionary, technical decisions |
| AI Assistant (You) | Implementation, testing, documentation |

---

## Documentation Standards

All documentation follows:
- **Markdown** format
- **English** for technical docs
- **Turkish** for user-facing content specs
- **Version controlled** with code
- **Cross-referenced** (no duplication)

---

## References

- [MEB Chemistry Curriculum](https://mufredat.meb.gov.tr/)
- [YKS Chemistry Syllabus](https://www.osym.gov.tr/)
- [Laravel 13 Documentation](https://laravel.com/docs/13.x)
- [Filament 5 Documentation](https://filamentphp.com/docs/5.x)

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*