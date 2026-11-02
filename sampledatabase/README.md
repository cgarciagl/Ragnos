# Base de datos / Database

| Script | Contenido / Contents | ¿Obligatorio? / Required? |
| :-- | :-- | :-: |
| `ragnos_base.sql` | Tablas del sistema: usuarios, grupos, auditoría y sesiones. Usuario inicial `admin` / `admin`. <br> System tables: users, groups, audit log and sessions. Initial user `admin` / `admin`. | ✅ |
| `demo_classicmodels.sql` | Demo de la tienda de modelos a escala (Classicmodels): catálogos, reportes y panel de ventas. <br> Scale-model store demo (Classicmodels): catalogs, reports and sales dashboard. | Opcional / Optional |

```bash
mysql -u root -p -e "CREATE DATABASE ragnos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p ragnos < sampledatabase/ragnos_base.sql
mysql -u root -p ragnos < sampledatabase/demo_classicmodels.sql   # opcional / optional
```

- Los scripts no fijan el nombre de la base: se importan en la seleccionada. / The scripts don't hard-code the database name.
- Ambos eliminan y recrean sus tablas: úsalos solo para instalar. / Both drop and recreate their tables: use them only to install.
- Ragnos detecta la demo automáticamente (menús, panel y reportes). / Ragnos detects the demo automatically.
- `esquema.png`: diagrama entidad-relación de Classicmodels. / Classicmodels ER diagram.

Documentación / Docs: <https://ragnos.build/docs/fundamentos/instalacion/>
