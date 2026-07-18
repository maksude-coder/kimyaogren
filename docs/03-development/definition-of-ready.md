# Definition of Ready

## Purpose

A task is **Ready** only when ALL criteria below are met. No work begins until DoR is satisfied.

---

## Task-Level Definition of Ready

### Requirements Clarity

- [ ] **Acceptance Criteria** written in Gherkin format (Given/When/Then)
- [ ] **Scope** clearly bounded (what's in, what's out)
- [ ] **Dependencies** identified and available
- [ ] **No ambiguity** - all questions answered by PM

### Technical Preparation

- [ ] **Data Dictionary** approved by Architect (if DB changes)
- [ ] **API Contract** defined (if new/modified endpoints)
- [ ] **UI Specification** approved by PM (if Filament changes)
- [ ] **Architecture Review** completed (if new patterns)

### Testability

- [ ] **Test scenarios** identified (happy path + edge cases)
- [ ] **Test data requirements** defined
- [ ] **Existing tests** verified as passing baseline

### Size & Complexity

- [ ] **Estimated ≤ 2 days** work (split if larger)
- [ ] **Single domain** (no cross-domain changes)
- [ ] **No refactoring** required (pure implementation)

### Resources

- [ ] **Assignee** confirmed available
- [ ] **Documentation** accessible
- [ ] **Environment** ready (staging, DB, services)

---

## Sprint-Level Definition of Ready

### Sprint Planning Prerequisites

- [ ] **Product Backlog** prioritized by PM
- [ ] **Top N items** meet Task DoR (N = team capacity)
- [ ] **Sprint Goal** defined (one sentence)
- [ ] **Capacity** calculated (accounting for meetings, leave)
- [ ] **No blocking dependencies** on external teams

### Team Alignment

- [ ] All team members understand Sprint Goal
- [ ] Tasks distributed (no over-allocation)
- [ ] Risks identified and mitigated

---

## Gherkin Acceptance Criteria Template

```gherkin
Feature: {Feature Name}

  Scenario: {Happy Path}
    Given {precondition}
    When {action}
    Then {expected outcome}
    And {additional outcome}

  Scenario: {Edge Case - Invalid Input}
    Given {precondition}
    When {invalid action}
    Then {validation error}
    And {no side effects}

  Scenario: {Edge Case - Unauthorized}
    Given {unauthorized user}
    When {action}
    Then {403 Forbidden}
    And {no data changed}
```

---

## Data Dictionary Approval Checklist

Before marking task Ready:

- [ ] Table name follows conventions
- [ ] All columns defined with type, constraints, purpose
- [ ] Primary key defined
- [ ] All foreign keys with cascade rules
- [ ] All indexes documented
- [ ] Unique constraints for business keys
- [ ] Enum definitions with values/labels
- [ ] Soft deletes decision documented
- [ ] Audit fields (created_by, updated_by) included
- [ ] Relationships mapped (BelongsTo, HasMany, etc.)
- [ ] Business rules listed
- [ ] Architect signed off
- [ ] PM confirmed alignment

---

## API Contract Approval Checklist

- [ ] Endpoint paths defined (RESTful)
- [ ] HTTP methods correct
- [ ] Request/response schemas (JSON)
- [ ] Query parameters documented
- [ ] Error responses defined
- [ ] Authentication/authorization specified
- [ ] Rate limits documented
- [ ] Versioning strategy noted

---

## UI Specification Approval Checklist

- [ ] Wireframes/mockups for all states
- [ ] Form fields with validation rules
- [ ] Table columns with sorting/filtering
- [ ] Actions available per row
- [ ] Bulk actions defined
- [ ] Empty states designed
- [ ] Loading states designed
- [ ] Error states designed
- [ ] Mobile responsiveness confirmed
- [ ] Accessibility notes (WCAG 2.1 AA)

---

## DoR Violation Protocol

If work starts without DoR:

1. **Stop** immediately
2. **Document** missing criteria
3. **Escalate** to PM
4. **Complete** missing items
5. **Re-verify** DoR
6. **Resume** work

---

*DoR is a contract. Both PM and Developer must sign off.*