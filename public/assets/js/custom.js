function pruebaBusquedaOffice(e) {
  let datos = e.data("searchdata");
  console.log("Los datos de la busqueda por oficina", datos);
}

//si cambia el producto ajusta el precio unitario
function _productCodeOnSearch(control) {
  $('#detalleorden input[name="priceEach"]').val(
    control.data("searchdata").MSRP
  );
}

// con cada cambio en la tabla de detalles de ordenes
// recalcula el total de la orden
function _OrdenesdetallesOnChange(tabla) {
  let orden = $("input[name='orderNumber']").val();
  getObject("tienda/ordenes/calculatotal", { orden: orden }, function (data) {
    $('input[name="total"]').val(data.total);
  });
}
