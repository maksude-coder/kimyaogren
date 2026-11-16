@php
    $topicUrl = fn ($topic) => route('curriculum.topic', [
        'program' => $program->slug,
        'grade' => $topic->unit->grade->number,
        'unit' => $topic->unit->code,
        'topic' => $topic->slug,
    ]);
@endphp

<x-student.layout title="Bugün" :program="$program" class="flex flex-col gap-7">
    <header>
        <p class="text-sm text-ink3 lg:text-[15px]">{{ $date }}</p>
        <h1 class="text-2xl/tight font-semibold lg:text-[28px]/[34px]">{{ $isNewStudent ? 'Hoş geldin' : $greeting }}, {{ $firstName }}</h1>
    </header>

    <div class="grid items-start gap-7 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="flex flex-col gap-5">
            @if ($isNewStudent)
                {{-- New student (§ 07): choose where to start. --}}
                <section aria-labelledby="baslangic" class="flex flex-col gap-4 rounded-[14px] border border-line bg-surface p-5 lg:p-7">
                    <div class="flex flex-col gap-1.5">
                        <h2 id="baslangic" class="text-xl/tight font-semibold lg:text-[26px]/tight">Nereden başlamak istersin?</h2>
                        <p class="max-w-[560px] text-ink2">Bir sınıf seç ve konulara göz at; öneriler ilk çözdüğün sorulardan sonra kişiselleşir.</p>
                    </div>

                    @if ($grade === null)
                        <p class="text-ink2">Müfredat hazırlanıyor.</p>
                    @else
                        @if ($grades->count() > 1)
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($grades as $item)
                                    @continue($item->is($grade))
                                    <li><a href="{{ route('curriculum.grade', ['program' => $program->slug, 'grade' => $item->number]) }}" class="btn-secondary">{{ $item->number }}. Sınıf</a></li>
                                @endforeach
                            </ul>
                        @endif
                        <a href="{{ route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number]) }}" class="btn-primary min-h-[52px] self-start px-7">{{ $grade->number }}. Sınıf ile başla</a>
                    @endif
                </section>
            @elseif ($recommendation === null)
                <section aria-labelledby="oneri" class="flex flex-col items-start gap-3 rounded-[14px] border border-line bg-surface p-5 lg:p-7">
                    <h2 id="oneri" class="eyebrow text-amber-ink">BUGÜNÜN ÖNERİSİ</h2>
                    <p class="font-semibold">Sorular hazırlanıyor.</p>
                    <p class="max-w-[560px] text-ink2">Şu an çözebileceğin onaylı soru yok. Konu anlatımlarına müfredattan ulaşabilirsin.</p>
                    <a href="{{ route('curriculum.index') }}" class="btn-primary mt-1">Müfredata git</a>
                </section>
            @else
                @php
                    $topic = $recommendation->topic;
                @endphp
                <section aria-labelledby="oneri" class="flex flex-col gap-5 rounded-[14px] border border-line bg-surface p-5 lg:flex-row lg:items-center lg:justify-between lg:gap-8 lg:p-7">
                    <div class="flex min-w-0 flex-col gap-3">
                        <h2 id="oneri" class="eyebrow text-amber-ink">BUGÜNÜN ÖNERİSİ · ≈ {{ $recommendation->minutes }} DK</h2>
                        <div>
                            <p class="text-[15px] text-ink3">{{ $topic->unit->grade->number }}. Sınıf · {{ $topic->unit->name }}</p>
                            <p class="mt-1 text-xl/tight font-semibold lg:text-[26px]/tight"><a href="{{ $topicUrl($topic) }}" class="text-ink no-underline hover:text-ink">{{ $topic->name }}</a></p>
                        </div>
                        <p class="flex items-start gap-2.5 text-ink2">
                            @if ($recommendation->kind === 'weak')
                                <span aria-hidden="true" class="mt-0.5 grid size-5 flex-none place-items-center rounded-full bg-warn-soft text-xs font-bold text-warn">!</span>
                            @endif
                            <span>{{ $recommendation->reason }}</span>
                        </p>
                    </div>

                    @if ($recommendation->session)
                        <a href="{{ route('practice.show', $recommendation->session->id) }}" class="btn-primary min-h-[52px] flex-none px-7 text-center">Kaldığın yerden devam et</a>
                    @else
                        <form method="POST" action="{{ route('practice.store') }}" class="flex flex-none">
                            @csrf
                            <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                            <input type="hidden" name="size" value="{{ \App\Http\Controllers\Student\TodayController::RECOMMENDED_SIZE }}">
                            <button type="submit" class="btn-primary min-h-[52px] flex-1 cursor-pointer px-7">Çalışmaya başla</button>
                        </form>
                    @endif
                </section>
            @endif

            @if ($openSession)
                <section aria-labelledby="kaldigin-yer" class="flex flex-wrap items-center gap-x-6 gap-y-3 rounded-[14px] border border-line bg-surface p-5 lg:px-6">
                    <div class="flex min-w-0 flex-1 basis-64 flex-col gap-1.5">
                        <h2 id="kaldigin-yer" class="text-sm font-normal text-ink3">Kaldığın yer · {{ $openSession->topic->unit->grade->number }}. Sınıf · {{ $openSession->topic->unit->name }}</h2>
                        <p class="text-[17px] font-semibold">{{ $openSession->topic->name }}</p>
                        <div class="flex max-w-[420px] items-center gap-2.5">
                            <span aria-hidden="true" class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface2">
                                <span class="block h-full bg-ink2" style="width: {{ $openSession->question_count > 0 ? round($openSessionAnswered / $openSession->question_count * 100) : 0 }}%"></span>
                            </span>
                            <span class="font-mono text-[13px] font-medium text-ink2">{{ $openSessionAnswered }}/{{ $openSession->question_count }} soru</span>
                        </div>
                    </div>
                    <a href="{{ route('practice.show', $openSession->id) }}" class="btn-secondary min-h-11 px-5">Devam et</a>
                </section>
            @endif
        </div>

        @unless ($isNewStudent)
            <div class="flex flex-col gap-5">
                @if ($weakTopics->isNotEmpty())
                    <section aria-labelledby="zayif-konular" class="overflow-hidden rounded-[14px] border border-line bg-surface">
                        <div class="flex items-baseline justify-between gap-3 border-b border-line px-4 py-4 lg:px-5">
                            <h2 id="zayif-konular" class="text-[17px] font-semibold">Zayıf konular</h2>
                            <span class="text-[13px] text-ink3">ustalık &lt; %50</span>
                        </div>
                        <ul>
                            @foreach ($weakTopics as $row)
                                <li class="border-b border-line last:border-b-0">
                                    <a href="{{ $row['url'] }}" class="flex min-h-14 items-center gap-3 px-4 py-2 text-ink no-underline hover:bg-surface2 hover:text-ink lg:px-5">
                                        <span class="flex min-w-0 flex-1 flex-col">
                                            <span class="font-medium">{{ $row['mastery']->topic->name }}</span>
                                            <span class="text-[13px] text-ink3">{{ $row['mastery']->topic->unit->grade->number }}. Sınıf · {{ $row['answers'] }} soru</span>
                                        </span>
                                        <span class="flex-none font-mono text-[15px] font-semibold text-err"><span aria-hidden="true">! </span>%{{ $row['mastery']->displayPercentage() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section aria-labelledby="bu-hafta" class="flex flex-col gap-3 rounded-[14px] border border-line bg-surface px-4 py-4 lg:px-5">
                    <h2 id="bu-hafta" class="text-[17px] font-semibold">Bu hafta</h2>
                    <dl class="grid grid-cols-3 gap-3">
                        <div class="flex flex-col-reverse">
                            <dt class="text-[13px] text-ink3">çözülen soru</dt>
                            <dd class="font-mono text-[22px] font-semibold">{{ $week['answers'] }}</dd>
                        </div>
                        <div class="flex flex-col-reverse">
                            <dt class="text-[13px] text-ink3">doğruluk</dt>
                            <dd class="font-mono text-[22px] font-semibold">{{ $week['accuracy'] === null ? '—' : '%'.$week['accuracy'] }}</dd>
                        </div>
                        <div class="flex flex-col-reverse">
                            <dt class="text-[13px] text-ink3">gün çalıştın</dt>
                            <dd class="font-mono text-[22px] font-semibold">{{ $week['days'] }}/{{ $week['of'] }}</dd>
                        </div>
                    </dl>
                    <p class="text-[13px] text-ink3">Bugün dahil son {{ $week['of'] }} gün.</p>
                    <a href="{{ route('progress.index') }}" class="flex min-h-11 items-center self-start text-[15px] font-semibold">İlerleme panelini aç</a>
                </section>
            </div>
        @endunless
    </div>
</x-student.layout>
