@props(['title', 'step' => null])

{{--
    Registration / login shell (§ 08): one column on mobile, brand panel
    beside the form on desktop. Progress does not exist yet, so the panel
    carries the product promise instead of "Kaldığın kazanım seni bekliyor."
--}}
<x-student.document :title="$title">
    <x-slot:head>
        <meta name="robots" content="noindex">
    </x-slot:head>

    <a href="#icerik" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-20 focus:rounded-lg focus:bg-surface focus:px-4 focus:py-3">İçeriğe geç</a>

    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <aside class="hidden flex-col justify-between border-r border-line bg-surface px-[72px] py-14 lg:flex">
            <x-student.logo />

            <div class="flex max-w-[480px] flex-col gap-5">
                <div aria-hidden="true" class="grid grid-cols-[repeat(4,72px)] gap-2">
                    @foreach ([['H', 1], ['C', 6], ['O', 8], ['Na', 11]] as [$symbol, $number])
                        <span @class([
                            'relative grid h-[72px] place-items-center rounded-md border-[1.5px] font-mono text-[22px] font-semibold',
                            'border-ink bg-amber text-on-amber' => $loop->last,
                            'border-line2' => ! $loop->last,
                        ])>
                            {{ $symbol }}<span @class(['absolute top-1 left-1.5 text-[10px] leading-none', 'text-ink3' => ! $loop->last])>{{ $number }}</span>
                        </span>
                    @endforeach
                </div>
                <p class="text-2xl/[1.4] font-medium text-pretty">Kimyayı ezberleme, nedenini anla.</p>
            </div>

            <span class="text-[13px] text-ink3">Lise Kimya MEB · 9–12. sınıf · TYT/AYT</span>
        </aside>

        <main id="icerik" class="flex min-h-screen flex-col px-6 pt-4 pb-7 lg:min-h-0 lg:items-center lg:justify-center lg:p-14">
            <div class="flex w-full max-w-[400px] flex-1 flex-col gap-5 max-lg:mx-auto lg:flex-none">
                <div class="flex min-h-11 items-center justify-between lg:hidden">
                    <x-student.logo />
                    @if ($step)
                        <span class="font-mono text-[13px] font-medium text-ink3">Adım {{ $step }} / 2</span>
                    @endif
                </div>

                @if ($step)
                    <span class="hidden font-mono text-[13px] font-medium text-ink3 lg:block">Adım {{ $step }} / 2</span>
                @endif

                @if (session('status'))
                    <p role="status" class="flex gap-2.5 rounded-[10px] bg-ok-soft px-3.5 py-3 text-[15px] font-medium text-ok">
                        <span aria-hidden="true" class="font-bold">✓</span>{{ session('status') }}
                    </p>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</x-student.document>
