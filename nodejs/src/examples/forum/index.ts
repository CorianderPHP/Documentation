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
