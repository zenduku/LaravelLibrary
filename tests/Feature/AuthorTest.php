<?php

namespace Tests\Feature;

use App\Author;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test can create author
     *
     * @return void
     */
    public function testCanCreateAuthor()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/authors', [
                             'name' => 'Gabriel García Márquez',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'author' => ['id', 'name', 'books_count']
                 ])
                 ->assertJson([
                     'author' => [
                         'books_count' => 0
                     ]
                 ]);

        $this->assertDatabaseHas('authors', [
            'name' => 'Gabriel García Márquez',
        ]);
    }

    /**
     * Test can list authors
     *
     * @return void
     */
    public function testCanListAuthors()
    {
        Author::create(['name' => 'Author 1']);
        Author::create(['name' => 'Author 2']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/authors');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    /**
     * Test can get specific author
     *
     * @return void
     */
    public function testCanGetAuthor()
    {
        $author = Author::create(['name' => 'Test Author']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/authors/' . $author->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $author->id,
                     'name' => 'Test Author',
                 ]);
    }

    /**
     * Test can update author
     *
     * @return void
     */
    public function testCanUpdateAuthor()
    {
        $author = Author::create(['name' => 'Original Name']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson('/api/authors/' . $author->id, [
                             'name' => 'Updated Name',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'author' => [
                         'name' => 'Updated Name'
                     ]
                 ]);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test can delete author
     *
     * @return void
     */
    public function testCanDeleteAuthor()
    {
        $author = Author::create(['name' => 'Test Author']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->deleteJson('/api/authors/' . $author->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('authors', [
            'id' => $author->id,
        ]);
    }
}
