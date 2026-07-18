# Product Backlog README

## Purpose

The product backlog is the single authoritative list of everything needed in the KimyaÖğren platform. It is prioritized by the Project Manager and refined collaboratively.

---

## Backlog Structure

```
Product Backlog
├── Epics (Large initiatives)
│   ├── Stories (User-facing features)
│   │   ├── Tasks (Implementation steps)
│   │   └── Bugs (Defects)
│   └── Spikes (Research)
└── Technical Debt (Maintenance)
```

---

## Item Types

| Type | Prefix | Description | Estimation |
|------|--------|-------------|------------|
| **Epic** | `EPIC-` | Large initiative spanning multiple sprints | T-shirt (S/M/L/XL) |
| **Story** | `US-` | User-facing feature with acceptance criteria | Story points (1,2,3,5,8) |
| **Task** | `TASK-` | Implementation step for a story | Hours or points |
| **Bug** | `BUG-` | Defect in existing functionality | Points (usually 1-3) |
| **Spike** | `SPIKE-` | Time-boxed research | Time-box (1-3 days) |
| **Tech Debt** | `DEBT-` | Refactoring, upgrades, cleanup | Points |

---

## Backlog States

```
ICEBOX → REFINING → READY → SPRINT BACKLOG → IN PROGRESS → IN REVIEW → DONE
```

| State | Criteria |
|-------|----------|
| **Icebox** | Captured, not yet prioritized |
| **Refining** | Being analyzed, Data Dictionary in progress |
| **Ready** | Meets Definition of Ready, estimated, prioritized |
| **Sprint Backlog** | Committed for current sprint |
| **In Progress** | Actively being worked on |
| **In Review** | PR open, awaiting approval |
| **Done** | Merged, deployed to staging, verified |

---

## Prioritization Framework

### WSJF (Weighted Shortest Job First)

```
Priority = (Business Value + Time Criticality + Risk Reduction) / Job Size
```

| Factor | Scale (1-10) | Description |
|--------|--------------|-------------|
| **Business Value** | 1-10 | Revenue, user impact, strategic alignment |
| **Time Criticality** | 1-10 | Deadline, market window, compliance |
| **Risk Reduction** | 1-10 | Learning, uncertainty reduction |
| **Job Size** | 1-13 (points) | Effort estimate |

### MoSCoW (for Release Planning)

| Category | Description | % of Release |
|----------|-------------|--------------|
| **Must Have** | Non-negotiable, release fails without | 60% |
| **Should Have** | High value, workaround exists | 20% |
| **Could Have** | Nice to have, low risk | 15% |
| **Won't Have** | This release | 5% |

---

## Definition of Ready (DoR)

A backlog item is **Ready** when:

- [ ] Clear, testable acceptance criteria
- [ ] Data Dictionary approved by Architect (if DB changes)
- [ ] Dependencies identified and resolved
- [ ] Estimated by team (Planning Poker)
- [ ] Sized ≤ 5 points (split if larger)
- [ ] UI/UX mockups available (if user-facing)
- [ ] API contract defined (if API)
- [ ] Security review completed (if auth/data)
- [ ] Prioritized by PM

---

## Definition of Done (DoD)

A backlog item is **Done** when:

- [ ] Code complete per acceptance criteria
- [ ] Unit tests written (≥80% coverage on new code)
- [ ] Feature tests written (Pest)
- [ ] All tests pass (local + CI)
- [ ] Code style passes (Pint)
- [ ] Static analysis passes (PHPStan if configured)
- [ ] Self-review completed
- [ ] PR approved by PM or Architect
- [ ] Merged to `develop`
- [ ] Deployed to staging
- [ ] Verified on staging by PM
- [ ] Documentation updated (CHANGELOG, API docs, user guide)
- [ ] No critical/major bugs open

---

## Backlog Grooming (Refinement)

### Cadence
- **Weekly** (30 min) during sprint
- **Pre-planning** (1 hour) before sprint planning

### Activities
1. **Review new items** from stakeholders
2. **Clarify** acceptance criteria
3. **Split** large stories (>5 pts)
5. **Estimate** unestimated items
6. **Re-prioritize** based on new info
7. **Identify dependencies** and risks

### Participants
- PM (facilitator)
- Architect (technical guidance)
- AI Developer (estimation)
- QA (testability input)

---

## Backlog Management Tools

| Tool | Purpose |
|------|---------|
| **GitHub Projects** | Kanban board, sprint tracking |
| **GitHub Issues** | Item tracking, discussion |
| **Milestones** | Sprint goals, release tracking |
| **Labels** | Type, priority, domain, status |
| **Markdown Files** | Epics, documentation |

### GitHub Labels

| Label | Color | Purpose |
|-------|-------|---------|
| `type:epic` | Purple | Epic |
| `type:story` | Green | User story |
| `type:task` | Blue | Task |
| `type:bug` | Red | Bug |
| `type:spike` | Orange | Research |
| `type:debt` | Gray | Tech debt |
| `priority:must` | Red | MoSCoW Must |
| `priority:should` | Orange | MoSCoW Should |
| `priority:could` | Yellow | MoSCoW Could |
| `domain:academic` | Teal | Academic domain |
| `domain:content` | Indigo | Content domain |
| `domain:assessment` | Purple | Assessment domain |
| `status:ready` | Green | Definition of Ready met |
| `status:blocked` | Red | Blocked |

---

## Backlog Health Metrics

| Metric | Target | Warning |
|--------|--------|---------|
| **Ready items** | > 2 sprints worth | < 1 sprint |
| **Average age** | < 30 days | > 60 days |
| **Icebox size** | < 50 items | > 100 items |
| **Refinement rate** | 100% of sprint items | < 80% |
| **Carryover rate** | < 10% | > 20% |

---

## Release Planning

### Release Types

| Type | Cadence | Scope |
|------|---------|-------|
| **Major** | Quarterly | New domains, breaking changes |
| **Minor** | Monthly | New features, backward compatible |
| **Patch** | Weekly/As needed | Bug fixes, security |

### Release Criteria
- All `Must Have` stories Done
- Zero critical bugs
- Performance benchmarks met
- Security scan clean
- Documentation complete
- PM sign-off

---

## Backlog Governance

| Role | Responsibility |
|------|----------------|
| **PM** | Prioritization, acceptance, stakeholder communication |
| **Architect** | Technical feasibility, technical debt prioritization |
| **AI Developer** | Estimation, technical implementation decisions |
| **Stakeholders** | Business value input, feedback |

### Decision Rights

| Decision | Owner |
|----------|-------|
| What to build next | PM |
| How to build it | AI Developer + Architect |
| When to release | PM + Architect |
| Technical debt allocation | Architect (20% capacity) |

---

## Icebox Management

Items in Icebox are:
- Not prioritized
- Not estimated
- Not committed

**Review quarterly:**
- Promote to Refining if relevant
- Archive if obsolete
- Delete if duplicate

---

## References

- [Product Backlog (Main)](README.md)
- [Epic Template](epic-template.md)
- [EPIC-00: Project Foundation](epic-00-project-foundation.md)
- [EPIC-01: Academic Domain](epic-01-academic-domain.md)
- [Technical Debt Register](debt-log.md)
- [Sprint Workflow](../ai/10-sprint-workflow.md)
- [Task Workflow](../ai/09-task-workflow.md)

---

*Backlog is a living document. Refine continuously. Prioritize ruthlessly.*