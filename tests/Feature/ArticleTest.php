<?php

namespace Tests\Feature;

use App\Enum\PlatformEnum;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the complete API integration with all filters and sorting.
     * Individual filter logic is tested in ArticleQueryFactoryTest (unit tests).
     */
    public function test_api_endpoint_returns_paginated_articles_with_filters(): void
    {
        Article::factory()->create([
            'platform' => PlatformEnum::GUARDIAN->value,
            'category' => 'technology',
            'source' => 'BBC',
            'publishedAt' => '2024-01-15 10:00:00',
        ]);
        Article::factory()->create([
            'platform' => PlatformEnum::NEWSAPI->value,
            'category' => 'business',
            'source' => 'CNN',
            'publishedAt' => '2024-01-20 10:00:00',
        ]);

        $response = $this->getJson('/api/articles?platform=guardian&category=technology&from_date=2024-01-14&to_date=2024-01-16&sort_by=publishedAt&sort_direction=asc&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'source',
                        'author',
                        'title',
                        'description',
                        'url',
                        'urlToImage',
                        'content',
                        'publishedAt',
                        'category',
                        'platform',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
            ])
            ->assertJson([
                'success' => true,
                'meta' => [
                    'per_page' => 10,
                    'total' => 1,
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(PlatformEnum::GUARDIAN->value, $data[0]['platform']);
        $this->assertEquals('technology', $data[0]['category']);
    }

    public function test_api_endpoint_handles_validation_errors(): void
    {
        $response = $this->getJson('/api/articles?search=a&per_page=100&to_date=2024-01-01&from_date=2024-01-31');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }
}
