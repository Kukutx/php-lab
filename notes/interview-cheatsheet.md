# PHP 面试速记 · PHP interview cheat sheet

每题：中文要点 → 🇮🇹 一句可以直接说的意大利语回答。
代码例子都能在 [`pdo-crud/`](../pdo-crud) 里找到对应实现。

---

## 1. `==` vs `===`

- `==` 宽松比较：先做类型转换再比值。`"1" == 1` 为 true，`null == false` 为 true。
- `===` 严格比较：类型和值都要相同。`"1" === 1` 为 false。
- PHP 8 改了字符串和数字的比较：`0 == "abc"` 以前是 true，现在是 false（数字先转成字符串比）。
- 实践：默认用 `===`；`match` 内部就是 `===`；`in_array($x, $arr, true)` 记得第三个参数。

🇮🇹 *"`==` confronta il valore dopo la conversione di tipo, `===` confronta anche il tipo: per questo uso quasi sempre `===`, che evita sorprese come `'1' == 1`."*

## 2. `include` / `require` / `_once`

- `include` 文件不存在：警告（E_WARNING），脚本继续。
- `require` 文件不存在：致命错误，脚本停止。必要的文件（autoload、配置）用 require。
- `_once`：同一文件只加载一次，防止函数/类重复定义。
- 现代项目几乎不手写：Composer 自动加载只需 `require 'vendor/autoload.php'` 一次。

🇮🇹 *"`include` dà solo un warning se il file manca, `require` blocca lo script; la variante `_once` evita di caricare due volte lo stesso file. Oggi però uso l'autoload di Composer."*

## 3. Session vs Cookie

- Cookie：数据存在**浏览器**，每次请求都带上，用户可以看到和修改，有大小限制（~4KB）。
- Session：数据存在**服务器**，浏览器只保存一个 session ID（通常放在 `PHPSESSID` cookie 里）。
- 安全：cookie 设 `HttpOnly`（JS 读不到，防 XSS 偷 cookie）、`Secure`（只走 HTTPS）、`SameSite=Lax`（防 CSRF）；登录后 `session_regenerate_id(true)` 防会话固定攻击。
- 见 `pdo-crud/public/index.php` 的 `session_start([...])`。

🇮🇹 *"Il cookie vive nel browser, la sessione sul server: il browser conserva solo l'ID di sessione. I dati sensibili li tengo in sessione e imposto i cookie con HttpOnly, Secure e SameSite."*

## 4. `password_hash`

- `password_hash($pwd, PASSWORD_DEFAULT)`：默认 bcrypt（也可 Argon2id），**自动加随机 salt**，结果里包含算法、cost、salt。
- 校验用 `password_verify($pwd, $hash)`，不要自己比较。
- `password_needs_rehash()`：算法或 cost 升级时，用户登录成功后顺便重新哈希。
- 绝不用 `md5`/`sha1` 存密码：太快，容易被暴力破解。

🇮🇹 *"Per le password uso `password_hash` con `PASSWORD_DEFAULT`, che genera il salt da solo, e `password_verify` per il controllo. MD5 o SHA1 sono troppo veloci e quindi insicuri."*

## 5. SQL 注入 / XSS / CSRF

| 攻击 | 原理 | 防御 |
|---|---|---|
| SQL 注入 | 用户输入被拼进 SQL，改变了查询结构 | PDO **预处理语句** + 绑定参数；`ATTR_EMULATE_PREPARES=false`；表名/排序字段用白名单 |
| XSS | 用户输入被原样输出到 HTML，变成可执行的脚本 | **输出时转义** `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`；模板引擎（Blade `{{ }}`、Twig）默认自动转义；CSP 头 |
| CSRF | 别的网站诱导已登录用户的浏览器发请求 | 表单里放随机 **token** 并在服务器校验；`SameSite` cookie；修改数据只用 POST |

代码：`PdoContactRepository`（预处理）、`templates/helpers.php` 的 `e()`（转义）、`src/Http/Csrf.php`（token + `hash_equals`）。

🇮🇹 *"Contro la SQL injection uso prepared statement con PDO; contro l'XSS faccio l'escape dell'output con `htmlspecialchars` o il templating di Blade/Twig; contro il CSRF uso un token nel form e cookie SameSite."*

## 6. Composer 与自动加载

- Composer = PHP 的包管理器（类似 npm / NuGet）。`composer.json` 声明依赖，`composer.lock` 锁定确切版本（要提交到 git）。
- `composer install` 按 lock 安装；`composer update` 更新并改写 lock。
- 自动加载：`"autoload": {"psr-4": {"App\\": "src/"}}`，于是 `App\Domain\Contact` → `src/Domain/Contact.php`。用到类时才加载（`spl_autoload_register`）。
- 改了 autoload 配置后运行 `composer dump-autoload`；生产环境用 `--optimize`/`-o` 生成 classmap。

🇮🇹 *"Composer gestisce le dipendenze e genera l'autoloader: con PSR-4 un namespace corrisponde a una cartella, quindi non scrivo mai `require` per le mie classi. Il file `composer.lock` va sempre committato."*

## 7. PSR-1 / PSR-4 / PSR-12

- **PSR-1** 基础规范：`<?php` 标签、UTF-8、类名 `StudlyCaps`、方法名 `camelCase`、常量 `UPPER_CASE`；一个文件要么声明符号要么产生副作用，不要两者都做。
- **PSR-4** 自动加载：命名空间前缀 ↔ 目录，类名 ↔ 文件名。
- **PSR-12** 代码风格（取代 PSR-2）：4 空格缩进、类和方法的 `{` 换行、`declare(strict_types=1)` 的位置等。工具：PHP_CodeSniffer（`phpcs`/`phpcbf`）、PHP-CS-Fixer。
- 其他常提：PSR-3 Logger、PSR-7 HTTP 消息、PSR-11 容器、PSR-15 中间件。

🇮🇹 *"PSR-1 e PSR-12 riguardano lo stile del codice, PSR-4 l'autoloading. Nei progetti li faccio rispettare in automatico con PHP_CodeSniffer."*

## 8. PHP 8 新特性（8.0 → 8.4）

- **8.0**：命名参数、构造器属性提升、联合类型、`match`、nullsafe `?->`、attributes `#[...]`、JIT、`str_contains`。
- **8.1**：**enum**、**readonly 属性**、`never` 返回类型、first-class callable `strlen(...)`、Fibers、纯交集类型。
- **8.2**：**readonly class**、DNF 类型、`true/false/null` 独立类型、动态属性被弃用。
- **8.3**：类型化类常量、`#[\Override]`、`json_validate()`。
- **8.4**：property hooks、非对称可见性 `public private(set)`、`new` 不用括号链式调用。
- 项目里用到的：`readonly class Contact`、`enum Category: string`、`match`、构造器提升、命名参数。

🇮🇹 *"Da PHP 8 uso molto gli enum, le proprietà readonly, `match`, la promozione delle proprietà nel costruttore e i named arguments: rendono il codice più sicuro e leggibile."*

## 9. trait / interface / 抽象类

- **interface**：只有契约（方法签名 + 常量），一个类可以实现多个。表达"能做什么"。
- **抽象类**：可以有实现和状态（属性），也可以有抽象方法；只能单继承。表达"是什么"，共享基础实现。
- **trait**：横向复用代码，"复制粘贴"到类里；不是类型，不能 `instanceof`；冲突用 `insteadof`/`as` 解决。
- 例子：`ContactRepository` 是 interface，`PdoContactRepository` 实现它，控制器只依赖接口 → 可替换、易测试。

🇮🇹 *"L'interfaccia definisce un contratto, la classe astratta condivide implementazione e stato con ereditarietà singola, il trait riusa codice orizzontalmente tra classi diverse."*

## 10. `static` / `self` / 延迟静态绑定

- `self::` 指向**写这段代码的那个类**（编译时决定）。
- `static::` 指向**实际调用的那个类**（运行时决定）= 延迟静态绑定（Late Static Binding）。
- 经典例子：父类 `public static function create(): static { return new static(); }`，子类调用时返回子类实例；用 `new self()` 会返回父类。
- `static` 也用于静态属性/方法（属于类不属于实例）和函数里的静态变量。

```php
class Model { public static function make(): static { return new static(); } }
class User extends Model {}
User::make(); // User, 如果用 new self() 则是 Model
```

🇮🇹 *"`self` si riferisce alla classe in cui è scritto il codice, `static` alla classe effettivamente chiamata a runtime: è il late static binding, utile per esempio nei metodi factory ereditati."*

## 11. 错误 vs 异常

- PHP 7+ 统一层级：`Throwable` ← `Error`（引擎错误：TypeError、DivisionByZeroError…）和 `Exception`（业务/可预期的问题）。
- 老式错误（warning/notice）不能 catch，可以用 `set_error_handler` 转成 `ErrorException`。
- 实践：自定义异常表达业务含义（`ContactNotFoundException` → 404，`ValidationException` → 422）；在边界（控制器/全局 handler）统一捕获；`finally` 做清理；生产环境 `display_errors=Off`，记录到日志。
- PDO 设 `ERRMODE_EXCEPTION`，出错直接抛异常而不是返回 false。

🇮🇹 *"Dal PHP 7 sia `Error` che `Exception` implementano `Throwable`. Creo eccezioni personalizzate per i casi di business e le gestisco in un punto centrale; in produzione gli errori vanno nei log, non a schermo."*

## 12. PHP-FPM / OPcache

- **PHP-FPM**（FastCGI Process Manager）：一个常驻的 PHP 进程池。Nginx 收到 `.php` 请求后通过 FastCGI 交给 FPM 的 worker 执行。可调 `pm = dynamic/static/ondemand`、`pm.max_children`。与 Apache mod_php 相比：Web 服务器和 PHP 解耦，资源隔离更好。
- **OPcache**：把 PHP 脚本编译后的 **opcode 缓存在共享内存**，下次请求不用再解析/编译 → 性能大幅提升。生产建议 `opcache.validate_timestamps=0`（部署后重启 FPM 清缓存）；PHP 7.4+ 还有 preloading。
- PHP 是"每个请求从头开始"（share-nothing），所以 OPcache 很关键。DDEV 默认就是 nginx + php-fpm。

🇮🇹 *"PHP-FPM è un pool di processi che esegue il PHP dietro Nginx tramite FastCGI; OPcache tiene in memoria il bytecode già compilato, così ogni richiesta non deve ricompilare gli script."*

---

## 面试小技巧

- 先说结论，再举项目里的例子："In un progetto di esercizio ho fatto un CRUD con PDO…"
- 讲你的背景：Drupal 9→10 升级（Maggioli）、WordPress/WooCommerce（Hinge 16）、.NET 和 Node 的经验迁移（DI、ORM、MVC 概念相通）。
- 不会的就说思路："Non l'ho usato direttamente, ma immagino funzioni come… in .NET."
