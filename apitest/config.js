/* Runtime configuration for the API demo. Keep environment-specific values here. */
(() => {
    const configuredBase = window.RAGNOS_API_BASE;
    const apiBase = configuredBase
        ? configuredBase.replace(/\/$/, '')
        : new URL('../content/index.php', document.baseURI).toString().replace(/\/$/, '');

    window.RAGNOS_API_CONFIG = Object.freeze({
        apiBase,
        openApiUrl: `${apiBase}/api/openapi.json`,
        swaggerUrl: `${apiBase}/api/docs`,
        pageSize: 10,
        requestHistoryLimit: 20
    });
})();
