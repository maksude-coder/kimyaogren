# Quality Policy

## Purpose

Defines quality standards, metrics, and processes for KimyaÖğren. Quality is not a phase — it's embedded in every activity.

---

## Quality Objectives

| Objective | Target | Measurement |
|-----------|--------|-------------|
| **Code Quality** | Zero critical/high static analysis issues | PHPStan Level 5+ |
| **Test Coverage** | ≥80% on new code; ≥70% overall | PHPUnit coverage report |
| **Defect Rate** | <5% of stories have escaped defects | Sprint retrospective data |
| **Performance** | API p95 < 200ms; UI FCP < 1.5s | Laravel Telescope / Lighthouse |
| **Accessibility** | WCAG 2.1 AA compliant | axe-core + manual audit |
| **Security** | Zero critical vulnerabilities | Dependency audit + SAST |
| **Documentation** | 100% of public APIs documented | OpenAPI spec + PHPDoc |
| **Deployability** | Zero-downtime deployments | CI/CD pipeline success |

---

## Quality Gates

### 1. Pre-Commit (Local)
```bash
./vendor/bin/pint --test          # Code style
./vendor/bin/phpstan analyse      # Static analysis
php artisan test                  # Tests (PHPUnit)
```

### 2. Pull Request (CI)
| Gate | Tool | Threshold |
|------|------|-----------|
| Code Style | Laravel Pint | 0 violations |
| Static Analysis | PHPStan | Level 5, 0 errors |
| Tests | PHPUnit | 100% pass |
| Coverage | PHPUnit + Xdebug | ≥80% new code |
| Security Audit | Composer Audit | 0 critical |
| Dependency Freshness | Dependabot | <30 days behind |

### 3. Merge to Develop
- All CI gates passing
- At least 1 approval (PM or Architect)
- No unresolved review comments
- Branch rebased on develop

### 4. Release Candidate
- Full test suite on staging
- Manual regression testing
- Performance benchmark comparison
- Security scan (OWASP ZAP)
- Accessibility audit (axe-core)
- PM sign-off

### 5. Production Release
- Zero critical/high bugs open
- Rollback plan documented
- Monitoring alerts configured
- On-call rotation confirmed

---

## Definition of Quality by Role

### Developer (AI Assistant)
- Follows all coding standards
- Writes tests with implementation
- Self-reviews before PR
- Updates documentation
- No TODO/FIXME in merged code

### Architect
- Reviews Data Dictionaries
- Approves new patterns/abstractions
- Validates cross-domain changes
- Owns technical debt register
- Conducts architecture reviews

### Project Manager
- Defines clear acceptance criteria
- Prioritizes defects appropriately
- Tracks quality metrics
- Owns release quality decision
- Communicates quality status to stakeholders

---

## Quality Metrics Dashboard

### Sprint-Level (Tracked Every Sprint)

| Metric | Target | Trend |
|--------|--------|-------|
| Velocity | Stable/Increasing | ↗ |
| Commitment Reliability | >85% | → |
| Defect Escape Rate | <5% | ↘ |
| Test Coverage (new) | >80% | → |
| Code Review Turnaround | <4 hours | ↘ |
| PR Cycle Time | <1 day | ↘ |

### Release-Level (Tracked Per Release)

| Metric | Target |
|--------|--------|
| Critical Bugs | 0 |
| High Bugs | 0 |
| Medium Bugs | <3 |
| Performance Regression | 0% |
| Accessibility Violations | 0 (AA) |
| Security Vulnerabilities | 0 (Critical/High) |

### System-Level (Continuous)

| Metric | Target | Tool |
|--------|--------|------|
| Uptime | 99.9% | Uptime monitor |
| API p95 Latency | <200ms | Telescope/Blackfire |
| Error Rate | <0.1% | Sentry |
| Queue Lag | <30s | Horizon |
| DB Slow Queries | 0 | Slow query log |

---

## Defect Classification

| Severity | Definition | SLA |
|----------|------------|-----|
| **Critical** | Data loss, security breach, complete outage | Fix immediately / Hotfix |
| **High** | Major feature broken, no workaround | Fix in current sprint |
| **Medium** | Feature partially broken, workaround exists | Next sprint |
| **Low** | Minor UI issue, cosmetic | Backlog (tech debt) |
| **Cosmetic** | Typo, alignment, non-functional | Next release |

---

## Quality Processes

### Code Review
- **Scope**: Logic, standards, tests, security, performance
- **Size**: <400 lines per PR (split if larger)
- **Turnaround**: <4 hours during work hours
- **Approval**: 1 required (PM or Architect)

### Testing Strategy
| Layer | Scope | Tools | Coverage Target |
|-------|-------|-------|-----------------|
| **Feature** | HTTP endpoints, Filament resources, user flows | PHPUnit + Laravel | 80% |
| **Unit** | Actions, Services, Enums, Rules, Calculators | PHPUnit | 90% |
| **Contract** | API contracts, Data Dictionary compliance | PHPUnit + custom | 100% |
| **Browser** | Critical user journeys (future) | Dusk | Happy paths |

### Static Analysis
- **PHPStan Level 5** minimum
- **Larastan** for Laravel-specific rules
- **Custom rules**: No `dd()`, no `$guarded = []`, no Facades in Models

### Dependency Management
- **Security audit**: Every PR (`composer audit`)
- **Updates**: Weekly review, monthly batch update
- **Pinning**: Exact versions in `composer.lock`
- **License check**: MIT/BSD/Apache only

---

## Continuous Improvement

### Retrospective Actions
- Quality-related actions prioritized
- Owner assigned, due date set
- Tracked in GitHub Projects
- Reviewed next retrospective

### Technical Debt Budget
- **20% of sprint capacity** allocated
- Tracked in `DEBT-` labeled issues
- Prioritized by Architect
- Visible in sprint planning

### Quality Reviews
| Review | Frequency | Participants |
|--------|-----------|--------------|
| Sprint Retrospective | Every sprint | PM, Architect, Developer |
| Architecture Review | New domain / major change | Architect, PM |
| Security Review | Quarterly + pre-release | PM, Architect |
| Performance Review | Monthly | Developer, Architect |
| Accessibility Audit | Pre-release + quarterly | Developer |

---

## Non-Negotiable Standards

| Standard | Enforcement |
|----------|-------------|
| No `dd()`/`dump()`/`ray()` in committed code | CI grep check |
| No `$guarded = []` | PHPStan rule |
| No Facades in Models | PHPStan rule |
| No direct `$_GET`/`$_POST` | PHPStan rule |
| All public methods have PHPDoc | CI check |
| All enums have `label()`, `options()`, `color()` | Unit test |
| All migrations rollback cleanly | `migrate:refresh --seed` in CI |
| All Filament Resources have tests | Feature test required |

---

## Escalation Path

```
Developer detects quality issue
       │
       ▼
Can fix in current task? ──Yes──► Fix immediately
       │
      No
       │
       ▼
Create DEBT- issue, assign to Architect
       │
       ▼
Architect prioritizes in tech debt budget
       │
       ▼
If Critical/High ──► Hotfix / Sprint interruption
```

---

*Quality is not a checklist item. It is the way we work.*