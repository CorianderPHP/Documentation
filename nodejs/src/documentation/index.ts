import { initMobileNavigationDrawers } from '../shared/MobileNavigationDrawer';

initMobileNavigationDrawers();

document.querySelectorAll<HTMLElement>('[data-scroll-memory]').forEach((element) => {
  const memoryKey = element.dataset.scrollMemory?.trim();
  if (!memoryKey) {
    return;
  }

  const storageKey = `scroll:${memoryKey}`;
  const storedTop = window.sessionStorage.getItem(storageKey);
  if (storedTop !== null) {
    element.scrollTop = Number.parseInt(storedTop, 10) || 0;
  }

  element.addEventListener('scroll', () => {
    window.sessionStorage.setItem(storageKey, String(element.scrollTop));
  }, { passive: true });
});

const searchInput = document.querySelector<HTMLInputElement>('#documentation-search');
const resultContainer = document.querySelector<HTMLElement>('#documentation-search-results');
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

    fetch(`/api/documentation/search?q=${encodeURIComponent(query)}&scope=${encodeURIComponent(scope)}`, { signal: controller.signal })
      .then((response) => response.ok ? response.json() : null)
      .then((payload: { results?: Array<{ title: string; slug: string; section: string; excerpt: string }> } | null) => {
        if (!payload?.results) {
          return;
        }

        resultContainer.innerHTML = payload.results.length === 0
          ? '<p class="py-5 text-black/70 dark:text-white/70">No documentation pages matched this search scope.</p>'
          : payload.results.map((result) => `
              <a href="/documentation/${escapeHtml(result.slug)}" class="block py-5">
                <div class="flex flex-wrap items-center gap-2">
                  <h2 class="font-concert-one text-2xl text-dark-green dark:text-mint">${escapeHtml(result.title)}</h2>
                  <span class="rounded-full border border-dark-green/15 px-2 py-0.5 text-xs font-semibold text-black/55 dark:border-mint/20 dark:text-white/55">${escapeHtml(result.section)}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-black/65 dark:text-white/65">${escapeHtml(result.excerpt)}</p>
              </a>
            `).join('');
        window.CorianderCodeHighlighter?.highlightAll();
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
