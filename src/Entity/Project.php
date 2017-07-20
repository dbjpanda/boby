<?php

namespace Drupal\ml_engine\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ml_engine\ProjectInterface;
use Drupal\Core\Url;

/**
 * Defines the contact form entity.
 *
 * @ConfigEntityType(
 *   id = "ml_engine_project",
 *   label = @Translation("ML Engine Project"),
 *   handlers = {
 *     "access" = "Drupal\ml_engine\ProjectAccessControlHandler",
 *     "list_builder" = "Drupal\ml_engine\ProjectListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ml_engine\Form\ProjectEdit",
 *       "edit" = "Drupal\ml_engine\Form\ProjectEdit",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "form",
 *   admin_permission = "administer ml_engine_project",
 *   bundle_of = "ml_engine_project_input",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/ml_engine/manage/{ml_engine_project}/delete",
 *     "edit-form" = "/admin/structure/ml_engine/manage/{ml_engine_project}",
 *     "collection" = "/admin/structure/ml_engine",
 *     "canonical" = "/ml_engine/{ml_engine_project}",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   }
 * )
 */
class Project extends ConfigEntityBundleBase implements ProjectInterface {

    /**
     * The form ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The human-readable label of the category.
     *
     * @var string
     */
    protected $label;


}
