<?php

/**
 * @file
 * One-off script used to place the theme blocks the first time.
 *
 * The result is already exported in config/sync (block.block.comune_theme_*),
 * so you do NOT need to run it: it is kept to show how blocks map to regions.
 * Run with: drush php:script scripts/place-blocks.php && drush cex.
 */

$theme = 'comune_theme';
$blocks = [
  ['comune_theme_messages', 'system_messages_block', 'highlighted', -10, []],
  ['comune_theme_main_menu', 'system_menu_block:main', 'primary_menu', 0, ['level' => 1, 'depth' => 1]],
  ['comune_theme_breadcrumbs', 'system_breadcrumb_block', 'breadcrumb', 0, []],
  ['comune_theme_page_title', 'page_title_block', 'content', -5, []],
  ['comune_theme_local_tasks', 'local_tasks_block', 'content', -4, []],
  ['comune_theme_content', 'system_main_block', 'content', 0, []],
  ['comune_theme_evidenza', 'comune_avvisi_highlighted', 'home_evidenza', 0, []],
  ['comune_theme_ultimi_avvisi', 'views_block:avvisi-block_1', 'home_avvisi', 0, []],
];
$storage = \Drupal::entityTypeManager()->getStorage('block');
// Installing a theme copies blocks from the previous default theme into
// regions with the same name. Remove them and place exactly what we want.
$storage->delete($storage->loadByProperties(['theme' => $theme]));
foreach ($blocks as [$id, $plugin, $region, $weight, $settings]) {
  $storage->create([
    'id' => $id,
    'theme' => $theme,
    'plugin' => $plugin,
    'region' => $region,
    'weight' => $weight,
    'settings' => ['label_display' => '0'] + $settings,
  ])->save();
}

echo "Blocks placed.\n";
