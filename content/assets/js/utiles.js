// Initialize Ragnos namespace
window.Ragnos = window.Ragnos || {};

// Set base_url if not defined
if (typeof globalThis.base_url !== "string" || !globalThis.base_url) {
  try {
    const url = new URL(window.location.href);
    const pathSegments = url.pathname
      .replace(/^\/|\/$/g, "")
      .split("/")
      .filter(Boolean);

    globalThis.base_url = `${url.protocol}//${url.host}/${encodeURIComponent(
      pathSegments[0] || "",
    )}/`;
  } catch (error) {
    console.error(
      "Error al establecer base_url. Usando raíz como fallback:",
      error,
    );
    globalThis.base_url = "/";
  }
}

const debounceTimers = new Map();

/* ==========================================================================
   1. Ragnos.DOM: Helper functions for DOM manipulation and timing
   ========================================================================== */
Ragnos.DOM = {
  onReady(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback, { once: true });
      return;
    }
    callback();
  },

  getElement(target, root = document) {
    if (target instanceof Element || target === document || target === window) {
      return target;
    }
    return typeof target === "string" ? root.querySelector(target) : null;
  },

  getElements(target, root = document) {
    if (typeof target === "string") {
      return Array.from(root.querySelectorAll(target));
    }
    if (target instanceof Element) {
      return [target];
    }
    return Array.from(target || []);
  },

  setHtml(target, html) {
    const element = Ragnos.DOM.getElement(target);
    if (!element) return null;

    element
      .querySelectorAll("table")
      .forEach((table) => Ragnos.Table.destroyDataTable(table));
    element
      .querySelectorAll("select")
      .forEach((select) => select.tomselect?.destroy());
    if (typeof RagnosSearch !== "undefined") {
      RagnosSearch.destroyWithin(element);
    }
    element.innerHTML = html ?? "";
    element.querySelectorAll("script").forEach((script) => {
      const executableScript = document.createElement("script");
      Array.from(script.attributes).forEach(({ name, value }) => {
        executableScript.setAttribute(name, value);
      });
      executableScript.textContent = script.textContent;
      script.replaceWith(executableScript);
    });

    return element;
  },

  dispatchInputEvents(element) {
    element.dispatchEvent(new Event("input", { bubbles: true }));
    element.dispatchEvent(new Event("change", { bubbles: true }));
  },

  debounce(func, delay, key = "default") {
    clearTimeout(debounceTimers.get(key));
    debounceTimers.set(
      key,
      setTimeout(() => {
        debounceTimers.delete(key);
        func();
      }, delay),
    );
  },

  escapeHtml(unsafe) {
    return String(unsafe || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  },

  limitText(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
      limitField.value = limitField.value.slice(0, limitNum);
    }
  },

  moneyToNumber(amt) {
    if (amt === null || typeof amt === "undefined" || amt === "") {
      return 0;
    }
    const strAmt = String(amt);
    const cleanStr = strAmt.replace(/[^0-9.-]+/g, "");
    if (cleanStr === "" || cleanStr === ".") {
      return 0;
    }
    return parseFloat(cleanStr);
  },

  moneyFormat(amt, currency = "USD") {
    let numAmt = Ragnos.DOM.moneyToNumber(amt);
    if (isNaN(numAmt)) {
      numAmt = 0;
    }
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: currency,
    }).format(numAmt);
  },

  serializeForm(formElement) {
    if (!(formElement instanceof HTMLFormElement)) return {};
    return Object.fromEntries(new FormData(formElement));
  },

  serializeParams(obj, prefix) {
    let str = [];
    for (let p in obj) {
      if (!obj.hasOwnProperty(p)) continue;
      let k = prefix ? `${prefix}[${p}]` : p,
        v = obj[p];
      if (v === null || v === undefined) v = "";
      if (typeof v === "object" && !Array.isArray(v)) {
        str.push(Ragnos.DOM.serializeParams(v, k));
      } else if (Array.isArray(v)) {
        v.forEach((val, idx) => {
          if (val === null || val === undefined) val = "";
          str.push(Ragnos.DOM.serializeParams(val, `${k}[${idx}]`));
        });
      } else {
        str.push(`${encodeURIComponent(k)}=${encodeURIComponent(v)}`);
      }
    }
    return str.join("&");
  },

  trim(inputString) {
    return String(inputString || "").trim();
  },

  limpia(cadena) {
    return String(cadena || "").replace("-", " ").replace(/\s+/g, " ").trim();
  },

  inArray(element, array) {
    return Array.isArray(array) && array.includes(element);
  },
};

/* ==========================================================================
   2. Ragnos.Http: Networking, Ajax, URLs, and Redirects
   ========================================================================== */
Ragnos.Http = {
  fixUrl(relativeUrl) {
    try {
      if (typeof relativeUrl !== "string" || !relativeUrl.trim()) {
        console.warn("fixUrl: purl es inválido o está vacío.");
        return "";
      }
      const isAbsolute = /^https?:\/\//i.test(relativeUrl);
      if (isAbsolute || typeof base_url === "undefined") {
        return relativeUrl;
      }
      if (typeof base_url !== "string" || !base_url.trim()) {
        console.error("fixUrl: base_url no está definido o es inválido.");
        return relativeUrl;
      }
      const normalizedBaseUrl = base_url.replace(/\/+$/, "");
      const normalizedPath = relativeUrl.replace(/^\/+/, "");
      return `${normalizedBaseUrl}/index.php/${normalizedPath}`;
    } catch (error) {
      console.error("fixUrl: Error procesando la URL:", error);
      return "";
    }
  },

  redirectTo(urlToRedirect) {
    setTimeout(function () {
      window.location.href = Ragnos.Http.fixUrl(urlToRedirect);
    }, 0);
  },

  openInNew(urlToOpen) {
    window.open(Ragnos.Http.fixUrl(urlToOpen), "_new");
  },

  redirectByPost(purl, parameters = {}, inNewTab = true) {
    try {
      if (typeof purl !== "string" || !purl.trim()) {
        console.error("redirectByPost: URL inválida.");
        return false;
      }
      const url = Ragnos.Http.fixUrl(purl);
      const form = document.createElement("form");
      form.method = "post";
      form.action = url;
      if (inNewTab) form.target = "_blank";
      if (typeof csrfToken !== "undefined") {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "csrf_token";
        input.value = csrfToken;
        form.appendChild(input);
      }
      Object.entries(parameters).forEach(([key, value]) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = String(value);
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
      return true;
    } catch (error) {
      console.error("redirectByPost: Error durante la redirección.", error);
      return false;
    }
  },

  refreshPage() {
    location.reload();
  },

  async getValue(url, params = {}, callback) {
    const config = {
      timeout: params.timeout || 12000,
      retryAttempts: params.retryAttempts || 1,
      retryDelay: params.retryDelay || 1000,
    };
    const cleanParams = { ...params };
    delete cleanParams.timeout;
    delete cleanParams.retryAttempts;
    delete cleanParams.retryDelay;

    const body = Ragnos.DOM.serializeParams(cleanParams);

    const makeRequest = async () => {
      let attempts = 0;
      let lastError = null;

      while (attempts < config.retryAttempts) {
        try {
          const resPromise = fetch(Ragnos.Http.fixUrl(url), {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
              "X-Requested-With": "XMLHttpRequest",
            },
            body: body,
          });
          const response = await Promise.race([
            resPromise,
            new Promise((_, reject) =>
              setTimeout(() => reject(new Error("Timeout")), config.timeout),
            ),
          ]);
          const text = await response.text();
          if (!response.ok) {
            throw {
              status: response.status,
              statusText: response.statusText,
              response: text,
            };
          }
          return { response: text, error: null };
        } catch (error) {
          lastError = {
            error: error.message || "Error desconocido",
            status: error.status || 0,
          };
          Ragnos.Http.manejaError(error);
          attempts++;
          if (attempts < config.retryAttempts) {
            await new Promise((res) => setTimeout(res, config.retryDelay));
          }
        }
      }
      return { response: null, error: lastError };
    };

    if (typeof callback === "function") {
      const result = await makeRequest();
      callback(result.response, result.error);
      return;
    }
    const result = await makeRequest();
    if (result.error) throw result.error;
    return result.response;
  },

  async getObject(purl, pparameters, callbackfunction) {
    const processResponse = async (response) => {
      try {
        const obj = JSON.parse(response);
        return { result: obj, error: null };
      } catch (error) {
        return { result: null, error };
      }
    };

    if (typeof callbackfunction !== "function") {
      try {
        const response = await Ragnos.Http.getValue(purl, pparameters);
        const { result, error } = await processResponse(response);
        if (error) throw error;
        return result;
      } catch (error) {
        throw error;
      }
    }

    try {
      await Ragnos.Http.getValue(purl, pparameters, async (response, errors) => {
        if (errors) {
          callbackfunction(null, errors);
          return;
        }
        const { result, error } = await processResponse(response);
        callbackfunction(result, error);
      });
    } catch (error) {
      callbackfunction(null, error);
    }
  },

  async postFormData(url, formData, callback) {
    const config = {
      timeout: 0,
      retryAttempts: 1,
      retryDelay: 1000,
    };

    const makeRequest = async () => {
      let attempts = 0;
      let lastError = null;

      while (attempts < config.retryAttempts) {
        try {
          const resPromise = fetch(Ragnos.Http.fixUrl(url), {
            method: "POST",
            headers: {
              "X-Requested-With": "XMLHttpRequest",
            },
            body: formData,
          });
          const response = await resPromise;
          const text = await response.text();
          if (!response.ok) {
            throw {
              status: response.status,
              statusText: response.statusText,
              response: text,
            };
          }
          return { response: text, error: null };
        } catch (error) {
          lastError = {
            error: error.message || "Error desconocido",
            status: error.status || 0,
          };
          Ragnos.Http.manejaError(lastError);
          attempts++;
          if (attempts < config.retryAttempts) {
            await new Promise((res) => setTimeout(res, config.retryDelay));
          }
        }
      }
      return { response: null, error: lastError };
    };

    if (typeof callback === "function") {
      const result = await makeRequest();
      callback(result.response, result.error);
      return;
    }
    const result = await makeRequest();
    if (result.error) throw result.error;
    return result.response;
  },

  async uploadObject(purl, formData, callbackfunction) {
    const processResponse = async (response) => {
      try {
        const obj = JSON.parse(response);
        return { result: obj, error: null };
      } catch (error) {
        return { result: null, error };
      }
    };

    if (typeof callbackfunction !== "function") {
      try {
        const response = await Ragnos.Http.postFormData(purl, formData);
        const { result, error } = await processResponse(response);
        if (error) throw error;
        return result;
      } catch (error) {
        throw error;
      }
    }

    try {
      await Ragnos.Http.postFormData(purl, formData, async (response, errors) => {
        if (errors) {
          callbackfunction(null, errors);
          return;
        }
        const { result, error } = await processResponse(response);
        callbackfunction(result, error);
      });
    } catch (error) {
      callbackfunction(null, error);
    }
  },

  async getSession() {
    return await Ragnos.Http.getObject("admin/sess", {});
  },

  manejaError(responseOrError) {
    const errorMessages = {
      401: "Su sesión ha expirado, por favor inicie sesión nuevamente.",
      403: "No tiene permiso para realizar esta acción.",
      404: "No se encontró la página solicitada.",
      500: "Error interno del servidor.",
    };
    let status = responseOrError && responseOrError.status;
    let message = errorMessages[status] || "Error desconocido";

    Ragnos.UI.showToast(message, "error");

    if (status === 401) {
      if (window.Swal) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: errorMessages[401],
          timer: 2000,
          didClose: () => {
            window.location.href = Ragnos.Http.fixUrl("admin/login");
          },
        });
      } else {
        alert(errorMessages[401]);
        window.location.href = Ragnos.Http.fixUrl("admin/login");
      }
    }
  },
};

/* ==========================================================================
   3. Ragnos.Table: DataTables and HTML Table Manipulations
   ========================================================================== */
Ragnos.Table = {
  destroyDataTable(table) {
    const element = Ragnos.DOM.getElement(table);
    if (!element?.ragnosDataTable) return;

    try {
      element.ragnosDataTable.destroy();
    } catch (error) {
      console.warn("Unable to destroy DataTable cleanly:", error);
    } finally {
      delete element.ragnosDataTable;
    }
  },

  aplicarDebounceABusqueda(datatableInstance, delay = 500) {
    const table = datatableInstance.table().node();
    const wrapper = table.closest(".dt-container") || table.parentElement;
    const searchInput = wrapper?.querySelector(".dt-search input");
    if (!searchInput || searchInput.dataset.ragnosDebounced === "true") return;

    searchInput.dataset.ragnosDebounced = "true";
    searchInput.addEventListener("input", () => {
      Ragnos.DOM.debounce(
        () => datatableInstance.search(searchInput.value).draw(),
        delay,
        searchInput,
      );
    });
  },

  ponTablaPaginada(tableSelector, optionsextra = {}) {
    let options = {
      pagingType: "numbers",
      order: [],
      language: {
        processing: "Procesando...",
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron registros",
        info: "Mostrando desde _START_ hasta _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando desde 0 hasta 0 de 0 registros",
        infoFiltered: "",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          previous: "Anterior",
          next: "Siguiente",
          last: "Último",
        },
      },
    };

    const table = Ragnos.DOM.getElement(tableSelector);
    if (!(table instanceof HTMLTableElement)) {
      throw new TypeError("ponTablaPaginada requires a table element");
    }

    options = { ...options, ...optionsextra };
    const dataTable = new DataTable(table, options);
    table.ragnosDataTable = dataTable;
    return dataTable;
  },

  ponTotalesEnTabla(
    targetTable,
    enRenglonFinal = true,
    enColumnaFinal = false,
  ) {
    if (
      !targetTable ||
      !(Ragnos.DOM.getElement(targetTable) instanceof HTMLTableElement)
    ) {
      console.error("Invalid table element");
      return;
    }

    targetTable = Ragnos.DOM.getElement(targetTable);
    const tbody = targetTable.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    let columnCount = rows[0] ? rows[0].querySelectorAll("td").length : 0;

    if (enColumnaFinal) {
      Ragnos.Table.agregarColumnaTotal(targetTable, rows, columnCount);
      columnCount += 1;
    }
    if (enRenglonFinal) {
      Ragnos.Table.agregarRenglonTotal(tbody, rows, columnCount);
    }
  },

  agregarRenglonTotal(tbody, rows, columnCount) {
    if (rows.length < 1) return;
    const totalRow = document.createElement("tr");
    for (let i = 0; i < columnCount; i++) {
      const td = document.createElement("td");
      td.classList.add("total");
      td.style.fontWeight = "bold";
      td.textContent = i === 0 ? "Total" : Ragnos.Table.calcularColumnaTotal(rows, i);
      totalRow.appendChild(td);
    }
    tbody.appendChild(totalRow);
  },

  agregarColumnaTotal(table, rows, columnCount) {
    if (columnCount < 2) return;
    const theadRow = table.querySelector("thead tr");
    if (theadRow) {
      const th = document.createElement("th");
      th.classList.add("total");
      th.style.fontWeight = "bold";
      th.textContent = "Total";
      theadRow.appendChild(th);
    }
    rows.forEach((row) => {
      const total = Ragnos.Table.calcularFilaTotal(row, columnCount);
      const td = document.createElement("td");
      td.classList.add("total");
      td.style.fontWeight = "bold";
      td.textContent = total;
      row.appendChild(td);
    });
  },

  calcularColumnaTotal(rows, colIndex) {
    let esDinero = false;
    let sum = 0;
    rows.forEach((row) => {
      const cell = row.querySelectorAll("td")[colIndex];
      if (!cell) return;
      let valor = cell.textContent.trim();
      if (valor.startsWith("$")) esDinero = true;
      sum += Ragnos.DOM.moneyToNumber(valor) || 0;
    });
    return Ragnos.Table.formatearTotal(sum, esDinero);
  },

  calcularFilaTotal(row, columnCount) {
    let esDinero = false;
    let sum = 0;
    const cells = Array.from(row.querySelectorAll("td")).slice(
      1,
      columnCount - 1,
    );
    cells.forEach((cell) => {
      let valor = cell.textContent.trim();
      if (valor.startsWith("$")) esDinero = true;
      sum += Ragnos.DOM.moneyToNumber(valor) || 0;
    });
    return Ragnos.Table.formatearTotal(sum, esDinero);
  },

  formatearTotal(totalAmount, isCurrency) {
    if (isCurrency) {
      return Ragnos.DOM.moneyFormat(totalAmount);
    }
    return Number.isInteger(totalAmount)
      ? totalAmount.toFixed(0)
      : totalAmount.toFixed(2);
  },

  quitaTotaldeColumna(targetTable, colIndex) {
    if (!targetTable) return;
    targetTable = Ragnos.DOM.getElement(targetTable);
    if (!(targetTable instanceof HTMLTableElement)) return;
    const tbody = targetTable.querySelector("tbody");
    const rows = tbody ? tbody.rows : targetTable.rows;
    if (rows.length === 0) return;
    const totalRow = rows[rows.length - 1];
    const indexesToClear = Array.isArray(colIndex) ? colIndex : [colIndex];
    indexesToClear.forEach((index) => {
      if (totalRow.cells.length > index) {
        totalRow.cells[index].textContent = "";
        totalRow.cells[index].innerHTML = "";
      }
    });
  },

  quitaTotaldeRenglon(targetTable, rowIndex) {
    if (!targetTable) return;
    targetTable = Ragnos.DOM.getElement(targetTable);
    if (!(targetTable instanceof HTMLTableElement)) return;
    const tbody = targetTable.querySelector("tbody");
    const rows = tbody ? tbody.rows : targetTable.rows;
    const indexesToClear = Array.isArray(rowIndex) ? rowIndex : [rowIndex];
    indexesToClear.forEach((idx) => {
      if (idx >= 0 && idx < rows.length) {
        const targetRow = rows[idx];
        if (targetRow.cells.length > 0) {
          const lastCellIndex = targetRow.cells.length - 1;
          targetRow.cells[lastCellIndex].textContent = "";
          targetRow.cells[lastCellIndex].innerHTML = "";
        }
      }
    });
  },

  exportToExcel(fileName, htmlContent) {
    try {
      if (!fileName || typeof fileName !== "string") {
        throw new Error("Invalid file name");
      }
      if (!htmlContent || typeof htmlContent !== "string") {
        throw new Error("Invalid HTML content");
      }
      const EXCEL_URI = "data:application/vnd.ms-excel;charset=UTF-8;base64,";
      const EXCEL_TEMPLATE = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" 
            xmlns:x="urn:schemas-microsoft-com:office:excel" 
            xmlns="http://www.w3.org/TR/REC-html40">
          <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta charset="utf-8" />
            <!--[if gte mso 9]>
            <xml>
              <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                  <x:ExcelWorksheet>
                    <x:Name>{worksheet}</x:Name>
                    <x:WorksheetOptions>
                      <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                  </x:ExcelWorksheet>
                </x:ExcelWorksheets>
              </x:ExcelWorkbook>
            </xml>
            <![endif]-->
          </head>
          <body>
            <table>{table}</table>
          </body>
        </html>`;

      const base64 = (s) => window.btoa(unescape(encodeURIComponent(s)));
      const format = (template, context) =>
        template.replace(/{(\w+)}/g, (match, key) => context[key] || "");

      const link = document.createElement("a");
      link.download = `${fileName.trim()}.xls`;
      link.href =
        EXCEL_URI +
        base64(
          format(EXCEL_TEMPLATE, {
            worksheet: "Worksheet",
            table: htmlContent,
          }),
        );
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      return true;
    } catch (error) {
      console.error("Error exporting to Excel:", error);
      Ragnos.UI.showToast("Error al exportar a Excel", "error");
      return false;
    }
  },

  tablaCompleta(tableElement) {
    try {
      const table = Ragnos.DOM.getElement(tableElement);
      if (!(table instanceof HTMLTableElement)) {
        throw new Error("Invalid table element");
      }
      const dataTableInstance = new DataTable.Api(table);
      if (!dataTableInstance) {
        throw new Error("DataTable not initialized");
      }
      const extractedRows = dataTableInstance.rows().data().toArray();
      const columnHeaders = dataTableInstance.columns().header().toArray();
      const fieldNames = columnHeaders.map((header) => header.textContent.trim());

      let tableHtml = '<table border="1"><thead><tr>';
      fieldNames.slice(0, -1).forEach((fieldName) => {
        tableHtml += `<th style="background-color: #f2f2f2; padding: 8px;">${Ragnos.DOM.escapeHtml(
          fieldName,
        )}</th>`;
      });
      tableHtml += "</tr></thead><tbody>";
      extractedRows.forEach((row) => {
        tableHtml += "<tr>";
        Object.values(row).forEach((value) => {
          tableHtml += `<td style="padding: 6px;">${Ragnos.DOM.escapeHtml(
            String(value),
          )}</td>`;
        });
        tableHtml += "</tr>";
      });
      tableHtml += "</tbody></table>";
      return tableHtml;
    } catch (error) {
      console.error("Error in tablaCompleta:", error);
      Ragnos.UI.showToast("Error al generar la tabla", "error");
      return "";
    }
  },

  exportaTablaCompletaAExcel(fileName, tablae) {
    let tableHtml = Ragnos.Table.tablaCompleta(tablae);
    Ragnos.Table.exportToExcel(fileName, tableHtml);
  },

  convertToTable(dataInput, options = {}) {
    if (!dataInput) return "";
    const defaultOptions = {
      tableClass: "table",
      stripHtml: true,
      maxCellLength: 100,
      emptyText: "-",
    };
    const settings = { ...defaultOptions, ...options };
    try {
      return Array.isArray(dataInput)
        ? Ragnos.Table.arrayToTable(dataInput, settings)
        : Ragnos.Table.objectToTable(dataInput, settings);
    } catch (error) {
      console.error("Error converting data to table:", error);
      return "";
    }
  },

  objectToTable(obj, settings) {
    const keys = Object.keys(obj);
    if (keys.length === 0) return settings.emptyText;
    return `
      <table class="${Ragnos.DOM.escapeHtml(settings.tableClass)}">
        <thead>${Ragnos.Table.createHeaderRow(keys, settings)}</thead>
        <tbody>${Ragnos.Table.objectToRow(obj, keys, settings)}</tbody>
      </table>
    `;
  },

  arrayToTable(array, settings) {
    if (!array.length) return settings.emptyText;
    const keys = Object.keys(array[0]);
    if (keys.length === 0) return settings.emptyText;
    return `
      <table class="${Ragnos.DOM.escapeHtml(settings.tableClass)}">
        <thead>${Ragnos.Table.createHeaderRow(keys, settings)}</thead>
        <tbody>${array.map((item) => Ragnos.Table.objectToRow(item, keys, settings)).join("")}</tbody>
      </table>
    `;
  },

  objectToRow(obj, keys, settings) {
    return `
      <tr>
        ${keys
          .map((key) => `<td>${Ragnos.Table.formatCellContent(obj[key], settings)}</td>`)
          .join("")}
      </tr>
    `;
  },

  createHeaderRow(keys, settings) {
    return `
      <tr>
        ${keys
          .map((key) => `<th>${Ragnos.Table.formatCellContent(key, settings)}</th>`)
          .join("")}
      </tr>
    `;
  },

  formatCellContent(content, settings) {
    if (content === null || content === undefined) return settings.emptyText;
    let formatted = String(content);
    if (settings.maxCellLength && formatted.length > settings.maxCellLength) {
      formatted = `${formatted.substring(0, settings.maxCellLength)}...`;
    }
    if (settings.stripHtml) {
      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = formatted;
      formatted = tempDiv.textContent || tempDiv.innerText || "";
    }
    return Ragnos.DOM.escapeHtml(formatted);
  },

  ponValorEnSelect(elemento, id, texto) {
    const select = Ragnos.DOM.getElement(elemento);
    if (!(select instanceof HTMLSelectElement)) return;
    Array.from(select.options).forEach((option) => {
      if (String(option.value) === String(id)) option.remove();
      else option.selected = false;
    });
    select.add(new Option(texto, id, true, true));
    if (select.tomselect) {
      select.tomselect.sync();
      select.tomselect.setValue(String(id));
    }
    Ragnos.DOM.dispatchInputEvents(select);
  },
};

/* ==========================================================================
   4. Ragnos.UI: Toasts, Modals, Loading, and User Feedback
   ========================================================================== */
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

const ToastBottom = Swal.mixin({
  toast: true,
  position: "bottom-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

Ragnos.UI = {
  showToast(mensaje = "", tipo = "info", timer = 3000) {
    Toast.fire({
      icon: tipo,
      title: mensaje,
      timer: timer,
    });
  },

  showToastDown(mensaje = "", tipo = "info", timer = 3000) {
    ToastBottom.fire({
      icon: tipo,
      title: mensaje,
      timer: timer,
    });
  },

  showModal(html, encabezado = "", id = "miModal", onClose = null) {
    try {
      if (typeof html !== "string" && !(html instanceof HTMLElement)) {
        throw new Error("Invalid HTML content");
      }
      if (typeof id !== "string" || !id.trim()) {
        throw new Error("Invalid modal ID");
      }
      const modalId = id.trim();
      let modalElement = document.getElementById(modalId);

      if (!modalElement) {
        const modalTemplate = `
          <div id="${modalId}" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="${modalId}Label">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="${modalId}-modaltitle"></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="${modalId}-modalescondido"></div> 
                </div>
                <div class="modal-footer"></div>
              </div>
            </div>
          </div>`;

        document.body.insertAdjacentHTML("beforeend", modalTemplate);
        modalElement = document.getElementById(modalId);
      }

      const content = document.getElementById(`${modalId}-modalescondido`);
      const title = document.getElementById(`${modalId}-modaltitle`);
      if (html instanceof HTMLElement) {
        content.replaceChildren(html);
      } else {
        Ragnos.DOM.setHtml(content, html);
      }
      title.textContent = encabezado || "";

      if (modalElement.ragnosCloseHandler) {
        modalElement.removeEventListener("hidden.bs.modal", modalElement.ragnosCloseHandler);
      }
      modalElement.ragnosCloseHandler = () => {
        modalElement.ragnosCloseHandler = null;
        if (typeof onClose === "function") {
          try {
            onClose();
          } catch (error) {
            console.error("Error in modal close callback:", error);
          }
        }
      };
      modalElement.addEventListener("hidden.bs.modal", modalElement.ragnosCloseHandler, {
        once: true,
      });

      bootstrap.Modal.getOrCreateInstance(modalElement).show();
      return modalElement;
    } catch (error) {
      console.error("Error showing modal:", error);
      Ragnos.UI.showToast("Error al mostrar la ventana modal", "error");
      return null;
    }
  },

  cierraModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      console.warn(`Modal with ID "${modalId}" does not exist.`);
      return;
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    if (!modalElement.classList.contains("show")) {
      modal.dispose();
      modalElement.remove();
      return;
    }
    modalElement.addEventListener("hidden.bs.modal", () => {
      modal.dispose();
      modalElement.remove();
    }, { once: true });
    modal.hide();
  },

  mostrarCargando() {
    let divLoading = document.querySelector(".loading-container");
    if (!divLoading) {
      let loadingContainer = document.createElement("div");
      loadingContainer.classList.add("loading-container");
      let loading = document.createElement("div");
      loading.classList.add("loading");
      loadingContainer.appendChild(loading);

      loadingContainer.style.position = "fixed";
      loadingContainer.style.top = "0";
      loadingContainer.style.left = "0";
      loadingContainer.style.width = "100%";
      loadingContainer.style.height = "100%";
      loadingContainer.style.backgroundColor = "rgba(255, 255, 255, 0.8)";
      loadingContainer.style.display = "flex";
      loadingContainer.style.alignItems = "center";
      loadingContainer.style.justifyContent = "center";
      loadingContainer.style.zIndex = "9999";

      loading.style.border = "5px solid #f3f3f3";
      loading.style.borderTop = "5px solid #3498db";
      loading.style.borderRadius = "50%";
      loading.style.width = "50px";
      loading.style.height = "50px";
      loading.style.animation = "spin 2s linear infinite";
      document.body.appendChild(loadingContainer);
    }
  },

  ocultarCargando() {
    let loadingContainer = document.querySelector(".loading-container");
    if (loadingContainer) {
      loadingContainer.remove();
    }
  },

  shakeElement(el) {
    el = Ragnos.DOM.getElement(el);
    if (!el) return;
    el.classList.add("shake");
    setTimeout(() => el.classList.remove("shake"), 400);
  },

  printElement(target, title = document.title) {
    const element = Ragnos.DOM.getElement(target);
    if (!element) return false;

    const printable = element.cloneNode(true);
    const sourceControls = element.querySelectorAll("input, textarea, select");
    printable.querySelectorAll("input, textarea, select").forEach((control, index) => {
      const source = sourceControls[index];
      if (!source) return;
      if (control instanceof HTMLInputElement) {
        control.value = source.value;
        control.toggleAttribute("checked", source.checked);
      } else if (control instanceof HTMLTextAreaElement) {
        control.textContent = source.value;
      } else if (control instanceof HTMLSelectElement) {
        Array.from(control.options).forEach((option, optionIndex) => {
          option.selected = source.options[optionIndex]?.selected || false;
        });
      }
    });
    printable.querySelectorAll("script, iframe, object, embed").forEach((node) => node.remove());
    [printable, ...printable.querySelectorAll("*")].forEach((node) => {
      Array.from(node.attributes).forEach((attribute) => {
        if (attribute.name.toLowerCase().startsWith("on")) {
          node.removeAttribute(attribute.name);
        }
      });
    });

    const frame = document.createElement("iframe");
    frame.hidden = true;
    frame.title = "Print preview";
    document.body.append(frame);
    const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
      .map((node) => node.outerHTML)
      .join("");
    frame.srcdoc = `<!doctype html><html><head><title>${Ragnos.DOM.escapeHtml(title)}</title>${styles}</head><body>${printable.outerHTML}</body></html>`;
    frame.addEventListener(
      "load",
      () => {
        frame.contentWindow.focus();
        frame.contentWindow.print();
        setTimeout(() => frame.remove(), 1000);
      },
      { once: true },
    );
    return true;
  },
};

/* ==========================================================================
   5. Backward Compatibility Layer: Top-Level Global Aliases
   ========================================================================== */
window.onReady = Ragnos.DOM.onReady;
window.getElement = Ragnos.DOM.getElement;
window.getElements = Ragnos.DOM.getElements;
window.setHtml = Ragnos.DOM.setHtml;
window.dispatchInputEvents = Ragnos.DOM.dispatchInputEvents;
window.debounce = Ragnos.DOM.debounce;
window.escapeHtml = Ragnos.DOM.escapeHtml;
window.limitText = Ragnos.DOM.limitText;
window.moneyToNumber = Ragnos.DOM.moneyToNumber;
window.moneyFormat = Ragnos.DOM.moneyFormat;
window.serializeForm = Ragnos.DOM.serializeForm;
window.serializeParams = Ragnos.DOM.serializeParams;
window.trim = Ragnos.DOM.trim;
window.limpia = Ragnos.DOM.limpia;
window.inArray = Ragnos.DOM.inArray;

window.fixUrl = Ragnos.Http.fixUrl;
window.redirectTo = Ragnos.Http.redirectTo;
window.openInNew = Ragnos.Http.openInNew;
window.redirectByPost = Ragnos.Http.redirectByPost;
window.refreshPage = Ragnos.Http.refreshPage;
window.getValue = Ragnos.Http.getValue;
window.getObject = Ragnos.Http.getObject;
window.postFormData = Ragnos.Http.postFormData;
window.uploadObject = Ragnos.Http.uploadObject;
window.getSession = Ragnos.Http.getSession;
window.manejaError = Ragnos.Http.manejaError;

window.destroyDataTable = Ragnos.Table.destroyDataTable;
window.aplicarDebounceABusqueda = Ragnos.Table.aplicarDebounceABusqueda;
window.ponTablaPaginada = Ragnos.Table.ponTablaPaginada;
window.ponTotalesEnTabla = Ragnos.Table.ponTotalesEnTabla;
window.exportToExcel = Ragnos.Table.exportToExcel;
window.tablaCompleta = Ragnos.Table.tablaCompleta;
window.exportaTablaCompletaAExcel = Ragnos.Table.exportaTablaCompletaAExcel;
window.convertToTable = Ragnos.Table.convertToTable;
window.ponValorEnSelect = Ragnos.Table.ponValorEnSelect;
window.quitaTotaldeColumna = Ragnos.Table.quitaTotaldeColumna;
window.quitaTotaldeRenglon = Ragnos.Table.quitaTotaldeRenglon;

window.showToast = Ragnos.UI.showToast;
window.showToastDown = Ragnos.UI.showToastDown;
window.showModal = Ragnos.UI.showModal;
window.cierraModal = Ragnos.UI.cierraModal;
window.mostrarCargando = Ragnos.UI.mostrarCargando;
window.ocultarCargando = Ragnos.UI.ocultarCargando;
window.shakeElement = Ragnos.UI.shakeElement;
window.printElement = Ragnos.UI.printElement;
