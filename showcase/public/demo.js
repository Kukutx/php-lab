// Turns the paths in the live demo table into links to a running codespace.
// The codespace name comes from ?cs=<name>, from the form, or from the last visit.
// Shared by the three language versions of the home page.
(function () {
  var TEXT = {
    it: {
      before: 'Link attivi per il codespace ',
      after: '. ',
      reset: 'Rimuovi',
      invalid: 'Indirizzo non riconosciuto. Esempio: https://nome-del-codespace-8004.app.github.dev',
    },
    en: {
      before: 'Links now point to codespace ',
      after: '. ',
      reset: 'Remove',
      invalid: 'Address not recognised. Example: https://your-codespace-name-8004.app.github.dev',
    },
    zh: {
      before: '链接已指向 codespace ',
      after: '。',
      reset: '清除',
      invalid: '无法识别这个地址。示例：https://your-codespace-name-8004.app.github.dev',
    },
  };
  var t = TEXT[(document.documentElement.lang || 'it').slice(0, 2)] || TEXT.it;
  var KEY = 'php-lab-codespace';
  var form = document.getElementById('demo-form');
  var input = document.getElementById('demo-url');
  var status = document.getElementById('demo-status');
  if (!form || !input || !status) return;

  function parse(value) {
    value = (value || '').trim().toLowerCase();
    var m = /^(?:https?:\/\/)?([a-z0-9-]+?)(?:-\d{2,5})?\.app\.github\.dev(?:[\/?#]|$)/.exec(value);
    if (m) return m[1];
    return /^[a-z0-9][a-z0-9-]{2,}$/.test(value) ? value : '';
  }
  function load() { try { return parse(localStorage.getItem(KEY)); } catch (e) { return ''; } }
  function save(name) {
    try { name ? localStorage.setItem(KEY, name) : localStorage.removeItem(KEY); } catch (e) {}
  }
  function apply(name) {
    document.querySelectorAll('a[data-demo]').forEach(function (a) {
      if (!name) { a.removeAttribute('href'); return; }
      var parts = a.getAttribute('data-demo').split('/');
      var port = parts.shift();
      a.href = 'https://' + name + '-' + port + '.app.github.dev/' + parts.join('/');
      a.target = '_blank';
      a.rel = 'noopener';
    });
    status.textContent = '';
    if (!name) return;
    status.append(t.before, Object.assign(document.createElement('code'), { textContent: name }), t.after);
    var reset = document.createElement('button');
    reset.type = 'button';
    reset.className = 'linklike';
    reset.textContent = t.reset;
    reset.addEventListener('click', function () { save(''); apply(''); });
    status.append(reset);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var name = parse(input.value);
    if (!name) {
      status.textContent = t.invalid;
      return;
    }
    save(name);
    input.value = '';
    apply(name);
  });

  var fromUrl = parse(new URLSearchParams(location.search).get('cs'));
  if (fromUrl) save(fromUrl);
  apply(fromUrl || load());
})();
