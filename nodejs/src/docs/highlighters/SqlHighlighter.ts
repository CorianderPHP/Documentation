import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class SqlHighlighter implements ILanguageHighlighter {
  private readonly keywords = new Set([
    'add',
    'and',
    'as',
    'asc',
    'autoincrement',
    'by',
    'check',
    'constraint',
    'create',
    'current_timestamp',
    'default',
    'delete',
    'desc',
    'drop',
    'exists',
    'foreign',
    'from',
    'group',
    'if',
    'in',
    'index',
    'inner',
    'insert',
    'integer',
    'into',
    'is',
    'join',
    'key',
    'left',
    'like',
    'limit',
    'not',
    'null',
    'on',
    'or',
    'order',
    'primary',
    'references',
    'select',
    'set',
    'table',
    'text',
    'unique',
    'update',
    'values',
    'where',
  ]);

  public highlight(code: string): string {
    const escaped = escapeHtml(code);
    const parts = escaped.split(/(--.*|\/\*[\s\S]*?\*\/|"(?:\\.|[^"])*"|'(?:\\.|[^'])*'|:[A-Za-z_][A-Za-z0-9_]*|\b\d+(?:\.\d+)?\b|\b[A-Za-z_][A-Za-z0-9_]*\b)/g);

    return parts.map((part) => this.highlightPart(part)).join('');
  }

  private highlightPart(part: string): string {
    if (part === '') {
      return '';
    }

    if (part.startsWith('--') || part.startsWith('/*')) {
      return `<span class="text-emerald-600">${part}</span>`;
    }

    if (part.startsWith('"') || part.startsWith("'")) {
      return `<span class="text-orange-400">${part}</span>`;
    }

    if (part.startsWith(':')) {
      return `<span class="text-cyan-500">${part}</span>`;
    }

    if (/^\d/.test(part)) {
      return `<span class="text-violet-500">${part}</span>`;
    }

    if (this.keywords.has(part.toLowerCase())) {
      return `<span class="text-sky-500">${part}</span>`;
    }

    return part;
  }
}
