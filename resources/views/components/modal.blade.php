@props(['model' => null, 'maxWidth' => 'md', 'title' => null])

@php
	$maxWidthClass = match($maxWidth) {
		'sm' => 'max-w-sm',
		'md' => 'max-w-md',
		'lg' => 'max-w-lg',
		'xl' => 'max-w-xl',
		default => 'max-w-md',
	};
@endphp

<div x-data="{ open: @entangle($model) }" x-show="open" class="fixed inset-0 z-40 overflow-y-auto" aria-modal="true" role="dialog">
	<div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
		<div class="fixed inset-0 transition-all backdrop-blur-xs bg-opacity-75 dark:bg-zinc-900 dark:bg-opacity-75" x-show="open" x-transition.opacity @click="open = false"></div>
		<span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

		<div x-show="open" x-transition x-cloak class="relative z-10 inline-block w-full {{ $maxWidthClass }} p-6 my-8 overflow-hidden text-left align-middle transform bg-white dark:bg-zinc-800 shadow-xl rounded-2xl">
			<div class="flex items-start justify-between">
				<div>
					@if($title)
						<h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h2>
					@else
						{{ $header ?? '' }}
					@endif
				</div>
				<div>
					<button type="button" class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-300" @click="open = false" aria-label="Fechar">
						<x-phosphor-x class="w-5 h-5" />
					</button>
				</div>
			</div>

			<div class="mt-4">
				{{ $slot }}
			</div>

			@if(isset($footer))
				<div class="mt-4">
					{{ $footer }}
				</div>
			@endif
		</div>
	</div>
</div>
