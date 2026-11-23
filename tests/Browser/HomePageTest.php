<?php

it('shows the home page without JavaScript errors', function () {
    visit('/')
        ->assertSee(__('home.heading'))
        ->assertNoJavaScriptErrors();
});

it('loads the self-hosted brand fonts', function () {
    $page = visit('/');

    $loaded = $page->script(<<<'JS'
        () => document.fonts.ready.then(() => [...document.fonts]
            .filter((font) => font.status === 'loaded')
            .map((font) => font.family.replaceAll('"', '')))
        JS);

    expect($loaded)->toContain('Space Grotesk', 'Work Sans');
});
