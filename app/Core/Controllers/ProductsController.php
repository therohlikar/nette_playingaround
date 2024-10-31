<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Nette\Utils\Json;
use Nette\Database\Explorer;

/**
 * @Path("/products")
 */
class ProductsController extends BaseController
{
    public function __construct(
		private Explorer $database,
	) {

	}
    /**
     * @Path("/")
     * @Method("GET")
     */
    public function index(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        // This is an endpoint
        //  - its path is /products
        //  - it should be available on address example.com/products
        $response = $database->fetchAll('SELECT * FROM products');
        return $response;
    }

}