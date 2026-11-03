function pruebaBusquedaOffice(e) {
  let datos = e.ragnosSearchData;
  console.log("Los datos de la busqueda por oficina", datos);
}

//si cambia el producto ajusta el precio unitario
function _productCodeOnSearch(control) {
  document.querySelector('#detalleorden input[name="priceEach"]').value =
    control.ragnosSearchData.MSRP;
}

// con cada cambio en la tabla de detalles de ordenes
// recalcula el total de la orden
function _OrdenesdetallesOnChange(tabla) {
  let orden = document.querySelector("input[name='orderNumber']").value;
  getObject("tienda/ordenes/calculatotal", { orden: orden }, function (data) {
    document.querySelector('input[name="total"]').value = data.total;
  });
}
