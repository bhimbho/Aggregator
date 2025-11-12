<?php

namespace Tests\Unit;

use App\Action\ProcessArticle;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProcessArticleTest extends TestCase
{
    use RefreshDatabase;

    private ProcessArticle $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new ProcessArticle();
    }

    public function test_execute_creates_articles_successfully(): void
    {
        $articles = [
            [
                'type' => 'article',
                'source' => 'Test Source',
                'title' => 'Test Article 1',
                'description' => 'Test Description',
                'url' => 'https://example.com/article1',
                'publishedAt' => '2024-01-15 10:00:00',
                'platform' => 'guardian',
            ],
            [
                'type' => 'article',
                'source' => 'Test Source 2',
                'title' => 'Test Article 2',
                'description' => 'Test Description 2',
                'url' => 'https://example.com/article2',
                'publishedAt' => '2024-01-16 10:00:00',
                'platform' => 'news api',
            ],
        ];

        $this->processor->execute($articles);

        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 1',
            'url' => 'https://example.com/article1',
        ]);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 2',
            'url' => 'https://example.com/article2',
        ]);
    }

    public function test_execute_handles_empty_array(): void
    {
        $this->processor->execute([]);

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_execute_rolls_back_on_error(): void
    {
        $articles = [
            [
                'type' => 'article',
                'title' => 'Valid Article',
                'url' => 'https://example.com/valid',
                'publishedAt' => '2024-01-15 10:00:00',
                'platform' => 'guardian',
            ],
            [
                'type' => 'article',
                'title' => null,
                'url' => null,
                'publishedAt' => 'invalid-date',
                'platform' => null,
            ],
        ];

        Log::shouldReceive('error')->once();

        try {
            $this->processor->execute($articles);
        } catch (\Exception $e) {
            // Expected to fail
        }

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_execute_processes_articles_in_transaction(): void
    {
        $articles = [
            [
                'type' => 'article',
                'source' => 'Test Source',
                'title' => 'Test Article',
                'url' => 'https://example.com/article',
                'publishedAt' => '2024-01-15 10:00:00',
                'platform' => 'guardian',
            ],
        ];

        $initialCount = Article::count();
        $this->processor->execute($articles);
        $finalCount = Article::count();

        $this->assertEquals($initialCount + 1, $finalCount);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
        ]);
    }
}

