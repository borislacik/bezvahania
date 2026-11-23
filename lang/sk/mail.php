<?php

return [
    'greeting' => 'Dobrý deň, :name,',
    'signature' => 'Teším sa na stretnutie',

    'booking_confirmed' => [
        'subject' => 'Potvrdenie rezervácie :reference',
        'heading' => 'Vaša rezervácia je potvrdená',
        'intro' => 'ďakujem za rezerváciu workshopu prvej pomoci. Termín je pre vás rezervovaný:',
        'payment' => 'Platba prebieha až po skončení workshopu, v hotovosti alebo kartou. Deň vopred sa vám ozvem a potvrdím presný čas príchodu.',
        'change' => 'Ak potrebujete termín zmeniť alebo zrušiť, zavolajte na :phone alebo napíšte na :email.',
    ],

    'booking_cancelled' => [
        'subject' => 'Zrušenie rezervácie :reference',
        'heading' => 'Rezervácia bola zrušená',
        'intro' => 'rezervácia :reference (:course, :date, :slot) bola zrušená.',
        'reason' => 'Dôvod',
        'rebook' => 'Ak si chcete vybrať iný termín, urobíte to jednoducho v kalendári.',
        'button' => 'Vybrať nový termín',
    ],

    'new_booking_admin' => [
        'subject' => 'Nová rezervácia: :date, :slot',
        'heading' => 'Nová rezervácia z webu',
        'button' => 'Otvoriť administráciu',
    ],

    'contact_admin' => [
        'subject' => 'Nová správa z webu od :name',
        'heading' => 'Nová správa z kontaktného formulára',
    ],
];
