<script type="text/javascript">

    $("#<?= $controllerUniqueID ?>admin_div").hide();

    function <?= $controllerUniqueID ?>refreshAjax() {
        var oTable = $("#<?= $controllerUniqueID ?>_table").DataTable();
        var sel = $('.Ragnos_selected_row').index();
        $("#<?= $controllerUniqueID ?>").data('preselect', sel);
        oTable.draw(false);
    }

    $("#<?= $controllerUniqueID ?>btn_search_admin").click(function (e) {
        e.preventDefault();
        $("#<?= $controllerUniqueID ?>").hide('slide');
        getValue('<?= $clase ?>/tableByAjax/', Ragnos_csrf,
            function (s) {
                $("#<?= $controllerUniqueID ?>admin_div").hide();
                $("#<?= $controllerUniqueID ?>admin_container").html(s);
                $("#<?= $controllerUniqueID ?>admin_div").show('slide');
            });
    });

    $("#<?= $controllerUniqueID ?>btn_search_admin_back").click(function (e) {
        e.preventDefault();
        $("#<?= $controllerUniqueID ?>").show('slide');
        $("#<?= $controllerUniqueID ?>admin_div").hide('slide');;
        ;<?= $controllerUniqueID ?>refreshAjax();
    });

    $("#<?= $controllerUniqueID ?>btn_ok_search").click(function (e) {
        e.preventDefault();
        let tds = $("#<?= $controllerUniqueID ?>_table tbody tr.Ragnos_selected_row").first().find("td");
        let fid = tds.last().attr('idr');
        let fname = tds.first().text();
        let ResultData = {
            id: fid || '',
            name: fname || ''
        };
        $(this).closest('.Ragnos-widget').first().remove();
        //let t = stacksearches.pop();
        let t = RagnosSearch.searchStack.pop();
        if (t) {
            t.val(ResultData.name);
            t.data('id', ResultData.id);
            t.data('name', ResultData.name);
            t.closest(".input-group").next('input[type=hidden]').val(ResultData.id);

            let campostabla = <?= json_encode($tablefields) ?>;

            let campoprimary = '<?= $primaryKey ?>';

            //tds son las celdas de la fila seleccionada, convierte sus valores a un objeto
            var obj = { y_id: ResultData.id, y_name: ResultData.name };
            obj[campoprimary] = ResultData.id;
            tds.each(function () {
                obj[campostabla[$(this).index()]] = $(this).text();
            });
            t.data('searchdata', obj);
            //si hay un callback javascript y si es una función valida lo ejecutamos y 
            //le pasamos el control que disparó la busqueda
            let funcioncallback = '_' + t.attr('id') + 'OnSearch';
            if ((typeof window[funcioncallback] === 'function')) {
                window[funcioncallback](t);
            }
        }
        //stackwidgets.pop().show();
        cierraModal('YSearchModal');
        //ponemos el foco en el siguiente control de input...
        t.closest('.divfield').nextAll('.divfield').first().find('input, textarea, select').first().focus();
    });

    $("#<?= $controllerUniqueID ?>btn_cancel_search").click(function (e) {
        e.preventDefault();
        $(this).closest('.Ragnos-widget').first().remove();
        // stackwidgets.pop().show();
        //var t = stacksearches.pop();
        let t = RagnosSearch.searchStack.pop();
        if (t.data('name')) {
            t.val(t.data('name'));
        }
        cierraModal('YSearchModal');
    });

    <?= view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/datatable_init', ['controllerUniqueID' => $controllerUniqueID, 'tableController' => $tableController]); ?>

    $('#<?= $controllerUniqueID ?>_Tablediv .dt-search').append($('#<?= $controllerUniqueID ?>_combo'));

    var bodytable = $("#<?= $controllerUniqueID ?>_table tbody");

    bodytable.closest('.modal').removeAttr('data-bs-keyboard');
    bodytable.closest('.modal').removeClass('fade');

    bodytable.closest('.modal').on('keydown', function (event) {
        if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(event.key)) {
            event.preventDefault();
            var trsel = bodytable.find('.Ragnos_selected_row');
            if (trsel.length > 0) {
                var actions = {
                    'ArrowDown': function () {
                        var trnext = trsel.next('tr');
                        if (trnext.length > 0) {
                            trsel.removeClass('Ragnos_selected_row');
                            trnext.addClass('Ragnos_selected_row');
                        }
                    },
                    'ArrowUp': function () {
                        var trprev = trsel.prev('tr');
                        if (trprev.length > 0) {
                            trsel.removeClass('Ragnos_selected_row');
                            trprev.addClass('Ragnos_selected_row');
                        }
                    },
                    ' ': function () {
                        trsel.trigger('dblclick');
                    },
                    'Enter': function () {
                        let controlbusqueda = $('#<?= $controllerUniqueID ?>_Tablediv .dt-search input');
                        if (controlbusqueda.val() == '') {
                            trsel.trigger('dblclick');
                        }
                    }
                };
                actions[event.key] && actions[event.key]();
            }
            return false;
        }
    });

    bodytable.delegate('tr', 'dblclick',
        function (ev) {
            ev.preventDefault();
            var op = $(this).find("td").last();
            if (op.attr('idr')) {
                $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
            } else {
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            }
            //devolvemos los datos del registro(s) seleccionado(s)
            if (!op.hasClass('dataTables_empty')) {
                $("#<?= $controllerUniqueID ?>btn_ok_search").trigger('click');
            }
            return false;
        });

    bodytable.delegate('tr', 'mousedown',
        function (ev) {
            ev.preventDefault();
            var op = $(this).find("td").last();
            if (op.attr('idr')) {
                $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
            } else {
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            }
            $("#<?= $controllerUniqueID ?>_table tbody tr").removeClass('Ragnos_selected_row');
            $(this).addClass('Ragnos_selected_row');
        });

    //event for search on enter keyup or on blur

    $('#<?= $controllerUniqueID ?>_Tablediv .dataTables_filter input').data('objtable', $('#<?= $controllerUniqueID ?>_table')).unbind('keyup')
        .unbind('keypress')
        .unbind('input')
        .bind('keyup', function (e) {
            if (e.keyCode != 13)
                return;
            $('#<?= $controllerUniqueID ?>_sel').focus();
        }).bind('change', function () {
            $(this).data('objtable').fnFilter($(this).val());
        });

    function fnData2<?= $controllerUniqueID ?>(data, fnCallback, settings) {
        /* se agregan datos extras a la petición ajax */
        var sonlyfield = $('#<?= $controllerUniqueID ?>_sel').val();
        if (sonlyfield != '') {
            data.sOnlyField = sonlyfield;
        }

        sSource = '<?= site_url($clase . '/getAjaxGridData'); ?>';

        var lookingfor = "<?= $sSearch ?>";

        if (lookingfor != '' && !(data.search.value)) {
            data.search.value = lookingfor;
        }

        var sfilter = "<?= $sFilter ?>";
        if (sfilter != '') {
            data.sFilter = sfilter;
        }

        getObject(sSource, data, function (json) {
            fnCallback(json);
            $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            if (json.data.length > 0) {
                $("#<?= $controllerUniqueID ?>_table tbody tr").each(
                    function () {
                        var op = $(this).find("td").last();
                        var id = op.text();
                        op.attr('idr', id);
                        op.html('');
                    });
            }
            if (json.sSearch.value != '') {
                $("#<?= $controllerUniqueID ?>_searching_title").text("<?= lang('Ragnos.Ragnos_searching') ?>" + " (" + json.sSearch.value + ") ...").show();;
            } else {
                $("#<?= $controllerUniqueID ?>_searching_title").text("").hide();
            }

            var primerodelatabla = $("#<?= $controllerUniqueID ?>_table tbody tr").first();
            primerodelatabla.addClass('Ragnos_selected_row');

            if ((json.data.length == 1) && (json.recordsTotal == 1) && (json.sSearch.value != '')) {
                //devolvemos los datos del registro(s) seleccionado(s)
                $("#<?= $controllerUniqueID ?>btn_ok_search").trigger('click');
            }
        });

    }

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
    })

</script>