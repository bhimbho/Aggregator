<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_articles(): void
    {
        Article::factory()->count(25)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
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
            ]);
    }

    public function test_index_handles_pagination(): void
    {
        Article::factory()->count(25)->create();

        $response = $this->getJson('/api/articles?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 10,
                    'total' => 25,
                    'last_page' => 3,
                ],
            ]);
    }

    public function test_index_returns_empty_when_no_articles(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                ],
            ]);
    }

    public function test_index_handles_default_per_page(): void
    {
        Article::factory()->count(20)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 15,
                ],
            ]);
    }
}

