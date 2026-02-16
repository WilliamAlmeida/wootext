<div>
    <div x-data="{ appearance: $wire.entangle('appearance').live }" class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-1">
        <!-- Light Option -->
        <label class="relative flex items-center cursor-pointer">
            <input type="radio" name="appearance" value="light" x-model="appearance" class="sr-only peer">
            <div class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-150
                peer-checked:bg-gray-900 peer-checked:text-white peer-checked:shadow-sm
                dark:peer-checked:bg-white dark:peer-checked:text-gray-900
                text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                <x-phosphor-sun class="w-4 h-4" />
                <span>{{ __('Light') }}</span>
            </div>
        </label>

        <!-- Dark Option -->
        <label class="relative flex items-center cursor-pointer">
            <input type="radio" name="appearance" value="dark" x-model="appearance" class="sr-only peer">
            <div class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-150
                peer-checked:bg-gray-900 peer-checked:text-white peer-checked:shadow-sm
                dark:peer-checked:bg-white dark:peer-checked:text-gray-900
                text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                <x-phosphor-moon class="w-4 h-4" />
                <span>{{ __('Dark') }}</span>
            </div>
        </label>

        <!-- System Option -->
        <label class="relative flex items-center cursor-pointer">
            <input type="radio" name="appearance" value="system" x-model="appearance" class="sr-only peer">
            <div class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-150
                peer-checked:bg-gray-900 peer-checked:text-white peer-checked:shadow-sm
                dark:peer-checked:bg-white dark:peer-checked:text-gray-900
                text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                <x-phosphor-desktop class="w-4 h-4" />
                <span>{{ __('System') }}</span>
            </div>
        </label>
    </div>
</div>
