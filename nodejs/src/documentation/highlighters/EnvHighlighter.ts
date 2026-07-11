import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class EnvHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    return escapeHtml(code).replace(
      /^([A-Z0-9_]+)(=)(.*)$/gm,
      '<span class="text-cyan-500">$1</span>$2<span class="text-orange-400">$3</span>',
    );
  }
}
