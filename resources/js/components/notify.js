export function registerNotify() {
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
                const index = this.clientNotifications.findIndex((notification) => notification.id === id);
                if (index > -1) {
                    this.clientNotifications.splice(index, 1);
                }
            },
        }));

        window.Alpine.data('notification', (data) => ({
            visible: false,
            data,

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
            },
        }));

        window.notify = function notify(message, type = 'info', redirectOnEnd = null, duration = 5000) {
            window.dispatchEvent(new CustomEvent('notify-client', {
                detail: { type, message, redirectOnEnd, duration },
            }));
        };

        return true;
    };

    if (!register()) {
        document.addEventListener('alpine:init', register);
    }
}
