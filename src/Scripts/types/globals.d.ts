export {};

declare global {
  interface Window {
    CorianderCodeHighlighter?: {
      highlightAll: () => void;
    };
  }
}
