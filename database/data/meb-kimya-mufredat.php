<?php

declare(strict_types=1);

/*
 * MEB Ortaöğretim Kimya (2018) curriculum skeleton and the mapping of every
 * kimyaogren.com WordPress post (by its legacy slug) onto a topic.
 *
 * Shape: grade number => unit order => [code, name, topics => topic order => [name, difficulty, posts => [legacy slugs]]]
 * Post order inside a topic is taken from the WordPress publication date,
 * not from this list. Topics with no posts are imported as Draft.
 *
 * "Ünite Tekrarı" topics (order 90) hold unit-wide review posts
 * ("Yazılıya Hazırlık", cross-topic question sets); they are not MEB topics.
 *
 * Used by: php artisan kimyaogren:import-wordpress (Sprint 2.2).
 */

return [
    'program' => [
        'code' => 'KIM_MEB_TR',
        'name' => 'Lise Kimya (MEB)',
        'slug' => 'lise-kimya',
        'short_description' => 'MEB ortaöğretim kimya dersi öğretim programı, 9–12. sınıf.',
    ],

    'grades' => [
        9 => [
            1 => ['code' => '9.1', 'name' => 'Kimya Bilimi', 'topics' => [
                1 => ['name' => 'Simyadan Kimyaya', 'difficulty' => 1, 'posts' => ['simyadan-kimyaya', '548-2']],
                2 => ['name' => 'Kimya Disiplinleri ve Kimyacıların Çalışma Alanları', 'difficulty' => 1, 'posts' => ['kimya-disiplinleri-ve-kimyacilarin-calisma-alanlari']],
                3 => ['name' => 'Kimyanın Sembolik Dili', 'difficulty' => 2, 'posts' => ['elementler', 'bilesikler']],
                4 => ['name' => 'Kimya Uygulamalarında İş Sağlığı ve Güvenliği', 'difficulty' => 1, 'posts' => ['laboratuvarda-uyulmasi-gereken-kurallar', 'guvenlik-ve-uyari-isaretleri', 'bazi-kimyasal-maddelerin-insan-sagligi-ve-cevreye-etkileri']],
                90 => ['name' => 'Ünite Tekrarı', 'difficulty' => 2, 'posts' => ['kimya-bilimi-ornek-soru-cozumleri', 'kimya-bilimi-unitesi-yaziliya-hazirlik-calismasi']],
            ]],
            2 => ['code' => '9.2', 'name' => 'Atom ve Periyodik Sistem', 'topics' => [
                1 => ['name' => 'Atom Modelleri', 'difficulty' => 2, 'posts' => ['atom-modelleri-dalton-atom-modeli', 'atom-modelleri-thomson-atom-modeli', 'atom-modelleri-rutherford-atom-modeli', '243-2', 'atom-modelleri-soru-cozumleri']],
                2 => ['name' => 'Atomun Yapısı', 'difficulty' => 2, 'posts' => ['atomu-olusturan-tanecikler-1', 'atomu-olusturan-tanecikler-2', 'atom-ile-ilgili-kavramlar', 'atom-ile-ilgili-kavramlar-soru-cozumleri']],
                3 => ['name' => 'Periyodik Sistem', 'difficulty' => 3, 'posts' => ['periyodik-sistemin-tarihcesi-ve-ozellikleri', 'atomlarin-elektron-katman-dizilimi', 'periyodik-sistemde-yer-bulma', 'elementlerin-siniflandirilmasi', 'metallerin-ozellikleri', 'ametallerin-ozellikleri', 'yari-metallerin-ozellikleri', 'soy-gazlarin-ozellikleri', 'atom-yaricapi', 'iyonlasma-enerjisi-2', 'periyodik-ozellikler-soru-cozumleri']],
                90 => ['name' => 'Ünite Tekrarı', 'difficulty' => 2, 'posts' => ['kimya-bilimi-atomun-yapisi-yaziliya-hazirlik-calismasi-1', 'kimya-bilimi-atomun-yapisi-yaziliya-hazirlik-calismasi-2', 'atomun-yapisi-ve-periyodik-cetvel-yaziliya-hazirlik-calismasi']],
            ]],
            3 => ['code' => '9.3', 'name' => 'Kimyasal Türler Arası Etkileşimler', 'topics' => [
                1 => ['name' => 'Kimyasal Tür', 'difficulty' => 2, 'posts' => ['kimyasal-tur']],
                2 => ['name' => 'Kimyasal Türler Arası Etkileşimlerin Sınıflandırılması', 'difficulty' => 2, 'posts' => ['kimyasal-turler-arasi-etkilesimin-siniflandirilmasi', 'bag-enerjileri']],
                3 => ['name' => 'Güçlü Etkileşimler', 'difficulty' => 3, 'posts' => ['lewis-nokta-yapisi', 'iyonik-bag', 'iyonik-bilesiklerin-ozellikleri', 'iyonik-bagli-bilesiklerin-adlandirilmasi-formullerinin-yazilmasi', 'kovalent-bagin-tanimi-ve-olusumu', 'apolar-kovalent-bag-ve-polar-kovalent-bag', 'molekulde-polarlik-ve-apolarlik', 'kovalent-bagli-bilesiklerin-adlandirilmasi', 'metalik-bag', 'guclu-etkilesimler-soru-cevap-calismasi']],
                4 => ['name' => 'Zayıf Etkileşimler', 'difficulty' => 3, 'posts' => ['zayif-etkilesimler', 'van-der-waals-etkilesimleri', 'hidrojen-bagi', 'zayif-etkilesimler-soru-cevap-calismasi']],
                5 => ['name' => 'Fiziksel ve Kimyasal Değişimler', 'difficulty' => 2, 'posts' => ['fiziksel-ve-kimyasal-degisim']],
            ]],
            4 => ['code' => '9.4', 'name' => 'Maddenin Halleri', 'topics' => [
                1 => ['name' => 'Maddenin Fiziksel Halleri', 'difficulty' => 2, 'posts' => ['maddenin-fiziksel-halleri-hal-degisimi', 'erime-nedir-nasil-gerceklesir', 'donma-nedir-nasil-gerceklesir', 'buharlasma-nedir-nasil-gerceklesir-3', 'yogusma-nedir-nasil-gerceklesir', 'sublimlesme-nedir-nasil-gerceklesir', 'kiragilasma-nedir-nasil-gerceklesir', 'maddenin-fiziksel-ozellikleri-soru-cozumleri', 'suyun-farkli-hallerinin-onemi', 'lpg-ve-lngnin-ozellikleri', 'itici-gazlar', 'sogutucu-akiskanlar', 'havadan-oksijen-ve-azot-eldesi', 'suyun-farkli-hallerinin-onemi-endustride-hal-degisimi-soru-cozumleri']],
                2 => ['name' => 'Katılar', 'difficulty' => 2, 'posts' => ['maddenin-kati-hali-ve-ozellikleri', 'amorf-katilar', 'kristal-katilar', 'iyonik-kristal-nedir', 'kovalent-kristal-nedir', 'molekuler-kristal-nedir', 'metalik-kristal-nedir', 'kristal-katilar-soru-cozumleri']],
                3 => ['name' => 'Sıvılar', 'difficulty' => 2, 'posts' => ['maddenin-sivi-hali-ve-ozellikleri', 'buharlasma-nedir', 'buharlasma-hizi-nedir', 'denge-buhar-basinci-nedir', 'buhar-basincina-etki-eden-faktorler', 'viskozite-nedir']],
                4 => ['name' => 'Gazlar', 'difficulty' => 2, 'posts' => ['maddenin-gaz-hali-ve-ozellikleri']],
                5 => ['name' => 'Plazma', 'difficulty' => 1, 'posts' => ['maddenin-plazma-hali-ve-ozellikleri']],
            ]],
            5 => ['code' => '9.5', 'name' => 'Doğa ve Kimya', 'topics' => [
                1 => ['name' => 'Su ve Hayat', 'difficulty' => 1, 'posts' => []],
                2 => ['name' => 'Çevre Kimyası', 'difficulty' => 1, 'posts' => []],
            ]],
        ],

        10 => [
            1 => ['code' => '10.1', 'name' => 'Kimyanın Temel Kanunları ve Kimyasal Hesaplamalar', 'topics' => [
                1 => ['name' => 'Kimyanın Temel Kanunları', 'difficulty' => 2, 'posts' => ['kimyanin-temel-kanunlari-kutlenin-korunumu-kanunu', 'sabit-oranlar-kanunu', 'katli-oranlar-kanunu', 'kimyanin-temel-kanunlari-soru-cozumleri', 'kimyanin-temel-kanunlari-yaziliya-hazirlik-calismasi-1']],
                2 => ['name' => 'Mol Kavramı', 'difficulty' => 3, 'posts' => ['mol-kavrami-konu-anlatimi-ve-soru-cozumleri', '154-2', '179-2', 'bagil-atom-kutlesi-atomik-kutle-birimi-soru-cozumleri', 'izotop-atom-ve-ortalama-atom-kutlesi', 'mol-sayisi-hesaplamalari-molekul-kutlesi-mol-kutlesi', '290-2', 'mol-sayisi-kutle-iliskisi-soru-cozumleri', 'mol-sayisi-tanecik-iliskisi', 'mol-sayisi-kutle-tanecik-iliskisi-soru-cozumleri', 'mol-kavrami-yaziliya-hazirlik-calismasi-2', '474-2', '509-2', 'mol-sayisi-kutle-tanecik-hacim-iliskisi-soru-cozumleri', 'mol-sayisi-hesaplamalari-karisim-problemleri']],
                3 => ['name' => 'Kimyasal Tepkimeler ve Denklemler', 'difficulty' => 3, 'posts' => ['kimyasal-tepkime-ve-denklem-nedir', '757-2', 'kimyasal-tepkimeleri-denklestirme', 'kimyasal-tepkimeler-ve-denklemler-soru-cozumleri-1', 'kimyasal-tepkimeler-ve-denklemler-soru-cozumleri-2', 'tepkime-turleri-yanma-tepkimeleri', 'yanma-tepkimeleri-soru-cozumleri', 'sentez-olusum-tepkimeleri', 'analiz-ayrisma-tepkimeleri', 'asit-baz-tepkimeleri', 'cozunme-cokelme-tepkimeleri']],
                4 => ['name' => 'Kimyasal Tepkimelerde Hesaplamalar', 'difficulty' => 4, 'posts' => ['formulu-bilinmeyen-maddenin-formulunu-bulma', 'denklemli-miktar-hesaplamalari', 'denklemli-miktar-hesaplamalari-soru-cozumleri', 'artan-maddesi-olan-tepkimeler', 'artan-maddesi-olan-tepkimeler-soru-cozumleri', 'verim-hesaplamalari', 'karisim-tepkimeleri-birbirini-izleyen-tepkimeler-atom-kutlesi-ve-formul-bulma', 'formulu-bilinmeyen-maddenin-formulunu-bulma-tekrar-calismasi']],
            ]],
            2 => ['code' => '10.2', 'name' => 'Karışımlar', 'topics' => [
                1 => ['name' => 'Homojen ve Heterojen Karışımlar', 'difficulty' => 3, 'posts' => ['karisimlarin-siniflandirilmasi', 'heterojen-karisimlar', 'homojen-karisimlar-cozeltiler', 'cozunme-olayi-nedir-nasil-gerceklesir', '1581-2', 'homojen-karisimlar-cozeltiler-soru-cozumleri', 'cozunen-madde-miktarina-gore-cozeltilerin-siniflandirilmasi', 'cozeltilerde-kutlece-derisim', 'cozeltilerin-seyreltilmesi', 'cozeltilerin-karistirilmasi', 'koligatif-ozellikler']],
                2 => ['name' => 'Ayırma ve Saflaştırma Teknikleri', 'difficulty' => 2, 'posts' => ['tanecik-boyutu-farkindan-yararlanarak-ayirma', 'yogunluk-farkiyla-ayirma', 'cozunurluk-farkiyla-ayirma', 'kaynama-noktasi-farki-ile-ayirma', 'erime-noktasi-farkindan-yararlanarak-ayirma', 'miknatislanma-ozelligi-ile-ayirma', 'karisimlari-ayirma-yontemleri', 'karisimlari-ayirma-yontemleri-soru-cozumleri']],
            ]],
            3 => ['code' => '10.3', 'name' => 'Asitler, Bazlar ve Tuzlar', 'topics' => [
                1 => ['name' => 'Asitler ve Bazlar', 'difficulty' => 2, 'posts' => ['asitler-ve-genel-ozellikleri', 'bazlar-ve-genel-ozellikleri', 'indikator-ayrac-nedir', 'ph-nedir', 'molekuler-duzeyde-asitlik-bazlik', 'oksitlerin-asitligi-bazligi']],
                2 => ['name' => 'Asitlerin ve Bazların Tepkimeleri', 'difficulty' => 3, 'posts' => ['yanmaya-karsi-asallik', 'notrallesme-tepkimeleri', 'asitlerin-aktif-metallerle-tepkimeleri', 'asitlerin-yari-soy-metallerle-tepkimeleri', 'metallerin-hidrojene-gore-aktiflik-siralamasi', 'bazlarin-amfoter-metallerle-tepkimeleri', 'asitlerin-soy-metallerle-tepkimeleri', 'asitlerin-amfoter-metallerle-tepkimeleri', 'asit-ve-bazlarin-metallerle-tepkimeleri-soru-cozumleri']],
                3 => ['name' => 'Hayatımızda Asitler ve Bazlar', 'difficulty' => 1, 'posts' => []],
                4 => ['name' => 'Tuzlar', 'difficulty' => 2, 'posts' => []],
            ]],
            4 => ['code' => '10.4', 'name' => 'Kimya Her Yerde', 'topics' => [
                1 => ['name' => 'Yaygın Günlük Hayat Kimyasalları', 'difficulty' => 1, 'posts' => []],
                2 => ['name' => 'Gıdalar', 'difficulty' => 1, 'posts' => []],
            ]],
        ],

        11 => [
            1 => ['code' => '11.1', 'name' => 'Modern Atom Teorisi', 'topics' => [
                1 => ['name' => 'Atomun Kuantum Modeli', 'difficulty' => 4, 'posts' => ['yorunge-orbital-kavrami', 'kuantum-sayilari', 'kuantum-sayilari-soru-cozumleri', 'orbitallerin-ozellikleri-orbitallerin-enerji-seviyeleri']],
                2 => ['name' => 'Periyodik Sistem ve Elektron Dizilimleri', 'difficulty' => 4, 'posts' => ['pauli-ilkesi-hund-kurali-aufbau-kurali', '161-2', 'temel-hal-uyarilmis-hal-iyonlarda-elektron-dagilimi', 'elementlerin-grup-ve-periyodunun-belirlenmesi', '227-2']],
                3 => ['name' => 'Periyodik Özellikler', 'difficulty' => 3, 'posts' => ['periyodik-ozellikler-atom-yaricapi', 'atom-yaricapi-soru-cozumleri', 'iyonlasma-enerjisi', 'iyonlasma-enerjisi-soru-cozumleri', 'periyodik-ozellikler-elektron-ilgisi', 'periyodik-ozellikler-elektronegatiflik', 'metalik-ametalik-aktiflik-oksit-ve-hidroksitlerin-asitligi-bazligi']],
                4 => ['name' => 'Elementleri Tanıyalım', 'difficulty' => 2, 'posts' => ['767-2']],
                5 => ['name' => 'Yükseltgenme Basamakları', 'difficulty' => 3, 'posts' => ['yukseltgenme-basamaklari-2']],
                90 => ['name' => 'Ünite Tekrarı', 'difficulty' => 3, 'posts' => ['438-2', 'atomun-kuantum-modeli-yaziliya-hazirlik-calismasi-1', 'periyodik-sistem-yaziliya-hazirlik-calismasi']],
            ]],
            2 => ['code' => '11.2', 'name' => 'Gazlar', 'topics' => [
                1 => ['name' => 'Gazların Özellikleri ve Gaz Yasaları', 'difficulty' => 3, 'posts' => ['gazlarin-ozellikleri', 'gazlari-niteleyen-ozellikler', 'boyle-mariotte-yasasi', 'charles-gaz-yasasi', 'gay-lussac-yasasi', 'avagadro-yasasi']],
                2 => ['name' => 'İdeal Gaz Yasası', 'difficulty' => 3, 'posts' => ['ideal-gaz-denklemi', 'gazlarin-yogunlugu']],
                3 => ['name' => 'Gazlarda Kinetik Teori', 'difficulty' => 3, 'posts' => ['gazlarda-kinetik-teori-graham-difuzyon-yasasi', 'gazlarda-kinetik-teori-soru-cozumleri']],
                4 => ['name' => 'Gaz Karışımları', 'difficulty' => 4, 'posts' => ['gazlarda-kismi-basinc-mol-kesri', 'gazlarin-su-uzerinde-toplanmasi', 'tepkime-vermeyen-gaz-karisimlari', 'tepkime-veren-gaz-karisimlari']],
                5 => ['name' => 'Gerçek Gazlar', 'difficulty' => 3, 'posts' => ['gercek-gazlar', 'faz-diyagramlari', 'joule-thomson-olayi']],
            ]],
            3 => ['code' => '11.3', 'name' => 'Sıvı Çözeltiler ve Çözünürlük', 'topics' => [
                1 => ['name' => 'Çözücü Çözünen Etkileşimleri', 'difficulty' => 2, 'posts' => ['sivi-cozeltiler-cozeltilerin-ozellikleri', 'sivi-cozeltiler-cozunme-olayi']],
                2 => ['name' => 'Derişim Birimleri', 'difficulty' => 4, 'posts' => ['sivi-cozeltilerde-derisim-birimleri-genel-kavramlar', 'sivi-cozeltilerde-kutlece-derisim', 'seyreltik-ve-derisik-cozeltilerde-kutlece-derisim', 'cozeltilerin-karistirilmasi-ve-kutlece-derisim-hesabi', 'hacimce-derisim', 'mol-kesri-nedir-nasil-hesaplanir', 'ppm-nedir-nasil-hesaplanir', 'molarite-molar-derisim-nasil-hesaplanir', 'iyonlarin-molar-derisimi-nasil-hesaplanir', 'seyreltik-ve-derisik-cozeltilerde-molar-derisim', 'ayni-tur-cozeltilerin-karistirilmasinda-molar-derisim-hesabi', 'farkli-tur-cozeltilerin-karistirilmasinda-molar-derisim-hesabi', 'molarite-yogunluk-iliskisi', 'molar-derisimi-ve-hacmi-bilinen-cozelti-nasil-hazirlanir', 'molal-derisim-molalite']],
                3 => ['name' => 'Koligatif Özellikler', 'difficulty' => 4, 'posts' => ['buhar-basinci-dusmesi', 'kaynama-noktasi-yukselmesi-ebulyoskobi', 'donma-noktasi-dusmesi-kriyoskopi', 'ozmotik-basinc']],
                4 => ['name' => 'Çözünürlük', 'difficulty' => 3, 'posts' => ['cozeltilerin-siniflandirilmasi', 'cozunurluk-nedir']],
                5 => ['name' => 'Çözünürlüğe Etki Eden Faktörler', 'difficulty' => 3, 'posts' => ['cozunurlugu-etkileyen-faktorler-nelerdir', 'cozunme-hizina-etki-eden-faktorler-nelerdir']],
            ]],
            4 => ['code' => '11.4', 'name' => 'Kimyasal Tepkimelerde Enerji', 'topics' => [
                1 => ['name' => 'Tepkimelerde Isı Değişimi', 'difficulty' => 3, 'posts' => ['kimyasal-tepkimelerde-isi-degisimi', 'ekzotermik-tepkimelerde-entalpi', 'endotermik-tepkimelerde-entalpi', 'tepkime-entalpisine-etki-eden-faktorler', 'kimyasal-tepkimelerde-isi-soru-cozumleri']],
                2 => ['name' => 'Oluşum Entalpisi', 'difficulty' => 3, 'posts' => ['standart-olusum-entalpisi-nedir', 'tepkime-entalpisi-nasil-hesaplanir', 'standart-olusum-entalpisi-soru-cozumleri']],
                3 => ['name' => 'Bağ Enerjileri', 'difficulty' => 3, 'posts' => ['bag-enerjisi']],
                4 => ['name' => 'Tepkime Isılarının Toplanabilirliği', 'difficulty' => 4, 'posts' => ['hess-yasasi-nedir', 'hess-yasasi-ornek-soru-cozumleri']],
            ]],
            5 => ['code' => '11.5', 'name' => 'Kimyasal Tepkimelerde Hız', 'topics' => [
                1 => ['name' => 'Tepkime Hızları', 'difficulty' => 3, 'posts' => []],
                2 => ['name' => 'Tepkime Hızını Etkileyen Faktörler', 'difficulty' => 3, 'posts' => []],
            ]],
            6 => ['code' => '11.6', 'name' => 'Kimyasal Tepkimelerde Denge', 'topics' => [
                1 => ['name' => 'Kimyasal Denge', 'difficulty' => 4, 'posts' => []],
                2 => ['name' => 'Dengeyi Etkileyen Faktörler', 'difficulty' => 4, 'posts' => []],
                3 => ['name' => 'Sulu Çözelti Dengeleri', 'difficulty' => 5, 'posts' => []],
            ]],
        ],

        12 => [
            1 => ['code' => '12.1', 'name' => 'Kimya ve Elektrik', 'topics' => [
                1 => ['name' => 'İndirgenme-Yükseltgenme Tepkimelerinde Elektrik Akımı', 'difficulty' => 3, 'posts' => ['yukseltgenme-basamaklari', 'indirgenme-yukseltgenme-tepkimeleri', 'redoks-tepkimelerinin-denklestirilmesi', 'aktiflik', 'redoks-soru-cozumleri']],
                2 => ['name' => 'Elektrotlar ve Elektrokimyasal Hücreler', 'difficulty' => 4, 'posts' => ['elektrokimyasal-piller', 'elektrokimyasal-piller-2', 'elektrokimyasal-piller-soru-cozumleri']],
                3 => ['name' => 'Elektrot Potansiyelleri', 'difficulty' => 4, 'posts' => ['standart-pil-potansiyeline-etki-eden-faktorler', 'nersnt-esitligi', 'derisim-pilleri']],
                4 => ['name' => 'Kimyasallardan Elektrik Üretimi', 'difficulty' => 3, 'posts' => []],
                5 => ['name' => 'Elektroliz', 'difficulty' => 4, 'posts' => ['elektroliz-1', 'suyun-elektrolizi-erimis-tuzlarin-elektrolizi', 'sulu-cozeltilerin-elektrolizi', 'faraday-kanunlari', 'metal-kaplamacilik']],
                6 => ['name' => 'Korozyon', 'difficulty' => 2, 'posts' => ['korozyon-nedir-korozyondan-korunma-yontemleri', 'katodik-koruma-ve-kurban-elektrot-nedir']],
            ]],
            2 => ['code' => '12.2', 'name' => 'Karbon Kimyasına Giriş', 'topics' => [
                1 => ['name' => 'Anorganik ve Organik Bileşikler', 'difficulty' => 2, 'posts' => ['organik-ve-anorganik-bilesikler', 'organik-bilesik-nedir']],
                2 => ['name' => 'Basit Formül ve Molekül Formülü', 'difficulty' => 3, 'posts' => ['1361-2']],
                3 => ['name' => 'Doğada Karbon', 'difficulty' => 2, 'posts' => ['dogada-karbon']],
                4 => ['name' => 'Lewis Formülleri', 'difficulty' => 3, 'posts' => ['kovalent-bagli-bilesiklerin-lewis-formulleri']],
                5 => ['name' => 'Hibritleşme ve Molekül Geometrileri', 'difficulty' => 4, 'posts' => ['hibritlesme-nedir', 'karbon-atomunun-hibritlesmesi-nasil-olur', '1460-2', 'sigma-ve-pi-baglari-nasil-olusur', 'sigma-ve-pi-bagi-sayisinin-kolay-yoldan-bulunmasi-2', 'c2h4-molekulunun-hibritlesmesi-sigma-ve-pi-baglari', 'hibrit-orbitallerinin-s-karakteri', 'hibritlesmede-bag-kuvveti', 'merkez-atomun-hibritlesme-turunun-kisa-yoldan-bulunmasi', 'molekul-geometrisi-nedir', 'vsepr-gosterimi', 'ch4-molekulunun-3-boyutlu-gosterimi', '2a-grubu-elementlerinin-molekul-geometrisi-beh2-molekulu', '3a-grubu-elementlerinin-molekul-geometrisi-bh3-molekulu', '4a-grubu-elementlerinin-molekul-geometrisi-ch4-molekulu', 'c2h4-molekulunun-molekul-geometrisi', 'c2h2-molekulunun-molekul-geometrisi', '5a-grubu-elementlerinin-molekul-geometrisi-nh3-molekulu', '6a-grubu-elementlerinin-molekul-geometrisi-h2o-molekulu', 'molekul-geometrisi-vsepr-gosterimi-soru-cozumleri']],
            ]],
            3 => ['code' => '12.3', 'name' => 'Organik Bileşikler', 'topics' => [
                1 => ['name' => 'Hidrokarbonlar: Alkanlar', 'difficulty' => 3, 'posts' => ['hidrokarbonlar', 'alkanlarin-genel-ozellikleri', 'alkanlarin-adlandirilmasi', 'alkanlarda-karbon-atomlari', 'alkil-gruplari', 'izo-neo-dallanmasi', 'alkanlarin-adlandirilmasi-2', 'alkanlarin-adlandirilmasi-soru-cozumleri', 'alkanlarda-cizgi-bag-iskelet-formulu', 'sikloalkanlarin-ozellikleri', 'sikloalkanlarin-adlandirilmasi', 'alkanlarin-fiziksel-ozellikleri', 'alkanlarin-kimyasal-ozellikleri', 'alkanlarin-kullanim-alanlari', 'alkanlarin-kimyasal-tepkimeleri', 'alkanlarda-izomeri', 'alkanlarda-izomeri-soru-cozumleri']],
                2 => ['name' => 'Hidrokarbonlar: Alkenler', 'difficulty' => 3, 'posts' => ['alkenlerin-genel-ozellikleri', 'alkenlerin-adlandirilmasi', 'alkenlerin-adlandirilmasi-soru-cozumleri', 'sikloalkenlerin-ozellikleri-ve-adlandirilmasi', 'alkenlerde-cis-trans-izomeri', 'alkenlerin-kimyasal-ozellikleri-ve-kullanim-alanlari', 'alkenlerin-kimyasal-tepkimeleri-yanma-tepkimesi', 'alkenlerin-kimyasal-tepkimeleri-hidrojen-katilmasi', 'alkenlerin-kimyasal-tepkimeleri-halojen-katilmasi', 'alkenlerin-kimyasal-tepkimeleri-hidrojen-halojenur-katilmasi', 'alkenlerin-kimyasal-tepkimeleri-polimerlesme-tepkimesi', 'alkenlerin-kimyasal-tepkimeleri-su-katilmasi']],
                3 => ['name' => 'Hidrokarbonlar: Alkinler', 'difficulty' => 3, 'posts' => ['alkinlerin-genel-ozellikleri', 'alkinlerin-sistematik-adlandirilmasi']],
                4 => ['name' => 'Aromatik Hidrokarbonlar', 'difficulty' => 3, 'posts' => []],
                5 => ['name' => 'Fonksiyonel Gruplar', 'difficulty' => 3, 'posts' => []],
                6 => ['name' => 'Alkoller', 'difficulty' => 3, 'posts' => []],
                7 => ['name' => 'Eterler', 'difficulty' => 3, 'posts' => []],
                8 => ['name' => 'Karbonil Bileşikleri', 'difficulty' => 4, 'posts' => []],
                9 => ['name' => 'Karboksilik Asitler', 'difficulty' => 4, 'posts' => []],
                10 => ['name' => 'Esterler', 'difficulty' => 3, 'posts' => []],
            ]],
            4 => ['code' => '12.4', 'name' => 'Enerji Kaynakları ve Bilimsel Gelişmeler', 'topics' => [
                1 => ['name' => 'Fosil Yakıtlar', 'difficulty' => 2, 'posts' => []],
                2 => ['name' => 'Alternatif Enerji Kaynakları', 'difficulty' => 2, 'posts' => []],
                3 => ['name' => 'Sürdürülebilirlik', 'difficulty' => 1, 'posts' => []],
                4 => ['name' => 'Nanoteknoloji', 'difficulty' => 2, 'posts' => []],
            ]],
        ],
    ],

    // WordPress category archives → grade (301 redirect targets).
    'categories' => [
        '9-sinif-konulari' => 9,
        '10-sinif-konulari' => 10,
        '11-sinif-konulari' => 11,
        '12-sinif-konulari' => 12,
    ],
];
