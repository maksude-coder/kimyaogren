<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function verificationUrl(User $user, ?string $email = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($email ?? $user->email),
        ]);
    }

    public function test_unverified_student_can_use_the_site_and_sees_a_reminder(): void
    {
        $user = User::factory()->unverified()->graduate()->create();

        $this->actingAs($user)
            ->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('E-posta adresini doğrulamak için')
            ->assertSee('Yeniden gönder');

        $this->actingAs(User::factory()->graduate()->create())
            ->get(route('legal.terms'))
            ->assertDontSee('E-posta adresini doğrulamak için');
    }

    public function test_verification_link_points_to_the_turkish_route(): void
    {
        $user = User::factory()->unverified()->create();

        $url = (new VerifyEmail)->toMail($user)->actionUrl;

        $this->assertStringContainsString('/e-posta-dogrula/'.$user->id.'/'.sha1($user->email), $url);
    }

    public function test_email_is_verified_by_the_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user))
            ->assertRedirect(route('curriculum.index'))
            ->assertSessionHas('status', 'E-posta adresin doğrulandı.');

        $this->assertTrue($user->refresh()->hasVerifiedEmail());
    }

    public function test_link_for_another_address_or_without_signature_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->verificationUrl($user, 'baska@example.com'))->assertForbidden();
        $this->actingAs($user)->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();

        $this->assertFalse($user->refresh()->hasVerifiedEmail());
    }

    public function test_verification_link_requires_login(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->verificationUrl($user))->assertRedirect(route('login'));
    }

    public function test_verification_mail_can_be_sent_again(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from(route('legal.terms'))
            ->post(route('verification.send'))
            ->assertRedirect(route('legal.terms'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verified_user_is_not_mailed_again(): void
    {
        Notification::fake();

        $this->actingAs(User::factory()->create())->post(route('verification.send'));

        Notification::assertNothingSent();
    }
}
