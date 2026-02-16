@props(['digits' => 6])

<div x-data="{
    length: {{ $digits }},
    get value() {
        return this.$root.querySelector('input').value;
    }
}" class="relative flex gap-2 justify-center" @input.stop>
    <template x-for="i in length">
        <div
            class="flex h-12 w-10 items-center justify-center rounded-md border border-zinc-200 bg-white text-lg font-semibold dark:border-zinc-800 dark:bg-zinc-900"
            x-text="value ? value[i-1] || '' : ''"
            :class="{ 'border-sky-500 ring-2 ring-sky-500/20': value && value.length === i-1 || (!value && i === 1) }"
        ></div>
    </template>
    
    <input
        type="text"
        {{ $attributes }}
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="{{ $digits }}"
        class="absolute inset-0 opacity-0 cursor-default"
        x-init="$nextTick(() => $el.focus())"
        @focus-2fa-auth-code.window="$el.focus()"
        @clear-2fa-auth-code.window="$el.value = ''; $el.dispatchEvent(new Event('input'))"
    >
</div>
