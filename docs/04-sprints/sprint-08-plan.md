# Sprint 2.3 Plan — Student Registration and Login

> **Status: DONE** — approved and committed in `5e4d8b0` (see `sprint-08-review.md`).

## Sprint Goal
> Students can create an account in two steps (account, then grade), sign in and out, reset a forgotten password and confirm their e-mail address — design § 08 "Kayıt ve giriş".

Epic: `docs/05-backlog/epic-02-student-interface.md` · Data: `docs/02-data/dictionaries/dd-users.md` v1.1

## PM Decisions (16.11.2026)

| Question | Decision |
|----------|----------|
| Password rule (design vs. `domain-user.md`) | **Design rule:** at least 8 characters and at least one digit. `domain-user.md` Password Policy updated to match. |
| E-mail verification | **Sent, not enforced.** The account works immediately; a reminder with a "send again" action is shown until the address is confirmed. |
| Terms / KVKK / parental consent | **Page skeletons + consent timestamp.** `/kullanim-kosullari` and `/kvkk-aydinlatma-metni` exist with placeholder text to be supplied by the PM; the moment of acceptance is stored. The design's sentence about parental consent is removed; the parental flow is a later sprint. |
| "Mezun · YKS hazırlık" option | **Included.** Stored as `users.is_graduate`; a graduate has no `grade_id`. |

## 1. Scope

| # | Item | Design § |
|---|------|----------|
| 1 | `users.grade_id`, `users.is_graduate`, `users.terms_accepted_at` (migration, `dd-users.md` v1.1) | — |
| 2 | `App\Enums\UserStatus` (documented in `enums.md`, never created) and its cast on `User` | — |
| 3 | Register step 1 `GET/POST /kayit` — name, e-mail, password; password rules visible before submit; errors under the field with icon + text | 08 |
| 4 | Register step 2 `GET/POST /kayit/sinif` — grade radio list (9–12 of the published program) + graduate | 08 |
| 5 | Login `GET/POST /giris` (remember me), logout `POST /cikis` | 08 |
| 6 | Forgotten password `GET/POST /sifremi-unuttum`, `GET /sifre-sifirla/{token}`, `POST /sifre-sifirla` | 08 ("Şifremi unuttum") |
| 7 | E-mail verification link `GET /e-posta-dogrula/{id}/{hash}`, resend `POST /e-posta-dogrula/yeniden`, reminder banner | — |
| 8 | Legal page skeletons `GET /kullanim-kosullari`, `GET /kvkk-aydinlatma-metni` | 08 (footer links) |
| 9 | Navigation: "Giriş yap" / "Ücretsiz hesap aç" for guests, name + "Çıkış yap" for signed-in users (landing page and app shell) | Tanıtım, 02 |
| 10 | `/mufredat` opens the signed-in student's own grade | 03 |
| 11 | Turkish auth, password-reset and e-mail texts (`lang/tr`) | — |
| 12 | Feature tests for every flow | — |

## 2. Business Rules

1. **Step 1 creates the account** (role `student`, status `Active`) and signs the student in; step 2 only stores the grade. A student who leaves at step 2 keeps a working account without a grade and is offered step 2 again from the reminder in the app shell.
2. Registration always creates a **student**. Role and status are never taken from the request.
3. Password: minimum 8 characters, at least one digit (PM decision). Name: maximum 100 characters (`dd-users.md`).
4. Grade choices are the **active grades of the first published program** (same rule as `/mufredat`). A chosen grade sets `grade_id` and clears `is_graduate`; "Mezun" sets `is_graduate` and clears `grade_id`.
5. Login is refused for `Suspended` and `Deleted` accounts with the same generic message as a wrong password (no account enumeration).
6. Login throttling: 5 failed attempts per e-mail + IP lock the form for 15 minutes (`domain-user.md` Password Policy).
7. The password-reset request always answers with the same message whether or not the address exists (no account enumeration).
8. `terms_accepted_at` is set at registration; the form states that continuing means acceptance and links both legal pages.
9. After login/registration the student lands on their grade in the curriculum browser (the "Bugün" page does not exist yet).

## 3. Explicitly Out of Scope

- Parental consent for under-18 students, `birth_year`, `parent_email` (PM decision — later sprint).
- Profile page (changing name, grade, password) — design says "Sonradan Profil'den değiştirebilirsin"; until Profile exists the grade can be changed by revisiting `/kayit/sinif`.
- Remaining `dd-users.md` columns (`phone`, `avatar_path`, `school`, `city`, `last_active_at`, `preferences`, soft deletes), spatie roles/permissions, 2FA, password history, Sanctum API endpoints.
- Filament `UserResource`.
- Final legal texts (content, supplied by the PM).
- Production mail service configuration (deployment; locally mail goes to the log).

## 4. Design Deviations (recorded, for PM awareness)

| Design | Implemented | Why |
|--------|-------------|-----|
| Step 2 has a "‹ Geri" button to step 1 | No back button; "Şimdilik geç" link instead | The account already exists after step 1 (rule 1), so there is nothing to go back to |
| "18 yaş altıysan velinin onayı istenir." | Sentence removed | PM decision — no parental flow yet |
| Login panel text "Kaldığın kazanım seni bekliyor." | "Kimyayı ezberleme, nedenini anla." | Progress does not exist yet; the claim would be false |
| Step 2 hint "Sonradan Profil’den değiştirebilirsin." | "Sonradan değiştirebilirsin." | The Profile page does not exist yet |
| Grade radio rendered as buttons | Native radio inputs styled as the design's rows | Works without JavaScript and with the keyboard |

## 5. Definition of Done

- [x] All tests pass (existing 124 + 48 new = 172)
- [x] `vendor/bin/pint --test` clean
- [x] `npm run build` succeeds
- [x] Register → grade → curriculum, logout, failed and successful login verified in a browser (mobile and desktop width, light theme)
- [x] `dd-users.md`, `domain-user.md`, `epic-02-student-interface.md`, `debt-log.md`, `changelog.md` updated
