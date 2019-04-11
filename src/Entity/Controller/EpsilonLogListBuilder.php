<?php

namespace Drupal\epsilon_harmony\Entity\Controller;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for epsilon_harmony entity.
 *
 * @ingroup epsilon_harmony
 */
class EpsilonLogListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new EpsilonLogListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatter $date_formatter, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);

    $this->dateFormatter = $date_formatter;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('All the Epsilon harmony API callbacks are logged here. To clear previous logs: <a href="@adminlink">Clear logs</a>', [
        '@adminlink' => Url::fromRoute('epsilon_harmony.clear_logs'),
      ]),
    ];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Sort results descending based on id & limit results to 20 per page.
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort($this->entityType->getKey('id'), 'DESC');
    $query->pager(20);
    return $query->execute();
  }


  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the epsilon log list
   */
  public function buildHeader() {
    $header['id'] = $this->t('Log ID');
    $header['method'] = $this->t('Method');
    $header['endpoint'] = $this->t('Endpoint');
    $header['status_code'] = $this->t('Status Code');
    $header['status_message'] = $this->t('Status Message');
    $header['created'] = $this->t('Created on');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * Building the rows for the epsilon log list.
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['method'] = $entity->method->value;
    $row['endpoint'] = $entity->endpoint->value;
    $row['status_code'] = $entity->status_code->value;
    $row['status_message'] = $entity->status_message->value;
    $row['created'] = $this->dateFormatter->format($entity->getCreatedTime(), 'short');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   *
   * Adding custom operation on the list.
   */
  public function getOperations(EntityInterface $entity) {
    $operations['view'] = array(
      'title' => t('View'),
      'url' => $this->ensureDestination($entity->toUrl('canonical')),
      'weight' => -10,
    );
    return $operations;
  }

}
