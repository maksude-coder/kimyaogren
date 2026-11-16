@props(['for' => 'password'])

{{-- Password rules, visible before submit (§ 08); app.js ticks them while typing. --}}
<ul data-password-rules="{{ $for }}" class="flex flex-col gap-1 text-[13px] text-ink3">
    <li data-rule="length"><span aria-hidden="true" data-rule-icon>○</span> En az 8 karakter</li>
    <li data-rule="digit"><span aria-hidden="true" data-rule-icon>○</span> En az bir rakam</li>
</ul>
