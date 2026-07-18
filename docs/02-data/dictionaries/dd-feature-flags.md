# Data Dictionary: feature_flags

## Overview
- **Table**: `feature_flags`
- **Domain**: Settings
- **Purpose**: Feature toggles with rollout percentages and targeting
- **Owner**: Platform Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| name | VARCHAR(100) | NOT NULL, UNIQUE | Flag key | "yks_simulation_v2" |
| description | VARCHAR(500) | NULLABLE | Human description | "New YKS simulation engine" |
| enabled | BOOLEAN | NOT NULL, DEFAULT FALSE | Global on/off | true |
| rollout_percentage | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 | % of users (0-100) | 25 |
| targeting_rules | JSON | NULLABLE | User targeting | {"roles": ["teacher"], "grades": [11,12]} |
| variants | JSON | NULLABLE | A/B variants | {"control": 50, "new_ui": 50} |
| created_by_id | BIGINT UNSIGNED | FK → users.id, SET NULL | Creator | 1 |
| created_at | TIMESTAMP | NOT NULL | Creation time | 2024-01-15 10:00:00 |
| updated_at | TIMESTAMP | NOT NULL | Last update | 2024-01-20 14:30:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| feature_flags_name_unique | name | UNIQUE | Flag lookup |

## Targeting Rules Structure

```json
{
  "roles": ["teacher", "admin"],
  "grades": [11, 12],
  "users": [1, 5, 10],
  "excluded_users": [2],
  "schools": [101, 102],
  "date_range": {
    "start": "2024-01-01",
    "end": "2024-06-30"
  }
}
```

## Variants Structure (A/B Testing)

```json
{
  "control": 50,
  "new_algorithm": 50
}
// OR
{
  "variant_a": 33,
  "variant_b": 33,
  "variant_c": 34
}
```

## Evaluation Logic

```
isEnabledForUser(user):
  1. If !enabled → return false
  2. If targeting_rules match user → return true
  3. If rollout_percentage == 100 → return true
  4. If rollout_percentage == 0 → return false
  5. Hash(user.id + name) % 100 < rollout_percentage → return true
  5. Return false

getVariant(user):
  1. If !variants → return null
  2. If targeting_rules match → use targeted variant
  3. Hash(user.id + name + variant_key) % 100 → assign variant
  3. Store assignment for consistency
```

## Standard Flags

| Name | Description | Default | Target |
|------|-------------|---------|--------|
| yks_simulation_v2 | New YKS simulation engine | false | 11th, 12th grade |
| adaptive_recommendations | AI-powered recommendations | false | All students |
| new_dashboard_ui | Redesigned student dashboard | false | 50% rollout |
| gamification_badges | Achievement badges | false | Opt-in schools |
| parent_portal | Parent progress view | false | Opt-in teachers |
| offline_pwa | PWA offline mode | true | All users |
| new_question_types | Matching, ordering questions | false | Content editors |
| ai_explanation | AI-generated explanations | false | Beta users |

## Business Rules

1. **Name** unique, kebab-case (e.g., `new-dashboard-ui`)
2. **Enabled** = master switch (off = no one sees it)
3. **Rollout** = percentage of eligible users
4. **Targeting** = specific user segments
5. **Variants** = A/B test buckets
6. **Consistent assignment** = same user gets same variant
6. **Audit trail** = created_by tracks who created
7. **Safe defaults** = all false initially

## Sample Data

```sql
INSERT INTO feature_flags (name, description, enabled, rollout_percentage, targeting_rules, variants, created_by_id) VALUES
('yks_simulation_v2', 'New YKS simulation with official 2024 format', true, 25, '{"grades": [11, 12], "roles": ["student"]}', '{"control": 50, "new_simulation": 50}', 1),
('adaptive_recommendations', 'AI-powered topic recommendations', false, 0, '{"roles": ["student"]}', null, 1),
('new_dashboard_ui', 'Redesigned student dashboard with progress rings', true, 50, '{}', '{"control": 50, "new_ui": 50}', 1),
('offline_pwa', 'Progressive Web App offline support', true, 100, '{}', null, 1);
```

## Migration Checklist

- [ ] Unique index on `name`
- [ ] FK on `created_by_id` with SET NULL
- [ ] `targeting_rules` and `variants` as JSON
- [ ] `rollout_percentage` 0-100 constraint
- [ ] Timestamps included
- [ ] No soft deletes
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*