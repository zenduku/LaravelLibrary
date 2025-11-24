<?php

namespace App\Jobs;

use App\Author;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyAndFixAuthorBookCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $authorId;

    /**
     * Create a new job instance.
     *
     * @param  int|null  $authorId  If null, verifies all authors
     * @return void
     */
    public function __construct($authorId = null)
    {
        $this->authorId = $authorId;
    }

    /**
     * Execute the job.
     * Verifies and fixes the books_count for one or all authors.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->authorId) {
            // Verify and fix a specific author
            $this->verifyAndFixAuthor($this->authorId);
        } else {
            // Verify and fix all authors
            $authors = Author::all();
            foreach ($authors as $author) {
                $this->verifyAndFixAuthor($author->id);
            }
        }
    }

    /**
     * Verify and fix the books_count for a specific author.
     *
     * @param  int  $authorId
     * @return void
     */
    protected function verifyAndFixAuthor($authorId)
    {
        $author = Author::find($authorId);
        
        if (!$author) {
            return;
        }

        // Get actual count from database
        $actualCount = $author->books()->count();
        
        // Get stored count directly from database (bypassing the accessor)
        $storedCount = DB::table('authors')->where('id', $authorId)->value('books_count') ?? 0;
        
        // If counts don't match, fix it
        if ($actualCount != $storedCount) {
            DB::table('authors')->where('id', $authorId)->update(['books_count' => $actualCount]);
            Log::info("Fixed books_count for author {$authorId}: {$storedCount} -> {$actualCount}");
        }
    }
}
