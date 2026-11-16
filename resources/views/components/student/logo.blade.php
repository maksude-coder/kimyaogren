@props(['compact' => false])

{{-- Brand mark: periodic cell "Kö", atomic number 6 (carbon). `compact` keeps only the cell on narrow screens. --}}
<a href="{{ route('home') }}" {{ $attributes->class('flex items-center gap-2.5 text-ink no-underline hover:text-ink') }}>
    <span aria-hidden="true" class="relative grid size-9 flex-none place-items-center rounded-md border-[1.5px] border-ink bg-amber font-mono text-sm font-semibold text-on-amber">
        Kö<span class="absolute top-px left-[3px] text-[8px] leading-none">6</span>
    </span>
    <span @class(['text-lg font-semibold', 'max-sm:sr-only' => $compact])>KimyaÖğren</span>
</a>
