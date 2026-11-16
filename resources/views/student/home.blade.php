@php
    $options = [
        ['key' => 'A', 'text' => '4 g', 'hint' => '4 g neden çıktı? 0,2 mol asidin kütlesi ile karıştırılıp 0,1 mol NaOH hesaplanmış olabilir.'],
        ['key' => 'B', 'text' => '8 g', 'hint' => '8 g neden çıktı? Katsayı 2 atlanıp mol oranı 1 : 1 alınırsa bu sonuca ulaşılır.'],
        ['key' => 'C', 'text' => '12 g', 'hint' => '12 g neden çıktı? Mol sayısı 0,3 alınmış; denklemdeki katsayıları yeniden kontrol et.'],
        ['key' => 'D', 'text' => '16 g', 'hint' => ''],
        ['key' => 'E', 'text' => '20 g', 'hint' => '20 g neden çıktı? Mol oranı 1 : 2,5 gibi alınmış; denkleştirilmiş denklemden oranı oku.'],
    ];

    $steps = [
        ['label' => 'Denklemi yaz', 'body' => '<span class="font-mono text-sm">H<sub>2</sub>SO<sub>4</sub> + 2NaOH → Na<sub>2</sub>SO<sub>4</sub> + 2H<sub>2</sub>O</span>'],
        ['label' => 'Mol oranı', 'body' => 'Mol oranı 1 : 2 → <span class="font-mono">0,2 × 2 = 0,4 mol</span> NaOH'],
        ['label' => 'Kütle', 'body' => '<span class="font-mono">m = n × M = 0,4 × 40 = 16 g</span>'],
    ];

    $grades = [
        9 => 'Kimya bilimi, atom ve periyodik sistem, etkileşimler',
        10 => 'Temel kanunlar, karışımlar, asitler-bazlar-tuzlar',
        11 => 'Gazlar, çözeltiler, enerji, hız ve denge',
        12 => 'Elektrokimya, karbon ve organik kimya, enerji kaynakları',
    ];

    $faq = [
        'Ücretli mi?' => 'Müfredatı incelemek ücretsiz ve hesap gerektirmez. Ücretli planların ayrıntıları ürün ekibi tarafından netleştirilecek.',
        'İnternetim yavaşsa çalışır mı?' => 'Evet. Sayfalar hafif; video ve büyük görsel yok.',
        'Sadece YKS için mi?' => 'Hayır. Okul derslerine sınıf sınıf eşlik eder; YKS adayları tüm sınıfları birlikte tekrar edebilir.',
        'Ustalık düzeyi nasıl hesaplanıyor?' => 'Bir konudaki son 20 sorunun doğruluk oranı. En az 5 soru çözmeden düzey gösterilmez.',
    ];
@endphp

<x-student.document description="MEB kimya müfredatı 9–12. sınıf: her soru bir kazanıma bağlı, yanlışın nereden geldiğini adım adım gör.">
    <a href="#dene" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-20 focus:rounded-lg focus:bg-surface focus:px-4 focus:py-3">Örnek soruya geç</a>

    <header class="sticky top-0 z-10 border-b border-line bg-bg">
        <div class="mx-auto flex min-h-[68px] max-w-[1200px] items-center gap-4 px-5">
            <x-student.logo />
            <nav aria-label="Sayfa içi" class="hidden flex-1 flex-wrap justify-center gap-0.5 md:flex">
                <a href="#dene" class="flex min-h-11 items-center px-3 text-[15px] text-ink2 no-underline">Örnek soru</a>
                <a href="#nasil" class="flex min-h-11 items-center px-3 text-[15px] text-ink2 no-underline">Nasıl çalışır</a>
                <a href="#mufredat" class="flex min-h-11 items-center px-3 text-[15px] text-ink2 no-underline">Müfredat</a>
                <a href="#sss" class="flex min-h-11 items-center px-3 text-[15px] text-ink2 no-underline">SSS</a>
            </nav>
            @auth
                <a href="{{ route('curriculum.index') }}" class="ml-auto flex min-h-11 items-center px-3 text-[15px] font-semibold md:ml-0">Müfredata git</a>
            @else
                <a href="{{ route('login') }}" class="ml-auto flex min-h-11 items-center px-3 text-[15px] font-semibold md:ml-0">Giriş yap</a>
                <a href="{{ route('register') }}" class="btn-secondary max-sm:hidden">Ücretsiz hesap aç</a>
            @endauth
        </div>
    </header>

    <main>
        <section class="border-b border-line">
            <div class="mx-auto grid max-w-[1200px] grid-cols-[repeat(auto-fit,minmax(min(100%,460px),1fr))] items-center gap-[clamp(32px,5vw,64px)] px-5 py-[clamp(40px,7vw,80px)]">
                <div class="flex flex-col gap-[22px]">
                    <span class="eyebrow">9–12. SINIF · TYT / AYT · MEB MÜFREDATI</span>
                    <h1 class="text-[clamp(34px,5vw,54px)] leading-[1.1] font-semibold tracking-[-.015em] text-balance">Kimyayı ezberleme, <span class="text-amber-ink">nedenini</span> anla.</h1>
                    <p class="max-w-[520px] text-lg/[1.6] text-pretty text-ink2">Her soru bir MEB kazanımına bağlı. Yanlış yaptığında hatanın nereden geldiğini adım adım görürsün, sonra tam o kazanımı yeniden çalışırsın.</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('curriculum.index') }}" class="btn-primary min-h-[52px] px-[26px]">Müfredatı incele</a>
                        <a href="#dene" class="flex min-h-[52px] items-center px-3.5 font-semibold">Önce bir soru dene ↓</a>
                    </div>
                    <p class="text-sm text-ink3">Telefonda ve yavaş bağlantıda çalışır.</p>
                </div>

                <div id="dene" data-sample-question data-correct="D" class="flex scroll-mt-[88px] flex-col gap-4 rounded-2xl border border-line bg-surface p-[clamp(18px,3vw,28px)]">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="font-mono text-[13px] font-medium text-ink3">KIM.10.3.2.3 · Uygulama</span>
                        <span class="text-[13px] font-semibold text-amber-ink">ÖRNEK SORU · kayıt gerekmez</span>
                    </div>

                    <p id="ornek-soru" class="text-lg/[1.65] text-pretty">0,2 mol <span class="font-mono">H<sub>2</sub>SO<sub>4</sub></span> içeren sulu çözeltiyi tamamen nötralleştirmek için kaç gram <span class="font-mono">NaOH</span> gerekir? <span class="text-ink3">(NaOH: 40 g/mol)</span></p>

                    <div role="radiogroup" aria-labelledby="ornek-soru" class="flex flex-col gap-2">
                        @foreach ($options as $option)
                            <button type="button" role="radio" aria-checked="false" class="option" data-option="{{ $option['key'] }}" data-hint="{{ $option['hint'] }}">
                                <span class="option-key">{{ $option['key'] }}</span>
                                <span class="flex-1" data-text>{{ $option['text'] }}</span>
                                <span class="text-[13px] font-semibold text-ink3" data-tag></span>
                            </button>
                        @endforeach
                    </div>

                    <button type="button" data-check disabled class="btn-primary min-h-[52px] cursor-pointer disabled:cursor-not-allowed disabled:bg-surface2 disabled:text-ink3">Kontrol et</button>

                    <div data-result-panel hidden class="flex flex-col gap-3.5">
                        <div role="status" tabindex="-1" data-status class="flex items-center gap-3 rounded-xl px-3.5 py-3">
                            <span data-status-icon aria-hidden="true" class="grid size-7 flex-none place-items-center rounded-full font-bold"></span>
                            <span data-status-text class="font-semibold"></span>
                        </div>

                        <div class="flex flex-col gap-2.5 rounded-xl border border-line p-4">
                            <span class="text-[15px] font-semibold">Adım adım çözüm</span>
                            @foreach ($steps as $step)
                                <div class="grid grid-cols-[24px_1fr] gap-2.5 text-[15px]/normal">
                                    <span aria-hidden="true" class="grid size-6 place-items-center rounded-[5px] bg-surface2 font-mono text-xs font-semibold">{{ $loop->iteration }}</span>
                                    <span>{!! $step['body'] !!}</span>
                                </div>
                            @endforeach
                            <div data-hint-panel hidden class="flex gap-2.5 rounded-[10px] bg-warn-soft px-3 py-2.5 text-sm/[1.45]">
                                <span aria-hidden="true" class="flex-none font-bold text-warn">!</span>
                                <span data-hint-text></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2.5">
                            <a href="{{ route('curriculum.index') }}" class="btn-primary min-h-[52px] min-w-[180px] flex-1 text-base">Kazanımları incele</a>
                            <button type="button" data-reset class="btn-secondary min-h-[52px]">Yeniden dene</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="nasil" aria-labelledby="nasil-baslik" class="scroll-mt-[68px] border-b border-line">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-9 px-5 py-[clamp(48px,7vw,88px)]">
                <div class="flex max-w-[640px] flex-col gap-2.5">
                    <span class="eyebrow">NASIL ÇALIŞIR</span>
                    <h2 id="nasil-baslik" class="text-[clamp(26px,3.4vw,36px)] leading-[1.2] font-semibold text-balance">Her gün tek bir öneri. Neden o olduğu da yazar.</h2>
                </div>
                <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,260px),1fr))] gap-5">
                    @foreach ([
                        ['Sç', 'Sınıfını seç', '9–12. sınıf üniteleri MEB sırasıyla. YKS’ye hazırlanıyorsan tüm yılları birlikte tekrar edebilirsin.'],
                        ['Çz', 'YKS formatında çöz', '5 şıklı sorular, isteğe bağlı süre. Her cevaptan sonra adım adım çözüm ve seçtiğin yanlışın açıklaması.'],
                        ['Tk', 'Zayıf kazanımı tekrar et', 'Son 20 sorudaki doğruluğun %50’nin altındaysa o kazanım öneriye girer. Puan ya da sıralama yok; yalnızca neyi bildiğin.'],
                    ] as [$symbol, $heading, $body])
                        <div class="flex flex-col gap-3.5 rounded-[14px] border border-line bg-surface p-6">
                            <x-student.cell :symbol="$symbol" :number="$loop->iteration" :active="$loop->last" />
                            <h3 class="text-[19px] font-semibold">{{ $heading }}</h3>
                            <p class="text-pretty text-ink2">{{ $body }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="mufredat" aria-labelledby="mufredat-baslik" class="scroll-mt-[68px] border-b border-line bg-surface">
            <div class="mx-auto grid max-w-[1200px] grid-cols-[repeat(auto-fit,minmax(min(100%,420px),1fr))] items-start gap-10 px-5 py-[clamp(48px,7vw,88px)]">
                <div class="flex flex-col gap-3.5">
                    <span class="eyebrow">MÜFREDAT</span>
                    <h2 id="mufredat-baslik" class="text-[clamp(26px,3.4vw,36px)] leading-[1.2] font-semibold text-balance">MEB kazanımlarıyla birebir.</h2>
                    <p class="text-[17px] text-pretty text-ink2">İçerik Program → Sınıf → Ünite → Konu → Kazanım olarak düzenli. Her kazanımın kodu ve Bloom basamağı görünür, böylece hatırlama sorusu mu analiz sorusu mu çözdüğünü bilirsin.</p>
                    <div class="flex flex-col gap-2 rounded-xl border border-line bg-bg p-4">
                        <span class="font-mono text-[13px] font-medium text-ink3">KIM.10.3.2.3</span>
                        <span>Nötralleşme tepkimelerinde madde miktarı hesaplamaları yapar.</span>
                        <x-student.bloom :level="\App\Enums\BloomLevel::Apply" />
                    </div>
                    <a href="{{ route('curriculum.index') }}" class="self-start font-semibold">Müfredatı incele →</a>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($grades as $number => $summary)
                        <div @class([
                            'flex aspect-square flex-col justify-between rounded-[10px] border-[1.5px] p-4',
                            'border-ink bg-amber text-on-amber' => $loop->last,
                            'border-line2 bg-bg' => ! $loop->last,
                        ])>
                            <span @class(['font-mono text-[13px] font-medium', 'text-ink3' => ! $loop->last])>{{ $number }}</span>
                            <span class="font-mono text-[clamp(28px,4vw,40px)] font-semibold">{{ $number }}.</span>
                            <span @class(['text-[15px] max-sm:text-sm', 'text-ink2' => ! $loop->last])>{{ $summary }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="sss" aria-labelledby="sss-baslik" class="scroll-mt-[68px] border-b border-line">
            <div class="mx-auto flex max-w-[820px] flex-col gap-6 px-5 py-[clamp(48px,7vw,88px)]">
                <div class="flex flex-col gap-2.5">
                    <span class="eyebrow">SIK SORULANLAR</span>
                    <h2 id="sss-baslik" class="text-[clamp(26px,3.4vw,36px)] leading-[1.2] font-semibold">Başlamadan önce</h2>
                </div>
                <div class="border-t border-line">
                    @foreach ($faq as $question => $answer)
                        <details class="group border-b border-line">
                            <summary class="flex min-h-[60px] cursor-pointer items-center justify-between gap-4 text-[17px] font-medium">
                                {{ $question }}<span aria-hidden="true" class="text-xl text-ink3 group-open:hidden">+</span><span aria-hidden="true" class="hidden text-xl text-ink3 group-open:inline">−</span>
                            </summary>
                            <p class="mb-5 text-ink2">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="border-b border-line">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-6 px-5 py-[clamp(48px,7vw,80px)]">
                <h2 class="max-w-[560px] text-[clamp(24px,3vw,32px)] leading-[1.25] font-semibold text-balance">Bugün bir kazanımla başla. 20 dakika yeter.</h2>
                @auth
                    <a href="{{ route('curriculum.index') }}" class="btn-primary min-h-[52px] px-7">Müfredata git</a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary min-h-[52px] px-7">Ücretsiz hesap aç</a>
                @endauth
            </div>
        </section>
    </main>

    <footer class="mx-auto flex max-w-[1200px] flex-wrap justify-between gap-x-6 gap-y-4 px-5 pt-7 pb-10 text-sm text-ink3">
        <span>© {{ date('Y') }} KimyaÖğren</span>
        <nav aria-label="Yasal" class="flex flex-wrap gap-x-5 gap-y-2">
            <a href="{{ route('legal.terms') }}" class="text-ink3">Kullanım koşulları</a>
            <a href="{{ route('legal.kvkk') }}" class="text-ink3">KVKK aydınlatma metni</a>
        </nav>
    </footer>
</x-student.document>
