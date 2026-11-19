<?php

use App\Enums\ApplicationStatus;
use App\Livewire\Board;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are sent to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('the board shows only my own applications', function () {
    $me = User::factory()->create();
    Application::factory()->for($me)->create(['company' => 'My Company']);
    Application::factory()->create(['company' => 'Someone Else Inc']);

    $this->actingAs($me)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('My Company')
        ->assertDontSee('Someone Else Inc');
});

test('I can add a job', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Board::class)
        ->call('create', 'applied')
        ->set('company', 'Acme')
        ->set('role', 'Laravel Developer')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $application = $user->applications()->sole();
    expect($application->company)->toBe('Acme')
        ->and($application->status)->toBe(ApplicationStatus::Applied)
        ->and($application->applied_at)->not->toBeNull();
});

test('company and role are required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Board::class)
        ->call('create')
        ->call('save')
        ->assertHasErrors(['company' => 'required', 'role' => 'required']);
});

test('dragging a card changes its stage and order', function () {
    $user = User::factory()->create();
    $first = Application::factory()->for($user)->status(ApplicationStatus::Interview)->create(['position' => 0]);
    $card = Application::factory()->for($user)->create(); // on the wishlist

    Livewire::actingAs($user)
        ->test(Board::class)
        ->call('move', $card->id, 'interview', [$card->id, $first->id]);

    expect($card->fresh()->status)->toBe(ApplicationStatus::Interview)
        ->and($card->fresh()->position)->toBe(0)
        ->and($first->fresh()->position)->toBe(1)
        ->and($card->fresh()->applied_at)->not->toBeNull();
});

test("I can't move or edit someone else's application", function () {
    $other = Application::factory()->create();

    expect(fn () => Livewire::actingAs(User::factory()->create())
        ->test(Board::class)
        ->call('move', $other->id, 'offer', [$other->id])
    )->toThrow(ModelNotFoundException::class);

    expect($other->fresh()->status)->toBe(ApplicationStatus::Wishlist);
});

test('I can delete an application', function () {
    $user = User::factory()->create();
    $application = Application::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Board::class)
        ->call('delete', $application->id);

    expect(Application::count())->toBe(0);
});
