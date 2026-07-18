# Definition of Success

## Purpose

Defines what "done" and "successful" look like for KimyaÖğren at every level — from individual tasks to the 5-year vision. This is the scorecard for the project.

---

## Hierarchy of Success

```
VISION SUCCESS (5 Years)
    │
    ├─► RELEASE SUCCESS (Quarterly)
    │       │
    │       ├─► SPRINT SUCCESS (Bi-weekly)
    │       │       │
    │       │       ├─► TASK SUCCESS (Daily)
    │       │       └─► QUALITY SUCCESS (Continuous)
    │       │
    │       └─► METRICS SUCCESS (Monthly)
    │
    └─► ORGANIZATIONAL SUCCESS (Ongoing)
```

---

## Task Success (Definition of Done)

A task is **successful** when **ALL** criteria are met:

| Category | Criteria | Verification |
|----------|----------|--------------|
| **Functional** | All acceptance criteria pass | Manual + automated test |
| **Code Quality** | `pint --test` passes | CI |
| **Static Analysis** | `phpstan analyse` passes (Level 5) | CI |
| **Tests** | All tests pass; ≥80% coverage on new code | CI |
| **Documentation** | CHANGELOG, Data Dictionary, API docs updated | PR review |
| **Security** | No critical/high vulnerabilities | Composer audit |
| **Performance** | No N+1; indexes on filtered columns | Code review |
| **Accessibility** | WCAG 2.1 AA (if UI) | axe-core + manual |
| **Deployment** | Migrates cleanly; rolls back cleanly | Staging deploy |
| **Review** | Approved by PM or Architect | GitHub approval |

**No partial credit. A task is either Done or Not Done.**

---

## Sprint Success

A sprint is **successful** when:

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Velocity** | ≥90% of committed points completed | Sprint review |
| **Commitment Reliability** | ≥85% | Completed / Committed |
| **Defect Escape Rate** | <5% | Bugs found in sprint / Stories |
| **Quality Gates** | 100% pass | CI dashboard |
| **Carryover** | ≤1 story (and <3 pts) | Sprint board |
| **Demo** | All completed stories demonstrated | Sprint review |
| **Retrospective** | 1-2 actionable improvements | Retro notes |

**Sprint failure** = Any metric missed → Root cause in retrospective.

---

## Release Success (v1.0, v2.0, etc.)

A release is **successful** when:

| Dimension | v1.0 (Foundation) | v2.0 (Practice) | v3.0 (Assessment) | v4.0 (Launch) |
|-----------|-------------------|-----------------|-------------------|---------------|
| **Scope** | All Must-Have stories Done | All Must-Have + Should-Have | All Must-Have + Should-Have | All Must-Have + Should-Have |
| **Quality** | Zero Critical/High bugs | Zero Critical/High bugs | Zero Critical/High bugs | Zero Critical/High bugs |
| **Performance** | API p95 < 300ms | API p95 < 200ms | API p95 < 200ms | API p95 < 200ms |
| **Accessibility** | WCAG 2.1 AA (admin) | WCAG 2.1 AA (all) | WCAG 2.1 AA (all) | WCAG 2.1 AA (all) |
| **Security** | Audit passed | Audit passed | Pen-test passed | Pen-test passed |
| **Documentation** | Complete for release scope | Complete | Complete | Complete + User guides |
| **Staging Validation** | PM sign-off | PM sign-off | PM + Teacher pilot sign-off | PM + Beta user sign-off |
| **Rollback** | Tested | Tested | Tested | Tested + Runbook |

---

## Product Success Metrics (North Star)

### Leading Indicators (Predictive)

| Metric | Target | Frequency |
|--------|--------|-----------|
| **Daily Active Students (DAS)** | 1,000 by M6; 10,000 by M12 | Daily |
| **Question Completion Rate** | >70% | Weekly |
| **Session Duration (median)** | >15 min | Weekly |
| **Return Rate (D7)** | >40% | Weekly |
| **Spaced Repetition Adherence** | >60% of due reviews completed | Weekly |
| **Teacher Active Classes** | >50 by M6; >200 by M12 | Monthly |

### Lagging Indicators (Outcome)

| Metric | Target | Frequency |
|--------|--------|-----------|
| **Monthly Active Students (MAS)** | 50,000 by M24 | Monthly |
| **YKS Score Improvement** | Measurable vs control | Per exam cycle |
| **MEB Curriculum Coverage** | 100% | Per curriculum version |
| **Teacher NPS** | >50 | Quarterly |
| **Student NPS** | >40 | Quarterly |
| **Revenue (if applicable)** | Break-even by M18 | Monthly |
| **Churn (Student)** | <5% monthly | Monthly |

---

## Quality Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Test Coverage (Overall)** | ≥70% | Pest coverage |
| **Test Coverage (New Code)** | ≥80% | CI diff coverage |
| **Static Analysis** | PHPStan Level 5, 0 errors | CI |
| **Code Style** | 0 Pint violations | CI |
| **Security Vulnerabilities** | 0 Critical/High | Composer audit / Snyk |
| **Accessibility** | 0 AA violations | axe-core |
| **Performance (API p95)** | <200ms | Telescope/Blackfire |
| **Performance (FCP)** | <1.5s | Lighthouse |
| **Uptime** | 99.9% | Uptime monitor |
| **Error Rate** | <0.1% | Sentry |

---

## Team Success

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Onboarding Time (New AI)** | <30 min to productive | Time to first merged PR |
| **Documentation Freshness** | Updated in same PR as code | Doc audit |
| **Technical Debt Ratio** | <20% of capacity | Sprint allocation |
| **Incident MTTR** | <1 hour (Critical) | Incident log |
| **Deployment Frequency** | Daily (to staging) | CI/CD logs |
| **Lead Time (Idea → Production)** | <2 weeks | GitHub Insights |

---

## Business Success (If Applicable)

| Metric | Year 1 | Year 2 | Year 3 |
|--------|--------|--------|--------|
| **Registered Students** | 10,000 | 50,000 | 150,000 |
| **Paid Schools/Teachers** | 0 | 50 | 300 |
| **Revenue** | $0 | $50K | $500K |
| **Content Items** | 5,000 questions | 15,000 | 40,000 |
| **Curriculum Versions Supported** | 1 (2024) | 2 | 3 |

---

## Failure Definitions

### Task Failure
- Any DoD criterion not met
- Merged without approval
- Deployed without staging validation

### Sprint Failure
- <85% commitment reliability
- >5% defect escape rate
- Critical bug in released code
- No retrospective held

### Release Failure
- Critical/High bug in production
- Performance regression >20%
- Accessibility regression
- Security incident
- Rollback required

### Project Failure
- <10% of North Star metrics achieved at 12 months
- Team velocity declining for 3+ sprints
- Technical debt >40% capacity
- Key personnel burnout/turnover

---

## Success Review Cadence

| Review | Frequency | Participants | Output |
|--------|-----------|--------------|--------|
| **Task Review** | Per PR | Reviewer + Author | Approved/Rejected |
| **Sprint Review** | Bi-weekly | PM, Architect, Developer | Accepted/Rejected + Metrics |
| **Release Review** | Per release | PM, Architect, Stakeholders | Go/No-Go |
| **Monthly Business Review** | Monthly | PM, Stakeholders | KPI Dashboard + Decisions |
| **Quarterly Strategy Review** | Quarterly | PM, Architect, Leadership | Roadmap Adjustment |
| **Annual Vision Review** | Yearly | All | Vision Validation/Pivot |

---

## Success Communication

| Audience | Channel | Frequency | Content |
|----------|---------|-----------|---------|
| **Team** | Slack #general | Per merge | "Task X merged ✓" |
| **Team** | Sprint Review | Bi-weekly | Demo + Metrics |
| **Stakeholders** | Email/Notion | Monthly | KPI Dashboard + Risks |
| **Leadership** | Quarterly Review | Quarterly | Strategic Progress + Decisions |

---

## Continuous Success Calibration

> **Success criteria evolve. Review quarterly.**

1. **Measure** against current targets
2. **Analyze** gaps and trends
3. **Adjust** targets up (if consistently exceeding) or down (if unrealistic)
4. **Document** changes in this file
5. **Communicate** to all stakeholders

---

*Success is not a destination. It's a discipline practiced daily.*