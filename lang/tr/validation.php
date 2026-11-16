<?php

declare(strict_types=1);

// Only the rules the student forms use; anything else falls back to English.
return [
    'min' => [
        'string' => ':Attribute en az :min karakter olmalı.',
    ],
    'password' => [
        'numbers' => ':Attribute en az bir rakam içermeli.',
    ],

    'attributes' => [
        'name' => 'ad',
        'email' => 'e-posta',
        'password' => 'şifre',
    ],
];
