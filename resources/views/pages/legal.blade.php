<x-layouts.public :seo="$seo">
    <x-site.page-header :title="$page->title" />

    <section class="bg-surface pb-16 lg:pb-24">
        <div class="container-page">
            <div class="prose-legal">
                @if ($page->content)
                    {!! $page->content !!}
                @else
                    <p>{{ __('site.legal.missing', ['email' => $settings['contact_email']]) }}</p>
                @endif
            </div>
            @if ($page->exists)
                <p class="text-body-sm text-on-surface-variant mt-10">
                    {{ __('site.legal.updated', ['date' => $page->updated_at?->format('j. n. Y')]) }}
                </p>
            @endif
        </div>
    </section>
</x-layouts.public>
