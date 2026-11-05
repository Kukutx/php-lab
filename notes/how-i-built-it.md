# 我是怎么做的（How I built and checked php-lab）

这份笔记讲方法，不讲每一行代码。面试时被问 "你怎么保证质量 / 怎么排查问题" 可以直接用。

## 1. 先让项目"一条命令跑起来"

- 每个子项目都用 **SQLite**，不需要装 MySQL，也没有任何密码要放进仓库。
- `demo/setup.sh` 从零装好四个站（composer install、建库、导入演示数据），`demo/start.sh` 用 PHP 内置服务器在 8001–8004 端口启动。
- `.devcontainer/` 让 GitHub Codespaces 自动执行这两个脚本，所以你点一下按钮就能在线看到。
- 为什么：一个项目如果要手动做十步才能跑起来，别人（包括面试官和未来的你）就不会去跑。**可重复的安装脚本就是最好的文档。**

## 2. 用"真实用户的操作"做检查，而不是只看代码

我用 **Playwright**（浏览器自动化）写了一个小脚本，像真人一样操作每个站：

1. 打开首页、列表页，点筛选和分页。
2. 用 admin/admin 登录，新建、编辑、删除一条内容。
3. 把窗口改成手机宽度 390px，再看一遍。
4. 全程记录：所有 HTTP 状态码 ≥ 400 的请求、浏览器控制台报错、页面是否横向溢出（`scrollWidth > 390`），并截图。

这次找到并修好的问题：

| 站点 | 问题 | 原因 | 修法 |
|---|---|---|---|
| pdo-crud | 手机上表格超出屏幕 | 表格列太多 | `.table-responsive` 包一层，手机上隐藏 Phone 列，按钮不换行 |
| laravel | 新建时日期留空，通知"消失"了 | 留空 = 草稿，按日期倒序排序时 NULL 排在最后一页；草稿还会出现在公开列表 | 新建表单默认填"现在"；列表默认只显示已发布，加 "Drafts & scheduled / All" 筛选；补了一个测试 |
| laravel | 手机上导航栏副标题被截断 | 空间不够 | 小屏幕隐藏副标题（`d-none d-sm-inline`） |
| drupal10 | 后台页面上汉堡菜单图标 404 | 模板里用了相对路径，在 `/user/1` 下会变成 `/user/themes/...` | 统一用 preprocess 里生成的绝对路径 `{{ sprites }}` |
| drupal10 | 没有登录入口 | 头部没放账户链接 | 按 "modello Comuni" 在 slim header 放 "Accedi all'area personale"，登录后变成 "Area personale / Esci" |
| drupal10 | 菜单顺序 Avvisi 在 Home 前面；筛选显示英文 "- Any -" | 两个菜单项 weight 都是 0；Views 默认文字 | 调 weight 并 `drush cex` 写回 config/sync；用 `hook_form_views_exposed_form_alter` 改文字 |
| wordpress | 没有导航菜单、没有登录入口 | 演示脚本没建菜单 | 演示脚本用 `wp_create_nav_menu` 建 Home / Avvisi / Servizi / Accedi，并放到 `primary` 位置 |
| wordpress | 页脚是英文默认小工具，还有 "Hello world!" | WordPress 安装自带的示例内容 | 删除示例文章，页脚换成 "Contatti" 区块 |
| wordpress | 没装 Elementor，自定义小工具看不到 | 之前没装 | `setup.sh` 里 `wp plugin install elementor --activate`（联网时），顺便装意大利语包 |

## 3. 修完后的固定流程

每次改完都按这个顺序做，CI 也做一样的事：

```bash
# 自动化测试
cd pdo-crud && composer test        # PHPUnit
cd laravel  && php artisan test     # Laravel feature + unit tests
# 代码规范 / 静态分析
composer stan && composer cs        # PHPStan level 8 + PSR-12 (pdo-crud)
vendor/bin/pint --test              # Laravel
vendor/bin/phpcs                    # Drupal coder / WordPress WPCS
# 再跑一次 Playwright 检查，看截图
```

- Drupal 的配置改动（比如菜单 weight）一定要进 `config/sync`，否则重新安装就丢了。在 UI 里改完要 `drush cex`，别人拉代码后 `drush cim`。
- WordPress 的数据（菜单、小工具）不在代码里，所以写进 `scripts/demo-content.php`，保证每次安装结果一样。

## 4. 一些习惯

- **先复现，再修**：先用脚本证明问题存在，修完再用同一个脚本证明它没了。
- **修根因**：Laravel 那个问题，表面是"通知不见了"，根因是"草稿和排序"，所以不是只改排序，而是把"草稿/已发布"的概念做完整。
- **小步提交**：每个提交只做一件事，提交信息说清楚 why。
- **秘密永远不进仓库**：`.env`、`wp-config.php`、`settings.local.php` 都在 `.gitignore`，gitleaks 在 CI 和 pre-commit 里检查。
