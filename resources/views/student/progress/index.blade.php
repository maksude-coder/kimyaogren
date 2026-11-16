@php
    use App\Enums\MasteryLevel;
    use App\Models\TopicMastery;

    $minimum = MasteryLevel::MINIMUM_ATTEMPTS;
@endphp

<x-student.layout title="İlerleme" :program="$program" class="flex flex-col gap-7">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between lg:gap-6">
        <div>
            <p class="text-sm text-ink3 lg:text-[15px]">Başlangıçtan bu yana</p>
            <h1 class="text-2xl/tight font-semibold lg:text-[28px]/[34px]">İlerleme</h1>
        </div>

        <dl class="grid grid-cols-3 rounded-xl border border-line bg-surface">
            <div class="flex flex-col-reverse border-r border-line px-4 py-2.5 lg:px-5">
                <dt class="text-[13px] text-ink3">çözülen soru</dt>
                <dd class="font-mono text-xl font-semibold">{{ $totalAnswers }}</dd>
            </div>
            <div class="flex flex-col-reverse border-r border-line px-4 py-2.5 lg:px-5">
                <dt class="text-[13px] text-ink3">genel doğruluk</dt>
                <dd class="font-mono text-xl font-semibold">{{ $accuracy === null ? '—' : '%'.$accuracy }}</dd>
            </div>
            <div class="flex flex-col-reverse px-4 py-2.5 lg:px-5">
                <dt class="text-[13px] text-ink3">konu başlandı / {{ $topicsTotal }}</dt>
                <dd class="font-mono text-xl font-semibold">{{ $topicsStarted }}</dd>
            </div>
        </dl>
    </div>

    @if ($totalAnswers === 0)
        <div class="flex flex-col items-start gap-3 rounded-[14px] border border-line bg-surface p-6">
            <p class="font-semibold">Henüz soru çözmedin.</p>
            <p class="max-w-[560px] text-ink2">İlk sorularını çözdüğünde konu ustalığın, zorlandığın kazanımlar ve çalışma düzenin burada görünür.</p>
            <a href="{{ route('curriculum.index') }}" class="btn-primary mt-1">Müfredata git</a>
        </div>
    @else
        <div class="grid items-start gap-7 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
            <section aria-labelledby="konu-ustaligi" class="@container overflow-hidden rounded-[14px] border border-line bg-surface">
                <div class="flex flex-col gap-3 border-b border-line px-4 py-4 lg:px-6 @2xl:flex-row @2xl:items-center @2xl:justify-between @2xl:gap-4">
                    <div class="flex flex-col gap-0.5">
                        <h2 id="konu-ustaligi" class="text-lg font-semibold">Konu ustalığı</h2>
                        <p class="text-[13px] text-ink3">Son {{ TopicMastery::WINDOW }} sorudaki doğruluk · Usta ≥ %80 · Yetkin %65–79 · Gelişiyor %50–64 · Zayıf &lt; %50 · En az {{ $minimum }} soru gerekir.</p>
                    </div>

                    @if ($grades->count() > 1)
                        <nav aria-label="Sınıflar" class="grid flex-none auto-cols-fr grid-flow-col gap-[3px] rounded-[10px] bg-surface2 p-[3px]">
                            @foreach ($grades as $item)
                                @php
                                    $current = $item->is($grade);
                                @endphp
                                <a href="{{ route('progress.index', ['sinif' => $item->number]) }}"
                                   @if ($current) aria-current="page" @endif
                                   @class([
                                       'flex min-h-11 items-center justify-center rounded-[7px] px-3 text-sm no-underline',
                                       'bg-surface font-semibold text-ink' => $current,
                                       'text-ink2 hover:text-ink' => ! $current,
                                   ])>
                                    {{ $item->number }}<span class="sr-only">. Sınıf</span>
                                </a>
                            @endforeach
                        </nav>
                    @endif
                </div>

                @if ($masteries->isEmpty())
                    <p class="px-4 py-5 text-ink2 lg:px-6">{{ $grade?->number }}. sınıf konularında henüz soru çözmedin.</p>
                @else
                    <ul>
                        @foreach ($masteries as $row)
                            @php
                                $mastery = $row['mastery'];
                                $percentage = $mastery->displayPercentage();
                                $level = $mastery->mastery_level;
                            @endphp
                            <li class="border-b border-line last:border-b-0">
                                <a href="{{ $row['url'] }}" class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1.5 px-4 py-3 text-ink no-underline hover:bg-surface2 hover:text-ink lg:px-6 @2xl:min-h-14 @2xl:grid-cols-[minmax(0,1fr)_minmax(120px,260px)_56px_112px] @2xl:py-2">
                                    <span class="font-medium">{{ $mastery->topic->name }}</span>

                                    @if ($percentage === null)
                                        <span class="col-span-2 row-start-2 text-[13px] text-ink3 @2xl:col-span-3 @2xl:row-start-auto">{{ __('curriculum.mastery.0') }} ({{ $mastery->total_attempts }}/{{ $minimum }} soru)</span>
                                    @else
                                        <span aria-hidden="true" class="col-span-2 row-start-2 h-2 overflow-hidden rounded bg-surface2 @2xl:col-span-1 @2xl:row-start-auto">
                                            <span @class([
                                                'block h-full',
                                                'bg-ok' => $level === MasteryLevel::Mastered,
                                                'bg-err' => $level === MasteryLevel::Weak,
                                                'bg-ink2' => $level === MasteryLevel::Proficient || $level === MasteryLevel::Developing,
                                            ]) style="width: {{ $percentage }}%"></span>
                                        </span>
                                        <span @class(['col-start-2 row-start-1 text-right font-mono text-[15px] font-semibold @2xl:col-start-auto @2xl:row-start-auto @2xl:text-left', 'text-err' => $level === MasteryLevel::Weak])>%{{ $percentage }}</span>
                                        <x-student.mastery :level="$level" class="col-span-2 row-start-3 @2xl:col-span-1 @2xl:row-start-auto" />
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="flex flex-col gap-5">
                <section aria-labelledby="zayif-kazanimlar" class="overflow-hidden rounded-[14px] border border-line bg-surface">
                    <div class="flex flex-col gap-0.5 border-b border-line px-4 py-4 lg:px-5">
                        <h2 id="zayif-kazanimlar" class="text-lg font-semibold">Zayıf kazanımlar</h2>
                        <p class="text-[13px] text-ink3">Tüm sınıflar · son {{ TopicMastery::WINDOW }} soruda doğruluk &lt; %50</p>
                    </div>

                    @if ($weakObjectives === [])
                        <p class="px-4 py-4 text-[15px] text-ink2 lg:px-5">Şu an zayıf görünen bir kazanımın yok. Bir kazanımda en az {{ $minimum }} soru çözdüğünde burada değerlendirilir.</p>
                    @else
                        <ul>
                            @foreach ($weakObjectives as $objective)
                                <li class="flex items-center gap-3 border-b border-line px-4 py-3 last:border-b-0 lg:px-5">
                                    <span class="flex min-w-0 flex-1 flex-col">
                                        @if ($objective['code'])
                                            <span class="font-mono text-xs font-medium text-ink3">{{ $objective['code'] }}</span>
                                        @endif
                                        <span class="text-[15px]/snug">{{ $objective['description'] }}</span>
                                    </span>
                                    <span class="flex-none font-mono text-sm font-semibold text-err"><span aria-hidden="true">! </span>{{ $objective['correct'] }}/{{ $objective['total'] }}<span class="sr-only"> doğru</span></span>
                                    <a href="{{ $objective['url'] }}" class="btn-secondary min-h-11 flex-none px-3.5 text-sm">Çalış<span class="sr-only">: {{ $objective['description'] }}</span></a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section aria-labelledby="sureklilik" class="flex flex-col gap-3 rounded-[14px] border border-line bg-surface px-4 py-4 lg:px-5">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 id="sureklilik" class="text-lg font-semibold">Süreklilik</h2>
                        <p class="text-[15px]"><strong class="font-mono">{{ $calendar['filled'] }} / {{ count($calendar['days']) }}</strong> gün çalıştın</p>
                    </div>

                    <div aria-hidden="true" class="flex flex-col gap-1.5">
                        <div class="grid grid-cols-7 gap-1.5 text-center text-xs text-ink3">
                            @foreach (array_slice($calendar['days'], 0, 7) as $day)
                                <span>{{ $day['weekday'] }}</span>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach ($calendar['days'] as $day)
                                <span @class([
                                    'h-7 rounded',
                                    'bg-amber' => $day['filled'],
                                    'border border-line2' => ! $day['filled'],
                                    'outline-2 outline-offset-1 outline-ink' => $day['today'],
                                ])></span>
                            @endforeach
                        </div>
                    </div>

                    <p class="text-[13px] text-ink3">Son {{ count($calendar['days']) }} gün. Dolu = en az 1 soru çözüldü · Çerçeveli = bugün. Kaçırılan günler ceza değildir; yalnızca düzeni görmen için.</p>
                </section>
            </div>
        </div>
    @endif
</x-student.layout>
