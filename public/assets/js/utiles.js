// Set base_url if not defined
if (!base_url || typeof base_url !== "string") {
  try {
    const url = new URL(window.location.href);
    const pathSegments = url.pathname
      .replace(/^\/|\/$/g, "") // Quitar barras al inicio y al final
      .split("/")
      .filter(Boolean);

    base_url = `${url.protocol}//${url.host}/${encodeURIComponent(
      pathSegments[0] || ""
    )}/`;
  } catch (error) {
    console.error(
      "Error al establecer base_url. Usando raíz como fallback:",
      error
    );
    base_url = "/";
  }
}

/**
 * Ajusta una URL para que sea absoluta, combinándola con base_url si es necesario.
 *
 * @param {string} purl - La URL a procesar.
 * @returns {string} La URL completa y válida.
 */
function fixUrl(purl) {
  try {
    // Validar que purl sea una cadena no vacía
    if (typeof purl !== "string" || !purl.trim()) {
      console.warn("fixUrl: purl es inválido o está vacío.");
      return "";
    }

    // Verificar si la URL ya es absoluta
    const isAbsolute = /^https?:\/\//i.test(purl);
    if (isAbsolute || typeof base_url === "undefined") {
      return purl;
    }

    // Asegurar que base_url esté definido correctamente
    if (typeof base_url !== "string" || !base_url.trim()) {
      console.error("fixUrl: base_url no está definido o es inválido.");
      return purl;
    }

    // Normalizar base_url y purl para evitar duplicados de '/'
    const normalizedBaseUrl = base_url.replace(/\/+$/, ""); // Remover '/' al final
    const normalizedPath = purl.replace(/^\/+/, ""); // Remover '/' al inicio

    // Combinar base_url y purl con el separador adecuado
    return `${normalizedBaseUrl}/index.php/${normalizedPath}`;
  } catch (error) {
    console.error("fixUrl: Error procesando la URL:", error);
    return "";
  }
}

function redirectTo(purl) {
  setTimeout(function () {
    window.location.href = fixUrl(purl);
  }, 0);
}

/**
 * Opens a given URL in a new browser window.
 *
 * @param {string} purl - The URL to be opened.
 */
function openInNew(purl) {
  window.open(fixUrl(purl), "_new");
}

/**
 * Redirige al navegador a una URL específica usando una solicitud POST con parámetros opcionales.
 *
 * @param {string} purl - La URL a la que se redirigirá.
 * @param {Object} [parameters={}] - Un objeto con los parámetros a enviar como POST.
 * @param {boolean} [inNewTab=true] - Si se debe abrir en una nueva pestaña.
 * @returns {boolean} - Devuelve true si la redirección fue exitosa, false si ocurrió un error.
 */
function redirectByPost(purl, parameters = {}, inNewTab = true) {
  try {
    // Validar la URL
    if (typeof purl !== "string" || !purl.trim()) {
      console.error("redirectByPost: URL inválida.");
      return false;
    }

    // Obtener URL procesada
    const url = fixUrl(purl);

    // Crear el formulario
    const form = crearFormulario(url, parameters, inNewTab);

    // Enviar el formulario
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    return true;
  } catch (error) {
    console.error("redirectByPost: Error durante la redirección.", error);
    return false;
  }
}

/**
 * Crea un formulario para realizar una solicitud POST.
 *
 * @param {string} url - La URL de destino.
 * @param {Object} parameters - Los parámetros a enviar como POST.
 * @param {boolean} inNewTab - Si el formulario debe abrirse en una nueva pestaña.
 * @returns {HTMLFormElement} - El formulario generado.
 */
function crearFormulario(url, parameters, inNewTab) {
  const form = document.createElement("form");
  form.method = "post";
  form.action = url;

  // Establecer el destino
  if (inNewTab) {
    form.target = "_blank";
  }

  // Agregar token CSRF si está definido
  if (typeof csrfToken !== "undefined") {
    agregarCampoOculto(form, "csrf_token", csrfToken);
  }

  // Agregar los parámetros al formulario
  Object.entries(parameters).forEach(([key, value]) => {
    agregarCampoOculto(form, key, String(value));
  });

  return form;
}

/**
 * Agrega un campo oculto a un formulario.
 *
 * @param {HTMLFormElement} form - El formulario al que se agregará el campo.
 * @param {string} name - El nombre del campo.
 * @param {string} value - El valor del campo.
 */
function agregarCampoOculto(form, name, value) {
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = name;
  input.value = value;
  form.appendChild(input);
}

function trim(inputString) {
  return inputString.trim();
}

/**
 * Refreshes the current page by reloading it.
 */
function refreshPage() {
  location.reload();
}

$.fn.serializeObject = function () {
  if (!this.is("form")) {
    console.warn("serializeObject called on non-form element");
    return {};
  }

  const formData = {};
  const $form = $(this);

  // Handle regular form inputs
  $form.find("input, select, textarea").each(function () {
    const $input = $(this);
    const name = $input.attr("name");

    if (!name) return;

    const type = $input.attr("type");
    let value;

    // Handle different input types
    switch (type) {
      case "checkbox":
        value = $input.prop("checked");
        break;
      case "radio":
        if ($input.prop("checked")) {
          value = $input.val();
        }
        break;
      default:
        value = $input.val() || "";
    }

    // Handle multiple values for same name
    if (formData[name] !== undefined) {
      if (!Array.isArray(formData[name])) {
        formData[name] = [formData[name]];
      }
      if (value !== undefined) {
        formData[name].push(value);
      }
    } else if (value !== undefined) {
      formData[name] = value;
    }
  });

  return formData;
};

$.fn.shake = function (options) {
  let settings = {
    shakes: 2,
    distance: 10,
    duration: 400,
  };
  if (options) {
    $.extend(settings, options);
  }
  let pos;
  return this.each(function () {
    let $this = $(this);
    pos = $this.css("position");
    if (!pos || pos === "static") {
      $this.css("position", "relative");
    }
    for (let x = 1; x <= settings.shakes; x++) {
      $this
        .animate(
          {
            left: settings.distance * -1,
          },
          settings.duration / settings.shakes / 4
        )
        .animate(
          {
            left: settings.distance,
          },
          settings.duration / settings.shakes / 2
        )
        .animate(
          {
            left: 0,
          },
          settings.duration / settings.shakes / 4
        );
    }
  });
};

$(document)
  .ajaxStart(function () {
    mostrarCargando();
  })
  .ajaxStop(function () {
    ocultarCargando();
  });

/**
 * Adds totals to a table either in the final row, final column, or both.
 *
 * @param {jQuery} targetTable - The jQuery object representing the table to modify.
 * @param {boolean} [enRenglonFinal=false] - If true, adds a total row at the end of the table.
 * @param {boolean} [enColumnaFinal=true] - If true, adds a total column at the end of each row.
 */
function ponTotalesEnTabla(
  targetTable,
  enRenglonFinal = false,
  enColumnaFinal = true
) {
  if (!targetTable || !targetTable.length) {
    console.error("Invalid table element");
    return;
  }

  const $tbody = targetTable.find("tbody");
  const $rows = $tbody.find("tr");
  let columnCount = $rows.first().find("td").length;

  if (enColumnaFinal) {
    agregarColumnaTotal(targetTable, $rows, columnCount);
    columnCount += 1;
  }

  if (enRenglonFinal) {
    agregarRenglonTotal($tbody, $rows, columnCount);
  }
}

/**
 * Agrega un renglón con totales al final de la tabla.
 *
 * @param {jQuery} $tbody - Cuerpo de la tabla donde se agregará el renglón.
 * @param {jQuery} $rows - Filas de la tabla usadas para calcular los totales.
 * @param {number} columnCount - Número de columnas en la tabla.
 */
function agregarRenglonTotal($tbody, $rows, columnCount) {
  if ($rows.length <= 1) return;

  const $totalRow = $("<tr>").appendTo($tbody);

  for (let i = 0; i < columnCount; i++) {
    const cellContent = i === 0 ? "Total" : calcularColumnaTotal($rows, i);
    $("<td>")
      .addClass("totalt")
      .css("font-weight", "bold")
      .text(cellContent)
      .appendTo($totalRow);
  }
}

/**
 * Agrega una columna con totales al final de cada fila.
 *
 * @param {jQuery} targetTable - Tabla a la que se agregará la columna de totales.
 * @param {jQuery} $rows - Filas de la tabla usadas para calcular los totales.
 * @param {number} columnCount - Número de columnas en la tabla.
 */
function agregarColumnaTotal(targetTable, $rows, columnCount) {
  if (columnCount <= 2) return;

  // Agregar encabezado "Total" en la última columna
  targetTable
    .find("thead tr")
    .append(
      $("<th>").addClass("totalt").css("font-weight", "bold").text("Total")
    );

  // Calcular y agregar el total para cada fila
  $rows.each(function () {
    const $row = $(this);
    const total = calcularFilaTotal($row, columnCount);

    $row.append(
      $("<td>").addClass("totalt").css("font-weight", "bold").text(total)
    );
  });
}

/**
 * Calcula el total de una columna específica en las filas dadas.
 *
 * @param {jQuery} $rows - Las filas de la tabla.
 * @param {number} colIndex - Índice de la columna a calcular.
 * @returns {string|number} El total de la columna, formateado si aplica.
 */
function calcularColumnaTotal($rows, colIndex) {
  let esDinero = false;
  const total = $rows.toArray().reduce((sum, row) => {
    const valor = $(row).find(`td:eq(${colIndex})`).text();
    if (valor.startsWith("$")) esDinero = true;
    return sum + (moneyToNumber(valor) || 0);
  }, 0);

  return formatearTotal(total, esDinero);
}

/**
 * Calcula el total de una fila específica.
 *
 * @param {jQuery} $row - La fila a calcular.
 * @param {number} columnCount - Número de columnas en la tabla.
 * @returns {string|number} El total de la fila, formateado si aplica.
 */
function calcularFilaTotal($row, columnCount) {
  let esDinero = false;
  const total = $row
    .find("td")
    .toArray()
    .slice(1, columnCount - 1) // Ignorar la primera y última columna si aplica
    .reduce((sum, cell) => {
      const valor = $(cell).text();
      if (valor.startsWith("$")) esDinero = true;
      return sum + (moneyToNumber(valor) || 0);
    }, 0);

  return formatearTotal(total, esDinero);
}

/**
 * Formatea un valor total como texto, manejando números y valores monetarios.
 *
 * @param {number} total - El valor total a formatear.
 * @param {boolean} esDinero - Si el valor debe ser formateado como dinero.
 * @returns {string} El total formateado.
 */
function formatearTotal(total, esDinero) {
  if (esDinero) {
    return moneyFormat(total);
  }
  return Number.isInteger(total) ? total.toFixed(0) : total.toFixed(2);
}

/**
 * Exports HTML table content to an Excel file
 * @param {string} fileName - Name of the file to be downloaded (without extension)
 * @param {string} htmlContent - HTML table content to export
 * @returns {boolean} - True if export was successful, false otherwise
 */
function exportToExcel(fileName, htmlContent) {
  try {
    // Input validation
    if (!fileName || typeof fileName !== "string") {
      throw new Error("Invalid file name");
    }
    if (!htmlContent || typeof htmlContent !== "string") {
      throw new Error("Invalid HTML content");
    }

    // Constants
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

    // Convert content to base64
    const base64 = (s) => window.btoa(unescape(encodeURIComponent(s)));

    // Format template
    const format = (template, context) =>
      template.replace(/{(\w+)}/g, (match, key) => context[key] || "");

    // Create download link
    const link = document.createElement("a");
    link.download = `${fileName.trim()}.xls`;
    link.href =
      EXCEL_URI +
      base64(
        format(EXCEL_TEMPLATE, {
          worksheet: "Worksheet",
          table: htmlContent,
        })
      );

    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    return true;
  } catch (error) {
    console.error("Error exporting to Excel:", error);
    showToast("Error al exportar a Excel", "error");
    return false;
  }
}

/**
 * Generates a complete HTML table from a DataTable instance.
 *
 * @param {string} tablae - The selector for the table element.
 * @returns {string} The generated HTML table as a string.
 * @throws {Error} If the table element is invalid or the DataTable is not initialized.
 */
function tablaCompleta(tablae) {
  try {
    // Validate input
    if (!tablae || !$(tablae).length) {
      throw new Error("Invalid table element");
    }

    // Get DataTable instance
    const table = $(tablae).DataTable();
    if (!table) {
      throw new Error("DataTable not initialized");
    }

    // Get table data and headers
    const data = table.rows().data().toArray();
    const columnHeaders = table.columns().header().toArray();
    const fieldNames = columnHeaders.map((header) => $(header).text().trim());

    // Build table HTML with template literals
    let tableHtml = '<table border="1">';

    // Add header row
    tableHtml += "<thead><tr>";
    fieldNames.slice(0, -1).forEach((fieldName) => {
      tableHtml += `<th style="background-color: #f2f2f2; padding: 8px;">${escapeHtml(
        fieldName
      )}</th>`;
    });
    tableHtml += "</tr></thead>";

    // Add data rows
    tableHtml += "<tbody>";
    data.forEach((row) => {
      tableHtml += "<tr>";
      Object.values(row).forEach((value) => {
        tableHtml += `<td style="padding: 6px;">${escapeHtml(
          String(value)
        )}</td>`;
      });
      tableHtml += "</tr>";
    });

    tableHtml += "</tbody></table>";
    return tableHtml;
  } catch (error) {
    console.error("Error in tablaCompleta:", error);
    showToast("Error al generar la tabla", "error");
    return "";
  }
}

// Helper function to escape HTML special characters
function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/**
 * Exports the complete table to an Excel file.
 *
 * @param {string} fileName - The name of the Excel file to be created.
 * @param {HTMLElement} tablae - The table element to be exported.
 */
function exportaTablaCompletaAExcel(fileName, tablae) {
  let tableHtml = tablaCompleta(tablae);
  exportToExcel(fileName, tableHtml);
}

/**
 * Initializes a paginated DataTable with the specified options.
 *
 * @param {string} tableSelector - The selector for the table element to apply DataTables to.
 * @param {Object} [optionsextra={}] - Additional options to extend the default DataTables configuration.
 * @param {string} [optionsextra.pagingType="numbers"] - The type of pagination to use.
 * @param {Array} [optionsextra.order=[]] - The initial order of the table.
 * @param {Object} [optionsextra.oLanguage] - Language options for DataTables.
 * @param {string} [optionsextra.oLanguage.sProcessing="Procesando..."] - Text displayed while processing.
 * @param {string} [optionsextra.oLanguage.sLengthMenu="Mostrar _MENU_ registros"] - Text for the length menu.
 * @param {string} [optionsextra.oLanguage.sZeroRecords="No se encontraron registros"] - Text when no records are found.
 * @param {string} [optionsextra.oLanguage.sInfo="Mostrando desde _START_ hasta _END_ de _TOTAL_ registros"] - Info text.
 * @param {string} [optionsextra.oLanguage.sInfoEmpty="Mostrando desde 0 hasta 0 de 0 registros"] - Info text when empty.
 * @param {string} [optionsextra.oLanguage.sInfoFiltered=""] - Info text for filtered results.
 * @param {string} [optionsextra.oLanguage.sInfoPostFix=""] - Postfix for info text.
 * @param {string} [optionsextra.oLanguage.sSearch="Buscar:"] - Text for the search input.
 * @param {string} [optionsextra.oLanguage.sUrl=""] - URL for language file.
 * @param {Object} [optionsextra.oLanguage.oPaginate] - Pagination text options.
 * @param {string} [optionsextra.oLanguage.oPaginate.sFirst="Primero"] - Text for the "First" button.
 * @param {string} [optionsextra.oLanguage.oPaginate.sPrevious="Anterior"] - Text for the "Previous" button.
 * @param {string} [optionsextra.oLanguage.oPaginate.sNext="Siguiente"] - Text for the "Next" button.
 * @param {string} [optionsextra.oLanguage.oPaginate.sLast="Último"] - Text for the "Last" button.
 */
function ponTablaPaginada(tableSelector, optionsextra = {}) {
  // Opciones de configuración de DataTables
  let options = {
    pagingType: "numbers",
    order: [],
    oLanguage: {
      sProcessing: "Procesando...",
      sLengthMenu: "Mostrar _MENU_ registros",
      sZeroRecords: "No se encontraron registros",
      sInfo: "Mostrando desde _START_ hasta _END_ de _TOTAL_ registros",
      sInfoEmpty: "Mostrando desde 0 hasta 0 de 0 registros",
      sInfoFiltered: "",
      sInfoPostFix: "",
      sSearch: "Buscar:",
      sUrl: "",
      oPaginate: {
        sFirst: "Primero",
        sPrevious: "Anterior",
        sNext: "Siguiente",
        sLast: "Último",
      },
    },
  };

  $.extend(options, optionsextra);

  // Aplicar configuración de DataTables a la tabla
  $(tableSelector).DataTable(options);
}

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

/**
 * Displays a toast notification with the specified message, type, and duration.
 *
 * @param {string} [mensaje=""] - The message to display in the toast.
 * @param {string} [tipo="info"] - The type of the toast (e.g., "info", "success", "warning", "error").
 * @param {number} [timer=3000] - The duration the toast should be displayed in milliseconds.
 */
function showToast(mensaje = "", tipo = "info", timer = 3000) {
  Toast.fire({
    icon: tipo,
    title: mensaje,
    timer: timer,
  });
}

/**
 * Displays a toast notification at the bottom of the screen.
 *
 * @param {string} [mensaje=""] - The message to display in the toast.
 * @param {string} [tipo="info"] - The type of the toast (e.g., "success", "error", "warning", "info").
 * @param {number} [timer=3000] - The duration in milliseconds for which the toast should be visible.
 */
function showToastDown(mensaje = "", tipo = "info", timer = 3000) {
  ToastBottom.fire({
    icon: tipo,
    title: mensaje,
    timer: timer,
  });
}

/**
 * Displays a modal with the specified HTML content and header.
 *
 * @param {string|jQuery} html - The HTML content to display inside the modal. Can be a string or a jQuery object.
 * @param {string} [encabezado=""] - The header text for the modal. Defaults to an empty string.
 * @param {string} [id="miModal"] - The ID for the modal. Defaults to "miModal".
 * @param {Function} [onClose=null] - A callback function to execute when the modal is closed. Defaults to null.
 * @returns {jQuery|null} - The jQuery object representing the modal, or null if an error occurred.
 */
function showModal(html, encabezado = "", id = "miModal", onClose = null) {
  try {
    // Input validation
    if (typeof html !== "string" && !(html instanceof jQuery)) {
      throw new Error("Invalid HTML content");
    }
    if (typeof id !== "string" || !id.trim()) {
      throw new Error("Invalid modal ID");
    }

    // Create modal if it doesn't exist
    const modalId = id.trim();
    const $existingModal = $(`#${modalId}`);

    if (!$existingModal.length) {
      const modalTemplate = `
        <div id="${modalId}" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
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

      $("body").append(modalTemplate);
    }

    // Update modal content
    const $modal = $(`#${modalId}`);
    const $content = $(`#${modalId}-modalescondido`);
    const $title = $(`#${modalId}-modaltitle`);

    $content.html(html);
    $title.text(encabezado || "");

    // Handle modal events
    $modal.off("hidden.bs.modal").on("hidden.bs.modal", function () {
      if (typeof onClose === "function") {
        try {
          onClose();
        } catch (error) {
          console.error("Error in modal close callback:", error);
        }
      }
      $modal.off("hidden.bs.modal");
    });

    // Show modal
    $modal.modal("show");

    return $modal;
  } catch (error) {
    console.error("Error showing modal:", error);
    showToast("Error al mostrar la ventana modal", "error");
    return null;
  }
}

/**
 * Closes a Bootstrap modal with the given ID.
 *
 * @param {string} modalId - The ID of the modal to close.
 */
function cierraModal(modalId) {
  $("#" + modalId).modal("hide");
}

/**
 * Converts input data to an HTML table.
 *
 * @param {Array|Object} dataInput - The input data to be converted to a table. Can be an array or an object.
 * @param {Object} [options={}] - Optional settings for table conversion.
 * @param {string} [options.tableClass="table"] - CSS class to be applied to the table.
 * @param {boolean} [options.stripHtml=true] - Whether to strip HTML tags from the input data.
 * @param {number} [options.maxCellLength=100] - Maximum length of cell content. Longer content will be truncated.
 * @param {string} [options.emptyText="-"] - Text to display for empty cells.
 * @returns {string} The HTML string representing the table.
 */
function convertToTable(dataInput, options = {}) {
  // Input validation
  if (!dataInput) {
    console.error("Invalid input data");
    return "";
  }

  // Default options
  const defaultOptions = {
    tableClass: "table",
    stripHtml: true,
    maxCellLength: 100,
    emptyText: "-",
  };

  const settings = { ...defaultOptions, ...options };

  try {
    return Array.isArray(dataInput)
      ? arrayToTable(dataInput, settings)
      : objectToTable(dataInput, settings);
  } catch (error) {
    console.error("Error converting data to table:", error);
    return "";
  }
}

function objectToTable(obj, settings) {
  const keys = Object.keys(obj);
  if (keys.length === 0) return settings.emptyText;

  return `
    <table class="${escapeHtml(settings.tableClass)}">
      <thead>
        ${createHeaderRow(keys, settings)}
      </thead>
      <tbody>
        ${objectToRow(obj, keys, settings)}
      </tbody>
    </table>
  `;
}

function arrayToTable(array, settings) {
  if (!array.length) return settings.emptyText;

  const keys = Object.keys(array[0]);
  if (keys.length === 0) return settings.emptyText;

  return `
    <table class="${escapeHtml(settings.tableClass)}">
      <thead>
        ${createHeaderRow(keys, settings)}
      </thead>
      <tbody>
        ${array.map((item) => objectToRow(item, keys, settings)).join("")}
      </tbody>
    </table>
  `;
}

function objectToRow(obj, keys, settings) {
  return `
    <tr>
      ${keys
        .map((key) => `<td>${formatCellContent(obj[key], settings)}</td>`)
        .join("")}
    </tr>
  `;
}

function createHeaderRow(keys, settings) {
  return `
    <tr>
      ${keys
        .map((key) => `<th>${formatCellContent(key, settings)}</th>`)
        .join("")}
    </tr>
  `;
}

function formatCellContent(content, settings) {
  if (content === null || content === undefined) {
    return settings.emptyText;
  }

  let formatted = String(content);

  // Truncate long content
  if (settings.maxCellLength && formatted.length > settings.maxCellLength) {
    formatted = `${formatted.substring(0, settings.maxCellLength)}...`;
  }

  // Strip HTML if needed
  if (settings.stripHtml) {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = formatted;
    formatted = tempDiv.textContent || tempDiv.innerText || "";
  }

  return escapeHtml(formatted);
}

/**
 * Limits the text input to a specified number of characters.
 *
 * @param {HTMLInputElement} limitField - The input field to limit.
 * @param {number} limitNum - The maximum number of characters allowed.
 */
function limitText(limitField, limitNum) {
  if (limitField.value.length > limitNum) {
    limitField.value = limitField.value.slice(0, limitNum);
  }
}

/**
 * Updates a select element with a new option and sets it as selected.
 *
 * @param {jQuery} elemento - The jQuery object representing the select element.
 * @param {string|number} id - The value of the new option to be added and selected.
 * @param {string} texto - The text content of the new option to be added.
 */
function ponValorEnSelect(elemento, id, texto) {
  elemento.find("option[value='" + id + "']").remove();
  elemento.find("option").prop("selected", false).removeAttr("selected");
  let newOption = $("<option></option>")
    .val(id)
    .text(texto)
    .attr("selected", "selected");
  elemento.append(newOption).val(id).trigger("change");
}

function limpia(cadena) {
  return cadena.replace("-", " ").replace(/\s+/g, " ").trim();
}

function inArray(element, array) {
  return array.includes(element);
}

/**
 * Performs an asynchronous AJAX POST request to the specified URL with the given parameters.
 *
 * @async
 * @function getValue
 * @param {string} url - The URL to which the request is sent.
 * @param {Object} [params={}] - The parameters to be sent with the request.
 * @param {number} [params.timeout=1200000] - The timeout for the request in milliseconds.
 * @param {function} callback - The callback function to handle the response or error.
 * @param {string} callback.response - The response text from the server.
 * @param {Object} [callback.errorInfo] - The error information if the request fails.
 * @param {string} [callback.errorInfo.error] - The error message.
 * @param {number} [callback.errorInfo.status] - The HTTP status code.
 * @param {Object} [callback.errorInfo.jqXHR] - The jQuery XMLHttpRequest object.
 * @throws {Error} If the AJAX request fails.
 */
async function getValue(url, params = {}, callback) {
  // Configuración por defecto
  const config = {
    timeout: params.timeout || 1200000,
    retryAttempts: params.retryAttempts || 1,
    retryDelay: params.retryDelay || 1000,
  };

  // Función principal que realiza la petición
  const makeRequest = async () => {
    let attempts = 0;
    let lastError = null;

    while (attempts < config.retryAttempts) {
      try {
        const response = await $.ajax({
          url: fixUrl(url),
          type: "POST",
          data: params,
          dataType: "text",
          timeout: config.timeout,
        });

        return { response, error: null };
      } catch (error) {
        lastError = {
          error: error.statusText || "Error desconocido",
          status: error.status || 0,
          jqXHR: error,
        };

        attempts++;

        if (attempts < config.retryAttempts) {
          console.warn(`Reintento ${attempts} de ${config.retryAttempts}`);
          await new Promise((resolve) =>
            setTimeout(resolve, config.retryDelay * attempts)
          );
          continue;
        }

        console.error("Error al realizar la solicitud AJAX:", lastError);
        manejaError(error);
        return { response: null, error: lastError };
      }
    }
  };

  // Si se proporciona callback, usamos el estilo callback
  if (typeof callback === "function") {
    const result = await makeRequest();
    callback(result.response, result.error);
    return;
  }

  // Si no hay callback, retornamos una promesa
  const result = await makeRequest();
  if (result.error) {
    throw result.error;
  }
  return result.response;
}

/**
 * Maneja los errores de las solicitudes AJAX y muestra mensajes apropiados.
 *
 * @param {Object} jqXHR - El objeto jqXHR de la solicitud AJAX.
 * @param {number} jqXHR.status - El código de estado HTTP de la respuesta.
 */
function manejaError(jqXHR) {
  const errorMessages = {
    401: "Su sesión ha expirado, por favor inicie sesión nuevamente",
    403: "No tiene permiso para realizar esta acción",
    404: "No se encontró la página solicitada",
    500: "Error interno del servidor",
  };
  const message = errorMessages[jqXHR.status] || "Error desconocido";
  showToast(message, "error");

  if (jqXHR.status == 401) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: errorMessages[jqXHR.status],
      timer: 2000,
      didClose: () => {
        window.location.href = fixUrl("admin/login");
      },
    });
  }
}

/**
 * Asynchronously retrieves an object from a given URL with specified parameters.
 *
 * @param {string} purl - The URL to send the request to.
 * @param {Object} pparameters - The parameters to include in the request.
 * @param {function(Object|null, Error|null): void} callbackfunction - The callback function to handle the response.
 *        The callback receives two arguments:
 *        - The parsed object if the request is successful and the response is valid JSON, otherwise null.
 *        - An error object if there is an error during the request or parsing, otherwise null.
 * @returns {Promise<void>} A promise that resolves when the request is complete.
 */
async function getObject(purl, pparameters, callbackfunction) {
  // Función auxiliar para procesar la respuesta
  const processResponse = async (response) => {
    try {
      const obj = JSON.parse(response);
      return { result: obj, error: null };
    } catch (error) {
      return { result: null, error };
    }
  };

  // Si no hay callback, usamos promesas
  if (typeof callbackfunction !== "function") {
    try {
      const response = await getValue(purl, pparameters);
      const { result, error } = await processResponse(response);
      if (error) {
        throw error;
      }
      return result;
    } catch (error) {
      throw error;
    }
  }

  // Si hay callback, mantenemos el comportamiento original
  try {
    await getValue(purl, pparameters, async (response, errors) => {
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
}

async function getSession() {
  let o = await getObject("admin/sess", {});
  return o;
}

function mostrarCargando() {
  let divLoading = document.querySelector(".loading-container");
  if (!divLoading) {
    let loadingContainer = document.createElement("div");
    loadingContainer.classList.add("loading-container");
    // Crear el elemento de la animación de carga
    let loading = document.createElement("div");
    loading.classList.add("loading");
    loadingContainer.appendChild(loading);

    // Establecer estilos para el contenedor y la animación
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
}

function ocultarCargando() {
  let loadingContainer = document.querySelector(".loading-container");
  if (loadingContainer) {
    loadingContainer.remove();
  }
}

/**
 * Formats a number as USD currency string
 * @param {number|string} amt - The amount to format
 * @returns {string} The formatted amount with USD currency symbol ($)
 * @example
 * moneyFormat(123.45) // Returns "$123.45"
 * moneyFormat("123.45") // Returns "$123.45"
 * moneyFormat("$123.45") // Returns "$123.45" (unchanged)
 */
function moneyFormat(amt, currency = "USD") {
  // If the amount already has a currency symbol, return it as is
  if (amt && amt.toString().includes("$")) {
    return amt;
  }

  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency,
  }).format(amt);
}

/**
 * Converts a formatted money string to a number.
 *
 * @param {string} amt - The money string to be converted. It may contain currency symbols, commas, or other non-numeric characters.
 * @returns {number} The numeric value of the money string.
 */
function moneyToNumber(amt) {
  return parseFloat(amt.replace(/[^0-9.-]+/g, ""));
}
