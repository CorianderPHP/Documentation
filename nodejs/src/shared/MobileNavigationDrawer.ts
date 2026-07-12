export function initMobileNavigationDrawers(): void {
  const openButtons = document.querySelectorAll<HTMLButtonElement>('[data-mobile-nav-open]');
  let activeDrawer: HTMLElement | null = null;
  let activeOpenButton: HTMLButtonElement | null = null;
  let previousOverflow = '';

  const closeDrawer = () => {
    if (!activeDrawer || !activeOpenButton) {
      return;
    }

    activeDrawer.classList.add('hidden');
    activeDrawer.setAttribute('aria-hidden', 'true');
    activeOpenButton.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = previousOverflow;
    activeOpenButton.focus();

    activeDrawer = null;
    activeOpenButton = null;
  };

  const openDrawer = (button: HTMLButtonElement, drawer: HTMLElement) => {
    activeDrawer = drawer;
    activeOpenButton = button;
    previousOverflow = document.body.style.overflow;

    drawer.classList.remove('hidden');
    drawer.setAttribute('aria-hidden', 'false');
    button.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';

    drawer.querySelector<HTMLElement>('[data-mobile-nav-close]')?.focus();
  };

  openButtons.forEach((button) => {
    const drawerId = button.dataset.mobileNavOpen;
    if (!drawerId) {
      return;
    }

    const drawer = document.getElementById(drawerId);
    if (!drawer) {
      return;
    }

    button.addEventListener('click', () => {
      openDrawer(button, drawer);
    });
  });

  document.querySelectorAll<HTMLElement>('[data-mobile-nav-drawer]').forEach((drawer) => {
    drawer.querySelectorAll<HTMLElement>('[data-mobile-nav-close]').forEach((closeButton) => {
      closeButton.addEventListener('click', closeDrawer);
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeDrawer();
    }
  });
}
