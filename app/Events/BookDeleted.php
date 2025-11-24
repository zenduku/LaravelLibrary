<?php

namespace App\Events;

use App\Book;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookDeleted
{
    use Dispatchable, SerializesModels;

    public $book;

    /**
     * Create a new event instance.
     *
     * @param  \App\Book  $book
     * @return void
     */
    public function __construct(Book $book)
    {
        $this->book = $book;
    }
}
