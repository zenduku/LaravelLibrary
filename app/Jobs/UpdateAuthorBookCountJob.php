<?php

namespace App\Jobs;

use App\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAuthorBookCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $authorId;
    protected $action; // 'increment', 'decrement', or 'recalculate'

    /**
     * Create a new job instance.
     *
     * @param  int  $authorId
     * @param  string  $action
     * @return void
     */
    public function __construct($authorId, $action = 'recalculate')
    {
        $this->authorId = $authorId;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $author = Author::find($this->authorId);
        
        if (!$author) {
            return;
        }

        switch ($this->action) {
            case 'increment':
                $author->increment('books_count');
                break;
            case 'decrement':
                $author->decrement('books_count');
                break;
            case 'recalculate':
            default:
                // Recalculate based on actual count
                $actualCount = $author->books()->count();
                $author->update(['books_count' => $actualCount]);
                break;
        }
    }
}
