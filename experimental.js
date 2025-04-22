async function getValue(purl, pparameters, callback) {
  try {
    let valor = "N/A_";
    let asinc = true;
    let to = 1200000;

    if (pparameters && pparameters.timeout !== undefined) {
      to = pparameters.timeout;
    }

    const response = await fetch(fixUrl(purl), {
      method: "POST",
      body: JSON.stringify(pparameters),
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      mode: "cors", // Puedes ajustar esto según tus necesidades
      cache: "no-cache",
      credentials: "same-origin",
      redirect: "follow",
      referrerPolicy: "no-referrer",
      timeout: to,
    });

    if (!response.ok) {
      throw new Error(`Request failed with status: ${response.status}`);
    }

    valor = await response.text();

    // Llamar a la función de callback si se proporciona
    if (typeof callback === "function") {
      callback(valor);
    }

    return valor;
  } catch (error) {
    throw error;
  }
}

async function getObject(purl, pparameters, callback) {
  try {
    const value = await getValue(purl, pparameters);

    // Parsear el valor obtenido
    const parsedValue = await JSON.parse(value);

    // Llamar al callback con el valor parseado si se proporciona
    if (typeof callback === "function") {
      callback(parsedValue);
    }

    return parsedValue;
  } catch (error) {
    throw error;
  }
}

/*
Uso Sincrono: 

            (async () => {
                try {
                    p = await getObject2('admin/test', {});
                    console.log('en la funcion, p es ', p);
                    $('h1.test').text(p.usuario);
                } catch (error) {

                }
            })();

Uso Asincrono: getObject2('admin/test',{}).then((p)=>{console.log(p);});
*/
