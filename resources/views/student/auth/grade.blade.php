@php
    $graduate = \App\Http\Controllers\Student\Auth\GradeSelectionController::GRADUATE;
    $selected = old('grade', $selected);
@endphp

<x-student.auth title="Sınıfını seç" :step="2">
    <div>
        <h1 id="sinif-baslik" class="text-[26px]/tight font-semibold">Kaçıncı sınıftasın?</h1>
        <p class="mt-1.5 text-ink2">Sonradan değiştirebilirsin.</p>
    </div>

    <form method="POST" action="{{ route('register.grade') }}" class="flex flex-1 flex-col gap-5">
        @csrf

        <div role="radiogroup" aria-labelledby="sinif-baslik" @error('grade') aria-describedby="sinif-hata" @enderror class="flex flex-col gap-2.5">
            @foreach ($grades as $grade)
                <label class="grade-option">
                    <input type="radio" name="grade" value="{{ $grade->id }}" @checked((string) $selected === (string) $grade->id) required class="grade-radio">
                    <span>{{ $grade->number }}. Sınıf</span>
                </label>
            @endforeach

            <label class="grade-option">
                <input type="radio" name="grade" value="{{ $graduate }}" @checked($selected === $graduate) required class="grade-radio">
                <span>Mezun · YKS hazırlık<br><span class="text-[13px] font-normal text-ink3">Tüm sınıflar birlikte</span></span>
            </label>
        </div>

        @error('grade')
            <p id="sinif-hata" class="flex items-start gap-1.5 text-[13px] font-medium text-err">
                <span aria-hidden="true" class="mt-0.5 grid size-4 flex-none place-items-center rounded-full bg-err text-[11px] font-bold text-surface">!</span>
                <span>{{ $message }}</span>
            </p>
        @enderror

        <button type="submit" class="btn-primary mt-auto min-h-[52px] cursor-pointer lg:mt-1">Başla</button>

        <p class="text-center text-sm text-ink3"><a href="{{ route('curriculum.index') }}" class="font-semibold">Şimdilik geç</a></p>
    </form>
</x-student.auth>
