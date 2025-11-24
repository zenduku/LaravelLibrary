<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->token = JWTAuth::fromUser($this->user);
        $this->author = Author::create(['name' => 'Test Author']);
    }

    /**
     * Test can create book
     *
     * @return void
     */
    public function testCanCreateBook()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/books', [
                             'title' => 'Test Book',
                             'publication_date' => 2020,
                             'author_id' => $this->author->id,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'book' => ['id', 'title', 'author_id']
                 ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Test Book',
            'author_id' => $this->author->id,
        ]);

        // Verify that author's books_count was updated
        $this->author->refresh();
        $this->assertEquals(1, $this->author->books_count);
    }

    /**
     * Test books_count updates when book is created
     *
     * @return void
     */
    public function testBooksCountUpdatesOnCreate()
    {
        $this->assertEquals(0, $this->author->books_count);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
             ->postJson('/api/books', [
                 'title' => 'Book 1',
                 'publication_date' => 2020,
                 'author_id' => $this->author->id,
             ]);

        $this->author->refresh();
        $this->assertEquals(1, $this->author->books_count);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
             ->postJson('/api/books', [
                 'title' => 'Book 2',
                 'publication_date' => 2021,
                 'author_id' => $this->author->id,
             ]);

        $this->author->refresh();
        $this->assertEquals(2, $this->author->books_count);
    }

    /**
     * Test books_count updates when book is deleted
     *
     * @return void
     */
    public function testBooksCountUpdatesOnDelete()
    {
        $book = Book::create([
            'title' => 'Test Book',
            'publication_date' => 2020,
            'author_id' => $this->author->id,
        ]);

        $this->author->refresh();
        $this->assertEquals(1, $this->author->books_count);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
             ->deleteJson('/api/books/' . $book->id);

        $this->author->refresh();
        $this->assertEquals(0, $this->author->books_count);
    }

    /**
     * Test books_count updates when author changes
     *
     * @return void
     */
    public function testBooksCountUpdatesWhenAuthorChanges()
    {
        $author2 = Author::create(['name' => 'Author 2']);
        $book = Book::create([
            'title' => 'Test Book',
            'publication_date' => 2020,
            'author_id' => $this->author->id,
        ]);

        $this->author->refresh();
        $this->assertEquals(1, $this->author->books_count);
        $this->assertEquals(0, $author2->books_count);

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
             ->putJson('/api/books/' . $book->id, [
                 'author_id' => $author2->id,
             ]);

        // Refresh relationships to ensure accurate count
        $this->author->load('books');
        $author2->load('books');

        $this->assertEquals(0, $this->author->books_count);
        $this->assertEquals(1, $author2->books_count);
    }
}
