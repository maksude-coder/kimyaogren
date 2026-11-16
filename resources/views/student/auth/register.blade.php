<x-student.auth title="Hesap oluştur" :step="1">
    <div>
        <h1 class="text-[26px]/tight font-semibold">Hesap oluştur</h1>
        <p class="mt-1.5 text-ink2">Ücretsiz. Kredi kartı istenmez.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" novalidate class="flex flex-1 flex-col gap-5">
        @csrf

        <x-student.field name="name" label="Ad" autocomplete="given-name" maxlength="100" required autofocus />

        <x-student.field name="email" label="E-posta" type="email" autocomplete="email" inputmode="email" required />

        <x-student.field name="password" label="Şifre" type="password" autocomplete="new-password" required>
            <x-student.password-rules />
        </x-student.field>

        <button type="submit" class="btn-primary mt-1 min-h-[52px] cursor-pointer">Devam et</button>

        <p class="text-center text-sm text-ink3">Hesabın var mı? <a href="{{ route('login') }}" class="font-semibold">Giriş yap</a></p>

        <p class="mt-auto text-center text-[13px] text-ink3 lg:mt-2">
            Devam ederek <a href="{{ route('legal.terms') }}">Kullanım koşulları</a> ve
            <a href="{{ route('legal.kvkk') }}">KVKK aydınlatma metnini</a> kabul edersin.
        </p>
    </form>
</x-student.auth>
