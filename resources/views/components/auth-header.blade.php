@props(['title', 'description' => null])

<div class="flex flex-col gap-2 text-center">
    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
</div>
