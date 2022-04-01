<?php
namespace App\Domains\User\Observers;

use App\Models\User;
use App\Models\UsersVariant;
use App\Domains\User\Events\UserEvents;


// https://iwconnect.com/using-laravel-observers-and-events-to-create-history-logs/
// https://www.itsolutionstuff.com/post/laravel-8-model-observers-tutorial-exampleexample.html#:~:text=Laravel%20Observers%20are%20used%20to,like%20create%2C%20update%20and%20delete.&text=Retrieved%3A%20after%20a%20record%20has,a%20record%20has%20been%20created.

class UserObservers
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        // So here this working fine.
        // Now we should find a way to create the webhook using this method.
        
        // $user->slug = 'hello.com';
        event(new UserEvents($user));
    }
  
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        
    }
  
    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
          
    }
  
    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
          
    }
  
    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
          
    }
  
    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
          
    }
}