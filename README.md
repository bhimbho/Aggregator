# News Aggregator

A Laravel-based news aggregation system that fetches articles from multiple news sources (NewsAPI, The Guardian, and New York Times) and provides a unified API for searching and filtering articles.

## Features

- 🔄 **Multi-source aggregation**: Fetches news from NewsAPI, The Guardian, and New York Times
- 🔍 **Advanced search**: Full-text search across article titles, descriptions, and content
- 📊 **Flexible filtering**: Filter by platform, category, source, and date range
- 📄 **Pagination**: Efficient pagination with customizable page size
- 🎯 **Smart sorting**: Sort by publication date or title, ascending or descending
- ⚡ **Optimized performance**: Database indexes and bulk operations for handling large datasets
- 🧪 **Comprehensive testing**: Unit and feature tests covering core functionality

## Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: MySQL (with full-text search support)
- **Queue**: Laravel Queue for asynchronous article processing
- **Containerization**: Docker with Laravel Sail

## Prerequisites

- PHP 8.2 or higher
- Composer
- Docker and Docker Compose (for Sail)
- API keys for:
  - [NewsAPI](https://newsapi.org/)
  - [The Guardian](https://open-platform.theguardian.com/)
  - [New York Times](https://developer.nytimes.com/)

## Installation

### Step 1: Clone the repository

```bash
git clone https://github.com/bhimbho/news-aggregator.git
cd news-aggregator
```

### Step 2: Install dependencies

If you're using Docker (recommended):

```bash
./vendor/bin/sail build
./vendor/bin/sail up -d
```

Or without Docker:

```bash
composer install
```

### Step 3: Environment setup

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
./vendor/bin/sail artisan key:generate
# or
php artisan key:generate
```

### Step 4: Configure API keys

Add your API keys to the `.env` file:

```env
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NEW_YORK_TIMES_KEY=your_nytimes_key_here
```

### Step 5: Database setup

Run the migrations:

```bash
./vendor/bin/sail artisan migrate
# or
php artisan migrate
```

### Step 6: Start the scheduler

The scheduler fetches news from all sources. For testing, it's configured to run every 5 seconds. You can adjust this in `app/Console/Kernel.php`:

```bash
./vendor/bin/sail artisan schedule:run
# or
php artisan schedule:run
```

**Note**: In production, add this to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 7: Start the queue worker

Process articles asynchronously:

```bash
./vendor/bin/sail artisan queue:work
# or
php artisan queue:work
```

## API Documentation

### Get Articles

Retrieve a paginated list of articles with optional filtering and sorting.

**Endpoint:** `GET /api/articles`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search query (minimum 2 characters). Searches across title, description, and content using full-text search. |
| `platform` | string | No | Filter by platform. Options: `news api`, `guardian`, `new york times` |
| `category` | string | No | Filter by article category |
| `source` | string | No | Filter by article source |
| `from_date` | date | No | Filter articles published on or after this date (format: YYYY-MM-DD) |
| `to_date` | date | No | Filter articles published on or before this date (format: YYYY-MM-DD). Must be after or equal to `from_date`. |
| `sort_by` | string | No | Field to sort by. Options: `publishedAt`, `title` (default: `publishedAt`) |
| `sort_direction` | string | No | Sort direction. Options: `asc`, `desc` (default: `desc`) |
| `per_page` | integer | No | Number of articles per page (1-50, default: 15) |

**Example Request:**

```bash
GET /api/articles?platform=guardian&category=technology&from_date=2024-01-01&sort_by=publishedAt&sort_direction=desc&per_page=20
```

## Testing

Run the test suite:

```bash
./vendor/bin/sail artisan test
# or
php artisan test
```

T
### Code style

The project uses Laravel Pint for code formatting:

```bash
./vendor/bin/sail pint
# or
./vendor/bin/pint
```

