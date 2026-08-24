<div class="row">
    <div id="formbusqueda" style="width: 90%;">
        <label for="buscando">Buscando: </label>
        <input type="text" name="buscando" id="buscando" value="<?= @$valorabuscar ?>" style="width: 90%">
    </div>
</div>

<hr>
<div class='col-md-12'>
    <div id="respuestabusqueda" class="tablediv card boxshadowround Ragnos-widget Ragnos-search-widget d-none"></div>
</div>
<script>
    onReady(() => {
        const input = document.getElementById('buscando');
        const results = document.getElementById('respuestabusqueda');

        input.addEventListener('input', () => {
            debounce(buscar, 400, input);
        });

        function buscar() {
            const buscando = input.value;
            getObject('<?= @$ruta ?>', {
                searchTerm: buscando
            }, function (data) {

                let res = muestraResultado(data);
                destroyDataTable(results.querySelector('table'));
                results.innerHTML = res;
                results.classList.remove('d-none');
                if (data.resultado == 'NO') {
                    shakeElement(results);
                }

                const table = results.querySelector('table');
                table?.classList.add('table', 'table-striped', 'table-bordered', 'table-hover', 'table-sm');
                table?.addEventListener('click', (event) => {
                    const row = event.target.closest('tr');
                    if (!row || !row.parentElement.matches('tbody')) return;
                    cerrarModal(data.datos[Array.from(row.parentElement.rows).indexOf(row)]);
                });

                if (table) ponTablaPaginada(table, { ordering: false });

                setTimeout(function () {
                    input.focus();
                }, 500);
            });
        }

        function muestraResultado(data) {
            let s = '';
            if (data.resultado == 'NO') {
                s = data.mensaje;
                results.classList.add('alert-danger');
            }
            if (data.resultado == 'SI') {
                s = convertToTable(data.datos);
                results.classList.remove('alert-danger');
            }
            return s;
        }

        function cerrarModal(ResultData) {
            const modal = document.getElementById('busquedaModal');
            if (!modal) return;
            modal.ragnosResultData = ResultData;
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        }

        buscar();

    });
</script>

<style>
    #respuestabusqueda table {
        cursor: pointer;
    }

    #respuestabusqueda table tbody tr:hover {
        color: whitesmoke;
        background-color: navy;
    }
</style>
