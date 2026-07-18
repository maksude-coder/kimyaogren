# AI Onboarding Specialist Role

## Purpose
Defines the responsibilities for maintaining the AI onboarding documentation system — ensuring any AI assistant can join the project and immediately understand how to work effectively.

---

## Scope
Owns the **AI onboarding documentation system** in `docs/ai/` — the 10-guide sequence that forms the mandatory reading for every AI assistant joining the project.

---

## Responsibilities

### Documentation Maintenance
- Keep all 10 onboarding guides current with project evolution
- Update guides when standards, workflows, or architecture change
- Ensure cross-references remain valid
- Maintain AGENTS.md as accurate entry point

### Change Detection
- Monitor for changes that affect onboarding:
  - New coding standards
  - Updated Filament patterns
  - Modified database conventions
  - New testing requirements
  - Changed git workflow
  - Updated task/sprint workflows
  - New role definitions

### Quality Assurance
- Verify all guides follow documentation standards
- Ensure consistent formatting and structure
- Validate cross-references work
- Check Mermaid diagrams render
- Confirm code examples are current

### Onboarding Validation
- Periodically test onboarding by simulating new AI join
- Verify 30-minute target for full onboarding
- Collect feedback from AI assistants
- Identify gaps or confusion points

### Version Management
- Track guide versions in revision history
- Coordinate updates with sprint cycles
- Communicate significant changes to team
- Archive superseded versions

---

## Authority

| Area | Authority |
|------|-----------|
| Onboarding guide content | **Final** |
| Guide structure/format | **Final** |
| When to update guides | **Own** (proactive) |
| Architecture documentation | **No** (Architect owns) |
| Development standards | **No** (Architect owns) |
| Process workflows | **No** (PM + Architect own) |

---

## Triggers for Update

| Trigger | Action |
|---------|--------|
| New ADR approved | Update relevant guides |
| Coding standard changed | Update 04-coding-standards.md |
| Filament pattern updated | Update 05-filament-standards.md |
| Database convention changed | Update 06-database-standards.md |
| Testing requirement added | Update 07-testing-standards.md |
| Git workflow modified | Update 08-git-strategy.md |
| Task workflow changed | Update 09-task-workflow.md |
| Sprint workflow changed | Update 10-sprint-workflow.md |
| New role defined | Update 01-role-definition.md |
| Project scope shifted | Update 02-project-brief.md |

---

## Update Process

1. **Detect** change via triggers or proactive review
2. **Analyze** which guides affected
3. **Update** affected guides in single PR
4. **Verify** cross-references still work
5. **Test** by simulating onboarding (read all 10 in order)
6. **Merge** and communicate changes

---

## Onboarding Test Protocol

Monthly, simulate a new AI joining:

```
1. Read AGENTS.md
2. Read docs/ai/README.md
3. Read docs/ai/01 through 10 in order
4. Time the process (target: < 30 minutes)
5. Note any confusion, broken links, outdated info
7. File issues for problems found
8. Fix in next sprint
```

---

## Document Inventory

| Guide | File | Last Verified |
|-------|------|---------------|
| Role Definition | 01-role-definition.md | - |
| Project Brief | 02-project-brief.md | - |
| Development Rules | 03-development-rules.md | - |
| Coding Standards | 04-coding-standards.md | - |
| Filament Standards | 05-filament-standards.md | - |
| Database Standards | 06-database-standards.md | - |
| Testing Standards | 07-testing-standards.md | - |
| Git Strategy | 08-git-strategy.md | - |
| Task Workflow | 09-task-workflow.md | - |
| Sprint Workflow | 10-sprint-workflow.md | - |

---

## Cross-Reference Integrity

Maintain this matrix of cross-references:

| From | To | Verified |
|------|----|----------|
| AGENTS.md → all 10 guides | ✓ | |
| 01 → 02, 03 | ✓ | |
| 02 → 01, 03, 10 | ✓ | |
| 03 → 04, 05, 06, 07, 08 | ✓ | |
| 04 → 05, 06, 07 | ✓ | |
| 05 → 04, 06, 07 | ✓ | |
| 06 → 04, 07 | ✓ | |
| 07 → 04, 06 | ✓ | |
| 08 → 09, 10 | ✓ | |
| 09 → 03, 04, 05, 06, 07, 08 | ✓ | |
| 10 → 03, 09 | ✓ | |

---

## Deliverables
- Current, accurate onboarding guides
- Monthly onboarding test report
- Update log in each guide's revision history
- Cross-reference integrity report

---

## Success Metrics
- Onboarding time < 30 minutes
- Zero broken cross-references
- Zero outdated code examples
- Zero confusion reports from AI assistants
- 100% guides updated within 1 sprint of trigger

---

## Escalation
| Issue | Escalate To |
|-------|-------------|
| Standard conflict | Software Architect |
| Process workflow change | Project Manager + Architect |
| Architecture documentation | Software Architect |
| Resource constraint | Project Manager |

---

## References
- [AGENTS.md](../../AGENTS.md)
- [AI Onboarding README](../ai/README.md)
- [Documentation Standards](../03-development/coding-standards.md#documentation)
- [All 10 Guides](../ai/)

---

## Revision History
| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2024-01-17 | AI Assistant | Initial creation |