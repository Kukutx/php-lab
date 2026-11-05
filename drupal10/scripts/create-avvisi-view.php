<?php

/**
 * @file
 * Creates the "avvisi" view (normally you build it in the Views UI).
 *
 * Run once with: drush php:script scripts/create-avvisi-view.php
 * then `drush cex` writes it to config/sync/views.view.avvisi.yml.
 */

use Drupal\views\Entity\View;

if (View::load('avvisi')) {
  echo "View already exists.\n";
  return;
}

$filters = [
  'status' => [
    'id' => 'status', 'table' => 'node_field_data', 'field' => 'status',
    'plugin_id' => 'boolean', 'value' => '1', 'group' => 1,
    'expose' => ['operator' => FALSE], 'entity_type' => 'node', 'entity_field' => 'status',
  ],
  'type' => [
    'id' => 'type', 'table' => 'node_field_data', 'field' => 'type',
    'plugin_id' => 'bundle', 'value' => ['avviso' => 'avviso'],
    'entity_type' => 'node', 'entity_field' => 'type',
  ],
];

$category_filter = [
  'id' => 'field_categoria_target_id', 'table' => 'node__field_categoria',
  'field' => 'field_categoria_target_id', 'plugin_id' => 'taxonomy_index_tid',
  'operator' => 'or', 'value' => [], 'exposed' => TRUE,
  'expose' => [
    'operator_id' => 'field_categoria_target_id_op', 'label' => 'Categoria',
    'identifier' => 'categoria', 'multiple' => FALSE, 'remember' => FALSE,
    'reduce' => FALSE,
  ],
  'type' => 'select', 'vid' => 'categorie_avvisi', 'hierarchy' => FALSE,
  'limit' => TRUE, 'error_message' => TRUE,
];

$view = View::create([
  'id' => 'avvisi',
  'label' => 'Avvisi',
  'description' => 'List of municipal notices.',
  'base_table' => 'node_field_data',
  'base_field' => 'nid',
  'display' => [
    'default' => [
      'id' => 'default',
      'display_plugin' => 'default',
      'display_title' => 'Default',
      'position' => 0,
      'display_options' => [
        'title' => 'Avvisi',
        'access' => ['type' => 'perm', 'options' => ['perm' => 'access content']],
        'cache' => ['type' => 'tag', 'options' => []],
        'query' => ['type' => 'views_query', 'options' => []],
        'exposed_form' => ['type' => 'basic', 'options' => ['submit_button' => 'Filtra']],
        'pager' => ['type' => 'mini', 'options' => ['items_per_page' => 6]],
        'style' => ['type' => 'default', 'options' => ['row_class' => 'col-12 col-md-6 col-lg-4']],
        'row' => ['type' => 'entity:node', 'options' => ['view_mode' => 'teaser']],
        'filters' => $filters + ['field_categoria_target_id' => $category_filter],
        'sorts' => [
          'created' => [
            'id' => 'created', 'table' => 'node_field_data', 'field' => 'created',
            'plugin_id' => 'date', 'order' => 'DESC', 'granularity' => 'second',
            'entity_type' => 'node', 'entity_field' => 'created',
          ],
        ],
        'empty' => [
          'area_text_custom' => [
            'id' => 'area_text_custom', 'table' => 'views', 'field' => 'area_text_custom',
            'plugin_id' => 'text_custom', 'empty' => TRUE, 'content' => 'Nessun avviso trovato.',
          ],
        ],
      ],
    ],
    'page_1' => [
      'id' => 'page_1',
      'display_plugin' => 'page',
      'display_title' => 'Page',
      'position' => 1,
      'display_options' => [
        'path' => 'avvisi',
        'menu' => ['type' => 'normal', 'title' => 'Avvisi', 'menu_name' => 'main', 'weight' => 0],
      ],
    ],
    'block_1' => [
      'id' => 'block_1',
      'display_plugin' => 'block',
      'display_title' => 'Ultimi avvisi',
      'position' => 2,
      'display_options' => [
        'title' => 'Ultimi avvisi',
        'pager' => ['type' => 'some', 'options' => ['items_per_page' => 3]],
        'filters' => $filters,
        'defaults' => ['filters' => FALSE, 'filter_groups' => FALSE, 'pager' => FALSE, 'title' => FALSE],
        'filter_groups' => ['operator' => 'AND', 'groups' => [1 => 'AND']],
        'block_description' => 'Ultimi avvisi',
      ],
    ],
  ],
]);
$view->save();
echo "View 'avvisi' created.\n";
