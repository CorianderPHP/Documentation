import { initMobileNavigationDrawers } from '../../shared/MobileNavigationDrawer';

initMobileNavigationDrawers();

document.querySelectorAll<HTMLElement>('[data-scroll-memory]').forEach((element) => {
  const key = `scroll:${element.dataset.scrollMemory ?? ''}`;
  const storedTop = window.sessionStorage.getItem(key);

  if (storedTop !== null) {
    element.scrollTop = Number.parseInt(storedTop, 10) || 0;
  }

  element.addEventListener('scroll', () => {
    window.sessionStorage.setItem(key, String(element.scrollTop));
  }, { passive: true });
});

type Preset = {
  method: string;
  url: string;
  body: string;
};

const presets: Record<string, Preset> = {
  list: {
    method: 'GET',
    url: '/api/playground/shelter/animals?species=cat&status=available',
    body: '',
  },
  show: {
    method: 'GET',
    url: '/api/playground/shelter/animals/1',
    body: '',
  },
  create: {
    method: 'POST',
    url: '/api/playground/shelter/animals',
    body: JSON.stringify({
      name: 'Luna',
      species: 'cat',
      shelter_id: 1,
      age_months: 10,
      status: 'available',
      description: 'Playful young cat ready for adoption.',
    }, null, 2),
  },
  update: {
    method: 'PATCH',
    url: '/api/playground/shelter/animals/1',
    body: JSON.stringify({
      status: 'reserved',
      description: 'Updated from the safe playground.',
    }, null, 2),
  },
  delete: {
    method: 'DELETE',
    url: '/api/playground/shelter/animals/1',
    body: '',
  },
  species: {
    method: 'GET',
    url: '/api/playground/shelter/species',
    body: '',
  },
  shelters: {
    method: 'GET',
    url: '/api/playground/shelter/shelters',
    body: '',
  },
};

const playground = document.querySelector<HTMLElement>('[data-api-playground]');

if (playground) {
  const preset = playground.querySelector<HTMLSelectElement>('[data-api-preset]');
  const method = playground.querySelector<HTMLInputElement>('[data-api-method]');
  const url = playground.querySelector<HTMLInputElement>('[data-api-url]');
  const body = playground.querySelector<HTMLTextAreaElement>('[data-api-body]');
  const send = playground.querySelector<HTMLButtonElement>('[data-api-send]');
  const status = playground.querySelector<HTMLElement>('[data-api-status]');
  const response = playground.querySelector<HTMLElement>('[data-api-response]');

  const applyPreset = () => {
    const selected = presets[preset?.value ?? 'list'] ?? presets.list;
    if (method) method.value = selected.method;
    if (url) url.value = selected.url;
    if (body) body.value = selected.body;
  };

  const setResponse = (label: string, payload: unknown) => {
    if (status) status.textContent = label;
    if (response) response.textContent = typeof payload === 'string' ? payload : JSON.stringify(payload, null, 2);
  };

  preset?.addEventListener('change', applyPreset);
  applyPreset();

  send?.addEventListener('click', async () => {
    const requestMethod = method?.value.toUpperCase() || 'GET';
    const requestUrl = url?.value || '/api/playground/shelter/animals';
    const rawBody = body?.value.trim() ?? '';

    send.disabled = true;
    send.classList.add('opacity-70');
    setResponse('Status: sending', { message: 'Sending request...' });

    try {
      const init: RequestInit = {
        method: requestMethod,
        headers: { Accept: 'application/json' },
      };

      if (!['GET', 'HEAD', 'DELETE'].includes(requestMethod) && rawBody !== '') {
        init.headers = { ...init.headers, 'Content-Type': 'application/json' };
        init.body = rawBody;
      }

      const result = await fetch(requestUrl, init);
      const text = await result.text();
      let payload: unknown = text;

      try {
        payload = JSON.parse(text);
      } catch {
        payload = text;
      }

      setResponse(`Status: ${result.status} ${result.statusText}`, payload);
    } catch (error) {
      setResponse('Status: request failed', {
        error: error instanceof Error ? error.message : 'Request failed.',
      });
    } finally {
      send.disabled = false;
      send.classList.remove('opacity-70');
    }
  });
}
