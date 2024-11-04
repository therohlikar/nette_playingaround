<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\Product;

/**
 * @Path("/products")
 */
class ProductsController extends BaseController
{
    public function __construct(private EntityManagerInterface $em){

    }

    /**
     * @Path("/")
     * @Method("GET")
     */
    public function index(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $products = $this->em->getRepository(Product::class)->findAll();

        $jsonData = Json::encode($products);

        $response->getBody()->write($jsonData);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @Path("/")
     * @Method("POST")
     */
    public function create(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);
        
        $product = new Product(
            $requestBody['name'],
            $requestBody['stock_count'],
            $requestBody['price']
        );

        $this->em->persist($product);
        $this->em->flush();

        $jsonData = Json::encode($product);

        $response->getBody()->write($jsonData);
        return $response->withHeader('Content-Type', 'application/json');
    }

}