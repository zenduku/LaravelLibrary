<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'publication_date',
        'author_id'
    ];

    protected $casts = [
        'publication_date' => 'integer'
    ];

    /**
     * Temporary storage for old author_id during update
     * This is not a database column, just a temporary property
     */
    public $old_author_id;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($book) {
            event(new \App\Events\BookCreated($book));
        });

        static::updating(function ($book) {
            if ($book->exists && !isset($book->old_author_id)) {
                $book->old_author_id = $book->getOriginal('author_id') ?? $book->author_id;
            }
        });

        static::updated(function ($book) {
            if ($book->wasChanged('author_id')) {
                $oldAuthorId = $book->old_author_id ?? $book->getOriginal('author_id');
                event(new \App\Events\BookUpdated($book, $oldAuthorId));
            }
        });

        static::deleted(function ($book) {
            event(new \App\Events\BookDeleted($book));
        });
    }

    /**
     * Relationship: A book belongs to an author
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
