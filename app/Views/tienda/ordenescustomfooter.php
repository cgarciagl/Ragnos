<hr />
<div class="row clearfix" id="panelorden">
    <div class="card text-bg-dark">
        <h5 class="card-header">Detalles</h5>
        <div class="card-body">
            <div id="detalleorden">

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let orden = $("input[name='orderNumber']").val();
        if (orden == '') {
            $("#panelorden").remove();
        } else {
            RagnosUtils.showControllerTableIn('#detalleorden', 'tienda/ordenesdetalles', orden);
        }
    });
</script>