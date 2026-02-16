<?php

use Illuminate\Support\Facades\DB;

it('can connect to the chatwoot database', function () {
    $result = DB::connection('chatwoot')->select('SELECT 1 as connection_test');

    expect($result)->not->toBeEmpty()
        ->and($result[0]->connection_test)->toBe(1);
});

it('can query the channel_api table in chatwoot database', function () {
    try {
        $exists = DB::connection('chatwoot')->getSchemaBuilder()->hasTable('channel_api');
        expect($exists)->toBeTrue();
    } catch (\Exception $e) {
        $this->fail('Erro ao acessar tabela channel_api: '.$e->getMessage());
    }
});
