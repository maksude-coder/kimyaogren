@php
    $gradeUrl = route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number]);
    $unitUrl = route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number, 'unite' => $unit->code]);
@endphp

<x-student.layout :title="$topic->meta_title ?: $topic->name" :program="$program">
    <div class="grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="flex flex-col gap-7">
            <div class="flex flex-col gap-2.5">
                <nav aria-label="Konum" class="flex flex-wrap items-center gap-2 text-sm text-ink3">
                    <a href="{{ route('curriculum.index') }}" class="text-ink3 max-lg:hidden">Müfredat</a><span aria-hidden="true" class="max-lg:hidden">›</span>
                    <a href="{{ $gradeUrl }}" class="text-ink3">{{ $grade->number }}. Sınıf</a><span aria-hidden="true">›</span>
                    <a href="{{ $unitUrl }}" class="text-ink3">Ünite {{ $unitPosition }}<span class="max-lg:hidden"> · {{ $unit->name }}</span></a>
                </nav>

                <h1 class="text-2xl/tight font-semibold lg:text-[32px]/tight">
                    <span class="font-mono text-ink3">{{ $topicNumber }}</span> {{ $topic->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-sm text-ink2">
                    <x-student.difficulty :value="$topic->difficulty" :label="'Zorluk '.$topic->difficulty.'/5 · '.__('curriculum.difficulty.'.$topic->difficulty)" />
                    @if ($articles->isNotEmpty())
                        <span>{{ $articles->count() }} konu anlatımı</span>
                    @endif
                    @if ($objectives->isNotEmpty())
                        <span>{{ $objectives->count() }} kazanım</span>
                    @endif
                </div>
            </div>

            @if ($topic->description)
                <p class="max-w-[720px] text-[17px]/[1.65] text-pretty text-ink2">{{ $topic->description }}</p>
            @endif

            @if ($articles->isNotEmpty())
                <section aria-labelledby="anlatimlar" class="flex flex-col gap-3">
                    <h2 id="anlatimlar" class="text-lg font-semibold lg:text-xl">Konu anlatımları</h2>
                    <ol class="overflow-hidden rounded-[14px] border border-line bg-surface">
                        @foreach ($articles as $article)
                            <li class="border-b border-line last:border-b-0">
                                <a href="{{ $article->publicUrl() }}" class="flex items-start gap-3.5 px-3.5 py-3.5 text-ink no-underline hover:bg-surface2 hover:text-ink lg:px-5">
                                    <span class="mt-0.5 grid size-7 flex-none place-items-center rounded-[5px] bg-surface2 font-mono text-xs font-semibold text-ink2">{{ $loop->iteration }}</span>
                                    <span class="flex min-w-0 flex-col gap-0.5">
                                        <span class="font-medium">{{ $article->title }}</span>
                                        @if ($article->read_time_minutes)
                                            <span class="text-[13px] text-ink3">≈ {{ $article->read_time_minutes }} dk okuma</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            <section aria-labelledby="kazanimlar" @class(['flex flex-col gap-3', 'hidden' => $objectives->isEmpty() && $articles->isNotEmpty()])>
                <h2 id="kazanimlar" class="text-lg font-semibold lg:text-xl">Kazanımlar</h2>

                @if ($objectives->isEmpty())
                    <p class="rounded-[14px] border border-line bg-surface p-5 text-ink3">Bu konu için henüz kazanım eklenmedi.</p>
                @else
                    <ol class="overflow-hidden rounded-[14px] border border-line bg-surface">
                        @foreach ($objectives as $objective)
                            <li class="flex flex-col gap-1.5 border-b border-line px-3.5 py-3 last:border-b-0 lg:grid lg:min-h-[60px] lg:grid-cols-[140px_minmax(0,1fr)_150px] lg:items-center lg:gap-4 lg:px-5">
                                <span class="font-mono text-[13px] font-medium text-ink3 lg:text-sm">{{ $objective->code }}</span>
                                <span class="text-[15px]/snug lg:text-base">{{ $objective->description }}</span>
                                <x-student.bloom :level="$objective->bloom_level" />
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <aside class="flex flex-col gap-3 rounded-[14px] border border-line bg-surface p-6 lg:sticky lg:top-8">
            <h2 class="text-lg font-semibold">Soru çöz</h2>
            <p class="text-[15px] text-ink2">Bu konunun soruları hazırlanıyor. Soru bankası açıldığında buradan 10, 20 ya da 40 soruluk bir oturum başlatabileceksin.</p>
            <a href="{{ $unitUrl }}" class="btn-secondary mt-1">Üniteye dön</a>
        </aside>
    </div>
</x-student.layout>
