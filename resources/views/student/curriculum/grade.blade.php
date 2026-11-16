@php
    use App\Support\PeriodicSymbol;

    $unitUrl = fn ($unit) => route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number, 'unite' => $unit->code]).'#unite-'.$unit->id;
    $topicUrl = fn ($topic) => route('curriculum.topic', ['program' => $program->slug, 'grade' => $grade->number, 'unit' => $selectedUnit->code, 'topic' => $topic->slug]);
    $selectedPosition = $selectedUnit ? $units->search(fn ($unit) => $unit->is($selectedUnit)) + 1 : null;
@endphp

<x-student.layout :title="$grade->number.'. Sınıf · Müfredat'" :program="$program" class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between lg:gap-6">
        <div>
            <p class="text-sm text-ink3 lg:text-[15px]">{{ $program->name }}</p>
            <h1 class="text-2xl/tight font-semibold lg:text-[28px]/[34px]">Müfredat</h1>
        </div>

        <nav aria-label="Sınıflar" class="grid auto-cols-fr grid-flow-col gap-1 rounded-xl bg-surface2 p-1 lg:flex">
            @foreach ($grades as $item)
                @php($current = $item->is($grade))
                <a href="{{ route('curriculum.grade', ['program' => $program->slug, 'grade' => $item->number]) }}"
                   @if ($current) aria-current="page" @endif
                   @class([
                       'flex min-h-11 items-center justify-center rounded-[9px] px-2 text-[15px] no-underline lg:px-5',
                       'bg-surface font-semibold text-ink shadow-[0_1px_2px_rgba(0,0,0,.08)]' => $current,
                       'font-medium text-ink2 hover:text-ink' => ! $current,
                   ])>
                    @if ($current)
                        {{ $item->number }}. Sınıf
                    @else
                        {{ $item->number }}<span class="max-lg:sr-only">. Sınıf</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    @if ($units->isEmpty())
        <div class="rounded-[14px] border border-line bg-surface p-6 text-ink2">
            <p class="font-semibold text-ink">Bu sınıfta henüz yayımlanmış ünite yok.</p>
            <p class="mt-1">Başka bir sınıfı seçebilir ya da daha sonra tekrar bakabilirsin.</p>
        </div>
    @else
        <div class="grid items-start gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
            <section aria-labelledby="uniteler" class="flex flex-col gap-2.5">
                <h2 id="uniteler" class="text-[13px] font-semibold tracking-[.04em] text-ink3">ÜNİTELER · {{ $units->count() }}</h2>

                @foreach ($units as $unit)
                    @php($selected = $unit->is($selectedUnit))
                    <div id="unite-{{ $unit->id }}" @class([
                        'scroll-mt-20 overflow-hidden rounded-[14px] bg-surface',
                        'border-2 border-amber-ink' => $selected,
                        'border border-line' => ! $selected,
                    ])>
                        <a href="{{ $unitUrl($unit) }}" @if ($selected) aria-current="true" @endif @class(['flex items-center gap-3.5 text-ink no-underline hover:text-ink', 'p-[13px]' => $selected, 'p-3.5' => ! $selected])>
                            <x-student.cell :symbol="PeriodicSymbol::for($unit->name)" :number="$loop->iteration" :active="$selected" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-base/snug font-semibold">{{ $unit->name }}</span>
                                <span class="block text-[13px] text-ink3">
                                    {{ $unit->topics_count > 0 ? $unit->topics_count.' konu' : 'Henüz konu yok' }}
                                </span>
                            </span>
                            <span aria-hidden="true" @class(['text-ink3 lg:hidden', 'rotate-90' => $selected])>›</span>
                        </a>

                        @if ($selected)
                            {{-- Mobile: the selected unit opens in place (§ 03). --}}
                            <ul class="border-t border-line lg:hidden">
                                @forelse ($topics as $topic)
                                    <li class="border-b border-line last:border-b-0">
                                        <a href="{{ $topicUrl($topic) }}" class="flex flex-col gap-2 px-4 py-3.5 text-ink no-underline hover:bg-surface2 hover:text-ink">
                                            <span class="text-base font-medium">{{ $selectedPosition }}.{{ $loop->iteration }} {{ $topic->name }}</span>
                                            <span class="flex items-center gap-3 text-[13px] text-ink3">
                                                <x-student.difficulty :value="$topic->difficulty" :label="'Zorluk '.$topic->difficulty.'/5'" />
                                                @if ($topic->theory_articles_count)
                                                    · {{ $topic->theory_articles_count }} anlatım
                                                @endif
                                                @if ($topic->learning_objectives_count)
                                                    · {{ $topic->learning_objectives_count }} kazanım
                                                @endif
                                            </span>
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-4 py-3.5 text-sm text-ink3">Bu ünitede henüz yayımlanmış konu yok.</li>
                                @endforelse
                            </ul>
                        @endif
                    </div>
                @endforeach
            </section>

            @if ($selectedUnit)
                {{-- Desktop: the selected unit's topics beside the list (§ 03). --}}
                <section aria-labelledby="secili-unite" class="hidden overflow-hidden rounded-[14px] border border-line bg-surface lg:block">
                    <div class="flex items-baseline justify-between gap-4 border-b border-line px-6 py-5">
                        <h2 id="secili-unite" class="text-xl font-semibold">Ünite {{ $selectedPosition }} · {{ $selectedUnit->name }}</h2>
                        <span class="text-sm whitespace-nowrap text-ink3">{{ $topics->sum('theory_articles_count') }} anlatım · {{ $topics->sum('learning_objectives_count') }} kazanım</span>
                    </div>

                    @if ($topics->isEmpty())
                        <p class="px-6 py-5 text-ink3">Bu ünitede henüz yayımlanmış konu yok.</p>
                    @else
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-line text-[13px] font-semibold text-ink3">
                                    <th scope="col" class="py-2.5 pl-6 font-semibold">Konu</th>
                                    <th scope="col" class="w-44 py-2.5 font-semibold">Zorluk</th>
                                    <th scope="col" class="w-28 py-2.5 font-semibold">Anlatım</th>
                                    <th scope="col" class="w-28 py-2.5 pr-6 font-semibold">Kazanım</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topics as $topic)
                                    <tr class="border-b border-line last:border-b-0 hover:bg-surface2">
                                        <td class="h-16 pl-6">
                                            <a href="{{ $topicUrl($topic) }}" class="text-base font-medium text-ink hover:text-amber-ink">{{ $selectedPosition }}.{{ $loop->iteration }} {{ $topic->name }}</a>
                                        </td>
                                        <td class="text-[13px] text-ink2"><x-student.difficulty :value="$topic->difficulty" /></td>
                                        <td class="font-mono text-sm font-medium">{{ $topic->theory_articles_count }}</td>
                                        <td class="pr-6 font-mono text-sm font-medium">{{ $topic->learning_objectives_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            @endif
        </div>
    @endif
</x-student.layout>
