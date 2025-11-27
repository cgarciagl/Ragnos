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
    $(function () {

        $('#buscando').on('input', function () {
            debounce(function () { buscar() }, 400);
        });

        function buscar() {
            let buscando = $('#buscando').val();
            getObject('<?= @$ruta ?>', {
                searchTerm: buscando
            }, function (data) {

                let res = muestraResultado(data);
                $('#respuestabusqueda').hide().html(res).removeClass('d-none').show();
                if (data.resultado == 'NO') {
                    $('#respuestabusqueda').shake();
                }

                let tabla = $('#respuestabusqueda table');
                tabla.addClass('table table-striped table-bordered table-hover table-sm');
                tabla.on('click', 'td', function () {
                    let fila = $(this).parent('tr').index();
                    let ResultData = data.datos[fila];
                    cerrarModal(ResultData);
                    return;
                });

                ponTablaPaginada(tabla, { ordering: false });

                setTimeout(function () {
                    $('#buscando').focus();
                }, 500);
            });
        }

        function muestraResultado(data) {
            let s = '';
            if (data.resultado == 'NO') {
                s = data.mensaje;
                $('#respuestabusqueda').addClass('alert-danger');
            }
            if (data.resultado == 'SI') {
                r = data.datos;
                s = convertToTable(r);
                $('#respuestabusqueda').removeClass('alert-danger');
            }
            return s;
        }

        function cerrarModal(ResultData) {
            mimodal = $('#busquedaModal')
            mimodal.data('ResultData', ResultData);
            try {
                if (mimodal.length == 1) {
                    mimodal.modal('hide')
                }
            } catch (error) { }
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