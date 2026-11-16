<?php

declare(strict_types=1);

// "sent" and "user" are deliberately the same text: the form never reveals
// whether an address belongs to an account (Sprint 2.3 plan § 2, rule 7).
return [
    'reset' => 'Şifren yenilendi. Yeni şifrenle giriş yapabilirsin.',
    'sent' => 'Bu adrese kayıtlı bir hesap varsa şifre yenileme bağlantısını gönderdik.',
    'throttled' => 'Kısa süre önce bir bağlantı gönderdik. Biraz bekleyip yeniden dene.',
    'token' => 'Bu bağlantı geçersiz ya da süresi dolmuş. Yeni bir bağlantı iste.',
    'user' => 'Bu bağlantı geçersiz ya da süresi dolmuş. Yeni bir bağlantı iste.',
];
