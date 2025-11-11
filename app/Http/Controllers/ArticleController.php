<?php

namespace App\Http\Controllers;

use App\Factory\ArticleQueryFactory;
use App\Http\Requests\ArticleRequest;

class ArticleController extends Controller
{
    public function __construct(private ArticleQueryFactory $queryFactory)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ArticleRequest $request)
    {
        try {
            $query = $this->queryFactory->makeQuery($request->validated());
            $articles = $query->paginate($request->input('per_page', 15));
            
            return $this->paginated($articles, 'Articles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverError('Failed to retrieve articles: ' . $e->getMessage());
        }
    }
}
