<?php

namespace Drupal\ml_engine;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for input form.
 *
 * @see \Drupal\ml_engine\Entity\Input.
 */
class InputAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIf($account->hasPermission('administer ml_engine_project'))->cachePerPermissions();
  }

}
