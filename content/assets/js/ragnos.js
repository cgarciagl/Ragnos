// Use ES6+ class features and proper TypeScript-like documentation
class RagnosSearch {
  /**
   * @type {Stack}
   * @static
   */
  static searchStack = new (class Stack {
    constructor() {
      this.items = [];
    }

    push(item) {
      this.items.push(item);
    }

    pop() {
      return this.items.pop();
    }

    peek() {
      return this.items[this.items.length - 1];
    }

    get length() {
      return this.items.length;
    }

    clear() {
      this.items = [];
    }
  })();
  /**
   * Configura una búsqueda simple en un elemento
   * @param {JQuery} elemento - Elemento donde configurar la búsqueda
   * @param {string} ruta - Ruta para la búsqueda
   * @param {Object} params - Parámetros adicionales
   * @param {Function} callback - Función de callback
   */
  static setupSimpleSearch(elemento, ruta, params, callback) {
    const $elemento = $(elemento);

    // Crear botón de búsqueda
    $elemento.wrap('<div class="input-group"></div>');
    const searchButton = $(`
      <button class="btn btn-outline-secondary" type="button" aria-label="Search">
        <i class="bi bi-search"></i>
      </button>
    `);
    searchButton.insertAfter($elemento);

    // Configurar comportamiento de búsqueda
    const executeSearch = async (searchValue = "") => {
      try {
        const cleanValue = searchValue.replace(/[^a-zA-Z0-9 ]/g, "").trim();

        const requestData = {
          valorabuscar: cleanValue,
          ruta,
          params,
        };

        const result = await getValue("admin/busqueda", requestData);

        // Mostrar resultados en modal
        showModal(result, "", "busquedaModal", () => {
          const modal = $("#busquedaModal");
          const responseData = modal.data("ResultData");
          $elemento.data("searchdata", responseData);
          if (callback) callback($elemento);
        });
      } catch (error) {
        console.error("Error en búsqueda:", error);
      }
    };

    // Configurar eventos
    $elemento.attr("autocomplete", "off").on("keyup", (e) => {
      if (e.key === "Enter" && $elemento.val().trim().length > 0) {
        executeSearch($elemento.val());
      }
    });

    searchButton.on("click", () => executeSearch());
  }
  /**
   * @typedef {Object} SearchParams
   * @property {string} controller - The controller name to handle the search
   * @property {string} filter - The filter to apply to the search
   * @property {Function} [callback] - Optional callback function to execute after search
   */

  /**
   * @type {Map<string, RagnosSearch>}
   */
  static instances = new Map();

  /**
   * @param {HTMLElement} control
   * @param {SearchParams} params
   */
  constructor(control, params) {
    this.control = $(control);
    this.params = {
      controller: params.controller.toLowerCase(),
      filter: params.filter,
      callback: params.callback || (() => {}),
    };

    this.initialize();
  }

  /**
   * Initialize the search functionality
   * @private
   */
  initialize() {
    if (this.control.hasClass("Ragnosffied")) {
      return;
    }

    const controlName = this.control.attr("name");
    const button = this.createSearchButton();
    const hiddenField = this.createHiddenField(controlName);

    this.setupDOM(button, hiddenField);
    this.setupEventListeners(button);

    // Store instance reference
    RagnosSearch.instances.set(controlName, this);
  }

  /**
   * Create the search button element
   * @private
   * @returns {JQuery}
   */
  createSearchButton() {
    return $(`
      <button class="btn btn-outline-secondary" type="button" aria-label="Search">
        <i class="bi bi-search"></i>
      </button>
    `);
  }

  /**
   * Create the hidden field element
   * @private
   * @param {string} controlName
   * @returns {JQuery}
   */
  createHiddenField(controlName) {
    return $(`
      <input 
        type="hidden" 
        class="searchhiddenfield" 
        name="Ragnos_id_${controlName}" 
        id="Ragnos_id_${controlName}"
      >
    `);
  }

  /**
   * Set up DOM elements
   * @private
   * @param {JQuery} button
   * @param {JQuery} hiddenField
   */
  setupDOM(button, hiddenField) {
    button.insertAfter(this.control);

    const parentGroup = this.control.closest(".input-group");
    hiddenField.insertAfter(parentGroup);

    this.control.addClass("Ragnosffied");
  }

  /**
   * Set up event listeners
   * @private
   * @param {JQuery} button
   */
  setupEventListeners(button) {
    if (this.control.is("[readonly]")) {
      return;
    }

    this.control.on("change", () => {
      this.search(this.control.val(), false);
    });

    button.on("click", (e) => {
      e.preventDefault();
      this.search("", true);
    });
  }

  /**
   * Process and encode filter
   * @private
   * @returns {string}
   */
  processFilter() {
    let originalFilter = this.params.filter;
    try {
      const filter = atob(originalFilter);

      // Replace placeholders with element values
      const processedFilter = filter.replace(/\[([^\]]+)]/g, (_, elementId) => {
        const element = $(`#${elementId}`);
        return element.length ? element.val() ?? "" : "";
      });

      if (processedFilter.startsWith("function")) {
        const func = new Function(`return (${processedFilter})();`);
        return btoa(func());
      }

      return btoa(processedFilter);
    } catch (error) {
      console.error("Error processing filter:", error);
      return btoa("");
    }
  }

  /**
   * Sanitize search text
   * @private
   * @param {string} text
   * @returns {string}
   */
  sanitizeSearchText(text) {
    return text.replace(/[^a-zA-Z0-9 ]/g, "").trim();
  }

  /**
   * Perform the search operation
   * @param {string} searchText
   * @param {boolean} forced
   */
  async search(searchText, forced) {
    const sanitizedText = this.sanitizeSearchText(searchText);

    if (!sanitizedText && !forced) {
      const hiddenInput = this.control
        .closest(".input-group")
        .next("input[type=hidden]");

      if (hiddenInput.length) {
        hiddenInput.val("");
      }
      return;
    }

    // Agregar el control actual a la pila de búsquedas
    RagnosSearch.searchStack.push(this.control);

    try {
      const requestData = {
        sSearch: sanitizedText,
        sFilter: this.processFilter(),
        ...(typeof Ragnos_csrf !== "undefined" ? Ragnos_csrf : {}),
      };

      const result = await getValue(
        `${this.params.controller}/searchByAjax`,
        requestData
      );

      await this.showSearchResults(result);
      this.params.callback(this.control);
    } catch (error) {
      console.error("Search error:", error);
      // Implement proper error handling here
    }
  }

  /**
   * Show search results in modal
   * @private
   * @param {string} content
   * @returns {Promise}
   */
  showSearchResults(content) {
    return new Promise((resolve) => {
      showModal(content, "Búsqueda", "YSearchModal", resolve);
    });
  }
}

// jQuery plugin wrapper
$.fn.RagnosSearch = function (params) {
  return this.each(function () {
    new RagnosSearch(this, {
      controller: params.controller || "",
      filter: params.filter || "",
      callback: params.callback || (() => {}),
    });
  });
};

// Utility functions
class RagnosUtils {
  /**
   * Show controller table in specified element
   * @param {string} selector
   * @param {string} controller
   * @param {string} [master='']
   */
  static async showControllerTableIn(selector, controller, master = "") {
    try {
      if (master) {
        Ragnos_csrf.Ragnos_master = master;
      }

      const data = await getValue(`${controller}/tableByAjax`, Ragnos_csrf);

      $(selector).html(data);
    } catch (error) {
      console.error("Error loading controller table:", error);
      // Implement proper error handling
    }
  }

  /**
   * Show controller report in specified element
   * @param {string} selector
   * @param {string} controller
   */
  static async showControllerReportIn(selector, controller) {
    try {
      const data = await getValue(`${controller}/reportByAjax`, Ragnos_csrf);

      $(selector).html(data);
    } catch (error) {
      console.error("Error loading controller report:", error);
      // Implement proper error handling
    }
  }
}
