<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Models\Db;
use App\Config\AppSettings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->setBasePath('/api');

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withStatus(401);
});

$app->get("/flights/{dof}/{callsign}", function (Request $request, Response $response, array $args) {
    if ($request->getHeaderLine('X-Api-Key') != AppSettings::getInstance()->GetApiKey()) {
        return $response->withStatus(401);
    }

    try {
        $db = new Db();
        $db->connect();

        if(!$db->IsSystemOpen()) {
            return $response->withStatus(403, "Booking System in maintenance mode.");
        }

        $flights = $db->GetFlight($args['callsign'], $args['dof']);
        $db = null;

        $response->getBody()->write(json_encode($flights));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


$app->get("/flights", function (Request $request, Response $response) {
    if ($request->getHeaderLine('X-Api-Key') != AppSettings::getInstance()->GetApiKey()) {
        return $response->withStatus(401);
    }

    try {
        $db = new Db();
        $db->connect();

        if(!$db->IsSystemOpen()) {
            return $response->withStatus(403, "Booking System in maintenance mode.");
        }

        $flights = $db->GetAllFlights();
        $db = null;

        $response->getBody()->write(json_encode($flights));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->run();