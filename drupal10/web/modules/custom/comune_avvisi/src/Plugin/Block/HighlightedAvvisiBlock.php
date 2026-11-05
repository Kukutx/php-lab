<?php

declare(strict_types=1);

namespace Drupal\comune_avvisi\Plugin\Block;

use Drupal\comune_avvisi\AvvisiRepository;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block listing the highlighted notices.
 *
 * Plugins are discovered through the PHP attribute below (Drupal 10.2+;
 * older code uses a Block annotation in the docblock instead).
 */
#[Block(
  id: 'comune_avvisi_highlighted',
  admin_label: new TranslatableMarkup('Avvisi in evidenza'),
  category: new TranslatableMarkup('php-lab'),
)]
final class HighlightedAvvisiBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the block with its injected repository.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly AvvisiRepository $repository,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('comune_avvisi.repository'));
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $items = [];
    foreach ($this->repository->highlighted() as $node) {
      $items[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'category' => $node->get('field_categoria')->entity?->label(),
        'date' => date('d/m/Y', (int) $node->getCreatedTime()),
      ];
    }

    return [
      '#theme' => 'comune_avvisi_highlighted',
      '#items' => $items,
      '#cache' => ['tags' => ['node_list', 'config:comune_avvisi.settings']],
    ];
  }

}
