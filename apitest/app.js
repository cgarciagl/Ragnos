/**
 * ═══════════════════════════════════════════════════════════════
 *  Ragnos API Test — AlpineJS SPA Application
 *  Tests the API mode of Ragnos RDatasetController controllers
 * ═══════════════════════════════════════════════════════════════
 */

// ── Configuration ───────────────────────────────────────────────
const API_BASE = '../content/index.php'; // Relative to apitest/

const ITEMS_PER_PAGE = 10;

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

// ── API Helper ──────────────────────────────────────────────────

/**
 * Makes an API call to Ragnos backend.
 * @param {string} endpoint - Relative path (e.g., 'tienda/pagos')
 * @param {object} options  - { method, body, token, params }
 * @returns {Promise<{ok: boolean, status: number, data: object}>}
 */
async function apiCall(endpoint, options = {}) {
    const { method = 'GET', body = null, token = null, params = {} } = options;

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

        return { ok, status, data };
    } catch (err) {
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
        loginForm: { usuario: '', pword: '' },
        loginError: '',
        loginLoading: false,

        // ── UI state ──
        currentModule: 'dashboard',
        sidebarCollapsed: false,
        appTheme: 'dark',

        // ── Toasts ──
        toasts: [],

        get moduleTitle() {
            if (this.currentModule === 'dashboard') return 'Dashboard';
            return MODULE_CONFIG[this.currentModule]?.title || this.currentModule;
        },

        // ── Init ──
        init() {
            // Restore theme
            const savedTheme = localStorage.getItem('ragnos_api_theme') || 'dark';
            this.appTheme = savedTheme;
            document.documentElement.setAttribute('data-bs-theme', savedTheme);

            // Restore session from localStorage
            const saved = localStorage.getItem('ragnos_api_session');
            if (saved) {
                try {
                    const session = JSON.parse(saved);
                    this.token = session.token;
                    this.userId = session.userId;
                    this.isAuthenticated = true;
                } catch { /* skip */ }
            }

            // Create Alpine store accessible by child components
            Alpine.store('app', {
                token: this.token,
                currentModule: this.currentModule
            });

            this.$watch('token', val => Alpine.store('app').token = val);
            this.$watch('currentModule', val => Alpine.store('app').currentModule = val);
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
                this.isAuthenticated = true;

                localStorage.setItem('ragnos_api_session', JSON.stringify({
                    token: this.token,
                    userId: this.userId
                }));

                Alpine.store('app').token = this.token;
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
            this.currentModule = 'dashboard';
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

            const body = { ...this.form };

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
            } else if (result.status === 400 && result.data?.messages) {
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
                    this.loadCatalog();
                }
            });
        },

        getConfig() {
            return MODULE_CONFIG[this.currentModule] || MODULE_CONFIG.clientes;
        },

        catalogTitle() { return this.getConfig().title; },
        catalogIcon() { return this.getConfig().icon; },

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
        }
    };
}
