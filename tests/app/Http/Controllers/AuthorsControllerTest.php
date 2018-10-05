<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthorsControllerTest extends TestCase
{
    use DatabaseMigrations;


    public function test_index_responds_with_200_status_code()
    {
        $this->get('/authors')->seeStatusCode(Response::HTTP_OK);
    }

    public function test_index_should_return_a_collection_of_records()
    {
        $authors = factory(\App\Author::class, 2)->create();
        $this->get('/authors', ['Accept' => 'application/json']);
    
        $body = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertCount(2, $body['data']);


        foreach ($authors as $key => $author) {
            $this->seeJson([
                'id' => $author->id,
                'name' => $author->name,
                'gender' => $author->gender,
                'biography' => $author->biography,
                'created' => $author->created_at->toIso8601String(),
                'updated' => $author->updated_at->toIso8601String(),
            ]);
        }
    }

    public function test_show_optionally_includes_books()
    {
        $book = $this->bookFactory();

        $author = $book->author;
        $this->get(
            '/authors/'.$author->id.'?include=books',
            ['Accept' => 'application/json']
        );

        $body = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('data', $data['books']);
        $this->assertCount(1, $data['books']['data']);

        $this->seeJson([
            'id' => $author->id,
            'name' => $author->name,
        ]);

        $actual = $data['books']['data'][0];

        $this->assertEquals($book->id, $actual['id']);
        $this->assertEquals($book->title, $actual['title']);
        $this->assertEquals($book->description, $actual['description']);
        $this->assertEquals($book->created_at->toIso8601String(), $actual['created']);
        $this->assertEquals($book->updated_at->toIso8601String(), $actual['updated']);
    }

    public function test_store_method_validates_required_fields()
    {
        $this->post('/authors', [], ['Accept' => 'application/json']);

        $data = $this->response->getData(true);

        $fields = ['name','gender','biography'];
    
        foreach ($fields as $key => $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(['The '.$field.' field is required.'], $data[$field]);
        }
    }

    public function test_store_invalidates_incorrect_gender_data()
    {
        $postData = [
            'name' => 'John Doe',
            'gender' => 'unknown',
            'biography' => 'An anonymous author'
        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);

        $data = $this->response->getData(true);

        $this->seeStatusCode(422);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('gender', $data);
        $this->assertEquals(["Gender format is invalid: must equal 'male' or 'female'"], $data['gender']);
    }

    public function test_update_method_validates_required_fields()
    {
        $author = factory(\App\Author::class)->create();
        $this->put('/authors/'.$author->id, [], ['Accept' => 'application/json']);
        $this->seeStatusCode(422);

        $data = $this->response->getData(true);

        $fields = ['name','gender','biography'];

        foreach ($fields as $key => $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertEquals(['The '.$field.' field is required.'], $data[$field]);
        }
    }

    public function test_store_can_create_a_new_author()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer'
        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);

        $this->seeStatusCode(201);
        $data = $this->response->getData(true);
        $this->assertArrayHasKey('data', $data);
        $this->seeJson($postData);

        $this->seeInDatabase('authors', $postData);
    }

    public function test_store_is_valid_when_name_is_just_long_enough()
    {
        $postData = [
            'name' => str_repeat('a', 255),
            'gender' => 'male',
            'biography' => 'A Valid Biography'

        ];

        $this->post('/authors', $postData, ['Accept' => 'application/json']);
        $this->seeStatusCode(201);
        $this->seeInDatabase('authors', $postData);
    }

    public function test_store_returns_a_valid_location_header()
    {
        $postData = [
            'name' => 'H. G. Wells',
            'gender' => 'male',
            'biography' => 'Prolific Science-Fiction Writer'

        ];

        $this
            ->post('/authors', $postData, ['Accept' => 'application/json'])
            ->seeStatusCode(201);

        $data = $this->response->getData(true);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('id', $data['data']);

        $id = $data['data']['id'];
        $this->seeHeaderWithRegExp('Location', '#/authors/'.$id.'$#');
    }

    public function test_delete_can_remove_an_author_and_his_or_her_books()
    {
        $author = factory(\App\Author::class)->create();


        $this
            ->delete('/authors/'.$author->id)
            ->seeStatusCode(204)
            ->notSeeInDatabase('authors', ['id' => $author->id])
            ->notSeeInDatabase('books', ['author_id' => $author->id]);
    }

    public function test_deleting_an_invalid_author_should_return_a_404()
    {
        $this
            ->delete('/authors/99999', [], ['Accept' => 'application/json'])
            ->seeStatusCode(404);
    }

    public function test_store_fails_when_the_author_is_invalid()
    {
        $this->post('/authors/1/ratings', [], ['Accept' => 'application/json']);
        $this->seeStatusCode(404);
    }
}
