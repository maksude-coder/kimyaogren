@props(['level'])

{{-- Mastery level label (§ 06): colour is always paired with an icon and the word. --}}
@php($value = $level->value)
<span {{ $attributes->class([
    'text-[13px] font-semibold',
    'text-ok' => $value === 4,
    'text-ink2' => $value === 2 || $value === 3,
    'text-err' => $value === 1,
    'text-ink3' => $value === 0,
]) }}>@if ($value === 4)<span aria-hidden="true">✓ </span>@elseif ($value === 1)<span aria-hidden="true">! </span>@endif{{ __('curriculum.mastery.'.$value) }}</span>
