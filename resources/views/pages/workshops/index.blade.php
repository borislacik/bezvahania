<x-layouts.public :seo="$seo">
    <x-site.page-header :title="__('site.workshops.title')" :lead="__('site.workshops.lead')" />

    <section class="bg-surface pb-16 lg:pb-24">
        <div class="container-page">
            @if ($courses->isEmpty())
                <p class="text-body-lg text-on-surface-variant">{{ __('site.workshops.empty') }}</p>
            @else
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <x-site.course-card :course="$course" :index="$loop->index" heading-level="h2" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-site.contact-cta />
</x-layouts.public>
