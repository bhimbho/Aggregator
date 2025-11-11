<?php
namespace App\Service;

use App\Action\LatestNewsArticle;
use App\Action\ProcessArticle;
use App\Enum\NewsType;
use App\Enum\PlatformEnum;
use App\Jobs\ProcessNewsArticlesJob;
use App\Service\Interface\NewsService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class NYTimesApiService implements NewsService
{

    public function __construct(private LatestNewsArticle $latestNewsArticle)
    {}
    public function getNews(string $keyword): void
    {
        $lastArticle = $this->latestNewsArticle->execute(PlatformEnum::NYT);
        $response = $this->fetchFromApi($keyword, $lastArticle?->publishedAt);

        if ($response->successful()) {
            print "-- News from New York Times API Fetched Successfully--\n";
            $articles = $this->transform($response);
            ProcessNewsArticlesJob::dispatch($articles);
        }
    }

    public function fetchFromApi(string $keyword, string|null $lastDate): Response
    {
        $parameters = [
            'api-key' => config('services.nytimes.api_key'),
            'q' => $keyword,
            'sort' => 'oldest',
        ];
        
        if ($lastDate) {
            $parameters['begin_date'] = Carbon::parse($lastDate)->format('Ymd');
        }

        return Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', $parameters);
    }

    public function transform(Response $response): array
    {
        return collect($response->json()['response']['docs'])
        ->map(fn ($article) => [
            'platform' => PlatformEnum::NYT->value,
            'type' => $article['document_type'] ?? 'article',
            'source' => $article['source'],
            'author' => $article['byline']['original'] ?? null,
            'title' => $article['abstract'],
            'description' => $article['snippet'],
            'url' => $article['web_url'],
            'urlToImage' => $article['multimedia'][0]['url'] ?? null,
            'publishedAt' => Carbon::parse($article['pub_date'])->format('Y-m-d H:i:s'),
            'content' => $article['headline']["main"],
            'category' => $article['section_name'] ?? null,
        ])->toArray();
    }
}
