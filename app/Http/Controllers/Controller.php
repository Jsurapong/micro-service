<?php

namespace App\Http\Controllers;

use App\Http\Response\FractalResponse;
use League\Fractal\TransformerAbstract;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    private $fractal;


    public function __construct(FractalResponse $fractal)
    {
        $this->fractal = $fractal;
        $this->fractal->parseIncludes();
    }

    public function item($data, TransformerAbstract $transformer, $resourceKey = null)
    {
        return $this->fractal->item($data, $transformer, $resourceKey);
    }

    public function collection($data, TransformerAbstract $transformer, $resourceKey = null)
    {
        return $this->fractal->collection($data, $transformer, $resourceKey);
    }
}
