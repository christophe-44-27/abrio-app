<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Notifications\HouseholdInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    private function paidHousehold(): Household
    {
        $household = Household::factory()->create();
        $household->user->markUnlocked('cs_test_1');

        return $household->fresh();
    }

    private function member(Household $household): User
    {
        return User::factory()->create(['joined_household_id' => $household->id]);
    }

    /**
     * @return array{0: string, 1: string} plain token and the invited email
     */
    private function invite(Household $household, string $email = 'new@example.com'): array
    {
        Notification::fake();

        $this->actingAs($household->user)
            ->post(route('team.invite'), ['email' => $email])
            ->assertSessionHasNoErrors();

        $url = null;
        Notification::assertSentOnDemand(
            HouseholdInvitationNotification::class,
            function ($notification) use (&$url) {
                $url = $notification->url;

                return true;
            },
        );

        return [basename($url), $email];
    }

    public function test_members_get_the_premium_of_the_owner(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);

        $this->assertTrue($member->isUnlocked());
        $this->assertNull($member->housingLimit());
        $this->assertFalse($member->hasPaid());
    }

    public function test_members_edit_the_shared_household_and_housings(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);
        [$a, $b] = $household->members;

        $this->actingAs($member)->get(route('household.edit'))->assertOk();

        $this->actingAs($member)->put(route('housings.update'), ['housings' => [[
            'id' => 'h1', 'name' => 'Chez nous', 'rent' => 1200, 'parking' => 0,
            'heating' => 'included', 'heating_amount' => 0, 'one_time' => 0,
        ]]])->assertSessionHasNoErrors();

        $this->assertSame('Chez nous', $household->fresh()->housings[0]['name']);
        $this->assertDatabaseCount('households', 1);
    }

    public function test_a_stale_save_is_refused_instead_of_overwriting(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);
        $payload = fn (string $name, int $revision) => ['revision' => $revision, 'housings' => [[
            'id' => 'h1', 'name' => $name, 'rent' => 1000, 'parking' => 0,
            'heating' => 'included', 'heating_amount' => 0, 'one_time' => 0,
        ]]];

        $this->actingAs($household->user)->put(route('housings.update'), $payload('Premier', 0))
            ->assertSessionHasNoErrors();

        $this->actingAs($member)->put(route('housings.update'), $payload('Écrasé', 0))
            ->assertSessionHasErrors('conflict');

        $this->assertSame('Premier', $household->fresh()->housings[0]['name']);
        $this->assertSame(1, $household->fresh()->revision);

        $this->actingAs($member)->put(route('housings.update'), $payload('Suivant', 1))
            ->assertSessionHasNoErrors();
    }

    public function test_the_owner_invites_by_email_and_only_the_hash_is_stored(): void
    {
        $household = $this->paidHousehold();

        [$token, $email] = $this->invite($household);

        $invitation = HouseholdInvitation::firstOrFail();
        $this->assertSame($email, $invitation->email);
        $this->assertNotSame($token, $invitation->token);
        $this->assertTrue($invitation->expires_at->isFuture());
    }

    public function test_free_owners_cannot_invite(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->post(route('team.invite'), ['email' => 'new@example.com'])
            ->assertForbidden();
    }

    public function test_members_cannot_invite_remove_or_share(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);
        $other = $this->member($household);

        $this->actingAs($member)->post(route('team.invite'), ['email' => 'x@example.com'])->assertNotFound();
        $this->actingAs($member)->delete(route('team.member.remove', $other))->assertNotFound();
        $this->actingAs($member)->post(route('team.share.start'))->assertNotFound();

        $this->assertSame($household->id, $other->fresh()->joined_household_id);
    }

    public function test_the_owner_removes_a_member(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);

        $this->actingAs($household->user)->delete(route('team.member.remove', $member))->assertRedirect();

        $this->assertNull($member->fresh()->joined_household_id);
        $this->assertFalse($member->fresh()->isUnlocked());
    }

    public function test_an_owner_cannot_remove_someone_from_another_team(): void
    {
        $mine = $this->paidHousehold();
        $theirs = $this->paidHousehold();
        $stranger = $this->member($theirs);

        $this->actingAs($mine->user)->delete(route('team.member.remove', $stranger))->assertNotFound();
    }

    public function test_a_member_can_leave(): void
    {
        $household = $this->paidHousehold();
        $member = $this->member($household);

        $this->actingAs($member)->post(route('team.leave'))->assertRedirect();

        $this->assertNull($member->fresh()->joined_household_id);
    }

    public function test_the_team_is_capped(): void
    {
        $household = $this->paidHousehold();
        User::factory()->count(Household::MAX_USERS - 2)->create(['joined_household_id' => $household->id]);
        $this->invite($household, 'last@example.com');

        $this->actingAs($household->user)
            ->post(route('team.invite'), ['email' => 'toomany@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_accounts_with_their_own_household_cannot_be_invited(): void
    {
        $household = $this->paidHousehold();
        $busy = Household::factory()->create();

        $this->actingAs($household->user)
            ->post(route('team.invite'), ['email' => $busy->user->email])
            ->assertSessionHasErrors('email');
    }

    public function test_a_new_person_registers_from_the_link_and_joins_verified(): void
    {
        $household = $this->paidHousehold();
        [$token, $email] = $this->invite($household);
        auth()->logout();

        $this->get(route('invitations.show', $token))->assertRedirect(route('register'));
        $this->get(route('register'))->assertInertia(fn ($page) => $page
            ->where('invitation.email', $email));

        $this->post(route('register.store'), [
            'name' => 'Conjoint',
            'email' => $email,
            'country' => 'CA',
            'province' => 'QC',
            'password' => 'a-Very-Strong-Passw0rd!',
            'password_confirmation' => 'a-Very-Strong-Passw0rd!',
            'terms' => '1',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame($household->id, $user->joined_household_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseCount('household_invitations', 0);
        $this->assertDatabaseCount('households', 1);
    }

    public function test_registering_with_another_address_does_not_join(): void
    {
        $household = $this->paidHousehold();
        [$token] = $this->invite($household);
        auth()->logout();

        $this->get(route('invitations.show', $token));
        $this->post(route('register.store'), [
            'name' => 'Intrus',
            'email' => 'someone-else@example.com',
            'country' => 'CA',
            'province' => 'QC',
            'password' => 'a-Very-Strong-Passw0rd!',
            'password_confirmation' => 'a-Very-Strong-Passw0rd!',
            'terms' => '1',
        ]);

        $user = User::where('email', 'someone-else@example.com')->firstOrFail();
        $this->assertNull($user->joined_household_id);
        $this->assertNull($user->email_verified_at);
    }

    public function test_an_existing_account_signs_in_then_confirms(): void
    {
        $household = $this->paidHousehold();
        $existing = User::factory()->create(['email' => 'existing@example.com']);
        [$token] = $this->invite($household, 'existing@example.com');
        auth()->logout();

        $this->get(route('invitations.show', $token))->assertRedirect(route('login'));

        $this->actingAs($existing)->get(route('invitations.show', $token))
            ->assertInertia(fn ($page) => $page->where('state', 'confirm'));

        $this->actingAs($existing)->post(route('invitations.accept', $token))->assertRedirect(route('dashboard'));

        $this->assertSame($household->id, $existing->fresh()->joined_household_id);
        $this->assertDatabaseCount('household_invitations', 0);
    }

    public function test_someone_else_cannot_use_the_invitation(): void
    {
        $household = $this->paidHousehold();
        [$token] = $this->invite($household);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('invitations.show', $token))
            ->assertInertia(fn ($page) => $page->where('state', 'wrong_account'));
        $this->actingAs($stranger)->post(route('invitations.accept', $token))->assertForbidden();
    }

    public function test_expired_invitations_are_invalid(): void
    {
        $household = $this->paidHousehold();
        [$token] = $this->invite($household);
        HouseholdInvitation::query()->update(['expires_at' => now()->subMinute()]);
        auth()->logout();

        $this->get(route('invitations.show', $token))
            ->assertInertia(fn ($page) => $page->where('state', 'invalid'));
    }

    public function test_the_owner_shares_the_verdicts_publicly_and_can_stop(): void
    {
        $household = $this->paidHousehold();
        $household->update(['housings' => [[
            'id' => 'h1', 'name' => 'Logement A', 'rent' => 1000, 'parking' => 0,
            'heating' => 'included', 'heating_amount' => 0, 'one_time' => 0,
        ]]]);

        $this->actingAs($household->user)->post(route('team.share.start'))->assertRedirect();
        $token = $household->fresh()->share_token;
        $this->assertNotEmpty($token);

        auth()->logout();
        $this->get(route('shared.verdicts', $token))
            ->assertInertia(fn ($page) => $page
                ->component('household/Verdicts')
                ->where('shared', true)
                ->where('housings.0.name', 'Logement A'));

        $this->actingAs($household->user)->delete(route('team.share.stop'));
        auth()->logout();
        $this->get(route('shared.verdicts', $token))->assertNotFound();
    }

    public function test_a_shared_link_stops_working_without_the_premium(): void
    {
        $household = Household::factory()->create(['share_token' => 'abc123', 'housings' => [['id' => 'h1']]]);

        $this->get(route('shared.verdicts', 'abc123'))->assertNotFound();
    }

    public function test_the_team_page_lists_people_for_the_owner(): void
    {
        $household = $this->paidHousehold();
        $this->member($household);

        $this->actingAs($household->user)->get(route('team.edit'))
            ->assertInertia(fn ($page) => $page
                ->component('settings/Team')
                ->where('team.isOwner', true)
                ->has('team.members', 2));
    }
}
