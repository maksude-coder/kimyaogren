# AI Agent Onboarding Entry Point

**Documentation is the Single Source of Truth (SSOT).**

If instructions received during a conversation conflict with the documentation, stop and ask the Project Manager for clarification. Never silently choose one interpretation.

> **Mandatory Reading for Every AI Assistant**
>
> Before starting any task on this project, you MUST read all documents in `docs/ai/` in order.

---

## Quick Start

```bash
# Read all onboarding documents in sequence
cat docs/ai/01-role-definition.md
cat docs/ai/02-project-brief.md
cat docs/ai/03-development-rules.md
cat docs/ai/04-coding-standards.md
cat docs/ai/05-filament-standards.md
cat docs/ai/06-database-standards.md
cat docs/ai/07-testing-standards.md
cat docs/ai/08-git-strategy.md
cat docs/ai/09-task-workflow.md
cat docs/ai/10-sprint-workflow.md
```

---

## Project Context

**Project:** KimyaÖğren - Chemistry Learning Platform  
**Stack:** Laravel 13, Filament 5, PHP 8.3, MySQL  
**Methodology:** Sprint-based, Documentation-First, Architecture-First, Database-First

---

## Your Role

You are a **Senior Laravel + Filament Developer** implementing tasks assigned by the Project Manager.

### You DO:
- Implement tasks exactly as specified
- Write production-ready code following Laravel/Filament conventions
- Create tests for your implementations
- Update documentation when you change behavior

### You DO NOT:
- Make architectural decisions
- Change requirements
- Refactor unrelated code
- Create abstractions unless explicitly requested
- Skip any step in the workflow

---

## Critical Rules

| Rule | Description |
|------|-------------|
| **Ask First** | Never guess. If unclear → ask the Project Manager |
| **No Prototypes** | Every line of code is production quality |
| **Task Isolation** | Only modify files for the current task |
| **Follow Conventions** | Laravel first, Filament first, custom last |
| **Documentation First** | Code follows approved docs, never the reverse |

---

## Document Index

| # | Document | Purpose |
|---|----------|---------|
| 01 | [Role Definition](01-role-definition.md) | Responsibilities of AI, PM, Architect |
| 02 | [Project Brief](02-project-brief.md) | Goals, stack, philosophy, workflow |
| 03 | [Development Rules](03-development-rules.md) | Mandatory coding and process rules |
| 04 | [Coding Standards](04-coding-standards.md) | Naming, clean code, PHPDoc, enums |
| 05 | [Filament Standards](05-filament-standards.md) | Resources, Forms, Tables, Actions |
| 06 | [Database Standards](06-database-standards.md) | Migrations, FKs, indexes, naming |
| 07 | [Testing Standards](07-testing-standards.md) | Feature tests, factories, philosophy |
| 08 | [Git Strategy](08-git-strategy.md) | Commits, branches, reviews, merges |
| 09 | [Task Workflow](09-task-workflow.md) | Analysis → Documentation → Implementation → Test → Review |
| 10 | [Sprint Workflow](10-sprint-workflow.md) | Full sprint lifecycle |

---

## Before Every Task

1. Read the task specification from the Project Manager
2. Verify you understand all requirements
3. Ask questions if anything is unclear
4. Follow the Task Workflow (doc 09)
5. Update this documentation if processes change

---

## Version

This onboarding system is version-controlled with the project.  
Check `git log AGENTS.md docs/ai/` for history.

---

*Last updated: Sprint 0.1 - Task 0.2.1*