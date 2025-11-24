<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name',
        'books_count'
    ];

    protected $casts = [
        'books_count' => 'integer'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['books_count'];

    /**
     * Relationship: An author has many books
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    /**
     * Get the books_count attribute (calculated dynamically)
     * This ensures the count is always accurate and never out of sync
     *
     * @return int
     */
    public function getBooksCountAttribute()
    {
        // If the relationship is already loaded, use it for better performance
        if ($this->relationLoaded('books')) {
            return $this->books->count();
        }

        // Otherwise, count from the database
        return $this->books()->count();
    }
}
