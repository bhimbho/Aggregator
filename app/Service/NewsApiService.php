<?php
namespace App\Service;

use App\Action\LatestNewsArticle;
use App\Action\ProcessArticle;
use App\Enum\PlatformEnum;
use App\Jobs\ProcessNewsArticlesJob;
use App\Models\Article;
use App\Service\Interface\NewsService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class NewsApiService implements NewsService 
{
    public function __construct(private LatestNewsArticle $latestNewsArticle)
    {}
    public function getNews(string $keyword): void
    {
        $lastArticle = $this->latestNewsArticle->execute(PlatformEnum::NEWSAPI);
        $response = $this->fetchFromApi($keyword, $lastArticle?->publishedAt);

        if ($response->successful()) {
            print "-- News from News API Fetched Successfully--\n";
            $articles = $this->transform($response);
            ProcessNewsArticlesJob::dispatch($articles);
        } else {
            print "-- News from News API Fetched Failed--\n";
        }
    }

    public function fetchFromApi(string $keyword, string|null $lastDate): Response
    {
        $parameters = [
            'q' => $keyword,
            'apiKey' => config('services.news_api.key'),
        ];
        if ($lastDate) {
            $parameters['from'] = Carbon::parse($lastDate)->addMinutes(2)->toIso8601String();
        }

        return Http::get('https://newsapi.org/v2/everything', $parameters);
    }

    public function transform(Response $response): array 
    {
        return collect($response->json()['articles'])
        ->map(fn ($article) => [
            'type' => 'article',
            'source' => $article['source']['name'],
            'author' => $article['author'] ?? null,
            'title' => $article['title'],
            'description' => $article['description'],
            'url' => $article['url'],
            'urlToImage' => $article['urlToImage'] ?? null,
            'publishedAt' => Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s'),
            'content' => $article['content'],
            'platform' => PlatformEnum::NEWSAPI->value,
        ])->toArray();
    }
}