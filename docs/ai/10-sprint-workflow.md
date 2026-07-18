# Sprint Workflow

This document defines the complete sprint lifecycle for the KimyaÖğren project.

---

## Sprint Structure

| Sprint | Duration | Goal |
|--------|----------|------|
| Standard | 2 weeks (10 working days) | Deliver production-ready features |

### Sprint Calendar

```
Week 1:           Week 2:
┌─────────────┐   ┌─────────────┐
│ Mon: Planning │   │ Mon: Dev     │
│ Tue: Dev     │   │ Tue: Dev     │
│ Wed: Dev     │   │ Wed: Dev     │
│ Thu: Dev     │   │ Thu: Test    │
│ Fri: Review  │   │ Fri: Review  │
└─────────────┘   └─────────────┘
                        │
                   ┌────┴────┐
                   │ Weekend │
                   │ Buffer  │
                   └─────────┘
```

---

## Sprint Phases

### Phase 1: Sprint Planning (Day 1 - Monday, 2 hours)

#### Participants
- Project Manager (facilitator)
- Architect
- AI Assistant (you)

#### Inputs
- Product backlog (prioritized)
- Team capacity (1 AI = 100% for this project)
- Previous sprint velocity (after Sprint 1)

#### Outputs
- **Sprint Goal** (1 sentence)
- **Sprint Backlog** (committed tasks)
- **Task Assignments**

#### Process

```
1. PM presents top priority items from Product Backlog
2. Architect reviews technical feasibility
3. Team estimates each task (Story Points: 1, 2, 3, 5, 8)
4. Team selects tasks up to capacity
5. PM defines Sprint Goal
6. All commit to Sprint Backlog
```

#### Definition of Ready (Task must have)

- [ ] Clear acceptance criteria
- [ ] Data Dictionary approved (if DB changes)
- [ ] Architecture reviewed (if new patterns)
- [ ] Dependencies identified
- [ ] Estimated by team

---

### Phase 2: Development (Days 1-8)

#### Daily Routine

```
Each Day:
1. Review assigned task
2. Execute Task Workflow (doc 09)
   - Analyze → Document → Implement → Test → Review
3. Commit frequently (conventional commits)
4. Push to feature branch
5. Update task status
```

#### Task Execution Rules

| Rule | Enforcement |
|------|-------------|
| One task at a time | AI focuses on single task |
| Complete before next | No partial tasks carried over |
| Data Dictionary first | No migrations without approval |
| Tests with code | No implementation without tests |
| Self-review before PR | Checklist in doc 09 |

#### Mid-Sprint Check (Day 5 - Wednesday)

- PM reviews progress
- Identify blockers
- Re-prioritize if needed
- Scope adjustment (only remove, never add)

---

### Phase 3: Testing & Integration (Day 9 - Thursday)

#### Activities

```bash
# 1. Full test suite
./vendor/bin/pest --parallel

# 2. Code style
./vendor/bin/pint --test

# 3. Static analysis (if configured)
./vendor/bin/phpstan analyse

# 4. Integration testing
# Manual testing of complete flows

# 5. Performance check
# Load test critical paths
```

#### Release Candidate

```bash
# Create RC branch
git checkout develop
git pull origin develop
git checkout -b rc/v1.x.x

# Final verification
./vendor/bin/pest --parallel --coverage
```

---

### Phase 4: Sprint Review (Day 10 - Friday, 2 hours)

#### Demo Session

| Item | Duration |
|------|----------|
| Sprint Goal Review | 5 min |
| Feature Demos | 30 min |
| Metrics Review | 10 min |
| Stakeholder Feedback | 15 min |
| Next Sprint Preview | 10 min |

#### Sprint Metrics

| Metric | Target |
|--------|--------|
| Velocity (Story Points) | Track trend |
| Commitment Reliability | > 85% |
| Defect Rate | < 5% |
| Test Coverage | > 80% new code |
| Code Style Violations | 0 |

#### Review Output

- **Accepted Features** → Ready for release
- **Incomplete Tasks** → Return to backlog (re-estimate)
- **Feedback Items** → New backlog items

---

### Phase 5: Sprint Retrospective (Day 10 - Friday, 1 hour)

#### Format

```
What Went Well:
- [Item 1]
- [Item 2]

What Didn't Go Well:
- [Item 1]
- [Item 2]

Action Items:
- [Action 1] - Owner: X - Due: Next Sprint
- [Action 2] - Owner: Y - Due: Next Sprint
```

#### Continuous Improvement

- Action items become process improvements
- Track completion in next retrospective
- Update workflow docs if needed

---

## Task Lifecycle Within Sprint

```
BACKLOG → READY → IN PROGRESS → IN REVIEW → DONE
                  │
                  ▼
             BLOCKED
```

### Status Definitions

| Status | Meaning |
|--------|---------|
| **Backlog** | Prioritized, not ready |
| **Ready** | Meets Definition of Ready |
| **In Progress** | Actively being worked on |
| **In Review** | PR open, awaiting review |
| **Blocked** | Waiting on dependency |
| **Done** | Merged to develop, tested |

### WIP Limits

| Role | Max Concurrent Tasks |
|------|---------------------|
| AI Assistant | 1 |

---

## Definition of Done

A task is **Done** when ALL apply:

- [ ] Code implemented per spec
- [ ] All tests pass (unit + feature)
- [ ] Code style passes (Pint)
- [ ] Static analysis passes (if configured)
- [ ] Self-review completed
- [ ] PR approved by PM/Architect
- [ ] Merged to `develop`
- [ ] Documentation updated
- [ ] No console errors in browser
- [ ] Mobile responsive (if UI)

---

## Sprint Artifacts

### Sprint Planning Output

```markdown
# Sprint {N} Plan

## Sprint Goal
Deliver complete Curriculum domain: Units, Topics, Learning Objectives

## Committed Tasks
| ID | Task | Points | Assignee |
|----|------|--------|----------|
| 021 | Create Unit model/migration | 3 | AI |
| 022 | Create Topic model/migration | 3 | AI |
| 023 | Create LearningObjective model | 2 | AI |
| 024 | Build Filament UnitResource | 5 | AI |
| 025 | Build Filament TopicResource | 5 | AI |
| 026 | Build Filament ObjectiveResource | 3 | AI |

**Total: 21 Story Points**

## Risks
- Data Dictionary approval timing
- Filament relation manager complexity

## Dependencies
- None
```

### Sprint Review Output

```markdown
# Sprint {N} Review

## Sprint Goal: ✅ ACHIEVED

## Completed Tasks (21/21 pts)
| Task | Status | Notes |
|------|--------|-------|
| 021 | Done | |
| 022 | Done | Minor scope adjustment |
| 023 | Done | |
| 024 | Done | |
| 025 | Done | |
| 026 | Done | |

## Metrics
- Velocity: 21 pts
- Commitment: 100%
- Defects Found: 0
- Coverage: 87% (new code)

## Stakeholder Feedback
- "Curriculum structure matches MEB perfectly"
- "Admin panel intuitive for content editors"

## Carryover
- None

## Next Sprint Focus
- Content domain (Theory Articles, Media)
```

---

## Release Management

### Release Criteria

| Criterion | Required |
|-----------|----------|
| All sprint goals met | ✓ |
| Zero critical bugs | ✓ |
| Performance benchmarks met | ✓ |
| Security scan clean | ✓ |
| Documentation complete | ✓ |
| Stakeholder sign-off | ✓ |

### Release Process

```bash
# 1. Create release branch
git checkout -b release/v1.0.0 develop

# 2. Final checks
./vendor/bin/pest --parallel
./vendor/bin/pint --test

# 3. Version bump
# composer.json version
# CHANGELOG.md

# 4. Merge to main
git checkout main
git merge --no-ff release/v1.0.0
git tag -a v1.0.0 -m "Release v1.0.0"

# 5. Deploy (CI/CD)
# GitHub Actions handles deployment

# 6. Merge back to develop
git checkout develop
git merge --no-ff release/v1.0.0
```

---

## Scaling Guidelines

### When to Add Tasks Mid-Sprint

**Never.** Scope is fixed during sprint.

**Exceptions** (require PM approval):
- Critical production bug
- Security vulnerability
- Blocking dependency resolved early

### When to Extend Sprint

**Rarely.** Fixed timebox is core principle.

**If needed** (PM decision):
- Max 2 days extension
- Communicate to stakeholders
- Retrospective must address root cause

---

## Tools & Automation

### GitHub Project Board

```
Columns: Backlog → Ready → In Progress → In Review → Done
```

### GitHub Actions

```yaml
# .github/workflows/ci.yml
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.3', extensions: mbstring, pdo_mysql }
      - run: composer install --prefer-dist --no-progress
      - run: ./vendor/bin/pest --parallel
      - run: ./vendor/bin/pint --test
```

### Slack/Discord Notifications

- PR opened → #dev-prs
- PR merged → #dev-merges
- Deploy → #dev-deploys
- Test failure → #dev-alerts

---

*This document is part of the AI onboarding system. See [README.md](README.md) for reading order.*