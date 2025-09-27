# ChargeSpotter (Laravel + Vue/Inertia)

**Find the best locations for EV charging - fast, data-driven, demo-ready.**

ChargeSpotter is a map-first tool that scores city blocks by **expected EV charging demand** using simple, public, or simulated data layers (Points-of-Interest, population density, parking supply, and a traffic proxy). It outputs a **heatmap** and a **Top-10 list** with reasons for each suggestion. The MVP is designed to be built quickly while staying cleanly extensible.

---

## Table of Contents

1. [What it does](#what-it-does)
2. [Key features (MVP)](#key-features-mvp)
3. [Architecture](#architecture)
4. [Data model](#data-model)
5. [Scoring model](#scoring-model)
6. [Tech stack](#tech-stack)
7. [Quick start](#quick-start)
8. [Project setup (step-by-step)](#project-setup-step-by-step)
9. [Data import & demo data](#data-import--demo-data)
10. [Scoring workflow](#scoring-workflow)
11. [API design](#api-design)
12. [Frontend (Vue + Leaflet)](#frontend-vue--leaflet)
13. [Configuration](#configuration)
14. [Testing (Pest)](#testing-pest)
15. [Deployment notes](#deployment-notes)
16. [Roadmap](#roadmap)

---

## What it does

* Aggregates simple spatial signals into a **single demand score** per grid cell.
* Renders a **heatmap** and a **ranked list** of recommended sites.
* Lets users tweak **weights** (e.g., POI vs. population) and instantly re-rank.
* Exports results as **CSV/GeoJSON**.

Use it to create a convincing demo for city stakeholders, CPOs, or utility partners in hours, not weeks.

---

## Key features (MVP)

* **Scoring layers:** Population proxy, POI density, parking supply, traffic proxy (optional).
* **Grid tiling:** 250–500 m tiles (configurable).
* **Pre-normalization:** Each layer normalized to 0..1 per grid cell for instant reweighting.
* **Endpoints:**

  * Grid cells within a bounding box (for heatmap)
  * Top-N candidate locations (with explanation)
  * CSV/GeoJSON export
* **UI:** Leaflet map + sliders for weights + Top-10 table.

---

## Architecture

```
Laravel (API + services)
├── Domain
│   ├── Importers (CSV/GeoJSON parsers)
│   ├── GridBuilder (makes cells from bbox + size)
│   ├── ScoringService (normalization + totals)
│   └── Query services (bbox queries, top-N)
├── HTTP
│   ├── API Controllers (GridCell, Candidates, Export)
│   └── Inertia Controller (Dashboard page)
└── Storage
    └── seed/ (sample CSV/GeoJSON)

Frontend (Inertia + Vue 3)
├── Pages/Dashboard.vue (map, sliders, table)
└── Components/MapHeatmap.vue (Leaflet + heat plugin)
```

---

## Data model

> Keep it **simple** for the MVP (no PostGIS required). Use lat/lng centers and a JSON bbox per grid cell. You can add PostGIS later.

**Tables**

* `grid_cells`

  * `id` (PK)
  * `lat` (double), `lng` (double) - center of cell
  * `bbox_json` (json) - `[minLng, minLat, maxLng, maxLat]`
  * **Layer values (raw or aggregated):**

    * `population_z` (float 0..1)
    * `poi_z` (float 0..1)
    * `parking_z` (float 0..1)
    * `traffic_z` (float 0..1, nullable)
  * **Computed (cached, optional):**

    * `total_score_cached` (float, nullable)
    * `details_json` (json, nullable) - e.g., counters/reasons
  * `created_at`, `updated_at`

* `poi_locations`

  * `id`, `name`, `category` (enum/string), `lat`, `lng`

* `parking_locations`

  * `id`, `name`, `capacity` (int, nullable), `lat`, `lng`

* `population_cells`

  * `id`, `lat`, `lng`, `density` (float) - coarse raster proxy

> You can start with **just `grid_cells`** if you pre-aggregate z-scores during import.

---

## Scoring model

Base formula (configurable weights):

```
total_score = w_pop * population_z
            + w_poi * poi_z
            + w_parking * parking_z
            + w_traffic * traffic_z
```

* Each layer value is **normalized 0..1** across all grid cells (`min-max` or `z-score` → rescaled).
* Weights are **non-negative** and typically sum to 1, but do not need to.
* The UI sends weights as query params; backend recombines **without recomputing z-scores**.

**Top-N rationale** (stored in `details_json`):

* Store contributing factors per cell, e.g., nearest POI types, parking count within radius, population density bin.

---

## Tech stack

* **Backend:** PHP 8.2+, Laravel 12, Composer
* **Frontend:** Vue 3 + Inertia.js, Vite
* **Map:** Leaflet + `leaflet.heat`
* **DB:** mysql (recommended) or SQLite (for quick local demo)
* **Testing:** Pest

---

## Quick start

```bash
# prerequisites: PHP 8.2+, Composer, Node 18+, npm, mysql

# from your existing Laravel project root
cp .env.example .env
composer install
php artisan key:generate

npm install
npm run dev  # or: npm run build
```

Set DB credentials in `.env` (mysql)

Run initial migration seed (we’ll add migrations shortly):

```bash
php artisan migrate
```

---

## Project setup (step-by-step)

### 1) Install Inertia + Vue + Leaflet

```bash
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
# Register \Inertia\Middleware in app/Http/Kernel.php (web group)

npm i vue @inertiajs/vue3
npm i leaflet leaflet.heat axios
```

Update `resources/js/app.js`:

```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: name => import(`./Pages/${name}.vue`),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
```

Update `resources/views/app.blade.php` (if not present, create minimal Inertia layout):

```php
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>ChargeSpotter</title>
  @vite('resources/js/app.js')
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <style>html,body,#app{height:100%} .map{height:calc(100vh - 140px);}</style>
</head>
<body>
  @inertia
</body>
</html>
```

### 2) Create config file for weights and grid

```bash
php artisan make:config chargespotter
```

Create `config/chargespotter.php`:

```php
<?php
return [
    'default_weights' => [
        'population' => 0.4,
        'poi'        => 0.3,
        'parking'    => 0.2,
        'traffic'    => 0.1,
    ],
    'grid' => [
        'cell_size_m' => 500, // 250..500 recommended
    ],
    'export' => [
        'max_candidates' => 50,
    ],
];
```

### 3) Migrations & Models

```bash
php artisan make:model GridCell -m
php artisan make:model PoiLocation -m
php artisan make:model ParkingLocation -m
php artisan make:model PopulationCell -m
```

Fill `database/migrations/xxxx_create_grid_cells_table.php`:

```php
Schema::create('grid_cells', function (Blueprint $t) {
    $t->id();
    $t->double('lat');
    $t->double('lng');
    $t->json('bbox_json');
    $t->float('population_z')->default(0);
    $t->float('poi_z')->default(0);
    $t->float('parking_z')->default(0);
    $t->float('traffic_z')->nullable();
    $t->float('total_score_cached')->nullable();
    $t->json('details_json')->nullable();
    $t->timestamps();
});
```

Create simple columns for the other tables (`poi_locations`, `parking_locations`, `population_cells`) with `lat`, `lng`, and relevant attributes (see Data model).

Run:

```bash
php artisan migrate
```

### 4) Services & Commands

```bash
php artisan make:service ScoringService
php artisan make:service GridBuilder
php artisan make:service ImportService

php artisan make:command ImportDemoData
php artisan make:command BuildGrid
php artisan make:command ComputeScores
```

> If `make:service` doesn’t exist in your skeleton, create classes under `app/Services/` manually.

**`app/Services/GridBuilder.php` (skeleton):**

```php
<?php
namespace App\Services;

class GridBuilder {
    public function buildGridFromBbox(array $bbox, int $cellSizeM): array {
        // bbox = [minLng, minLat, maxLng, maxLat]
        // 1) convert meters to degrees approx. (simple equirectangular approx)
        // 2) iterate lon/lat steps, compute center + bbox per cell
        // 3) return list of ['lat'=>..., 'lng'=>..., 'bbox_json'=>[...]]
    }
}
```

**`app/Services/ScoringService.php` (skeleton):**

```php
<?php
namespace App\Services;

use App\Models\GridCell;

class ScoringService {
    public function normalizeLayers(): void {
        // min-max normalize population_z, poi_z, parking_z, traffic_z across grid_cells
        // read raw stats if you store raw first, then update *_z columns 0..1
    }
    public function computeTotals(array $weights): void {
        // total_score = w_pop*population_z + w_poi*poi_z + w_parking*parking_z + w_traffic*traffic_z
        // update total_score_cached (optional)
    }
    public function topN(int $n, array $weights): array {
        // SELECT with computed expression or reuse cached total
    }
}
```

**`app/Services/ImportService.php` (skeleton):**

```php
<?php
namespace App\Services;

class ImportService {
    public function importPoiCsv(string $path): int {}
    public function importParkingCsv(string $path): int {}
    public function importPopulationCsv(string $path): int {}
    public function assignLayerValuesToGrid(): void {
        // For each grid cell: compute counts/densities within radius/bbox
        // Then set raw values and later normalize
    }
}
```

**Artisan commands**: wire these services with CLI flags for bbox, grid size, file paths.

### 5) API Controllers & Routes

```bash
php artisan make:controller Api/GridCellController
php artisan make:controller Api/CandidatesController
php artisan make:controller Api/ExportController
php artisan make:controller DashboardController
```

`routes/web.php`:

```php
Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
```

`routes/api.php`:

```php
Route::get('/grid-cells', [GridCellController::class, 'index']);
Route::get('/candidates', [CandidatesController::class, 'index']);
Route::get('/export/csv', [ExportController::class, 'csv']);
Route::get('/export/geojson', [ExportController::class, 'geojson']);
```

**GridCellController@index (outline):**

* Accept `bbox` as `minLng,minLat,maxLng,maxLat`, and weights (`w_pop`, `w_poi`, `w_parking`, `w_traffic`).
* Query `grid_cells` overlapping bbox (compare cell `bbox_json` with bbox; simple numeric range checks).
* If weights provided, compute `total_score` inline (do **not** persist).
* Return array of `{ lat, lng, bbox, population_z, poi_z, parking_z, traffic_z, total_score }`.

**CandidatesController@index (outline):**

* Accept same weight params + `limit` (default 10).
* Return sorted cells (highest totals first) with `details_json` reasons.

**ExportController**:

* Generate CSV / GeoJSON from query + weights.

### 6) Inertia Dashboard

* Create `resources/js/Pages/Dashboard.vue`
* Create `resources/js/Components/MapHeatmap.vue`

`DashboardController@index`:

```php
return \Inertia\Inertia::render('Dashboard', [
  'defaultWeights' => config('chargespotter.default_weights'),
  'defaultBbox' => [21.5, 63.05, 21.75, 63.15], // example Vaasa bbox; adjust
]);
```

---

## Data import & demo data

### Folder structure

```
storage/app/seed/
  poi.csv
  parking.csv
  population.csv
```

### CSV formats (simple)

* `poi.csv`: `name,category,lat,lng`
* `parking.csv`: `name,capacity,lat,lng`
* `population.csv`: `lat,lng,density`

> For a quick demo, you can hand-craft 30–100 rows around the city center.

### Commands (example flow)

```bash
# 1) Build grid for a bbox (Vaasa example coordinates; replace as needed)
php artisan chargespotter:build-grid --bbox="21.5,63.05,21.75,63.15" --cell=500

# 2) Import layers
php artisan chargespotter:import-poi storage/app/seed/poi.csv
php artisan chargespotter:import-parking storage/app/seed/parking.csv
php artisan chargespotter:import-population storage/app/seed/population.csv

# 3) Aggregate layer values into grid + normalize
php artisan chargespotter:assign-layers
php artisan chargespotter:normalize
php artisan chargespotter:compute --weights="0.4,0.3,0.2,0.1"
```

**Assign-layers heuristic (fast)**
For each grid cell:

* **POI density:** count POIs within the cell bbox (or radius 250 m).
* **Parking:** sum `capacity` (or count if unknown) within bbox/radius.
* **Population:** average `density` of population points falling inside.
* **Traffic (optional):** if you have none, set constant or derive from major-road proximity (count of “road=primary/secondary” POIs if available).

Then **normalize** each column to 0..1 across all cells and persist in `*_z`.

---

## Scoring workflow

**On import time:**

1. Build grid cells for your bbox.
2. Load layer CSVs into tables.
3. Compute per-cell raw values and normalize → store as `*_z` columns.

**On request time (fast):**

* Take weight params from UI, recompute `total_score` as a SQL expression:

  ```sql
  (w_pop * population_z) + (w_poi * poi_z) + (w_parking * parking_z) + (w_traffic * COALESCE(traffic_z,0))
  ```
* Order by score; limit N for candidates, or return all cells for heatmap.

---

## API design

### `GET /api/grid-cells`

**Query params:**

* `bbox=minLng,minLat,maxLng,maxLat` (required)
* `w_pop, w_poi, w_parking, w_traffic` (optional; defaults from config)

**Response:**

```json
{
  "cells": [
    {
      "lat": 63.096,
      "lng": 21.615,
      "bbox": [21.61,63.094,21.62,63.098],
      "population_z": 0.83,
      "poi_z": 0.60,
      "parking_z": 0.45,
      "traffic_z": 0.10,
      "total_score": 0.68
    }
  ]
}
```

### `GET /api/candidates`

**Query params:** same as above + `limit=10`
**Response:** cells sorted by `total_score` with a short `reason` string (built from `details_json`).

### `GET /api/export/csv` and `/api/export/geojson`

* Returns the current selection with scores.

---

## Frontend (Vue + Leaflet)

### Minimal Dashboard.vue (outline)

* Bbox selector (hidden or fixed for demo)
* Weight sliders (`population`, `poi`, `parking`, `traffic`)
* MapHeatmap (fetches `/api/grid-cells`)
* Candidates table (fetches `/api/candidates`)
* Export buttons (CSV/GeoJSON)

**Weight slider tip:** Normalize front-end slider values to sum ~1 (or send as raw and let backend use them directly).

### MapHeatmap.vue (outline)

* Initialize Leaflet map with OSM tiles.
* Use `leaflet.heat` to draw a heat layer from `[lat, lng, intensity]` triplets, where `intensity = total_score`.
* On weight change or map move, refetch cells for the new bbox.

---

## Configuration

`.env` (key settings):

```
APP_NAME=ChargeSpotter
DB_CONNECTION=pgsql  # or sqlite for quick demo
# PG creds ...
```

`config/chargespotter.php` - tune defaults:

* `default_weights`
* `grid.cell_size_m`
* `export.max_candidates`

**Constants for categories** (optional): define allowed POI categories in `config/poi.php` to keep data clean.

---

## Testing (Pest)

Install Pest (if not already in your project):

```bash
composer require pestphp/pest --dev
php artisan pest:install
```

### Suggested tests

* **Unit: GridBuilder** - given bbox & cell size, returns expected number of cells and correct bbox geometry.
* **Unit: ScoringService** - normalization clamps to 0..1; totals match weights.
* **Feature: /api/candidates** - returns sorted list; limit respected; reasons present.
* **Feature: /api/grid-cells** - bbox filter works; returns heat payload shape.

**Example (outline)**

```php
it('computes normalized scores', function () {
    // seed few grid_cells with raw values
    // run ScoringService::normalizeLayers()
    // assert values between 0 and 1 and ordering preserved
});
```

---

## Deployment notes

* Use `php artisan config:cache`, `route:cache`.
* For production, run `npm run build` and serve compiled assets.
* If exposing publicly, add **rate limiting** to API and **CORS** rules.
* Consider mysql; later you can migrate to **PostGIS** for true geometry queries.

---

## Roadmap

* **Add network constraints:** distance to feeders/transformers, interconnection capacity.
* **Time-of-day modeling:** dynamic demand by hour/day.
* **Economic layer:** CAPEX/OPEX, tariff assumptions → payback calculator.
* **Admin UI:** upload new layers (CSV/GeoJSON), re-tile grid.
* **PostGIS:** real spatial filters, isochrones, nearest-neighbor on road graph.
* **Auth & multi-project:** save scenarios per city/customer.

---

## Implementation checklist (copy/paste friendly)

**Backend**

1. Config files: `chargespotter.php` (+ optional `poi.php`).
2. Migrations: `grid_cells`, `poi_locations`, `parking_locations`, `population_cells`.
3. Services: `GridBuilder`, `ImportService`, `ScoringService`.
4. Commands: `chargespotter:build-grid`, `:import-poi`, `:import-parking`, `:import-population`, `:assign-layers`, `:normalize`, `:compute`.
5. Controllers: `Api/GridCellController`, `Api/CandidatesController`, `Api/ExportController`, `DashboardController`.
6. Routes: `GET /` (Inertia), `/api/grid-cells`, `/api/candidates`, `/api/export/{csv|geojson}`.

**Frontend**

1. Inertia/Vue setup in `resources/js/app.js`.
2. `Pages/Dashboard.vue` with sliders + table + export buttons.
3. `Components/MapHeatmap.vue` using Leaflet + `leaflet.heat`.
4. Axios calls to API with bbox + weights.

**Data**

1. Place CSVs under `storage/app/seed/`.
2. Run CLI: build grid → import → assign layers → normalize → (optional) compute default totals.
3. Verify `/api/candidates` returns top sites.

---

### License

MIT (or your preferred license).

---

**Tip:** Start with a small bbox (city center), a 500 m grid, and ~50–150 POIs. You’ll get a smooth heatmap and a convincing Top-10 in under an hour of data prep.
