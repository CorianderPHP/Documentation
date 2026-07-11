import { EnvHighlighter } from './highlighters/EnvHighlighter';
import { HtmlHighlighter } from './highlighters/HtmlHighlighter';
import { HttpHighlighter } from './highlighters/HttpHighlighter';
import { ILanguageHighlighter } from './highlighters/ILanguageHighlighter';
import { JsonHighlighter } from './highlighters/JsonHighlighter';
import { PlainTextHighlighter } from './highlighters/PlainTextHighlighter';
import { ScriptHighlighter } from './highlighters/ScriptHighlighter';
import { ShellHighlighter } from './highlighters/ShellHighlighter';
import { SqlHighlighter } from './highlighters/SqlHighlighter';
import { StructureHighlighter } from './highlighters/StructureHighlighter';

export class CodeHighlighter {
  private readonly highlighters: Record<string, ILanguageHighlighter>;
  private readonly fallback = new PlainTextHighlighter();
  private readonly copyDisabledLanguages = new Set(['structure']);

  public constructor(autoInit: boolean = false) {
    const script = new ScriptHighlighter();
    const shell = new ShellHighlighter();

    this.highlighters = {
      bash: shell,
      env: new EnvHighlighter(),
      html: new HtmlHighlighter(),
      http: new HttpHighlighter(),
      js: script,
      javascript: script,
      json: new JsonHighlighter(),
      php: script,
      powershell: shell,
      sh: shell,
      shell,
      sql: new SqlHighlighter(),
      structure: new StructureHighlighter(),
      text: this.fallback,
      ts: script,
      tsx: script,
      txt: this.fallback,
      typescript: script,
    };

    if (autoInit) {
      this.highlightAllCode();
    }
  }

  public highlightAllCode(): void {
    document.querySelectorAll<HTMLPreElement>('pre[data-language]').forEach((block) => {
      const code = block.querySelector<HTMLElement>('code');
      const language = block.dataset.language?.toLowerCase() ?? '';
      const raw = code?.textContent ?? '';

      if (!code || raw === '') {
        return;
      }

      code.innerHTML = (this.highlighters[language] ?? this.fallback).highlight(raw);
      block.dataset.highlighted = 'true';
      this.addCopyButton(block, code, language);
    });
  }

  private addCopyButton(block: HTMLPreElement, code: HTMLElement, language: string): void {
    if (block.dataset.copyEnabled === 'true' || this.copyDisabledLanguages.has(language)) {
      return;
    }

    block.dataset.copyEnabled = 'true';
    block.classList.add('relative');

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'absolute right-2 top-2 rounded-md border border-dark-green/20 bg-true-white/95 px-2 py-1 text-xs font-semibold text-dark-green shadow-sm transition hover:border-dark-green hover:bg-dark-green hover:text-true-white focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-peach/30 dark:bg-true-black/95 dark:text-peach dark:hover:border-peach dark:hover:bg-peach dark:hover:text-black dark:focus:ring-peach/20';
    button.textContent = 'Copy';

    button.addEventListener('click', async () => {
      const copied = await this.copyText(code.textContent ?? '');
      button.textContent = copied ? 'Copied' : 'Failed';
      window.setTimeout(() => {
        button.textContent = 'Copy';
      }, 1400);
    });

    block.append(button);
  }

  private async copyText(value: string): Promise<boolean> {
    if (navigator.clipboard && window.isSecureContext) {
      try {
        await navigator.clipboard.writeText(value);
        return true;
      } catch {
        return this.copyWithFallback(value);
      }
    }

    return this.copyWithFallback(value);
  }

  private copyWithFallback(value: string): boolean {
    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.append(textarea);
    textarea.select();

    try {
      return document.execCommand('copy');
    } finally {
      textarea.remove();
    }
  }
}
