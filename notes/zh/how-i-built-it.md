# php-lab 是怎么做和怎么检查的

**中文** · [English](../en/how-i-built-it.md) · [Italiano](../it/how-i-built-it.md)

这篇讲方法：怎么让项目容易跑起来，怎么检查质量，发现问题后怎么修。不逐行讲代码。

## 1. 先让每个项目一条命令就能跑起来

- 每个子项目都用 **SQLite**，不需要装 MySQL，仓库里也不用放任何密码。
- `demo/setup.sh` 从零装好四个站（composer install、建库、导入演示数据），`demo/start.sh` 用 PHP 内置服务器在 8001–8004 端口启动它们。
- `.devcontainer/` 让 GitHub Codespaces 自动执行这两个脚本，所以点一下按钮就能在线看到。
- 原因：一个项目如果要手动做十步才能跑起来，别人（包括以后的自己）就不会去跑。安装步骤写成脚本，说明和实际做法就不会对不上。

## 2. 按真实用户的操作来检查，不只看代码

我用 **Playwright**（浏览器自动化）写了一个小脚本，像真人一样操作每个站：

1. 打开首页和列表页，用筛选和分页。
2. 用 admin/admin 登录，新建、编辑、删除一条内容。
3. 把窗口改成 390px 的手机宽度，再走一遍。
4. 全程记录：状态码在 400 及以上的 HTTP 请求、浏览器控制台报错、页面有没有横向溢出（`scrollWidth > 390`），并截图。

这一轮找到并修好的问题：

| 站点 | 问题 | 原因 | 改法 |
| --- | --- | --- | --- |
| pdo-crud | 手机上表格超出屏幕 | 列太多 | 外面包一层 `.table-responsive`，手机上隐藏 Phone 列，按钮不换行 |
| laravel | 新建时日期留空，通知就"不见了" | 日期留空就是草稿；按日期倒序排时 NULL 排在最后一页；草稿还会出现在公开列表里 | 新建表单默认填"现在"；列表默认只显示已发布的，另加"Drafts & scheduled / All"筛选；补了一个测试 |
| laravel | 手机上导航栏的副标题被截断 | 空间不够 | 小屏幕上隐藏副标题（`d-none d-sm-inline`） |
| drupal10 | 后台页面上的汉堡菜单图标 404 | 模板里用了相对路径，在 `/user/1` 下变成了 `/user/themes/...` | 统一用 preprocess 里生成的绝对路径 `{{ sprites }}` |
| drupal10 | 没有登录入口 | 页头没放账户链接 | 按"modello Comuni"在 slim header 放上"Accedi all'area personale"，登录后变成"Area personale / Esci" |
| drupal10 | 菜单里 Avvisi 排在 Home 前面；筛选显示英文"- Any -" | 两个菜单项的 weight 都是 0；Views 的默认文字 | 调整 weight，并用 `drush cex` 写回 `config/sync`；用 `hook_form_views_exposed_form_alter` 改文字 |
| wordpress | 没有导航菜单，也没有登录入口 | 演示脚本没有建菜单 | 演示脚本用 `wp_create_nav_menu` 建 Home / Avvisi / Servizi / Accedi，并放到 `primary` 位置 |
| wordpress | 页脚是英文的默认小工具，还有"Hello world!" | WordPress 安装时自带的示例内容 | 删除示例文章，页脚换成"Contatti"区块 |
| wordpress | 没装 Elementor，看不到自定义小工具 | 之前没装 | `setup.sh` 里执行 `wp plugin install elementor --activate`（联网时），顺便装上意大利语包 |

## 3. 每次修改后的固定流程

每次改完都按这个顺序做，CI 里也做同样的事：

```bash
# 自动化测试
cd pdo-crud && composer test        # PHPUnit
cd laravel  && php artisan test     # Laravel 的功能测试和单元测试
# 代码规范和静态分析
composer stan && composer cs        # PHPStan level 8 + PSR-12（pdo-crud）
vendor/bin/pint --test              # Laravel
vendor/bin/phpcs                    # Drupal coder / WordPress WPCS
# 再跑一次 Playwright 检查，看截图
```

- Drupal 的配置改动（比如菜单的 weight）一定要进 `config/sync`，否则重新安装就丢了。在界面里改完要执行 `drush cex`，别人拉取代码后执行 `drush cim`。
- WordPress 的数据（菜单、小工具）不在代码里，所以写进 `scripts/demo-content.php`，保证每次安装的结果都一样。

## 4. 几个习惯

- **先复现，再修**：先用脚本证明问题存在，修完再用同一个脚本证明它没了。
- **修根因**：Laravel 那个问题，表面上是"通知不见了"，根因是草稿和排序，所以不只改了排序，而是把"草稿 / 已发布"这个概念做完整。
- **小步提交**：每个提交只做一件事，提交说明写清楚为什么改。
- **密钥不进仓库**：`.env`、`wp-config.php`、`settings.local.php` 都在 `.gitignore` 里；gitleaks 在 CI 和 pre-commit 钩子里检查每次提交。
