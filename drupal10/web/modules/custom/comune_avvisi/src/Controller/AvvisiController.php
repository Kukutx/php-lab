<?php

declare(strict_types=1);

namespace Drupal\comune_avvisi\Controller;

use Drupal\comune_avvisi\AvvisiRepository;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Summary page: counts per category and notices that expire soon.
 */
final class AvvisiController extends ControllerBase {

  public function __construct(private readonly AvvisiRepository $repository) {}

  /**
   * Creates the controller from the service container.
   *
   * We pull out only what we need and pass it to the constructor
   * (dependency injection).
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('comune_avvisi.repository'));
  }

  /**
   * Returns a render array, not HTML: the theme layer decides the markup.
   */
  public function summary(): array {
    $days = (int) $this->config('comune_avvisi.settings')->get('expiring_days');

    $expiring = [];
    foreach ($this->repository->expiringWithin($days) as $node) {
      $expiring[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'date' => $node->get('field_scadenza')->date?->format('d/m/Y'),
      ];
    }

    return [
      '#theme' => 'comune_avvisi_summary',
      '#intro' => $this->t('Notices expiring in the next @days days.', ['@days' => $days]),
      '#counts' => $this->repository->countByCategory(),
      '#expiring' => $expiring,
      // Cache until any node changes or the settings are saved.
      '#cache' => [
        'tags' => ['node_list', 'config:comune_avvisi.settings'],
        'max-age' => 3600,
      ],
    ];
  }

}
