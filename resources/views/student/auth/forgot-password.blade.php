<x-student.auth title="Şifremi unuttum">
    <div>
        <h1 class="text-[26px]/tight font-semibold">Şifreni mi unuttun?</h1>
        <p class="mt-1.5 text-ink2">E-posta adresini yaz, yeni şifre belirlemen için bir bağlantı gönderelim.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" novalidate class="flex flex-col gap-5">
        @csrf

        <x-student.field name="email" label="E-posta" type="email" autocomplete="email" inputmode="email" required autofocus />

        <button type="submit" class="btn-primary min-h-[52px] cursor-pointer">Bağlantı gönder</button>

        <p class="text-center text-[15px] text-ink2"><a href="{{ route('login') }}" class="font-semibold">Girişe dön</a></p>
    </form>
</x-student.auth>
