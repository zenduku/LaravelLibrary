<?php

namespace App\Listeners;

use App\Events\BookCreated;
use App\Events\BookDeleted;
use App\Events\BookUpdated;
use App\Jobs\UpdateAuthorBookCountJob;
use App\Jobs\VerifyAndFixAuthorBookCountJob;

class UpdateAuthorBookCount
{
    /**
     * Handle the BookCreated event.
     * Dispatch a job to increment the author's books_count when a book is created.
     *
     * @param  BookCreated  $event
     * @return void
     */
    public function handleBookCreated(BookCreated $event)
    {
        // Dispatch job to update the counter
        UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'increment');
        
        // Also dispatch verification job to ensure accuracy
        VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
    }

    /**
     * Handle the BookUpdated event.
     * Dispatch jobs to update books_count when author changes.
     *
     * @param  BookUpdated  $event
     * @return void
     */
    public function handleBookUpdated(BookUpdated $event)
    {
        $oldAuthorId = $event->oldAuthorId ?? $event->book->getOriginal('author_id');
        
        if ($oldAuthorId !== null && $oldAuthorId != $event->book->author_id) {
            // Decrement old author's count
            UpdateAuthorBookCountJob::dispatch($oldAuthorId, 'decrement');
            VerifyAndFixAuthorBookCountJob::dispatch($oldAuthorId);
            
            // Increment new author's count
            if ($event->book->author_id) {
                UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'increment');
                VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
            }
        }
    }

    /**
     * Handle the BookDeleted event.
     * Dispatch a job to decrement the author's books_count when a book is deleted.
     *
     * @param  BookDeleted  $event
     * @return void
     */
    public function handleBookDeleted(BookDeleted $event)
    {
        // Dispatch job to update the counter
        UpdateAuthorBookCountJob::dispatch($event->book->author_id, 'decrement');
        
        // Also dispatch verification job to ensure accuracy
        VerifyAndFixAuthorBookCountJob::dispatch($event->book->author_id);
    }
}
