# Librerías de terceros / Third-party libraries

Copias locales de las librerías que Ragnos cargaba desde CDN, para que la
interfaz funcione sin acceso a internet (intranets, redes corporativas).
Local copies of the libraries Ragnos used to load from CDNs.

| Librería | Versión | Licencia | Archivo |
| :-- | :-- | :-- | :-- |
| Popper | 2.11.8 | MIT | `popper/popper.min.js` |
| Bootstrap (JS) | 5.3.2 | MIT | `bootstrap/bootstrap.min.js` |
| Bootstrap Icons | 1.11.0 | MIT | `bootstrap-icons/bootstrap-icons.min.css` + `fonts/` |
| SweetAlert2 | 11.26.25 | MIT | `sweetalert2/sweetalert2.all.min.js` |
| Animate.css | 4.1.1 | MIT | `animate/animate.min.css` |
| SortableJS | 1.15.0 | MIT | `sortablejs/Sortable.min.js` |

Obtenidas del registro de npm; Popper, Bootstrap y Bootstrap Icons son
idénticas byte a byte a las que servía el CDN (mismo hash SRI). Para
actualizar una librería, reemplaza su archivo y cambia la versión aquí y en
`src/Views/ragnos/scriptfiles.php`.
