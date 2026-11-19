<div class="py-8">
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Top bar: title, search, add button --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Applications</h1>
                <p class="text-sm text-gray-500">Drag a card to move it to the next stage.</p>
            </div>

            <div class="flex items-center gap-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search company or role…"
                       class="w-full sm:w-64 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" wire:click="create"
                        class="shrink-0 inline-flex items-center gap-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <span class="text-lg leading-none">+</span> Add job
                </button>
            </div>
        </div>

        {{-- The board: one column per status --}}
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach ($statuses as $status)
                @php $cards = $columns->get($status->value, collect()); @endphp

                <section class="flex min-w-60 flex-1 flex-col rounded-lg bg-gray-100/80" wire:key="column-{{ $status->value }}">
                    <header class="flex items-center justify-between px-3 py-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full {{ $status->dotColor() }}"></span>
                            <h2 class="text-sm font-semibold text-gray-700">{{ $status->label() }}</h2>
                            <span class="rounded-full bg-white px-2 text-xs font-medium text-gray-500">{{ $cards->count() }}</span>
                        </div>
                        <button type="button" wire:click="create('{{ $status->value }}')"
                                class="rounded p-1 text-gray-400 hover:bg-white hover:text-gray-700" title="Add to {{ $status->label() }}">+</button>
                    </header>

                    {{-- Cards. SortableJS (see resources/js/app.js) makes these draggable. --}}
                    <div class="flex min-h-32 flex-1 flex-col gap-2 px-2 pb-3"
                         data-status="{{ $status->value }}"
                         x-data
                         x-init="window.makeSortable($el, $wire)">
                        @foreach ($cards as $card)
                            <article wire:key="card-{{ $card->id }}" data-id="{{ $card->id }}"
                                     wire:click="edit({{ $card->id }})"
                                     class="cursor-grab rounded-md border border-gray-200 bg-white p-3 shadow-sm transition hover:border-indigo-300 hover:shadow active:cursor-grabbing">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $card->company }}</p>
                                <p class="mt-0.5 font-medium text-gray-900">{{ $card->role }}</p>

                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                    @if ($card->location)
                                        <span>📍 {{ $card->location }}</span>
                                    @endif
                                    @if ($card->salary)
                                        <span>💰 {{ $card->salary }}</span>
                                    @endif
                                    @if ($card->applied_at)
                                        <span title="Applied {{ $card->applied_at->toFormattedDateString() }}">🕒 {{ $card->applied_at->diffForHumans(short: true) }}</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    {{-- Add / edit form (a simple modal) --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-10"
             wire:click.self="cancel" x-data x-on:keydown.escape.window="$wire.cancel()">
            <form wire:submit="save" class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-gray-900">{{ $editingId ? 'Edit application' : 'Add a job' }}</h2>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="company" value="Company *" />
                        <x-text-input id="company" wire:model="company" class="mt-1 block w-full" autofocus />
                        <x-input-error :messages="$errors->get('company')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="role" value="Role *" />
                        <x-text-input id="role" wire:model="role" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="location" value="Location" />
                        <x-text-input id="location" wire:model="location" class="mt-1 block w-full" placeholder="e.g. Remote" />
                    </div>
                    <div>
                        <x-input-label for="salary" value="Salary" />
                        <x-text-input id="salary" wire:model="salary" class="mt-1 block w-full" placeholder="e.g. £30k–35k" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="url" value="Job posting link" />
                        <x-text-input id="url" type="url" wire:model="url" class="mt-1 block w-full" placeholder="https://…" />
                        <x-input-error :messages="$errors->get('url')" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="status" value="Stage" />
                        <select id="status" wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" wire:model="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div>
                        @if ($editingId)
                            <button type="button" wire:click="delete({{ $editingId }})" wire:confirm="Delete this application?"
                                    class="text-sm font-medium text-rose-600 hover:text-rose-500">Delete</button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        @if ($editingId && str_starts_with($url, 'http'))
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="self-center text-sm text-indigo-600 hover:underline">Open posting ↗</a>
                        @endif
                        <x-secondary-button type="button" wire:click="cancel">Cancel</x-secondary-button>
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
