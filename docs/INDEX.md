# KimyaÖğren Documentation Index

> **Documentation is the Single Source of Truth (SSOT).**

This index provides a complete overview of the project documentation hierarchy. Use this as the main entry point for navigating all project documentation.

---

## Documentation Hierarchy

```
docs/
├── INDEX.md                          # This file - main entry point
├── AGENTS.md                         # AI onboarding entry point
├── ai/                               # AI assistant onboarding (10 documents)
│   ├── README.md
│   ├── 01-role-definition.md
│   ├── 02-project-brief.md
│   ├── 03-development-rules.md
│   ├── 04-coding-standards.md
│   ├── 05-filament-standards.md
│   ├── 06-database-standards.md
│   ├── 07-testing-standards.md
│   ├── 08-git-strategy.md
│   ├── 09-task-workflow.md
│   └── 10-sprint-workflow.md
├── 00-project/                       # Project vision & governance
│   ├── vision.md
│   ├── roadmap.md
│   ├── milestones.md
│   ├── glossary.md
│   └── changelog.md
├── 01-architecture/                  # System architecture
│   ├── overview.md
│   ├── folder-structure.md
│   ├── modules.md
│   ├── dependencies.md
│   ├── domain-academic.md
│   ├── domain-content.md
│   ├── domain-assessment.md
│   ├── domain-practice.md
│   ├── domain-progress.md
│   ├── domain-user.md
│   ├── domain-media.md
│   └── domain-settings.md
├── 02-data/                          # Data layer documentation
│   ├── README.md
│   ├── data-dictionary.md
│   ├── naming-conventions.md
│   ├── enums.md
│   └── relationships.md
├── 03-development/                   # Development standards
│   ├── coding-standards.md
│   ├── definition-of-ready.md
│   ├── definition-of-done.md
│   └── checklists/
│       ├── migration.md
│       ├── model.md
│       ├── resource.md
│       ├── testing.md
│       ├── review.md
│       └── release.md
├── 04-sprints/                       # Sprint management
│   ├── README.md
│   └── sprint-template.md
├── 05-backlog/                       # Product backlog
│   ├── README.md
│   ├── epic-00-project-foundation.md
│   ├── epic-01-academic-domain.md
│   └── epic-template.md
└── 06-adr/                           # Architecture Decision Records
    ├── README.md
    ├── 000-template.md
    └── 001-project-principles.md
```

---

## Quick Navigation

### For New Team Members (Human)

| Start Here | Then Read |
|------------|-----------|
| [Project Vision](00-project/vision.md) | [Roadmap](00-project/roadmap.md) |
| [Architecture Overview](01-architecture/overview.md) | [Development Standards](03-development/coding-standards.md) |
| [Definition of Done](03-development/definition-of-done.md) | [Glossary](00-project/glossary.md) |

### For AI Assistants

**Mandatory Reading Order:**
1. [AGENTS.md](../AGENTS.md) - Entry point
2. [docs/ai/README.md](ai/README.md) - AI onboarding guide
3. All 10 documents in `docs/ai/` sequentially

### For Project Managers

| Document | Purpose |
|----------|---------|
| [Roadmap](00-project/roadmap.md) | Release planning |
| [Milestones](00-project/milestones.md) | Progress tracking |
| [Backlog](05-backlog/README.md) | Epic management |
| [Sprint Template](04-sprints/sprint-template.md) | Sprint planning |

### For Architects

| Document | Purpose |
|----------|---------|
| [Architecture Overview](01-architecture/overview.md) | System design |
| [Modules](01-architecture/modules.md) | Domain boundaries |
| [Dependencies](01-architecture/dependencies.md) | External integrations |
| [ADRs](06-adr/README.md) | Architecture decisions |

### For Developers

| Document | Purpose |
|----------|---------|
| [Coding Standards](03-development/coding-standards.md) | Code conventions |
| [Data Dictionary](02-data/data-dictionary.md) | Database schema |
| [Checklists](03-development/checklists/) | Quality gates |
| [Definition of Ready](03-development/definition-of-ready.md) | Task readiness |

---

## Document Maintenance

| Rule | Description |
|------|-------------|
| **SSOT** | Documentation is the single source of truth |
| **Version Control** | All docs in git with code |
| **Update with Code** | Documentation updated in same PR as code changes |
| **Cross-Reference** | Link related docs, avoid duplication |
| **Review** | Docs reviewed in sprint review |

---

## Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | Sprint 0.2 | AI Assistant | Initial documentation structure |

---

*Last updated: Sprint 0.2 - Task 0.2.1*