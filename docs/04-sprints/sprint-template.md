# Sprint Template

## Purpose

Standard template for sprint planning, execution, and review documentation.

---

## Sprint {N}: {Sprint Name}

### Sprint Goal
> {One sentence describing the sprint objective}

---

## Sprint Metadata

| Field | Value |
|-------|-------|
| Sprint Number | {N} |
| Start Date | YYYY-MM-DD |
| End Date | YYYY-MM-DD |
| Duration | 10 working days |
| Scrum Master | {Name} |
| Product Owner | {Name} |
| Team | AI Developer + PM + Architect |

---

## Capacity Planning

| Role | Availability | Focus Factor | Effective Hours |
|------|--------------|--------------|-----------------|
| AI Developer | 100% | 0.8 | 64 |
| Project Manager | 20% | 0.5 | 8 |
| Architect | 10% | 0.5 | 4 |
| **Total** | | | **~76** |

**Velocity Baseline:** {X} points (use previous sprint actuals)

---

## Sprint Backlog (Committed)

| Task ID | Task | Points | Assignee | Status |
|---------|------|--------|----------|--------|
| {NN.N} | {Task description} | {N} | {Name} | Ready/In Progress/Review/Done |
| {NN.N} | ... | ... | ... | ... |

**Total Committed:** {X} points

---

## Sprint Board

```
BACKLOG → READY → IN PROGRESS → IN REVIEW → DONE
                │                │
                ▼                ▼
           BLOCKED           BLOCKED
```

### WIP Limits
- **In Progress:** 1 per developer
- **In Review:** 2 max

---

## Daily Standup Notes

### Day 1 (Mon) - Sprint Planning
- [ ] Sprint goal confirmed
- [ ] Backlog prioritized
- [ ] Tasks estimated and committed
- [ ] Dependencies identified

### Day 2-9 (Tue-Fri)
| Date | Completed | In Progress | Blockers | Notes |
|------|-----------|-------------|----------|-------|
| YYYY-MM-DD | Task NN.N | Task NN.N | None | ... |

### Day 10 (Fri) - Sprint Review & Retrospective
- [ ] Demo completed features
- [ ] Metrics reviewed
- [ ] Retrospective held
- [ ] Action items captured

---

## Metrics Tracking

### Sprint Metrics

| Metric | Target | Actual |
|--------|--------|--------|
| Velocity (points completed) | {Planned} | {Actual} |
| Commitment Reliability | >85% | {XX}% |
| Defects Found | <5% | {N} |
| Test Coverage (new code) | >80% | {XX}% |
| Code Style Violations | 0 | {N} |

### Burndown

```
Points
  █
  ██
  ███
  ████
  █████
  ██████
  ███████
  ████████
  █████████
  ██████████
  1 2 3 4 5 6 7 8 9 10
  Day
```

---

## Sprint Review

### Demo Script
1. {Feature 1} - {Description}
2. {Feature 2} - {Description}
3. {Feature 3} - {Description}

### Stakeholder Feedback
| Feedback | Action |
|----------|--------|
| {Feedback} | {Accept/Defer/Reject} |

### Accepted Work
- [ ] Task NN.N - {Description}
- [ ] Task NN.N - {Description}

### Carryover (Return to Backlog)
| Task | Reason | Re-estimate |
|------|--------|-------------|
| NN.N | {Reason} | {Points} |

---

## Sprint Retrospective

### What Went Well
- {Item 1}
- {Item 2}

### What Didn't Go Well
- {Item 1}
- {Item 2}

### Action Items
| Action | Owner | Due | Status |
|--------|-------|-----|--------|
| {Action} | {Name} | {Date} | Open/In Progress/Done |

### Process Improvements
- {Improvement 1}
- {Improvement 2}

---

## Sprint Artifacts

### Generated Documents
- [ ] Sprint Plan (this document)
- [ ] Updated backlog priorities
- [ ] Updated roadmap/milestones
- [ ] Changelog entry

### Code
- [ ] All PRs merged to `develop`
- [ ] Release branch created (if applicable)
- [ ] Tag created (if release sprint)

---

## Sign-Off

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Product Owner | | | |
| Scrum Master | | | |
| Team Lead | | | |

---

*Template version 1.0. Create as `docs/04-sprints/sprint-{N}-{name}.md`*