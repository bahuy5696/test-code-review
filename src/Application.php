<?php

namespace App;

use App\Storage\DataStorage;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dotenv\Dotenv;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;


class Application
{
  private $entityManager;
  private $storage;
  private $request;

  public function run()
  {
    //Load package env
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    // Setup database connection and Init ORM
    $paths = [__DIR__ . "/Model"];
    $dbParams = [
      'driver'   => $_ENV['DB_DRIVER'],
      'host'     => $_ENV['DB_HOST'],
      'port'     => $_ENV['DB_PORT'],
      'user'     => $_ENV['DB_USER'],
      'password' => $_ENV['DB_PASS'],
      'dbname'   => $_ENV['DB_NAME'],
    ];

    $config = Setup::createAnnotationMetadataConfiguration($paths, false, null, null, false);
    $config->setAutoGenerateProxyClasses(true);
    $this->entityManager = EntityManager::create($dbParams, $config);
    $this->storage = new DataStorage($this->entityManager);

    $this->request = Request::createFromGlobals();
    $reader = new AnnotationReader();

    //Load all route classes
    $routeLoader = new class($reader) extends \Symfony\Component\Routing\Loader\AnnotationClassLoader {
      protected function configureRoute(\Symfony\Component\Routing\Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
      {
        $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
      }
    };

    //Load all routes from controller
    $loader = new AnnotationDirectoryLoader(new FileLocator(), $routeLoader);
    $routes = $loader->load(realpath(__DIR__ . '/Controller'));
    $context = new RequestContext();
    $context->fromRequest($this->request);
    $matcher = new UrlMatcher($routes, $context);

    try {
      $parameters = $matcher->match($this->request->getPathInfo());
      $this->request->attributes->add($parameters);
      $controllerFull = $parameters['_controller'];

      if (is_string($controllerFull) && strpos($controllerFull, '::') !== false) {
        [$controllerClass, $method] = explode('::', $controllerFull);
      } else {
        (new Response('error invalid controller', 500))->send();
      }

      $controller = new $controllerClass($this->storage);
      $response = $controller->$method($this->request);
      $response->send();
    } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
      (new Response('404 Not Found', 404))->send();
    } catch (\Exception $e) {
    }
  }
}
