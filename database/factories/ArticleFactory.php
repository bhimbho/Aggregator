<?php

namespace Database\Factories;

use App\Enum\PlatformEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'article',
            'source' => $this->faker->name,
            'author' => $this->faker->name,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'url' => $this->faker->url,
            'urlToImage' => $this->faker->imageUrl,
            'content' => $this->faker->paragraph,
            'category' => $this->faker->word,
            'publishedAt' => $this->faker->dateTime,
            'platform' => $this->faker->randomElement(array_column(PlatformEnum::cases(), 'value')),
        ];
    }
}
