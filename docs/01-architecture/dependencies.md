# Architecture Dependencies

## Purpose

Documents all external dependencies, services, and integrations. Ensures transparency and auditability of third-party code.

---

## Composer Dependencies (Production)

### Core Framework

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `laravel/framework` | ^13.0 | Application framework | MIT |
| `laravel/sanctum` | ^4.0 | API authentication | MIT |
| `livewire/livewire` | ^3.5 | Reactive components | MIT |

### Admin Panel

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `filament/filament` | ^5.7 | Admin panel framework | MIT |
| `filament/forms` | ^5.7 | Form builder | MIT |
| `filament/tables` | ^5.7 | Table builder | MIT |
| `filament/notifications` | ^5.7 | Notifications | MIT |
| `filament/infolists` | ^5.7 | Info lists | MIT |
| `filament/widgets` | ^5.7 | Dashboard widgets | MIT |
| `filament/spatie-laravel-media-library-plugin` | ^5.0 | Media library integration | MIT |
| `filament/geopoints` | ^5.0 | Map fields (if needed) | MIT |

### Database & ORM

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `doctrine/dbal` | ^3.8 | Schema management | MIT |
| `staudenmeir/eloquent-has-many-deep` | ^1.5 | Deep relationships | MIT |
| `staudenmeir/eloquent-json-columns` | ^1.0 | JSON column queries | MIT |

### Media & Files

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `spatie/laravel-medialibrary` | ^11.10 | File management | MIT |
| `spatie/laravel-image-optimizer` | ^1.9 | Image optimization | MIT |
| `intervention/image` | ^3.8 | Image manipulation | MIT |

### Utilities

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `spatie/laravel-permission` | ^6.13 | Roles & permissions | MIT |
| `spatie/laravel-settings` | ^3.5 | System settings | MIT |
| `spatie/laravel-activitylog` | ^4.8 | Audit trail | MIT |
| `spatie/laravel-sluggable` | ^3.5 | URL slugs | MIT |
| `spatie/laravel-tags` | ^4.5 | Tagging | MIT |
| `benwiggy/filament-tags-field` | ^5.0 | Tags field for Filament | MIT |
| `ralouphie/getallheaders` | ^3.0 | HTTP headers polyfill | MIT |
| `nesbot/carbon` | ^2.72 | Date/time handling | MIT |

### Search

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `laravel/scout` | ^10.7 | Full-text search | MIT |
| `meilisearch/meilisearch-php` | ^1.4 | Meilisearch client | MIT |

### PWA & Performance

| Package | Version | Purpose | License |
|---------|---------|---------|---------|
| `silviocorrea/laravel-pwa` | ^3.0 | PWA manifest & service worker | MIT |

---

## Composer Dependencies (Development)

### Testing

| Package | Version | Purpose |
|---------|---------|---------|
| `pestphp/pest` | ^2.35 | Testing framework |
| `pestphp/pest-plugin-laravel` | ^2.3 | Laravel integration |
| `pestphp/pest-plugin-parallel` | ^2.1 | Parallel execution |
| `pestphp/pest-plugin-type-coverage` | ^2.0 | Type coverage |
| `mockery/mockery` | ^1.6 | Mocking (minimal use) |
| `fakerphp/faker` | ^1.23 | Test data generation |
| `nunomaduro/collision` | ^7.10 | Error formatting |

### Code Quality

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/pint` | ^1.18 | Code style |
| `phpstan/phpstan` | ^1.11 | Static analysis |
| `phpstan/phpstan-deprecation-rules` | ^1.1 | Deprecation detection |
| `phpstan/phpstan-phpunit` | ^1.3 | PHPUnit integration |
| `larastan/larastan` | ^3.0 | Laravel-specific rules |
| `nunomaduro/larastan` | ^2.9 | Legacy alias |

### Developer Experience

| Package | Version | Purpose |
|---------|---------|---------|
| `barryvdh/laravel-ide-helper` | ^3.2 | IDE autocomplete |
| `barryvdh/laravel-debugbar` | ^3.10 | Debug toolbar |
| `beyondcode/laravel-query-detector` | ^1.8 | N+1 detection |
| `beyondcode/laravel-er-diagram-generator` | ^2.0 | ER diagrams |

---

## External Services

| Service | Purpose | Integration | Fallback |
|---------|---------|-------------|----------|
| **MySQL 8.0** | Primary database | PDO + Eloquent | Read replica |
| **Redis** | Cache, queues, sessions | `predis/predis` | Database cache |
| **Meilisearch** | Full-text search | Scout driver | MySQL FTS |
| **S3-compatible (MinIO/AWS)** | Media storage | `league/flysystem-aws-s3-v3` | Local disk |
| **Mailgun / SMTP** | Transactional email | `symfony/mailgun-mailer` | Log driver |
| **GitHub Actions** | CI/CD | Native | - |
| **Laravel Cloud / AWS** | Hosting | Docker + Nginx | - |

---

## Frontend Assets (npm)

> Minimal - Filament 5 uses no Vite build for admin. Student PWA uses Vite.

```json
{
  "devDependencies": {
    "vite": "^5.0",
    "laravel-vite-plugin": "^1.0",
    "autoprefixer": "^10.4",
    "postcss": "^8.4",
    "tailwindcss": "^3.4",
    "alpinejs": "^3.14"
  }
}
```

---

## Version Pinning Policy

| Dependency Type | Strategy |
|-----------------|----------|
| Laravel core | `^13.0` (minor updates auto) |
| Filament | `^5.7` (minor updates auto) |
| Spatie packages | `^x.y` (minor updates auto) |
| Pest | `^2.35` (minor updates auto) |
| PHPStan | `^1.11` (minor updates auto) |
| All others | Exact version in `composer.lock` |

---

## Dependency Audit

### Monthly Checks
```bash
composer audit
npm audit
```

### Security Monitoring
- GitHub Dependabot alerts enabled
- `composer audit` in CI pipeline
- Immediate patch for CVE > 7.0

---

## License Compliance

All dependencies are MIT, BSD, or Apache 2.0 compatible.
No GPL or copyleft licenses in production dependencies.

---

## Adding New Dependencies

**Process:**
1. Create GitHub Issue with justification
2. Architect reviews (license, maintenance, bundle size)
3. Add to `composer.json` with version constraint
4. Run `composer update vendor/package`
5. Commit `composer.lock`
6. Update this document

**Prohibited without explicit approval:**
- Packages with < 100 stars (unless official Laravel/Spatie)
- Packages not updated in 12+ months
- Packages adding significant JS bundle
- Packages requiring PHP extensions not in base image

---

*Reviewed monthly. Updated with each `composer update`.*