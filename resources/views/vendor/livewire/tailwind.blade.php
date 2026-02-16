@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <div class="relative flex justify-between h-12 w-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl py-2 ltr:pl-4 pl-4 pr-3 items-center select-none">
            <div class="flex items-center gap-3">
                <span class="min-w-0 text-sm font-normal line-clamp-1 text-gray-700 dark:text-gray-400">{!! __('Showing') !!} {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} {!! __('of') !!} {{ $paginator->total() }} {!! __('results') !!}</span>
            </div>

            <div class="flex items-center gap-2">
                {{-- First page (double chevrons left) --}}
                <button type="button" @if($paginator->onFirstPage()) disabled aria-disabled="true" @endif wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                    class="flex items-center justify-center transition-all duration-100 ease-out rounded-lg disabled:opacity-50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 focus:outline-none h-8 w-8 text-sm active:enabled:scale-[0.97] -space-x-2.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </button>

                {{-- Previous page (single chevron left) --}}
                <button type="button" @if($paginator->onFirstPage()) disabled aria-disabled="true" @endif wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="!w-8 !h-6 inline-flex items-center min-w-0 gap-2 transition-all duration-100 ease-out border-0 rounded-lg disabled:opacity-50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 focus:outline-none h-8 w-8 p-0 text-sm active:enabled:scale-[0.97] justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>

                {{-- Page indicator --}}
                    <div class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                    <span class="px-3 tabular-nums py-0.5 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $paginator->currentPage() }}</span>
                    <span class="truncate"> {!! __('of') !!} {{ $paginator->lastPage() }} {!! __('pages') !!}</span>
                </div>

                {{-- Next page (single chevron right) --}}
                <button type="button" @if(! $paginator->hasMorePages()) disabled aria-disabled="true" @endif wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="!w-8 !h-6 inline-flex items-center min-w-0 gap-2 transition-all duration-100 ease-out border-0 rounded-lg disabled:opacity-50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 focus:outline-none h-8 w-8 p-0 text-sm active:enabled:scale-[0.97] justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>

                {{-- Last page (double chevrons right) --}}
                <button type="button" @if(! $paginator->hasMorePages()) disabled aria-disabled="true" @endif wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="flex items-center justify-center transition-all duration-100 ease-out rounded-lg disabled:opacity-50 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 focus:outline-none h-8 w-8 text-sm active:enabled:scale-[0.97] -space-x-2.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </div>
    @endif
</div>
