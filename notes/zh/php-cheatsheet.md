# PHP 基础速记

**中文** · [English](../en/php-cheatsheet.md) · [Italiano](../it/php-cheatsheet.md)

十二个经常遇到的 PHP 基础问题。每个主题先列要点，最后用一句话总结。项目里有对应实现的，会注明 [`pdo-crud/`](../../pdo-crud) 里的文件。

## 1. `==` 和 `===`

- `==` 是宽松比较：先做类型转换，再比较值。`"1" == 1` 为 true，`null == false` 也为 true。
- `===` 是严格比较：类型和值都要相同。`"1" === 1` 为 false。
- PHP 8 改了字符串和数字的比较规则：`0 == "abc"` 以前是 true，现在是 false（数字先转成字符串再比较）。
- 实践中默认用 `===`。`match` 内部用的就是 `===`；`in_array($x, $arr, true)` 记得加第三个参数。

**一句话：** `==` 在类型转换后比较值，`===` 连类型一起比较，所以默认用 `===`，避免 `'1' == 1` 这类意外。

## 2. `include`、`require` 和 `_once`

- `include`：文件不存在时只发出警告（E_WARNING），脚本继续执行。
- `require`：文件不存在时报致命错误，脚本停止。必需的文件（autoload、配置）用 `require`。
- `_once`：同一个文件只加载一次，避免函数或类被重复定义。
- 现代项目里几乎不用手写这些：Composer 的自动加载只需要 `require 'vendor/autoload.php'` 一次。

**一句话：** 文件缺失时，`include` 只给警告，`require` 直接中止；`_once` 防止重复加载。现在一般交给 Composer 的自动加载。

## 3. Session 和 Cookie

- Cookie：数据存在**浏览器**里，每次请求都会带上，用户能看到，也能修改，大小有限（约 4 KB）。
- Session：数据存在**服务器**上，浏览器只保存一个 session ID（通常放在 `PHPSESSID` 这个 cookie 里）。
- 安全设置：cookie 加上 `HttpOnly`（JavaScript 读不到，防止被 XSS 窃取）、`Secure`（只走 HTTPS）、`SameSite=Lax`（防 CSRF）；登录后调用 `session_regenerate_id(true)`，防止会话固定攻击。
- 见 `pdo-crud/public/index.php` 里的 `session_start([...])`。

**一句话：** Cookie 在浏览器，Session 在服务器，浏览器只保存 session ID。敏感数据放进 session，cookie 加上 HttpOnly、Secure 和 SameSite。

## 4. `password_hash`

- `password_hash($pwd, PASSWORD_DEFAULT)`：默认用 bcrypt（也可以用 Argon2id），**自动生成随机 salt**，结果里包含算法、cost 和 salt。
- 校验用 `password_verify($pwd, $hash)`，不要自己比较字符串。
- `password_needs_rehash()`：算法或 cost 升级后，在用户下次成功登录时顺便重新计算哈希。
- 不要用 `md5` 或 `sha1` 存密码：它们算得太快，容易被暴力破解。

**一句话：** 存密码用 `password_hash` 加 `PASSWORD_DEFAULT`，salt 自动生成；校验用 `password_verify`。MD5、SHA1 太快，不安全。

## 5. SQL 注入、XSS、CSRF

| 攻击 | 原理 | 防御 |
| --- | --- | --- |
| SQL 注入 | 用户输入被拼进 SQL，改变了查询结构 | PDO **预处理语句**加参数绑定；`ATTR_EMULATE_PREPARES=false`；表名和排序字段用白名单 |
| XSS | 用户输入被原样输出到 HTML，变成可执行的脚本 | **输出时转义**：`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`；模板引擎（Blade 的 `{{ }}`、Twig）默认自动转义；CSP 响应头 |
| CSRF | 别的网站诱导已登录用户的浏览器发出请求 | 表单里放随机 **token**，在服务器端校验；`SameSite` cookie；修改数据只用 POST |

代码：`PdoContactRepository`（预处理语句）、`templates/helpers.php` 里的 `e()`（转义）、`src/Http/Csrf.php`（token 和 `hash_equals`）。

**一句话：** 防 SQL 注入靠 PDO 预处理语句，防 XSS 靠输出转义（`htmlspecialchars` 或 Blade、Twig 模板），防 CSRF 靠表单 token 和 SameSite cookie。

## 6. Composer 和自动加载

- Composer 是 PHP 的包管理器（类似 npm、NuGet）。`composer.json` 声明依赖，`composer.lock` 锁定确切版本（要提交到 Git）。
- `composer install` 按 lock 文件安装；`composer update` 更新依赖，并改写 lock 文件。
- 自动加载：配置 `"autoload": {"psr-4": {"App\\": "src/"}}` 之后，`App\Domain\Contact` 对应 `src/Domain/Contact.php`。类在第一次用到时才加载（`spl_autoload_register`）。
- 改了 autoload 配置要运行 `composer dump-autoload`；生产环境加 `--optimize`（`-o`），生成 classmap。

**一句话：** Composer 管理依赖，并生成自动加载器。按 PSR-4，命名空间对应目录，自己的类不用再写 `require`。`composer.lock` 一定要提交。

## 7. PSR-1、PSR-4、PSR-12

- **PSR-1** 基础规范：`<?php` 标签、UTF-8、类名用 `StudlyCaps`、方法名用 `camelCase`、常量用 `UPPER_CASE`；一个文件要么声明符号，要么产生副作用，不要两者都做。
- **PSR-4** 自动加载：命名空间前缀对应目录，类名对应文件名。
- **PSR-12** 代码风格（取代 PSR-2）：4 个空格缩进、类和方法的 `{` 另起一行、`declare(strict_types=1)` 的位置等。工具：PHP_CodeSniffer（`phpcs`、`phpcbf`）、PHP-CS-Fixer。
- 其他常提到的：PSR-3 日志、PSR-7 HTTP 消息、PSR-11 容器、PSR-15 中间件。

**一句话：** PSR-1 和 PSR-12 管代码风格，PSR-4 管自动加载。项目里用 PHP_CodeSniffer 自动检查。

## 8. PHP 8 的新特性（8.0 到 8.4）

- **8.0**：命名参数、构造器属性提升、联合类型、`match`、nullsafe 运算符 `?->`、attributes `#[...]`、JIT、`str_contains`。
- **8.1**：**enum**、**readonly 属性**、`never` 返回类型、first-class callable 语法 `strlen(...)`、Fibers、纯交集类型。
- **8.2**：**readonly class**、DNF 类型、`true`/`false`/`null` 可以单独作为类型、动态属性被弃用。
- **8.3**：类常量可以声明类型、`#[\Override]`、`json_validate()`。
- **8.4**：property hooks、非对称可见性 `public private(set)`、`new` 表达式不加括号也能链式调用方法。
- 这个项目里用到的：`readonly class Contact`、`enum Category: string`、`match`、构造器属性提升、命名参数。

**一句话：** PHP 8 以后我常用 enum、readonly、`match`、构造器属性提升和命名参数，代码更安全，也更好读。

## 9. trait、interface 和抽象类

- **interface**：只有契约（方法签名和常量），一个类可以实现多个接口。描述的是"能做什么"。
- **抽象类**：可以有实现和状态（属性），也可以有抽象方法；只能单继承。描述的是"是什么"，用来共享基础实现。
- **trait**：横向复用代码，相当于把代码复制进类里；它不是类型，不能用 `instanceof` 判断；方法冲突用 `insteadof` 和 `as` 解决。
- 例子：`ContactRepository` 是接口，`PdoContactRepository` 实现它，控制器只依赖接口，所以实现可以替换，也容易测试。

**一句话：** interface 定义契约；抽象类在单继承下共享实现和状态；trait 在不相关的类之间横向复用代码。

## 10. `static`、`self` 和延迟静态绑定

- `self::` 指向**写这段代码的那个类**，编译时就确定了。
- `static::` 指向**实际被调用的那个类**，运行时才确定。这就是延迟静态绑定（late static binding）。
- 典型例子：父类里写 `public static function create(): static { return new static(); }`，在子类上调用时返回子类实例；如果写成 `new self()`，返回的是父类实例。
- `static` 还用于静态属性和静态方法（属于类，不属于实例），以及函数里的静态变量。

```php
class Model { public static function make(): static { return new static(); } }
class User extends Model {}
User::make(); // 返回 User；如果写成 new self()，返回的是 Model
```

**一句话：** `self` 指代码所在的类，`static` 指运行时实际调用的类，也就是延迟静态绑定，常用在会被继承的工厂方法里。

## 11. 错误和异常

- PHP 7 起统一成一个层级：`Throwable` 下面有 `Error`（引擎错误，如 TypeError、DivisionByZeroError）和 `Exception`（业务上可以预期的问题）。
- 老式错误（warning、notice）不能用 catch 捕获，可以用 `set_error_handler` 转成 `ErrorException`。
- 实践中：用自定义异常表达业务含义（`ContactNotFoundException` 对应 404，`ValidationException` 对应 422）；在边界（控制器或全局 handler）集中捕获；`finally` 里做清理；生产环境设 `display_errors=Off`，错误写进日志。
- PDO 设成 `ERRMODE_EXCEPTION`，出错时直接抛异常，而不是返回 false。

**一句话：** PHP 7 起 `Error` 和 `Exception` 都实现 `Throwable`。业务情况用自定义异常，集中在一处处理；生产环境的错误写进日志，不显示在页面上。

## 12. PHP-FPM 和 OPcache

- **PHP-FPM**（FastCGI Process Manager）：一个常驻的 PHP 进程池。Nginx 收到 `.php` 请求后，通过 FastCGI 交给 FPM 的 worker 执行。可以调整 `pm = dynamic/static/ondemand` 和 `pm.max_children`。和 Apache 的 mod_php 相比，Web 服务器和 PHP 分开运行，资源隔离更好。
- **OPcache**：把 PHP 脚本编译出来的 **opcode 缓存在共享内存里**，之后的请求不用再解析和编译，性能提升很明显。生产环境建议设 `opcache.validate_timestamps=0`（部署后重启 FPM 清缓存）；PHP 7.4 起还有 preloading。
- PHP 的每个请求都从头开始（share-nothing），所以 OPcache 很关键。DDEV 默认就是 nginx 加 php-fpm。

**一句话：** PHP-FPM 是在 Nginx 后面通过 FastCGI 执行 PHP 的进程池；OPcache 把编译好的字节码留在内存里，每次请求就不用重新编译脚本。
