// Initialize Ragnos namespace
window.Ragnos = window.Ragnos || {};

/* ==========================================================================
   1. Ragnos.Stack / RagnosStack
   ========================================================================== */
class RagnosStack {
  constructor() {
    this.items = [];
  }

  push(item) {
    this.items.push(item);
  }

  pop() {
    return this.items.pop();
  }

  remove(item) {
    const index = this.items.lastIndexOf(item);
    if (index >= 0) this.items.splice(index, 1);
  }

  peek() {
    return this.items.at(-1);
  }

  get length() {
    return this.items.length;
  }

  clear() {
    this.items.length = 0;
  }
}

/* ==========================================================================
   2. Ragnos.Search / RagnosSearch
   ========================================================================== */
class RagnosSearch {
  static searchStack = new RagnosStack();
  static instances = new Map();

  static destroyWithin(root) {
    this.instances.forEach((instance, key) => {
      if (root.contains(instance.control)) {
        this.searchStack.remove(instance.control);
        this.instances.delete(key);
      }
    });
  }

  static setupSimpleSearch(control, route, params = {}, callback = null) {
    const input = getElement(control);
    if (!(input instanceof HTMLInputElement)) return null;

    if (input.classList.contains("Ragnosffied")) return input;
    input.classList.add("Ragnosffied");

    const wrapper =
      input.closest(".input-group") || this.wrapInInputGroup(input);

    let searchButton = wrapper.querySelector(".btn-ragnos-search");
    if (!searchButton) {
      searchButton = this.createButton("search", "Search");
      searchButton.classList.add("btn-ragnos-search");
      wrapper.append(searchButton);
    }

    let clearButton = wrapper.querySelector(".btn-ragnos-clear");
    if (params.canSetToNull !== false && !clearButton) {
      clearButton = this.createButton("x-lg", "Remove");
      clearButton.classList.add("btn-ragnos-clear");
      wrapper.append(clearButton);
    }

    const triggerHooks = (element) => {
      if (typeof callback === "function") callback(element);
      const hookId = element.id ? `_${element.id}OnSearch` : null;
      const hookName = element.name ? `_${element.name}OnSearch` : null;
      if (hookId && typeof window[hookId] === "function") {
        window[hookId](element);
      } else if (hookName && typeof window[hookName] === "function") {
        window[hookName](element);
      }
    };

    const clearField = () => {
      input.value = "";
      input.ragnosSearchData = null;
      const hiddenInput = wrapper.nextElementSibling;
      if (hiddenInput?.matches("input[type=hidden].searchhiddenfield")) {
        hiddenInput.value = "";
      }
      dispatchInputEvents(input);
      triggerHooks(input);
    };

    if (clearButton) {
      clearButton.addEventListener("click", (event) => {
        event.preventDefault();
        clearField();
      });
    }

    const executeSearch = async (searchValue = input.value) => {
      try {
        const cleanValue = String(searchValue || "")
          .replace(/[^\p{L}\p{N} ]/gu, "")
          .trim();
        const result = await getValue("admin/busqueda", {
          valorabuscar: cleanValue,
          ruta: route,
          params,
        });
        const existingModal = document.getElementById("busquedaModal");
        if (existingModal) existingModal.ragnosResultData = null;

        showModal(result, "", "busquedaModal", () => {
          const modal = document.getElementById("busquedaModal");
          if (
            modal &&
            modal.ragnosResultData !== undefined &&
            modal.ragnosResultData !== null
          ) {
            input.ragnosSearchData = modal.ragnosResultData;
          }
          modal
            ?.querySelectorAll("table")
            .forEach((table) => destroyDataTable(table));
          triggerHooks(input);
        });
      } catch (error) {
        console.error("Simple search error:", error);
        showToast("No fue posible completar la búsqueda", "error");
      }
    };

    input.autocomplete = "off";

    if (!input.readOnly && !input.disabled) {
      input.addEventListener("input", () => {
        if (input.value.trim()) {
          debounce(() => executeSearch(input.value), 400, input);
        } else {
          input.ragnosSearchData = null;
          const hiddenInput = wrapper.nextElementSibling;
          if (hiddenInput?.matches("input[type=hidden].searchhiddenfield")) {
            hiddenInput.value = "";
          }
        }
      });

      input.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
          event.preventDefault();
          if (input.value.trim()) {
            executeSearch(input.value);
          }
        }
      });

      searchButton.addEventListener("click", (event) => {
        event.preventDefault();
        executeSearch(input.value);
      });
    }

    return input;
  }

  static wrapInInputGroup(input) {
    const wrapper = document.createElement("div");
    wrapper.className = "input-group";
    input.replaceWith(wrapper);
    wrapper.append(input);
    return wrapper;
  }

  static createButton(icon, label) {
    const button = document.createElement("button");
    button.className = "btn btn-outline-secondary";
    button.type = "button";
    button.ariaLabel = label;
    button.innerHTML = `<i class="bi bi-${icon}" aria-hidden="true"></i>`;
    return button;
  }

  constructor(control, params) {
    this.control = getElement(control);
    if (!(this.control instanceof HTMLInputElement)) {
      throw new TypeError("RagnosSearch requires an input element");
    }

    this.params = {
      controller: String(params.controller || "").toLowerCase(),
      filter: params.filter || "",
      callback: params.callback || (() => {}),
      canSetToNull: params.canSetToNull !== false,
    };
    this.initialize();
  }

  initialize() {
    if (this.control.classList.contains("Ragnosffied")) return;

    const controlName = this.control.name;
    const searchButton = RagnosSearch.createButton("search", "Search");
    const removeButton = this.params.canSetToNull
      ? RagnosSearch.createButton("x-lg", "Remove")
      : null;
    const hiddenField = document.createElement("input");
    hiddenField.type = "hidden";
    hiddenField.className = "searchhiddenfield";
    hiddenField.name = `Ragnos_id_${controlName}`;
    hiddenField.id = hiddenField.name;

    const inputGroup = this.control.closest(".input-group");
    if (inputGroup) {
      inputGroup.append(searchButton);
      if (removeButton) inputGroup.append(removeButton);
      inputGroup.insertAdjacentElement("afterend", hiddenField);
    } else {
      this.control.insertAdjacentElement("afterend", searchButton);
      if (removeButton)
        searchButton.insertAdjacentElement("afterend", removeButton);
      (removeButton || searchButton).insertAdjacentElement(
        "afterend",
        hiddenField,
      );
    }

    this.control.classList.add("Ragnosffied");
    this.setupEventListeners(searchButton, removeButton, hiddenField);
    RagnosSearch.instances.set(this.control, this);
  }

  setupEventListeners(searchButton, removeButton, hiddenField) {
    if (this.control.readOnly) return;

    this.control.addEventListener("input", () => {
      debounce(() => this.search(this.control.value, false), 400, this.control);
    });
    searchButton.addEventListener("click", (event) => {
      event.preventDefault();
      this.search("", true);
    });
    removeButton?.addEventListener("click", (event) => {
      event.preventDefault();
      this.control.value = "";
      this.control.ragnosSearchData = null;
      hiddenField.value = "";
      dispatchInputEvents(this.control);
    });
  }

  processFilter() {
    try {
      const decoded = atob(this.params.filter);
      const filters = decoded ? JSON.parse(decoded) : [];
      const processed = filters.map((filter) => ({
        ...filter,
        value:
          typeof filter.value === "string"
            ? filter.value.replace(/\[([^\]]+)]/g, (_, id) => {
                return document.getElementById(id)?.value ?? "";
              })
            : filter.value,
      }));
      return btoa(JSON.stringify(processed));
    } catch (error) {
      console.error("Error processing JSON filter:", error);
      return btoa("[]");
    }
  }

  sanitizeSearchText(text) {
    return String(text || "")
      .replace(/[^\p{L}\p{N} ]/gu, "")
      .trim();
  }

  async search(searchText, forced) {
    const sanitizedText = this.sanitizeSearchText(searchText);
    const hiddenInput =
      this.control.closest(".input-group")?.nextElementSibling;

    if (!sanitizedText && !forced) {
      if (hiddenInput?.matches("input[type=hidden]")) hiddenInput.value = "";
      return;
    }

    RagnosSearch.searchStack.push(this.control);
    try {
      const content = await getValue(`${this.params.controller}/searchByAjax`, {
        sSearch: sanitizedText,
        sFilter: this.processFilter(),
        ...(globalThis.Ragnos_csrf || {}),
      });
      await new Promise((resolve) => {
        showModal(content, "Búsqueda", "YSearchModal", () => {
          document
            .getElementById("YSearchModal")
            ?.querySelectorAll("table")
            .forEach((table) => destroyDataTable(table));
          resolve();
        });
      });
      RagnosSearch.searchStack.remove(this.control);
      this.params.callback(this.control);
    } catch (error) {
      RagnosSearch.searchStack.remove(this.control);
      console.error("Search error:", error);
    }
  }
}

/* ==========================================================================
   3. Ragnos.Editor / RagnosRichTextEditor
   ========================================================================== */
class RagnosRichTextEditor {
  static allowedTags = new Set([
    "A",
    "B",
    "BLOCKQUOTE",
    "BR",
    "CODE",
    "DIV",
    "EM",
    "H1",
    "H2",
    "H3",
    "H4",
    "H5",
    "H6",
    "HR",
    "I",
    "LI",
    "OL",
    "P",
    "PRE",
    "S",
    "SPAN",
    "STRONG",
    "TABLE",
    "TBODY",
    "TD",
    "TFOOT",
    "TH",
    "THEAD",
    "TR",
    "U",
    "UL",
  ]);

  static blockedTags = new Set([
    "BASE",
    "EMBED",
    "FORM",
    "IFRAME",
    "INPUT",
    "LINK",
    "META",
    "OBJECT",
    "SCRIPT",
    "STYLE",
  ]);

  constructor(textarea) {
    this.textarea = getElement(textarea);
    if (!(this.textarea instanceof HTMLTextAreaElement)) return;
    this.createEditor();
  }

  static sanitize(html) {
    const template = document.createElement("template");
    template.innerHTML = String(html || "");

    Array.from(template.content.querySelectorAll("*")).forEach((element) => {
      if (this.blockedTags.has(element.tagName)) {
        element.remove();
        return;
      }
      if (!this.allowedTags.has(element.tagName)) {
        element.replaceWith(...element.childNodes);
        return;
      }

      Array.from(element.attributes).forEach((attribute) => {
        const name = attribute.name.toLowerCase();
        const commonAttributes = ["class", "colspan", "rowspan", "title"];
        const linkAttributes =
          element.tagName === "A" ? ["href", "target", "rel"] : [];
        if (![...commonAttributes, ...linkAttributes, "style"].includes(name)) {
          element.removeAttribute(attribute.name);
        }
      });

      if (element.tagName === "A") {
        const href = element.getAttribute("href") || "";
        if (!/^(?:https?:|mailto:|tel:|#|\/|\.\/|\.\.\/)/i.test(href)) {
          element.removeAttribute("href");
        }
        if (element.getAttribute("target") === "_blank") {
          element.setAttribute("rel", "noopener noreferrer");
        } else {
          element.removeAttribute("target");
          element.removeAttribute("rel");
        }
      }

      const style = element.getAttribute("style");
      if (style) {
        const safeStyle = style
          .split(";")
          .map((rule) => rule.trim())
          .filter((rule) =>
            /^(?:text-align|margin-left|list-style-type)\s*:/i.test(rule),
          )
          .filter((rule) => !/(?:url|expression|javascript)/i.test(rule))
          .join("; ");
        if (safeStyle) element.setAttribute("style", safeStyle);
        else element.removeAttribute("style");
      }
    });

    return template.innerHTML;
  }

  createEditor() {
    const editor = document.createElement("div");
    editor.className = "ragnos-rich-text border rounded";
    editor.innerHTML = `
      <div class="ragnos-rich-text__toolbar btn-toolbar gap-1 p-2 border-bottom" role="toolbar" aria-label="Text formatting">
        <select class="form-select form-select-sm ragnos-rich-text__style" data-action="formatBlock" aria-label="Paragraph style" title="Paragraph style">
          <option value="p">Paragraph</option>
          <option value="h2">Heading 2</option>
          <option value="h3">Heading 3</option>
          <option value="blockquote">Quote</option>
          <option value="pre">Code block</option>
        </select>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold" aria-label="Bold" title="Bold"><b>B</b></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="underline" aria-label="Underline" title="Underline"><u>U</u></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="removeFormat" aria-label="Clear formatting" title="Clear formatting"><i class="bi bi-eraser"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList" aria-label="Bulleted list" title="Bulleted list"><i class="bi bi-list-ul"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList" aria-label="Numbered list" title="Numbered list"><i class="bi bi-list-ol"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="justifyLeft" aria-label="Align left" title="Align left"><i class="bi bi-text-left"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="justifyCenter" aria-label="Align center" title="Align center"><i class="bi bi-text-center"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="justifyRight" aria-label="Align right" title="Align right"><i class="bi bi-text-right"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="outdent" aria-label="Decrease indent" title="Decrease indent"><i class="bi bi-text-indent-right"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="indent" aria-label="Increase indent" title="Increase indent"><i class="bi bi-text-indent-left"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="table" aria-label="Insert table" title="Insert table"><i class="bi bi-table"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="createLink" aria-label="Insert link" title="Insert link"><i class="bi bi-link-45deg"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="code" aria-label="HTML source" title="HTML source"><i class="bi bi-code-slash"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="fullscreen" aria-label="Fullscreen" title="Fullscreen"><i class="bi bi-arrows-fullscreen"></i></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="help" aria-label="Help" title="Help"><i class="bi bi-question-circle"></i></button>
      </div>
      <div class="ragnos-rich-text__content p-3" contenteditable="true" role="textbox" aria-multiline="true"></div>`;
    this.textarea.hidden = true;
    this.textarea.insertAdjacentElement("afterend", editor);
    this.editor = editor;
    this.content = editor.querySelector(".ragnos-rich-text__content");
    this.content.innerHTML = RagnosRichTextEditor.sanitize(this.textarea.value);
    this.sync();

    editor
      .querySelector(".ragnos-rich-text__toolbar")
      .addEventListener("mousedown", (event) => {
        if (event.target.closest("button")) event.preventDefault();
      });
    editor
      .querySelector(".ragnos-rich-text__toolbar")
      .addEventListener("click", (event) => {
        const button = event.target.closest("button");
        if (button) this.runAction(button);
      });
    editor
      .querySelector('[data-action="formatBlock"]')
      .addEventListener("change", (event) => {
        this.restoreSelection();
        document.execCommand("formatBlock", false, event.target.value);
        this.sync();
      });
    ["input", "keyup", "mouseup"].forEach((eventName) => {
      this.content.addEventListener(eventName, () => {
        this.saveSelection();
        if (eventName === "input") this.sync();
      });
    });
    this.content.addEventListener("paste", (event) => this.handlePaste(event));
    this.textarea.form?.addEventListener("submit", () => this.sync());
  }

  runAction(button) {
    this.restoreSelection();
    if (button.dataset.action === "fullscreen") {
      this.editor.classList.toggle("ragnos-rich-text--fullscreen");
      return;
    }
    if (button.dataset.action === "code") {
      const showingCode = this.content.dataset.code === "true";
      if (showingCode) {
        this.content.innerHTML = RagnosRichTextEditor.sanitize(
          this.content.textContent,
        );
      } else {
        this.content.textContent = RagnosRichTextEditor.sanitize(
          this.content.innerHTML,
        );
      }
      this.content.dataset.code = String(!showingCode);
      this.editor.classList.toggle("ragnos-rich-text--code", !showingCode);
      this.sync();
      return;
    }
    if (button.dataset.action === "table") {
      this.insertTable();
      return;
    }
    if (button.dataset.action === "help") {
      this.showHelp();
      return;
    }

    const command = button.dataset.command;
    const value = command === "createLink" ? prompt("URL") : null;
    if (command !== "createLink" || value)
      document.execCommand(command, false, value);
    this.sync();
  }

  saveSelection() {
    const selection = window.getSelection();
    if (!selection?.rangeCount) return;
    const range = selection.getRangeAt(0);
    if (this.content.contains(range.commonAncestorContainer)) {
      this.selection = range.cloneRange();
    }
  }

  restoreSelection() {
    this.content.focus();
    if (!this.selection) return;
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(this.selection);
  }

  handlePaste(event) {
    event.preventDefault();
    const clipboard = event.clipboardData;
    const html = clipboard?.getData("text/html");
    const text = clipboard?.getData("text/plain") || "";
    const safeContent = html
      ? RagnosRichTextEditor.sanitize(html)
      : escapeHtml(text).replace(/\r?\n/g, "<br>");
    document.execCommand("insertHTML", false, safeContent);
    this.sync();
  }

  insertTable() {
    const requestedRows = prompt("Rows", "2");
    if (requestedRows === null) return;
    const requestedColumns = prompt("Columns", "2");
    if (requestedColumns === null) return;

    const rows = Math.min(
      20,
      Math.max(1, Number.parseInt(requestedRows, 10) || 1),
    );
    const columns = Math.min(
      10,
      Math.max(1, Number.parseInt(requestedColumns, 10) || 1),
    );

    const cells = `<td><br></td>`.repeat(columns);
    const html = `<table class="table table-bordered"><tbody>${`<tr>${cells}</tr>`.repeat(rows)}</tbody></table><p><br></p>`;
    document.execCommand("insertHTML", false, html);
    this.sync();
  }

  showHelp() {
    const message =
      "Select text and use the toolbar to apply styles, lists, alignment, links or tables. HTML source mode accepts only safe formatting markup.";
    if (globalThis.Swal) {
      Swal.fire({ icon: "info", title: "Rich text editor", text: message });
    } else {
      alert(message);
    }
  }

  sync() {
    const html =
      this.content.dataset.code === "true"
        ? this.content.textContent
        : this.content.innerHTML;
    this.textarea.value = RagnosRichTextEditor.sanitize(html);
    dispatchInputEvents(this.textarea);
  }
}

/* ==========================================================================
   4. Ragnos.Utils / RagnosUtils
   ========================================================================== */
class RagnosUtils {
  static async showControllerTableIn(selector, controller, master = "") {
    try {
      const parameters = { ...(globalThis.Ragnos_csrf || {}) };
      if (master) parameters.Ragnos_master = master;
      setHtml(
        selector,
        await getValue(`${controller}/tableByAjax`, parameters),
      );
    } catch (error) {
      console.error("Error loading controller table:", error);
    }
  }

  static async showControllerReportIn(selector, controller) {
    try {
      setHtml(
        selector,
        await getValue(
          `${controller}/reportByAjax`,
          globalThis.Ragnos_csrf || {},
        ),
      );
    } catch (error) {
      console.error("Error loading controller report:", error);
    }
  }
}

/* ==========================================================================
   5. Namespace Composition & Top-Level Global Aliases
   ========================================================================== */
Ragnos.Stack = RagnosStack;
Ragnos.Search = RagnosSearch;
Ragnos.Editor = RagnosRichTextEditor;
Ragnos.Utils = RagnosUtils;

window.RagnosStack = RagnosStack;
window.RagnosSearch = RagnosSearch;
window.RagnosRichTextEditor = RagnosRichTextEditor;
window.RagnosUtils = RagnosUtils;
