<?php

namespace App\Support;

/**
 * The website is a one-pager: the menu scrolls to these sections of the home page.
 * The former separate pages redirect (301) to them (routes/web.php).
 */
final class HomeSections
{
    public const WORKSHOPS = 'workshopy';

    public const INSTRUCTOR = 'lektor';

    public const FAQ = 'otazky';

    public const CONTACT = 'kontakt';

    /** Menu label key (lang/sk/site.php → nav.*) => section id. */
    public const MENU = [
        'workshops' => self::WORKSHOPS,
        'about' => self::INSTRUCTOR,
        'faq' => self::FAQ,
        'contact' => self::CONTACT,
    ];

    /** Former separate pages and the section they redirect to. */
    public const FORMER_PAGES = [
        '/workshopy' => self::WORKSHOPS,
        '/o-lektorovi' => self::INSTRUCTOR,
        '/caste-otazky' => self::FAQ,
        '/kontakt' => self::CONTACT,
    ];

    public static function url(string $section): string
    {
        return route('home').'#'.$section;
    }
}
