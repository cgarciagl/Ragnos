<script type="text/javascript">

    function <?= $controllerUniqueID ?>refreshAjax() {
        var oTable = $("#<?= $controllerUniqueID ?>_table").DataTable();
        var sel = $('#<?= $controllerUniqueID ?> .Ragnos_selected_row').index();
        $("#<?= $controllerUniqueID ?>").data('preselect', sel);
        oTable.draw(false);
        if ((typeof window['_<?= $controller_name ?>OnChange'] === 'function')) {
            _<?= $controller_name ?>OnChange(oTable);
        }
    }

    $("#<?= $controllerUniqueID ?>btn_cancel").click(
        function (e) {
            e.preventDefault();
            $('#tab_<?= $controllerUniqueID ?>_Table').click();
            ;<?= $controllerUniqueID ?>refreshAjax();
        });

    $("#<?= $controllerUniqueID ?>btn_ok").click(
        function (e) {
            e.preventDefault();
            var forma = $('#<?= $controllerUniqueID ?>_FormContent form');

            $('#<?= $controllerUniqueID ?>_FormContent form input[money]').each(function () {
                $(this).val(moneyToNumber($(this).val()));
            });

            var a = forma.first().serializeObject();
            /* get the previous values for the controls*/
            var f = forma.first().find('[data-valueant]');
            $.each(f, function () {
                var s = 'Ragnos_value_ant_' + $(this).attr('name');
                a[s] = $(this).attr('data-valueant');
            });
            $.extend(a, Ragnos_csrf);

            <?php use App\ThirdParty\Ragnos\Controllers\Ragnos; ?>
            let currency = '<?= Ragnos::config()->currency ?? 'USD' ?>';

            $('#<?= $controllerUniqueID ?>_FormContent form input[money]').each(function () {
                $(this).val(moneyFormat($(this).val(), currency));
            });

            /*limpiamos los errores anteriores*/
            forma.find('.ui-state-error').remove();
            $('#<?= $controllerUniqueID ?> .has-error').removeClass('has-error');
            getObject('<?= $clase ?>/formProcess', a, function (obj) {
                if (obj.result != 'ok') {
                    $.each(obj.errors, function (i, val) {
                        var gi = $("#group_" + i);
                        gi.append('<span class="ui-state-error badge text-bg-danger">' +
                            val + '</span>');
                        $('#' + i).focus();
                        gi.addClass('has-error').shake();
                    });
                    if (obj.errors['general_error']) {
                        Swal.fire({
                            icon: 'error',
                            text: obj.errors['general_error'],
                        });
                    }
                } else {
                    <?php if ($hasdetails): ?>
                        if (obj.insertedid) {
                            ;<?= $controllerUniqueID ?>getform(obj.insertedid);
                        } else {
                            $('#tab_<?= $controllerUniqueID ?>_Table').click();
                            ;<?= $controllerUniqueID ?>refreshAjax();
                        }
                    <?php else: ?>
                        $('#tab_<?= $controllerUniqueID ?>_Table').click();
                        ;<?= $controllerUniqueID ?>refreshAjax();
                    <?php endif; ?>
                    if (obj.insertedid) {
                        showToast('<?= lang('Ragnos.Ragnos_record_inserted') ?>', 'success');
                    } else {
                        showToast('<?= lang('Ragnos.Ragnos_record_updated') ?>', 'success');
                    }
                }
            });
        });

    function <?= $controllerUniqueID ?>getform(id) {
        $("#<?= $controllerUniqueID ?>_FormContent").html('');
        <?php if ($master): ?>
            Ragnos_csrf.Ragnos_master = '<?= $master ?>';
        <?php endif; ?>
        getValue('<?= $clase ?>/getFormData/' + id, Ragnos_csrf,
            function (s) {
                $("#<?= $controllerUniqueID ?>_FormContent").hide();
                $("#<?= $controllerUniqueID ?>_FormContent").html(s);
                $("#<?= $controllerUniqueID ?>_FormContent").show();
                $("#<?= $controllerUniqueID ?>_FormContent").find('[readonly]').addClass('text-bg-info');
            });
    }

    $('#<?= $controllerUniqueID ?> button[data-bs-toggle="tab"]').on('shown.bs.tab',
        function (e) {
            if ($(e.target).attr('data-bs-target') == "#<?= $controllerUniqueID ?>_Form") {
                if ($("#<?= $controllerUniqueID ?>").data('idactivo') == '') {
                    var op = $("#<?= $controllerUniqueID ?>_table tbody").find('tr').first().find('td').last();
                    if (op.attr('idr') != undefined) {
                        $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
                    } else {
                        $("#<?= $controllerUniqueID ?>").data('idactivo', '');
                    }
                }
                ;<?= $controllerUniqueID ?>getform($("#<?= $controllerUniqueID ?>").data('idactivo'))
            }
        });

    <?= view(
        'App\ThirdParty\Ragnos\Views\rdatasetcontroller/datatable_init',
        [
            'controllerUniqueID' => $controllerUniqueID,
            'tableController'    => $tableController,
            'clase'              => $clase,
            'sortingField'       => $sortingField,
            'sortingDir'         => $sortingDir
        ]
    ); ?>

    $('#<?= $controllerUniqueID ?>_Tablediv .dt-search').append($('#<?= $controllerUniqueID ?>_combo'));

    var bodytable = $("#<?= $controllerUniqueID ?>_table tbody");

    bodytable.delegate('tr', 'dblclick',
        function (ev) {
            var op = $(this).find("td").last();
            if (op.attr('idr')) {
                $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
            } else {
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            }
            if (!op.hasClass('dataTables_empty')) {
                $('#tab_<?= $controllerUniqueID ?>_Form').click();
            }
            return false;
        });

    bodytable.delegate('tr', 'mousedown',
        function (ev) {
            var op = $(this).find("td").last();
            if (op.attr('idr')) {
                $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
            } else {
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            }
            $("#<?= $controllerUniqueID ?>_table tbody tr").removeClass('Ragnos_selected_row');
            $(this).addClass('Ragnos_selected_row');
        });

    let myModalAlternative<?= $controllerUniqueID ?>;

    bodytable.delegate('.<?= $controllerUniqueID ?>deleteme', 'click',
        function (ev) {
            var r = $(this).closest('tr');
            var todelete = $(this).attr('idr');
            r.addClass('todelete');
            Swal.fire({
                text: '<?= lang('Ragnos.Ragnos_delete_message') ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<?= lang('Ragnos.Ragnos_yes') ?>',
                cancelButtonText: '<?= lang('Ragnos.Ragnos_no') ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteAction();
                } else {
                    r.removeClass('todelete');
                }
                $(' #<?= $controllerUniqueID ?>_table').find('.todelete').removeClass('todelete');
            });
            return false;
        });


    function deleteAction() {
        var y = $(' #<?= $controllerUniqueID ?>_table').find('.todelete').first();
        var todelete = y.find('td').last().attr('idr');
        y.removeClass('borrar');
        obj = {
            id: todelete
        };
        $.extend(obj, Ragnos_csrf);
        getObject('<?= $clase . '/getRecordByAjax' ?>', obj, function (j) {
            <?php foreach ($fieldlist as $fieldItem): ?>
                obj.Ragnos_value_ant_<?= $fieldItem->getFieldName(); ?> = j.<?= $fieldItem->getFieldName(); ?>;
            <?php endforeach; ?>

            getObject('<?= $clase . '/delete' ?>', obj, function (obj) {
                if (obj.result != 'ok') {
                    Swal.fire({
                        icon: 'error',
                        text: obj.errors['general_error'],
                    });
                } else {
                    ;<?= $controllerUniqueID ?>refreshAjax();
                    if (myModalAlternative<?= $controllerUniqueID ?>) {
                        myModalAlternative<?= $controllerUniqueID ?>.hide();
                    }
                    showToast('<?= lang('Ragnos.Ragnos_record_deleted') ?>', 'success');
                }
            });
        });
    };

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

    $('#btn_<?= $controllerUniqueID ?>_New').click(
        function (e) {
            e.preventDefault();
            $("#<?= $controllerUniqueID ?>").data('idactivo', 'new');
            $('#tab_<?= $controllerUniqueID ?>_Form').click();
        });

    $('#btn_<?= $controllerUniqueID ?>_Refresh').click(
        function (e) {
            e.preventDefault();
            $("#<?= $controllerUniqueID ?>_table").DataTable().draw();
        });

    $('#btn_<?= $controllerUniqueID ?>_Print').click(
        function (e) {
            e.preventDefault();
            var widget = $(this).closest('.Ragnos-widget').first();
            var widget_container = widget.parent();
            widget.hide();
            getValue('<?= $clase ?>/reportByAjax', Ragnos_csrf,
                function (r) {
                    $(r).appendTo(widget_container).show('slide');
                });
        });

    $('#<?= $controllerUniqueID ?>_sel').change(
        function () {
            $('#btn_<?= $controllerUniqueID ?>_Refresh').click();
        });


    function fnData2<?= $controllerUniqueID ?>(data, fnCallback, settings) {
        var sonlyfield = $('#<?= $controllerUniqueID ?>_sel').val();
        if (sonlyfield != '') {
            data.sOnlyField = sonlyfield;
        }

        <?php if ($master): ?>
            data.Ragnos_master = '<?= $master ?>';
        <?php endif; ?>

        sSource = '<?= site_url($clase . '/getAjaxGridData'); ?>';
        getObject(sSource, data, function (json) {
            fnCallback(json);
            $("#<?= $controllerUniqueID ?>").data('idactivo', '');
            if (json.data.length > 0) {
                $("#<?= $controllerUniqueID ?>_table tbody tr").each(
                    function () {
                        var op = $(this).find("td").last();
                        var id = op.text();
                        op.attr('idr', id);
                        <?php if ($modelo->canDelete): ?>
                            op.html('<i class="bi bi-trash ybtndelete"></i>');
                            op.addClass('<?= $controllerUniqueID ?>deleteme');
                        <?php else: ?>
                            op.html('');
                        <?php endif; ?>
                    });
            }
            if (json.sSearch.value != '') {
                $("#<?= $controllerUniqueID ?>_searching_title").text("<?= lang('Ragnos.Ragnos_searching') ?>" +
                    " (" + json.sSearch.value + ") ...").show();
            } else {
                $("#<?= $controllerUniqueID ?>_searching_title").text("").hide();
            }
            if ($("#<?= $controllerUniqueID ?>").data('preselect') > 0) {
                var r = $("#<?= $controllerUniqueID ?>_table tbody tr").get($("#<?= $controllerUniqueID ?>").data('preselect'));
                if (r) {
                    var op = $(r).find("td").last();
                    if (op.attr('idr')) {
                        $("#<?= $controllerUniqueID ?>").data('idactivo', op.attr('idr'));
                    } else {
                        $("#<?= $controllerUniqueID ?>").data('idactivo', '');
                    }
                    $(r).addClass('Ragnos_selected_row');
                }
            } else {
                $("#<?= $controllerUniqueID ?>_table tbody tr").first().addClass('Ragnos_selected_row');
            }
        });
    }

</script>