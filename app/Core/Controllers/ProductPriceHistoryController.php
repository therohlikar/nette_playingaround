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
     * @RequestParameters({
     *      @RequestParameter(name="id", type="int", description="History of Product's Prices")
     * })
     */
    public function getProductHistory(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $requestBody = Json::decode($request->getBody()->getContents(), Json::FORCE_ARRAY);

        $product = $this->em->getRepository(Product::class)->find($requestBody['id']);
        if (!$product) {
            $response->getBody()->write("Product not found");
            return $response->withHeader('Content-Type', 'application/json');
        }

        $history = $this->em->getRepository(ProductPriceHistory::class)->findBy(['product' => $requestBody['id']]);
        
        $historyData = array_map(function ($entry) {
            return [
                'id' => $entry->getId(),
                'newPrice' => $entry->getNewPrice(),
                'oldPrice' => $entry->getOldPrice(),
                'dateOfChange' => $entry->getDateOfChange()->format('Y-m-d H:i:s'),
            ];
        }, $history);

        $jsonData = Json::encode($historyData);

        $response->getBody()->write($jsonData);
        return $response->withHeader('Content-Type', 'application/json');
    }
}