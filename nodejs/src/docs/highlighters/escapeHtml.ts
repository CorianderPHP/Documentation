export function escapeHtml(value: string): string {
  const span = document.createElement('span');
  span.textContent = value;
  return span.innerHTML;
}
