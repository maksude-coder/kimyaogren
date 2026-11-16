@props(['value', 'label' => null])

{{-- Difficulty 1–5 (§ 01): filled squares up to the value, text label alongside. --}}
<span {{ $attributes->class('inline-flex items-center gap-1.5') }}>
    <span aria-hidden="true" class="flex gap-0.5">
        @for ($i = 1; $i <= 5; $i++)
            <span @class(['size-2', 'bg-ink2' => $i <= $value, 'border border-line2' => $i > $value])></span>
        @endfor
    </span>
    {{ $label ?? __('curriculum.difficulty.'.$value) }}
</span>
