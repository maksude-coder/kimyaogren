<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';
    case ContentEditor = 'content_editor';
}
