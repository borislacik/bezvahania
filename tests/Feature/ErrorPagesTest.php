<?php

it('shows a Slovak 404 page', function () {
    $this->get('/neexistujuca-stranka')
        ->assertNotFound()
        ->assertSee(__('errors.404.title'))
        ->assertSee(__('errors.home'));
});

it('renders every error page in Slovak', function (string $code) {
    $html = view("errors.{$code}")->render();

    expect($html)->toContain(__("errors.{$code}.title"))->toContain('lang="sk"');
})->with(['404', '419', '429', '500', '503']);
