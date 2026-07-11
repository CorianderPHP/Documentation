import { CodeHighlighter } from '../docs/CodeHighlighter';

new CodeHighlighter(true);

document.querySelectorAll<HTMLFormElement>('[data-demo-form]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const button = event.submitter instanceof HTMLButtonElement ? event.submitter : null;
    if (!button) {
      return;
    }

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
  });
});

const flash = document.querySelector<HTMLElement>('[data-demo-flash]');
if (flash) {
  flash.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}
