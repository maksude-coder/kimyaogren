@props(['name', 'label', 'type' => 'text', 'value' => null, 'aside' => null])

{{--
    Form field (§ 08): label above, 48px control, error under the field with
    icon + text. A password field gets a "Göster" toggle (app.js).
--}}
@php
    $invalid = $errors->has($name);
    $password = $type === 'password';
@endphp

<div class="flex flex-col gap-1.5">
    <div class="flex items-baseline justify-between gap-3 text-sm font-medium">
        <label for="{{ $name }}">{{ $label }}</label>
        {{ $aside }}
    </div>

    <div @class(['field-control', 'field-control-invalid' => $invalid]) @if ($password) data-password-field @endif>
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @unless ($password) value="{{ old($name, $value) }}" @endunless
            @if ($invalid) aria-invalid="true" @endif
            aria-describedby="{{ $name }}-yardim"
            {{ $attributes->class('field-input') }}
        >
        @if ($password)
            <button type="button" data-password-toggle aria-controls="{{ $name }}" aria-pressed="false" hidden class="min-h-10 cursor-pointer rounded-lg px-2.5 text-sm font-semibold text-amber-ink">Göster</button>
        @endif
    </div>

    <div id="{{ $name }}-yardim" class="flex flex-col gap-1">
        @error($name)
            <p class="flex items-start gap-1.5 text-[13px] font-medium text-err">
                <span aria-hidden="true" class="mt-0.5 grid size-4 flex-none place-items-center rounded-full bg-err text-[11px] font-bold text-surface">!</span>
                <span>{{ $message }}</span>
            </p>
        @enderror

        {{ $slot }}
    </div>
</div>
