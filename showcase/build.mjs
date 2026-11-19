// Builds the static showcase into dist/: copies public/ (the home page in
// Italian, English and Chinese) and renders the Markdown notes from
// ../notes/<lang>/ into HTML pages that share the site layout.
import { cpSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Marked } from 'marked';

const here = dirname(fileURLToPath(import.meta.url));
const dist = join(here, 'dist');
const repo = 'https://github.com/Kukutx/php-lab/blob/main';

// Italian is the default language and lives at the site root.
const langs = {
  it: {
    prefix: '',
    label: 'IT',
    nav: { home: 'Progetti', cheatsheet: 'Appunti di PHP', built: 'Come è stato fatto' },
    source: 'Sorgente',
    titles: { 'php-cheatsheet': 'Appunti di PHP', 'how-i-built-it': 'Come è stato costruito e verificato php-lab' },
  },
  en: {
    prefix: '/en',
    label: 'EN',
    nav: { home: 'Projects', cheatsheet: 'PHP cheat sheet', built: 'How it was built' },
    source: 'Source',
    titles: { 'php-cheatsheet': 'PHP cheat sheet', 'how-i-built-it': 'How php-lab was built and checked' },
  },
  zh: {
    prefix: '/zh',
    label: '中文',
    nav: { home: '项目', cheatsheet: 'PHP 基础速记', built: '怎么做的' },
    source: '源文件',
    titles: { 'php-cheatsheet': 'PHP 基础速记', 'how-i-built-it': 'php-lab 是怎么做和怎么检查的' },
  },
};
const order = ['zh', 'en', 'it'];
const slugs = ['php-cheatsheet', 'how-i-built-it'];

const escapeHtml = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);

// One parser per language: relative links in the notes point into the
// repository, so they are sent to GitHub; links to the same note in another
// language are sent to that language's page on this site.
function parserFor(lang) {
  const md = new Marked();
  md.use({
    walkTokens(token) {
      if (token.type !== 'link' || /^(https?:|mailto:|#)/.test(token.href)) return;
      const other = /^\.\.\/(zh|en|it)\/([\w-]+)\.md$/.exec(token.href);
      if (other && slugs.includes(other[2])) {
        token.href = `${langs[other[1]].prefix}/notes/${other[2]}/`;
        return;
      }
      const path = new URL(token.href, `https://x/notes/${lang}/`).pathname.replace(/^\//, '');
      token.href = `${repo}/${path}`;
    },
  });
  return md;
}

const page = ({ lang, slug, title, body, source }) => {
  const l = langs[lang];
  const switcher = order
    .map((code) => (code === lang
      ? `<a href="${l.prefix}/notes/${slug}/" aria-current="page">${langs[code].label}</a>`
      : `<a href="${langs[code].prefix}/notes/${slug}/" lang="${code}" hreflang="${code}">${langs[code].label}</a>`))
    .join('');
  return `<!doctype html>
<html lang="${lang}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${escapeHtml(title)} · php-lab</title>
  <link rel="stylesheet" href="/style.css">
${order.map((code) => `  <link rel="alternate" hreflang="${code}" href="${langs[code].prefix}/notes/${slug}/">`).join('\n')}
</head>
<body>
<header class="site">
  <div class="wrap">
    <a class="brand" href="${l.prefix}/">php-lab</a>
    <nav>
      <a href="${l.prefix}/">${l.nav.home}</a>
      <a href="${l.prefix}/notes/php-cheatsheet/">${l.nav.cheatsheet}</a>
      <a href="${l.prefix}/notes/how-i-built-it/">${l.nav.built}</a>
      <a href="https://github.com/Kukutx/php-lab">GitHub</a>
      <span class="langs">${switcher}</span>
    </nav>
  </div>
</header>
<main class="wrap">
  <article class="doc">
${body}
  </article>
</main>
<footer class="site">
  <div class="wrap">${l.source}: <a href="${source}">${escapeHtml(source.replace('https://', ''))}</a></div>
</footer>
</body>
</html>
`;
};

rmSync(dist, { recursive: true, force: true });
cpSync(join(here, 'public'), dist, { recursive: true });

for (const lang of order) {
  const parser = parserFor(lang);
  for (const slug of slugs) {
    // The language switch line at the top of each note is replaced by the one in the header.
    const md = readFileSync(join(here, '..', 'notes', lang, `${slug}.md`), 'utf8')
      .replace(/^(# .*\n)\n.*·.*·.*\n/, '$1');
    const out = join(dist, langs[lang].prefix.replace(/^\//, ''), 'notes', slug);
    mkdirSync(out, { recursive: true });
    writeFileSync(
      join(out, 'index.html'),
      page({ lang, slug, title: langs[lang].titles[slug], body: parser.parse(md), source: `${repo}/notes/${lang}/${slug}.md` }),
    );
  }
}

console.log(`Built ${dist}`);
