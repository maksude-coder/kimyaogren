@props(['symbol', 'number', 'active' => false])

{{-- Periodic-cell motif (§ 01): corner number carries meaning (e.g. unit no). --}}
<span aria-hidden="true" @class([
    'relative grid size-12 flex-none place-items-center rounded-md border-[1.5px] font-mono text-base font-semibold',
    'border-ink bg-amber text-on-amber' => $active,
    'border-line2 text-ink' => ! $active,
])>
    {{ $symbol }}<span @class(['absolute top-0.5 left-1 text-[9px] leading-none', 'text-ink3' => ! $active])>{{ $number }}</span>
</span>
