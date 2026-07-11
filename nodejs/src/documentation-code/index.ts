import { CodeHighlighter } from '../documentation/CodeHighlighter';

const highlighter = new CodeHighlighter();

const highlightAll = (): void => {
  highlighter.highlightAllCode();
};

window.CorianderCodeHighlighter = { highlightAll };

highlightAll();
