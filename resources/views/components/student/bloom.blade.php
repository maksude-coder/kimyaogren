@props(['level'])

{{-- Bloom badge (§ 01): numbered cell = level; 1–3 open cell, 4–6 filled. --}}
<span {{ $attributes->class('inline-flex h-7 items-center gap-1.5 justify-self-start self-start lg:self-center rounded-md border border-line2 pr-2.5 pl-[3px] text-[13px] font-medium text-ink2') }}>
    <span @class([
        'grid size-[22px] place-items-center rounded font-mono text-xs font-semibold',
        'bg-surface2 text-ink' => $level->value <= 3,
        'bg-ink text-bg' => $level->value >= 4,
    ])>{{ $level->value }}</span>
    {{ __('curriculum.bloom.'.$level->value) }}
</span>
