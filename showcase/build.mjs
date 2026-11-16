// Builds the static showcase into dist/: copies public/ and renders the
// Markdown notes from ../notes into HTML pages that share the site layout.
import { cpSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { marked } from 'marked';

const here = dirname(fileURLToPath(import.meta.url));
const dist = join(here, 'dist');
const repo = 'https://github.com/Kukutx/php-lab/blob/main';

const notes = [
  { slug: 'interview-cheatsheet', title: 'PHP interview cheat sheet', lang: 'zh' },
  { slug: 'how-i-built-it', title: 'How php-lab was built and tested', lang: 'zh' },
];

const escapeHtml = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);

// Relative links in the notes point into the repository: send them to GitHub.
marked.use({
  walkTokens(token) {
    if (token.type === 'link' && !/^(https?:|mailto:|#)/.test(token.href)) {
      const path = new URL(token.href, 'https://x/notes/').pathname.replace(/^\//, '');
      token.href = `${repo}/${path}`;
    }
  },
});

const page = ({ title, lang, body, source }) => `<!doctype html>
<html lang="${lang}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${escapeHtml(title)} · php-lab</title>
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="site">
  <div class="wrap">
    <a class="brand" href="/">php-lab</a>
    <nav>
      <a href="/">Projects</a>
      <a href="/notes/interview-cheatsheet/">Cheat sheet</a>
      <a href="/notes/how-i-built-it/">How it was built</a>
      <a href="https://github.com/Kukutx/php-lab">GitHub</a>
    </nav>
  </div>
</header>
<main class="wrap">
  <article class="doc">
${body}
  </article>
</main>
<footer class="site">
  <div class="wrap">Source: <a href="${source}">${escapeHtml(source.replace('https://', ''))}</a></div>
</footer>
</body>
</html>
`;

rmSync(dist, { recursive: true, force: true });
cpSync(join(here, 'public'), dist, { recursive: true });

for (const note of notes) {
  const md = readFileSync(join(here, '..', 'notes', `${note.slug}.md`), 'utf8');
  const out = join(dist, 'notes', note.slug);
  mkdirSync(out, { recursive: true });
  writeFileSync(
    join(out, 'index.html'),
    page({ ...note, body: marked.parse(md), source: `${repo}/notes/${note.slug}.md` }),
  );
}

console.log(`Built ${dist}`);
