<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const FAILED = 'E-posta ya da şifre hatalı.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Giriş yap')
            ->assertSee('Bu cihazda oturumu açık tut')
            ->assertSee(route('password.request'))
            ->assertSee(route('register'));
    }

    public function test_student_can_log_in(): void
    {
        $user = User::factory()->create(['email' => 'elif@example.com']);

        $this->post(route('login'), ['email' => ' Elif@Example.com', 'password' => 'password'])
            ->assertRedirect(route('curriculum.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_returns_to_the_page_that_required_it(): void
    {
        User::factory()->create(['email' => 'elif@example.com']);

        $this->get(route('register.grade'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => 'elif@example.com', 'password' => 'password'])
            ->assertRedirect(route('register.grade'));
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password', 'remember' => '1']);

        $this->assertNotNull($user->refresh()->remember_token);
    }

    public function test_wrong_password_and_unknown_email_get_the_same_message(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'yanlis-sifre'])
            ->assertSessionHasErrors(['email' => self::FAILED]);

        $this->post(route('login'), ['email' => 'yok@example.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::FAILED]);

        $this->assertGuest();
    }

    public function test_suspended_account_cannot_log_in_and_gets_the_generic_message(): void
    {
        $user = User::factory()->suspended()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => self::FAILED]);

        $this->assertGuest();
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'yanlis-sifre'])
                ->assertSessionHasErrors(['email' => self::FAILED]);
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Çok fazla deneme yaptın. 15 dakika sonra yeniden dene.']);

        $this->assertGuest();

        $this->travel(16)->minutes();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_required_fields_have_turkish_messages(): void
    {
        $this->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'E-posta adresini yaz.', 'password' => 'Şifreni yaz.']);
    }

    public function test_student_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_logout_requires_post(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/cikis')
            ->assertNotFound();

        $this->assertAuthenticated();
    }

    public function test_signed_in_users_are_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('curriculum.index'));
    }

    public function test_navigation_offers_login_to_guests_and_the_curriculum_to_students(): void
    {
        $this->get(route('home'))
            ->assertSee(route('login'))
            ->assertSee('Ücretsiz hesap aç');

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertDontSee('Ücretsiz hesap aç')
            ->assertSee('Müfredata git');
    }
}
