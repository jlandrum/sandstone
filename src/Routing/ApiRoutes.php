<?php

namespace Drupal\sandstone\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\sandstone\Controller\SandstoneController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ApiRoutes extends RouteSubscriberBase {

  public function routes() {
  }

  /**
   */
  public function __construct() {
  }

    /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $config = \Drupal::service('config.factory')->getEditable('sandstone.settings');
    $baseRoute = $config->get('base_route') ?: '/api/v1';
   
    $controllers = SandstoneController::getControllers();

    foreach ($controllers as $key => $controller) {
      $reflection = new $controller();

      if (!$reflection->isDisabled()) {
        $collection->add(
          "sandstone.route.{$reflection->getIdentifier()}",
          new Route(
            "{$baseRoute}{$reflection->getRoute()}",
            [
              '_controller' => SandstoneController::class . "::invoke",
              'class' => $controller
            ],
            [
              '_permission' => 'access sandstone apis',
            ]
          )
        );
      }
    }

    if ($route = $collection->get('sandstone.routes')) {
      $route->setPath($baseRoute . '/{controller}/{resource}');
      $collection->add('sandstone.routes', $route);
    }
  }
}