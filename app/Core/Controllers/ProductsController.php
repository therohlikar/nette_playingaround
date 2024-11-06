<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestBody;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\Product;
use App\Model\ProductPriceHistory;

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
    public function getProducts(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);

        $products = $this->em->getRepository(Product::class)->findBy(
            ['available' => true], 
            (isset($requestBody['sort']) ? ['stockCount' => 'DESC'] : [])
        );

        $jsonData = Json::encode($products);

        $response->getBody()->write($jsonData);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @Path("/find")
     * @Method("GET")
     */
    public function findProductByName(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        // {
        //     "keyword":"lux"
        // }

        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);

        $products = $this->em->getRepository(Product::class)
            ->createQueryBuilder('products')
            ->where('products.name LIKE :name')
            ->setParameter('name', '%' . (isset($requestBody['keyword']) ? $requestBody['keyword'] : "") . '%')
            ->getQuery()
            ->getResult();

        $jsonData = Json::encode($products);

        $response->getBody()->write($jsonData);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @Path("/")
     * @Method("POST")
     * @RequestBody(
     *     description="Product - json",
     *     entity="App\Model\Product",
     *     required=true,
     *     validation=true
     * )
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
    /**
     * @Path("/{id}")
     * @Method("PUT")
     * @RequestParameters({
     *      @RequestParameter(name="id", type="int", description="Product ID")
     * })
     */
    public function update(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        // localhost:8080/api/base/products/1
        // {
        //     "price":6.99
        // }
        $id = $request->getParameter('id');
        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);

        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product) {
            return $response->withStatus(404, 'Product not found');
        }

        $oldPrice = $product->getPrice();

        if (isset($requestBody['price'])) {
            $product->changePrice($requestBody['price']);

            $historyInput = new ProductPriceHistory(
                $product,
                $requestBody['price'],
                $oldPrice
            );
    
            $this->em->persist($historyInput);
            $this->em->flush();
            $jsonData = Json::encode($product);
    
            $response->getBody()->write($jsonData);
        }else{
            $response->getBody()->write("{ 'message':'Missing value in :price: variable', 'status':'400' }");
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * @Path("/delete/{id}")
     * @Method("DELETE")
     * @RequestParameters({
     *      @RequestParameter(name="id", type="int", description="Product ID to delete")
     * })
     */
    public function delete(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        // localhost:8080/api/base/products/delete/1
        $id = $request->getParameter('id');

        $product = $this->em->getRepository(Product::class)->find($id );
        if (!$product) {
            $response->getBody()->write("Product not found");
            return $response->withHeader('Content-Type', 'application/json');
        }

        $history = $this->em->getRepository(ProductPriceHistory::class)->findBy(['product' => $id]);

        foreach($history as $row){
            $this->em->remove($row);
        }

        $this->em->remove($product);
        $this->em->flush();

        $response->getBody()->write("OK");
        return $response->withHeader('Content-Type', 'application/json');
    }
}