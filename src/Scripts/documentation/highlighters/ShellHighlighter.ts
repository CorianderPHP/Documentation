import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class ShellHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    return escapeHtml(code)
      .replace(/(^|\n)(\s*#.*)/g, '$1<span class="text-emerald-600">$2</span>')
      .replace(/\b(composer|php|npm|node|curl|git)\b/g, '<span class="text-sky-500">$1</span>')
      .replace(/\b(install|run|make:[a-z-]+|migrate)\b/g, '<span class="text-cyan-500">$1</span>');
  }
}
