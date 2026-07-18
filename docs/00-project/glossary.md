# Project Glossary

## Purpose

Single source of truth for domain terminology. Ensures consistent language across code, documentation, and communication.

---

## Core Domain Terms

| Term | Definition | Turkish | Context |
|------|------------|---------|---------|
| **Grade** | Educational year level (9-12) | Sınıf | Academic structure |
| **Unit** | Major curriculum division within a grade | Ünite | Academic structure |
| **Topic** | Specific subject within a unit | Konu | Academic structure |
| **Learning Objective** | Measurable outcome for a topic | Kazanımlar | Academic structure |
| **Theory Content** | Explanatory material (text, media) | Teorik İçerik | Content domain |
| **Question** | Assessment item with answer | Soru | Assessment domain |
| **Question Type** | Format of question | Soru Tipi | Assessment domain |
| **Difficulty** | Cognitive demand level | Zorluk | Assessment domain |
| **Practice Session** | Student answering questions | Çalışma Oturumu | Learning domain |
| **Mastery** | Proficiency level on a topic | Hakimiyet | Progress domain |
| **Spaced Repetition** | Algorithm scheduling reviews | Aralıkla Tekrar | Learning domain |

---

## YKS-Specific Terms

| Term | Definition | Turkish |
|------|------------|---------|
| **YKS** | Higher Education Institutions Exam | Yükseköğretim Kurumları Sınavı |
| **TYT** | Basic Proficiency Test | Temel Yeterlilik Testi |
| **AYT** | Field Proficiency Test | Alan Yeterlilik Testi |
| **Session** | Exam section (Turkish, Math, Science, etc.) | Oturum |
| **Net Score** | Correct - (Wrong/4) | Net Puanı |
| **Raw Score** | Total correct answers | Ham Puan |
| **Weighted Score** | Score with subject coefficients | Ağırlıklı Puan |

---

## Technical Terms

| Term | Definition |
|------|------------|
| **Data Dictionary** | Approved schema definition before migration |
| **Domain** | Bounded context (Academic, Content, Assessment, etc.) |
| **Aggregate Root** | Entity controlling consistency boundary |
| **Value Object** | Immutable object defined by attributes |
| **Factory** | Test data generator (Laravel) |
| **Seeder** | Database population script |
| **Observer** | Model lifecycle event handler |
| **Policy** | Authorization logic class |
| **Resource** | Filament admin CRUD interface |
| **Relation Manager** | Filament nested resource management |

---

## User Roles

| Role | Permissions | Turkish |
|------|-------------|---------|
| **Student** | Practice, view progress, take exams | Öğrenci |
| **Teacher** | Manage classes, assignments, view reports | Öğretmen |
| **Content Editor** | Create/edit curriculum & questions | İçerik Editörü |
| **Admin** | Full system access, user management | Yönetici |
| **Super Admin** | System config, deployments, secrets | Süper Yönetici |

---

## Status Enum Values

### Content Status
| Value | Label | Description |
|-------|-------|-------------|
| `draft` | Taslak | Work in progress, not visible to students |
| `review` | İnceleme | Submitted for editorial review |
| `published` | Yayında | Live for students |
| `archived` | Arşivlendi | Retired but preserved |

### Question Status
| Value | Label | Description |
|-------|-------|-------------|
| `draft` | Taslak | Incomplete or unreviewed |
| `approved` | Onaylandı | Quality checked, ready for use |
| `deprecated` | Kullanım Dışı | Replaced, not for new practice |

### Mastery Levels
| Value | Label | Range | Description |
|-------|-------|-------|-------------|
| `novice` | Başlangıç | 0-29% | New to topic |
| `learning` | Öğrenme | 30-59% | Making progress |
| `proficient` | Yeterli | 60-79% | Solid understanding |
| `mastered` | Hakim | 80-100% | Ready for advanced |

---

## Naming Conventions in Code

| Concept | Model | Table | Resource | Policy |
|---------|-------|-------|----------|--------|
| Grade | `Grade` | `grades` | `GradeResource` | `GradePolicy` |
| Unit | `Unit` | `units` | `UnitResource` | `UnitPolicy` |
| Topic | `Topic` | `topics` | `TopicResource` | `TopicPolicy` |
| Objective | `LearningObjective` | `learning_objectives` | `LearningObjectiveResource` | `LearningObjectivePolicy` |
| Theory | `TheoryArticle` | `theory_articles` | `TheoryArticleResource` | `TheoryArticlePolicy` |
| Question | `Question` | `questions` | `QuestionResource` | `QuestionPolicy` |
| Practice | `PracticeSession` | `practice_sessions` | `PracticeSessionResource` | `PracticeSessionPolicy` |
| Mastery | `TopicMastery` | `topic_masteries` | (internal) | `MasteryPolicy` |

---

## Acronyms

| Acronym | Full Form |
|---------|-----------|
| MEB | Milli Eğitim Bakanlığı (Ministry of Education) |
| YKS | Yükseköğretim Kurumları Sınavı |
| TYT | Temel Yeterlilik Testi |
| AYT | Alan Yeterlilik Testi |
| SR | Spaced Repetition |
| SM-2 | SuperMemo 2 Algorithm |
| DDD | Domain-Driven Design |
| ADR | Architecture Decision Record |
| DoR | Definition of Ready |
| DoD | Definition of Done |
| SSOT | Single Source of Truth |

---

*Update this glossary when new domain terms emerge. Reference in PR descriptions.*