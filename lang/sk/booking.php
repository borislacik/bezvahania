<?php

return [
    'eyebrow' => 'Online rezervácia',
    'title' => 'Zarezervujte si termín priamo k vám',
    'lead' => 'Vyberte deň a časť dňa v kalendári, zvoľte workshop a vyplňte kontaktné údaje. Rezervácia je potvrdená hneď a potvrdenie vám príde e-mailom.',

    'steps' => [
        'date' => 'Výber dňa',
        'slot' => 'Doobeda alebo poobede',
        'course' => 'Výber workshopu',
        'contact' => 'Kontaktné údaje pre návštevu',
    ],

    'calendar' => [
        'previous' => 'Predchádzajúci mesiac',
        'next' => 'Nasledujúci mesiac',
        'weekdays' => ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'],
        'legend_available' => 'Voľný termín',
        'legend_unavailable' => 'Obsadené alebo nedostupné',
        'day_available' => ':date – voľné termíny',
        'day_unavailable' => ':date – nedostupné',
        'pick_date_first' => 'Najprv vyberte deň v kalendári.',
        'selected_day' => 'Vybraný deň: :date',
        'slot_taken' => 'Obsadené',
        'slot_free' => 'Voľné',
    ],

    'fields' => [
        'course' => 'Workshop',
        'date' => 'Dátum',
        'time_slot' => 'Časť dňa',
        'contact_name' => 'Meno a priezvisko',
        'phone' => 'Telefón',
        'email' => 'E-mail',
        'address' => 'Adresa konania (kam mám prísť)',
        'participants' => 'Počet účastníkov',
        'note' => 'Poznámka alebo špecifické otázky (nepovinné)',
        'consent' => 'Súhlasím s :terms a beriem na vedomie :privacy.',
        'consent_terms' => 'podmienkami rezervácie',
        'consent_privacy' => 'spracovanie osobných údajov',
    ],

    'placeholders' => [
        'contact_name' => 'napr. Peter Novák',
        'phone' => '+421 9xx xxx xxx',
        'email' => 'vas@email.sk',
        'address' => 'Ulica a číslo, mesto alebo obec',
        'note' => 'napr. máme malé bábätko, chceli by sme sa zamerať aj na dusenie u detí',
    ],

    'participants_option' => ':count osoby',
    'area_note' => 'Doprava do 60 km od Bratislavy je v cene, ďalej podľa individuálnej dohody.',

    'summary' => [
        'title' => 'Súhrn rezervácie',
        'reference' => 'Číslo rezervácie',
        'course' => 'Workshop',
        'date' => 'Termín',
        'duration' => 'Trvanie',
        'address' => 'Adresa',
        'participants' => 'Počet účastníkov',
        'price' => 'Cena za skupinu',
        'not_selected' => 'nevybrané',
    ],

    'consent_required' => 'Pred rezerváciou potvrďte súhlas s podmienkami rezervácie a spracovaním osobných údajov.',

    'submit' => 'Záväzne rezervovať workshop',
    'payment_note' => 'Platba až po skončení workshopu, v hotovosti alebo kartou.',

    'confirmation' => [
        'title' => 'Ďakujeme za rezerváciu!',
        'lead' => 'Termín je pre vás rezervovaný. Potvrdenie sme poslali na :email.',
        'next' => 'Deň pred workshopom sa vám ozvem a potvrdím presný čas príchodu.',
        'change' => 'Potrebujete termín zmeniť alebo zrušiť? Zavolajte na :phone alebo napíšte na :email.',
        'home' => 'Späť na úvod',
    ],

    'errors' => [
        'slot_unavailable' => 'Vybraný termín už nie je voľný. Vyberte si prosím iný deň alebo časť dňa.',
    ],
];
