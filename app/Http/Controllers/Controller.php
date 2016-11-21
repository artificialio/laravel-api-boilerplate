<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $fractal;
    protected $statusCode = 200;

    public function __construct (Request $request, Manager $manager)
    {
        $this->fractal = $manager;

        if ($request->get('include')) {
            $this->fractal->parseIncludes($request->get('include'));
        }
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    public function paginatedCollection(LengthAwarePaginator $paginator, TransformerAbstract $transformer)
    {
        $collection = $paginator->getCollection();
        $resource = new Collection($collection, $transformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return response($this->fractal->createData($resource)->toArray(), $this->statusCode);
    }
    
    public function item($item, TransformerAbstract $transformer)
    {
        $resource = new Item($item, $transformer);

        return response($this->fractal->createData($resource)->toArray(), $this->statusCode);
    }

    public function badRequest($message = 'Bad request')
    {
        return response(['error' => $message], 400);
    }
}