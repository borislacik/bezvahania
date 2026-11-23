<x-layouts.public :seo="$seo">
    <x-site.page-header :title="__('site.about.title')" :lead="__('site.about.lead')" />

    @foreach ($members as $member)
        <section @class (['section', 'bg-surface-container-low' => $loop->odd, 'bg-surface' => $loop->even])>
            <div class="container-page">
                <x-site.instructor :member="$member" />
            </div>
        </section>
    @endforeach

    <section class="section bg-surface">
        <div class="container-page max-w-3xl text-center">
            <h2 class="text-headline-xl-mobile lg:text-headline-xl text-on-surface mb-4 font-bold">
                {{ __('site.about.why_title') }}
            </h2>
            <p class="text-body-lg text-on-surface-variant">{{ __('site.about.why_text') }}</p>
        </div>
    </section>

    <x-site.contact-cta />
</x-layouts.public>
