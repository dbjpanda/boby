<?php

namespace Drupal\ml_engine\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\ml_engine\InputInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the contact message entity.
 *
 * @ContentEntityType(
 *   id = "ml_engine_project_input",
 *   label = @Translation("ML Engine Project Input"),
 *   handlers = {
 *     "access" = "Drupal\ml_engine\InputAccessControlHandler",
 *     "storage" = "Drupal\Core\Entity\ContentEntityNullStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "default" = "Drupal\ml_engine\Form\InputForm"
 *     }
 *   },
 *   admin_permission = "administer ml_engine_project",
 *   entity_keys = {
 *     "bundle" = "ml_engine_project",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode"
 *   },
 *   bundle_entity_type = "ml_engine_project",
 *   field_ui_base_route = "entity.ml_engine_project.edit_form",
 * )
 */
class Input extends ContentEntityBase implements InputInterface {

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
        /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
        $fields = parent::baseFieldDefinitions($entity_type);


        // Standard field, used as unique if primary index.
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the Contact entity.'))
            ->setReadOnly(TRUE);

        // Standard field, unique outside of the scope of the current project.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Contact entity.'))
            ->setReadOnly(TRUE);


        $fields['langcode'] = BaseFieldDefinition::create('language')
            ->setLabel(t('Language code'))
            ->setDescription(t('The language code of ContentEntityExample entity.'));
        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        return $fields;
    }

}
