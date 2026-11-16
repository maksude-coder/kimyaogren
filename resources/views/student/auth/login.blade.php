<x-student.auth title="Giriş yap">
    <h1 class="text-[28px]/tight font-semibold">Giriş yap</h1>

    <form method="POST" action="{{ route('login') }}" novalidate class="flex flex-col gap-5">
        @csrf

        <x-student.field name="email" label="E-posta" type="email" autocomplete="email" inputmode="email" required autofocus />

        <x-student.field name="password" label="Şifre" type="password" autocomplete="current-password" required>
            <x-slot:aside>
                <a href="{{ route('password.request') }}" class="font-semibold">Şifremi unuttum</a>
            </x-slot:aside>
        </x-student.field>

        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px]">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-[22px] flex-none cursor-pointer accent-amber">
            Bu cihazda oturumu açık tut
        </label>

        <button type="submit" class="btn-primary min-h-[52px] cursor-pointer">Giriş yap</button>

        <p class="text-center text-[15px] text-ink2">Hesabın yok mu? <a href="{{ route('register') }}" class="font-semibold">Ücretsiz hesap aç</a></p>
    </form>
</x-student.auth>
