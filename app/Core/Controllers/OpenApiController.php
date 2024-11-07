<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Controller;
use Apitte\Core\Annotation\Controller\ControllerPath;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Core\UI\Controller\IController;
use Apitte\OpenApi\ISchemaBuilder;

use OpenApi\Annotations as OA;

/**
 * @Path("/api/openapi")
 * @OA\Info(title="Swagger UI OpenAPI for nette_playingaround", version="BETA")
 */
final class OpenApiController implements IController
{
    /** @var ISchemaBuilder */
    private $schemaBuilder;

    public function __construct(ISchemaBuilder $schemaBuilder)
    {
        $this->schemaBuilder = $schemaBuilder;
    }

    /**
     * @Path("/")
     * @Method("GET")
     */
    public function openApiIndex(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
			->writeJsonBody(
				$this->schemaBuilder->build()->toArray()
			);
    }

}