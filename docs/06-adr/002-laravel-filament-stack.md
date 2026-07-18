# ADR-002: Laravel 13 + Filament 5 Stack

## Status
Accepted

## Context
KimyaÖğren needs a modern, maintainable, full-stack PHP framework with a powerful admin panel. The stack must support:
- Rapid CRUD development for admin
- Real-time UI via Livewire
- Strong ecosystem and long-term support
- Type safety with PHP 8.3+

## Decision
**Primary Stack: Laravel 13 + Filament 5 + PHP 8.3 + MySQL 8.0**

### Framework: Laravel 13
- Latest LTS (or current stable) for modern features
- Native: Sanctum, Octane-ready, Pint, Pest support
- Excellent package ecosystem

### Admin Panel: Filament 5
- Best-in-class Laravel admin panel
- Livewire-based (no separate JS build)
- Rich field types, actions, widgets, relation managers
- Plugin ecosystem (forms, tables, infolists, widgets)

### Language: PHP 8.3+
- Typed properties, readonly, enums, attributes
- `strict_types=1` enforced
- Modern syntax throughout

### Database: MySQL 8.0
- JSON support for flexible fields
- CTEs, window functions for analytics
- Proven at scale
- ACID compliance

### Frontend: Livewire 3 + Alpine.js
- Server-driven reactivity
- Minimal JS bundle
- Progressive enhancement

### Testing: Pest PHP
- Expressive syntax
- Parallel execution
- Laravel integration

### CI/CD: GitHub Actions
- Native to repo
- Parallel test execution
- Docker-based environments

---

## Consequences

### Positive
- Unified PHP stack (no Node.js build step for admin)
- Filament handles 80% of admin UI automatically
- Livewire enables reactive UIs without SPA complexity
- Strong type safety with PHP 8.3
- Excellent developer experience

### Negative
- Livewire learning curve for traditional Laravel devs
- Filament customization requires understanding internals
- SEO-limited for public pages (SSR via Livewire possible but complex)
- PHP-only team limits frontend hiring pool

---

## Alternatives Considered

| Stack | Pros | Cons | Decision |
|-------|------|------|----------|
| Laravel + Vue/React SPA | Full SPA control | Double the work, SEO challenges, two codebases | Rejected |
| Laravel + Inertia + Vue | SPA-like, single routes | Still JS build, more complexity than Filament | Rejected |
| Symfony + EasyAdmin | Enterprise-grade | Smaller ecosystem, steeper learning | Rejected |
| **Laravel + Filament** | **Best admin DX, unified stack** | **Livewire mental model shift** | **Accepted** |

---

## Related
- ADR-003: Domain-Driven Design Structure
- ADR-005: MySQL Database

*Accepted: Sprint 0.2 - Project initialization*