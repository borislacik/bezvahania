<?php

it('shows the home page without JavaScript errors', function () {
    visit('/')
        ->assertSee(__('home.heading'))
        ->assertNoJavaScriptErrors();
});
