# E-Fatwa Roadmap

Planned improvements after the FYP, prompted by lecturer feedback that the website could be more interesting with more features. Each phase leaves the site working, so work can stop after any phase.

Tech stack stays the same (Laravel 12, MySQL, Blade + Bootstrap 5.3, Leaflet). New packages and services are added only when a feature needs them.

---

## Phase 1: Foundation (fix what's there)

**Goal:** the site is secure and its existing features all work.

- [x] **Admin login**
  - A small hand-written login page in Bootstrap (no `laravel/ui` needed). No public registration.
  - Add an `is_admin` column to `users` and an `admin` gate (`can:admin` middleware, `@can('admin')` in views).
  - Put every create, edit and delete route (fatwas, preachers, mosques) behind `auth` + `can:admin`. Visitors can still browse and search.
  - Protect `POST /api/fatwas/store` with a Sanctum token that the scraper sends in its request headers.
- [x] **Wire up preacher search:** add a `/search-preacher` route and a navigation link (`PreacherController@search` and `preachers/search.blade.php` already exist).
- [x] **Fix the database mismatch:** make `fatwas.issuing_agency` nullable so scraper imports stop failing.
- [x] **Tidy up:** remove duplicate routes in `routes/web.php`, rename `scraper/scrape_fatwas.py` to `.mjs`, and remove the unused Vite and Tailwind packages.
- [x] **Fix half-finished pages:** add the missing `MosqueController` methods (map, create, edit, delete) and rebuild the preacher list page.
- [x] **Admin seeder:** create the first admin account from `.env` values (no password in code).

## Phase 2: Core value (the central-repository promise)

**Goal:** more sources, fuller content, better search.

- [x] **Full fatwa text:** the scraper opens each fatwa's page and saves its text, and the PDF link if there is one. Add a `source_url` column.
- [x] **More sources:** the scraper is now a Laravel command (`php artisan fatwa:scrape`) with one adapter per website, now covering twelve of fourteen states (all except Pahang, under maintenance, and Terengganu, which publishes no list). Each fatwa is linked to its state's authority.
- [x] **Better search:** MySQL full-text index on title and content, plus state and year filters (as in the SRS).
- [x] **State comparison page** *(headline feature):* pick a category or keyword and see each state's fatwas side by side.

## Phase 3: Engaging features

**Goal:** make the site lively for a demo.

- [x] **Statistics dashboard:** Chart.js (via CDN) charts of fatwas by state, category and year at `/statistik`; the home page shows counts, per-state bars, categories and the latest fatwas.
- [x] **Mosques near me:** use the browser's location to centre the map and list the nearest mosques by distance. *(Done with the UI redesign.)*
- [x] **Prayer times:** today's times from JAKIM e-Solat for the user's zone, cached for the day. Shown on the dashboard and mosque pages.
- [x] **Kuliah and events:** `lectures` table linked to mosques and preachers, an upcoming list at `/kuliah` with "add to calendar", shown on preacher pages and in mosque map popups.

## Phase 4: Users and AI

**Goal:** personal features and an AI helper.

- [x] **Public user accounts and bookmarks:** turn registration back on for regular users so they can save fatwas.
- [x] **Email alerts:** users follow categories and get a weekly email of new fatwas (Laravel scheduler + Mail).
- [x] **Malay/English interface:** move all interface text into translation files and add a language switch.
- [x] **AI question helper:** the site searches the fatwa database first, then Claude summarises only the fatwas found, with links to each one. Always shows a disclaimer that it is a summary, not a ruling. API key stays in `.env`. *(Done: answers are returned directly, not queued; rate-limited per user, and cached for a day per question.)*

## Phase 5: Admin area and data quality

**Goal:** give admins one place to run the site and clean up the data, so what visitors see gets better. Hand-built Blade pages in the site's own theme under `/admin` (admins only); no admin-panel package.

Done after Phase 4 (not admin pages): OCR for scanned fatwas (Tesseract), the "Keputusan" decision box on fatwa pages, Sabah and Sarawak importers, and official agency names.

- [x] **Overview:** totals, the latest import per state and its result, fatwas added this week, unread feedback and new users.
- [x] **Data quality queue:** lists fatwas with no text, no category, no date or no decision found, OCR text that looks garbled, and possible duplicates within a state. Each item has a quick fix (set category, date or title) or "mark as checked".
- [x] **Category management:** rename and merge categories (e.g. "Sosial & Kemasyarakatan" into "Sosial/Syariah"), plus keyword rules that categorise the ~720 uncategorised fatwas automatically. Fatwas no rule matches stay uncategorised.
- [x] **Imports:** run the importer for one state from the browser, with a stored history of each run (new, updated, failed, time taken) instead of log files.
- [x] **Feedback inbox:** read, mark as handled and delete feedback (it is saved today but no page shows it).
- [x] **Users:** list accounts and their alert settings, give or remove admin rights, disable accounts.
- [x] **Agencies:** edit names, logos, addresses and websites in a form instead of the seeder. *(Done; the seeder also fills in each agency's official website.)*

## Later ideas

Not planned yet; picked up when there is time or budget.

- **Same issue in other states:** each fatwa page lists the closest fatwas from other states (MySQL full-text similarity).
- **Malaysia map:** clickable map on the home page, coloured by fatwas per state (Leaflet).
- **Automated tests:** feature tests for routes, admin permissions, search, bookmarks and the decision/date parsers.
- **Malay validation messages:** `lang/ms/validation.php`, so form errors aren't in English in Malay mode.
- **Pahang:** retry when its portal is back from maintenance.
- **Tanya AI:** switch on with an `ANTHROPIC_API_KEY` (code is ready).
- **AI summaries:** a short plain-language summary per fatwa, generated once and stored (Batch API keeps it to a few dollars).
- **Publishing:** hosting, real email and the scheduler on a server, if the project goes public.

---

## Extra packages and services by phase

| Phase | Addition | Purpose |
|---|---|---|
| 1 | Laravel Sanctum (already installed) | Token for the scraper's import API |
| 2 | MySQL full-text index | Faster, more accurate search over full text |
| 2 | Laravel Scout + Meilisearch *(optional)* | If MySQL full-text search isn't good enough |
| 3 | Chart.js (CDN) | Dashboard charts |
| 3 | JAKIM e-Solat API | Prayer times |
| 3–4 | Alpine.js (CDN) or Livewire *(optional)* | Small interactive parts such as live search and bookmark buttons |
| 4 | Claude API via the official Anthropic PHP SDK (`anthropic-ai/sdk`) | AI question helper |
| 4+ | Tesseract OCR (Malay + English data) | Text from scanned fatwas |
| 5 | None planned | Admin pages are plain Laravel + Blade |

## Hosting notes

Laravel needs a PHP + MySQL host. Email alerts and the AI helper also need scheduled tasks (cron) and Laravel's queue, so a small VPS or a managed service such as Laravel Forge or Laravel Cloud is a better fit than basic shared hosting.
