<?php

namespace Drupal\sandstone\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SandstoneController 
{
  public function admin() {
    return array(
      '#title' => 'Sandstone',
      '#markup' => 'Sandstone Controller',
    );
  }
  
  /**
   * Gets all of the controllers available
   * @return ApiControllerBase[]
   */
  public static function getControllers(): array {
    $controllers = [];
    \Drupal::moduleHandler()->invokeAll('add_controller', [&$controllers]);
    return $controllers;
  }

  public function invoke(Request $request) {
    $controller = $request->attributes->get('class');
    $controllerInstance = new $controller();

    // TODO: Access controls will be handled by config

    if ($request->isMethod('GET') && $controllerInstance->can('GET')) {
      return $controllerInstance->onRead($request);
    } else if ($request->isMethod('POST') && $controllerInstance->can('POST')) {
      return $controllerInstance->onUpdate($request);
    } else if ($request->isMethod('PUT') && $controllerInstance->can('PUT')) {
      return $controllerInstance->onCreate($request);
    } else if ($request->isMethod('DELETE') && $controllerInstance->can('DELETE')) {
      return $controllerInstance->onDelete($request);
    } else {
      return new JsonResponse([
        'error' => 'No controller exists to handle this endpoint.',
        'details' => [
        ]
      ]);  
    }
  }
}