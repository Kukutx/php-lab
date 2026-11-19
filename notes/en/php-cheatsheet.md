# PHP cheat sheet

[中文](../zh/php-cheatsheet.md) · **English** · [Italiano](../it/php-cheatsheet.md)

Twelve PHP fundamentals that come up often. Each topic lists the key points and ends with a one-sentence summary. Where the project has a matching implementation, the note points to the file in [`pdo-crud/`](../../pdo-crud).

## 1. `==` vs `===`

- `==` is loose comparison: types are converted first, then the values are compared. `"1" == 1` is true, and so is `null == false`.
- `===` is strict comparison: type and value must both match. `"1" === 1` is false.
- PHP 8 changed how strings and numbers are compared: `0 == "abc"` used to be true and is now false (the number is converted to a string first).
- In practice, use `===` by default. `match` uses `===` internally; remember the third argument in `in_array($x, $arr, true)`.

**In short:** `==` compares values after type conversion, `===` also compares the type, so `===` is the default and avoids surprises like `'1' == 1`.

## 2. `include`, `require` and `_once`

- `include`: if the file is missing, PHP raises a warning (E_WARNING) and the script continues.
- `require`: if the file is missing, a fatal error stops the script. Use it for files that must exist (autoload, configuration).
- `_once`: the same file is loaded only once, so functions and classes aren't defined twice.
- Modern projects rarely write these by hand: with Composer's autoloader, a single `require 'vendor/autoload.php'` is enough.

**In short:** a missing file only triggers a warning with `include` but stops the script with `require`; `_once` prevents loading a file twice. These days Composer's autoloader takes care of it.

## 3. Sessions vs cookies

- Cookie: the data lives in the **browser**, is sent with every request, can be read and changed by the user, and is limited in size (about 4 KB).
- Session: the data lives on the **server**; the browser only keeps a session ID (usually in the `PHPSESSID` cookie).
- Security: set cookies with `HttpOnly` (JavaScript can't read them, so XSS can't steal them), `Secure` (HTTPS only) and `SameSite=Lax` (against CSRF); call `session_regenerate_id(true)` after login to prevent session fixation.
- See `session_start([...])` in `pdo-crud/public/index.php`.

**In short:** cookies live in the browser and sessions on the server, with only the session ID kept in the browser. Sensitive data goes in the session, and cookies get HttpOnly, Secure and SameSite.

## 4. `password_hash`

- `password_hash($pwd, PASSWORD_DEFAULT)` uses bcrypt by default (Argon2id is also available), **generates a random salt automatically**, and returns a string that contains the algorithm, cost and salt.
- Check passwords with `password_verify($pwd, $hash)`; never compare the strings yourself.
- `password_needs_rehash()`: after an algorithm or cost upgrade, rehash the next time the user logs in successfully.
- Never store passwords with `md5` or `sha1`: they are fast to compute, which makes them easy to brute-force.

**In short:** store passwords with `password_hash` and `PASSWORD_DEFAULT`, which generates the salt, and check them with `password_verify`. MD5 and SHA1 are too fast to be safe.

## 5. SQL injection, XSS, CSRF

| Attack | How it works | Defence |
| --- | --- | --- |
| SQL injection | User input is concatenated into SQL and changes the structure of the query | PDO **prepared statements** with bound parameters; `ATTR_EMULATE_PREPARES=false`; allow-lists for table names and sort columns |
| XSS | User input is written into HTML as is and becomes executable script | **Escape on output**: `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; template engines (Blade's `{{ }}`, Twig) escape by default; a CSP header |
| CSRF | Another site makes a logged-in user's browser send a request | A random **token** in the form, checked on the server; `SameSite` cookies; data changes only through POST |

Code: `PdoContactRepository` (prepared statements), `e()` in `templates/helpers.php` (escaping), `src/Http/Csrf.php` (token and `hash_equals`).

**In short:** prepared statements with PDO against SQL injection, output escaping (`htmlspecialchars`, or Blade and Twig templates) against XSS, and a form token plus SameSite cookies against CSRF.

## 6. Composer and autoloading

- Composer is PHP's package manager (like npm or NuGet). `composer.json` declares the dependencies and `composer.lock` pins their exact versions (commit it to Git).
- `composer install` installs from the lock file; `composer update` updates the dependencies and rewrites the lock file.
- Autoloading: with `"autoload": {"psr-4": {"App\\": "src/"}}`, `App\Domain\Contact` maps to `src/Domain/Contact.php`. Classes are loaded the first time they are used (`spl_autoload_register`).
- After changing the autoload configuration, run `composer dump-autoload`; in production, add `--optimize` (`-o`) to generate a classmap.

**In short:** Composer manages dependencies and generates the autoloader. With PSR-4 a namespace maps to a folder, so your own classes never need a `require`. Always commit `composer.lock`.

## 7. PSR-1, PSR-4, PSR-12

- **PSR-1**, the basic coding standard: `<?php` tags, UTF-8, class names in `StudlyCaps`, method names in `camelCase`, constants in `UPPER_CASE`; a file either declares symbols or causes side effects, not both.
- **PSR-4**, autoloading: a namespace prefix maps to a directory, a class name to a file name.
- **PSR-12**, the coding style (replaces PSR-2): 4-space indentation, the opening `{` of classes and methods on its own line, where `declare(strict_types=1)` goes, and so on. Tools: PHP_CodeSniffer (`phpcs`, `phpcbf`) and PHP-CS-Fixer.
- Others that come up: PSR-3 (logger), PSR-7 (HTTP messages), PSR-11 (container), PSR-15 (middleware).

**In short:** PSR-1 and PSR-12 cover coding style, PSR-4 covers autoloading. In my projects PHP_CodeSniffer enforces them automatically.

## 8. What's new in PHP 8 (8.0 to 8.4)

- **8.0**: named arguments, constructor property promotion, union types, `match`, the nullsafe operator `?->`, attributes `#[...]`, JIT, `str_contains`.
- **8.1**: **enums**, **readonly properties**, the `never` return type, first-class callable syntax `strlen(...)`, Fibers, pure intersection types.
- **8.2**: **readonly classes**, DNF types, `true`, `false` and `null` as standalone types, deprecation of dynamic properties.
- **8.3**: typed class constants, `#[\Override]`, `json_validate()`.
- **8.4**: property hooks, asymmetric visibility (`public private(set)`), calling methods on `new` without extra parentheses.
- Used in this project: `readonly class Contact`, `enum Category: string`, `match`, constructor promotion, named arguments.

**In short:** since PHP 8 I mostly use enums, readonly properties, `match`, constructor promotion and named arguments; they make code safer and easier to read.

## 9. Traits, interfaces and abstract classes

- **Interface**: a contract only (method signatures and constants); a class can implement several. It describes what something can do.
- **Abstract class**: can contain implementation and state (properties) as well as abstract methods; single inheritance only. It describes what something is, and shares a base implementation.
- **Trait**: horizontal code reuse; the code is effectively copied into the class. A trait isn't a type, so it can't be checked with `instanceof`; method conflicts are resolved with `insteadof` and `as`.
- Example: `ContactRepository` is an interface, `PdoContactRepository` implements it, and the controller depends only on the interface, so the implementation can be swapped and is easy to test.

**In short:** an interface defines a contract, an abstract class shares implementation and state through single inheritance, and a trait reuses code across unrelated classes.

## 10. `static`, `self` and late static binding

- `self::` refers to **the class where the code is written**, resolved at compile time.
- `static::` refers to **the class that was actually called**, resolved at runtime. This is late static binding.
- Classic example: a parent class with `public static function create(): static { return new static(); }` returns an instance of the subclass when called on it; with `new self()` it would return the parent class.
- `static` is also used for static properties and methods (which belong to the class, not to an instance) and for static variables inside functions.

```php
class Model { public static function make(): static { return new static(); } }
class User extends Model {}
User::make(); // User; with new self() it would be Model
```

**In short:** `self` is the class where the code is written, `static` is the class actually called at runtime. This late static binding is useful in inherited factory methods, for example.

## 11. Errors vs exceptions

- Since PHP 7 there is a single hierarchy: `Throwable`, with `Error` (engine errors such as TypeError and DivisionByZeroError) and `Exception` (expected, business-level problems) below it.
- Old-style errors (warnings, notices) can't be caught with `catch`; `set_error_handler` can turn them into an `ErrorException`.
- In practice: custom exceptions carry business meaning (`ContactNotFoundException` becomes a 404, `ValidationException` a 422) and are caught in one place at the boundary (a controller or a global handler); `finally` does the cleanup; in production, `display_errors=Off` and errors go to the log.
- Set PDO to `ERRMODE_EXCEPTION`, so that errors throw an exception instead of returning false.

**In short:** since PHP 7, both `Error` and `Exception` implement `Throwable`. Business cases get custom exceptions handled in one place, and in production errors go to the logs, not to the screen.

## 12. PHP-FPM and OPcache

- **PHP-FPM** (FastCGI Process Manager): a pool of long-running PHP processes. Nginx receives a `.php` request and hands it to an FPM worker through FastCGI. It can be tuned with `pm = dynamic/static/ondemand` and `pm.max_children`. Compared with Apache's mod_php, the web server and PHP run separately, with better resource isolation.
- **OPcache** **caches compiled opcode in shared memory**, so later requests don't parse and compile the scripts again, which gives a large performance gain. In production `opcache.validate_timestamps=0` is recommended (restart FPM after each deployment to clear the cache); PHP 7.4 also added preloading.
- PHP starts every request from scratch (share-nothing), which is why OPcache matters so much. DDEV uses nginx and php-fpm by default.

**In short:** PHP-FPM is a process pool that runs PHP behind Nginx through FastCGI; OPcache keeps compiled bytecode in memory, so scripts aren't recompiled on every request.
