# Sprint 2.2 Plan — kimyaogren.com Content Import

> **Status: DONE** — approved and committed in `5e4d8b0` (see `sprint-07-review.md`).

## Sprint Goal
> Import all 302 kimyaogren.com WordPress posts into the curriculum as Theory Articles under the correct MEB grade → unit → topic, without breaking any existing URL (ADR-004).

## PM Decisions (25.09.2026)

| Question | Decision |
|----------|----------|
| URLs | Same address: `/{slug}/` keeps working (ADR-004) |
| Structure | Official MEB units/topics; each post becomes a Theory Article under its topic |
| Images | Download and serve from the same `/wp-content/uploads/…` path |

## Source Analysis

- WordPress REST API (`/wp-json/wp/v2/posts`) is public: 302 posts, 2 pages (privacy, sample), categories only at grade level (9/10/11/12, TYT, AYT, Genel). **No unit/topic information exists on the site.**
- Content HTML uses only `p, strong, b, br, hr, img, iframe` (315 YouTube embeds, 1,361 images, all on `kimyaogren.com/wp-content/uploads`). No post-to-post links inside content.
- 15 posts carry two grade categories (e.g. `gazlarin-ozellikleri` → 9 and 11); 4 posts have none (metals/non-metals/noble gases).

## Scope

1. **Curriculum mapping** — `database/data/meb-kimya-mufredat.php`: MEB 2018 program (9–12, 19 units, 83 topics incl. 3 "Ünite Tekrarı"; 60 have content and are published) and a hand-made mapping of all 302 legacy slugs onto topics. Verified: 302 mapped, 0 duplicates, 0 unmapped. Topics without posts are imported as **Draft** (hidden). Unit-wide review posts go to an "Ünite Tekrarı" topic (order 90).
2. **Content domain (partial)** — `theory_articles` + `theory_versions` per the data dictionaries, plus the ADR-004 legacy columns (`dd-theory-articles.md` v1.1). `ArticleStatus` enum. `Topic::theoryArticles()`.
3. **Importer** — `php artisan kimyaogren:import-wordpress`: idempotent (keyed by WordPress post id); re-running creates a new immutable version only when content changed; downloads images (skip existing); `--skip-images`, `--dry-run`.
4. **Public pages** — legacy article page at `/{slug}/`; topic page lists its articles; category/tag redirects.
5. **Tests** — HTML sanitising/rewriting unit tests, legacy routing & visibility feature tests, importer test with `Http::fake()`.

## Out of Scope

- Filament admin resource for articles (next sprint — content team editing).
- Tags (1,392 WordPress SEO tags), comments, authors, the two WP pages.
- Learning objectives for the imported topics (MEB kazanım codes are not on the site).
- `media` table / spatie-medialibrary — images stay as static files for now.

## Mapping Judgement Calls (for PM review)

| Posts | Site category | Placed in | Why |
|-------|---------------|-----------|-----|
| `gazlarin-ozellikleri`, `gazlari-niteleyen-ozellikler` | 9 + 11 | 11.2 Gazlar › Gaz Yasaları | Content is P-V-T-n / gas-law groundwork |
| Kütlece/hacimce % derişim "Sıvı Çözeltilerde…" (5) | 10 + 11 | 11.3 › Derişim Birimleri | Titles follow the 11th-grade unit |
| `koligatif-ozellikler`, `1581-2` | 10 + 11 | 10.2 Karışımlar | MEB 10.2 covers them introductorily |
| `metallerin-hidrojene-gore-aktiflik-siralamasi` | 10 + 12 | 10.3 › Asit-Baz Tepkimeleri | Written for acid–metal reactions |
| Metals / non-metals / metalloids / noble gases (4) | none | 9.2 › Periyodik Sistem | Element classification (MEB 9.2.3) |
| 12.3 Hidrokarbonlar (31 posts) | 12 | split: Alkanlar / Alkenler / Alkinler | One MEB topic would hold 31 articles |
