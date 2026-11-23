<x-layouts.public :seo="$seo">
    <x-site.page-header :title="__('site.faq.title')" :lead="__('site.faq.lead')" />

    <section class="bg-surface pb-16 lg:pb-24">
        <div class="container-page">
            @if ($faqs->isEmpty())
                <p class="text-body-lg text-on-surface-variant">{{ __('site.faq.empty') }}</p>
            @else
                <x-site.faq-list :faqs="$faqs" />
            @endif
        </div>
    </section>

    <x-site.contact-cta />
</x-layouts.public>
