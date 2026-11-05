<?php

/**
 * @file
 * Demo content (categories and notices).
 *
 * Run with: drush php:script scripts/demo-content.php
 * Content is NOT config: `drush cex` does not export it, so a fresh site
 * installed from config/sync needs this script for example data.
 */

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

// Terms are content too: create the categories if they are missing
// (hook_install() skips them when installing from existing config).
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
foreach (['Lavori stradali', 'Eventi', 'Servizi', 'Emergenze'] as $weight => $name) {
  if (!$term_storage->loadByProperties(['vid' => 'categorie_avvisi', 'name' => $name])) {
    Term::create(['vid' => 'categorie_avvisi', 'name' => $name, 'weight' => $weight])->save();
  }
}

$terms = [];
foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'categorie_avvisi']) as $term) {
  $terms[$term->label()] = $term->id();
}

$avvisi = [
  ['Chiusura di Via Roma per lavori alla rete idrica', 'Lavori stradali', TRUE, '+20 days', 'Dal 10 al 21 novembre Via Roma sarà chiusa al traffico tra i civici 10 e 40, dalle 8 alle 18. Le linee bus 3 e 7 sono deviate su Corso Italia.'],
  ['Nuovi orari dell\'Ufficio Anagrafe', 'Servizi', TRUE, NULL, 'Dal 1° dicembre l\'Ufficio Anagrafe è aperto dal lunedì al venerdì dalle 8:30 alle 12:30 e il martedì e giovedì pomeriggio dalle 14:30 alle 17:00. Prenotazione online consigliata.'],
  ['Allerta meteo arancione per pioggia intensa', 'Emergenze', TRUE, '+1 day', 'La Protezione Civile ha emesso un\'allerta arancione. Evitare sottopassi e argini, mettere in sicurezza gli oggetti sui balconi e seguire i canali ufficiali.'],
  ['Festa d\'autunno in Piazza Garibaldi', 'Eventi', FALSE, '+10 days', 'Musica, prodotti tipici e laboratori per bambini sabato e domenica dalle 10 alle 20. Ingresso libero.'],
  ['Calendario raccolta rifiuti durante le festività', 'Servizi', FALSE, '+40 days', 'Durante le festività la raccolta porta a porta segue un calendario diverso: la carta del 24 dicembre è anticipata al 23, il vetro del 1° gennaio è posticipato al 2.'],
  ['Bando contributi per le attività commerciali', 'Servizi', FALSE, '-5 days', 'Sono aperte fino al 31 ottobre le domande per i contributi alle attività commerciali del centro storico.'],
];

$i = 0;
foreach ($avvisi as [$title, $category, $evidenza, $scadenza, $body]) {
  Node::create([
    'type' => 'avviso',
    'title' => $title,
    'body' => ['value' => "<p>$body</p>", 'format' => 'basic_html'],
    'field_categoria' => $terms[$category],
    'field_in_evidenza' => $evidenza,
    'field_scadenza' => $scadenza ? gmdate('Y-m-d', strtotime($scadenza)) : NULL,
    'created' => strtotime('-' . ($i++ * 2) . ' days'),
    'status' => 1,
    'uid' => 1,
  ])->save();
}

echo "Demo content ready.\n";
