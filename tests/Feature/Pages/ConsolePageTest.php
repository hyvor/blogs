<?php

namespace Tests\Feature\Pages;

it('loads console', function() {
   
    $this->get('/console')
        ->assertOk()
        ->assertSee('Console');
    
});

it('redirects to login when the user is not logged in', function() {
    
    config(['hyvorconnecter.dummy' => false]);
   
    $this->get('/console')
        ->assertRedirectContains('login')
        ->assertRedirectContains('redirect=')
        ->assertRedirectContains('console');
    
});
