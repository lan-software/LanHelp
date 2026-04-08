<?php

use Illuminate\Support\Facades\Config;

it('blocks demo registration via guardrail middleware', function (): void {
    Config::set('app.demo', true);

    $status = $this->post('/register', [
        'name' => 'Demo',
        'email' => 'demo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->status();

    expect($status)->toBeIn([403, 404, 405]);
});
