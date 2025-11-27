<script type="text/javascript">

    $(function () {

        // Ocultar el div de administración al cargar
        $("#<?= $controllerUniqueID ?>admin_div").hide();

        // Refrescar la tabla mediante Ajax
        function <?= $controllerUniqueID ?>refreshAjax() {
            const oTable = $("#<?= $controllerUniqueID ?>_table").DataTable();
            const sel = $('.Ragnos_selected_row').index();
            $("#<?= $controllerUniqueID ?>").data('preselect', sel);
            oTable.draw(false);
        }

        // Botón para buscar en administración
        $("#<?= $controllerUniqueID ?>btn_search_admin").click(function (e) {
            e.preventDefault();
            $("#<?= $controllerUniqueID ?>").hide('slide');
            getValue('<?= $clase ?>/tableByAjax/', Ragnos_csrf, function (s) {
                $("#<?= $controllerUniqueID ?>admin_div").hide();
                $("#<?= $controllerUniqueID ?>admin_container").html(s);
                $("#<?= $controllerUniqueID ?>admin_div").show('slide');
            });
        });

        // Botón para regresar de la búsqueda en administración
        $("#<?= $controllerUniqueID ?>btn_search_admin_back").click(function (e) {
            e.preventDefault();
            $("#<?= $controllerUniqueID ?>").show('slide');
            $("#<?= $controllerUniqueID ?>admin_div").hide('slide');
        <?= $controllerUniqueID ?>refreshAjax();
        });

        // Botón para confirmar la selección
        $("#<?= $controllerUniqueID ?>btn_ok_search").click(function (e) {
            e.preventDefault();
            const tds = $("#<?= $controllerUniqueID ?>_table tbody tr.Ragnos_selected_row").first().find("td");
            const fid = tds.last().attr('idr');
            const fname = tds.first().text();
            const ResultData = { id: fid || '', name: fname || '' };

            $(this).closest('.Ragnos-widget').first().remove();
            const t = RagnosSearch.searchStack.pop();

            if (t) {
                t.val(ResultData.name);
                t.data('id', ResultData.id);
                t.data('name', ResultData.name);
                t.closest(".input-group").next('input[type=hidden]').val(ResultData.id);

                const tableFields = <?= json_encode($tablefields) ?>;
                const primaryKey = '<?= $primaryKey ?>';

                // Convertir las celdas seleccionadas en un objeto
                const obj = { y_id: ResultData.id, y_name: ResultData.name };
                obj[primaryKey] = ResultData.id;
                tds.each(function () {
                    obj[tableFields[$(this).index()]] = $(this).text();
                });
                t.data('searchdata', obj);

                // Ejecutar callback si existe
                const callbackFunction = `_${t.attr('id')}OnSearch`;
                if (typeof window[callbackFunction] === 'function') {
                    window[callbackFunction](t);
                }
            }

            cierraModal('YSearchModal');
            t.closest('.divfield').nextAll('.divfield').first().find('input, textarea, select').first().focus();

        });

        // Botón para cancelar la búsqueda
        $("#<?= $controllerUniqueID ?>btn_cancel_search").click(function (e) {
            e.preventDefault();
            $(this).closest('.Ragnos-widget').first().remove();
            const t = RagnosSearch.searchStack.pop();
            if (t.data('name')) {
                t.val(t.data('name'));
            }
            cierraModal('YSearchModal');
        });

        // Inicializar DataTable
        <?= view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/datatable_init', ['controllerUniqueID' => $controllerUniqueID, 'tableController' => $tableController]); ?>

        // Configurar búsqueda en DataTable
        $('#<?= $controllerUniqueID ?>_Tablediv .dt-search').append($('#<?= $controllerUniqueID ?>_combo'));

        const bodyTable = $("#<?= $controllerUniqueID ?>_table tbody");

        // Configurar eventos de teclado en el modal
        bodyTable.closest('.modal').removeAttr('data-bs-keyboard').removeClass('fade').on('keydown', function (event) {
            if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(event.key)) {
                event.preventDefault();
                const trSelected = bodyTable.find('.Ragnos_selected_row');
                if (trSelected.length > 0) {
                    const actions = {
                        'ArrowDown': () => trSelected.next('tr').addClass('Ragnos_selected_row').siblings().removeClass('Ragnos_selected_row'),
                        'ArrowUp': () => trSelected.prev('tr').addClass('Ragnos_selected_row').siblings().removeClass('Ragnos_selected_row'),
                        ' ': () => trSelected.trigger('dblclick'),
                        'Enter': () => {
                            const searchInput = $('#<?= $controllerUniqueID ?>_Tablediv .dt-search input');
                            if (searchInput.val() === '') {
                                if (!$(document.activeElement).is('input')) {
                                    trSelected.trigger('dblclick');
                                }
                            }
                        }
                    };
                    actions[event.key]?.();
                }
                return false;
            }
        });

        // Doble clic en una fila
        bodyTable.on('dblclick', 'tr', function (ev) {
            ev.preventDefault();
            const op = $(this).find("td").last();
            $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr') || '');
            if (!op.hasClass('dataTables_empty')) {
                $("#<?= $controllerUniqueID ?>btn_ok_search").trigger('click');
            }
            return false;
        });

        // Selección de fila con clic
        bodyTable.on('mousedown', 'tr', function (ev) {
            ev.preventDefault();
            const op = $(this).find("td").last();
            $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr') || '');
            bodyTable.find('tr').removeClass('Ragnos_selected_row');
            $(this).addClass('Ragnos_selected_row');
        });

        // Configurar búsqueda en el filtro de DataTable
      /*  $('#<?= $controllerUniqueID ?>_Tablediv.dataTables_filter input')
            .data('objtable', $('#<?= $controllerUniqueID ?>_table'))
            .off('keyup change')
            .on('keyup', function (e) {
                if (e.keyCode === 13) {
                    $('#<?= $controllerUniqueID ?>_sel').focus();
                }
            })
            .on('change', function () {
                $(this).data('objtable').fnFilter($(this).val());
            });*/


        // Función para agregar datos extra a la petición Ajax
        function fnData2<?= $controllerUniqueID ?>(data, fnCallback) {
            const onlyField = $('#<?= $controllerUniqueID ?>_sel').val();
            if (onlyField) {
                data.sOnlyField = onlyField;
            }

            const source = '<?= site_url($clase . '/getAjaxGridData'); ?>';
            const searchValue = "<?= $sSearch ?>";
            const filterValue = "<?= $sFilter ?>";

            if (searchValue && !data.search.value) {
                data.search.value = searchValue;
            }
            if (filterValue) {
                data.sFilter = filterValue;
            }

            getObject(source, data, function (json) {
                fnCallback(json);
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');
                if (json.data.length > 0) {
                    $("#<?= $controllerUniqueID ?>_table tbody tr").each(function () {
                        const op = $(this).find("td").last();
                        const id = op.text();
                        op.attr('idr', id).html('');
                    });
                }
                const searchTitle = $("#<?= $controllerUniqueID ?>_searching_title");
                if (json.sSearch.value) {
                    searchTitle.text("<?= lang('Ragnos.Ragnos_searching') ?>" + " (" + json.sSearch.value + ") ...").show();
                } else {
                    searchTitle.text("").hide();
                }

                const firstRow = $("#<?= $controllerUniqueID ?>_table tbody tr").first();
                firstRow.addClass('Ragnos_selected_row');

                if (json.data.length === 1 && json.recordsTotal === 1 && json.sSearch.value) {
                    $("#<?= $controllerUniqueID ?>btn_ok_search").trigger('click');
                }
            });
        }

        // Eventos de teclado en la tabla
        $('#<?= $controllerUniqueID ?>_table').on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                $("#<?= $controllerUniqueID ?>btn_ok_search").trigger('click');
                return false;
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                $("#<?= $controllerUniqueID ?>btn_cancel_search").trigger('click');
                return false;
            }
        });

    });
</script>