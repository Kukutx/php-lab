# Elementor basics

Elementor is a page builder plugin: editors build pages visually by dragging
**widgets** (heading, image, button, …) into **sections/containers**. The
layout is saved as JSON in post meta (`_elementor_data`), and Elementor renders
it to HTML + generated CSS.

## Install and set up

```bash
ddev wp plugin install elementor --activate
ddev wp theme install hello-elementor        # optional: minimal theme made for Elementor
```

Elementor works with any theme. With our child theme, pick the page template
**"Elementor Full Width"** or **"Elementor Canvas"** (no header/footer) in the
page settings.

## Key concepts

| Concept | What it is |
|---|---|
| Container (Flexbox/Grid) | Layout box, replaces the old Section/Column model |
| Widget | A content element; plugins can add their own |
| Global colors / fonts | Site Settings → design tokens reused everywhere (set them to the brand palette, e.g. `#0066CC`) |
| Template / Theme Builder (Pro) | Reusable headers, footers, single and archive templates |
| Responsive mode | Per-breakpoint values (desktop / tablet / mobile) |
| Dynamic tags (Pro) | Fill a widget from post fields, ACF, etc. |

## Our custom widget

`comune-avvisi` registers an **"Avvisi"** widget when Elementor is active
(`includes/class-elementor-widget.php`):

- hook: `elementor/widgets/register` — the file is only loaded if Elementor exists;
- controls: number of notices and category, shown in the Elementor panel;
- `render()` reuses the same query and template as the `[avvisi]` shortcode.

Without the custom widget an editor can also use Elementor's **Shortcode**
widget with `[avvisi numero="3"]`.

> Not tested in the cloud demo: Elementor is downloaded from wordpress.org,
> which the sandbox cannot reach. Run the install command above in DDEV.

## Good practices

- Define global colors/fonts first; avoid per-widget styling.
- Keep business logic in plugins (CPTs, shortcodes, widgets), not in pages.
- Elementor adds CSS/JS weight: enable "Improved asset loading" and check Lighthouse.
- Accessibility: use real heading levels, alt text on images, and check contrast.
