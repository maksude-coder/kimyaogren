<?php

declare(strict_types=1);

namespace Tests\Feature\Student\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Grade;
use App\Models\Program;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @return array<string, string>
     */
    private function validInput(): array
    {
        return ['name' => 'Elif', 'email' => 'elif@example.com', 'password' => 'kimya2026'];
    }

    public function test_register_page_shows_the_password_rules_before_submit(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Hesap oluştur')
            ->assertSee('En az 8 karakter')
            ->assertSee('En az bir rakam')
            ->assertSee(route('legal.terms'))
            ->assertSee(route('legal.kvkk'))
            ->assertDontSee('velinin onayı');
    }

    public function test_student_can_register_and_is_sent_to_grade_selection(): void
    {
        Notification::fake();
        $this->freezeSecond();

        $this->post(route('register'), $this->validInput())
            ->assertRedirect(route('register.grade'));

        $user = User::query()->where('email', 'elif@example.com')->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Elif', $user->name);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertTrue(now()->equalTo($user->terms_accepted_at));
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->grade_id);
        $this->assertFalse($user->is_graduate);
        $this->assertNotSame('kimya2026', $user->password);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_role_and_status_cannot_be_set_from_the_request(): void
    {
        Notification::fake();

        $this->post(route('register'), [...$this->validInput(), 'role' => 'admin', 'status' => 3, 'is_graduate' => 1]);

        $user = User::query()->sole();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertFalse($user->is_graduate);
    }

    public function test_email_is_stored_lowercase_and_trimmed(): void
    {
        Notification::fake();

        $this->post(route('register'), [...$this->validInput(), 'email' => '  Elif@Example.COM ']);

        $this->assertDatabaseHas('users', ['email' => 'elif@example.com']);
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string, 2: string}>
     */
    public static function invalidInput(): array
    {
        return [
            'missing name' => [['name' => ''], 'name', 'Adını yaz.'],
            'name too long' => [['name' => str_repeat('a', 101)], 'name', 'Ad en fazla 100 karakter olabilir.'],
            'incomplete email' => [['email' => 'elif@gmail'], 'email', 'E-posta adresi eksik görünüyor (ör. elif@gmail.com)'],
            'short password' => [['password' => 'kimya1'], 'password', 'Şifre en az 8 karakter olmalı.'],
            'password without digit' => [['password' => 'kimyaogren'], 'password', 'Şifre en az bir rakam içermeli.'],
        ];
    }

    /**
     * @param  array<string, string>  $override
     */
    #[DataProvider('invalidInput')]
    public function test_invalid_input_is_rejected_with_a_turkish_message(array $override, string $field, string $message): void
    {
        $this->from(route('register'))
            ->post(route('register'), [...$this->validInput(), ...$override])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_errors_are_rendered_under_the_field_and_the_password_is_not_echoed(): void
    {
        $this->from(route('register'))
            ->followingRedirects()
            ->post(route('register'), [...$this->validInput(), 'email' => 'elif@gmail'])
            ->assertOk()
            ->assertSee('E-posta adresi eksik görünüyor')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('value="Elif"', false)
            ->assertDontSee('kimya2026');
    }

    public function test_an_email_can_only_register_once(): void
    {
        User::factory()->create(['email' => 'elif@example.com']);

        $this->post(route('register'), $this->validInput())
            ->assertSessionHasErrors(['email' => 'Bu e-posta ile bir hesap var. Giriş yapmayı dene.']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_signed_in_users_are_redirected_away_from_register(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('register'))
            ->assertRedirect(route('curriculum.index'));
    }

    public function test_grade_selection_requires_login(): void
    {
        $this->get(route('register.grade'))->assertRedirect(route('login'));
        $this->post(route('register.grade'), ['grade' => 'mezun'])->assertRedirect(route('login'));
    }

    public function test_grade_selection_lists_active_grades_of_the_published_program_and_graduate(): void
    {
        $program = Program::factory()->highSchool()->published()->create(['display_order' => 1]);
        Grade::factory()->for($program)->number(9)->active()->create(['order' => 1]);
        Grade::factory()->for($program)->number(10)->active()->create(['order' => 2]);
        Grade::factory()->for($program)->number(11)->create(['order' => 3, 'is_active' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('register.grade'))
            ->assertOk()
            ->assertSee('Kaçıncı sınıftasın?')
            ->assertSeeInOrder(['9. Sınıf', '10. Sınıf', 'Mezun · YKS hazırlık'])
            ->assertDontSee('11. Sınıf');
    }

    public function test_student_can_choose_a_grade_and_lands_on_it(): void
    {
        $program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 1]);
        Grade::factory()->for($program)->number(9)->active()->create(['order' => 1]);
        $grade = Grade::factory()->for($program)->number(10)->active()->create(['order' => 2]);
        $user = User::factory()->graduate()->create();

        $this->actingAs($user)
            ->post(route('register.grade'), ['grade' => (string) $grade->id])
            ->assertRedirect(route('curriculum.index'));

        $user->refresh();
        $this->assertSame($grade->id, $user->grade_id);
        $this->assertFalse($user->is_graduate);

        $this->actingAs($user)
            ->get(route('curriculum.index'))
            ->assertRedirect(route('curriculum.grade', ['program' => 'lise-kimya', 'grade' => 10]));
    }

    public function test_student_can_choose_graduate(): void
    {
        $program = Program::factory()->highSchool()->published()->create(['slug' => 'lise-kimya', 'display_order' => 1]);
        $grade = Grade::factory()->for($program)->number(12)->active()->create();
        $user = User::factory()->create(['grade_id' => $grade->id]);

        $this->actingAs($user)
            ->post(route('register.grade'), ['grade' => 'mezun'])
            ->assertRedirect(route('curriculum.index'));

        $user->refresh();
        $this->assertNull($user->grade_id);
        $this->assertTrue($user->is_graduate);
    }

    public function test_grade_must_be_an_active_grade_of_the_published_program(): void
    {
        $program = Program::factory()->highSchool()->published()->create(['display_order' => 1]);
        $inactive = Grade::factory()->for($program)->number(9)->create(['is_active' => false]);
        $otherProgram = Grade::factory()->for(Program::factory()->create())->number(10)->active()->create();
        $user = User::factory()->create();

        foreach ([null, '', '999', (string) $inactive->id, (string) $otherProgram->id] as $value) {
            $this->actingAs($user)
                ->post(route('register.grade'), ['grade' => $value])
                ->assertSessionHasErrors('grade');
        }

        $this->assertNull($user->refresh()->grade_id);
    }

    public function test_student_without_a_grade_is_reminded_in_the_app_shell(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Sınıfını seç')
            ->assertSee($user->name)
            ->assertSee('Çıkış yap');

        $this->actingAs(User::factory()->graduate()->create())
            ->get(route('legal.terms'))
            ->assertDontSee('Sınıfını seç')
            ->assertSee('Mezun · YKS hazırlık');
    }
}
