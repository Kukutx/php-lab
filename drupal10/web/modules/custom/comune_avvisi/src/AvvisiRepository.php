<?php

declare(strict_types=1);

namespace Drupal\comune_avvisi;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;

/**
 * Read-only queries about notices, shared by the controller and the block.
 */
final class AvvisiRepository {

  use StringTranslationTrait;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly TimeInterface $time,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Published "avviso" nodes flagged as highlighted, newest first.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The highlighted notices.
   */
  public function highlighted(): array {
    $limit = (int) $this->configFactory->get('comune_avvisi.settings')->get('highlighted_limit');

    $ids = $this->nodeQuery()
      ->condition('field_in_evidenza', 1)
      ->sort('created', 'DESC')
      ->range(0, max(1, $limit))
      ->execute();

    return $this->load($ids);
  }

  /**
   * Notices whose expiry date falls within the next $days days.
   *
   * @param int $days
   *   Size of the window in days.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The notices expiring soon, earliest first.
   */
  public function expiringWithin(int $days): array {
    $now = $this->time->getRequestTime();
    $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;

    $ids = $this->nodeQuery()
      ->condition('field_scadenza', gmdate($format, $now), '>=')
      ->condition('field_scadenza', gmdate($format, $now + $days * 86400), '<=')
      ->sort('field_scadenza', 'ASC')
      ->execute();

    return $this->load($ids);
  }

  /**
   * Number of published notices per category term name.
   *
   * @return array<string, int>
   *   Counts keyed by category name.
   */
  public function countByCategory(): array {
    $counts = [];
    foreach ($this->load($this->nodeQuery()->execute()) as $node) {
      $term = $node->get('field_categoria')->entity;
      $name = $term ? $term->label() : (string) $this->t('Uncategorised');
      $counts[$name] = ($counts[$name] ?? 0) + 1;
    }
    ksort($counts);
    return $counts;
  }

  /**
   * Entity query for published notices.
   *
   * The accessCheck(TRUE) call makes the query respect node permissions.
   */
  private function nodeQuery(): QueryInterface {
    return $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'avviso')
      ->condition('status', NodeInterface::PUBLISHED);
  }

  /**
   * Loads nodes by ID.
   *
   * @param array<int|string, int|string> $ids
   *   Node IDs.
   *
   * @return \Drupal\node\NodeInterface[]
   *   The loaded nodes, re-indexed.
   */
  private function load(array $ids): array {
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($ids);
    return array_values($nodes);
  }

}
