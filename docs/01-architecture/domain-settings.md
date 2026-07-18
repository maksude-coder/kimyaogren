# Domain: Settings

## 1. Overview

| Attribute | Value |
|-----------|-------|
| **Domain Name** | Settings |
| **Bounded Context** | System Configuration, Feature Flags, Personalization |
| **Core Responsibility** | System-wide config, feature toggles, user preferences, runtime configuration |
| **Owner** | Platform Lead |
| **Status** | Designed |

## 2. Purpose

The Settings domain manages all configurable aspects of the platform:
- **System Settings** - Global configuration (pacing, grading, features)
- **Feature Flags** - Gradual rollouts, A/B testing, kill switches
- **User Preferences** - Personalization (theme, notifications, language)
- **Program Settings** - Per-program overrides (pacing, grading scales)
- **Runtime Config** - Feature flags evaluated per-request

## 3. Core Entities

### 3.1 Setting (spatie/laravel-settings)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `group` | VARCHAR(255) | NOT NULL | Logical grouping (practice, spaced_repetition, yks, content, platform, security) |
| `key` | VARCHAR(255) | NOT NULL | Setting key within group |
| `value` | TEXT | NOT NULL | Serialized value (JSON, string, int, float, bool, array) |
| `type` | VARCHAR(50) | NOT NULL | `string`, `integer`, `float`, `boolean`, `array`, `json`, `object` |
| `description` | TEXT | NULLABLE | Human-readable description |
| `is_public` | BOOLEAN | NOT NULL DEFAULT FALSE | Exposed to frontend via `/api/config` |
| `validation_rules` | JSON | NULLABLE | Laravel validation rules for UI forms |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(group, key)`

### 3.2 FeatureFlag

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `name` | VARCHAR(100) | NOT NULL, UNIQUE | Kebab-case key (e.g., `new-dashboard-ui`) |
| `description` | VARCHAR(500) | NULLABLE | Human description |
| `enabled` | BOOLEAN | NOT NULL DEFAULT FALSE | Global on/off |
| `rollout_percentage` | TINYINT UNSIGNED | NOT NULL DEFAULT 0 | 0-100 % of eligible users |
| `targeting_rules` | JSON | NULLABLE | Role, grade, school, user IDs, date range |
| `variants` | JSON | NULLABLE | A/B test buckets: `{"control": 50, "new_ui": 50}` |
| `created_by_id` | BIGINT UNSIGNED | NULLABLE, FK → users.id, SET NULL | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

### 3.3 FeatureFlagAssignment (Sticky Assignment)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `feature_flag_id` | BIGINT UNSIGNED | NOT NULL, FK → feature_flags.id, CASCADE | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `variant` | VARCHAR(50) | NOT NULL | Assigned variant key |
| `assigned_at` | TIMESTAMP | NOT NULL | |
| `expires_at` | TIMESTAMP | NULLABLE | For time-limited experiments |

**Unique**: `(feature_flag_id, user_id)`

### 3.4 UserPreference

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FK → users.id, CASCADE | |
| `key` | VARCHAR(100) | NOT NULL | Preference key |
| `value` | TEXT | NOT NULL | Serialized value |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(user_id, key)`

### 3.5 ProgramSetting (Program Override)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AI | |
| `program_id` | BIGINT UNSIGNED | NOT NULL, FK → programs.id, CASCADE | |
| `group` | VARCHAR(255) | NOT NULL | Same groups as System Setting |
| `key` | VARCHAR(255) | NOT NULL | Override key |
| `value` | TEXT | NOT NULL | Override value |
| `type` | VARCHAR(50) | NOT NULL | |
| `is_locked` | BOOLEAN | NOT NULL DEFAULT FALSE | Prevent further overrides |
| `created_by_id` | BIGINT UNSIGNED | NULLABLE, FK → users.id, SET NULL | |
| `created_at` | TIMESTAMP | NOT NULL | |
| `updated_at` | TIMESTAMP | NOT NULL | |

**Unique**: `(program_id, group, key)`

---

## 4. Standard Settings Catalog

### 4.1 Practice Settings (group: `practice`)

| Key | Type | Default | Description | Public |
|-----|------|---------|-------------|--------|
| `min_questions_per_session` | integer | 5 | Minimum questions to start | ✓ |
| `max_questions_per_session` | integer | 50 | Maximum questions | ✓ |
| `session_timeout_minutes` | integer | 120 | Auto-submit after | ✓ |
| `enable_skip_button` | boolean | true | Allow skipping | ✓ |
| `show_explanation_immediately` | boolean | true | Show after answer | ✓ |
| `weak_area_threshold` | integer | 60 | Mastery % below = weak | |
| `spaced_repetition_enabled` | boolean | true | Global SR toggle | |
| `daily_question_limit` | integer | 100 | Safety cap | |

### 4.2 Spaced Repetition (group: `spaced_repetition`)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `initial_ease_factor` | float | 2.50 | SM-2 starting EF |
| `min_ease_factor` | float | 1.30 | Floor |
| `easy_bonus` | float | 1.30 | Interval multiplier for quality 5 |
| `hard_penalty` | float | 0.80 | Multiplier for quality 3 |
| `lapse_reset_interval` | integer | 1 | Days after failure |
| `max_interval_days` | integer | 365 | Cap |
| `new_cards_per_day` | integer | 20 | Max new per day |
| `reviews_per_day_limit` | integer | 200 | Cap total reviews |

### 4.3 YKS Settings (group: `yks`)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `simulation_enabled` | boolean | true | Enable full sim |
| `tyt_duration_minutes` | integer | 135 | TYT total |
| `ayt_duration_minutes` | integer | 180 | AYT total |
| `chemistry_questions_tyt` | integer | 20 | TYT chemistry count |
| `chemistry_questions_ayt` | integer | 40 | AYT chemistry count |
| `scoring_formula` | string | "correct - wrong/4" | Net calculation |

### 4.4 Content (group: `content`)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `enable_rich_editor` | boolean | true | TipTap editor |
| `max_image_upload_mb` | integer | 5 | Image upload limit |
| `auto_generate_slug` | boolean | true | Auto-slug from title |
| `seo_default_image` | string | "/images/og-default.png" | Default OG image |
| `allow_embed_iframe` | boolean | false | Allow YouTube/Vimeo embeds |

### 4.4 Platform (group: `platform`)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `maintenance_mode` | boolean | false | Global maintenance |
| `registration_enabled` | boolean | true | Allow new accounts |
| `default_locale` | string | "tr" | Default language |
| `supported_locales` | array | ["tr", "en"] | Available |
| `session_lifetime_minutes` | integer | 120 | Session timeout |
| `csrf_exempt_routes` | array | [] | API routes exempt from CSRF |

### 4.5 Security (group: `security`)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `password_min_length` | integer | 8 | |
| `password_require_mixed_case` | boolean | true | |
| `password_require_numbers` | boolean | true | |
| `password_require_symbols` | boolean | false | |
| `login_throttle_attempts` | integer | 5 | Max failed |
| `login_throttle_decay_minutes` | integer | 15 | Lockout duration |
| `two_factor_enabled` | boolean | false | Optional 2FA |

---

## 4. Feature Flags Catalog

| Flag | Description | Default | Target | Variants |
|------|-------------|---------|--------|----------|
| `yks_simulation_v2` | New YKS engine | false | Grade 11-12 | `{control: 50, v2: 50}` |
| `adaptive_recommendations` | AI recommendations | false | All students | - |
| `new_dashboard_ui` | Redesigned dashboard | false | 50% rollout | `{control: 50, new: 50}` |
| `gamification_badges` | Achievement badges | false | Opt-in schools | - |
| `parent_portal` | Parent progress view | false | Opt-in teachers | - |
| `offline_pwa` | PWA offline mode | true | All users | - |
| `new_question_types` | Matching, ordering | false | Content editors | - |
| `ai_explanation` | AI-generated explanations | false | Beta users | - |
| `voice_input` | Speech-to-text for answers | false | Mobile app | - |
| `dark_mode_default` | Dark theme default | false | User preference | - |

---

## 5. Settings Service

### 5.1 System Settings (Global)

```php
class SettingsService
{
    // Get with type casting
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('group', $group)->where('key', $key)->first();
        return $setting ? $this->castValue($setting->value, $setting->type) : $default;
    }
    
    public function set(string $group, string $key, mixed $value, string $type = null): void
    {
        $type ??= $this->inferType($value);
        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $this->serializeValue($value, $type), 'type' => $type]
        );
        
        // Bust cache
        Cache::forget("setting:{$group}.{$key}");
        
        // Audit
        activity()->performedOn(new Setting(['group' => $group, 'key' => $key]))
            ->withProperties(['old' => $old, 'new' => $value])
            ->log('setting_updated');
    }
    
    // Bulk get for config endpoint
    public function getPublicConfig(): array
    {
        return Cache::remember('config:public', 3600, function () {
            return Setting::where('is_public', true)
                ->get()
                ->mapWithKeys(fn ($s) => ["{$s->group}.{$s->key}" => $this->castValue($s->value, $s->type)])
                ->toArray();
        });
    }
}
```

### 5.2 Feature Flags

```php
class FeatureFlagService
{
    public function isEnabled(string $flag, ?User $user = null): bool
    {
        $flag = FeatureFlag::where('name', $flag)->first();
        if (!$flag || !$flag->enabled) return false;
        
        if ($flag->rollout_percentage === 100) return true;
        if ($flag->rollout_percentage === 0) return false;
        
        if ($user && $this->matchesTargeting($flag, $user)) return true;
        
        // Consistent hash-based rollout
        $hash = crc32($flag->name . ($user?->id ?? 'anon'));
        return ($hash % 100) < $flag->rollout_percentage;
    }
    
    public function getVariant(string $flag, User $user): ?string
    {
        $flag = FeatureFlag::where('name', $flag)->first();
        if (!$flag || !$flag->variants) return null;
        
        // Check sticky assignment
        $assignment = FeatureFlagAssignment::where('feature_flag_id', $flag->id)
            ->where('user_id', $user->id)
            ->first();
        if ($assignment) return $assignment->variant;
        
        // Assign based on variants weights
        $variant = $this->pickWeightedVariant($flag->variants);
        
        FeatureFlagAssignment::create([
            'feature_flag_id' => $flag->id,
            'user_id' => $user->id,
            'variant' => $variant,
        ]);
        
        return $variant;
    }
    
    private function matchesTargeting(FeatureFlag $flag, User $user): bool
    {
        $rules = $flag->targeting_rules;
        if (!$rules) return false;
        
        // Role matching
        if (isset($rules['roles']) && !$user->hasAnyRole($rules['roles'])) return false;
        
        // Grade matching
        if (isset($rules['grades']) && $user->grade_id && !in_array($user->grade_id, $rules['grades'])) return false;
        
        // Specific users
        if (isset($rules['users']) && !in_array($user->id, $rules['users'])) return false;
        if (isset($rules['excluded_users']) && in_array($user->id, $rules['excluded_users'])) return false;
        
        // Schools
        if (isset($rules['schools']) && $user->school_id && !in_array($user->school_id, $rules['schools'])) return false;
        
        // Date range
        if (isset($rules['date_range'])) {
            $now = now();
            if (isset($rules['date_range']['start']) && $now->lt($rules['date_range']['start'])) return false;
            if (isset($rules['date_range']['end']) && $now->gt($rules['date_range']['end'])) return false;
        }
        
        return true;
    }
}
```

### 5.3 User Preferences

```php
class UserPreferenceService
{
    public function get(User $user, string $key, mixed $default = null): mixed
    {
        return Cache::remember("user_pref:{$user->id}.{$key}", 3600, function () use ($user, $key, $default) {
            return $user->preferences()->where('key', $key)->value('value') ?? $default;
        });
    }
    
    public function set(User $user, string $key, mixed $value): void
    {
        $user->preferences()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)]
        );
        
        Cache::forget("user_pref:{$user->id}.{$key}");
    }
    
    public function getAll(User $user): array
    {
        return $user->preferences->pluck('value', 'key')
            ->map(fn ($v) => json_decode($v, true))
            ->toArray();
    }
}
```

---

## 6. API Contracts

### 6.1 Public Config

```
GET /api/v1/config
Response:
{
  "practice": {
    "min_questions_per_session": 5,
    "max_questions_per_session": 50,
    "spaced_repetition_enabled": true
  },
  "spaced_repetition": {
    "initial_ease_factor": 2.5,
    "max_interval_days": 365
  },
  "platform": {
    "registration_enabled": true,
    "default_locale": "tr"
  },
  "feature_flags": {
    "offline_pwa": true,
    "dark_mode_default": false
  }
}
```

### 6.2 User Preferences

```
GET    /api/v1/user/preferences
PATCH  /api/v1/user/preferences
{
  "theme": "dark",
  "notifications": {"email": true, "push": false, "weekly_summary": true},
  "language": "tr",
  "study_reminder_time": "19:00",
  "preferred_difficulty": 3
}
```

### 6.3 Feature Flags (Admin)

```
GET    /api/v1/admin/feature-flags
POST   /api/v1/admin/feature-flags
PATCH  /api/v1/admin/feature-flags/{flag}
POST   /api/v1/admin/feature-flags/{flag}/rollout
{
  "percentage": 25,
  "targeting_rules": {"roles": ["student"], "grades": [11, 12]}
}
```

### 6.4 System Settings (Admin)

```
GET    /api/v1/admin/settings
GET    /api/v1/admin/settings/{group}
PATCH  /api/v1/admin/settings/{group}
{
  "practice": {
    "min_questions_per_session": 10,
    "spaced_repetition_enabled": true
  }
}
```

---

## 6. Filament Resources

```
Settings/
├── SettingResource
│   ├── Pages: List (grouped tabs), Edit
│   ├── Form: Dynamic based on type (text, number, toggle, select, json-editor)
│   └── Filters: group
├── FeatureFlagResource
│   ├── Pages: List, Create, Edit, View
│   ├── Form: targeting_rules (JSON editor), variants (repeater)
│   ├── Actions: Enable/Disable, Rollout modal
│   └── Widgets: FlagAdoptionWidget
└── ProgramSettingResource (nested in ProgramResource)
    └── RelationManager: Override settings per program
```

---

## 6. Caching Strategy

| Cache Key | TTL | Invalidation |
|-----------|-----|--------------|
| `config:public` | 1 hour | Setting updated (is_public=true) |
| `setting:{group}.{key}` | 24 hours | Setting updated |
| `feature_flag:{name}` | 5 minutes | Flag updated |
| `user_pref:{user_id}.{key}` | 1 hour | Preference updated |
| `feature_assignment:{flag_id}:{user_id}` | 24 hours | Assignment created |

---

## 7. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| **Config Drift** | High | All changes via admin UI; audit log; no direct DB edits |
| **Flag Evaluation Latency** | Medium | Cache 5min; local evaluation; async sync |
| **Sticky Assignment Leak** | Medium | TTL on assignments; cleanup job |
| **Config Tampering** | Critical | Admin-only endpoints; audit log; 2FA for critical flags |
| **Rollout Bugs** | High | Canary deploy; instant kill switch (enabled=false); monitoring |
| **Type Coercion Bugs** | Medium | Strict casting in service; validation on write |

---

*End of Settings Domain Design*