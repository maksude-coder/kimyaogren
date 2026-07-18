# Release Checklist

## Purpose

Mandatory checks before any release to staging or production.

---

## Pre-Release (Development Complete)

### Code Complete
- [ ] All sprint tasks merged to `develop`
- [ ] No open PRs blocking release
- [ ] All CI checks green on `develop`
- [ ] Changelog updated for version

### Testing
- [ ] Full test suite passes: `./vendor/bin/pest --parallel`
- [ ] Code style: `./vendor/bin/pint --test`
- [ ] Static analysis: `./vendor/bin/phpstan analyse` (if configured)
- [ ] Coverage ≥ 80% overall
- [ ] Manual regression testing on staging

### Database
- [ ] Migrations run clean: `php artisan migrate:fresh --seed`
- [ ] Rollback tested: `php artisan migrate:rollback --step=5`
- [ ] No pending migration changes

### Documentation
- [ ] CHANGELOG.md updated with version
- [ ] API docs current (if changed)
- [ ] README.md current (if setup changed)
- [ ] Deployment guide current

---

## Staging Deployment

### Infrastructure
- [ ] Staging environment healthy
- [ ] Database backed up
- [ ] Redis available
- [ ] Storage (S3/MinIO) accessible
- [ ] SSL certificates valid

### Deployment
- [ ] Deploy `develop` to staging
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Warm caches: `php artisan optimize`
- [ ] Queue workers restarted

### Verification
- [ ] Health endpoint: `GET /up` returns 200
- [ ] Login works (admin, teacher, student)
- [ ] Key flows work:
  - [ ] Curriculum browsing
  - [ ] Question practice
  - [ ] Exam simulation
  - [ ] Progress dashboard
- [ ] Filament admin accessible
- [ ] File uploads work
- [ ] Email notifications send
- [ ] Scheduled commands run

### Performance
- [ ] Response time < 200ms p95
- [ ] No N+1 queries (check Telescope/Query Detector)
- [ ] Queue processing < 30s lag
- [ ] Memory usage normal

---

## Production Release

### Pre-Production
- [ ] Staging verified by PM
- [ ] Release branch created: `release/v{X.Y.Z}`
- [ ] Version bumped in `composer.json`
- [ ] Final CHANGELOG entry
- [ ] Database backup scheduled
- [ ] Rollback plan documented

### Release Branch
- [ ] Merge `develop` → `release/v{X.Y.Z}`
- [ ] Final CI run on release branch
- [ ] Tag created: `v{X.Y.Z}`
- [ ] GitHub Release drafted

### Production Deployment
- [ ] Deploy release branch/tag to production
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear & warm caches
- [ ] Restart queue workers
- [ ] Restart horizon/scheduler (if used)
- [ ] Verify health endpoint
- [ ] Smoke test critical paths

### Post-Deployment
- [ ] Monitor error rates (Sentry/Logs) - 15 min
- [ ] Monitor response times - 15 min
- [ ] Monitor queue depth - 15 min
- [ ] Verify scheduled commands running
- [ ] PM sign-off on release
- [ ] Merge release branch → `main` and `develop`
- [ ] Delete release branch
- [ ] Update GitHub Release with notes

---

## Rollback Procedure

### If Critical Issue Found

1. **Immediate**: Disable feature flag (if applicable)
2. **Decision** (5 min): Rollback vs Hotfix
3. **Rollback**:
   - [ ] Deploy previous tag: `git checkout v{X.Y.Z-1}`
   - [ ] Run down migrations if needed: `php artisan migrate:rollback --step=1`
   - [ ] Clear caches
   - [ ] Verify health
4. **Hotfix** (if chosen):
   - [ ] Create `hotfix/v{X.Y.Z+1}` from `main`
   - [ ] Fix, test, deploy
   - [ ] Merge to `main` and `develop`

### Rollback Decision Matrix

| Severity | Action |
|----------|--------|
| Data loss | Immediate rollback |
| Security breach | Immediate rollback |
| Major feature broken | Rollback or hotfix < 1hr |
| Minor bug | Hotfix next deploy |
| Cosmetic | Next sprint |

---

## Versioning

| Version | When |
|---------|------|
| MAJOR (X.0.0) | Breaking changes, architecture shift |
| MINOR (X.Y.0) | New features, backward compatible |
| PATCH (X.Y.Z) | Bug fixes, no new features |

**Current**: 0.1.0 (pre-release)

---

## Release Communication

- [ ] Slack #releases: deployment started
- [ ] Slack #releases: deployment complete
- [ ] Email stakeholders (if major)
- [ ] Update status page (if applicable)
- [ ] Team retrospective (if issues)

---

## Emergency Contacts

| Role | Name | Contact |
|------|------|---------|
| Release Manager | PM | - |
| Database | Architect | - |
| Infrastructure | DevOps | - |
| On-Call | Rotating | - |

---

*No release without completing this checklist. No exceptions.*