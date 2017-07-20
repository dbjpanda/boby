<?php

namespace Drupal\ml_engine;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of MLEngine Project entities.
 *
 * @see \Drupal\ml_engine\Entity\Project
 */
class ProjectListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['project_name'] = t('Project Name');
    $header['input'] = t('Input');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // Special case the personal form.
    if ($entity->id() == 'personal') {
      $row['form'] = $entity->label();
      $row['recipients'] = t('Selected user');
      $row['selected'] = t('No');
    }
    else {
      $row['project_name'] = $entity->link(NULL, 'canonical');
      $row['input'] = $entity->link(NULL, 'canonical');
    }
    return $row + parent::buildRow($entity);
  }

}
