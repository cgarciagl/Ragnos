<script type="text/javascript">
    (() => {
        const widget = document.getElementById('<?= $controllerUniqueID ?>');
        const table = document.getElementById('<?= $controllerUniqueID ?>_table');
        const tbody = table.tBodies[0];
        const adminDiv = document.getElementById('<?= $controllerUniqueID ?>admin_div');
        adminDiv.hidden = true;

        function getDataTable() {
            return table.ragnosDataTable || new DataTable.Api(table);
        }

        function removeSearchWidget() {
            destroyDataTable(table);
            widget.remove();
        }

        window['<?= $controllerUniqueID ?>refreshAjax'] = () => {
            const rows = Array.from(tbody.rows);
            widget.dataset.preselect = rows.indexOf(tbody.querySelector('.Ragnos_selected_row'));
            getDataTable().ajax.reload(null, false);
        };

        document.getElementById('<?= $controllerUniqueID ?>btn_search_admin')?.addEventListener('click', async (event) => {
            event.preventDefault();
            try {
                const content = await getValue('<?= $clase ?>/tableByAjax/', Ragnos_csrf);
                widget.hidden = true;
                setHtml(document.getElementById('<?= $controllerUniqueID ?>admin_container'), content);
                adminDiv.hidden = false;
            } catch (error) {
                console.error('Unable to load administration table:', error);
                Swal.fire({ icon: 'error', text: '<?= lang('Ragnos.Ragnos_server_error') ?>' });
            }
        });

        document.getElementById('<?= $controllerUniqueID ?>btn_search_admin_back')?.addEventListener('click', (event) => {
            event.preventDefault();
            widget.hidden = false;
            adminDiv.hidden = true;
            window['<?= $controllerUniqueID ?>refreshAjax']();
        });

        document.getElementById('<?= $controllerUniqueID ?>btn_ok_search').addEventListener('click', (event) => {
            event.preventDefault();
            const selectedRow = tbody.querySelector('tr.Ragnos_selected_row');
            const cells = Array.from(selectedRow?.cells || []);
            const lastCell = cells.at(-1);
            const isEmpty = cells[0]?.classList.contains('dt-empty');
            const result = {
                id: isEmpty ? '' : lastCell?.dataset.idr || '',
                name: isEmpty ? '' : cells[0]?.textContent || ''
            };

            removeSearchWidget();
            const target = RagnosSearch.searchStack.pop();
            if (target) {
                target.value = result.name;
                target.dataset.id = result.id;
                target.dataset.name = result.name;
                const hidden = target.closest('.input-group')?.nextElementSibling;
                if (hidden?.matches('input[type=hidden]')) hidden.value = result.id;

                const tableFields = <?= json_encode($tablefields) ?>;
                const searchData = { y_id: result.id, y_name: result.name, <?= json_encode($primaryKey) ?>: result.id };
                cells.forEach((cell, index) => {
                    if (tableFields[index]) searchData[tableFields[index]] = cell.textContent;
                });
                target.ragnosSearchData = searchData;
                const callback = window[`_${target.id}OnSearch`];
                if (typeof callback === 'function') callback(target);
            }

            cierraModal('YSearchModal');
            target?.closest('.divfield')?.nextElementSibling?.querySelector('input, textarea, select')?.focus();
        });

        document.getElementById('<?= $controllerUniqueID ?>btn_cancel_search').addEventListener('click', (event) => {
            event.preventDefault();
            removeSearchWidget();
            const target = RagnosSearch.searchStack.pop();
            if (target?.dataset.name) target.value = target.dataset.name;
            cierraModal('YSearchModal');
        });

        <?= view('App\ThirdParty\Ragnos\Views\rdatasetcontroller/datatable_init', ['controllerUniqueID' => $controllerUniqueID, 'tableController' => $tableController]); ?>

        const search = document.querySelector('#<?= $controllerUniqueID ?>_Tablediv .dt-search');
        search?.append(document.getElementById('<?= $controllerUniqueID ?>_combo'));
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
        const confirmRow = (row) => {
            selectRow(row);
            if (!row.querySelector('td:last-child')?.classList.contains('dt-empty')) {
                document.getElementById('<?= $controllerUniqueID ?>btn_ok_search').click();
            }
        };

        const modal = tbody.closest('.modal');
        modal?.classList.remove('fade');
        modal?.removeAttribute('data-bs-keyboard');
        modal?.addEventListener('keydown', (event) => {
            const isEditing = event.target.matches('input, textarea, select, [contenteditable="true"]');
            if (isEditing && event.key !== 'Escape') return;
            const selected = tbody.querySelector('.Ragnos_selected_row');
            if (!selected) return;
            if (event.key === 'ArrowDown' && selected.nextElementSibling) selectRow(selected.nextElementSibling);
            else if (event.key === 'ArrowUp' && selected.previousElementSibling) selectRow(selected.previousElementSibling);
            else if (event.key === ' ' || event.key === 'Enter') confirmRow(selected);
            else if (event.key === 'Escape') document.getElementById('<?= $controllerUniqueID ?>btn_cancel_search').click();
            else return;
            event.preventDefault();
        });

        let lastTapTime = 0;
        tbody.addEventListener('dblclick', (event) => {
            const row = event.target.closest('tr');
            if (row) confirmRow(row);
        });
        tbody.addEventListener('touchend', (event) => {
            const now = Date.now();
            if (now - lastTapTime < 500 && now - lastTapTime > 0) {
                event.preventDefault();
                const row = event.target.closest('tr');
                if (row) confirmRow(row);
                lastTapTime = 0;
            } else lastTapTime = now;
        });
        tbody.addEventListener('mousedown', (event) => {
            const row = event.target.closest('tr');
            if (row) selectRow(row);
        });
    })();

    function fnData2<?= $controllerUniqueID ?>(data, callback) {
        const onlyField = document.getElementById('<?= $controllerUniqueID ?>_sel').value;
        if (onlyField) data.sOnlyField = onlyField;

        const searchValue = <?= json_encode((string) $sSearch) ?>;
        const filterValue = <?= json_encode((string) $sFilter) ?>;
        if (searchValue && !data.search.value) data.search.value = searchValue;
        if (filterValue) data.sFilter = filterValue;

        getObject('<?= site_url($clase . '/getAjaxGridData'); ?>', data, (response, error) => {
            if (error || !response) {
                console.error('Unable to load search data:', error);
                callback({ draw: data.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                return;
            }
            callback(response);
            const widget = document.getElementById('<?= $controllerUniqueID ?>');
            widget.dataset.idactivo = '';
            document.querySelectorAll('#<?= $controllerUniqueID ?>_table tbody tr').forEach((row) => {
                const lastCell = row.querySelector('td:last-child');
                lastCell.dataset.idr = lastCell.textContent;
                lastCell.replaceChildren();
            });

            const searchTitle = document.getElementById('<?= $controllerUniqueID ?>_searching_title');
            const actualSearch = response.sSearch?.value || '';
            searchTitle.textContent = actualSearch ? `<?= lang('Ragnos.Ragnos_searching') ?> (${actualSearch})...` : '';
            searchTitle.hidden = !actualSearch;

            const firstRow = document.querySelector('#<?= $controllerUniqueID ?>_table tbody tr');
            if (firstRow) {
                firstRow.classList.add('Ragnos_selected_row');
                firstRow.setAttribute('aria-selected', 'true');
            }
            if (response.data.length === 1 && response.recordsTotal === 1 && actualSearch) {
                document.getElementById('<?= $controllerUniqueID ?>btn_ok_search').click();
            }
        });
    }
</script>
