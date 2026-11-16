# Sprint 2.3: Student Registration and Login — Review

Plan: `docs/04-sprints/sprint-08-plan.md` · Epic: `docs/05-backlog/epic-02-student-interface.md` · Data: `dd-users.md` v1.1

## Demo Summary

| Route | Screen | Notes |
|-------|--------|-------|
| `GET/POST /kayit` (`register`) | 08 Kayıt, adım 1 | Name, e-mail, password. Rules listed before submit and ticked while typing; errors under the field with icon + text; "Göster/Gizle" on the password |
| `GET/POST /kayit/sinif` (`register.grade`) | 08 Kayıt, adım 2 | Active grades of the published program + "Mezun · YKS hazırlık" |
| `GET/POST /giris` (`login`) | 08 Giriş | Remember me; one generic error for wrong password, unknown address and suspended account; locked for 15 minutes after 5 failures |
| `POST /cikis` (`logout`) | — | |
| `GET/POST /sifremi-unuttum`, `GET /sifre-sifirla/{token}`, `POST /sifre-sifirla` | — | Same answer whether or not the address exists |
| `GET /e-posta-dogrula/{id}/{hash}`, `POST /e-posta-dogrula/yeniden` | — | Signed link; reminder banner with "Yeniden gönder" until verified; never blocks the account |
| `GET /kullanim-kosullari`, `GET /kvkk-aydinlatma-metni` | — | Skeletons; text to be supplied by the PM |

After registration or login the student lands on their own grade in `/mufredat`. The landing page and the app shell now show "Giriş yap" / "Ücretsiz hesap aç" to guests and the name, grade and "Çıkış yap" to signed-in students.

## Files

- **Docs** — `sprint-08-plan.md`, this review, `dd-users.md` v1.1, `domain-user.md` (password policy), `epic-02-student-interface.md`, `debt-log.md` (`DEBT-013`), `changelog.md`
- **Schema** — `2026_11_16_080458_add_grade_and_consent_to_users_table.php` (`grade_id`, `is_graduate`, `terms_accepted_at`)
- **Domain** — `App\Enums\UserStatus`; `User`: `MustVerifyEmail`, `status`/`is_graduate`/`terms_accepted_at` casts, `grade()`, `needsGradeSelection()`; `UserFactory` states `suspended()`, `graduate()`
- **HTTP** — `App\Http\Controllers\Student\Auth\{Register,GradeSelection,Login,PasswordReset,EmailVerification}Controller`, `App\Http\Requests\Student\{Register,Login}Request`, `routes/web.php`, `bootstrap/app.php` (guest/auth redirects), `CurriculumController::index()` (own grade first)
- **Views** — `components/student/{auth,field,password-rules}.blade.php`, `student/auth/*`, `student/legal/*`, `components/student/layout.blade.php` (account block, reminders), `student/home.blade.php` (auth links, legal footer)
- **Frontend** — `resources/css/app.css` (field and grade-option styles), `resources/js/app.js` (password toggle, live rules)
- **Language** — `lang/tr/{auth,passwords,validation}.php`, `lang/tr.json` (verification and reset e-mails)
- **Tests** — `tests/Feature/Student/Auth/{Registration,Login,PasswordReset,EmailVerification}Test.php`, `tests/Feature/Student/LegalPagesTest.php`, `tests/Unit/UserStatusTest.php`

## Definition of Done — Verified

| Criterion | Status | Evidence |
|-----------|--------|----------|
| All tests pass | ✅ | 172/172 (124 existing + 48 new) |
| Code style | ✅ | `vendor/bin/pint --test` clean |
| Build | ✅ | `npm run build` succeeds |
| Browser verification | ✅ partial | Light theme, mobile and desktop width: register (error state and success) → grade → curriculum; logout; failed and successful login; Turkish verification e-mail in the mail log. **Not opened in a browser:** forgot/reset password pages, legal pages, dark theme (covered by feature tests only) |
| Docs synchronized | ✅ | See Files |

## Findings

1. **`DEBT-013` — flaky test from Sprint 2.2.** `LegacyContentTest::test_topic_page_links_to_articles_in_order` failed once in a full-suite run and passed on the next: the factory's random `order` can tie with the pinned `0`. Not touched (Task Isolation); one-line fix recommended in the debt log.
2. **Suite duration.** The full suite took 82 s on the first run and about 15 s afterwards on this machine.

## Needs PM Attention

1. **Legal texts.** `/kullanim-kosullari` and `/kvkk-aydinlatma-metni` say the text is being prepared. Registration records `terms_accepted_at`, but there is nothing substantive to accept until the texts exist — they must be in place before public launch. Most students are minors; the parental-consent flow is still open.
2. **Mail in production.** Verification and reset e-mails need a real mail service (`MAIL_*`, sender address). Mail is sent during the request: if the service is down, registration fails with an error. Queueing mail is a deployment decision.
3. **`dd-users.md` v1.1** adds `is_graduate` and `terms_accepted_at` on the PM's decisions of 16.11.2026; column names and types were chosen by the AI developer and need Architect confirmation.
4. **Account status.** Self-registered accounts start `Active`, because verification is not enforced; `Pending` is currently unused.
5. **Admin panel.** Student accounts cannot be listed or suspended from Filament yet (no `UserResource`).
6. `.claude/launch.json` was added so the local server can be opened in the preview pane (development tooling only).

## Approval

**Status: Pending PM approval.** No commit has been created.
