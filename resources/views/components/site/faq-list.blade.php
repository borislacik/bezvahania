@props (['faqs'])

<div {{ $attributes->class('mx-auto flex max-w-3xl flex-col gap-3') }} x-data="{ open: null }">
    @foreach ($faqs as $faq)
        <div class="bg-surface-container-lowest rounded-xl shadow-sm">
            <h3>
                <button
                    type="button"
                    class="font-heading text-headline-sm text-on-surface flex w-full items-center justify-between gap-4 p-5 text-left font-bold"
                    @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}"
                    :aria-expanded="(open === {{ $faq->id }}).toString()"
                    aria-controls="faq-{{ $faq->id }}"
                >
                    {{ $faq->question }}
                    <span class="transition-transform duration-200" :class="open === {{ $faq->id }} && 'rotate-180'">
                        <x-site.icon name="expand_more" class="text-primary size-6" />
                    </span>
                </button>
            </h3>
            <div id="faq-{{ $faq->id }}" x-show="open === {{ $faq->id }}" x-collapse x-cloak>
                <div class="text-body-md text-on-surface-variant px-5 pb-5">{!! nl2br(e($faq->answer)) !!}</div>
            </div>
        </div>
    @endforeach
</div>
