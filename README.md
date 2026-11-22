# E-Fatwa (Fatwa Stop Centre)

A centralised web application for searching and referring to fatwas (Islamic rulings) issued by federal and state religious authorities across Malaysia, together with directories of religious authorities, certified preachers and mosques.

Built as my Final Year Project (FYP) at Universiti Tenaga Nasional (UNITEN), 2025.

## Why

Fatwas in Malaysia are published separately by each state and federal religious authority, so finding a ruling means searching many different websites. E-Fatwa brings them into a single, searchable repository and adds related information (who issued it, certified preachers, nearby mosques) in one place.

## Features

| Feature | Description |
|---|---|
| **Home** | Hero search with live suggestions, animated statistics, fatwa coverage per state, today's prayer times, categories and the latest fatwas (`/`). |
| **Explore & Search Fatwa** | Browse fatwas with full-text search over title and fatwa text, filterable by state, year and category (`/fatwas`). Each fatwa page shows the extracted text, the official PDF and related fatwas. |
| **Compare States** | Pick an issue or category and see each state's fatwas side by side (`/bandingkan`). |
| **Statistics** | Chart.js charts of fatwas per state (with text coverage), per year and per category, plus a summary table (`/statistik`). |
| **Religious Authorities** | Profiles of federal and state religious bodies with address, contact info, fatwas per year and their latest fatwas (`/authorities`). |
| **Certified Preachers** | Directory of certified preachers linked to their authority (`/preachers`). |
| **Masjid Locator** | Mosque list plus an interactive map (Leaflet + OpenStreetMap): the list and map are linked, and "locate me" sorts mosques by distance (`/mosques`, `/mosques/map`). |
| **Kuliah & Events** | Upcoming kuliah, ceramah, tazkirah and forums at mosques, filterable by mosque, preacher and type, with "add to calendar" (.ics) downloads. Shown on preacher pages and mosque map popups (`/kuliah`). |
| **Prayer Times** | Today's times from JAKIM e-Solat for any of the 60 official zones, with a countdown to the next prayer; the chosen zone is remembered (`/waktu-solat` JSON). |
| **Tanya AI** | Ask a question; the site finds matching fatwas with full-text search and Claude summarises only what those fatwas say, with numbered links to each one and a disclaimer that it is not a ruling. Needs a login and `ANTHROPIC_API_KEY` (`/tanya`). |
| **Accounts & bookmarks** | Free sign-up (`/daftar`). Users save fatwas ("Simpanan saya") and manage their profile, password and alerts (`/akaun`). |
| **Email alerts** | Opt-in weekly email of new fatwas in the categories and states a user follows, with a one-click signed unsubscribe link (`php artisan fatwa:send-alerts`). |
| **Malay / English** | "EN | BM" switch in the navbar; Malay is the default. Remembered per visitor and per account. |
| **Feedback** | Users submit feedback (name, email, phone, message) (`/feedbacks`). |
| **Admin login** | Admins log in at `/login` to create, edit and delete fatwas, preachers, mosques and kuliah. Admins only come from the seeder; sign-ups are ordinary users. |
| **Fatwa importer** | `php artisan fatwa:scrape` imports fatwas from ten sources: Wilayah Persekutuan, Selangor, Negeri Sembilan, Perak, Kedah, Perlis, Pulau Pinang, Melaka, Kelantan and Johor, including their full text where it is published. |
| **Import API** | `POST /api/fatwas/store` accepts JSON fatwas from external tools (needs an admin API token). |

### Interface

- Emerald and gold design system (`public/css/theme.css`) on top of Bootstrap 5.3, with a light/dark theme toggle that remembers the visitor's choice.
- Live search suggestions in the navbar and home page (`/fatwa-suggest`), with keyboard navigation. Press `/` anywhere to jump to search.
- Reading view for each fatwa: progress bar, text size and font controls, copy link and print.
- Scroll-in animations and counters (turned off for visitors who prefer reduced motion).
- All interactions live in one small script, `public/js/app.js`, with no build step.

## Tech stack

- **Backend:** PHP 8.2+, Laravel 12, Laravel Sanctum
- **Frontend:** Blade templates, Bootstrap 5.3 + Bootstrap Icons (via CDN), Google Fonts (Fraunces, Plus Jakarta Sans, Amiri), `public/css/theme.css`, `public/js/app.js`
- **Maps:** Leaflet 1.9.4 with OpenStreetMap tiles
- **Charts:** Chart.js 4.4 (via CDN)
- **AI:** Claude (`claude-opus-5-5`) through the official Anthropic PHP SDK (`anthropic-ai/sdk`), with server-side refusal fallbacks (`app/Services/FatwaAssistant.php`)
- **Languages:** Laravel translations, Malay strings as keys with English in `lang/en.json` (`app/Http/Middleware/SetLocale.php`)
- **Prayer times:** JAKIM e-Solat API, cached per zone per day (`app/Services/PrayerTimes.php`, zones in `config/prayer_zones.php`)
- **Database:** MySQL (developed against a database named `eFatwa_db`)
- **Search:** MySQL FULLTEXT index on fatwa title + text
- **Importer:** Laravel Artisan command with one adapter per website (`app/Scrapers`); PDF text via Poppler's `pdftotext`
- **HTTPS:** outgoing requests verify certificates with `composer/ca-bundle` plus extra intermediates in `resources/certs/` (`app/Support/CaBundle.php`), so no `php.ini` CA setup is needed

## Data model

```
Authority 1───* Fatwa *───1 Category
    │
    └──1───* Preacher 1───* Lecture *───1 Mosque

Mosque     (name, location, contact_info, latitude, longitude)
Lecture    (title, type, description, starts_at, ends_at, venue)
Feedback   (name, email, phone_number, content)
```

- **fatwas**: `title`, `content` (summary), `full_text`, `source_url` (unique), `pdf_url`, `issuing_agency` (→ authorities), `category_id` (→ categories), `date_issued`, `issued_year`, `gazette_no`, `status`
- A fatwa's **state** comes from its authority.
- **authorities**: `authority_name`, `contact_info`, `street`, `city`, `poscode`, `state`
- **preachers**: `preacher_name`, `certification_details`, `location`, `state`, `authority_id`
- **lectures** (kuliah): `title`, `type` (Kuliah / Ceramah / Tazkirah / Forum), `description`, `starts_at`, `ends_at`, `mosque_id`, `preacher_id`, `venue` (when not at a listed mosque)

## Getting started

Requirements: PHP 8.2+ (with curl, fileinfo, intl, mbstring, openssl, pdo_mysql), Composer, MySQL 8. Importing fatwa text also needs Poppler's `pdftotext` (`scoop install poppler` on Windows).

```bash
git clone https://github.com/abdqayyum03/e-fatwa.git
cd e-fatwa

composer install

cp .env.example .env
php artisan key:generate
```

Edit `.env` to point at MySQL and set the first admin account:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eFatwa_db
DB_USERNAME=root
DB_PASSWORD=

ADMIN_NAME="Admin"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=choose-a-strong-password
```

Then create the tables, seed the admin account and authorities, and run:

```bash
php artisan migrate --seed
php artisan serve    # http://localhost:8000
```

No front-end build step is needed: Bootstrap, Bootstrap Icons, Leaflet and Chart.js load from CDNs.

The app runs on Malaysian time (`APP_TIMEZONE=Asia/Kuala_Lumpur`), which kuliah times and calendar files rely on.

### Admin access

Visitors can browse and search everything without logging in. Creating, editing and deleting fatwas, preachers, mosques and kuliah needs an admin login: click **Admin** in the navigation bar (or go to `/login`). There is no public registration; admin accounts come from the `ADMIN_*` values in `.env` (re-run `php artisan db:seed --class=AdminUserSeeder` after changing them).

## Importing fatwas

The importer reads each mufti website directly and saves fatwas to the database. It is safe to re-run: fatwas are matched by their source URL and updated instead of duplicated.

```bash
php artisan fatwa:scrape                  # all sources (list below)
php artisan fatwa:scrape selangor         # one source
php artisan fatwa:scrape --limit=5        # quick test, 5 per source
php artisan fatwa:scrape --no-text        # skip PDF downloads (fast, no full text)
php artisan fatwa:scrape --refresh-text   # re-extract text for every fatwa
```

| Source | Website | How it is read | Full text |
|---|---|---|---|
| `wp` | efatwa.muftiwp.gov.my | Paged HTML table (title, year, category, status, PDF) | Yes, from PDF |
| `perak` | mufti.perak.gov.my | Single HTML table of gazetted fatwas | Mostly no: most PDFs are scanned images |
| `selangor` | muftiselangor.gov.my | WordPress JSON API (gazetted-fatwa category) | Only newer posts: posts from 2023 and earlier are page images |
| `ns` | muftins.gov.my | WordPress JSON API (`fatwa` post type, with categories) | Yes, as web text; gazette PDFs are scans |
| `kedah` | ifatwa.kedah.gov.my | Three HTML tables: gazetted, Irsyad Al-Fatwa, Irsyad Al-Hukmi | Yes, from PDF |
| `perlis` | muftiperlis.gov.my | RSS feed of the "Himpunan Fatwa Negeri" category | Yes, as web text |
| `penang` | mufti.penang.gov.my | HTML table of gazetted fatwas; PDF links expire, so they are read at import time and not stored | Yes, from PDF |
| `melaka` | muftimelaka.gov.my | Three category lists, one page per fatwa | Yes, the page shows the gazette as text |
| `kelantan` | data.gov.my (official open data, CC BY) | CSV of fatwas 2015–2018 | Yes, in the CSV |
| `johor` | said.johor.gov.my (e-Fatwa Johor) | Status-filtered list (about 1,600) plus a detail page each | Yes, as web text |

Notes:

- The importer waits about a second between requests so it doesn't overload the government sites, and identifies itself honestly as `e-fatwa-scraper/1.0`. A first full run takes about an hour (Johor alone is ~30 minutes); later runs skip PDFs already read.
- PDF text is extracted with `pdftotext`. Set `PDFTOTEXT_PATH` in `.env` if it isn't on your PATH.
- Some sites send an incomplete TLS certificate chain. The missing intermediate certificates are kept in `resources/certs/`.
- Dates: when a site only gives an upload date, the importer reads the gazette or meeting date from the fatwa text instead. Selangor's image-only posts were all re-uploaded on one day in 2023, so their date is left unknown unless the title contains a year.
- Categories with different names on different sites are merged (e.g. Ibadah → Ibadat, Muamalat/Ekonomi → Muamalat).
- **Not imported (Nov 2026):** **Pahang** (portal under maintenance), **Terengganu** (the mufti website publishes no fatwa list), plus Sabah and Sarawak (not attempted). JAKIM's i-FIQH portal was not used because its pages contained injected spam links.
- **Kelantan** has no fatwa list on its own site; the department links to the official open-data set on data.gov.my, which only covers 2015–2018.
- To add another state, write a class implementing `App\Scrapers\FatwaSource` and register it in `app/Console/Commands/ScrapeFatwas.php`.

The older Node.js/Puppeteer script in `scraper/` (from the FYP) is superseded by this command.

## Project structure

```
app/Http/Controllers/       Fatwa, Authority, Preacher, Mosque, Lecture, Statistics, PrayerTime, Feedback
app/Http/Controllers/Auth/  LoginController (admin login)
app/Http/Controllers/Api/   FatwaApiController (import endpoint)
app/Console/Commands/        ScrapeFatwas (fatwa:scrape)
app/Scrapers/               One adapter per mufti website + FatwaImporter
app/Models/                 Eloquent models
app/Services/PrayerTimes.php  JAKIM e-Solat client (cached)
app/Support/CaBundle.php    Certificate bundle for outgoing HTTPS
database/migrations/        Schema
database/seeders/           AdminUserSeeder, AuthoritySeeder
resources/views/            Blade views per feature
routes/web.php, api.php     Routes
resources/certs/            Extra TLS intermediates for government sites
config/prayer_zones.php     The 60 JAKIM prayer-time zones
scraper/                    Original FYP Node.js scraper (superseded)
docs/                       FYP1 report, SRS, final report
```

## Documentation

The `docs/` folder contains the original academic documents:

- `FYP1 SW01082193.doc`: FYP1 proposal/report
- `SRS.docx`: Software Requirements Specification
- `SW01082193 FINAL REPORT.docx`: Final report

## Running scheduled jobs

Weekly jobs are defined in `routes/console.php`: re-import fatwas (Sunday 23:00) and send email alerts (Monday 08:00, Malaysian time). They run only while the scheduler runs:

```bash
php artisan schedule:work        # or a cron / Task Scheduler entry running "php artisan schedule:run" every minute
```

Email uses `MAIL_MAILER=log` by default (messages go to `storage/logs/laravel.log`); set the `MAIL_*` values in `.env` to send real email. Set `APP_URL` to the public address so links in emails are correct.

## Known gaps / ideas for future work

See [ROADMAP.md](ROADMAP.md) for the full plan of fixes and new features.

Things left unfinished at the end of the FYP, noted for anyone (or me) picking it up again:

- **Scanned documents have no text.** Most Perak PDFs, Selangor posts from 2023 and earlier, some Kedah PDFs and Negeri Sembilan's gazette PDFs are scanned images, so 323 of 2,732 fatwas can only be found by title. OCR (e.g. Tesseract) would fix this.
- **Unknown dates.** 207 fatwas have no known date (mostly Selangor's re-uploaded image posts and scanned gazettes).
- **Ten of fourteen states.** Missing: Pahang (portal under maintenance), Terengganu (no fatwa list online), Sabah and Sarawak (not attempted). Kelantan only covers 2015–2018 (its open-data set). The national portals (e-fatwa.gov.my, e-SMAF) were unreachable in Nov 2026.
- **Categories** come from each site's own scheme (Johor alone uses Masjid and Wakaf heavily); Perak, Selangor, Kedah, Perlis and Penang publish none, so 671 fatwas are uncategorised.
- **Uneven coverage.** Johor has 1,608 fatwas, about 60% of the database, so statistics and search results lean towards Johor.
- **Laravel's own validation messages** ("The email field is required") are English in Malay mode, because the project has no Malay `lang/ms/validation.php`.
- **Tanya AI** has only been tested without an API key; set `ANTHROPIC_API_KEY` and try a few questions before relying on it.
- **No admin pages yet** for categories, religious authorities or reading submitted feedback (`CategoryController` is an empty stub).
- **Authority profile columns missing.** The authority page shows `sejarah`, `visi`, `misi`, `objektif` and `org_chart`, but no migration creates them (they existed only in the old WAMP database), so those sections are blank. Add a migration and fill them in.
- **Original data lost.** The FYP database lived in WampServer and was not exported. Fatwas are re-imported with `fatwa:scrape`, but preachers, mosques and kuliah start empty and must be added by an admin.
- **Home page video.** `public/videos/fatwa-hero.mp4` from the FYP is no longer shown on the home page.
- **No tests** beyond the Laravel defaults.

## License

Personal academic project. No license has been chosen yet; all rights reserved by the author.
