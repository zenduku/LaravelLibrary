<?php

namespace App\Events;

use App\Book;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookUpdated
{
    use Dispatchable, SerializesModels;

    public $book;
    public $oldAuthorId;

    /**
     * Create a new event instance.
     *
     * @param  \App\Book  $book
     * @param  int|null  $oldAuthorId
     * @return void
     */
    public function __construct(Book $book, $oldAuthorId = null)
    {
        $this->book = $book;
        $this->oldAuthorId = $oldAuthorId;
    }
}
