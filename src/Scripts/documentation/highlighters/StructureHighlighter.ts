import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class StructureHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    return escapeHtml(code)
      .split('\n')
      .map((line) => this.highlightLine(line))
      .join('\n');
  }

  private highlightLine(line: string): string {
    const match = line.match(/^(\s*)(.+)$/);
    if (!match) {
      return line;
    }

    const [, indent, entry] = match;
    const isDirectory = entry.endsWith('/');
    const isPhp = entry.endsWith('.php');
    const isMarkdown = entry.endsWith('.md');
    const isAsset = /\.(ts|js|css|sql|sqlite|yml|env|json|zip)$/i.test(entry);
    const parts = entry.split('/');
    const label = parts.pop() ?? entry;
    const prefix = parts.length > 0 ? `${parts.join('/')}/` : '';
    const labelClass = isDirectory
      ? 'text-sky-500'
      : isPhp
        ? 'text-cyan-500'
        : isMarkdown
          ? 'text-orange-400'
          : isAsset
            ? 'text-violet-500'
            : 'text-black/80 dark:text-white/80';

    return `${indent}<span class="text-black/45 dark:text-white/45">${prefix}</span><span class="${labelClass}">${label}</span>`;
  }
}
