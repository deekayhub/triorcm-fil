<x-filament-panels::page>
    <x-filament::section>
        @forelse ($this->getNotifications() as $n)
            <div class="border-b py-2">
                <p class="font-semibold">{{ $n->data['title'] ?? 'Notification' }}</p>
                <p class="text-sm text-gray-500">{{ $n->data['body'] ?? '' }}</p>
                <p class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">You're all caught up.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
