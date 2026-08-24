<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ragnos API Documentation</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/swagger-ui.css') ?>">
</head>
<body>
<div id="swagger-ui"></div>
<script src="<?= base_url('assets/js/swagger-ui-bundle.js') ?>"></script>
<script src="<?= base_url('assets/js/swagger-ui-standalone-preset.js') ?>"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    window.ui = SwaggerUIBundle({
        url: <?= json_encode($specUrl, JSON_UNESCAPED_SLASHES) ?>,
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
        layout: 'StandaloneLayout',
        persistAuthorization: false,
    });
});
</script>
</body>
</html>
