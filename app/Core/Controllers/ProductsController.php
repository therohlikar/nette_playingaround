<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestBody;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\Product;
use App\Model\ProductPriceHistory;
use OpenApi\Annotations as OA;

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
     * @OA\Get(
    *     path="/api/base/products",
    *     summary="Products list",
    *     description="Lists all products, which are available",
    *     @OA\Parameter(
    *         name="sort",
    *         in="query",
    *         required=false,
    *         @OA\Schema(type="boolean"),
    *         description="Descending sort"
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Products list",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="name", type="string", example="Luxury Sofa"),
    *                 @OA\Property(property="price", type="number", format="float", example=499.99),
    *                 @OA\Property(property="stock_count", type="number", format="integer", example=10),
    *                 @OA\Property(property="available", type="boolean", format="bool", example=false) 
    *             )
    *         )
    *     )
    * )
     */
    public function getProducts(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $sort = $request->getQueryParam('sort', false);
        $sort = filter_var($sort, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $products = $this->em->getRepository(Product::class)->findBy(
            ['available' => true], 
            (isset($sort) && $sort == true ? ['stockCount' => 'DESC'] : [])
        );

        $response->getBody()->write(Json::encode($products));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @Path("/find")
     * @Method("GET")
     * @OA\Get(
    *     path="/api/base/products/find",
    *     summary="Find products by name keyword",
    *     description="Searches for products with names that contain the provided keyword.",
    *     @OA\Parameter(
    *         name="keyword",
    *         in="query",
    *         required=true,
    *         @OA\Schema(type="string"),
    *         description="Keyword to search for in product names"
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Search results matching keyword",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="name", type="string", example="Luxury Sofa"),
    *                 @OA\Property(property="price", type="number", format="float", example=499.99),
    *                 @OA\Property(property="stock_count", type="number", format="integer", example=10),
    *                 @OA\Property(property="available", type="boolean", format="bool", example=false) 
    *             )
    *         )
    *     )
    * )
     */
    public function findProductByName(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $keyword = $request->getQueryParam('keyword', '');

        $products = $this->em->getRepository(Product::class)
            ->createQueryBuilder('products')
            ->where('products.name LIKE :name')
            ->setParameter('name', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();

        $response->getBody()->write(Json::encode($products));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
    }

    /**
     * @Path("/")
     * @Method("POST")
     * 
     * @OA\Post(
     *     path="/api/base/products",
     *     summary="Create new product",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product - create new",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "price", "stock_count"},
     *             @OA\Property(property="name", type="string", example="Example Product"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99),
     *             @OA\Property(property="stock_count", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing variables"
     *     )
     * )
     */
    public function create(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);

        if(!isset($requestBody['name']) || 
            !isset($requestBody['stock_count']) || 
            !isset($requestBody['price'])
        ){
            $response->getBody()->write("{ 'message':'Missing value in :price: variable', 'status':'400' }");
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $product = new Product(
            $requestBody['name'],
            $requestBody['stock_count'],
            $requestBody['price']
        );

        $this->em->persist($product);
        $this->em->flush();

        $response->getBody()->write(Json::encode($product));
        return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
    }
    /**
     * @Path("/{id}")
     * @Method("PUT")
     * @OA\Put(
     *     path="/api/base/products/{id}",
     *     summary="Update price of a product",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         description="Product ID to be updated",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New price of a product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Missing variables"
     *     )
     * )
     */
    public function update(ApiRequest $request, ApiResponse $response): ApiResponse
    {
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
    * @OA\Delete(
    *     path="/api/base/products/delete/{id}",
    *     summary="Delete a product and its price history",
    *     description="Deletes a product by its ID, including all related entries in the price history table.",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID of the product to delete",
    *         @OA\Schema(type="integer")
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Product and associated history deleted successfully",
    *         @OA\JsonContent(
    *             type="string",
    *             example="OK"
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Product not found",
    *         @OA\JsonContent(
    *             type="string",
    *             example="Product not found"
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     )
    * )
     */
    public function delete(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $id = $request->getParameter('id');

        $product = $this->em->getRepository(Product::class)->find($id );
        if (!$product) {
            $response->getBody()->write("Product not found");
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $history = $this->em->getRepository(ProductPriceHistory::class)->findBy(['product' => $id]);

        foreach($history as $row){
            $this->em->remove($row);
        }

        $this->em->remove($product);
        $this->em->flush();

        $response->getBody()->write("OK");
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

    }
}