<x-student.auth title="Yeni şifre belirle">
    <h1 class="text-[26px]/tight font-semibold">Yeni şifre belirle</h1>

    <form method="POST" action="{{ route('password.update') }}" novalidate class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-student.field name="email" label="E-posta" type="email" :value="$email" autocomplete="email" inputmode="email" required />

        <x-student.field name="password" label="Yeni şifre" type="password" autocomplete="new-password" required autofocus>
            <x-student.password-rules />
        </x-student.field>

        <button type="submit" class="btn-primary min-h-[52px] cursor-pointer">Şifreyi kaydet</button>
    </form>
</x-student.auth>
