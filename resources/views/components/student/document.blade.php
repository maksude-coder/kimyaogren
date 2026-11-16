@props(['title' => null, 'description' => null, 'head' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#FAF8F4" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#171410" media="(prefers-color-scheme: dark)">

        <title>{{ $title ? $title.' · KimyaÖğren' : 'KimyaÖğren' }}</title>

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        {{ $head }}

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body {{ $attributes }}>
        {{ $slot }}
    </body>
</html>
