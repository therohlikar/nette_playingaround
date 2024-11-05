<?php

namespace App\Core\Controllers;

use Apitte\Core\Annotation\Controller\Controller;
use Apitte\Core\Annotation\Controller\ControllerPath;
use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Core\UI\Controller\IController;
use Apitte\OpenApi\ISchemaBuilder;

/**
 * @Path("/openapi")
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
			->withAddedHeader('Access-Control-Allow-Origin', '*')
			->writeJsonBody(
				$this->schemaBuilder->build()->toArray()
			);
    }

}