# E-Fatwa (Fatwa Stop Centre)

A centralised web application for searching and referring to fatwas (Islamic rulings) issued by federal and state religious authorities across Malaysia, together with directories of religious authorities, certified preachers and mosques.

Built as my Final Year Project (FYP) at Universiti Tenaga Nasional (UNITEN), 2025.

## Why

Fatwas in Malaysia are published separately by each state and federal religious authority, so finding a ruling means searching many different websites. E-Fatwa brings them into a single, searchable repository and adds related information (who issued it, certified preachers, nearby mosques) in one place.

## Features

| Feature | Description |
|---|---|
| **Home** | Hero search with live suggestions, animated statistics, fatwa coverage per state, categories and the latest fatwas (`/`). |
| **Explore & Search Fatwa** | Browse fatwas with full-text search over title and fatwa text, filterable by state, year and category (`/fatwas`). Each fatwa page shows the extracted text, the official PDF and related fatwas. |
| **Compare States** | Pick an issue or category and see each state's fatwas side by side (`/bandingkan`). |
| **Religious Authorities** | Profiles of federal and state religious bodies with address, contact info, fatwas per year and their latest fatwas (`/authorities`). |
| **Certified Preachers** | Directory of certified preachers linked to their authority (`/preachers`). |
| **Masjid Locator** | Mosque list plus an interactive map (Leaflet + OpenStreetMap): the list and map are linked, and "locate me" sorts mosques by distance (`/mosques`, `/mosques/map`). |
| **Feedback** | Users submit feedback (name, email, phone, message) (`/feedbacks`). |
| **Admin login** | Admins log in at `/login` to create, edit and delete fatwas, preachers and mosques. |
| **Fatwa importer** | `php artisan fatwa:scrape` imports gazetted fatwas from the Federal Territory, Perak and Selangor mufti websites, including the text of their PDFs. |
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
- **Database:** MySQL (developed against a database named `eFatwa_db`)
- **Search:** MySQL FULLTEXT index on fatwa title + text
- **Importer:** Laravel Artisan command with one adapter per website (`app/Scrapers`); PDF text via Poppler's `pdftotext`

## Data model

```
Authority 1───* Fatwa *───1 Category
    │
    └──1───* Preacher

Mosque     (name, location, contact_info, latitude, longitude)
Feedback   (name, email, phone_number, content)
```

- **fatwas**: `title`, `content` (summary), `full_text`, `source_url` (unique), `pdf_url`, `issuing_agency` (→ authorities), `category_id` (→ categories), `date_issued`, `issued_year`, `gazette_no`, `status`
- A fatwa's **state** comes from its authority.
- **authorities**: `authority_name`, `contact_info`, `street`, `city`, `poscode`, `state`
- **preachers**: `preacher_name`, `certification_details`, `location`, `state`, `authority_id`

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

No front-end build step is needed: Bootstrap, Bootstrap Icons and Leaflet load from CDNs.

### Admin access

Visitors can browse and search everything without logging in. Creating, editing and deleting fatwas, preachers and mosques needs an admin login: click **Admin** in the navigation bar (or go to `/login`). There is no public registration; admin accounts come from the `ADMIN_*` values in `.env` (re-run `php artisan db:seed --class=AdminUserSeeder` after changing them).

## Importing fatwas

The importer reads each mufti website directly and saves fatwas to the database. It is safe to re-run: fatwas are matched by their source URL and updated instead of duplicated.

```bash
php artisan fatwa:scrape                  # all sources: wp, perak, selangor
php artisan fatwa:scrape selangor         # one source
php artisan fatwa:scrape --limit=5        # quick test, 5 per source
php artisan fatwa:scrape --no-text        # skip PDF downloads (fast, no full text)
php artisan fatwa:scrape --refresh-text   # re-extract text for every fatwa
```

| Source | Website | How it is read | Full text |
|---|---|---|---|
| `wp` | efatwa.muftiwp.gov.my | Paged HTML table (title, year, category, status, PDF) | Yes, from PDF |
| `perak` | mufti.perak.gov.my | Single HTML table of gazetted fatwas | Mostly no: most PDFs are scanned images |
| `selangor` | muftiselangor.gov.my | WordPress JSON API (gazetted-fatwa category) | Yes, from PDF |

Notes:

- The importer waits about a second between requests so it doesn't overload the government sites. A full run takes 15–20 minutes.
- PDF text is extracted with `pdftotext`. Set `PDFTOTEXT_PATH` in `.env` if it isn't on your PATH.
- Some sites send an incomplete TLS certificate chain. The missing intermediate certificates are kept in `resources/certs/` and trusted only by the importer.
- To add another state, write a class implementing `App\Scrapers\FatwaSource` and register it in `app/Console/Commands/ScrapeFatwas.php`.

The older Node.js/Puppeteer script in `scraper/` (from the FYP) is superseded by this command.

## Project structure

```
app/Http/Controllers/       Fatwa, Authority, Preacher, Mosque, Feedback
app/Http/Controllers/Auth/  LoginController (admin login)
app/Http/Controllers/Api/   FatwaApiController (import endpoint)
app/Console/Commands/        ScrapeFatwas (fatwa:scrape)
app/Scrapers/               One adapter per mufti website + FatwaImporter
app/Models/                 Eloquent models
database/migrations/        Schema
database/seeders/           AdminUserSeeder, AuthoritySeeder
resources/views/            Blade views per feature
routes/web.php, api.php     Routes
resources/certs/            Extra TLS intermediates for the importer
scraper/                    Original FYP Node.js scraper (superseded)
docs/                       FYP1 report, SRS, final report
```

## Documentation

The `docs/` folder contains the original academic documents:

- `FYP1 SW01082193.doc`: FYP1 proposal/report
- `SRS.docx`: Software Requirements Specification
- `SW01082193 FINAL REPORT.docx`: Final report

## Known gaps / ideas for future work

See [ROADMAP.md](ROADMAP.md) for the full plan of fixes and new features.

Things left unfinished at the end of the FYP, noted for anyone (or me) picking it up again:

- **Scanned documents have no text.** Most Perak PDFs and Selangor posts from 2023 and earlier are scanned images, so about 275 of 456 fatwas can only be found by title. OCR (e.g. Tesseract) would fix this.
- **Only three sources.** Pahang, Negeri Sembilan and other states still need adapters. The national portal (e-fatwa.gov.my / e-SMAF) was unreachable in Nov 2026.
- **Categories** come only from the Federal Territory site; Perak and Selangor fatwas are uncategorised.
- **No admin pages yet** for categories, religious authorities or reading submitted feedback (`CategoryController` is an empty stub).
- **Authority profile columns missing.** The authority page shows `sejarah`, `visi`, `misi`, `objektif` and `org_chart`, but no migration creates them (they existed only in the old WAMP database), so those sections are blank. Add a migration and fill them in.
- **Original data lost.** The FYP database lived in WampServer and was not exported. Fatwas are re-imported with `fatwa:scrape`, but preachers and mosques start empty and must be added by an admin.
- **Home page video.** `public/videos/fatwa-hero.mp4` from the FYP is no longer shown on the home page.
- **No tests** beyond the Laravel defaults.

## License

Personal academic project. No license has been chosen yet; all rights reserved by the author.
