<?php

namespace Drupal\ml_engine;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the MLEngine Project entity type.
 *
 * @see \Drupal\ml_engine\Entity\Project.
 */
class ProjectAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
      return AccessResult::allowedIf($account->hasPermission('administer ml_engine_project'))->cachePerPermissions();
  }

}
