@props(['title' => null, 'description' => null, 'program' => null, 'head' => null])

{{--
    Student app shell (§ 02–04): sidebar on desktop, top bar on mobile.
    Only destinations that exist are listed (Bugün / İlerleme / Profil
    arrive with their domains — Sprint 2.1 plan § 2).
--}}
@php($user = auth()->user())

<x-student.document :title="$title" :description="$description" :head="$head">
    <a href="#icerik" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-20 focus:rounded-lg focus:bg-surface focus:px-4 focus:py-3">İçeriğe geç</a>

    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <header class="sticky top-0 z-10 flex min-h-16 items-center gap-2 border-b border-line bg-bg px-5 lg:hidden">
            <x-student.logo />
            <nav aria-label="Ana menü" class="ml-auto flex items-center">
                <a href="{{ route('curriculum.index') }}" @if (request()->routeIs('curriculum.*')) aria-current="page" @endif class="flex min-h-11 items-center px-3 text-[15px] font-semibold text-amber-ink">Müfredat</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex min-h-11 cursor-pointer items-center px-3 text-[15px] font-semibold text-ink2">Çıkış</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex min-h-11 items-center px-3 text-[15px] font-semibold">Giriş yap</a>
                @endauth
            </nav>
        </header>

        <aside class="hidden border-r border-line bg-surface px-4 py-6 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:gap-7">
            <x-student.logo class="px-2" />

            <nav aria-label="Ana menü" class="flex flex-col gap-1">
                <a href="{{ route('curriculum.index') }}" @if (request()->routeIs('curriculum.*')) aria-current="page" @endif class="flex min-h-11 items-center gap-3 rounded-lg bg-amber-soft px-3 font-semibold text-ink no-underline">
                    <span aria-hidden="true" class="size-4 rounded-[3px] bg-amber-ink"></span>Müfredat
                </a>
            </nav>

            <div class="mt-auto flex flex-col gap-3">
                @if ($program)
                    <div class="rounded-[10px] border border-line p-3 text-[13px] text-ink3">
                        Program<br><span class="text-sm font-medium text-ink">{{ $program->name }}</span>
                    </div>
                @endif

                @auth
                    <div class="flex flex-col gap-1 rounded-[10px] border border-line p-3 text-[13px] text-ink3">
                        <span class="truncate text-sm font-medium text-ink">{{ $user->name }}</span>
                        @if ($user->is_graduate)
                            <span>Mezun · YKS hazırlık</span>
                        @elseif ($user->grade)
                            <span>{{ $user->grade->number }}. Sınıf</span>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex min-h-11 cursor-pointer items-center text-sm font-semibold text-amber-ink hover:text-ink">Çıkış yap</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">Giriş yap</a>
                    <a href="{{ route('register') }}" class="flex min-h-11 items-center justify-center text-[15px] font-semibold">Ücretsiz hesap aç</a>
                @endauth
            </div>
        </aside>

        <main id="icerik" {{ $attributes->class('px-5 pt-4 pb-10 lg:px-14 lg:pt-10') }}>
            @if (session('status'))
                <p role="status" class="mb-5 flex gap-2.5 rounded-[10px] bg-ok-soft px-3.5 py-3 text-[15px] font-medium text-ok">
                    <span aria-hidden="true" class="font-bold">✓</span>{{ session('status') }}
                </p>
            @endif

            @auth
                @if ($user->needsGradeSelection())
                    <p class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-[10px] bg-warn-soft px-3.5 py-3 text-[15px]">
                        <span aria-hidden="true" class="font-bold text-warn">!</span>
                        <span class="flex-1">Sınıfını seçersen müfredat kendi sınıfınla açılır.</span>
                        <a href="{{ route('register.grade') }}" class="font-semibold">Sınıfını seç</a>
                    </p>
                @elseif (! $user->hasVerifiedEmail())
                    <form method="POST" action="{{ route('verification.send') }}" class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-[10px] bg-warn-soft px-3.5 py-3 text-[15px]">
                        @csrf
                        <span aria-hidden="true" class="font-bold text-warn">!</span>
                        <span class="flex-1">E-posta adresini doğrulamak için gelen kutundaki bağlantıya tıkla.</span>
                        <button type="submit" class="cursor-pointer font-semibold text-amber-ink hover:text-ink">Yeniden gönder</button>
                    </form>
                @endif
            @endauth

            {{ $slot }}
        </main>
    </div>
</x-student.document>
