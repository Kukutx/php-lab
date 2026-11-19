# Appunti di PHP

[中文](../zh/php-cheatsheet.md) · [English](../en/php-cheatsheet.md) · **Italiano**

Dodici argomenti di base di PHP che si incontrano spesso. Per ognuno ci sono i punti principali e una frase di sintesi. Dove il progetto ha un'implementazione corrispondente, indico il file in [`pdo-crud/`](../../pdo-crud).

## 1. `==` e `===`

- `==` è il confronto debole: prima converte i tipi, poi confronta i valori. `"1" == 1` è true, e lo è anche `null == false`.
- `===` è il confronto stretto: tipo e valore devono coincidere. `"1" === 1` è false.
- PHP 8 ha cambiato il confronto tra stringhe e numeri: `0 == "abc"` prima era true, ora è false (il numero viene convertito prima in stringa).
- In pratica si usa `===` di default. `match` usa `===` internamente; in `in_array($x, $arr, true)` bisogna ricordarsi il terzo argomento.

**In breve:** `==` confronta il valore dopo la conversione di tipo, `===` confronta anche il tipo: per questo uso quasi sempre `===`, che evita sorprese come `'1' == 1`.

## 2. `include`, `require` e `_once`

- `include`: se il file manca, PHP emette un warning (E_WARNING) e lo script continua.
- `require`: se il file manca, un errore fatale ferma lo script. Si usa per i file indispensabili (autoload, configurazione).
- `_once`: lo stesso file si carica una sola volta, così funzioni e classi non vengono definite due volte.
- Nei progetti moderni non si scrivono quasi mai a mano: con l'autoload di Composer basta un solo `require 'vendor/autoload.php'`.

**In breve:** se il file manca, `include` dà solo un warning e `require` blocca lo script; la variante `_once` evita di caricare due volte lo stesso file. Oggi però uso l'autoload di Composer.

## 3. Sessione e cookie

- Cookie: i dati stanno nel **browser**, viaggiano con ogni richiesta, l'utente li può leggere e modificare, e la dimensione è limitata (circa 4 KB).
- Sessione: i dati stanno sul **server**; il browser conserva solo l'ID di sessione (di solito nel cookie `PHPSESSID`).
- Sicurezza: i cookie vanno impostati con `HttpOnly` (JavaScript non può leggerli, quindi un attacco XSS non può rubarli), `Secure` (solo HTTPS) e `SameSite=Lax` (contro il CSRF); dopo il login si chiama `session_regenerate_id(true)` per evitare la session fixation.
- Vedi `session_start([...])` in `pdo-crud/public/index.php`.

**In breve:** il cookie vive nel browser, la sessione sul server, e il browser conserva solo l'ID di sessione. I dati sensibili li tengo in sessione e imposto i cookie con HttpOnly, Secure e SameSite.

## 4. `password_hash`

- `password_hash($pwd, PASSWORD_DEFAULT)` usa bcrypt di default (è disponibile anche Argon2id), **genera da solo un salt casuale** e restituisce una stringa che contiene algoritmo, cost e salt.
- Per il controllo si usa `password_verify($pwd, $hash)`, mai un confronto tra stringhe fatto a mano.
- `password_needs_rehash()`: quando cambiano algoritmo o cost, si ricalcola l'hash al successivo login riuscito dell'utente.
- Mai salvare le password con `md5` o `sha1`: sono troppo veloci da calcolare, quindi è facile forzarle con un attacco brute force.

**In breve:** per le password uso `password_hash` con `PASSWORD_DEFAULT`, che genera il salt da solo, e `password_verify` per il controllo. MD5 e SHA1 sono troppo veloci e quindi insicuri.

## 5. SQL injection, XSS, CSRF

| Attacco | Come funziona | Difesa |
| --- | --- | --- |
| SQL injection | L'input dell'utente viene concatenato nell'SQL e cambia la struttura della query | **Prepared statement** PDO con parametri associati; `ATTR_EMULATE_PREPARES=false`; whitelist per nomi di tabella e colonne di ordinamento |
| XSS | L'input dell'utente finisce nell'HTML così com'è e diventa uno script eseguibile | **Escape in output**: `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; i motori di template (`{{ }}` di Blade, Twig) fanno l'escape di default; header CSP |
| CSRF | Un altro sito fa partire una richiesta dal browser di un utente autenticato | Un **token** casuale nel form, verificato sul server; cookie `SameSite`; dati modificati solo via POST |

Codice: `PdoContactRepository` (prepared statement), `e()` in `templates/helpers.php` (escape), `src/Http/Csrf.php` (token e `hash_equals`).

**In breve:** contro la SQL injection uso prepared statement con PDO; contro l'XSS faccio l'escape dell'output con `htmlspecialchars` o con i template di Blade e Twig; contro il CSRF uso un token nel form e cookie SameSite.

## 6. Composer e autoload

- Composer è il gestore di pacchetti di PHP (come npm o NuGet). `composer.json` dichiara le dipendenze, `composer.lock` ne fissa le versioni esatte (va committato in Git).
- `composer install` installa dal lock file; `composer update` aggiorna le dipendenze e riscrive il lock file.
- Autoload: con `"autoload": {"psr-4": {"App\\": "src/"}}`, `App\Domain\Contact` corrisponde a `src/Domain/Contact.php`. Le classi vengono caricate la prima volta che servono (`spl_autoload_register`).
- Dopo aver cambiato la configurazione dell'autoload si lancia `composer dump-autoload`; in produzione si aggiunge `--optimize` (`-o`) per generare una classmap.

**In breve:** Composer gestisce le dipendenze e genera l'autoloader: con PSR-4 un namespace corrisponde a una cartella, quindi non scrivo mai `require` per le mie classi. Il file `composer.lock` va sempre committato.

## 7. PSR-1, PSR-4, PSR-12

- **PSR-1**, lo standard di base: tag `<?php`, UTF-8, nomi di classe in `StudlyCaps`, metodi in `camelCase`, costanti in `UPPER_CASE`; un file dichiara simboli oppure produce effetti collaterali, non entrambe le cose.
- **PSR-4**, l'autoload: il prefisso del namespace corrisponde a una cartella, il nome della classe al nome del file.
- **PSR-12**, lo stile del codice (sostituisce PSR-2): indentazione di 4 spazi, `{` di classi e metodi su una riga a sé, posizione di `declare(strict_types=1)` e così via. Strumenti: PHP_CodeSniffer (`phpcs`, `phpcbf`) e PHP-CS-Fixer.
- Altri che si citano spesso: PSR-3 (logger), PSR-7 (messaggi HTTP), PSR-11 (container), PSR-15 (middleware).

**In breve:** PSR-1 e PSR-12 riguardano lo stile del codice, PSR-4 l'autoload. Nei miei progetti li faccio rispettare in automatico con PHP_CodeSniffer.

## 8. Le novità di PHP 8 (dalla 8.0 alla 8.4)

- **8.0**: named arguments, constructor property promotion, union types, `match`, operatore nullsafe `?->`, attributi `#[...]`, JIT, `str_contains`.
- **8.1**: **enum**, **proprietà readonly**, tipo di ritorno `never`, sintassi first-class callable `strlen(...)`, Fiber, tipi intersezione puri.
- **8.2**: **classi readonly**, tipi DNF, `true`, `false` e `null` come tipi autonomi, proprietà dinamiche deprecate.
- **8.3**: costanti di classe tipizzate, `#[\Override]`, `json_validate()`.
- **8.4**: property hook, visibilità asimmetrica (`public private(set)`), chiamata di metodi su `new` senza parentesi aggiuntive.
- Usate in questo progetto: `readonly class Contact`, `enum Category: string`, `match`, constructor promotion, named arguments.

**In breve:** da PHP 8 uso soprattutto gli enum, le proprietà readonly, `match`, la promozione delle proprietà nel costruttore e i named arguments: rendono il codice più sicuro e più leggibile.

## 9. Trait, interfacce e classi astratte

- **Interfaccia**: solo un contratto (firme dei metodi e costanti); una classe può implementarne più di una. Descrive cosa un oggetto sa fare.
- **Classe astratta**: può contenere implementazione e stato (proprietà) oltre a metodi astratti; ereditarietà singola. Descrive cosa un oggetto è, e condivide un'implementazione di base.
- **Trait**: riuso orizzontale del codice, che di fatto viene copiato nella classe. Un trait non è un tipo, quindi non si può verificare con `instanceof`; i conflitti tra metodi si risolvono con `insteadof` e `as`.
- Esempio: `ContactRepository` è un'interfaccia, `PdoContactRepository` la implementa e il controller dipende solo dall'interfaccia, quindi l'implementazione si può sostituire ed è facile da testare.

**In breve:** l'interfaccia definisce un contratto, la classe astratta condivide implementazione e stato con l'ereditarietà singola, il trait riusa il codice tra classi non collegate.

## 10. `static`, `self` e late static binding

- `self::` indica **la classe in cui è scritto il codice**, risolta in fase di compilazione.
- `static::` indica **la classe effettivamente chiamata**, risolta a runtime. È il late static binding.
- Esempio classico: una classe padre con `public static function create(): static { return new static(); }` restituisce un'istanza della sottoclasse quando viene chiamata su di essa; con `new self()` restituirebbe la classe padre.
- `static` si usa anche per proprietà e metodi statici (che appartengono alla classe, non a un'istanza) e per le variabili statiche dentro le funzioni.

```php
class Model { public static function make(): static { return new static(); } }
class User extends Model {}
User::make(); // User; con new self() sarebbe Model
```

**In breve:** `self` si riferisce alla classe in cui è scritto il codice, `static` alla classe effettivamente chiamata a runtime: è il late static binding, utile per esempio nei metodi factory ereditati.

## 11. Errori ed eccezioni

- Da PHP 7 c'è un'unica gerarchia: `Throwable`, con sotto `Error` (errori del motore, come TypeError e DivisionByZeroError) ed `Exception` (problemi di business previsti).
- I vecchi errori (warning, notice) non si possono intercettare con `catch`; `set_error_handler` li può trasformare in `ErrorException`.
- In pratica: eccezioni personalizzate con un significato di business (`ContactNotFoundException` diventa un 404, `ValidationException` un 422), intercettate in un solo punto al confine (un controller o un handler globale); `finally` per la pulizia; in produzione `display_errors=Off` ed errori nei log.
- PDO va impostato su `ERRMODE_EXCEPTION`, così in caso di errore lancia un'eccezione invece di restituire false.

**In breve:** da PHP 7 sia `Error` sia `Exception` implementano `Throwable`. Creo eccezioni personalizzate per i casi di business e le gestisco in un punto centrale; in produzione gli errori vanno nei log, non a schermo.

## 12. PHP-FPM e OPcache

- **PHP-FPM** (FastCGI Process Manager): un pool di processi PHP sempre attivi. Nginx riceve la richiesta `.php` e la passa a un worker FPM tramite FastCGI. Si regola con `pm = dynamic/static/ondemand` e `pm.max_children`. Rispetto a mod_php di Apache, web server e PHP girano separati e le risorse sono isolate meglio.
- **OPcache** **tiene in memoria condivisa l'opcode compilato**, così le richieste successive non devono analizzare e compilare di nuovo gli script: il guadagno di prestazioni è notevole. In produzione si consiglia `opcache.validate_timestamps=0` (dopo ogni deploy si riavvia FPM per svuotare la cache); da PHP 7.4 c'è anche il preloading.
- PHP riparte da zero a ogni richiesta (share-nothing), per questo OPcache è così importante. DDEV usa nginx e php-fpm di default.

**In breve:** PHP-FPM è un pool di processi che esegue PHP dietro Nginx tramite FastCGI; OPcache tiene in memoria il bytecode già compilato, così gli script non vengono ricompilati a ogni richiesta.
