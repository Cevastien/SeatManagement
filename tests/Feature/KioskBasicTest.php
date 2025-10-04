<?php

declare(strict_types=1);

test('kiosk welcome screen loads without database', function () {
    $response = $this->get('/kiosk');
    
    $response->assertStatus(200);
    $response->assertSee('Welcome to Gervacios Restaurant');
    $response->assertSee('Join Queue');
});

test('kiosk queue interface loads without database', function () {
    $response = $this->get('/kiosk/queue');
    
    $response->assertStatus(200);
    $response->assertSee('Join Our Waitlist');
    $response->assertSee('Your Name');
    $response->assertSee('Party Size');
});

test('kiosk health check endpoint works', function () {
    $response = $this->get('/kiosk/health');
    
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'healthy',
        'version' => '1.0.0'
    ]);
});

test('kiosk reset endpoint works', function () {
    $response = $this->post('/kiosk/reset');
    
    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'reset',
        'message' => 'Kiosk has been reset successfully'
    ]);
});

