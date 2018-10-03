<?php

namespace Tests\App\Exceptions;

use TestCase;
use \Mockery as m;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HandlerTest extends TestCase
{
    public function test_it_responds_with_html_when_json_is_not_accepted()
    {
        $subject = m::mock(Handler::class)->makePartial();
        $subject
            ->shouldNotReceive('isDebugMode')
            ->andReturn(false);

        $request = m::mock(Request::class);
        $request
            ->shouldReceive('wantsJson')
            ->andReturn(false);

        $exception = m::mock(\Exception::class, ['Error!']);
        $exception->shouldNotReceive('getStatusCode');
        $exception->shouldNotReceive('getTrace');
        $exception->shouldNotReceive('getMessage');

        $result = $subject->render($request, $exception);

        $this->assertNotInstanceOf(JsonResponse::class, $result);
    }


    public function test_it_responds_with_json_for_json_consumers()
    {
        $subject = m::mock(Handler::class)->makePartial();
        $subject
            ->shouldReceive('isDebugMode')
            ->andReturn(false);

        $request = m::mock(Request::class);
        $request
                ->shouldReceive('wantsJson')
                ->andReturn(true);
                
        $exception = m::mock(\Exception::class, ['Doh!']);
        $exception
                ->shouldReceive('getMessage')
                ->andReturn('Doh!');

       

        $result = $subject->render($request, $exception);
        $data = $result->getData();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertObjectHasAttribute('error', $data);
        $this->assertAttributeEquals('Doh!', 'message', $data->error);
        $this->assertAttributeEquals(400, 'status', $data->error);
    }

    public function test_it_provides_json_responses_for_http_exceptions()
    {
        $subject = m::mock(Handler::class)->makePartial();
        $subject
            ->shouldReceive('isDebugMode')
            ->andReturn(false);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $examples = [
            [
                'mock' => NotFoundHttpException::class,
                'status' => 404,
                'message' => 'Not Found'
            ],
            [
                'mock' => AccessDeniedHttpException::class,
                'status' => 403,
                'message' => 'Forbidden'
            ],
            [
                'mock' => ModelNotFoundException::class,
                'status' => 404,
                'message' => 'Not Found'
            ]
        ];


        foreach ($examples as $key => $example) {
            $exception = m::mock($example['mock']);
            $exception->shouldReceive('getMessage')->andReturn(null);
            $exception->shouldReceive('getStatusCode')->andReturn($example['status']);
         
            $result = $subject->render($request, $exception);

            $data = $result->getData();
        
            $this->assertEquals($example['status'], $result->getStatusCode());
            $this->assertEquals($example['message'], $data->error->message);
            $this->assertEquals($example['status'], $data->error->status);
        }
    }
}
