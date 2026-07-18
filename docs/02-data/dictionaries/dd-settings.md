# Data Dictionary: settings

## Overview
- **Table**: `settings` (spatie/laravel-settings)
- **Domain**: Settings
- **Purpose**: System-wide key-value configuration with type casting
- **Owner**: Platform Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| group | VARCHAR(255) | NOT NULL | Settings group | "practice" |
| key | VARCHAR(255) | NOT NULL | Setting key | "min_questions_per_session" |
| value | TEXT | NOT NULL | Serialized value | "10" |
| type | VARCHAR(50) | NOT NULL | PHP type | "integer" |
| description | TEXT | NULLABLE | Human description | "Minimum questions per practice session" |
| is_public | BOOLEAN | NOT NULL, DEFAULT FALSE | Visible in frontend config | false |
| created_at | TIMESTAMP | NULLABLE | Creation time | 2024-01-15 10:00:00 |
| updated_at | TIMESTAMP | NULLABLE | Last update | 2024-01-20 14:30:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| settings_group_key_unique | (group, key) | UNIQUE | Unique setting per group |

## Type Casting

| Type | PHP Cast | Example |
|------|----------|---------|
| string | string | "tr" |
| integer | int | 10 |
| float | float | 2.5 |
| boolean | bool | true |
| array | array | ["topic", "mixed"] |
| json | array | {"easy": 1.3} |
| object | stdClass | (object)["key" => "value"] |

## Standard Settings

### Practice Settings (group: "practice")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| min_questions_per_session | integer | 5 | Minimum questions to start session |
| max_questions_per_session | integer | 50 | Maximum questions per session |
| session_timeout_minutes | integer | 120 | Auto-submit after minutes |
| enable_skip_button | boolean | true | Allow skipping questions |
| show_explanation_immediately | boolean | true | Show explanation after answer |
| weak_area_threshold | integer | 60 | Mastery % below = weak area |
| spaced_repetition_enabled | boolean | true | Enable SM-2 scheduling |

### Spaced Repetition (group: "spaced_repetition")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| initial_ease_factor | float | 2.50 | SM-2 starting ease |
| min_ease_factor | float | 1.30 | Minimum ease factor |
| easy_bonus | float | 1.30 | Interval multiplier for quality 5 |
| hard_penalty | float | 0.80 | Interval multiplier for quality 3 |
| lapse_reset_interval | integer | 1 | Days after failed review |
| max_interval_days | integer | 365 | Maximum interval |
| new_cards_per_day | integer | 20 | Max new cards daily |

### YKS (group: "yks")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| simulation_enabled | boolean | true | Enable full YKS simulation |
| tytt_duration_minutes | integer | 135 | TYT total duration |
| ayt_duration_minutes | integer | 180 | AYT total duration |
| questions_per_subject | array | {"chemistry": 20, "physics": 14, "biology": 13} | TYT distribution |
| ayt_questions | array | {"chemistry": 40} | AYT chemistry count |

### Content (group: "content")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| enable_rich_editor | boolean | true | TipTap editor |
| max_image_upload_mb | integer | 5 | Image upload limit |
| auto_generate_slug | boolean | true | Auto-slug from title |
| seo_default_image | string | "/images/og-default.png" | Default OG image |

### Platform (group: "platform")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| maintenance_mode | boolean | false | Global maintenance |
| registration_enabled | boolean | true | Allow new registrations |
| default_locale | string | "tr" | Default language |
| supported_locales | array | ["tr", "en"] | Available languages |
| session_lifetime_minutes | integer | 120 | Session timeout |
| csrf_exempt_routes | array | [] | API routes exempt from CSRF |

### Security (group: "security")

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| password_min_length | integer | 8 | Minimum password length |
| password_require_mixed_case | boolean | true | Require upper+lower |
| password_require_numbers | boolean | true | Require numbers |
| password_require_symbols | boolean | false | Require special chars |
| login_throttle_attempts | integer | 5 | Max failed logins |
| login_throttle_decay_minutes | integer | 15 | Lockout duration |
| two_factor_enabled | boolean | false | 2FA option |

## Business Rules

1. **Managed by spatie/laravel-settings** - use `Settings::get()` / `Settings::set()`
2. **Groups** organize settings logically
3. **Type casting** automatic based on `type` column
4. **Public settings** exposed to frontend via `/api/config`
5. **Changes audited** via activity log
6. **Validation** via config rules, not DB constraints
7. **Cache** with 1-hour TTL, bust on update

## Sample Data

```php
// Using Settings facade
Settings::set('practice.min_questions_per_session', 10);
Settings::set('spaced_repetition.initial_ease_factor', 2.50);
Settings::set('platform.maintenance_mode', false);

// In code
$minQuestions = Settings::get('practice.min_questions_per_session'); // int 10
$easeFactor = Settings::get('spaced_repetition.initial_ease_factor'); // float 2.5
```

## Migration Checklist

- [ ] Uses spatie/laravel-settings migration
- [ ] Unique index on (group, key)
- [ ] Type column for casting
- [ ] is_public boolean for frontend exposure
- [ ] Description column for documentation
- [ ] No soft deletes
- [ ] Timestamps included
- [ ] Tested: `php artisan migrate:fresh --seed`
- [ ] Tested: `php artisan migrate:rollback`

---

*Generated from Data Dictionary v1.0*