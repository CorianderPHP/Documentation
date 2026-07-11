import { ILanguageHighlighter } from './ILanguageHighlighter';
import { ScriptHighlighter } from './ScriptHighlighter';
import { escapeHtml } from './escapeHtml';

export class HtmlHighlighter implements ILanguageHighlighter {
  private readonly phpHighlighter = new ScriptHighlighter();

  public highlight(code: string): string {
    let output = '';
    let index = 0;

    while (index < code.length) {
      if (code.startsWith('<!--', index)) {
        const end = code.indexOf('-->', index + 4);
        const nextIndex = end === -1 ? code.length : end + 3;
        output += this.wrap(code.slice(index, nextIndex), 'text-emerald-600');
        index = nextIndex;
        continue;
      }

      if (code.startsWith('<?', index)) {
        const end = code.indexOf('?>', index + 2);
        const nextIndex = end === -1 ? code.length : end + 2;
        output += this.phpHighlighter.highlight(code.slice(index, nextIndex));
        index = nextIndex;
        continue;
      }

      if (code[index] === '<') {
        const end = this.findTagEnd(code, index);
        output += this.highlightTag(code.slice(index, end));
        index = end;
        continue;
      }

      output += escapeHtml(code[index] ?? '');
      index += 1;
    }

    return output;
  }

  private highlightTag(tag: string): string {
    if (tag.startsWith('</')) {
      const match = tag.match(/^<\/\s*([A-Za-z][A-Za-z0-9:-]*)([\s\S]*?)>$/);
      if (!match) {
        return escapeHtml(tag);
      }

      return `<span class="text-sky-500">&lt;/${match[1]}</span>${escapeHtml(match[2] ?? '')}<span class="text-sky-500">&gt;</span>`;
    }

    if (tag.startsWith('<!')) {
      return this.wrap(tag, 'text-sky-500');
    }

    const match = tag.match(/^<([A-Za-z][A-Za-z0-9:-]*)/);
    if (!match) {
      return escapeHtml(tag);
    }

    let output = `<span class="text-sky-500">&lt;${match[1]}</span>`;
    let index = match[0].length;

    while (index < tag.length) {
      if (tag.startsWith('/>', index)) {
        output += '<span class="text-sky-500">/&gt;</span>';
        index += 2;
        continue;
      }

      if (tag[index] === '>') {
        output += '<span class="text-sky-500">&gt;</span>';
        index += 1;
        continue;
      }

      if (/\s/.test(tag[index] ?? '')) {
        output += escapeHtml(tag[index] ?? '');
        index += 1;
        continue;
      }

      const attr = tag.slice(index).match(/^[^\s=/>]+/);
      if (!attr) {
        output += escapeHtml(tag[index] ?? '');
        index += 1;
        continue;
      }

      output += this.wrap(attr[0], 'text-cyan-500');
      index += attr[0].length;

      while (index < tag.length && /\s/.test(tag[index] ?? '')) {
        output += escapeHtml(tag[index] ?? '');
        index += 1;
      }

      if (tag[index] !== '=') {
        continue;
      }

      output += '=';
      index += 1;

      while (index < tag.length && /\s/.test(tag[index] ?? '')) {
        output += escapeHtml(tag[index] ?? '');
        index += 1;
      }

      const quote = tag[index] ?? '';
      if (quote === '"' || quote === "'") {
        const valueEnd = this.findStringEnd(tag, index, quote);
        output += this.highlightAttributeValue(tag.slice(index, valueEnd));
        index = valueEnd;
        continue;
      }

      const value = tag.slice(index).match(/^[^\s>]+/);
      if (value) {
        output += this.wrap(value[0], 'text-orange-400');
        index += value[0].length;
      }
    }

    return output;
  }

  private highlightAttributeValue(value: string): string {
    if (!value.includes('<?')) {
      return this.wrap(value, 'text-orange-400');
    }

    const quote = value[0] ?? '';
    const inner = value.slice(1, -1);
    const closingQuote = value[value.length - 1] ?? '';

    return [
      this.wrap(quote, 'text-orange-400'),
      this.highlightTextWithPhp(inner, 'text-orange-400'),
      this.wrap(closingQuote, 'text-orange-400'),
    ].join('');
  }

  private highlightTextWithPhp(value: string, textClass: string): string {
    let output = '';
    let index = 0;

    while (index < value.length) {
      if (value.startsWith('<?', index)) {
        const end = value.indexOf('?>', index + 2);
        const nextIndex = end === -1 ? value.length : end + 2;
        output += this.phpHighlighter.highlight(value.slice(index, nextIndex));
        index = nextIndex;
        continue;
      }

      const nextPhp = value.indexOf('<?', index);
      const nextIndex = nextPhp === -1 ? value.length : nextPhp;
      output += this.wrap(value.slice(index, nextIndex), textClass);
      index = nextIndex;
    }

    return output;
  }

  private findTagEnd(code: string, start: number): number {
    let quote: string | null = null;
    let index = start + 1;

    while (index < code.length) {
      const char = code[index] ?? '';

      if (quote !== null) {
        if (char === quote) {
          quote = null;
        }

        index += 1;
        continue;
      }

      if (char === '"' || char === "'") {
        quote = char;
        index += 1;
        continue;
      }

      if (char === '>') {
        return index + 1;
      }

      index += 1;
    }

    return code.length;
  }

  private findStringEnd(code: string, start: number, quote: string): number {
    let index = start + 1;

    while (index < code.length) {
      if (code[index] === quote) {
        return index + 1;
      }

      index += 1;
    }

    return code.length;
  }

  private wrap(value: string, classes: string): string {
    return `<span class="${classes}">${escapeHtml(value)}</span>`;
  }
}
