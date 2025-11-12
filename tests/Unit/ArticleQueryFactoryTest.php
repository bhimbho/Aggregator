<?php

namespace Tests\Unit;

use App\Factory\ArticleQueryFactory;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleQueryFactoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleQueryFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ArticleQueryFactory();
    }

    public function test_make_query_without_filters_returns_all_articles(): void
    {
        Article::factory()->count(5)->create();

        $query = $this->factory->makeQuery([]);
        $results = $query->get();

        $this->assertCount(5, $results);
    }

    public function test_applies_platform_filter(): void
    {
        Article::factory()->create(['platform' => 'guardian']);
        Article::factory()->create(['platform' => 'news api']);

        $query = $this->factory->makeQuery(['platform' => 'guardian']);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('guardian', $results->first()->platform);
    }

    public function test_applies_category_filter(): void
    {
        Article::factory()->create(['category' => 'technology']);
        Article::factory()->create(['category' => 'business']);

        $query = $this->factory->makeQuery(['category' => 'technology']);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('technology', $results->first()->category);
    }

    public function test_applies_source_filter(): void
    {
        Article::factory()->create(['source' => 'BBC']);
        Article::factory()->create(['source' => 'CNN']);

        $query = $this->factory->makeQuery(['source' => 'BBC']);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('BBC', $results->first()->source);
    }

    public function test_applies_date_range_filter(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-20 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-02-01 10:00:00']);

        $query = $this->factory->makeQuery([
            'from_date' => '2024-01-15',
            'to_date' => '2024-01-20',
        ]);
        $results = $query->get();

        $this->assertCount(2, $results);
    }

    public function test_applies_from_date_filter_only(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-10 10:00:00']);

        $query = $this->factory->makeQuery(['from_date' => '2024-01-12']);
        $results = $query->get();

        $this->assertCount(1, $results);
    }

    public function test_applies_to_date_filter_only(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-20 10:00:00']);

        $query = $this->factory->makeQuery(['to_date' => '2024-01-18']);
        $results = $query->get();

        $this->assertCount(1, $results);
    }

    public function test_applies_sorting_by_published_at_desc(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-20 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-10 10:00:00']);

        $query = $this->factory->makeQuery([
            'sort_by' => 'publishedAt',
            'sort_direction' => 'desc',
        ]);
        $results = $query->get();

        $this->assertEquals('2024-01-20 10:00:00', $results->first()->publishedAt->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-10 10:00:00', $results->last()->publishedAt->format('Y-m-d H:i:s'));
    }

    public function test_applies_sorting_by_published_at_asc(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-20 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-10 10:00:00']);

        $query = $this->factory->makeQuery([
            'sort_by' => 'publishedAt',
            'sort_direction' => 'asc',
        ]);
        $results = $query->get();

        $this->assertEquals('2024-01-10 10:00:00', $results->first()->publishedAt->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-20 10:00:00', $results->last()->publishedAt->format('Y-m-d H:i:s'));
    }

    public function test_applies_sorting_by_title(): void
    {
        Article::factory()->create(['title' => 'Zebra Article']);
        Article::factory()->create(['title' => 'Apple Article']);
        Article::factory()->create(['title' => 'Banana Article']);

        $query = $this->factory->makeQuery([
            'sort_by' => 'title',
            'sort_direction' => 'asc',
        ]);
        $results = $query->get();

        $this->assertEquals('Apple Article', $results->first()->title);
        $this->assertEquals('Zebra Article', $results->last()->title);
    }

    public function test_applies_multiple_filters_combined(): void
    {
        Article::factory()->create([
            'platform' => 'guardian',
            'category' => 'technology',
            'publishedAt' => '2024-01-15 10:00:00',
        ]);
        Article::factory()->create([
            'platform' => 'guardian',
            'category' => 'business',
            'publishedAt' => '2024-01-15 10:00:00',
        ]);
        Article::factory()->create([
            'platform' => 'news api',
            'category' => 'technology',
            'publishedAt' => '2024-01-15 10:00:00',
        ]);

        $query = $this->factory->makeQuery([
            'platform' => 'guardian',
            'category' => 'technology',
            'from_date' => '2024-01-14',
            'to_date' => '2024-01-16',
        ]);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('guardian', $results->first()->platform);
        $this->assertEquals('technology', $results->first()->category);
    }

    public function test_default_sorting_is_published_at_desc(): void
    {
        Article::factory()->create(['publishedAt' => '2024-01-15 10:00:00']);
        Article::factory()->create(['publishedAt' => '2024-01-20 10:00:00']);

        $query = $this->factory->makeQuery([]);
        $results = $query->get();

        $this->assertEquals('2024-01-20 10:00:00', $results->first()->publishedAt->format('Y-m-d H:i:s'));
    }
}

