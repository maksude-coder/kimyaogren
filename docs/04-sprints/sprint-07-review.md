# Sprint 2.2: kimyaogren.com Content Import — Review

Plan: `docs/04-sprints/sprint-07-plan.md` · Decision: `docs/06-adr/004-preserve-wordpress-urls.md`

## Result

| | |
|---|---|
| WordPress posts imported | **302 / 302** as Published theory articles, each with an immutable version 1 |
| Curriculum | Program `Lise Kimya (MEB)` (`lise-kimya`), grades 9–12, 19 units, 83 topics (60 published with content, 23 Draft/empty) |
| Images | 1,378 downloaded to `public/wp-content/uploads/…` (73 MB). **285 could not be downloaded: they return 404 on the live WordPress site too** (media records exist, files are gone from the server; not in the Internet Archive either). List: `storage/app/wordpress-import/eksik-gorseller.txt` — 235 inline images in 51 posts + 50 cover images (covers are cleared so they don't render broken) |
| Videos | 315 YouTube embeds kept |
| Re-run | Idempotent: second run → 0 new articles, 0 new versions |

## URL Preservation (verified in the browser against the dev server)

| Old URL | New behaviour |
|---------|---------------|
| `/notrallesme-tepkimeleri/` | 200, article page, `<link rel="canonical">` = same URL |
| `/notrallesme-tepkimeleri` | 301 → `/notrallesme-tepkimeleri/` (as WordPress) |
| `/wp-content/uploads/2022/05/…jpg` | 200, static file, same path |
| `/category/10-sinif-konulari/` | 301 → `/mufredat/lise-kimya/10` |
| `/tag/…/` | 301 → `/mufredat` |
| unknown slug | 404 |
| `/admin`, `/mufredat/…` | unaffected (legacy handling is the router fallback) |

## Files

- **Docs** — ADR-004; `dd-theory-articles.md` v1.1 (`order`, `featured_image_path`, `legacy_slug`, `legacy_wp_id`); this sprint's plan/review; changelog
- **Data** — `database/data/meb-kimya-mufredat.php` (curriculum + 302-slug mapping)
- **Schema** — `create_theory_articles_table`, `create_theory_versions_table` migrations
- **Domain** — `ArticleStatus` enum, `TheoryArticle`, `TheoryVersion` models, `Topic::theoryArticles()`, `TheoryArticleFactory`
- **Import** — `app/Console/Commands/ImportWordPressContent.php`, `app/Support/WordPressContent.php` (allowlist sanitizer, upload-URL rewriting)
- **Web** — `LegacyContentController` (fallback), `student/article.blade.php`, article list on topic page, article counts on grade page, `.article-content` styles, `<head>` slot for canonical/OG tags
- **Server** — `public/.htaccess`: removed the trailing-slash 301 rule; `.gitignore`: `/public/wp-content`
- **Tests** — `WordPressContentTest` (6), `LegacyContentTest` (8), `ImportWordPressContentTest` (6) → suite **124/124**, Pint clean

## Bugs Found During Verification

1. **Cover images were attached to the wrong articles.** `Collection::flatMap()` renumbered the media-id keys, so only 32 of 302 articles got a cover and those by accident. Fixed with an explicit keyed loop; re-import corrected all rows. Covered by `test_missing_cover_images_are_cleared` (asserts the id → path mapping).
2. Test client strips trailing slashes, so legacy-URL tests dispatch the exact URI through the HTTP kernel.

## Needs PM Attention

1. **Mapping review.** The site had no unit/topic data; every placement is my judgement against the MEB 2018 program. Contested cases are listed in the plan ("Mapping Judgement Calls"). Moving a post = editing one line in `meb-kimya-mufredat.php` and re-running the import.
2. **285 missing images** — only recoverable from a hosting/WordPress backup. Copy them into `public/wp-content/uploads/` with the same paths and re-run the import (covers get re-attached automatically).
3. **Deployment:** run `php artisan kimyaogren:import-wordpress` on the server **before** DNS switches away from WordPress (it reads the live WP API and downloads images). On this Windows machine PHP had no CA bundle; the import was run with `-d curl.cainfo=…` — a local environment issue, not a code change.
4. The 1,392 WordPress tags, TYT/AYT/Genel categories and the two WP pages (privacy, sample) are not imported; `/gizlilik-politikasi/` will 404 until a privacy page exists (a KVKK page is needed anyway).
5. Next sprint candidate: Filament resource for theory articles so the content team can edit imported content (new version per save).

## Approval

**Status: Approved and committed** — `5e4d8b0` (together with Sprints 2.1 and 2.3).
