<?php

namespace Tests\Feature;

use App\Http\Requests\ArticleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ArticleRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_passes_with_valid_data(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'search' => 'technology',
            'source' => 'BBC',
            'category' => 'tech',
            'from_date' => '2024-01-01',
            'to_date' => '2024-01-31',
            'sort_by' => 'publishedAt',
            'sort_direction' => 'desc',
            'platform' => 'guardian',
            'per_page' => 20,
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_search_is_too_short(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'search' => 'a',
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('search', $validator->errors()->toArray());
    }

    public function test_validation_fails_when_sort_by_is_invalid(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sort_by' => 'invalid_field',
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_sort_direction_is_invalid(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'sort_direction' => 'invalid',
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_platform_is_invalid(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'platform' => 'invalid platform',
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_to_date_is_before_from_date(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'from_date' => '2024-01-31',
            'to_date' => '2024-01-01',
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('to_date', $validator->errors()->toArray());
    }

    public function test_validation_passes_when_to_date_equals_from_date(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'from_date' => '2024-01-15',
            'to_date' => '2024-01-15',
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_per_page_exceeds_maximum(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'per_page' => 100,
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_fails_when_per_page_is_below_minimum(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'per_page' => 0,
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    public function test_validation_passes_with_nullable_fields(): void
    {
        $request = new ArticleRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_custom_error_messages_are_returned(): void
    {
        $request = new ArticleRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('search.min', $messages);
        $this->assertArrayHasKey('to_date.after_or_equal', $messages);
    }
}

