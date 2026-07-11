import { CodeHighlighter } from './CodeHighlighter';

new CodeHighlighter(true);

const searchInput = document.querySelector<HTMLInputElement>('#docs-search');
const resultContainer = document.querySelector<HTMLElement>('#docs-search-results');
const scopeInput = document.querySelector<HTMLInputElement>('input[name="scope"]');

if (searchInput && resultContainer) {
  let controller: AbortController | null = null;

  const runSearch = () => {
    const query = searchInput.value.trim();
    if (query.length < 2) {
      return;
    }

    controller?.abort();
    controller = new AbortController();
    const scope = scopeInput?.value ?? 'all';

    fetch(`/api/docs/search?q=${encodeURIComponent(query)}&scope=${encodeURIComponent(scope)}`, { signal: controller.signal })
      .then((response) => response.ok ? response.json() : null)
      .then((payload: { results?: Array<{ title: string; slug: string; section: string; excerpt: string }> } | null) => {
        if (!payload?.results) {
          return;
        }

        resultContainer.innerHTML = payload.results.length === 0
          ? '<p class="py-5 text-black/70 dark:text-white/70">No documentation pages matched this search scope.</p>'
          : payload.results.map((result) => `
              <a href="/docs/${escapeHtml(result.slug)}" class="block py-5">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="font-concert-one text-2xl text-dark-green dark:text-peach">${escapeHtml(result.title)}</h2>
                  <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold text-black/55 dark:border-peach/20 dark:text-white/55">${escapeHtml(result.section)}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65">${escapeHtml(result.excerpt)}</p>
              </a>
            `).join('');
        new CodeHighlighter(true);
      })
      .catch((error: unknown) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }
      });
  };

  searchInput.addEventListener('input', runSearch);
}

function escapeHtml(value: string): string {
  const span = document.createElement('span');
  span.textContent = value;
  return span.innerHTML;
}
