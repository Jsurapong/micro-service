<?php

namespace Tests\App\Transformer;

use TestCase;

use App\Transformer\BundleTransformer;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BundleTransformerTest extends TestCase
{
    use DatabaseMigrations;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->subject = new BundleTransformer();
    }

    public function test_it_can_be_initialized()
    {
        $this->assertInstanceOf(
            BundleTransformer::class,
            $this->subject
        );
    }

    public function test_it_can_transform_a_bundle()
    {
        $bundle = factory(\App\Bundle::class)->create();
        $actual = $this->subject->transform($bundle);

        $this->assertEquals($bundle->id, $actual['id']);
        $this->assertEquals($bundle->title, $actual['title']);
        $this->assertEquals($bundle->description, $actual['description']);
        $this->assertEquals($bundle->created_at->toIso8601String(), $actual['created']);
        $this->assertEquals($bundle->updated_at->toIso8601String(), $actual['updated']);
    }

    public function test_it_can_transform_related_books()
    {
        $bundle = $this->bundleFactory();
        $data = $this->subject->includeBooks($bundle);
        $this->assertInstanceOf(\League\Fractal\Resource\Collection::class, $data);
        $this->assertInstanceOf(\App\Book::class, $data->getData()[0]);
        $this->assertCount(2, $data->getData());
    }

    public function test_addBook_should_add_a_book_to_a_bundle()
    {
        $bundle = factory(\App\Bundle::class)->create();
        $book = $this->bookFactory();

        $this->notSeeInDatabase('book_bundle', ['bundle_id' => $bundle->id]);

        $this->put('/bundles/'.$bundle->id.'/books/'.$book->id, [], ['Accept' => 'application/json']);
        $this->seeStatusCode(200);

        $dbBundle = \App\Bundle::with('books')->find($bundle->id);

        $this->assertCount(1, $dbBundle->books, 'The bundle should have 1 associated book');
        $this->assertEquals($dbBundle->books()->first()->id, $book->id);

        $body = $this->response->getData(true);

        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('books', $body['data']);
        $this->assertArrayHasKey('data', $body['data']['books']);


        $books = $body['data']['books'];
        $this->assertEquals($book->id, $books['data'][0]['id']);
    }


    public function test_removeBook_should_remove_a_book_from_a_bundle()
    {
        $bundle = $this->bundleFactory(3);
        $book = $bundle->books()->first();

        $this->seeInDatabase('book_bundle', [
            'book_id' => $book->id,
            'bundle_id' => $bundle->id
            ]);
        $this->assertCount(3, $bundle->books);
        $this
            ->delete('/bundles/'.$bundle->id.'/books/'.$book->id)
            ->seeStatusCode(204)
            ->notSeeInDatabase('book_bundle', [
                'book_id' => $book->id,
                'bundle_id' => $bundle->id
            ]);
        $dbBundle = \App\Bundle::find($bundle->id);
        $this->assertCount(2, $dbBundle->books);
    }
}
