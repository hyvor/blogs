<?php
namespace App\Domains\Tag\Observers;

use App\Models\Tag;
use App\Domains\Tag\Events\TagEvent;
use App\Domains\Tag\Events\UpdateTagEvent;

// Event Testing - https://laracasts.com/discuss/channels/eloquent/event-listener-or-model-observer 
// Observers, Events, Listeners - https://iwconnect.com/using-laravel-observers-and-events-to-create-history-logs/
// https://www.itsolutionstuff.com/post/laravel-8-model-observers-tutorial-exampleexample.html#:~:text=Laravel%20Observers%20are%20used%20to,like%20create%2C%20update%20and%20delete.&text=Retrieved%3A%20after%20a%20record%20has,a%20record%20has%20been%20created.
// https://dev.to/kingsconsult/laravel-8-events-and-listeners-with-practical-example-9m7


class TagObserver
{
    /**
     * Handle the Tag "created" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function creating(Tag $tag)
    {
        
    }
  
    /**
     * Handle the Tag "created" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function created(Tag $tag)
    {
        // So here this working fine.
        // Now we should find a way to create the webhook using this method.
        
        // $tag->slug = 'hello.com';
        event(new TagEvent($tag));
    }
  
     /**
     * Handle the Tag "updated" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function updating(Tag $tag)
    {
        dd('Observer');
        event(new UpdateTagEvent($tag));
    }

    /**
     * Handle the Tag "updated" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function updated(Tag $tag)
    {
        dd('Observer');
        event(new TagEvent($tag));
    }
  
    /**
     * Handle the Tag "deleted" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function deleted(Tag $tag)
    {
          
    }
  
    /**
     * Handle the Tag "restored" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function restored(Tag $tag)
    {
          
    }
  
    /**
     * Handle the Tag "force deleted" event.
     *
     * @param  \App\Models\Tag  $tag
     * @return void
     */
    public function forceDeleted(Tag $tag)
    {
          
    }
}