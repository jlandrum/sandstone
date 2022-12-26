<?php

namespace Drupal\sandstone\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\sandstone\Controller\SandstoneController;
use Drupal\user\PermissionHandler;
use Drupal\user\PermissionHandlerInterface;

class EditApiRouteForm extends FormBase {
  public function getFormId() {
    return 'sandstone_api_route_form';  
  }

  public function buildForm(array $form, FormStateInterface $form_state, $controller = NULL) {
    $controller = SandstoneController::getControllers()[$controller];
    $controllerImpl = new $controller();
    $permissionOptions = [
      -1 => 'No Access Control'
    ];

    if ($permissions = \Drupal::service('user.permissions')) {
      foreach ($permissions->getPermissions() as $key => $permission) {
        $permissionOptions[$key] = $permission['title']->render();
      }
    }

    $form['route'] = [
      '#type' => 'textfield',
      '#title' => 'Route',
      '#placeholder' => '/api/v1/',
      '#default_value' => $controllerImpl->getRoute(),
    ];

    $form['disabled'] = [
      '#type' => 'checkbox',
      '#title' => 'Disabled',
      '#default_value' => $controllerImpl->isDisabled(),
    ];

    if ($controllerImpl->can('PUT')) {
      $form['put_permission'] = [
        '#type' => 'select',
        '#title' => 'Create Permission',
        '#options' => $permissionOptions,
        '#default_value' => $controllerImpl->getProperty('permission.put')
      ];  
    }

    if ($controllerImpl->can('GET')) {
      $form['get_permission'] = [
        '#type' => 'select',
        '#title' => 'Read Permission',
        '#options' => $permissionOptions,
        '#default_value' => $controllerImpl->getProperty('permission.get')
      ];  
    }

    if ($controllerImpl->can('POST')) {
      $form['post_permission'] = [
        '#type' => 'select',
        '#title' => 'Update Permission',
        '#options' => $permissionOptions,
        '#default_value' => $controllerImpl->getProperty('permission.post')
      ];  
    }

    if ($controllerImpl->can('DELETE')) {
      $form['delete_permission'] = [
        '#type' => 'select',
        '#title' => 'Delete Permission',
        '#options' => $permissionOptions,
        '#default_value' => $controllerImpl->getProperty('permission.delete')
      ];  
    }
    
    $form['class'] = [
      '#type' => 'hidden',
      '#value' => $controller,
    ];

    $controllerImpl->form($form);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];
  
    return $form;
  }

	public function submitForm(array &$form, FormStateInterface $form_state) {
    $controllerName = $form['class']['#value'];
    $controller = new $controllerName;
    $controllerImpl = new $controller();

    $controllerImpl->setDisabled($form['disabled']['#checked']);
    $controllerImpl->setRoute($form['route']['#value']);
    $controllerImpl->submitForm($form);

    if (array_key_exists('post_permission', $form)) {
      $controllerImpl->setProperty('permission.post', $form['post_permission']['#value']);
    }

    if (array_key_exists('get_permission', $form)) {
      $controllerImpl->setProperty('permission.get', $form['get_permission']['#value']);
    }

    if (array_key_exists('put_permission', $form)) {
      $controllerImpl->setProperty('permission.put', $form['put_permission']['#value']);
    }

    if (array_key_exists('delete_permission', $form)) {
      $controllerImpl->setProperty('permission.delete', $form['delete_permission']['#value']);
    }
  }
}