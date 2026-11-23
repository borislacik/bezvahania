<?php

use App\Support\UntranslatedTextScanner;

it('ignores translated echoes, directives, comments and scripts', function () {
    $blade = <<<'BLADE'
        {{-- Komentár --}}
        <!-- HTML komentár -->
        <h1 class="text-xl">{{ __('home.heading') }}</h1>
        @if ($show)
            {!! __('home.lead') !!}
        @endif
        <x-layouts.public :title="__('home.title')" />
        <script>console.log('ahoj svet');</script>
        &nbsp;
        BLADE;

    expect((new UntranslatedTextScanner)->scan($blade))->toBe([]);
});

it('reports hardcoded text with its line number', function () {
    $blade = "<div>\n    <p>Rezervujte si termín</p>\n</div>";

    expect((new UntranslatedTextScanner)->scan($blade))
        ->toBe([['line' => 2, 'text' => 'Rezervujte si termín']]);
});

it('reports hardcoded visible attributes but not bound ones', function () {
    $blade = <<<'BLADE'
        <img src="a.webp" alt="Ukážka kurzu">
        <input :placeholder="__('form.name')" placeholder="{{ __('form.name') }}">
        BLADE;

    expect((new UntranslatedTextScanner)->scan($blade))
        ->toBe([['line' => 1, 'text' => 'alt="Ukážka kurzu"']]);
});

it('ignores arrows and comparisons inside directives and attributes', function () {
    $blade = <<<'BLADE'
        <a
            @class([
                'text-primary' => request()->routeIs('home'),
                'text-on-surface' => ! $active,
            ])
            :class="date === '2026-12-02' ? 'a' : 'b'"
            @if ($active) aria-current="page" @endif
        >
            {{ __('site.nav.home') }}
        </a>
        <x-site.card :index="$loop->index + 1" heading-level="h2" />
        BLADE;

    expect((new UntranslatedTextScanner)->scan($blade))->toBe([]);
});
