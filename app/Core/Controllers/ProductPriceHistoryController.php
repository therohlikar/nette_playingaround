<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\Product;
use App\Model\ProductPriceHistory;
use OpenApi\Annotations as OA;

/**
 * @Path("/history")
 */
class ProductPriceHistoryController extends BaseController
{
    public function __construct(private EntityManagerInterface $em){

    }

    /**
     * @Path("/{id}")
     * @Method("GET")
     * @OA\Get(
    *     path="/api/base/history/{id}",
    *     summary="Product's price history",
    *     description="Lists history of change in the product's price",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(type="integer"),
    *         description="Product's ID"
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Product's price history",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="product_id", type="integer", example="2"),
    *                 @OA\Property(property="new_price", type="number", format="float", example=499.99),
    *                 @OA\Property(property="old_price", type="number", format="float", example=10.99),
    *                 @OA\Property(property="date_of_change", type="timestamp", format="Y-m-d H:i:s", example="2024-11-04 15:14:11") 
    *             )
    *         )
    *     )
    * )
     */
    public function getProductHistory(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $id = $request->getParameter('id');

        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product) {
            $response->getBody()->write("Product not found");
            return $response->withHeader('Content-Type', 'application/json');
        }

        $history = $this->em->getRepository(ProductPriceHistory::class)->findBy(['product' => $id]);
        
        $historyData = array_map(function ($entry) {
            return [
                'id' => $entry->getId(),
                'newPrice' => $entry->getNewPrice(),
                'oldPrice' => $entry->getOldPrice(),
                'dateOfChange' => $entry->getDateOfChange()->format('Y-m-d H:i:s'),
            ];
        }, $history);

        $response->getBody()->write(Json::encode($historyData));
        return $response->withHeader('Content-Type', 'application/json');
    }
}