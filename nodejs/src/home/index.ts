const primaryLinks = document.querySelectorAll<HTMLAnchorElement>('a[href="/docs"], a[href="/start"], a[href="/guided-projects/forum"]');

primaryLinks.forEach((link) => {
  link.addEventListener('focus', () => {
    link.classList.add('outline', 'outline-2', 'outline-offset-2', 'outline-dark-green', 'dark:outline-peach');
  });

  link.addEventListener('blur', () => {
    link.classList.remove('outline', 'outline-2', 'outline-offset-2', 'outline-dark-green', 'dark:outline-peach');
  });
});
