<?php

namespace Tests\App\Http\Controllers;

use TestCase;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthorsControllerValidationTest extends TestCase
{
    use DatabaseMigrations;

    private function getValidationTestData()
    {
        $author = factory(\App\Author::class)->create();
        $tests = [
            [
                'method' => 'post',
                'url' => '/authors',
                'data' => [
                    'name' => 'John Doe',
                    'gender' => 'male',
                    'biography' => 'An anonymous author'
                ]
            ],
            [
                'method' => 'put',
                'url' => '/authors/'.$author->id,
                'data' => [
                    'name' => $author->name,
                    'gender' => $author->gender,
                    'biography' => $author->biography
                ]
            ]
        ];
        return $tests;
    }

    public function test_validation_validates_required_fields()
    {
        $tests = $this->getValidationTestData();

        foreach ($tests as $key => $test) {
            $method = $test['method'];
            $this->$method($test['url'], [], ['Accept' => 'application/json']);
            $this->seeStatusCode(422);
            $data = $this->response->getData(true);

            $fields = ['name','gender','biography'];

            foreach ($fields as $key => $field) {
                $this->assertArrayHasKey($field, $data);
                $this->assertEquals(['The '.$field.' field is required.'], $data[$field]);
            }
        }
    }


    public function test_validation_invalidates_incorrect_gender_data()
    {
        $tests = $this->getValidationTestData();
        
        foreach ($tests as $key => $test) {
            $method = $test['method'];
            $test['data']['gender']  ='unknown';


            $this->$method($test['url'], $test['data'], ['Accept' => 'application/json']);
            $this->seeStatusCode(422);

            $data = $this->response->getData(true);

            $this->assertCount(1, $data);
            $this->assertArrayHasKey('gender', $data);
            $this->assertEquals(["Gender format is invalid: must equal 'male' or 'female'"], $data['gender']);
        }
    }



    public function test_validation_invalidates_name_when_name_is_just_too_long()
    {
        $tests = $this->getValidationTestData();
        
        foreach ($tests as $key => $test) {
            $method = $test['method'];
            
            $test['data']['name'] = str_repeat('a', 256);

            $this->$method($test['url'], $test['data'], ['Accept' => 'application/json']);
            $this->seeStatusCode(422);

            $data = $this->response->getData(true);

            $this->assertCount(1, $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertEquals(["The name may not be greater than 255 characters."], $data['name']);
        }
    }
}
