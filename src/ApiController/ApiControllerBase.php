<?php

namespace Drupal\sandstone\ApiController;

use Drupal\Core\Render\Element\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiControllerBase
{
  /**
   * Returns the display name of this API Controller.
   * @return string
   */
  public abstract function getName(): string;

  /**
   * Summary of what this controller is intended for.
   * @return string
   */
  public function getDescription(): string { return ''; }

  /**
   * The unique identifier for this API controller.
   * @return string
   */
  public abstract function getIdentifier(): string;

  /**
   * The default route to provide for this controller.
   * @return string
   */
  public abstract function getDefaultRoute(): string;

  /**
   * The supported methods, defaults to [GET]
   * @return string[]
   */
  public function getMethods(): Array { return ['GET']; }

  /**
   * Indicates if the route can respond to the given method.
   * @param string $apiMethod
   * @return bool
   */
  public function can(string $apiMethod) {
    return in_array($apiMethod, $this->getMethods());
  }

  /**
   * The form used to extend properties for this route.
   * @param Form $form
   * @return void
   */
  public function form(array &$form) {}

  /**
   * Handles saving the extended properties for this route.
   * @param Form $form
   * @return void
   */
  public function submitForm(array $form) {}

  /**
   * Handler for PUT requests.
   * @return JsonResponse
   */
  public function onCreate(Request $request): ?JsonResponse
  {
    return null; 
  }

  /**
   * Handler for GET requests.
   * @return JsonResponse|null
   */
  public function onRead(Request $request): ?JsonResponse
  {
    return null; 
  }

  /**
   * Handler for POST requests.
   * @return JsonResponse|null
   */
  public function onUpdate(Request $request): ?JsonResponse
  {
    return null; 
  }

  /**
   * Handler for DELETE requests.
   * @return JsonResponse|null
   */
  public function onDelete(Request $request): ?JsonResponse
  {
    return null; 
  }

  /**
   * Gets the current assigned route for this controller.
   * @return string
   */
  public function getRoute(): string 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    return $config->get("routes.{$this->getIdentifier()}.route") ?: $this->getDefaultRoute();
  }

  /**
   * Sets the new route for this controller.
   * @param string $route The new route for this controller.
   * @return void
   */
  public function setRoute(string $route) 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    $config->set("routes.{$this->getIdentifier()}.route", $route)->save();
    \Drupal::service("router.builder")->rebuild();
  }

  /**
   * Utility function to set a property of the API controller.
   * @param string $key The key to set.
   * @param mixed $value The value to set.
   * @return void
   */
  public function setProperty(string $key, mixed $value) 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    $config->set("routes.{$this->getIdentifier()}.properties.{$key}", $value)->save(); 
  }

  /**
   * Utility function for getting the property of the API controller.
   * @param string $key The key to get.
   * @return mixed The value of the given key, or NULL if non-existent.
   */
  public function getProperty(string $key) 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    return $config->get("routes.{$this->getIdentifier()}.properties.{$key}");
  }

  /**
   * Sets the enabled/disabled state of this API.
   * @param bool $enabled true if disabled, false if enabled.
   * @return void
   */
  public function setDisabled(bool $disabled) 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    $config->set("routes.{$this->getIdentifier()}.disabled", $disabled)->save();
  }

  /**
   * Determines if this route is enabled. If disabled, the routes will not work.
   * @return bool
   */
  public function isDisabled(): bool 
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    return !($config->get("routes.{$this->getIdentifier()}.disabled") ?: false);
  }

  /**
   * Returns a summary of the status of this route. By default it only returns if
   * this route is enabled or disabled but can be extended to include additional
   * details based on the implementation.
   * @return string
   */
  public function getStatus(): string
  {
    $config = \Drupal::service('config.factory')->getEditable("sandstone.settings");
    return $this->isDisabled() ? 'Disabled' : 'Enabled' ;
  }
}
