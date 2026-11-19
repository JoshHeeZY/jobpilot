<?php

namespace App\Livewire;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Board')]
class Board extends Component
{
    /** Text typed in the search box (filters by company or role). */
    public string $search = '';

    /** Whether the add/edit form is open, and which application it's editing (null = new). */
    public bool $showForm = false;
    public ?int $editingId = null;

    // Form fields
    public string $company = '';
    public string $role = '';
    public string $location = '';
    public string $url = '';
    public string $salary = '';
    public string $status = 'wishlist';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'salary' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function create(string $status = 'wishlist'): void
    {
        $this->resetForm();
        $this->status = $status;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $application = $this->findOwned($id);

        $this->resetForm();
        $this->editingId = $application->id;
        $this->fill([
            'company' => $application->company,
            'role' => $application->role,
            'location' => (string) $application->location,
            'url' => (string) $application->url,
            'salary' => (string) $application->salary,
            'status' => $application->status->value,
            'notes' => (string) $application->notes,
        ]);
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        // Store empty optional fields as NULL rather than ''.
        $data = array_map(fn ($value) => $value === '' ? null : $value, $data);

        if ($this->editingId) {
            $application = $this->findOwned($this->editingId);
            $application->fill($data);
        } else {
            $application = auth()->user()->applications()->make($data);
            // New cards go to the bottom of their column.
            $application->position = auth()->user()->applications()->where('status', $data['status'])->max('position') + 1;
        }

        $this->stampAppliedDate($application);
        $application->save();

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->findOwned($id)->delete();

        $this->showForm = false;
        $this->resetForm();
    }

    /**
     * Called from the browser when a card is dropped.
     *
     * @param  int  $id  the card that was dragged
     * @param  string  $status  the column it was dropped into
     * @param  array<int>  $orderedIds  every card id in that column, in the new order
     */
    public function move(int $id, string $status, array $orderedIds): void
    {
        $application = $this->findOwned($id);
        $application->status = ApplicationStatus::from($status);
        $this->stampAppliedDate($application);
        $application->save();

        foreach (array_values($orderedIds) as $index => $cardId) {
            auth()->user()->applications()
                ->whereKey((int) $cardId)
                ->update(['position' => $index]);
        }
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.board', [
            'statuses' => ApplicationStatus::cases(),
            'columns' => $this->columns(),
        ]);
    }

    /** @return Collection<string, Collection<int, Application>> applications grouped by status */
    private function columns(): Collection
    {
        return auth()->user()->applications()
            ->when($this->search !== '', function ($query) {
                $query->where(fn ($q) => $q
                    ->where('company', 'like', "%{$this->search}%")
                    ->orWhere('role', 'like', "%{$this->search}%"));
            })
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Application $application) => $application->status->value);
    }

    /** Only ever load applications that belong to the logged-in user. */
    private function findOwned(int $id): Application
    {
        return auth()->user()->applications()->findOrFail($id);
    }

    /** The first time a card leaves the wishlist, record today as the applied date. */
    private function stampAppliedDate(Application $application): void
    {
        if ($application->status !== ApplicationStatus::Wishlist && $application->applied_at === null) {
            $application->applied_at = now();
        }
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'company', 'role', 'location', 'url', 'salary', 'status', 'notes']);
        $this->resetValidation();
    }
}
