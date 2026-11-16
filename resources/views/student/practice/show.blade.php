@php
    $total = $session->question_count;
    $remaining = $session->secondsRemaining();
    $correctOption = $question->options->firstWhere('is_correct', true);
    $selectedOption = $attempt ? $question->options->firstWhere('id', $attempt->selected_option_id) : null;
    $objective = $question->learningObjective;
@endphp

<x-student.document :title="'Soru '.$number.' / '.$total">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <a href="#soru" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-20 focus:rounded-lg focus:bg-surface focus:px-4 focus:py-3">Soruya geç</a>

    <header class="sticky top-0 z-10 border-b border-line bg-bg">
        <div class="mx-auto flex min-h-16 max-w-[960px] items-center gap-3 px-5">
            <form method="POST" action="{{ route('practice.finish', $session->id) }}">
                @csrf
                <button type="submit" class="flex min-h-11 cursor-pointer items-center gap-2 text-[15px] font-medium text-ink2"><span aria-hidden="true">✕</span> Oturumu bitir</button>
            </form>

            <span class="ml-auto text-right text-sm text-ink3 max-sm:hidden">{{ $topic?->name }}</span>
            <span class="font-mono text-sm font-medium max-sm:ml-auto">Soru {{ $number }} / {{ $total }}</span>

            @if ($remaining !== null)
                <span data-countdown data-seconds="{{ $remaining }}" data-expired-url="{{ route('practice.summary', $session->id) }}" role="timer" aria-label="Kalan süre" class="countdown">
                    <span data-countdown-text>{{ \App\Models\PracticeSession::formatDuration($remaining) }}</span>
                    <span data-countdown-label hidden>az kaldı</span>
                </span>
            @endif
        </div>
    </header>

    <main class="mx-auto flex max-w-[960px] flex-col gap-6 px-5 pt-5 pb-12">
        <nav aria-label="Sorular" class="flex flex-wrap gap-1.5">
            @foreach ($grid as $cell)
                <a
                    href="{{ route('practice.show', [$session->id, $cell['number']]) }}"
                    @if ($cell['number'] === $number) aria-current="true" @endif
                    data-state="{{ $cell['state'] }}"
                    class="grid-cell"
                    aria-label="Soru {{ $cell['number'] }}: {{ ['correct' => 'doğru', 'wrong' => 'yanlış', 'open' => 'cevapsız'][$cell['state']] }}{{ $cell['flagged'] ? ', işaretli' : '' }}"
                >
                    {{ $cell['number'] }}@if ($cell['state'] === 'correct')<span aria-hidden="true">✓</span>@elseif ($cell['state'] === 'wrong')<span aria-hidden="true">✕</span>@endif
                    @if ($cell['flagged'])<span aria-hidden="true" class="grid-flag"></span>@endif
                </a>
            @endforeach
        </nav>

        <section id="soru" class="flex flex-col gap-5 rounded-2xl border border-line bg-surface p-[clamp(18px,3vw,28px)]">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[13px] font-medium text-ink3">
                @if ($objective)
                    <span>{{ $objective->code }}</span><span aria-hidden="true">·</span>
                @endif
                <span>{{ __('curriculum.bloom.'.$question->bloom_level->value) }}</span><span aria-hidden="true">·</span>
                <span>Zorluk {{ $question->difficulty->value }}/5</span>
                @if ($isFlagged)
                    <span class="font-sans font-semibold text-amber-ink">İşaretli</span>
                @endif
            </div>

            <x-student.rich :html="$question->content" id="soru-metni" class="text-lg/[1.65] text-pretty" />

            @if ($attempt === null)
                <form id="isaretle" method="POST" action="{{ route('practice.flag', [$session->id, $number]) }}">@csrf</form>

                <form method="POST" action="{{ route('practice.answer', [$session->id, $number]) }}" class="flex flex-col gap-4">
                    @csrf

                    <div role="radiogroup" aria-labelledby="soru-metni" @error('option') aria-describedby="sik-hata" @enderror class="flex flex-col gap-2">
                        @foreach ($question->options as $option)
                            <label class="option">
                                <input type="radio" name="option" value="{{ $option->id }}" required class="sr-only">
                                @if ($question->type === \App\Enums\QuestionType::MultipleChoice)
                                    <span class="option-key">{{ $option->letter() }}</span>
                                @endif
                                <x-student.rich :html="$option->option_text" class="flex-1" />
                            </label>
                        @endforeach
                    </div>

                    @error('option')
                        <p id="sik-hata" class="flex items-start gap-1.5 text-[13px] font-medium text-err">
                            <span aria-hidden="true" class="mt-0.5 grid size-4 flex-none place-items-center rounded-full bg-err text-[11px] font-bold text-surface">!</span>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror

                    <div class="flex flex-wrap gap-2.5">
                        <button type="submit" form="isaretle" class="btn-secondary min-h-[52px]">{{ $isFlagged ? 'İşareti kaldır' : 'İşaretle' }}</button>
                        @if ($nextNumber)
                            <a href="{{ route('practice.show', [$session->id, $nextNumber]) }}" class="btn-secondary min-h-[52px]">Boş bırak</a>
                        @endif
                        <button type="submit" class="btn-primary min-h-[52px] min-w-[180px] flex-1 cursor-pointer">Kontrol et</button>
                    </div>
                </form>
            @else
                <div role="status" data-status data-state="{{ $attempt->is_correct ? 'correct' : 'wrong' }}" class="flex items-center gap-3 rounded-xl px-3.5 py-3">
                    <span data-status-icon aria-hidden="true" class="grid size-7 flex-none place-items-center rounded-full font-bold">{{ $attempt->is_correct ? '✓' : '✕' }}</span>
                    <span class="font-semibold">
                        @if ($attempt->is_correct)
                            Doğru.
                        @else
                            Yanlış. Doğru cevap: {{ $correctOption?->letter() && $question->type === \App\Enums\QuestionType::MultipleChoice ? $correctOption->letter().' · ' : '' }}{{ \App\Support\QuestionHtml::text($correctOption?->option_text) }}
                        @endif
                    </span>
                </div>

                <div class="flex flex-col gap-2">
                    @foreach ($question->options as $option)
                        @php
                            $result = $option->is_correct ? 'correct' : ($option->id === $selectedOption?->id ? 'wrong' : null);
                        @endphp
                        <div class="option cursor-default" @if ($result) data-result="{{ $result }}" @endif>
                            <span class="option-key">{{ $result === 'correct' ? '✓' : ($result === 'wrong' ? '✕' : ($question->type === \App\Enums\QuestionType::MultipleChoice ? $option->letter() : '')) }}</span>
                            <x-student.rich :html="$option->option_text" class="flex-1" />
                            <span data-tag class="text-[13px] font-semibold">{{ $result === 'correct' ? 'Doğru cevap' : ($result === 'wrong' ? 'Senin cevabın' : '') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2.5 rounded-xl border border-line p-4">
                    <h2 class="text-[15px] font-semibold">Adım adım çözüm</h2>
                    <x-student.rich :html="$question->explanation" class="text-[15px]/normal" />

                    @if (! $attempt->is_correct && filled($selectedOption?->feedback))
                        <div class="flex gap-2.5 rounded-[10px] bg-warn-soft px-3 py-2.5 text-sm/[1.45]">
                            <span aria-hidden="true" class="flex-none font-bold text-warn">!</span>
                            <span>{{ $selectedOption->feedback }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2.5">
                    @if ($nextNumber)
                        <a href="{{ route('practice.show', [$session->id, $nextNumber]) }}" class="btn-primary min-h-[52px] min-w-[180px] flex-1">Sonraki soru</a>
                    @else
                        <form method="POST" action="{{ route('practice.finish', $session->id) }}" class="flex min-w-[180px] flex-1">
                            @csrf
                            <button type="submit" class="btn-primary min-h-[52px] flex-1 cursor-pointer">Oturumu bitir</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>

        <p class="text-center text-sm text-ink3">{{ $answeredCount }} / {{ $total }} soru cevaplandı</p>
    </main>
</x-student.document>
