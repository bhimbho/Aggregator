<?php

namespace App\Factory;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ArticleQueryFactory
{
    private Builder $query;

    public function __construct()
    {
        $this->query = Article::query();
    }

    public function makeQuery(array $filters): Builder
    {
        return $this
            ->applySearchFilter($filters['search'] ?? null)
            ->applySourceFilter($filters['source'] ?? null)
            ->applyPlatformFilter($filters['platform'] ?? null)
            ->applyCategoryFilter($filters['category'] ?? null)
            ->applyDateRangeFilter(
                isset($filters['from_date']) ? Carbon::parse($filters['from_date'])->startOfDay() : null,
                isset($filters['to_date']) ? Carbon::parse($filters['to_date'])->endOfDay() : null
            )
            ->applySorting($filters['sort_by'] ?? 'publishedAt', $filters['sort_direction'] ?? 'desc')
            ->getQuery();
    }

    private function applySearchFilter(?string $search): self
    {
        if ($search) {
            $this->query->whereFullText(['title', 'description', 'content'], $search);
        }
        return $this;
    }

    private function applySourceFilter(?string $source): self
    {
        if ($source) {
            $this->query->where('source', $source);
        }
        return $this;
    }

    private function applyPlatformFilter(?string $platform): self
    {
        if ($platform) {
            $this->query->where('platform', $platform);
        }
        return $this;
    }

    private function applyCategoryFilter(?string $category): self
    {
        if ($category) {
            $this->query->where('category', $category);
        }
        return $this;
    }

    private function applyDateRangeFilter(?Carbon $fromDate, ?Carbon $toDate): self
    {
        if ($fromDate) {
            $this->query->where('publishedAt', '>=', $fromDate);
        }
        if ($toDate) {
            $this->query->where('publishedAt', '<=', $toDate);
        }
        return $this;
    }

    private function applySorting(string $sortBy, string $direction): self
    {
        $this->query->orderBy($sortBy, $direction);
        return $this;
    }

    private function getQuery(): Builder
    {
        return $this->query;
    }
}