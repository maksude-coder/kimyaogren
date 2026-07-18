# Sprint Management README

## Purpose

This directory contains all sprint-related documentation for the KimyaÖğren project.

---

## Directory Structure

```
docs/04-sprints/
├── README.md                    # This file
├── sprint-template.md           # Template for sprint documentation
├── sprint-01-plan.md            # Sprint 1 plan
├── sprint-01-review.md          # Sprint 1 review
├── sprint-01-retro.md           # Sprint 1 retrospective
├── sprint-02-plan.md            # ...
```

---

## Sprint Lifecycle

```
PLANNING → EXECUTION → REVIEW → RETROSPECTIVE
    ↓           ↓           ↓            ↓
  sprint-N-  sprint-N-  sprint-N-   sprint-N-
   plan.md    daily.md   review.md   retro.md
```

---

## Sprint Documents

### Sprint Plan (`sprint-{N}-plan.md`)
Created during Sprint Planning (Monday, Week 1)
- Sprint goal
- Committed backlog items
- Capacity planning
- Schedule
- Risks

### Daily Standup Notes (Optional)
Can be captured in GitHub Discussions or Slack, linked from plan

### Sprint Review (`sprint-{N}-review.md`)
Created on last day (Friday, Week 2)
- Demo notes
- Acceptance decisions
- Metrics
- Stakeholder feedback
- Carryover items

### Sprint Retrospective (`sprint-{N}-retro.md`)
Created after review (Friday, Week 2)
- What went well
- What didn't
- Action items
- Process improvements

---

## Sprint Cadence

| Week | Day | Activity | Output |
|------|-----|----------|--------|
| 1 | Mon | Sprint Planning | `sprint-N-plan.md` |
| 1 | Tue-Fri | Development | Code, PRs, tests |
| 2 | Mon-Thu | Development | Code, PRs, tests |
| 2 | Fri AM | Sprint Review | `sprint-N-review.md` |
| 2 | Fri PM | Retrospective | `sprint-N-retro.md` |

---

## Sprint Naming

| Sprint | Focus | Naming |
|--------|-------|--------|
| 0.1-0.5 | Foundation | `sprint-00-foundation` |
| 1.1-1.6 | Academic Domain | `sprint-01-curriculum`, `sprint-02-topics`, etc. |
| 2.1-2.8 | Practice Engine | `sprint-07-questions`, `sprint-10-spaced-repetition` |
| 3.1-3.6 | Assessment | `sprint-13-quiz-engine`, `sprint-14-yks-simulation` |
| 4.1-4.6 | Platform | `sprint-19-pwa`, `sprint-22-security` |

---

## Sprint Metrics

### Tracked Per Sprint

| Metric | Target | Source |
|--------|--------|--------|
| Velocity | Stable/Increasing | Completed story points |
| Commitment Reliability | >85% | Completed / Committed |
| Defect Rate | <5% | Bugs found in sprint / Stories |
| Test Coverage (new) | >80% | Pest coverage report |
| Code Style | 0 violations | Pint report |

### Velocity Chart

```
Sprint 01: ████████████ 13 pts
Sprint 02: ██████████████ 16 pts
Sprint 03: ███████████████ 18 pts
...
```

---

## Sprint Planning Process

### Inputs
- Prioritized Product Backlog
- Team capacity (from capacity planning)
- Previous sprint velocity
- Architectural constraints (from Architect)

### Process
1. **PM presents** top backlog items
2. **Architect reviews** technical feasibility
3. **Team estimates** each item (Planning Poker: 1,2,3,5,8,13)
4. **Team selects** items up to capacity
5. **PM defines** Sprint Goal
6. **All commit** to Sprint Backlog

### Definition of Ready (for Sprint Backlog items)
- [ ] Clear acceptance criteria
- [ ] Data Dictionary approved (if DB changes)
- [ ] Architecture reviewed (if new patterns)
- [ ] Dependencies identified
- [ ] Estimated by team

---

## Sprint Review Checklist

- [ ] All committed stories demoed
- [ ] Acceptance criteria verified
- [ ] Metrics presented
- [ ] Stakeholder feedback captured
- [ ] Carryover items identified
- [ ] Next sprint preview given

---

## Retrospective Format

### Start / Stop / Continue

| Start | Stop | Continue |
|-------|------|----------|
| New practice | Harmful practice | Good practice |

### Action Items Template

| Action | Owner | Due | Status |
|--------|-------|-----|--------|
| {Action} | {Name} | {Date} | Open |

---

## Tools & Templates

- **Planning:** `sprint-template.md`
- **Tracking:** GitHub Projects (Kanban)
- **Metrics:** GitHub Actions + custom scripts
- **Retro:** Miro/Notion/Markdown

---

## References

- [Sprint Template](sprint-template.md)
- [Backlog README](../05-backlog/README.md)
- [Task Workflow](../../ai/09-task-workflow.md)
- [Sprint Workflow](../../ai/10-sprint-workflow.md)

---

*Sprint documentation is the historical record of project progress. Keep it accurate and complete.*