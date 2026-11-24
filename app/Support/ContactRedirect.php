<?php

namespace App\Support;

/**
 * The contact form exists on the contact page and in the contact box on other public pages.
 * After submitting, the visitor returns to the same page, scrolled to the form.
 */
final class ContactRedirect
{
    public const FRAGMENT = 'kontaktny-formular';

    public static function back(): string
    {
        $previous = url()->previous();
        $domain = (string) config('app.domain');

        // Only return to our own public pages (never to an external or admin URL).
        if (parse_url($previous, PHP_URL_HOST) !== $domain) {
            return route('contact.create').'#'.self::FRAGMENT;
        }

        return strtok($previous, '#').'#'.self::FRAGMENT;
    }
}
