@php
    $format = fn (int $seconds): string => \App\Models\PracticeSession::formatDuration($seconds);
    $target = \App\Models\PracticeSession::SECONDS_PER_QUESTION;
    $isMultipleChoice = fn ($question): bool => $question->type === \App\Enums\QuestionType::MultipleChoice;
@endphp

<x-student.layout title="Oturum özeti">
    <div class="mx-auto flex max-w-[860px] flex-col gap-7">
        <header class="flex flex-col gap-1.5">
            <span class="text-sm text-ink3">{{ $topic?->name ?? 'Konu çalışması' }}</span>
            <h1 class="text-2xl/tight font-semibold lg:text-[32px]/tight">Oturum tamamlandı <span class="font-normal text-ink3">· {{ $session->question_count }} soru</span></h1>
        </header>

        <dl class="grid grid-cols-3 gap-3">
            <div class="flex flex-col gap-1 rounded-[14px] border border-line bg-surface p-4">
                <dd class="font-mono text-[28px] font-semibold text-ok">{{ $session->correct_count }} <span aria-hidden="true" class="text-xl">✓</span></dd>
                <dt class="text-sm text-ink2">doğru</dt>
            </div>
            <div class="flex flex-col gap-1 rounded-[14px] border border-line bg-surface p-4">
                <dd class="font-mono text-[28px] font-semibold text-err">{{ $session->wrongCount() }} <span aria-hidden="true" class="text-xl">✕</span></dd>
                <dt class="text-sm text-ink2">yanlış</dt>
            </div>
            <div class="flex flex-col gap-1 rounded-[14px] border border-line bg-surface p-4">
                <dd class="font-mono text-[28px] font-semibold">{{ $session->skipped_count }}</dd>
                <dt class="text-sm text-ink2">boş</dt>
            </div>
        </dl>

        <p class="text-[15px] text-ink2">
            Süre <span class="font-mono font-medium text-ink">{{ $format($session->durationSeconds()) }}</span>
            · soru başına ort. <span class="font-mono font-medium text-ink">{{ $format($averageSeconds) }}</span>
            (hedef {{ $format($target) }})
            @if ($session->isTimed() && $session->durationSeconds() >= $session->time_limit_seconds)
                · <span class="font-semibold text-warn">süre doldu</span>
            @endif
        </p>

        <section aria-labelledby="kazanim-bazinda" class="flex flex-col gap-3">
            <h2 id="kazanim-bazinda" class="text-lg font-semibold">Kazanım bazında</h2>
            <ul class="overflow-hidden rounded-[14px] border border-line bg-surface">
                @foreach ($objectives as $objective)
                    <li class="flex items-center gap-3 border-b border-line px-4 py-3 last:border-b-0">
                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            @if ($objective['code'])
                                <span class="font-mono text-[13px] font-medium text-ink3">{{ $objective['code'] }}</span>
                            @endif
                            <span class="text-[15px]/snug">{{ $objective['description'] }}</span>
                        </span>
                        <span @class([
                            'flex-none font-mono text-sm font-semibold',
                            'text-ok' => $objective['correct'] === $objective['total'],
                            'text-warn' => $objective['correct'] * 2 < $objective['total'],
                        ])>
                            @if ($objective['correct'] === $objective['total'])<span aria-hidden="true">✓ </span>@elseif ($objective['correct'] * 2 < $objective['total'])<span aria-hidden="true">! </span>@endif{{ $objective['correct'] }}/{{ $objective['total'] }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="flex flex-wrap gap-2.5">
            <a href="{{ $topicUrl }}" class="btn-secondary min-h-[52px]">Teoriye dön</a>
            @if ($missed->isNotEmpty())
                <a href="#yanlislar" class="btn-primary min-h-[52px] min-w-[200px] flex-1 text-center">Yanlışları gözden geçir</a>
            @else
                <a href="{{ $topicUrl }}" class="btn-primary min-h-[52px] min-w-[200px] flex-1">Konuya dön</a>
            @endif
        </div>

        @if ($missed->isNotEmpty())
            <section id="yanlislar" aria-labelledby="yanlislar-baslik" class="flex scroll-mt-6 flex-col gap-3">
                <h2 id="yanlislar-baslik" class="text-lg font-semibold">Yanlış ve boş sorular</h2>

                @foreach ($missed as $row)
                    @php
                        $question = $row['question'];
                        $attempt = $row['attempt'];
                        $correct = $row['correctOption'];
                        $selected = $attempt?->selectedOption;
                        $letter = fn ($option): string => $option && $isMultipleChoice($question) ? (string) $option->letter() : \App\Support\QuestionHtml::text($option?->option_text);
                    @endphp
                    <details class="group rounded-[14px] border border-line bg-surface">
                        <summary class="flex min-h-[60px] cursor-pointer flex-wrap items-center gap-x-3 gap-y-1 px-4 py-3">
                            <span class="grid size-7 flex-none place-items-center rounded-[5px] bg-surface2 font-mono text-xs font-semibold">{{ $row['number'] }}</span>
                            @if ($question->learningObjective)
                                <span class="font-mono text-[13px] font-medium text-ink3">{{ $question->learningObjective->code }}</span>
                            @endif
                            <span class="flex-1 text-[15px]">
                                @if ($attempt)
                                    <span class="font-semibold text-err"><span aria-hidden="true">✕</span> {{ $letter($selected) }} seçildi</span> · doğru {{ $letter($correct) }}
                                @else
                                    <span class="font-semibold text-ink2">Boş bırakıldı</span> · doğru {{ $letter($correct) }}
                                @endif
                            </span>
                            <span class="text-sm font-semibold text-amber-ink group-open:hidden">Çözümü aç</span>
                            <span class="hidden text-sm font-semibold text-amber-ink group-open:inline">Kapat</span>
                        </summary>

                        <div class="flex flex-col gap-4 border-t border-line p-4">
                            <x-student.rich :html="$question->content" class="text-[17px]/[1.6]" />

                            <ul class="flex flex-col gap-1.5 text-[15px]">
                                @foreach ($question->options as $option)
                                    <li @class([
                                        'flex gap-2.5 rounded-lg px-3 py-2',
                                        'bg-ok-soft text-ok font-semibold' => $option->is_correct,
                                        'bg-err-soft text-err font-semibold' => ! $option->is_correct && $option->id === $selected?->id,
                                    ])>
                                        @if ($isMultipleChoice($question))
                                            <span class="flex-none font-mono">{{ $option->letter() }}</span>
                                        @endif
                                        <x-student.rich :html="$option->option_text" class="flex-1" />
                                        @if ($option->is_correct)
                                            <span class="flex-none text-[13px]">Doğru cevap</span>
                                        @elseif ($option->id === $selected?->id)
                                            <span class="flex-none text-[13px]">Senin cevabın</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex flex-col gap-2">
                                <h3 class="text-[15px] font-semibold">Adım adım çözüm</h3>
                                <x-student.rich :html="$question->explanation" class="text-[15px]/normal" />
                            </div>

                            @if (filled($selected?->feedback))
                                <div class="flex gap-2.5 rounded-[10px] bg-warn-soft px-3 py-2.5 text-sm/[1.45]">
                                    <span aria-hidden="true" class="flex-none font-bold text-warn">!</span>
                                    <x-student.rich :html="$selected->feedback" class="flex-1" />
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach

                @if ($session->correct_count > 0)
                    <p class="text-sm text-ink3">Diğer {{ $session->correct_count }} soru doğru.</p>
                @endif
            </section>
        @endif
    </div>
</x-student.layout>
