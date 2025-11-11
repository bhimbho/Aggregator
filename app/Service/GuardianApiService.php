<?php
namespace App\Service;

use App\Action\LatestNewsArticle;
use App\Enum\PlatformEnum;
use App\Jobs\ProcessNewsArticlesJob;
use App\Service\Interface\NewsService;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GuardianApiService implements NewsService
{

    private const PAGE_SIZE = 50;

    public function __construct(private LatestNewsArticle $latestNewsArticle)
    {}

    public function getNews(string $keyword): void
    {
        $currentPage = 1;
        $totalPages = 1;
        $oldestArticle = $this->latestNewsArticle->execute(PlatformEnum::GUARDIAN);

        try {
            do {
                $response = $this->fetchFromApi($keyword, $currentPage, $oldestArticle?->publishedAt);
                if ($response->successful()) {
                    $responseData = $response->json()['response'];
                    $totalPages = $responseData['pages'];

                    print "-- Processing Guardian API page {$currentPage} of {$totalPages} --\n";

                    $articles = $this->transform($response);
                    ProcessNewsArticlesJob::dispatch($articles);
                    $currentPage++;
                }
            } while ($currentPage <= $totalPages);
        } catch (\Exception $e) {
            print "Error processing Guardian API: " . $e->getMessage() . "\n";
        }
        print "-- Completed fetching all Guardian API pages --\n";
    }

    private function fetchFromApi(string $keyword, int $page, string|null $oldestDate): Response
    {
        $parameters = [
            'api-key' => config('services.guardian.api_key'),
            'format' => 'json',
            'q' => $keyword,
            'page' => $page,
            'page-size' => self::PAGE_SIZE,
            'show-fields' => 'all'
        ];
        if ($oldestDate) {
            $parameters['from-date'] = Carbon::parse($oldestDate)->addMinutes(2)->toIso8601String();
        }
        return Http::get('https://content.guardianapis.com/search', $parameters);
    }

    public function transform(Response $response): array
    {
        return collect($response->json()['response']['results'])
        ->map(fn ($article) => [
            'author' => $article['fields']['byline'] ?? null,
            'title' => $article['webTitle'],
            'description' => $article['fields']['trailText'],
            'url' => $article['webUrl'],
            'publishedAt' => Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s'),
            'category' => $article['sectionName'],
            'content' => $article['fields']['body'],
            'platform' => PlatformEnum::GUARDIAN->value,
            'type' => $article['type'],
            'source' => $article['fields']['publication'],
        ])->toArray();
    }
}
