<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Page;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;

/**
 * SEO metadata for one page: title, description, canonical URL, Open Graph image and JSON-LD.
 * Values come from the admin (Page / Course SEO fields) with sensible fallbacks.
 */
final class Seo
{
    /**
     * @param  list<array<string, mixed>>  $jsonLd
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $image = null,
        public ?string $canonical = null,
        public string $type = 'website',
        public array $jsonLd = [],
        public bool $noIndex = false,
    ) {}

    public static function forPage(Page $page): self
    {
        return new self(
            title: $page->seo_title ?: $page->title,
            description: $page->seo_description ?: __('site.default_description'),
            image: $page->ogImageUrl(),
        );
    }

    public static function forCourse(Course $course): self
    {
        $description = $course->seo_description ?: Str::limit(strip_tags($course->short_description), 155);

        return new self(
            title: $course->seo_title ?: $course->name,
            description: $description,
            image: $course->imageUrl(),
            type: 'article',
            jsonLd: [[
                '@context' => 'https://schema.org',
                '@type' => 'Course',
                'name' => $course->name,
                'description' => $description,
                'inLanguage' => 'sk',
                'provider' => ['@type' => 'Organization', 'name' => config('app.name'), 'url' => route('home')],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => number_format((float) $course->price, 2, '.', ''),
                    'priceCurrency' => 'EUR',
                    'category' => 'Paid',
                ],
                'hasCourseInstance' => [
                    '@type' => 'CourseInstance',
                    'courseMode' => 'Onsite',
                    'location' => ['@type' => 'Place', 'name' => __('site.service_area')],
                ],
            ]],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withJsonLd(array $data): self
    {
        $this->jsonLd[] = $data;

        return $this;
    }

    public function fullTitle(): string
    {
        $brand = (string) config('app.name');

        return Str::contains($this->title, $brand) ? $this->title : $this->title.' | '.$brand;
    }

    public function imageUrl(): string
    {
        return $this->image ?? Vite::asset('resources/images/og-default.webp');
    }

    public function canonicalUrl(): string
    {
        return $this->canonical ?? url()->current();
    }
}
