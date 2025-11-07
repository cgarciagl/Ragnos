# Auditoría de Seguridad SQL — Ragnos

Fecha inicial: 2025-11-04
Última actualización: 2025-11-07
Alcance: carpeta `app/` del proyecto (CodeIgniter 4)

## Resumen ejecutivo

**Estado actual:** No se identifican riesgos críticos o medios pendientes.

### Cambios recientes:

1. **`SearchFilterTrait.php`:**

   - Se eliminó el uso de `sFilter` crudo.
   - Se implementó el método `parseStructuredFilters` para manejar filtros estructurados en formato JSON.
   - `performSearchForJson` ahora utiliza `parseStructuredFilters` para validar y aplicar filtros de manera segura.

2. **`perfil.php`:**
   - Los filtros en las líneas relevantes fueron actualizados para usar el formato JSON estructurado, asegurando compatibilidad con los cambios en el trait.

## Recomendaciones futuras

- Continuar utilizando el enfoque de filtros estructurados para cualquier funcionalidad que implique construcción dinámica de consultas SQL.
- Documentar el formato esperado de los filtros JSON para el equipo de frontend.
- Realizar auditorías periódicas para garantizar que no se introduzcan nuevas vulnerabilidades.

## Conclusión

El sistema ha sido actualizado para mitigar los riesgos identificados previamente. No se requieren acciones adicionales en este momento.
