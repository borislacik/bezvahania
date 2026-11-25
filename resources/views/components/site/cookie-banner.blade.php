<div
    x-data="cookieConsent(@js((string) config('services.google_analytics.id')))"
    x-show="open"
    x-cloak
    role="dialog"
    aria-modal="false"
    aria-labelledby="cookie-title"
    class="fixed inset-x-3 bottom-3 z-[70] sm:inset-x-auto sm:right-4 sm:bottom-4 sm:max-w-md"
    data-testid="cookie-banner"
    @keydown.escape.window="close()"
>
    <div class="bg-surface-container-lowest border-surface-container rounded-2xl border p-6 shadow-2xl">
        <div class="mb-2 flex items-center gap-2">
            <x-site.icon name="cookie" class="text-primary-container size-6" />
            <h2 id="cookie-title" class="text-headline-sm font-bold">{{ __('cookies.title') }}</h2>
            {{-- Closing without a new choice only makes sense once a choice exists (settings from the footer). --}}
            <button
                type="button"
                class="text-on-surface-variant hover:bg-surface-container hover:text-on-surface -mr-2 ml-auto rounded-lg p-1.5"
                x-show="hasConsent"
                @click="close()"
                aria-label="{{ __('cookies.close') }}"
                data-cookie-close
            >
                <x-site.icon name="close" class="size-5" />
            </button>
        </div>
        <p class="text-body-sm text-on-surface-variant">
            {{ __('cookies.text') }}
            <a href="{{ route('legal.cookies') }}" class="text-primary underline">{{ __('cookies.more') }}</a>
        </p>

        <div x-show="details" x-collapse class="mt-4 space-y-3">
            <div class="bg-surface-container-low rounded-xl p-3">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-heading text-label-lg font-bold">{{ __('cookies.necessary') }}</span>
                    <span class="text-label-md text-on-surface-variant">{{ __('cookies.always_on') }}</span>
                </div>
                <p class="text-body-sm text-on-surface-variant mt-1">{{ __('cookies.necessary_text') }}</p>
            </div>
            <label
                class="bg-surface-container-low flex cursor-pointer items-start justify-between gap-3 rounded-xl p-3"
            >
                <span>
                    <span class="font-heading text-label-lg block font-bold">{{ __('cookies.analytics') }}</span>
                    <span
                        class="text-body-sm text-on-surface-variant mt-1 block"
                        >{{ __('cookies.analytics_text') }}</span
                    >
                </span>
                <input type="checkbox" x-model="analytics" class="accent-primary-container mt-1 size-5 shrink-0" />
            </label>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">
            <button
                type="button"
                class="btn-light text-label-lg border border-slate-300 px-3 py-2.5"
                @click="reject()"
                data-cookie-reject
            >
                {{ __('cookies.reject') }}
            </button>
            <button type="button" class="btn-primary text-label-lg px-3 py-2.5" @click="acceptAll()">
                {{ __('cookies.accept_all') }}
            </button>
            <button
                type="button"
                class="text-label-lg text-on-surface-variant hover:text-on-surface col-span-2 py-1 underline"
                x-show="!details"
                @click="details = true"
            >
                {{ __('cookies.settings') }}
            </button>
            <button
                type="button"
                class="btn-dark text-label-lg col-span-2 px-3 py-2.5"
                x-show="details"
                x-cloak
                @click="save()"
            >
                {{ __('cookies.save') }}
            </button>
        </div>
    </div>
</div>
