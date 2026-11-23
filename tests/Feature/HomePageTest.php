<?php

it('renders the home page in Slovak', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<html lang="sk">', escape: false)
        ->assertSee(__('home.heading'));
});

it('exposes the health check endpoint', function () {
    $this->get('/up')->assertOk();
});
