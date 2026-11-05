<x-filament-panels::page>
    <x-filament::section>
        <div class="flex flex-col items-center gap-5 px-4 py-14 text-center">
            <span class="rounded-2xl bg-primary-100 p-4 dark:bg-primary-400/10">
                <x-dynamic-component
                    :component="$this->getNavigationIcon()"
                    class="size-10 text-primary-600 dark:text-primary-400"
                />
            </span>

            <div class="flex flex-col items-center gap-2">
                <p class="max-w-md text-sm leading-6 text-gray-500 dark:text-gray-400">
                    {{ $this->getStatusMessage() }}
                </p>

                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-3 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30"
                >
                    {{ $this->getPhaseLabel() }}
                </span>
            </div>

            <x-filament::button tag="a" href="{{ filament()->getHomeUrl() }}" color="gray" size="sm">
                Back to Dashboard
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-panels::page>
