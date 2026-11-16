<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const SENT = 'Bu adrese kayıtlı bir hesap varsa şifre yenileme bağlantısını gönderdik.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Şifreni mi unuttun?');
    }

    public function test_reset_link_is_sent_to_a_registered_address(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', self::SENT);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $this->assertStringContainsString('/sifre-sifirla/'.$notification->token, $notification->toMail($user)->actionUrl);

            return true;
        });
    }

    public function test_unknown_address_gets_the_same_answer_and_no_mail(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'yok@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', self::SENT);

        Notification::assertNothingSent();
    }

    public function test_reset_page_renders_with_the_token_and_email(): void
    {
        $this->get(route('password.reset', ['token' => 'abc123', 'email' => 'elif@example.com']))
            ->assertOk()
            ->assertSee('Yeni şifre belirle')
            ->assertSee('value="abc123"', false)
            ->assertSee('value="elif@example.com"', false)
            ->assertSee('En az bir rakam');
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'yenisifre1'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Şifren yenilendi. Yeni şifrenle giriş yapabilirsin.');

        $this->assertTrue(Hash::check('yenisifre1', $user->refresh()->password));
        $this->assertGuest();
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), ['token' => 'gecersiz', 'email' => $user->email, 'password' => 'yenisifre1'])
            ->assertSessionHasErrors(['email' => 'Bu bağlantı geçersiz ya da süresi dolmuş. Yeni bir bağlantı iste.']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_new_password_must_follow_the_password_rule(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'rakamsizsifre'])
            ->assertSessionHasErrors(['password' => 'Şifre en az bir rakam içermeli.']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
