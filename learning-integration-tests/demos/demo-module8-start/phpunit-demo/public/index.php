<?php
declare(strict_types=1);

use App\Catalog\Handler\ProductListHandler;
use App\DependencyInjection;
use Laminas\Diactoros\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

require '../vendor/autoload.php';

final class Application
{
    private ContainerInterface $diContainer;

    public function __construct()
    {
        $di = new DependencyInjection();
        $this->diContainer = $di->createContainer();
    }

    public function start(): void
    {
        // We're not using any routing here to keep the example simple.
        $handler = $this->diContainer->get(ProductListHandler::class);
        $response = $handler->handle($this->createServerRequest());
        $this->emitResponse($response);
    }

    private function createServerRequest(): ServerRequest
    {
        return Laminas\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
    }

    private function emitResponse(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        http_response_code($response->getStatusCode());

        echo $response->getBody();
    }
}

(new Application())->start();
