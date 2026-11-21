# E-Fatwa (Fatwa Stop Centre)

A centralised web application for searching and referring to fatwas (Islamic rulings) issued by federal and state religious authorities across Malaysia, together with directories of religious authorities, certified preachers and mosques.

Built as my Final Year Project (FYP) at Universiti Tenaga Nasional (UNITEN), 2025.

## Why

Fatwas in Malaysia are published separately by each state and federal religious authority, so finding a ruling means searching many different websites. E-Fatwa brings them into a single, searchable repository and adds related information (who issued it, certified preachers, nearby mosques) in one place.

## Features

| Feature | Description |
|---|---|
| **Dashboard** | Landing page with an overview and navigation to all features (`/`). |
| **Explore Fatwa** | Browse, view, create, edit and delete fatwas (`/fatwas`). |
| **Search Fatwa** | Keyword search on title/content, filterable by category and issuing authority (`/search-fatwa`). |
| **Religious Authorities** | Profiles of federal and state religious bodies, with address and contact info (`/authorities`). |
| **Certified Preachers** | Directory of certified preachers linked to their authority (`/preachers`). |
| **Masjid Locator** | Mosque list plus an interactive map using Leaflet + OpenStreetMap (`/mosques`, `/mosques/map`). |
| **Categories** | Manage fatwa categories (`/categories`). |
| **Feedback** | Users submit feedback (name, email, phone, message) (`/feedbacks/create`). |
| **Import API** | `POST /api/fatwas/store` accepts JSON fatwas, used by the scraper. |

## Tech stack

- **Backend:** PHP 8.2+, Laravel 12, Laravel Sanctum
- **Frontend:** Blade templates, Bootstrap 5.3, custom CSS (`public/css`), Vite + Tailwind 4 toolchain
- **Maps:** Leaflet 1.9.4 with OpenStreetMap tiles
- **Database:** MySQL (developed against a database named `eFatwa_db`)
- **Scraper:** Node.js + Puppeteer + Axios

## Data model

```
Authority 1───* Fatwa *───1 Category
    │
    └──1───* Preacher

Mosque     (name, location, contact_info, latitude, longitude)
Feedback   (name, email, phone_number, content)
```

- **fatwas**: `title`, `content`, `issuing_agency` (→ authorities), `category_id` (→ categories), `date_issued`
- **authorities**: `authority_name`, `contact_info`, `street`, `city`, `poscode`, `state`
- **preachers**: `preacher_name`, `certification_details`, `location`, `state`, `authority_id`

## Getting started

Requirements: PHP 8.2+, Composer, Node.js 18+, MySQL.

```bash
git clone https://github.com/abdqayyum03/e-fatwa.git
cd e-fatwa

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` to point at MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eFatwa_db
DB_USERNAME=root
DB_PASSWORD=
```

Then create the tables, seed the authorities, and run:

```bash
php artisan migrate --seed
npm run build        # or: npm run dev
php artisan serve    # http://localhost:8000
```

## Importing fatwas (scraper)

`scraper/scrape_fatwas.py` scrapes the fatwa listing from the Federal Territory Mufti's e-Fatwa site and posts each entry to the local import API.

> **Note:** despite the `.py` extension, this file is actually **JavaScript** (Node.js + Puppeteer). Rename it to `scrape_fatwas.mjs` before running.

```bash
npm install puppeteer axios
php artisan serve                 # the API must be running on :8000
node scraper/scrape_fatwas.mjs
```

The ChromeDriver binaries that were in `scraper/` are not included in the repository. The Puppeteer script does not need them.

## Project structure

```
app/Http/Controllers/       Fatwa, Authority, Preacher, Mosque, Category, Feedback
app/Http/Controllers/Api/   FatwaApiController (scraper import endpoint)
app/Models/                 Eloquent models
database/migrations/        Schema
database/seeders/           AuthoritySeeder
resources/views/            Blade views per feature
routes/web.php, api.php     Routes
scraper/                    Fatwa scraper
docs/                       FYP1 report, SRS, final report
```

## Documentation

The `docs/` folder contains the original academic documents:

- `FYP1 SW01082193.doc`: FYP1 proposal/report
- `SRS.docx`: Software Requirements Specification
- `SW01082193 FINAL REPORT.docx`: Final report

## Known gaps / ideas for future work

Things left unfinished at the end of the FYP, noted for anyone (or me) picking it up again:

- **No authentication.** All create/edit/delete pages and the import API are public. Admin-only access should be added, for example with Laravel Breeze and middleware on write routes.
- **Preacher search is not routed.** `PreacherController@search` and `preachers/search.blade.php` exist, but there is no route for them.
- **Schema mismatch.** The import API treats `issuing_agency` as nullable, but the `fatwas` migration declares it `NOT NULL`, so scraped fatwas with no agency can fail to insert.
- **Duplicate route definitions** in `routes/web.php` (`authorities`, `feedbacks`, `fatwas.index`) should be cleaned up.
- **Scraped content** stores only the link to the source fatwa, not its full text.
- **No tests** beyond the Laravel defaults.
- Search does not yet filter by state or year as described in the SRS.

## License

Personal academic project. No license has been chosen yet; all rights reserved by the author.
