@php
    $gradeUrl = route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number]);
    $unitUrl = route('curriculum.grade', ['program' => $program->slug, 'grade' => $grade->number, 'unite' => $unit->code]);
    $topicUrl = route('curriculum.topic', ['program' => $program->slug, 'grade' => $grade->number, 'unit' => $unit->code, 'topic' => $topic->slug]);
    $description = $article->meta_description ?: $article->excerpt;
@endphp

<x-student.layout :title="$article->meta_title ?: $article->title" :description="$description" :program="$program">
    <x-slot:head>
        <link rel="canonical" href="{{ $article->canonical_url ?: $article->publicUrl() }}">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $article->title }}">
        <meta property="og:url" content="{{ $article->publicUrl() }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        @if ($article->featured_image_path)
            <meta property="og:image" content="{{ url($article->featured_image_path) }}">
        @endif
        @if ($article->published_at)
            <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
        @endif
    </x-slot:head>

    <article class="mx-auto flex max-w-[760px] flex-col gap-6">
        <header class="flex flex-col gap-2.5">
            <nav aria-label="Konum" class="flex flex-wrap items-center gap-2 text-sm text-ink3">
                <a href="{{ $gradeUrl }}" class="text-ink3">{{ $grade->number }}. Sınıf</a><span aria-hidden="true">›</span>
                <a href="{{ $unitUrl }}" class="text-ink3">{{ $unit->name }}</a><span aria-hidden="true">›</span>
                <a href="{{ $topicUrl }}" class="text-ink3">{{ $topic->name }}</a>
            </nav>

            <h1 class="text-2xl/tight font-semibold text-balance lg:text-[32px]/tight">{{ $article->title }}</h1>

            <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink3">
                @if ($article->published_at)
                    <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->translatedFormat('j F Y') }}</time>
                @endif
                @if ($article->read_time_minutes)
                    <span>≈ {{ $article->read_time_minutes }} dk okuma</span>
                @endif
            </p>
        </header>

        @if ($article->featured_image_path)
            <img src="{{ $article->featured_image_path }}" alt="" class="w-full rounded-[14px] border border-line" decoding="async">
        @endif

        <div class="article-content">
            {!! $article->currentVersion->content !!}
        </div>

        <nav aria-label="Konudaki diğer anlatımlar" class="grid gap-3 border-t border-line pt-6 sm:grid-cols-2">
            @if ($previous)
                <a href="{{ $previous->publicUrl() }}" class="flex flex-col gap-1 rounded-[14px] border border-line bg-surface p-4 text-ink no-underline hover:border-line2 hover:text-ink">
                    <span class="text-[13px] text-ink3">‹ Önceki</span>
                    <span class="font-medium">{{ $previous->title }}</span>
                </a>
            @else
                <span class="max-sm:hidden"></span>
            @endif
            @if ($next)
                <a href="{{ $next->publicUrl() }}" class="flex flex-col gap-1 rounded-[14px] border border-line bg-surface p-4 text-right text-ink no-underline hover:border-line2 hover:text-ink">
                    <span class="text-[13px] text-ink3">Sonraki ›</span>
                    <span class="font-medium">{{ $next->title }}</span>
                </a>
            @endif
        </nav>

        <a href="{{ $topicUrl }}" class="btn-secondary self-start">Konuya dön: {{ $topic->name }}</a>
    </article>
</x-student.layout>
