# showcase — static portfolio page for php-lab

Cloudflare Pages cannot run PHP, so this folder builds a **static** site that
presents the four projects: descriptions, screenshots, links to the code and
the notes from [`../notes`](../notes) rendered as HTML.

```bash
cd showcase
npm install
npm run build      # writes dist/
npx serve dist     # optional local preview
```

- `public/` — hand-written `index.html`, `style.css` and screenshots
  (resized WebP copies of each project's `docs/screenshot-*.png`).
- `build.mjs` — copies `public/` to `dist/` and renders the Markdown notes.

## Deployment

[`.github/workflows/showcase.yml`](../.github/workflows/showcase.yml) builds the
site and deploys it to Cloudflare Pages (project `php-lab`) on every push to
`main` that touches `showcase/` or `notes/`, or when run manually.

It needs one repository secret, `CLOUDFLARE_API_TOKEN`: an account API token
with the **Cloudflare Pages: Edit** permission. The account ID is fixed in the
workflow. `setup-cloudflare.ps1` creates the secret with `gh` and starts the
first deployment.
