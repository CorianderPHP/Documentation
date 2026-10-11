import { ILanguageHighlighter } from './ILanguageHighlighter';
import { escapeHtml } from './escapeHtml';

export class ScriptHighlighter implements ILanguageHighlighter {
  private readonly keywords = new Set([
    'as',
    'catch',
    'class',
    'const',
    'declare',
    'default',
    'echo',
    'else',
    'elseif',
    'endforeach',
    'endif',
    'extends',
    'final',
    'finally',
    'foreach',
    'function',
    'if',
    'implements',
    'interface',
    'match',
    'namespace',
    'new',
    'parent',
    'private',
    'protected',
    'public',
    'readonly',
    'return',
    'self',
    'static',
    'throw',
    'trait',
    'try',
    'use',
    'while',
  ]);

  private readonly literals = new Set(['false', 'null', 'true']);
  private readonly primitiveTypes = new Set([
    'array',
    'bool',
    'callable',
    'float',
    'int',
    'iterable',
    'mixed',
    'never',
    'object',
    'string',
    'void',
  ]);

  public highlight(code: string): string {
    let output = '';
    let index = 0;

    while (index < code.length) {
      if (code.startsWith('<?php', index)) {
        output += this.wrap(code.slice(index, index + 5), 'text-fuchsia-500');
        index += 5;
        continue;
      }

      if (code.startsWith('<?=', index)) {
        output += this.wrap(code.slice(index, index + 3), 'text-fuchsia-500');
        index += 3;
        continue;
      }

      if (code.startsWith('?>', index)) {
        output += this.wrap(code.slice(index, index + 2), 'text-fuchsia-500');
        index += 2;
        continue;
      }

      if (code.startsWith('//', index) || code.startsWith('#', index)) {
        const end = this.findLineEnd(code, index);
        output += this.wrap(code.slice(index, end), 'text-emerald-600');
        index = end;
        continue;
      }

      if (code.startsWith('/*', index)) {
        const end = code.indexOf('*/', index + 2);
        const nextIndex = end === -1 ? code.length : end + 2;
        output += this.wrap(code.slice(index, nextIndex), 'text-emerald-600');
        index = nextIndex;
        continue;
      }

      const char = code[index] ?? '';

      if (char === '"' || char === "'" || char === '`') {
        const end = this.findStringEnd(code, index, char);
        output += this.wrap(code.slice(index, end), 'text-orange-400');
        index = end;
        continue;
      }

      if (char === '$') {
        const match = code.slice(index).match(/^\$[A-Za-z_][A-Za-z0-9_]*/);
        if (match) {
          output += this.wrap(match[0], 'text-cyan-500');
          index += match[0].length;
          continue;
        }
      }

      if (/\d/.test(char)) {
        const match = code.slice(index).match(/^\d+(?:\.\d+)?/);
        if (match) {
          output += this.wrap(match[0], 'text-violet-500');
          index += match[0].length;
          continue;
        }
      }

      if (/[A-Za-z_\\]/.test(char)) {
        const match = code.slice(index).match(/^(?:\\?[A-Za-z_][A-Za-z0-9_]*)+/);
        if (match) {
          output += this.highlightIdentifier(code, index, match[0]);
          index += match[0].length;
          continue;
        }
      }

      output += escapeHtml(char);
      index += 1;
    }

    return output;
  }

  private highlightIdentifier(code: string, index: number, identifier: string): string {
    const lower = identifier.toLowerCase();
    const previousIdentifier = this.previousIdentifier(code, index);
    const next = this.nextNonWhitespace(code, index + identifier.length);
    if (this.keywords.has(lower)) {
      return this.wrap(identifier, 'text-sky-500');
    }

    if (this.literals.has(lower)) {
      return this.wrap(identifier, 'text-violet-500');
    }

    if (this.primitiveTypes.has(lower)) {
      return this.wrap(identifier, 'text-amber-500');
    }

    if (/^[A-Z][A-Za-z0-9_]*$/.test(identifier) || /^[A-Z0-9_]+$/.test(identifier) || identifier.includes('\\')) {
      return this.wrap(identifier, 'text-amber-500');
    }

    if (previousIdentifier === 'function' && next === '(') {
      return this.wrap(identifier, 'text-blue-500');
    }

    if (next === '(') {
      return this.wrap(identifier, 'text-blue-500');
    }

    return escapeHtml(identifier);
  }

  private findLineEnd(code: string, start: number): number {
    const end = code.indexOf('\n', start);
    return end === -1 ? code.length : end;
  }

  private findStringEnd(code: string, start: number, quote: string): number {
    let index = start + 1;

    while (index < code.length) {
      if (code[index] === '\\') {
        index += 2;
        continue;
      }

      if (code[index] === quote) {
        return index + 1;
      }

      index += 1;
    }

    return code.length;
  }

  private previousIdentifier(code: string, index: number): string | null {
    const before = code.slice(0, index).trimEnd();
    const match = before.match(/([A-Za-z_][A-Za-z0-9_]*)$/);
    return match?.[1]?.toLowerCase() ?? null;
  }

  private nextNonWhitespace(code: string, index: number): string | null {
    const match = code.slice(index).match(/\S/);
    return match?.[0] ?? null;
  }

  private wrap(value: string, classes: string): string {
    return `<span class="${classes}">${escapeHtml(value)}</span>`;
  }
}
