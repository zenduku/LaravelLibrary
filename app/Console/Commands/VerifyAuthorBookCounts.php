<?php

namespace App\Console\Commands;

use App\Jobs\VerifyAndFixAuthorBookCountJob;
use Illuminate\Console\Command;

class VerifyAuthorBookCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authors:verify-books-count {--author-id= : Verify a specific author by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and fix books_count for authors (checks if stored count matches actual count)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $authorId = $this->option('author-id');

        if ($authorId) {
            $this->info("Verifying books_count for author ID: {$authorId}");
            VerifyAndFixAuthorBookCountJob::dispatch($authorId);
        } else {
            $this->info("Verifying books_count for all authors...");
            VerifyAndFixAuthorBookCountJob::dispatch();
        }

        $this->info("Verification job dispatched successfully!");
        $this->info("Check the logs for any corrections made.");

        return 0;
    }
}
