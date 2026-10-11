import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class HttpHighlighter implements ILanguageHighlighter {
  public highlight(code: string): string {
    return escapeHtml(code)
      .replace(/\b(GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD)\b/g, '<span class="text-sky-500">$1</span>')
      .replace(/\s(\/[^\s]*)/g, ' <span class="text-cyan-500">$1</span>')
      .replace(/\b(HTTP\/\d(?:\.\d)?|20\d|30\d|40\d|50\d)\b/g, '<span class="text-violet-500">$1</span>');
  }
}
