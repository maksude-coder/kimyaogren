# Ownership Model

## Purpose

Defines the aggregate root, ownership tree, and deletion semantics for the academic domain, as decided in `docs/05-backlog/debt-log.md` `DEBT-003` (Grade scoping) and `DEBT-004` (cascade strategy). This document is the single canonical reference — `dd-*.md` dictionaries and `docs/02-data/relationships.md` must agree with it.

---

## Aggregate Root

**`Program`** is the aggregate root of the academic domain. It is the consistency boundary for all curriculum content — every downstream entity exists only in the context of the Program it (transitively) belongs to. This was established in Task 1.1 (`docs/01-architecture/domain-program.md` § 7, "Program as Aggregate Root") and reaffirmed by `DEBT-003`'s resolution (Grade is Program-scoped, not global).

## Ownership Tree

```
Program
└── Grade        (program_id)
    └── Unit         (grade_id)
        └── Topic        (unit_id)
            └── Learning Objective  (topic_id)
```

Each level is a strict parent-child ownership relationship (`HasMany` down, `BelongsTo` up). The tree is a DAG with no circular references and no shared ownership — every child has exactly one owning parent.

**Not part of this tree:** entities that *reference into* the hierarchy without being owned by it — e.g. `Question`, `TheoryArticle` (reference `Topic`), `Class` (references `Grade`/`Program`). These use `RESTRICT` or `SET NULL`, a deliberate, separate design choice (preserve assessment/content history), not an exception to this model.

## Ownership Rules

1. An owned entity must not outlive its owner — deleting a Program permanently deletes its entire curriculum subtree.
2. A relationship has exactly **one** cascade rule. The parent's `HasMany` and the child's `BelongsTo` describe the same physical foreign key and must always agree. Disagreement between documents is a documentation defect, not a design option (see `DEBT-004`).
3. Referencing-in relationships (outside the tree) are never cascade — they use `RESTRICT` (block deletion, preserve history) or `SET NULL` (detach, preserve the referencing record), decided per-entity by the domain that owns them.

## Cascade Strategy

Every foreign key within the ownership tree (`grades.program_id`, `units.grade_id`, `topics.unit_id`, `learning_objectives.topic_id`) is declared `->cascadeOnDelete()` in its migration. This is uniform across all four levels — no mixed strategies within the tree.

## Soft Delete Strategy

Every entity in the tree uses Eloquent `SoftDeletes`. A soft delete is an `UPDATE ... SET deleted_at = ...` statement — it does **not** touch child rows, does **not** trigger the database's `ON DELETE CASCADE`, and does not require any manual cleanup. Routine deletion (the default Filament `DeleteAction`) is always a soft delete. Children remain fully intact and queryable when their parent is soft-deleted.

## Force Delete Strategy

Only `forceDelete()` issues a real SQL `DELETE`, which is what triggers the database's `ON DELETE CASCADE` and permanently removes the entire owned subtree beneath the deleted row. This is:

- **Admin-only**: gated by each entity's Policy `forceDelete()` method (currently `UserRole::Admin` only — see `ProgramPolicy`, `GradePolicy`).
- **Explicit**: only reachable via Filament's `ForceDeleteAction`, never the default delete button.
- **Empirically verified**: confirmed for `Program → Grade` in the Sprint 1.2 review (`docs/04-sprints/sprint-02-review.md`) — soft-delete leaves children untouched; `forceDelete()` cascades correctly.

---

*Source of truth for ownership/cascade decisions. Update this document, not just the dictionaries, whenever the ownership tree changes (e.g. when `Unit`, `Topic`, `Learning Objective` are implemented).*
