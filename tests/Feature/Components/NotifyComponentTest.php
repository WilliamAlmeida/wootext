<?php

use App\Livewire\Components\Notify;
use Livewire\Livewire;

it('can add server-side notification', function () {
    Livewire::test(Notify::class)
        ->dispatch('notify', type: 'success', message: 'Test notification')
        ->assertSee('Test notification');
});

it('supports different notification types', function () {
    $types = ['info', 'success', 'warning', 'error'];

    foreach ($types as $type) {
        Livewire::test(Notify::class)
            ->dispatch('notify', type: $type, message: "Test {$type}")
            ->assertSee("Test {$type}");
    }
});

it('can remove notification', function () {
    $component = Livewire::test(Notify::class)
        ->dispatch('notify', type: 'info', message: 'Test notification');

    $notificationId = $component->get('notifications')[0]['id'];

    $component
        ->call('removeNotification', $notificationId)
        ->assertDontSee('Test notification');
});

it('supports redirect on end parameter', function () {
    Livewire::test(Notify::class)
        ->dispatch('notify',
            type: 'success',
            message: 'Redirecting...',
            redirectOnEnd: '/dashboard'
        )
        ->assertSet('notifications.0.redirectOnEnd', '/dashboard');
});

it('supports custom duration', function () {
    Livewire::test(Notify::class)
        ->dispatch('notify',
            type: 'error',
            message: 'Custom duration',
            duration: 10000
        )
        ->assertSet('notifications.0.duration', 10000);
});

it('can handle multiple notifications', function () {
    Livewire::test(Notify::class)
        ->dispatch('notify', type: 'success', message: 'First')
        ->dispatch('notify', type: 'error', message: 'Second')
        ->assertSee('First')
        ->assertSee('Second')
        ->assertCount('notifications', 2);
});
