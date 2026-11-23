<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Page;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect(Page::KEYS)
            ->reject(fn (string $route): bool => $route === 'booking.create')
            ->map(fn (string $route): array => ['loc' => route($route), 'lastmod' => null])
            ->push(['loc' => route('booking.create'), 'lastmod' => null])
            ->merge(Course::query()->active()->get()->map(fn (Course $course): array => [
                'loc' => route('workshops.show', $course),
                'lastmod' => $course->updated_at?->toAtomString(),
            ]))
            ->values();

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /rezervacia/', 'Allow: /', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
