<script type="text/javascript">

    $(function () {

        function <?= $controllerUniqueID ?>refreshAjax() {
            const oTable = $("#<?= $controllerUniqueID ?>_table").DataTable();
            const selectedRowIndex = $('#<?= $controllerUniqueID ?> .Ragnos_selected_row').index();
            $("#<?= $controllerUniqueID ?>").data('preselect', selectedRowIndex);
            oTable.draw(false);

            if (typeof window['_<?= $controller_name ?>OnChange'] === 'function') {
                window['_<?= $controller_name ?>OnChange'](oTable);
            }
        }

        $("#<?= $controllerUniqueID ?>btn_cancel").on('click', function (e) {
            e.preventDefault();
            $('#tab_<?= $controllerUniqueID ?>_Table').click();
            <?= $controllerUniqueID ?>refreshAjax();
        });

        $("#<?= $controllerUniqueID ?>btn_ok").click(function (e) {
            e.preventDefault();

            const form = $('#<?= $controllerUniqueID ?>_FormContent form');
            const formElement = document.querySelector('#<?= $controllerUniqueID ?>_FormContent form');
            const formData = serializeForm(formElement);

            // Convert money inputs to numeric values
            form.find('input[money]').each(function () {
                $(this).val(moneyToNumber($(this).val()));
            });

            // Add previous values for controls
            form.find('[data-valueant]').each(function () {
                const fieldName = $(this).attr('name');
                formData[`Ragnos_value_ant_${fieldName}`] = $(this).attr('data-valueant');
            });

            // Add CSRF token
            $.extend(formData, Ragnos_csrf);

            // Format money inputs back to currency
            <?php use App\ThirdParty\Ragnos\Controllers\Ragnos; ?>
            const currency = '<?= Ragnos::config()->currency ?? 'USD' ?>';
            form.find('input[money]').each(function () {
                $(this).val(moneyFormat($(this).val(), currency));
            });

            // Clear previous errors
            form.find('.ui-state-error').remove();
            $('#<?= $controllerUniqueID ?> .has-error').removeClass('has-error');

            // Submit form data
            getObject('<?= $clase ?>/formProcess', formData, function (response) {
                if (response.result !== 'ok') {
                    // Handle validation errors
                    $.each(response.errors, function (field, errorMessage) {
                        const group = $(`#group_${field}`);
                        group.append(`<span class="ui-state-error badge text-bg-danger">${errorMessage}</span>`);
                        $(`#${field}`).focus();
                        group.addClass('has-error');

                        // Shake elements with errors
                        document.querySelectorAll('#<?= $controllerUniqueID ?> .has-error').forEach(el => {
                            shakeElement(el);
                        });
                    });

                    // Show general error message
                    if (response.errors['general_error']) {
                        Swal.fire({
                            icon: 'error',
                            text: response.errors['general_error'],
                        });
                    }
                } else {
                    // Handle successful form submission
                    <?php if ($hasdetails): ?>
                        if (response.insertedid) {
                                                                        <?= $controllerUniqueID ?>getform(response.insertedid);
                        } else {
                            $('#tab_<?= $controllerUniqueID ?>_Table').click();
                                                                        <?= $controllerUniqueID ?>refreshAjax();
                        }
                    <?php else: ?>
                        $('#tab_<?= $controllerUniqueID ?>_Table').click();
                                                                    <?= $controllerUniqueID ?>refreshAjax();
                    <?php endif; ?>

                    // Show success message
                    const successMessage = response.insertedid
                        ? '<?= lang('Ragnos.Ragnos_record_inserted') ?>'
                        : '<?= lang('Ragnos.Ragnos_record_updated') ?>';
                    showToast(successMessage, 'success');
                }
            });
        });

        function <?= $controllerUniqueID ?>getform(id) {
            const formContent = $("#<?= $controllerUniqueID ?>_FormContent");
            formContent.html('').hide();

            <?php if ($master): ?>
                Ragnos_csrf.Ragnos_master = '<?= $master ?>';
            <?php endif; ?>

            getValue('<?= $clase ?>/getFormData/' + id, Ragnos_csrf, function (response) {
                formContent.html(response).show();
                formContent.find('[readonly]').addClass('text-bg-info');
            });
        }

        $('#<?= $controllerUniqueID ?> button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const targetTab = $(e.target).attr('data-bs-target');
            const formTab = "#<?= $controllerUniqueID ?>_Form";

            if (targetTab === formTab) {
                let activeId = $("#<?= $controllerUniqueID ?>").data('idactivo');

                if (!activeId) {
                    const firstRow = $("#<?= $controllerUniqueID ?>_table tbody tr").first();
                    const lastCell = firstRow.find('td').last();
                    const recordId = lastCell.attr('idr');

                    activeId = recordId ? recordId : '';
                    $("#<?= $controllerUniqueID ?>").data('idactivo', activeId);
                }

            <?= $controllerUniqueID ?>getform(activeId);
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

        const bodytable = $("#<?= $controllerUniqueID ?>_table tbody");

        bodytable.on('dblclick', 'tr', function () {
            var lastCell = $(this).find("td").last();
            var recordId = lastCell.attr('idr') || '';
            $("#<?= $controllerUniqueID ?>").data('idactivo', recordId);

            if (!lastCell.hasClass('dataTables_empty')) {
                $('#tab_<?= $controllerUniqueID ?>_Form').click();
            }
        });

        bodytable.on('mousedown', 'tr', function () {
            var lastCell = $(this).find("td").last();
            var recordId = lastCell.attr('idr') || '';
            $("#<?= $controllerUniqueID ?>").data('idactivo', recordId);

            $("#<?= $controllerUniqueID ?>_table tbody tr").removeClass('Ragnos_selected_row');
            $(this).addClass('Ragnos_selected_row');
        });

        let myModalAlternative<?= $controllerUniqueID ?>;

        bodytable.on('click', '.<?= $controllerUniqueID ?>deleteme', function (ev) {
            const row = $(this).closest('tr');
            const recordId = $(this).attr('idr');
            row.addClass('todelete');

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
                    row.removeClass('todelete');
                }
                $('#<?= $controllerUniqueID ?>_table').find('.todelete').removeClass('todelete');
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
      /*  $('#<?= $controllerUniqueID ?>_Tablediv.dataTables_filter input').data('objtable', $('#<?= $controllerUniqueID ?>_table')).unbind('keyup')
            .unbind('keypress')
            .unbind('input')
            .bind('keyup', function (e) {
                if (e.keyCode != 13)
                    return;
                $('#<?= $controllerUniqueID ?>_sel').focus();

            }).bind('change', function () {
                $(this).data('objtable').fnFilter($(this).val());
            });*/

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
            var selectedField = $('#<?= $controllerUniqueID ?>_sel').val();
            if (selectedField) {
                data.sOnlyField = selectedField;
            }

            <?php if ($master): ?>
                data.Ragnos_master = '<?= $master ?>';
            <?php endif; ?>

            const sourceUrl = '<?= site_url($clase . '/getAjaxGridData'); ?>';
            getObject(sourceUrl, data, function (response) {
                fnCallback(response);
                $("#<?= $controllerUniqueID ?>").data('idactivo', '');

                if (response.data.length > 0) {
                    $("#<?= $controllerUniqueID ?>_table tbody tr").each(function () {
                        var lastCell = $(this).find("td").last();
                        var recordId = lastCell.text();
                        lastCell.attr('idr', recordId);

                        <?php if ($modelo->canDelete): ?>
                            lastCell.html('<i class="bi bi-trash ybtndelete"></i>');
                            lastCell.addClass('<?= $controllerUniqueID ?>deleteme');
                        <?php else: ?>
                            lastCell.html('');
                        <?php endif; ?>
                    });
                }

                if (response.sSearch.value) {
                    $("#<?= $controllerUniqueID ?>_searching_title")
                        .text("<?= lang('Ragnos.Ragnos_searching') ?>" + " (" + response.sSearch.value + ") ...")
                        .show();
                } else {
                    $("#<?= $controllerUniqueID ?>_searching_title").text("").hide();
                }

                var preselectedIndex = $("#<?= $controllerUniqueID ?>").data('preselect');
                if (preselectedIndex > 0) {
                    var preselectedRow = $("#<?= $controllerUniqueID ?>_table tbody tr").get(preselectedIndex);
                    if (preselectedRow) {
                        var lastCell = $(preselectedRow).find("td").last();
                        var preselectedId = lastCell.attr('idr') || '';
                        $("#<?= $controllerUniqueID ?>").data('idactivo', preselectedId);
                        $(preselectedRow).addClass('Ragnos_selected_row');
                    }
                } else {
                    $("#<?= $controllerUniqueID ?>_table tbody tr").first().addClass('Ragnos_selected_row');
                }
            });
        }

    });
</script>