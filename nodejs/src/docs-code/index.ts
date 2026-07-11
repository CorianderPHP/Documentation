import { CodeHighlighter } from '../docs/CodeHighlighter';

const highlighter = new CodeHighlighter();

const highlightAll = (): void => {
  highlighter.highlightAllCode();
};

window.CorianderCodeHighlighter = { highlightAll };

highlightAll();
