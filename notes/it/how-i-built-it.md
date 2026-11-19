# Come è stato costruito e verificato php-lab

[中文](../zh/how-i-built-it.md) · [English](../en/how-i-built-it.md) · **Italiano**

Questa nota parla del metodo: come i progetti sono stati resi facili da avviare, come si controlla la qualità e come si correggono i problemi trovati. Non entra nel codice riga per riga.

## 1. Ogni progetto parte con un solo comando

- Ogni sottoprogetto usa **SQLite**: non serve installare MySQL e nel repository non c'è nessuna password.
- `demo/setup.sh` installa da zero i quattro siti (composer install, database, dati di esempio) e `demo/start.sh` li avvia con il server integrato di PHP sulle porte 8001–8004.
- `.devcontainer/` fa eseguire i due script in automatico a GitHub Codespaces, quindi basta un clic per vedere tutto online.
- Il motivo è che, se un progetto richiede dieci passaggi manuali per partire, gli altri (me compreso, tra qualche mese) non lo avviano. Se l'installazione è uno script, le istruzioni restano sempre allineate con quello che funziona davvero.

## 2. Controllare quello che fa un utente reale, non solo il codice

Ho scritto un piccolo script con **Playwright** (automazione del browser) che usa ogni sito come farebbe una persona:

1. Apre la home page e gli elenchi, usa i filtri e la paginazione.
2. Accede come admin/admin, poi crea, modifica ed elimina un contenuto.
3. Riduce la finestra alla larghezza di un telefono (390px) e ripete tutto.
4. Per tutto il tempo registra ogni risposta HTTP con stato 400 o superiore, gli errori nella console del browser e l'eventuale overflow orizzontale della pagina (`scrollWidth > 390`), e salva gli screenshot.

Problemi trovati e corretti in questo giro:

| Sito | Problema | Causa | Correzione |
| --- | --- | --- | --- |
| pdo-crud | Sul telefono la tabella esce dallo schermo | Troppe colonne | Tabella dentro `.table-responsive`, colonna Phone nascosta sul telefono, pulsanti senza a capo |
| laravel | Lasciando vuota la data, l'avviso "spariva" | Data vuota significa bozza; ordinando dal più recente, le date NULL finivano nell'ultima pagina; le bozze comparivano anche nell'elenco pubblico | Il form di creazione propone "adesso"; l'elenco mostra di default solo gli avvisi pubblicati, con un filtro "Drafts & scheduled / All"; aggiunto un test |
| laravel | Sul telefono il sottotitolo della barra di navigazione veniva tagliato | Spazio insufficiente | Sottotitolo nascosto sugli schermi piccoli (`d-none d-sm-inline`) |
| drupal10 | L'icona del menu a hamburger dava 404 nelle pagine di amministrazione | Il template usava un percorso relativo, che sotto `/user/1` diventava `/user/themes/...` | Uso sistematico del percorso assoluto `{{ sprites }}` generato nel preprocess |
| drupal10 | Nessun modo per accedere | Nessun link all'account nell'header | Come nel "modello Comuni", aggiunto "Accedi all'area personale" nello slim header; dopo l'accesso diventa "Area personale / Esci" |
| drupal10 | Nel menu Avvisi veniva prima di Home; il filtro mostrava "- Any -" in inglese | Le due voci di menu avevano entrambe weight 0; testo predefinito di Views | Corretti i weight ed esportati in `config/sync` con `drush cex`; testo cambiato con `hook_form_views_exposed_form_alter` |
| wordpress | Nessun menu di navigazione e nessun link per accedere | Lo script della demo non creava il menu | Lo script della demo crea Home / Avvisi / Servizi / Accedi con `wp_create_nav_menu` e lo assegna alla posizione `primary` |
| wordpress | Nel footer c'erano i widget predefiniti in inglese e "Hello world!" | Contenuti di esempio dell'installazione di WordPress | Eliminato l'articolo di esempio, footer sostituito con un blocco "Contatti" |
| wordpress | Elementor non era installato, quindi il widget personalizzato non si vedeva | Mai installato | `setup.sh` esegue `wp plugin install elementor --activate` (se c'è rete) e installa anche il pacchetto della lingua italiana |

## 3. La stessa procedura dopo ogni modifica

Dopo ogni modifica, in quest'ordine; la CI fa lo stesso:

```bash
# Test automatici
cd pdo-crud && composer test        # PHPUnit
cd laravel  && php artisan test     # test feature e unit di Laravel
# Standard del codice e analisi statica
composer stan && composer cs        # PHPStan level 8 + PSR-12 (pdo-crud)
vendor/bin/pint --test              # Laravel
vendor/bin/phpcs                    # Drupal coder / WordPress WPCS
# Poi di nuovo il controllo con Playwright, guardando gli screenshot
```

- Le modifiche alla configurazione di Drupal (per esempio i weight del menu) devono finire in `config/sync`, altrimenti con una reinstallazione si perdono. Dopo una modifica dall'interfaccia si esegue `drush cex`; chi scarica il codice esegue `drush cim`.
- I dati di WordPress (menu, widget) non stanno nel codice, quindi vanno in `scripts/demo-content.php`: così ogni installazione dà lo stesso risultato.

## 4. Alcune abitudini

- **Prima riprodurre, poi correggere**: uno script dimostra che il problema esiste, e lo stesso script dimostra che non c'è più.
- **Correggere la causa**: il problema di Laravel sembrava "l'avviso è sparito", ma la causa erano bozze e ordinamento; quindi, invece di cambiare solo l'ordinamento, è stato completato il concetto di bozza/pubblicato.
- **Commit piccoli**: una modifica per commit, con un messaggio che spiega il perché.
- **I segreti non entrano mai nel repository**: `.env`, `wp-config.php` e `settings.local.php` sono in `.gitignore`, e gitleaks controlla ogni commit nella CI e in un hook pre-commit.
