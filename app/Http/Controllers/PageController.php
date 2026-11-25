<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\Seo;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $faqs = Faq::query()->active()->get();

        $seo = Seo::forPage(Page::forKey('home'))->withJsonLd($this->localBusiness());

        if ($faqs->isNotEmpty()) {
            $seo->withJsonLd([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn (Faq $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->answer)],
                ])->all(),
            ]);
        }

        return view('pages.home', [
            'seo' => $seo,
            'courses' => Course::query()->active()->get(),
            'instructor' => TeamMember::query()->active()->first(),
            'testimonials' => Testimonial::query()->active()->get(),
            'faqs' => $faqs,
        ]);
    }

    public function legal(string $key): View
    {
        $page = Page::forKey($key);

        return view('pages.legal', ['seo' => Seo::forPage($page), 'page' => $page]);
    }

    /**
     * @return array<string, mixed>
     */
    private function localBusiness(): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => config('app.name'),
            'description' => __('site.default_description'),
            'url' => route('home'),
            'telephone' => Setting::get('contact_phone'),
            'email' => Setting::get('contact_email'),
            'areaServed' => ['@type' => 'City', 'name' => 'Bratislava'],
            'priceRange' => '€€',
            'sameAs' => array_filter([Setting::get('social_facebook')]) ?: null,
        ]);
    }
}
