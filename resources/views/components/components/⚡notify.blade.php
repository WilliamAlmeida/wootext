<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public array $notifications = [];

    #[On('notify')]
    public function addNotification(
        string $type = 'info',
        string $message = '',
        ?string $redirectOnEnd = null,
        int $duration = 5000
    ): void {
        $id = uniqid('notify_', true);

        $this->notifications[] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'redirectOnEnd' => $redirectOnEnd,
            'duration' => $duration,
        ];
    }

    public function removeNotification(string $id): void
    {
        $this->notifications = array_filter(
            $this->notifications,
            fn ($notification) => $notification['id'] !== $id
        );
    }
};
?>

<div
    x-data="notificationManager"
    x-init="init()"
    class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-sm pointer-events-none"
    role="region"
    aria-live="polite"
    aria-label="Notificações"
>
    {{-- Server-side notifications --}}
    @foreach($notifications as $notification)
        <div
            x-data="notification(@js($notification))"
            x-init="show()"
            x-show="visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            @class([
                'pointer-events-auto flex items-start gap-3 p-4 rounded-lg shadow-lg backdrop-blur-sm border',
                'bg-blue-50/95 dark:bg-blue-950/95 border-blue-200 dark:border-blue-800' => $notification['type'] === 'info',
                'bg-green-50/95 dark:bg-green-950/95 border-green-200 dark:border-green-800' => $notification['type'] === 'success',
                'bg-yellow-50/95 dark:bg-yellow-950/95 border-yellow-200 dark:border-yellow-800' => $notification['type'] === 'warning',
                'bg-red-50/95 dark:bg-red-950/95 border-red-200 dark:border-red-800' => $notification['type'] === 'error',
            ])
        >
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                @if($notification['type'] === 'success')
                    <x-phosphor-check-circle class="size-5 text-green-600 dark:text-green-400" />
                @elseif($notification['type'] === 'warning')
                    <x-phosphor-warning class="size-5 text-yellow-600 dark:text-yellow-400" />
                @elseif($notification['type'] === 'error')
                    <x-phosphor-x-circle class="size-5 text-red-600 dark:text-red-400" />
                @else
                    <x-phosphor-info class="size-5 text-blue-600 dark:text-blue-400" />
                @endif
            </div>

            {{-- Message --}}
            <div class="flex-1 text-sm">
                <p @class([
                    'font-medium',
                    'text-blue-900 dark:text-blue-100' => $notification['type'] === 'info',
                    'text-green-900 dark:text-green-100' => $notification['type'] === 'success',
                    'text-yellow-900 dark:text-yellow-100' => $notification['type'] === 'warning',
                    'text-red-900 dark:text-red-100' => $notification['type'] === 'error',
                ])>
                    {{ $notification['message'] }}
                </p>
            </div>

            {{-- Close button --}}
            <button
                type="button"
                @click="remove()"
                @class([
                    'shrink-0 p-1 rounded hover:bg-black/5 dark:hover:bg-white/5 transition-colors',
                    'text-blue-600 dark:text-blue-400' => $notification['type'] === 'info',
                    'text-green-600 dark:text-green-400' => $notification['type'] === 'success',
                    'text-yellow-600 dark:text-yellow-400' => $notification['type'] === 'warning',
                    'text-red-600 dark:text-red-400' => $notification['type'] === 'error',
                ])
                aria-label="Fechar notificação"
            >
                <x-phosphor-x class="size-4" />
            </button>
        </div>
    @endforeach

    {{-- Client-side notifications container --}}
    <template x-for="notification in clientNotifications" :key="notification.id">
        <div
            x-data="notification(notification)"
            x-init="show()"
            x-show="visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'pointer-events-auto flex items-start gap-3 p-4 rounded-lg shadow-lg backdrop-blur-sm border': true,
                'bg-blue-50/95 dark:bg-blue-950/95 border-blue-200 dark:border-blue-800': notification.type === 'info',
                'bg-green-50/95 dark:bg-green-950/95 border-green-200 dark:border-green-800': notification.type === 'success',
                'bg-yellow-50/95 dark:bg-yellow-950/95 border-yellow-200 dark:border-yellow-800': notification.type === 'warning',
                'bg-red-50/95 dark:bg-red-950/95 border-red-200 dark:border-red-800': notification.type === 'error',
            }"
        >
            {{-- Icon (dynamic) --}}
            <div class="shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" :class="{
                    'size-5 text-green-600 dark:text-green-400': notification.type === 'success',
                    'size-5 text-yellow-600 dark:text-yellow-400': notification.type === 'warning',
                    'size-5 text-red-600 dark:text-red-400': notification.type === 'error',
                    'size-5 text-blue-600 dark:text-blue-400': notification.type === 'info',
                }">
                    <template x-if="notification.type === 'success'">
                        <path fill="currentColor" d="M173.66 98.34a8 8 0 0 1 0 11.32l-56 56a8 8 0 0 1-11.32 0l-24-24a8 8 0 0 1 11.32-11.32L112 148.69l50.34-50.35a8 8 0 0 1 11.32 0M232 128A104 104 0 1 1 128 24a104.11 104.11 0 0 1 104 104m-16 0a88 88 0 1 0-88 88a88.1 88.1 0 0 0 88-88"/>
                    </template>
                    <template x-if="notification.type === 'warning'">
                        <path fill="currentColor" d="M236.8 188.09L149.35 36.22a24.76 24.76 0 0 0-42.7 0L19.2 188.09a23.51 23.51 0 0 0 0 23.72A24.35 24.35 0 0 0 40.55 224h174.9a24.35 24.35 0 0 0 21.33-12.19a23.51 23.51 0 0 0 .02-23.72M120 104a8 8 0 0 1 16 0v40a8 8 0 0 1-16 0Zm8 88a12 12 0 1 1 12-12a12 12 0 0 1-12 12"/>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <path fill="currentColor" d="M165.66 101.66L139.31 128l26.35 26.34a8 8 0 0 1-11.32 11.32L128 139.31l-26.34 26.35a8 8 0 0 1-11.32-11.32L116.69 128l-26.35-26.34a8 8 0 0 1 11.32-11.32L128 116.69l26.34-26.35a8 8 0 0 1 11.32 11.32M232 128A104 104 0 1 1 128 24a104.11 104.11 0 0 1 104 104m-16 0a88 88 0 1 0-88 88a88.1 88.1 0 0 0 88-88"/>
                    </template>
                    <template x-if="notification.type === 'info'">
                        <path fill="currentColor" d="M128 24a104 104 0 1 0 104 104A104.11 104.11 0 0 0 128 24m-4 48a12 12 0 1 1-12 12a12 12 0 0 1 12-12m12 112a16 16 0 0 1-16-16v-40a8 8 0 0 1 0-16a16 16 0 0 1 16 16v40a8 8 0 0 1 0 16"/>
                    </template>
                </svg>
            </div>

            {{-- Message --}}
            <div class="flex-1 text-sm">
                <p :class="{
                    'font-medium text-blue-900 dark:text-blue-100': notification.type === 'info',
                    'font-medium text-green-900 dark:text-green-100': notification.type === 'success',
                    'font-medium text-yellow-900 dark:text-yellow-100': notification.type === 'warning',
                    'font-medium text-red-900 dark:text-red-100': notification.type === 'error',
                }" x-text="notification.message"></p>
            </div>

            {{-- Close button --}}
            <button
                type="button"
                @click="remove()"
                :class="{
                    'shrink-0 p-1 rounded hover:bg-black/5 dark:hover:bg-white/5 transition-colors': true,
                    'text-blue-600 dark:text-blue-400': notification.type === 'info',
                    'text-green-600 dark:text-green-400': notification.type === 'success',
                    'text-yellow-600 dark:text-yellow-400': notification.type === 'warning',
                    'text-red-600 dark:text-red-400': notification.type === 'error',
                }"
                aria-label="Fechar notificação"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="size-4" fill="currentColor">
                    <path d="M205.66 194.34a8 8 0 0 1-11.32 11.32L128 139.31l-66.34 66.35a8 8 0 0 1-11.32-11.32L116.69 128L50.34 61.66a8 8 0 0 1 11.32-11.32L128 116.69l66.34-66.35a8 8 0 0 1 11.32 11.32L139.31 128Z"/>
                </svg>
            </button>
        </div>
    </template>
</div>

@script
<script>
(() => {
    const register = () => {
        if (!window.Alpine || window.__notifyAlpineRegistered) {
            return !!window.Alpine;
        }

        window.__notifyAlpineRegistered = true;

        window.Alpine.data('notificationManager', () => ({
            clientNotifications: [],

            init() {
                window.addEventListener('notify-client', (event) => {
                    this.addClientNotification(event.detail);
                });
            },

            addClientNotification(detail) {
                const notification = {
                    id: `client_${Date.now()}_${Math.random()}`,
                    type: detail.type || 'info',
                    message: detail.message || '',
                    redirectOnEnd: detail.redirectOnEnd || null,
                    duration: detail.duration || 5000,
                };

                this.clientNotifications.push(notification);

                if (notification.duration > 0) {
                    setTimeout(() => {
                        this.removeClientNotification(notification.id);
                    }, notification.duration);
                }
            },

            removeClientNotification(id) {
                const index = this.clientNotifications.findIndex(n => n.id === id);
                if (index > -1) {
                    this.clientNotifications.splice(index, 1);
                }
            }
        }));

        window.Alpine.data('notification', (data) => ({
            visible: false,
            data: data,

            show() {
                setTimeout(() => {
                    this.visible = true;
                }, 10);

                if (data.duration > 0) {
                    setTimeout(() => {
                        this.remove();
                    }, data.duration);
                }
            },

            remove() {
                this.visible = false;

                setTimeout(() => {
                    if (data.id && data.id.startsWith('notify_')) {
                        this.$wire.removeNotification(data.id);
                    }

                    if (data.redirectOnEnd) {
                        window.location.href = data.redirectOnEnd;
                    }
                }, 200);
            }
        }));

        window.notify = function(message, type = 'info', redirectOnEnd = null, duration = 5000) {
            window.dispatchEvent(new CustomEvent('notify-client', {
                detail: { type, message, redirectOnEnd, duration }
            }));
        };

        return true;
    };

    if (!register()) {
        document.addEventListener('alpine:init', register);
    }
})();
</script>
@endscript