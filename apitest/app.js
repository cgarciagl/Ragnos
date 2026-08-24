/**
 * ═══════════════════════════════════════════════════════════════
 *  Ragnos API Test — AlpineJS SPA Application
 *  Tests the API mode of Ragnos RDatasetController controllers
 * ═══════════════════════════════════════════════════════════════
 */

// ── Configuration ───────────────────────────────────────────────
const API_CONFIG = window.RAGNOS_API_CONFIG || {
    apiBase: '../content/index.php',
    openApiUrl: '../content/index.php/api/openapi.json',
    pageSize: 10,
    requestHistoryLimit: 20
};
const API_BASE = API_CONFIG.apiBase;
const OPENAPI_URL = API_CONFIG.openApiUrl;
const ITEMS_PER_PAGE = API_CONFIG.pageSize || 10;

// ── Module config map (endpoint path, columns, id field) ────────
const MODULE_CONFIG = {
    pagos: {
        path: 'tienda/pagos',
        idField: 'idPayment',
        columns: ['customerNumber', 'checkNumber', 'paymentDate', 'amount'],
        title: 'Pagos',
        icon: 'bi-credit-card-2-front-fill'
    },
    clientes: {
        path: 'tienda/clientes',
        idField: 'customerNumber',
        columns: ['customerName', 'Contacto', 'salesRepEmployeeNumber'],
        title: 'Clientes',
        icon: 'bi-people-fill'
    },
    productos: {
        path: 'tienda/productos',
        idField: 'productCode',
        columns: ['productName', 'productCode', 'productLine', 'productVendor', 'quantityInStock', 'MSRP'],
        title: 'Productos',
        icon: 'bi-box-seam-fill'
    },
    empleados: {
        path: 'tienda/empleados',
        idField: 'employeeNumber',
        columns: ['nombreCompleto', 'employeeNumber', 'officeCode', 'reportsTo'],
        title: 'Empleados',
        icon: 'bi-person-badge-fill'
    },
    oficinas: {
        path: 'tienda/oficinas',
        idField: 'officeCode',
        columns: ['nombreCiudad', 'state', 'territory'],
        title: 'Oficinas',
        icon: 'bi-building-fill'
    },
    lineas: {
        path: 'tienda/lineas',
        idField: 'productLine',
        columns: ['productLine', 'textDescription'],
        title: 'Líneas de Productos',
        icon: 'bi-tags-fill'
    }
};

function humanizeResourceName(value) {
    return String(value || '')
        .split(/[-_/]/)
        .filter(Boolean)
        .map(part => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

function resourceIdFromPath(path) {
    return String(path || '').split('/').filter(Boolean).pop() || 'resource';
}

function schemaNameFromOperation(operation) {
    const ref = operation?.responses?.['200']?.content?.['application/json']?.schema
        ?.properties?.data?.items?.$ref;
    return ref ? ref.split('/').pop() : '';
}

function schemaFields(spec, operation) {
    const schemaName = schemaNameFromOperation(operation);
    const schema = schemaName ? spec?.components?.schemas?.[schemaName] : null;
    return schema?.properties || {};
}

function resourceConfigFromOpenApi(spec, path, operation) {
    const id = resourceIdFromPath(path);
    const tag = operation?.tags?.[0] || humanizeResourceName(id);
    const fields = schemaFields(spec, operation);
    const columns = Object.keys(fields).filter(field => !fields[field]?.readOnly).slice(0, 8);
    const idField = Object.keys(fields).find(field => fields[field]?.readOnly) || 'id';

    return {
        id,
        path: path.replace(/^\//, ''),
        title: tag,
        icon: 'bi-braces',
        columns,
        fields,
        idField,
        required: spec?.components?.schemas?.[schemaNameFromOperation(operation)]?.required || [],
        schemaName: schemaNameFromOperation(operation),
        supportsCrud: Boolean(spec.paths[`${path}/save`]),
        supportsDelete: Boolean(spec.paths[`${path}/delete/{id}`]),
        supportsHistory: Boolean(spec.paths[`${path}/history/{id}`]),
        generated: true
    };
}

function discoverResources(spec) {
    const resources = {};
    Object.entries(spec?.paths || {}).forEach(([path, operations]) => {
        if (!operations?.get || path.includes('{') || path.startsWith('/admin/')) return;
        const config = resourceConfigFromOpenApi(spec, path, operations.get);
        const existing = MODULE_CONFIG[config.id];
        resources[config.id] = existing
            ? { ...config, ...existing, fields: config.fields, generated: false }
            : config;
    });
    Object.entries(MODULE_CONFIG).forEach(([id, config]) => {
        resources[id] = resources[id] || { id, ...config, generated: false };
    });
    return resources;
}

function sanitiseInspectorValue(value) {
    if (!value || typeof value !== 'object') return value;
    const copy = Array.isArray(value) ? [...value] : { ...value };
    ['token', 'pword', 'password', 'Authorization'].forEach(key => {
        if (key in copy) copy[key] = '••••••••';
    });
    return copy;
}

function recordApiTraffic(entry) {
    const root = document.body?._x_dataStack?.[0];
    if (root?.recordRequest) root.recordRequest(entry);
}

// ── API Helper ──────────────────────────────────────────────────

/**
 * Makes an API call to Ragnos backend.
 * @param {string} endpoint - Relative path (e.g., 'tienda/pagos')
 * @param {object} options  - { method, body, token, params }
 * @returns {Promise<{ok: boolean, status: number, data: object}>}
 */
async function apiCall(endpoint, options = {}) {
    const { method = 'GET', body = null, token = null, params = {} } = options;
    const startedAt = performance.now();

    let url = `${API_BASE}/${endpoint}`;

    // Append query parameters
    const searchParams = new URLSearchParams();
    for (const [key, val] of Object.entries(params)) {
        if (val !== null && val !== undefined && val !== '') {
            searchParams.set(key, val);
        }
    }
    const qs = searchParams.toString();
    if (qs) url += (url.includes('?') ? '&' : '?') + qs;

    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const fetchOptions = { method, headers };

    if (body && (method === 'POST' || method === 'PUT')) {
        headers['Content-Type'] = 'application/json';
        fetchOptions.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(url, fetchOptions);
        let data;
        const contentType = response.headers.get('Content-Type') || '';
        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            // Try to parse as JSON anyway
            try { data = JSON.parse(text); } catch { data = { raw: text }; }
        }

        let status = response.status;
        let ok = response.ok;

        // Ragnos API mode sometimes returns {"error": "Unauthorized"} with 200 OK
        if (data && data.error === 'Unauthorized') {
            status = 401;
            ok = false;
        }

        recordApiTraffic({
            method,
            url,
            status,
            ok,
            duration: Math.round(performance.now() - startedAt),
            requestBody: sanitiseInspectorValue(body),
            responseBody: sanitiseInspectorValue(data),
            headers: { ...headers, Authorization: token ? 'Bearer ••••••••' : undefined }
        });

        return { ok, status, data };
    } catch (err) {
        recordApiTraffic({
            method,
            url,
            status: 0,
            ok: false,
            duration: Math.round(performance.now() - startedAt),
            requestBody: sanitiseInspectorValue(body),
            responseBody: { error: err.message },
            headers: { ...headers, Authorization: token ? 'Bearer ••••••••' : undefined }
        });
        return { ok: false, status: 0, data: { error: err.message } };
    }
}

// ── Utility: Pagination helper ──────────────────────────────────
function buildPaginationPages(currentPage, totalPages, maxVisible = 5) {
    const pages = [];
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages, start + maxVisible - 1);
    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1);
    }
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
}

// ── Utility: Format money ───────────────────────────────────────
function formatMoney(value) {
    const num = parseFloat(value);
    if (isNaN(num)) return value;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ═══════════════════════════════════════════════════════════════
//  MAIN APPLICATION (Alpine data)
// ═══════════════════════════════════════════════════════════════
function app() {
    return {
        // ── Auth state ──
        isAuthenticated: false,
        token: '',
        userId: '',
        userName: '',
        userGroup: '',
        loginForm: { usuario: '', pword: '' },
        loginError: '',
        loginLoading: false,

        // ── UI state ──
        currentModule: 'dashboard',
        sidebarCollapsed: false,
        appTheme: 'dark',
        resourceConfigs: { ...MODULE_CONFIG },
        resourceCatalog: Object.entries(MODULE_CONFIG).map(([id, config]) => ({ id, ...config })),
        openApiLoading: false,
        openApiError: '',
        openApiDocument: null,
        openApiUiUrl: API_CONFIG.swaggerUrl || '#',
        requestHistory: [],
        selectedRequest: null,
        inspectorOpen: false,

        // ── Toasts ──
        toasts: [],

        get moduleTitle() {
            if (this.currentModule === 'dashboard') return 'Dashboard';
            return this.resourceConfigs[this.currentModule]?.title || this.currentModule;
        },

        // ── Init ──
        init() {
            // Restore theme
            const savedTheme = localStorage.getItem('ragnos_api_theme') || 'dark';
            this.appTheme = savedTheme;
            document.documentElement.setAttribute('data-bs-theme', savedTheme);

            // Migrate the old persistent session once, then keep tokens per tab.
            const saved = sessionStorage.getItem('ragnos_api_session')
                || localStorage.getItem('ragnos_api_session');
            if (saved) {
                try {
                    const session = JSON.parse(saved);
                    this.token = session.token;
                    this.userId = session.userId;
                    this.userName = session.userName || '';
                    this.userGroup = session.userGroup || '';
                    this.isAuthenticated = true;
                    sessionStorage.setItem('ragnos_api_session', saved);
                    localStorage.removeItem('ragnos_api_session');
                } catch { /* skip */ }
            }

            // Create Alpine store accessible by child components
            Alpine.store('app', {
                token: this.token,
                currentModule: this.currentModule,
                resources: this.resourceConfigs
            });

            this.$watch('token', val => Alpine.store('app').token = val);
            this.$watch('currentModule', val => Alpine.store('app').currentModule = val);
            this.loadOpenApi();
        },

        async loadOpenApi() {
            this.openApiLoading = true;
            this.openApiError = '';
            const result = await apiCall('api/openapi.json', { token: this.token });
            this.openApiLoading = false;

            if (!result.ok || !result.data?.openapi) {
                this.openApiError = result.data?.error || 'No se pudo cargar la especificación OpenAPI';
                return;
            }

            this.openApiDocument = result.data;
            this.resourceConfigs = discoverResources(result.data);
            this.resourceCatalog = Object.values(this.resourceConfigs);
            Alpine.store('app').resources = this.resourceConfigs;
        },

        // ── Login ──
        async login() {
            this.loginError = '';
            this.loginLoading = true;

            const result = await apiCall('admin/login', {
                method: 'POST',
                body: {
                    usuario: this.loginForm.usuario,
                    pword: this.loginForm.pword
                }
            });

            this.loginLoading = false;

            if (result.ok && result.data?.token) {
                this.token = result.data.token;
                this.userId = result.data.user_id || result.data.userId || '?';
                this.userName = result.data.user_name || '';
                this.userGroup = result.data.user_group || '';
                this.isAuthenticated = true;

                sessionStorage.setItem('ragnos_api_session', JSON.stringify({
                    token: this.token,
                    userId: this.userId,
                    userName: this.userName,
                    userGroup: this.userGroup
                }));

                Alpine.store('app').token = this.token;
                this.loadOpenApi();
                this.addToast('Sesión iniciada exitosamente', 'success');
            } else {
                // Parse validation errors from Ragnos API response
                if (result.data?.errors && typeof result.data.errors === 'object') {
                    this.loginError = Object.values(result.data.errors).join(' ');
                } else {
                    this.loginError = result.data?.error || result.data?.message || 'Error de autenticación';
                }
            }
        },

        // ── Logout ──
        logout() {
            this.isAuthenticated = false;
            this.token = '';
            this.userId = '';
            this.userName = '';
            this.userGroup = '';
            this.currentModule = 'dashboard';
            sessionStorage.removeItem('ragnos_api_session');
            localStorage.removeItem('ragnos_api_session');
            Alpine.store('app').token = '';
            this.loginForm = { usuario: '', pword: '' };
        },

        // ── Navigation ──
        switchModule(mod) {
            this.currentModule = mod;
            // On mobile, collapse sidebar
            if (window.innerWidth < 992) this.sidebarCollapsed = true;
        },

        selectResource(resourceId) {
            if (this.resourceConfigs[resourceId]) this.switchModule(resourceId);
        },

        catalogResources() {
            return this.resourceCatalog.filter(resource => resource.id !== 'pagos');
        },

        recordRequest(request) {
            this.requestHistory = [request, ...this.requestHistory]
                .slice(0, API_CONFIG.requestHistoryLimit || 20);
            this.selectedRequest = this.requestHistory[0];
        },

        openInspector(request = this.selectedRequest || this.requestHistory[0]) {
            this.selectedRequest = request || null;
            this.inspectorOpen = true;
        },

        closeInspector() {
            this.inspectorOpen = false;
        },

        clearRequestHistory() {
            this.requestHistory = [];
            this.selectedRequest = null;
        },

        requestAsCurl(request) {
            if (!request) return '';
            const headerLines = Object.entries(request.headers || {})
                .filter(([, value]) => value)
                .map(([key, value]) => `-H ${JSON.stringify(`${key}: ${value}`)}`)
                .join(' ');
            const body = request.requestBody
                ? ` --data ${JSON.stringify(JSON.stringify(request.requestBody))}`
                : '';
            return `curl -X ${request.method} ${JSON.stringify(request.url)} ${headerLines}${body}`.trim();
        },

        async copyRequest(request = this.selectedRequest) {
            if (!request || !navigator.clipboard) return;
            await navigator.clipboard.writeText(this.requestAsCurl(request));
            this.addToast('Comando curl copiado', 'success');
        },

        // ── Toasts ──
        addToast(message, type = 'info') {
            this.toasts.push({ message, type });
            setTimeout(() => { this.toasts.shift(); }, 4500);
        },
        removeToast(index) {
            this.toasts.splice(index, 1);
        },

        // ── Theme Switcher ──
        toggleTheme() {
            this.appTheme = this.appTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', this.appTheme);
            localStorage.setItem('ragnos_api_theme', this.appTheme);
        }
    };
}

// ═══════════════════════════════════════════════════════════════
//  PAGOS MODULE (AlpineJS component)
// ═══════════════════════════════════════════════════════════════
function pagosModule() {
    return {
        // Config
        config: MODULE_CONFIG.pagos,

        // List state
        rows: [],
        loading: false,
        searchTerm: '',
        currentPage: 1,
        totalRecords: 0,
        totalPages: 1,
        sortField: '',
        sortDir: 'asc',

        // Form state
        formMode: 'create', // create | edit
        form: { customerNumber: '', checkNumber: '', paymentDate: '', amount: '' },
        formErrors: {},
        saving: false,
        editingId: null,
        customerLabel: '',

        // Customer search state
        customerSearchTerm: '',
        customerSearchResults: [],
        customerSearchLoading: false,
        customerCurrentPage: 1,
        customerTotalRecords: 0,
        customerTotalPages: 1,

        // Delete state
        deleteTarget: null,
        deleting: false,

        // ── Load Data ──
        async loadData() {
            this.loading = true;
            const token = Alpine.store('app').token;

            const params = {
                start: (this.currentPage - 1) * ITEMS_PER_PAGE,
                length: ITEMS_PER_PAGE
            };
            if (this.searchTerm) {
                params['search[value]'] = this.searchTerm;
            }
            if (this.sortField) {
                params['order[0][name]'] = this.sortField;
                params['order[0][dir]'] = this.sortDir;
            }

            const result = await apiCall(this.config.path, { token, params });

            if (result.ok && result.data) {
                this.rows = result.data.data || [];
                this.totalRecords = result.data.total || result.data.count || this.rows.length;
                this.totalPages = Math.max(1, Math.ceil(this.totalRecords / ITEMS_PER_PAGE));
            } else if (result.status === 401) {
                this.handleUnauthorized();
            } else {
                this.rows = [];
                const root = this.getRootApp();
                if (root) root.addToast('Error al cargar pagos: ' + (result.data?.error || 'Error desconocido'), 'error');
            }

            this.loading = false;
        },

        // ── Sorting ──
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDir = 'asc';
            }
            this.loadData();
        },
        getSortIcon(field) {
            if (this.sortField !== field) return 'bi-arrow-down-up opacity-25';
            return this.sortDir === 'asc' ? 'bi-arrow-up-circle-fill text-accent' : 'bi-arrow-down-circle-fill text-accent';
        },

        // ── Pagination ──
        goToPage(p) {
            if (p < 1 || p > this.totalPages) return;
            this.currentPage = p;
            this.loadData();
        },
        paginationPages() { return buildPaginationPages(this.currentPage, this.totalPages); },

        // ── Format ──
        formatMoney(val) { return formatMoney(val); },

        // ── Open Form (create) ──
        openForm() {
            this.formMode = 'create';
            this.editingId = null;
            this.form = {
                customerNumber: '',
                checkNumber: '',
                paymentDate: new Date().toISOString().slice(0, 10),
                amount: ''
            };
            this.formErrors = {};
            this.customerLabel = '';
            this.showModal('pagosModal');
        },

        // ── Edit ──
        async editRow(row) {
            this.formMode = 'edit';
            this.editingId = row[this.config.idField] || row.id;
            
            // Clean previous state
            this.formErrors = {};
            this.customerLabel = 'Cargando datos...';
            this.showModal('pagosModal');

            // Determine if the list row actually has the raw ID or just the joined name
            // For Ragnos relational datasets (like customerNumber), the list API replaces the ID with the joined text.
            // Ergo, we MUST fetch the real record from the DB using getRecordByAjax.
            const token = Alpine.store('app').token;
            
            const result = await apiCall(this.config.path + '/getRecordByAjax', {
                token,
                params: { id: this.editingId }
            });

            if (result.ok && result.data) {
                this.form = {
                    customerNumber: result.data.customerNumber || '',
                    checkNumber: result.data.checkNumber || '',
                    paymentDate: result.data.paymentDate || '',
                    amount: result.data.amount || ''
                };
                // Fallback to what we know from the list row
                this.customerLabel = row.customerName || ''; 
            } else {
                this.form = { customerNumber: '', checkNumber: '', paymentDate: '', amount: '' };
                this.customerLabel = 'Error al cargar';
            }
        },

        // ── Save (create/update) ──
        async saveForm() {
            this.saving = true;
            this.formErrors = {};
            const token = Alpine.store('app').token;

            // The Ragnos CRUD pipeline distinguishes inserts and updates through
            // Ragnos_action when the request uses POST (the API's save endpoint).
            // Sending only the primary key is not enough: without this marker a
            // POST update is interpreted as an insert by the model.
            const body = {
                ...this.form,
                Ragnos_action: this.formMode === 'edit' ? 'update' : 'insert'
            };

            // For updates, include the ID
            if (this.formMode === 'edit' && this.editingId) {
                body[this.config.idField] = this.editingId;
            }

            const result = await apiCall(this.config.path + '/save', {
                method: 'POST',
                token,
                body
            });

            this.saving = false;

            if (result.ok) {
                this.hideModal('pagosModal');
                const root = this.getRootApp();
                const msg = this.formMode === 'create' ? 'Pago creado exitosamente' : 'Pago actualizado exitosamente';
                if (root) root.addToast(msg, 'success');
                this.loadData();
            } else if ((result.status === 400 || result.status === 422) && result.data?.messages) {
                // Validation errors
                this.formErrors = result.data.messages;
            } else if (result.status === 401) {
                this.handleUnauthorized();
            } else {
                const errorMsg = result.data?.error || result.data?.message || 'Error al guardar';
                this.formErrors = { _general: errorMsg };
                const root = this.getRootApp();
                if (root) root.addToast('Error: ' + errorMsg, 'error');
            }
        },

        // ── Delete ──
        confirmDelete(row) {
            this.deleteTarget = row;
            this.showModal('deleteModal');
        },

        async deleteRow() {
            if (!this.deleteTarget) return;
            this.deleting = true;
            const token = Alpine.store('app').token;
            const id = this.deleteTarget[this.config.idField] || this.deleteTarget.id;

            const result = await apiCall(this.config.path + '/delete/' + id, {
                method: 'POST',
                token
            });

            this.deleting = false;

            if (result.ok) {
                this.hideModal('deleteModal');
                const root = this.getRootApp();
                if (root) root.addToast('Pago eliminado correctamente', 'success');
                this.loadData();
            } else if (result.status === 401) {
                this.handleUnauthorized();
            } else {
                const errMsg = result.data?.error || result.data?.message || result.data?.messages || 'Error al eliminar';
                const root = this.getRootApp();
                if (root) root.addToast('Error: ' + (typeof errMsg === 'string' ? errMsg : JSON.stringify(errMsg)), 'error');
                this.hideModal('deleteModal');
            }
        },

        // ── Customer Search ──
        openCustomerSearch() {
            this.customerSearchTerm = '';
            this.customerSearchResults = [];
            this.customerCurrentPage = 1;
            this.showModal('customerSearchModal');
            this.searchCustomers();
        },

        async searchCustomers() {
            this.customerSearchLoading = true;
            const token = Alpine.store('app').token;

            const params = {
                start: (this.customerCurrentPage - 1) * ITEMS_PER_PAGE,
                length: ITEMS_PER_PAGE
            };
            if (this.customerSearchTerm) {
                params['search[value]'] = this.customerSearchTerm;
            }

            const result = await apiCall(MODULE_CONFIG.clientes.path, { token, params });

            if (result.ok && result.data) {
                this.customerSearchResults = result.data.data || [];
                this.customerTotalRecords = result.data.total || result.data.count || this.customerSearchResults.length;
                this.customerTotalPages = Math.max(1, Math.ceil(this.customerTotalRecords / ITEMS_PER_PAGE));
            } else {
                this.customerSearchResults = [];
            }
            this.customerSearchLoading = false;
        },

        selectCustomer(customer) {
            this.form.customerNumber = customer.customerNumber || customer.id || customer[MODULE_CONFIG.clientes.idField];
            this.customerLabel = customer.customerName || '';
            this.hideModal('customerSearchModal');
        },

        customerGoToPage(p) {
            if (p < 1 || p > this.customerTotalPages) return;
            this.customerCurrentPage = p;
            this.searchCustomers();
        },
        customerPaginationPages() {
            return buildPaginationPages(this.customerCurrentPage, this.customerTotalPages);
        },

        // ── Helpers ──
        showModal(id) {
            const el = document.getElementById(id);
            if (el) new bootstrap.Modal(el).show();
        },
        hideModal(id) {
            const el = document.getElementById(id);
            if (el) {
                const instance = bootstrap.Modal.getInstance(el);
                if (instance) instance.hide();
            }
        },
        handleUnauthorized() {
            const root = this.getRootApp();
            if (root) {
                root.addToast('Sesión expirada. Inicia sesión de nuevo.', 'error');
                root.logout();
            }
        },
        getRootApp() {
            // Access root Alpine component
            const bodyEl = document.body;
            if (bodyEl && bodyEl._x_dataStack) {
                return bodyEl._x_dataStack[0];
            }
            return null;
        }
    };
}

// ═══════════════════════════════════════════════════════════════
//  GENERIC CATALOG MODULE (read-only listing with search + pagination)
// ═══════════════════════════════════════════════════════════════
function catalogModule() {
    return {
        rows: [],
        columns: [],
        loading: false,
        error: '',
        searchTerm: '',
        currentPage: 1,
        totalRecords: 0,
        totalPages: 1,
        sortField: '',
        sortDir: 'asc',
        editorOpen: false,
        editorMode: 'create',
        editorJson: '{}',
        editorErrors: [],
        editorSaving: false,
        editorLoading: false,
        deleteTarget: null,
        deleting: false,

        get currentModule() {
            return Alpine.store('app').currentModule;
        },

        init() {
            // Re-load the data if we switch modules without unmounting
            this.$watch('$store.app.currentModule', (val) => {
                const supportedModules = ['clientes', 'productos', 'empleados', 'oficinas', 'lineas'];
                if (supportedModules.includes(val)) {
                    this.currentPage = 1;
                    this.searchTerm = '';
                    this.sortField = '';
                    this.sortDir = 'asc';
                    this.loadCatalog();
                }
            });
        },

        getConfig() {
            return Alpine.store('app').resources?.[this.currentModule]
                || MODULE_CONFIG[this.currentModule]
                || MODULE_CONFIG.clientes;
        },

        catalogTitle() { return this.getConfig().title; },
        catalogIcon() { return this.getConfig().icon; },

        resourceFields() {
            return this.getConfig().fields || {};
        },

        resourceIdField() {
            return this.getConfig().idField || 'id';
        },

        resourceSupportsCrud() {
            return Boolean(this.getConfig().supportsCrud);
        },

        async openEditor(row = null) {
            this.editorMode = row ? 'edit' : 'create';
            this.editorErrors = [];
            this.editorJson = JSON.stringify(row || this.defaultRecord(), null, 2);
            this.editorOpen = true;

            if (!row) return;
            const cfg = this.getConfig();
            const id = row[cfg.idField || 'id'] || row.id;
            if (id === undefined || id === null) return;

            this.editorLoading = true;
            const result = await apiCall(`${cfg.path}/getRecordByAjax`, {
                token: Alpine.store('app').token,
                params: { id }
            });
            this.editorLoading = false;
            if (result.ok && result.data && typeof result.data === 'object') {
                this.editorJson = JSON.stringify(result.data, null, 2);
            }
        },

        closeEditor() {
            if (!this.editorSaving) this.editorOpen = false;
        },

        defaultRecord() {
            return Object.fromEntries(Object.entries(this.resourceFields())
                .filter(([, schema]) => !schema.readOnly)
                .map(([name, schema]) => [name, schema.type === 'boolean' ? false : '']));
        },

        parseEditorPayload() {
            try {
                const payload = JSON.parse(this.editorJson || '{}');
                if (!payload || Array.isArray(payload) || typeof payload !== 'object') {
                    throw new Error('El cuerpo debe ser un objeto JSON.');
                }
                return payload;
            } catch (error) {
                this.editorErrors = [error.message];
                return null;
            }
        },

        validateEditorPayload(payload) {
            const required = this.getConfig().required || [];
            this.editorErrors = required
                .filter(field => payload[field] === undefined || payload[field] === '')
                .map(field => `El campo ${field} es obligatorio.`);
            return this.editorErrors.length === 0;
        },

        async saveEditor() {
            const payload = this.parseEditorPayload();
            if (!payload || !this.validateEditorPayload(payload)) return;

            const cfg = this.getConfig();
            this.editorSaving = true;
            const result = await apiCall(`${cfg.path}/save`, {
                method: 'POST',
                token: Alpine.store('app').token,
                body: { ...payload, Ragnos_action: this.editorMode === 'edit' ? 'update' : 'insert' }
            });
            this.editorSaving = false;

            if (result.ok) {
                this.editorOpen = false;
                this.loadCatalog();
                this.getRootApp()?.addToast('Registro guardado correctamente', 'success');
            } else {
                this.editorErrors = this.extractErrors(result);
                if (result.status === 401) this.handleUnauthorized();
            }
        },

        confirmDelete(row) {
            this.deleteTarget = row;
        },

        cancelDelete() {
            this.deleteTarget = null;
        },

        async deleteRecord() {
            if (!this.deleteTarget) return;
            const cfg = this.getConfig();
            const id = this.deleteTarget[cfg.idField || 'id'] || this.deleteTarget.id;
            if (id === undefined || id === null) return;

            this.deleting = true;
            const result = await apiCall(`${cfg.path}/delete/${encodeURIComponent(id)}`, {
                method: 'POST',
                token: Alpine.store('app').token
            });
            this.deleting = false;

            if (result.ok) {
                this.deleteTarget = null;
                this.loadCatalog();
                this.getRootApp()?.addToast('Registro eliminado correctamente', 'success');
            } else {
                this.getRootApp()?.addToast(this.extractErrors(result).join(' '), 'error');
                if (result.status === 401) this.handleUnauthorized();
            }
        },

        extractErrors(result) {
            const errors = result.data?.messages || result.data?.errors;
            if (errors && typeof errors === 'object') return Object.values(errors).flat();
            return [result.data?.error || result.data?.message || 'Error en la operación.'];
        },

        async loadCatalog() {
            this.loading = true;
            this.error = '';
            const token = Alpine.store('app').token;
            const cfg = this.getConfig();

            this.columns = cfg.columns;

            const params = {
                start: (this.currentPage - 1) * ITEMS_PER_PAGE,
                length: ITEMS_PER_PAGE
            };
            if (this.searchTerm) {
                params['search[value]'] = this.searchTerm;
            }
            if (this.sortField) {
                params['order[0][name]'] = this.sortField;
                params['order[0][dir]'] = this.sortDir;
            }

            const result = await apiCall(cfg.path, { token, params });

            if (result.ok && result.data) {
                this.rows = result.data.data || [];
                this.totalRecords = result.data.total || result.data.count || this.rows.length;
                this.totalPages = Math.max(1, Math.ceil(this.totalRecords / ITEMS_PER_PAGE));
            } else if (result.status === 401) {
                this.handleUnauthorized();
            } else {
                this.error = result.data?.error || 'Error al cargar datos';
                this.rows = [];
            }

            this.loading = false;
        },

        sortBy(field) {
            if (this.sortField === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDir = 'asc';
            }
            this.loadCatalog();
        },
        getSortIcon(field) {
            if (this.sortField !== field) return 'bi-arrow-down-up opacity-25';
            return this.sortDir === 'asc' ? 'bi-arrow-up-circle-fill text-accent' : 'bi-arrow-down-circle-fill text-accent';
        },

        goToPage(p) {
            if (p < 1 || p > this.totalPages) return;
            this.currentPage = p;
            this.loadCatalog();
        },
        paginationPages() { return buildPaginationPages(this.currentPage, this.totalPages); },

        handleUnauthorized() {
            const bodyEl = document.body;
            if (bodyEl && bodyEl._x_dataStack) {
                const root = bodyEl._x_dataStack[0];
                root.addToast('Sesión expirada. Inicia sesión de nuevo.', 'error');
                root.logout();
            }
        },

        getRootApp() {
            return document.body?._x_dataStack?.[0] || null;
        }
    };
}
