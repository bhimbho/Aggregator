<?php

namespace Tests\Unit;

use App\Enum\PlatformEnum;
use App\Service\NewsApiService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class NewsApiServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_transform_converts_response_to_article_array(): void
    {
        $service = new NewsApiService(app(\App\Action\LatestNewsArticle::class));

        $responseData = [
            'articles' => [
                [
                    'source' => ['name' => 'BBC'],
                    'author' => 'John Doe',
                    'title' => 'Test Article',
                    'description' => 'Test Description',
                    'url' => 'https://example.com/article',
                    'urlToImage' => 'https://example.com/image.jpg',
                    'publishedAt' => '2024-01-15T10:00:00Z',
                    'content' => 'Article content here',
                ],
            ],
        ];

        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('json')->andReturn($responseData);

        $articles = $service->transform($mockResponse);

        $this->assertIsArray($articles);
        $this->assertCount(1, $articles);
        $this->assertEquals('article', $articles[0]['type']);
        $this->assertEquals('BBC', $articles[0]['source']);
        $this->assertEquals('John Doe', $articles[0]['author']);
        $this->assertEquals('Test Article', $articles[0]['title']);
        $this->assertEquals('https://example.com/article', $articles[0]['url']);
        $this->assertEquals(PlatformEnum::NEWSAPI->value, $articles[0]['platform']);
    }

    public function test_transform_handles_null_author(): void
    {
        $service = new NewsApiService(app(\App\Action\LatestNewsArticle::class));

        $responseData = [
            'articles' => [
                [
                    'source' => ['name' => 'BBC'],
                    'author' => null,
                    'title' => 'Test Article',
                    'description' => 'Test Description',
                    'url' => 'https://example.com/article',
                    'urlToImage' => null,
                    'publishedAt' => '2024-01-15T10:00:00Z',
                    'content' => 'Article content',
                ],
            ],
        ];

        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('json')->andReturn($responseData);

        $articles = $service->transform($mockResponse);

        $this->assertNull($articles[0]['author']);
        $this->assertNull($articles[0]['urlToImage']);
    }

    public function test_transform_formats_published_at_correctly(): void
    {
        $service = new NewsApiService(app(\App\Action\LatestNewsArticle::class));

        $responseData = [
            'articles' => [
                [
                    'source' => ['name' => 'BBC'],
                    'title' => 'Test',
                    'description' => 'Test',
                    'url' => 'https://example.com',
                    'publishedAt' => '2024-01-15T10:30:45Z',
                    'content' => 'Content',
                ],
            ],
        ];

        $mockResponse = Mockery::mock(Response::class);
        $mockResponse->shouldReceive('json')->andReturn($responseData);

        $articles = $service->transform($mockResponse);

        $this->assertEquals('2024-01-15 10:30:45', $articles[0]['publishedAt']);
    }

    public function test_fetch_from_api_includes_keyword(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []], 200),
        ]);

        $service = new NewsApiService(app(\App\Action\LatestNewsArticle::class));
        $service->fetchFromApi('technology', null);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'newsapi.org/v2/everything') &&
                   isset($request->data()['q']) &&
                   $request->data()['q'] === 'technology';
        });
    }

    public function test_fetch_from_api_includes_date_when_provided(): void
    {
        Http::fake([
            'newsapi.org/*' => Http::response(['articles' => []], 200),
        ]);

        $service = new NewsApiService(app(\App\Action\LatestNewsArticle::class));
        $lastDate = '2024-01-15 10:00:00';
        $service->fetchFromApi('technology', $lastDate);

        Http::assertSent(function ($request) use ($lastDate) {
            $expectedDate = Carbon::parse($lastDate)->addMinutes(2)->toIso8601String();
            return isset($request->data()['from']) &&
                   $request->data()['from'] === $expectedDate;
        });
    }
}

