import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class JsonHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    const parts = code.split(/("(?:\\.|[^"])*"\s*:)|("(?:\\.|[^"])*")|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g);

    return parts.map((part) => this.highlightPart(part ?? '')).join('');
  }

  private highlightPart(part: string): string {
    if (part === '') {
      return '';
    }

    if (/^"(?:\\.|[^"])*"\s*:$/.test(part)) {
      return `<span class="text-cyan-500">${escapeHtml(part.slice(0, part.lastIndexOf(':')))}</span>:`;
    }

    if (part.startsWith('"')) {
      return `<span class="text-orange-400">${escapeHtml(part)}</span>`;
    }

    if (part === 'true' || part === 'false' || part === 'null') {
      return `<span class="text-violet-500">${part}</span>`;
    }

    if (/^-?\d/.test(part)) {
      return `<span class="text-violet-500">${part}</span>`;
    }

    return escapeHtml(part);
  }
}
