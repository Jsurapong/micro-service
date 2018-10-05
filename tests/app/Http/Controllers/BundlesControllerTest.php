<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BundlesControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_show_should_return_a_valid_bundle()
    {
        $bundle = $this->bundleFactory();

        \Log::info(print_r($bundle, true));


        $this->get('/bundles/'.$bundle->id, ['Accept' => 'application/json']);
        $this->seeStatusCode(200);
        $body = $this->response->getData(true);
        
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];



        $this->assertEquals($bundle->id, $data['id']);
        $this->assertEquals($bundle->title, $data['title']);
        $this->assertEquals($bundle->description, $data['description']);
        $this->assertEquals($bundle->created_at->toIso8601String(), $data['created']);
        $this->assertEquals($bundle->updated_at->toIso8601String(), $data['updated']);

        $this->assertArrayHasKey('books', $data);
        $books = $data['books'];

        $this->assertArrayHasKey('data', $books);
        $this->assertCount(2, $books['data']);

        $this->assertEquals(
            $bundle->books[0]->title,
            $books['data'][0]['title']
        );

        $this->assertEquals(
            $bundle->books[0]->description,
            $books['data'][0]['description']
        );

        $this->assertEquals(
            $bundle->books[0]->created_at->toIso8601String(),
            $books['data'][0]['created']
        );

        $this->assertEquals(
            $bundle->books[0]->updated_at->toIso8601String(),
            $books['data'][0]['updated']
        );
    }
}
