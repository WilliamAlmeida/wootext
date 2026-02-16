@props(['priority' => ''])

@if($priority)
    <span @class([
        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
        'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-300' => $priority === 'urgent',
        'bg-yellow-100 text-yellow-700 dark:bg-yellow-700 dark:text-yellow-300' => $priority === 'high',
        'bg-red-100 text-red-700 dark:bg-red-700 dark:text-red-300' => $priority === 'medium',
        'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-300' 
    ])>
        {{ $priority }}
    </span>
@endif