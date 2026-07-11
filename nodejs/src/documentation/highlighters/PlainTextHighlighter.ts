import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class PlainTextHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    return escapeHtml(code);
  }
}
