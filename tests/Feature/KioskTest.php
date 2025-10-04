<?php

declare(strict_types=1);

use Livewire\Livewire;
use App\Livewire\Kiosk\WelcomeScreen;
use App\Livewire\Kiosk\QueueInterface;

test('kiosk welcome screen loads correctly', function () {
    $response = $this->get('/kiosk');
    
    $response->assertStatus(200);
    $response->assertSee('Welcome to Gervacios Restaurant');
    $response->assertSee('Join Queue');
});

test('kiosk queue interface loads correctly', function () {
    $response = $this->get('/kiosk/queue');
    
    $response->assertStatus(200);
    $response->assertSee('Join Our Waitlist');
    $response->assertSee('Your Name');
    $response->assertSee('Email Address');
    $response->assertSee('Party Size');
});

test('welcome screen livewire component works', function () {
    Livewire::test(WelcomeScreen::class)
        ->assertSee('Welcome to Gervacios Restaurant')
        ->assertSee('Join Queue')
        ->call('joinQueue')
        ->assertRedirect(route('kiosk.queue'));
});

test('queue interface validation works', function () {
    Livewire::test(QueueInterface::class)
        ->set('customerName', '')
        ->set('partySize', 0)
        ->call('joinQueue')
        ->assertHasErrors(['customerName', 'partySize']);
});

test('queue interface accepts valid data', function () {
    Livewire::test(QueueInterface::class)
        ->set('customerName', 'John Doe')
        ->set('contactNumber', '+1-555-123-4567')
        ->set('partySize', 2)
        ->set('specialRequests', 'Window seat preferred')
        ->call('joinQueue')
        ->assertSet('showConfirmation', true)
        ->assertSee('You\'re in the Queue!')
        ->assertSee('John Doe')
        ->assertSee('#1'); // Queue number
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

