<?php

namespace Tests\App\Http\Response;

namespace App;

use TestCase;
use Mockery as m;
use League\Fractal\Manager;
use App\Http\Response\FractalResponse;
use League\Fractal\Serializer\SerializerAbstract;

class FractalResponseTest extends TestCase
{
    public function test_it_can_be_initialized()
    {
        $manager = m::mock(Manager::class);
        $serializer = m::mock(SerializerAbstract::class);

        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once()
            ->andReturn($manager);

        $fractal = new FractalResponse($manager, $serializer);
        $this->assertInstanceOf(FractalResponse::class, $fractal);
    }

    public function test_it_can_transform_an_item()
    {
        $transformer = m::mock('League\Fractal\TransformerAbstract');

        $scope = m::mock('League\Fractal\Scope');
        $scope
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['foo' => 'bar']);

        // Serializer
        $serializer = m::mock('League\Fractal\Serializer\SerializerAbstract');
        $manager = m::mock('League\Fractal\Manager');
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once();
        $manager
            ->shouldReceive('createData')
            ->once()
            ->andReturn($scope);
        $subject = new FractalResponse($manager, $serializer);
        $this->assertInternalType(
            'array',
            $subject->item(['foo' => 'bar'], $transformer)
        );
    }

    public function test_it_can_transform_a_collection()
    {
        $data = [
            ['foo' => 'bar'],
            ['fizz' => 'buzz']
        ];

        $transformer = m::mock('League\Fractal\TransformerAbstract');

        $scope = m::mock('League\Fractal\Scope');
        $scope
            ->shouldReceive('toArray')
            ->once()
            ->andReturn($data);
    

        $serializer = m::mock('League\Fractal\Serializer\SerializerAbstract');
        $manager = m::mock('League\Fractal\Manager');
        $manager
            ->shouldReceive('setSerializer')
            ->with($serializer)
            ->once();
        $manager
            ->shouldReceive('createData')
            ->once()
            ->andReturn($scope);
        $subject = new FractalResponse($manager, $serializer);
        $this->assertInternalType(
            'array',
            $subject->collection($data, $transformer)
        );
    }
}
