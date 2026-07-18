# Project Roadmap

## Purpose
High-level product roadmap showing planned epics, milestones, and release targets. Updated quarterly.

---

## Release Phases

### Phase 1: Foundation (Sprints 1-6) — Months 1-3
**Goal**: Core platform ready for content team

| Sprint | Focus | Deliverable |
|--------|-------|-------------|
| 0.1-0.2 | Project setup, docs, CI/CD | Repo, docs, pipeline |
| 1 | User & Auth domain | Registration, login, roles |
| 2 | Curriculum domain | Units, topics, objectives (MEB) |
| 3 | Content domain (Theory) | Articles, media, versions |
| 4 | Content domain (Media) | Images, videos, simulations |
| 5 | Admin panel complete | Filament resources for all domains |
| 6 | **Release v1.0** | Content team onboarding |

**Exit Criteria**: Content team can create full curriculum without dev help

---

### Phase 2: Practice Engine (Sprints 7-12) — Months 4-6
**Goal**: Students can practice with spaced repetition

| Sprint | Focus | Deliverable |
|--------|-------|-------------|
| 7 | Question domain | MCQ, TF, SA, Essay types |
| 8 | Question bank | Import, versioning, tagging |
| 9 | Practice modes | Topic, Mixed, Timed, Exam |
| 10 | Spaced repetition | SM-2 algorithm, scheduling |
| 11 | Mastery tracking | Per-topic mastery, weak areas |
| 12 | **Release v2.0** | Student practice MVP |

**Exit Criteria**: Student completes 100+ questions with mastery tracking

---

### Phase 3: Assessment & Analytics (Sprints 13-18) — Months 7-9
**Goal**: YKS simulations and actionable insights

| Sprint | Focus | Deliverable |
|--------|-------|-------------|
| 13 | Quiz/Exam engine | Timed, sectioned, randomized |
| 14 | YKS simulation | Full format, scoring, ranking |
| 15 | Student analytics | Dashboards, trends, predictions |
| 16 | Teacher analytics | Class reports, interventions |
| 17 | Content analytics | Question quality, gap detection |
| 18 | **Release v3.0** | Assessment platform |

**Exit Criteria**: School pilot with 5+ teachers, 200+ students

---

### Phase 4: Polish & Scale (Sprints 19-24) — Months 10-12
**Goal**: Production hardening and mobile access

| Sprint | Focus | Deliverable |
|--------|-------|-------------|
| 19 | PWA & Offline | Service worker, background sync |
| 20 | Performance | Query optimization, caching |
| 21 | Accessibility | WCAG 2.1 AA, screen readers |
| 22 | Security audit | Penetration test, GDPR/KVKK |
| 23 | Load testing | 10k concurrent users |
| 24 | **Release v4.0** | Production launch |

**Exit Criteria**: Public launch, 10k+ registered users

---

## Milestone Timeline

```
Q1 2025     Q2 2025     Q3 2025     Q4 2025
├──────────┼──────────┼──────────┼──────────┤
Foundation  Practice   Assessment  Polish
(S1-6)      (S7-12)    (S13-18)    (S19-24)
     │           │           │           │
     ▼           ▼           ▼           ▼
   v1.0        v2.0        v3.0        v4.0
```

---

## Dependencies & Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| Content creation bottleneck | Delays practice/assessment | Parallel content team hiring |
| Spaced repetition algorithm complexity | Sprint 10 overrun | Spike in Sprint 9, fallback to simpler algo |
| YKS format changes | Rework in Sprint 14 | Monitor OSYM announcements, flexible schema |
| Filament 5 breaking changes | Upgrade effort | Pin versions, test early |

---

## Resource Allocation

| Role | Phase 1 | Phase 2 | Phase 3 | Phase 4 |
|------|---------|---------|---------|---------|
| AI Developer | 100% | 100% | 100% | 100% |
| Project Manager | 50% | 50% | 50% | 50% |
| Architect | 20% | 10% | 10% | 10% |
| Content Lead | 0% | 50% | 100% | 50% |
| QA/Testing | 0% | 20% | 50% | 100% |

---

*Reviewed quarterly. Next review: End of Sprint 6.*