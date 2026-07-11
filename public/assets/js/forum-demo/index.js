"use strict";
(() => {
  var __defProp = Object.defineProperty;
  var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
  var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);

  // src/docs/highlighters/escapeHtml.ts
  function escapeHtml(value) {
    const span = document.createElement("span");
    span.textContent = value;
    return span.innerHTML;
  }

  // src/docs/highlighters/EnvHighlighter.ts
  var EnvHighlighter = class {
    highlight(code) {
      return escapeHtml(code).replace(
        /^([A-Z0-9_]+)(=)(.*)$/gm,
        '<span class="text-cyan-500">$1</span>$2<span class="text-orange-400">$3</span>'
      );
    }
  };

  // src/docs/highlighters/ScriptHighlighter.ts
  var ScriptHighlighter = class {
    constructor() {
      __publicField(this, "keywords", /* @__PURE__ */ new Set([
        "as",
        "catch",
        "class",
        "const",
        "declare",
        "default",
        "echo",
        "else",
        "elseif",
        "endforeach",
        "endif",
        "extends",
        "final",
        "finally",
        "foreach",
        "function",
        "if",
        "implements",
        "interface",
        "match",
        "namespace",
        "new",
        "parent",
        "private",
        "protected",
        "public",
        "readonly",
        "return",
        "self",
        "static",
        "throw",
        "trait",
        "try",
        "use",
        "while"
      ]));
      __publicField(this, "literals", /* @__PURE__ */ new Set(["false", "null", "true"]));
      __publicField(this, "primitiveTypes", /* @__PURE__ */ new Set([
        "array",
        "bool",
        "callable",
        "float",
        "int",
        "iterable",
        "mixed",
        "never",
        "object",
        "string",
        "void"
      ]));
    }
    highlight(code) {
      let output = "";
      let index = 0;
      while (index < code.length) {
        if (code.startsWith("<?php", index)) {
          output += this.wrap(code.slice(index, index + 5), "text-fuchsia-500");
          index += 5;
          continue;
        }
        if (code.startsWith("<?=", index)) {
          output += this.wrap(code.slice(index, index + 3), "text-fuchsia-500");
          index += 3;
          continue;
        }
        if (code.startsWith("?>", index)) {
          output += this.wrap(code.slice(index, index + 2), "text-fuchsia-500");
          index += 2;
          continue;
        }
        if (code.startsWith("//", index) || code.startsWith("#", index)) {
          const end = this.findLineEnd(code, index);
          output += this.wrap(code.slice(index, end), "text-emerald-600");
          index = end;
          continue;
        }
        if (code.startsWith("/*", index)) {
          const end = code.indexOf("*/", index + 2);
          const nextIndex = end === -1 ? code.length : end + 2;
          output += this.wrap(code.slice(index, nextIndex), "text-emerald-600");
          index = nextIndex;
          continue;
        }
        const char = code[index] ?? "";
        if (char === '"' || char === "'" || char === "`") {
          const end = this.findStringEnd(code, index, char);
          output += this.wrap(code.slice(index, end), "text-orange-400");
          index = end;
          continue;
        }
        if (char === "$") {
          const match = code.slice(index).match(/^\$[A-Za-z_][A-Za-z0-9_]*/);
          if (match) {
            output += this.wrap(match[0], "text-cyan-500");
            index += match[0].length;
            continue;
          }
        }
        if (/\d/.test(char)) {
          const match = code.slice(index).match(/^\d+(?:\.\d+)?/);
          if (match) {
            output += this.wrap(match[0], "text-violet-500");
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
    highlightIdentifier(code, index, identifier) {
      const lower = identifier.toLowerCase();
      const previousIdentifier = this.previousIdentifier(code, index);
      const next = this.nextNonWhitespace(code, index + identifier.length);
      if (this.keywords.has(lower)) {
        return this.wrap(identifier, "text-sky-500");
      }
      if (this.literals.has(lower)) {
        return this.wrap(identifier, "text-violet-500");
      }
      if (this.primitiveTypes.has(lower)) {
        return this.wrap(identifier, "text-amber-500");
      }
      if (/^[A-Z][A-Za-z0-9_]*$/.test(identifier) || /^[A-Z0-9_]+$/.test(identifier) || identifier.includes("\\")) {
        return this.wrap(identifier, "text-amber-500");
      }
      if (previousIdentifier === "function" && next === "(") {
        return this.wrap(identifier, "text-blue-500");
      }
      if (next === "(") {
        return this.wrap(identifier, "text-blue-500");
      }
      return escapeHtml(identifier);
    }
    findLineEnd(code, start) {
      const end = code.indexOf("\n", start);
      return end === -1 ? code.length : end;
    }
    findStringEnd(code, start, quote) {
      let index = start + 1;
      while (index < code.length) {
        if (code[index] === "\\") {
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
    previousIdentifier(code, index) {
      const before = code.slice(0, index).trimEnd();
      const match = before.match(/([A-Za-z_][A-Za-z0-9_]*)$/);
      return match?.[1]?.toLowerCase() ?? null;
    }
    nextNonWhitespace(code, index) {
      const match = code.slice(index).match(/\S/);
      return match?.[0] ?? null;
    }
    wrap(value, classes) {
      return `<span class="${classes}">${escapeHtml(value)}</span>`;
    }
  };

  // src/docs/highlighters/HtmlHighlighter.ts
  var HtmlHighlighter = class {
    constructor() {
      __publicField(this, "phpHighlighter", new ScriptHighlighter());
    }
    highlight(code) {
      let output = "";
      let index = 0;
      while (index < code.length) {
        if (code.startsWith("<!--", index)) {
          const end = code.indexOf("-->", index + 4);
          const nextIndex = end === -1 ? code.length : end + 3;
          output += this.wrap(code.slice(index, nextIndex), "text-emerald-600");
          index = nextIndex;
          continue;
        }
        if (code.startsWith("<?", index)) {
          const end = code.indexOf("?>", index + 2);
          const nextIndex = end === -1 ? code.length : end + 2;
          output += this.phpHighlighter.highlight(code.slice(index, nextIndex));
          index = nextIndex;
          continue;
        }
        if (code[index] === "<") {
          const end = this.findTagEnd(code, index);
          output += this.highlightTag(code.slice(index, end));
          index = end;
          continue;
        }
        output += escapeHtml(code[index] ?? "");
        index += 1;
      }
      return output;
    }
    highlightTag(tag) {
      if (tag.startsWith("</")) {
        const match2 = tag.match(/^<\/\s*([A-Za-z][A-Za-z0-9:-]*)([\s\S]*?)>$/);
        if (!match2) {
          return escapeHtml(tag);
        }
        return `<span class="text-sky-500">&lt;/${match2[1]}</span>${escapeHtml(match2[2] ?? "")}<span class="text-sky-500">&gt;</span>`;
      }
      if (tag.startsWith("<!")) {
        return this.wrap(tag, "text-sky-500");
      }
      const match = tag.match(/^<([A-Za-z][A-Za-z0-9:-]*)/);
      if (!match) {
        return escapeHtml(tag);
      }
      let output = `<span class="text-sky-500">&lt;${match[1]}</span>`;
      let index = match[0].length;
      while (index < tag.length) {
        if (tag.startsWith("/>", index)) {
          output += '<span class="text-sky-500">/&gt;</span>';
          index += 2;
          continue;
        }
        if (tag[index] === ">") {
          output += '<span class="text-sky-500">&gt;</span>';
          index += 1;
          continue;
        }
        if (/\s/.test(tag[index] ?? "")) {
          output += escapeHtml(tag[index] ?? "");
          index += 1;
          continue;
        }
        const attr = tag.slice(index).match(/^[^\s=/>]+/);
        if (!attr) {
          output += escapeHtml(tag[index] ?? "");
          index += 1;
          continue;
        }
        output += this.wrap(attr[0], "text-cyan-500");
        index += attr[0].length;
        while (index < tag.length && /\s/.test(tag[index] ?? "")) {
          output += escapeHtml(tag[index] ?? "");
          index += 1;
        }
        if (tag[index] !== "=") {
          continue;
        }
        output += "=";
        index += 1;
        while (index < tag.length && /\s/.test(tag[index] ?? "")) {
          output += escapeHtml(tag[index] ?? "");
          index += 1;
        }
        const quote = tag[index] ?? "";
        if (quote === '"' || quote === "'") {
          const valueEnd = this.findStringEnd(tag, index, quote);
          output += this.highlightAttributeValue(tag.slice(index, valueEnd));
          index = valueEnd;
          continue;
        }
        const value = tag.slice(index).match(/^[^\s>]+/);
        if (value) {
          output += this.wrap(value[0], "text-orange-400");
          index += value[0].length;
        }
      }
      return output;
    }
    highlightAttributeValue(value) {
      if (!value.includes("<?")) {
        return this.wrap(value, "text-orange-400");
      }
      const quote = value[0] ?? "";
      const inner = value.slice(1, -1);
      const closingQuote = value[value.length - 1] ?? "";
      return [
        this.wrap(quote, "text-orange-400"),
        this.highlightTextWithPhp(inner, "text-orange-400"),
        this.wrap(closingQuote, "text-orange-400")
      ].join("");
    }
    highlightTextWithPhp(value, textClass) {
      let output = "";
      let index = 0;
      while (index < value.length) {
        if (value.startsWith("<?", index)) {
          const end = value.indexOf("?>", index + 2);
          const nextIndex2 = end === -1 ? value.length : end + 2;
          output += this.phpHighlighter.highlight(value.slice(index, nextIndex2));
          index = nextIndex2;
          continue;
        }
        const nextPhp = value.indexOf("<?", index);
        const nextIndex = nextPhp === -1 ? value.length : nextPhp;
        output += this.wrap(value.slice(index, nextIndex), textClass);
        index = nextIndex;
      }
      return output;
    }
    findTagEnd(code, start) {
      let quote = null;
      let index = start + 1;
      while (index < code.length) {
        const char = code[index] ?? "";
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
        if (char === ">") {
          return index + 1;
        }
        index += 1;
      }
      return code.length;
    }
    findStringEnd(code, start, quote) {
      let index = start + 1;
      while (index < code.length) {
        if (code[index] === quote) {
          return index + 1;
        }
        index += 1;
      }
      return code.length;
    }
    wrap(value, classes) {
      return `<span class="${classes}">${escapeHtml(value)}</span>`;
    }
  };

  // src/docs/highlighters/HttpHighlighter.ts
  var HttpHighlighter = class {
    highlight(code) {
      return escapeHtml(code).replace(/\b(GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD)\b/g, '<span class="text-sky-500">$1</span>').replace(/\s(\/[^\s]*)/g, ' <span class="text-cyan-500">$1</span>').replace(/\b(HTTP\/\d(?:\.\d)?|20\d|30\d|40\d|50\d)\b/g, '<span class="text-violet-500">$1</span>');
    }
  };

  // src/docs/highlighters/JsonHighlighter.ts
  var JsonHighlighter = class {
    highlight(code) {
      const parts = code.split(/("(?:\\.|[^"])*"\s*:)|("(?:\\.|[^"])*")|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g);
      return parts.map((part) => this.highlightPart(part ?? "")).join("");
    }
    highlightPart(part) {
      if (part === "") {
        return "";
      }
      if (/^"(?:\\.|[^"])*"\s*:$/.test(part)) {
        return `<span class="text-cyan-500">${escapeHtml(part.slice(0, part.lastIndexOf(":")))}</span>:`;
      }
      if (part.startsWith('"')) {
        return `<span class="text-orange-400">${escapeHtml(part)}</span>`;
      }
      if (part === "true" || part === "false" || part === "null") {
        return `<span class="text-violet-500">${part}</span>`;
      }
      if (/^-?\d/.test(part)) {
        return `<span class="text-violet-500">${part}</span>`;
      }
      return escapeHtml(part);
    }
  };

  // src/docs/highlighters/PlainTextHighlighter.ts
  var PlainTextHighlighter = class {
    highlight(code) {
      return escapeHtml(code);
    }
  };

  // src/docs/highlighters/ShellHighlighter.ts
  var ShellHighlighter = class {
    highlight(code) {
      return escapeHtml(code).replace(/(^|\n)(\s*#.*)/g, '$1<span class="text-emerald-600">$2</span>').replace(/\b(composer|php|npm|node|curl|git)\b/g, '<span class="text-sky-500">$1</span>').replace(/\b(install|run|make:[a-z-]+|migrate)\b/g, '<span class="text-cyan-500">$1</span>');
    }
  };

  // src/docs/highlighters/SqlHighlighter.ts
  var SqlHighlighter = class {
    constructor() {
      __publicField(this, "keywords", /* @__PURE__ */ new Set([
        "add",
        "and",
        "as",
        "asc",
        "autoincrement",
        "by",
        "check",
        "constraint",
        "create",
        "current_timestamp",
        "default",
        "delete",
        "desc",
        "drop",
        "exists",
        "foreign",
        "from",
        "group",
        "if",
        "in",
        "index",
        "inner",
        "insert",
        "integer",
        "into",
        "is",
        "join",
        "key",
        "left",
        "like",
        "limit",
        "not",
        "null",
        "on",
        "or",
        "order",
        "primary",
        "references",
        "select",
        "set",
        "table",
        "text",
        "unique",
        "update",
        "values",
        "where"
      ]));
    }
    highlight(code) {
      const escaped = escapeHtml(code);
      const parts = escaped.split(/(--.*|\/\*[\s\S]*?\*\/|"(?:\\.|[^"])*"|'(?:\\.|[^'])*'|:[A-Za-z_][A-Za-z0-9_]*|\b\d+(?:\.\d+)?\b|\b[A-Za-z_][A-Za-z0-9_]*\b)/g);
      return parts.map((part) => this.highlightPart(part)).join("");
    }
    highlightPart(part) {
      if (part === "") {
        return "";
      }
      if (part.startsWith("--") || part.startsWith("/*")) {
        return `<span class="text-emerald-600">${part}</span>`;
      }
      if (part.startsWith('"') || part.startsWith("'")) {
        return `<span class="text-orange-400">${part}</span>`;
      }
      if (part.startsWith(":")) {
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
  };

  // src/docs/highlighters/StructureHighlighter.ts
  var StructureHighlighter = class {
    highlight(code) {
      return escapeHtml(code).split("\n").map((line) => this.highlightLine(line)).join("\n");
    }
    highlightLine(line) {
      const match = line.match(/^(\s*)(.+)$/);
      if (!match) {
        return line;
      }
      const [, indent, entry] = match;
      const isDirectory = entry.endsWith("/");
      const isPhp = entry.endsWith(".php");
      const isMarkdown = entry.endsWith(".md");
      const isAsset = /\.(ts|js|css|sql|sqlite|yml|env|json|zip)$/i.test(entry);
      const parts = entry.split("/");
      const label = parts.pop() ?? entry;
      const prefix = parts.length > 0 ? `${parts.join("/")}/` : "";
      const labelClass = isDirectory ? "text-sky-500" : isPhp ? "text-cyan-500" : isMarkdown ? "text-orange-400" : isAsset ? "text-violet-500" : "text-black/80 dark:text-white/80";
      return `${indent}<span class="text-black/45 dark:text-white/45">${prefix}</span><span class="${labelClass}">${label}</span>`;
    }
  };

  // src/docs/CodeHighlighter.ts
  var CodeHighlighter = class {
    constructor(autoInit = false) {
      __publicField(this, "highlighters");
      __publicField(this, "fallback", new PlainTextHighlighter());
      __publicField(this, "copyDisabledLanguages", /* @__PURE__ */ new Set(["structure"]));
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
        typescript: script
      };
      if (autoInit) {
        this.highlightAllCode();
      }
    }
    highlightAllCode() {
      document.querySelectorAll("pre[data-language]").forEach((block) => {
        const code = block.querySelector("code");
        const language = block.dataset.language?.toLowerCase() ?? "";
        const raw = code?.textContent ?? "";
        if (!code || raw === "") {
          return;
        }
        code.innerHTML = (this.highlighters[language] ?? this.fallback).highlight(raw);
        block.dataset.highlighted = "true";
        this.addCopyButton(block, code, language);
      });
    }
    addCopyButton(block, code, language) {
      if (block.dataset.copyEnabled === "true" || this.copyDisabledLanguages.has(language)) {
        return;
      }
      block.dataset.copyEnabled = "true";
      block.classList.add("relative");
      const button = document.createElement("button");
      button.type = "button";
      button.className = "absolute right-2 top-2 rounded-md border border-dark-green/20 bg-true-white/95 px-2 py-1 text-xs font-semibold text-dark-green shadow-sm transition hover:border-dark-green hover:bg-dark-green hover:text-true-white focus:outline-none focus:ring-2 focus:ring-dark-green/20 dark:border-mint/30 dark:bg-true-black/95 dark:text-mint dark:hover:border-mint dark:hover:bg-mint dark:hover:text-black dark:focus:ring-mint/20";
      button.textContent = "Copy";
      button.addEventListener("click", async () => {
        const copied = await this.copyText(code.textContent ?? "");
        button.textContent = copied ? "Copied" : "Failed";
        window.setTimeout(() => {
          button.textContent = "Copy";
        }, 1400);
      });
      block.append(button);
    }
    async copyText(value) {
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
    copyWithFallback(value) {
      const textarea = document.createElement("textarea");
      textarea.value = value;
      textarea.setAttribute("readonly", "readonly");
      textarea.style.position = "fixed";
      textarea.style.left = "-9999px";
      document.body.append(textarea);
      textarea.select();
      try {
        return document.execCommand("copy");
      } finally {
        textarea.remove();
      }
    }
  };

  // src/forum-demo/index.ts
  new CodeHighlighter(true);
  document.querySelectorAll("[data-demo-form]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const button = event.submitter instanceof HTMLButtonElement ? event.submitter : null;
      if (!button) {
        return;
      }
      button.disabled = true;
      button.setAttribute("aria-busy", "true");
    });
  });
  var flash = document.querySelector("[data-demo-flash]");
  if (flash) {
    flash.scrollIntoView({ block: "nearest", behavior: "smooth" });
  }
})();
