<script type="text/javascript">
    function <?= $controllerUniqueID ?>getDataTable() {
        const table = document.getElementById('<?= $controllerUniqueID ?>_table');
        return table.ragnosDataTable || new DataTable.Api(table);
    }

    function <?= $controllerUniqueID ?>refreshAjax() {
        const widget = document.getElementById('<?= $controllerUniqueID ?>');
        const table = document.getElementById('<?= $controllerUniqueID ?>_table');
        const rows = Array.from(table.tBodies[0]?.rows || []);
        widget.dataset.preselect = rows.indexOf(table.tBodies[0]?.querySelector('.Ragnos_selected_row'));
        const dataTable = <?= $controllerUniqueID ?>getDataTable();
        dataTable.ajax.reload(null, false);

        const onChange = window['_<?= $tableController ?>OnChange'];
        if (typeof onChange === 'function') onChange(dataTable);
    }

    document.getElementById('<?= $controllerUniqueID ?>btn_cancel').addEventListener('click', (event) => {
        event.preventDefault();
        document.getElementById('tab_<?= $controllerUniqueID ?>_Table').click();
        <?= $controllerUniqueID ?>refreshAjax();
    });

    document.getElementById('<?= $controllerUniqueID ?>btn_ok').addEventListener('click', (event) => {
        event.preventDefault();
        const widget = document.getElementById('<?= $controllerUniqueID ?>');
        const form = widget.querySelector('#<?= $controllerUniqueID ?>_FormContent form');
        if (!form) return;

        const formData = new FormData(form);
        form.querySelectorAll('input[type="datetime-local"]').forEach((input) => {
            if (input.name && input.value) formData.set(input.name, input.value.replace('T', ' '));
        });
        form.querySelectorAll('input[money]').forEach((input) => {
            if (input.value !== '') formData.set(input.name, moneyToNumber(input.value));
        });
        form.querySelectorAll('[data-valueant]').forEach((control) => {
            if (control.name) formData.append(`Ragnos_value_ant_${control.name}`, control.dataset.valueant || '');
        });
        Object.entries(globalThis.Ragnos_csrf || {}).forEach(([key, value]) => formData.append(key, value));

        form.querySelectorAll('.ui-state-error').forEach((error) => error.remove());
        widget.querySelectorAll('.has-error').forEach((group) => group.classList.remove('has-error'));

        uploadObject('<?= $clase ?>/formProcess', formData, (response, error) => {
            if (error) {
                console.error('Error submitting form:', error);
                Swal.fire({ icon: 'error', text: 'Error saving data. See console for details.' });
                return;
            }

            if (response.result !== 'ok') {
                Object.entries(response.errors || {}).forEach(([field, errorMessage]) => {
                    if (field === 'general_error') return;
                    const group = document.getElementById(`group_${field}`);
                    if (!group) return;
                    group.insertAdjacentHTML('beforeend', `<span class="ui-state-error badge text-bg-danger">${escapeHtml(String(errorMessage))}</span>`);
                    const tabPane = group.closest('.tab-pane');
                    const tabButton = tabPane ? document.querySelector(`button[data-bs-target="#${CSS.escape(tabPane.id)}"]`) : null;
                    if (tabButton) bootstrap.Tab.getOrCreateInstance(tabButton).show();
                    document.getElementById(field)?.focus();
                    group.classList.add('has-error');
                    shakeElement(group);
                });
                if (response.errors?.general_error) {
                    Swal.fire({ icon: 'error', text: response.errors.general_error });
                }
                return;
            }

            <?php if ($hasdetails): ?>
                if (response.insertedid) {
                    <?= $controllerUniqueID ?>getform(response.insertedid);
                } else {
                    document.getElementById('tab_<?= $controllerUniqueID ?>_Table').click();
                    <?= $controllerUniqueID ?>refreshAjax();
                }
            <?php else: ?>
                document.getElementById('tab_<?= $controllerUniqueID ?>_Table').click();
                <?= $controllerUniqueID ?>refreshAjax();
            <?php endif; ?>

            showToast(
                response.insertedid
                    ? '<?= lang('Ragnos.Ragnos_record_inserted') ?>'
                    : '<?= lang('Ragnos.Ragnos_record_updated') ?>',
                'success'
            );
        });
    });

    function <?= $controllerUniqueID ?>getform(id) {
        const formContent = document.getElementById('<?= $controllerUniqueID ?>_FormContent');
        formContent.replaceChildren();
        formContent.hidden = true;

        <?php if ($master): ?>
            Ragnos_csrf.Ragnos_master = <?= json_encode((string) $master, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        <?php endif; ?>

        getValue(`<?= $clase ?>/getFormData/${id}`, Ragnos_csrf, (response, error) => {
            if (error || response === null) {
                formContent.hidden = false;
                console.error('Unable to load form:', error);
                Swal.fire({ icon: 'error', text: '<?= lang('Ragnos.Ragnos_server_error') ?>' });
                return;
            }
            setHtml(formContent, response);
            formContent.hidden = false;
            formContent.querySelectorAll('[readonly]').forEach((control) => control.classList.add('text-bg-info'));
        });
    }

    document.querySelectorAll('#<?= $controllerUniqueID ?> button[data-bs-toggle="tab"]').forEach((button) => {
        button.addEventListener('shown.bs.tab', (event) => {
            if (event.target.dataset.bsTarget !== '#<?= $controllerUniqueID ?>_Form') return;
            const widget = document.getElementById('<?= $controllerUniqueID ?>');
            let activeId = widget.dataset.idactivo || '';
            if (!activeId) {
                activeId = widget.querySelector('tbody tr td:last-child')?.dataset.idr || '';
                widget.dataset.idactivo = activeId;
            }
            <?= $controllerUniqueID ?>getform(activeId);
        });
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

    (() => {
        const widget = document.getElementById('<?= $controllerUniqueID ?>');
        const table = document.getElementById('<?= $controllerUniqueID ?>_table');
        const tbody = table.tBodies[0];
        const search = document.querySelector('#<?= $controllerUniqueID ?>_Tablediv .dt-search');
        const combo = document.getElementById('<?= $controllerUniqueID ?>_combo');
        search?.append(combo);
        search?.classList.add('d-flex', 'flex-wrap', 'justify-content-between', 'align-items-center', 'ps-4', 'bg-body-secondary', 'rounded', 'border-start', 'border-4', 'border-primary', 'shadow-sm');

        const selectRow = (row) => {
            tbody.querySelectorAll('tr').forEach((item) => {
                item.classList.remove('Ragnos_selected_row');
                item.setAttribute('aria-selected', 'false');
            });
            row.classList.add('Ragnos_selected_row');
            row.setAttribute('aria-selected', 'true');
            widget.dataset.idactivo = row.querySelector('td:last-child')?.dataset.idr || '';
        };
        const openRow = (row) => {
            selectRow(row);
            if (!row.querySelector('td:last-child')?.classList.contains('dt-empty')) {
                document.getElementById('tab_<?= $controllerUniqueID ?>_Form').click();
            }
        };

        let lastTapTime = 0;
        tbody.addEventListener('dblclick', (event) => {
            const row = event.target.closest('tr');
            if (row) openRow(row);
        });
        tbody.addEventListener('touchend', (event) => {
            const now = Date.now();
            if (now - lastTapTime < 500 && now - lastTapTime > 0) {
                event.preventDefault();
                const row = event.target.closest('tr');
                if (row) openRow(row);
                lastTapTime = 0;
            } else {
                lastTapTime = now;
            }
        });
        tbody.addEventListener('mousedown', (event) => {
            const row = event.target.closest('tr');
            if (row) selectRow(row);
        });

        tbody.addEventListener('click', (event) => {
            const deleteCell = event.target.closest('.<?= $controllerUniqueID ?>deleteme');
            if (!deleteCell) return;
            event.preventDefault();
            event.stopPropagation();
            const row = deleteCell.closest('tr');
            row.classList.add('Ragnostodelete');

            Swal.fire({
                text: '<?= lang('Ragnos.Ragnos_delete_message') ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<?= lang('Ragnos.Ragnos_yes') ?>',
                cancelButtonText: '<?= lang('Ragnos.Ragnos_no') ?>'
            }).then((result) => {
                if (!result.isConfirmed) {
                    row.classList.remove('Ragnostodelete');
                    return;
                }

                const request = { id: deleteCell.dataset.idr, ...(globalThis.Ragnos_csrf || {}) };
                getObject('<?= $clase . '/getRecordByAjax' ?>', request, (record, fetchError) => {
                    if (fetchError || !record) {
                        row.classList.remove('Ragnostodelete');
                        console.error('Unable to load record before deletion:', fetchError);
                        Swal.fire({ icon: 'error', text: '<?= lang('Ragnos.Ragnos_server_error') ?>' });
                        return;
                    }
                    <?php foreach ($fieldlist as $fieldItem): ?>
                        request.Ragnos_value_ant_<?= $fieldItem->getFieldName(); ?> = record.<?= $fieldItem->getFieldName(); ?>;
                    <?php endforeach; ?>
                    getObject('<?= $clase . '/ajaxdelete' ?>', request, (response, deleteError) => {
                        row.classList.remove('Ragnostodelete');
                        if (deleteError || !response) {
                            console.error('Unable to delete record:', deleteError);
                            Swal.fire({ icon: 'error', text: '<?= lang('Ragnos.Ragnos_server_error') ?>' });
                            return;
                        }
                        if (response.result !== 'ok') {
                            Swal.fire({ icon: 'error', text: response.errors?.general_error || '<?= lang('Ragnos.Ragnos_server_error') ?>' });
                            return;
                        }
                        <?= $controllerUniqueID ?>refreshAjax();
                        showToast('<?= lang('Ragnos.Ragnos_record_deleted') ?>', 'success');
                    });
                });
            });
        });

        document.getElementById('btn_<?= $controllerUniqueID ?>_New')?.addEventListener('click', (event) => {
            event.preventDefault();
            widget.dataset.idactivo = 'new';
            document.getElementById('tab_<?= $controllerUniqueID ?>_Form').click();
        });
        document.getElementById('btn_<?= $controllerUniqueID ?>_Refresh')?.addEventListener('click', (event) => {
            event.preventDefault();
            <?= $controllerUniqueID ?>getDataTable().draw();
        });
        document.getElementById('<?= $controllerUniqueID ?>_sel')?.addEventListener('change', () => {
            document.getElementById('btn_<?= $controllerUniqueID ?>_Refresh').click();
        });
    })();

    function fnData2<?= $controllerUniqueID ?>(data, callback) {
        const widget = document.getElementById('<?= $controllerUniqueID ?>');
        const selectedField = document.getElementById('<?= $controllerUniqueID ?>_sel').value;
        if (selectedField) data.sOnlyField = selectedField;
        <?php if ($master): ?>
            data.Ragnos_master = <?= json_encode((string) $master, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        <?php endif; ?>

        getObject('<?= site_url($clase . '/getAjaxGridData'); ?>', data, (response, error) => {
            if (error || !response) {
                console.error('Unable to load table data:', error);
                callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                return;
            }
            callback(response);
            widget.dataset.idactivo = '';
            document.querySelectorAll('#<?= $controllerUniqueID ?>_table tbody tr').forEach((row) => {
                const lastCell = row.querySelector('td:last-child');
                const recordId = lastCell.textContent;
                lastCell.dataset.idr = recordId;
                <?php if ($modelo->canDelete): ?>
                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.className = 'btn btn-sm btn-link p-0 ybtndelete';
                    deleteButton.ariaLabel = <?= json_encode(lang('Ragnos.Ragnos_delete_message')) ?>;
                    deleteButton.innerHTML = '<i class="bi bi-trash" aria-hidden="true"></i>';
                    lastCell.replaceChildren(deleteButton);
                    lastCell.classList.add('<?= $controllerUniqueID ?>deleteme');
                <?php else: ?>
                    lastCell.replaceChildren();
                <?php endif; ?>
            });

            const searchTitle = document.getElementById('<?= $controllerUniqueID ?>_searching_title');
            const searchValue = response.sSearch?.value || '';
            searchTitle.textContent = searchValue ? `<?= lang('Ragnos.Ragnos_searching') ?> (${searchValue})...` : '';
            searchTitle.hidden = !searchValue;

            const rows = document.querySelectorAll('#<?= $controllerUniqueID ?>_table tbody tr');
            const preselectedIndex = Number(widget.dataset.preselect || 0);
            const selectedRow = rows[preselectedIndex] || rows[0];
            if (selectedRow) {
                selectedRow.classList.add('Ragnos_selected_row');
                selectedRow.setAttribute('aria-selected', 'true');
                widget.dataset.idactivo = selectedRow.querySelector('td:last-child')?.dataset.idr || '';
            }
        });
    }
</script>
