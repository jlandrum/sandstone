<?php

namespace Drupal\sandstone\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Url;
use Drupal\sandstone\ApiController\ApiControllerBase;
use Drupal\sandstone\Controller\SandstoneController;

class AdminForm extends FormBase {
  public function getFormId() {
    return 'sandstone_configuration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('sandstone.settings');

    $baseRoute = $config->get('base_route') ?: '/api/v1';

    $entityTypes = \Drupal::entityTypeManager()
      ->getDefinitions();

    $form['base_route'] = [
      '#type' => 'textfield',
      '#title' => 'Base Route',
      '#placeholder' => '/api/v1',
      '#default_value' => $baseRoute,
    ];
  
    $form['routes'] = [
      '#type' => 'table',
      '#caption' => $this->t('Routes'),
      '#header' => array(
        $this->t('Controller'),
        $this->t('Description'),
        $this->t('Route'),
        $this->t('Status'),
        $this->t('Operations'),
      )
    ];

    $controllers = SandstoneController::getControllers();

    foreach ($controllers as $key => $controller) {
      $instance = new $controller();
      $id = $instance->getIdentifier();

      $form['routes'][$id]['controller'] = [
        '#markup' => $this->t($instance->getName()),
      ];

      $form['routes'][$id]['description'] = [
        '#markup' => $this->t($instance->getDescription()),
      ];

      $form['routes'][$id]['route'] = [
        '#markup' => $this->t($instance->getRoute()),
      ];

      $form['routes'][$id]['status'] = [
        '#markup' => $this->t($instance->getStatus()),
      ];

      $form['routes'][$id]['operations'] = array(
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute('sandstone.edit_route', [
              'controller' => $key,
              'resource' => $instance->getIdentifier(),
            ])
          ],
          'get' => [
            'title' => $this->t('Open Get'),
            'url' => Url::fromUri("internal:{$baseRoute}/{$instance->getRoute()}"),
          ]
        ],
      );
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

	public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('sandstone.settings');
    $config->set('base_route', $form['base_route']['#value'])->save();
    \Drupal::service("router.builder")->rebuild();
  }
}