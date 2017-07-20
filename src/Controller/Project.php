<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ml_engine\ProjectInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for contact routes.
 */
class Project extends ControllerBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a ContactController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Presents the site-wide contact form.
   *
   * @param \Drupal\contact\ProjectInterface $contact_form
   *   The contact form to use.
   *
   * @return array
   *   The form as render array as expected by drupal_render().
   */
  public function contactSitePage(ProjectInterface $ml_engine_project = NULL) {
    $config = [];
    $input = $this->entityManager()
      ->getStorage('ml_engine_project_input')
      ->create([
        'ml_engine_project' => $ml_engine_project->id(),
      ]);

    $form = $this->entityFormBuilder()->getForm($input);
    $form['#title'] = "Set Tensorflow Input for ".$ml_engine_project->label();
    $this->renderer->addCacheableDependency($form, $config);
    return $form;
  }


}
