<?php

namespace Drupal\sandstone\ApiController;

use Drupal\Core\Render\Element\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthProfileController extends ApiControllerBase
{
  protected function resolveChild($child) {
    $eType = $child->getEntityTypeId();
    $type = $child->getType();
    $uuid = $child->uuid->value;
    $out = [];

    $userFieldDefinitions = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions($eType, $type);

    $entity = \Drupal::entityTypeManager()->getStorage($eType)->loadByProperties(['uuid' => $uuid]);
    $entity = reset($entity);

    foreach ($userFieldDefinitions as $field) {
      $out[$field->getName()] = $entity->get($field->getName())->value;
    }

    return $out;
  }

  public function onRead(Request $request): ?JsonResponse
  {
    $user = \Drupal::currentUser();

    if ($user->isAnonymous()) {
      return new JsonResponse([
        'user' => 'anonymous',
      ]);
    }

    $userEntity = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $fieldValues = [];
    
    $fields = array_filter($this->getProperty('fields'), fn($field) => !str_contains($field,'.') ) ?: [];
    $expanded = $this->getProperty('expanded') ?: [];

    foreach ($fields as $field) {     
      $userEntity->get($field)->value;
      if (in_array($field, $expanded)) {
        $subfield = array_map(
          fn($k) => $this->resolveChild($k), 
          $userEntity->get($field)->referencedEntities());

        $fieldValues[$field] = $subfield;
      } else {
        $fieldValues[$field] = $userEntity->get($field)->value;
      }
    }

    return new JsonResponse($fieldValues);
  }

  public function getMethods(): array
  {
    return ['GET', 'POST'];  
  }

	/**
	 * @return string
	 */
	public function getName(): string {
    return 'Profile';
	}

  /**
	 * @return string
	 */
	public function getDescription(): string {
    return 'Allows fetching and updating the current user\'s profile';
	}

	/**
	 * @return string
	 */
	public function getIdentifier(): string {
    return 'profile';
	}
	
	/**
	 * @return string
	 */
	public function getDefaultRoute(): string {
    return '/user/profile';
	}
	
  private function formDig(&$form, $field, $iterations = 0, $parent = '') {
    $fields = $this->getProperty('fields') ?: [];
    $expanded = $this->getProperty('expanded') ?: [];
    $identifier = "{$parent}{$field->getName()}";

    if ($iterations > 2)
      return;

    $form['profile_fields'][$identifier]['field'] = [
      '#markup' => str_repeat("&mdash; ", $iterations) . $field->getName()
    ];  

    $form['profile_fields'][$identifier]['enabled'] = [
      '#type' => 'checkbox',
      '#default_value' => in_array($identifier, $fields),
    ];  
    
    $form['profile_fields'][$identifier]['expand'] = [
      '#type' => 'checkbox',
      '#disabled' => $field->getType() != 'entity_reference',
      '#default_value' => in_array($identifier, $expanded),
    ];  
  }

	public function form(&$form) {
    $form['profile_fields'] = [
      '#type' => 'table',
      '#caption' => 'Fields',
      '#header' => [
        t('Field'),
        t('Enabled'),
        t('Expand'),
      ]
    ];

    $userFieldDefinitions = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions('user', 'user');

    foreach ($userFieldDefinitions as $field) {
      $this->formDig($form, $field, 0);
    }
	}

  public function submitForm(array $form)
  {
    $fields = [];
    $expandedFields = [];

    foreach (array_keys($form['profile_fields']) as $key) {
      if (str_starts_with($key, '#')) {
        continue;
      }
      $enabled = $form['profile_fields'][$key]['enabled']['#checked'];
      $expanded = $form['profile_fields'][$key]['expand']['#checked'];

      if ($enabled) {
        array_push($fields, $key);
      }

      if ($expanded) {
        array_push($expandedFields, $key);
      }
    }

    $this->setProperty('fields', $fields);
    $this->setProperty('expanded', $expandedFields);
  }
}
