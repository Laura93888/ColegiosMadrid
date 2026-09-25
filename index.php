<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Consulta información sobre los colegios públicos de Madrid.">
    <title>Madrid Colegios</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <main class="contenedor">
        <header class="cabecera">
            <div>
                <span class="eyebrow">Guía educativa de Madrid</span>
                <h1>Madrid <span>Colegios</span></h1>
                <p>Encuentra un colegio público y consulta sus datos principales.</p>
            </div>
            <div class="resumen" aria-label="Resumen de resultados">
                <strong id="contador-total">—</strong>
                <span>centros disponibles</span>
            </div>
        </header>

        <section class="buscador" aria-label="Buscar colegios">
            <div class="buscador-icono" aria-hidden="true">⌕</div>
            <div class="buscador-campo">
                <label for="buscar-colegio">Buscar colegio</label>
                <input
                    id="buscar-colegio"
                    type="search"
                    placeholder="Escribe el nombre del colegio..."
                    autocomplete="off"
                    spellcheck="false"
                >
            </div>
            <span id="estado-busqueda" class="estado-busqueda">Escribe para filtrar</span>
        </section>

        <section class="aplicacion" id="aplicacion">
            <aside class="panel-lista" id="panel-lista" aria-label="Lista de colegios">
                <div class="cabecera-lista">
                    <div>
                        <span class="seccion-kicker">Directorio</span>
                        <h2>Colegios públicos</h2>
                    </div>
                    <span class="contador-lista" id="contador-lista">—</span>
                </div>

                <div id="estado-lista" class="estado-lista estado-cargando" aria-live="polite">
                    <div class="skeleton skeleton-titulo"></div>
                    <div class="skeleton"></div>
                    <div class="skeleton"></div>
                    <div class="skeleton"></div>
                    <span>Cargando colegios...</span>
                </div>
                <div class="lista-colegios" id="lista-colegios" hidden></div>
            </aside>

            <section class="panel-detalle" id="panel-detalle" aria-live="polite" tabindex="-1">
                <button class="boton-volver" id="boton-volver" type="button">
                    <span aria-hidden="true">←</span> Volver a la lista
                </button>
                <div class="sin-seleccionar" id="detalle-vacio">
                    <div class="icono-vacio" aria-hidden="true">🏫</div>
                    <span class="detalle-kicker">Consulta un centro</span>
                    <h2>Selecciona un colegio</h2>
                    <p>Elige un colegio de la lista para consultar su dirección, descripción y ubicación.</p>
                </div>
                <div id="detalle-contenido" hidden></div>
            </section>
        </section>
    </main>

    <script>
        (() => {
            const state = {
                colegios: [],
                filtrados: [],
                seleccionado: null,
                timerBusqueda: null
            };
            //NECESITAMOS TODOS ESTOS DATOS PARA MOSTRARLOS Y USARLOS
            
            const aplicacion = document.querySelector("#aplicacion");
            const inputBusqueda = document.querySelector("#buscar-colegio");
            const lista = document.querySelector("#lista-colegios");
            const estadoLista = document.querySelector("#estado-lista");
            const estadoBusqueda = document.querySelector("#estado-busqueda");
            const detalleVacio = document.querySelector("#detalle-vacio");
            const detalleContenido = document.querySelector("#detalle-contenido");
            const panelDetalle = document.querySelector("#panel-detalle");
            const botonVolver = document.querySelector("#boton-volver");
            const contadorTotal = document.querySelector("#contador-total");
            const contadorLista = document.querySelector("#contador-lista");

            const escapeHtml = (value) => String(value ?? "")
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            const texto = (value, fallback = "No disponible") => {
                const result = String(value ?? "").trim();
                return result || fallback;
            };

            const nombreColegio = (colegio) => texto(colegio.title, "Colegio sin nombre");

            const obtenerCoordenadas = (colegio) => {
                const latitud = Number(colegio?.location?.latitude);
                const longitud = Number(colegio?.location?.longitude);

                if (!Number.isFinite(latitud) || !Number.isFinite(longitud)) {
                    return null;
                }

                return { latitud, longitud };
            };

            const mostrarEstado = (tipo, mensaje) => {
                estadoLista.className = `estado-lista estado-${tipo}`;
                estadoLista.innerHTML = `<span>${escapeHtml(mensaje)}</span>`;
                estadoLista.hidden = false;
                lista.hidden = true;
            };

            const mostrarLista = () => {
                if (state.filtrados.length === 0) {
                    mostrarEstado(
                        "vacio",
                        inputBusqueda.value.trim()
                            ? "No encontramos colegios con esa búsqueda."
                            : "No hay colegios disponibles."
                    );
                    contadorLista.textContent = "0";
                    return;
                }

                estadoLista.hidden = true;
                lista.hidden = false;
                contadorLista.textContent = state.filtrados.length;
                lista.innerHTML = state.filtrados.map((colegio) => {
                    const id = escapeHtml(colegio.id);
                    const activo = String(colegio.id) === String(state.seleccionado?.id);

                    return `
                        <button
                            class="colegio ${activo ? "activo" : ""}"
                            type="button"
                            data-id="${id}"
                            aria-pressed="${activo}"
                        >
                            <span class="colegio-punto" aria-hidden="true"></span>
                            <span class="colegio-nombre">${escapeHtml(nombreColegio(colegio))}</span>
                            <span class="colegio-flecha" aria-hidden="true">›</span>
                        </button>
                    `;
                }).join("");
            };

            const renderDetalle = (colegio) => {
                if (!colegio) {
                    state.seleccionado = null;
                    detalleContenido.hidden = true;
                    detalleVacio.hidden = false;
                    return;
                }

                const coordenadas = obtenerCoordenadas(colegio);
                const titulo = nombreColegio(colegio);
                const localidad = texto(colegio?.address?.locality);
                const codigoPostal = texto(colegio?.address?.["postal-code"]);
                const direccion = texto(colegio?.address?.["street-address"]);
                const descripcion = texto(colegio?.organization?.["organization-desc"]);
                const servicios = texto(colegio?.organization?.services);
                const googleMaps = coordenadas
                    ? `https://www.google.com/maps?q=${coordenadas.latitud},${coordenadas.longitud}`
                    : null;
                const mapa = coordenadas
                    ? `
                        <div class="mapa-contenedor">
                            <div class="subseccion-cabecera">
                                <div>
                                    <span class="seccion-kicker">Ubicación</span>
                                    <h3>Cómo llegar</h3>
                                </div>
                                <span class="coordenadas">${coordenadas.latitud.toFixed(4)}, ${coordenadas.longitud.toFixed(4)}</span>
                            </div>
                            <iframe
                                class="mapa"
                                title="Mapa de ${escapeHtml(titulo)}"
                                src="https://www.google.com/maps?q=${coordenadas.latitud},${coordenadas.longitud}&z=15&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </div>
                    `
                    : "";

                state.seleccionado = colegio;
                detalleVacio.hidden = true;
                detalleContenido.hidden = false;
                detalleContenido.innerHTML = `
                    <div class="detalle-encabezado">
                        <span class="etiqueta">Colegio público</span>
                        <h2>${escapeHtml(titulo)}</h2>
                        <p class="detalle-id">Información actualizada desde el catálogo de datos de Madrid</p>
                    </div>

                    <div class="datos-grid">
                        <div class="dato">
                            <span class="dato-icono" aria-hidden="true">⌖</span>
                            <div><strong>Localidad</strong><p>${escapeHtml(localidad)}</p></div>
                        </div>
                        <div class="dato">
                            <span class="dato-icono" aria-hidden="true">#</span>
                            <div><strong>Código postal</strong><p>${escapeHtml(codigoPostal)}</p></div>
                        </div>
                        <div class="dato dato-ancho">
                            <span class="dato-icono" aria-hidden="true">⌂</span>
                            <div><strong>Dirección</strong><p>${escapeHtml(direccion)}</p></div>
                        </div>
                    </div>

                    <div class="detalle-acciones">
                        ${googleMaps ? `<a class="boton-mapa" href="${googleMaps}" target="_blank" rel="noopener noreferrer">Abrir en Google Maps <span aria-hidden="true">↗</span></a>` : ""}
                    </div>

                    <div class="descripcion">
                        <span class="seccion-kicker">Sobre el centro</span>
                        <h3>Descripción</h3>
                        <p>${escapeHtml(descripcion)}</p>
                    </div>

                    <div class="descripcion">
                        <span class="seccion-kicker">Información adicional</span>
                        <h3>Servicios</h3>
                        <p>${escapeHtml(servicios)}</p>
                    </div>

                    ${mapa}
                `;
            };

            const seleccionarColegio = (id) => {
                const colegio = state.colegios.find((item) => String(item.id) === String(id));
                if (!colegio) return;

                renderDetalle(colegio);
                mostrarLista();
                aplicacion.classList.add("detalle-visible");
                panelDetalle.focus({ preventScroll: true });
                panelDetalle.scrollIntoView({ behavior: "smooth", block: "start" });
            };

            const filtrar = () => {
                const consulta = inputBusqueda.value.trim().toLocaleLowerCase("es");

                state.filtrados = state.colegios.filter((colegio) => {
                    const nombre = nombreColegio(colegio).toLocaleLowerCase("es");
                    const localidad = texto(colegio?.address?.locality, "").toLocaleLowerCase("es");
                    return !consulta || nombre.includes(consulta) || localidad.includes(consulta);
                });

                estadoBusqueda.textContent = consulta
                    ? `${state.filtrados.length} resultado${state.filtrados.length === 1 ? "" : "s"}`
                    : "Escribe para filtrar";
                mostrarLista();
            };

            const cargarColegios = async () => {
                mostrarEstado("cargando", "Cargando colegios...");

                try {
                    const respuesta = await fetch("api.php", {
                        headers: { "Accept": "application/json" }
                    });
                    const datos = await respuesta.json();

                    if (!respuesta.ok || !Array.isArray(datos.colegios)) {
                        throw new Error(datos.error || "La respuesta no es válida.");
                    }

                    state.colegios = datos.colegios;
                    state.filtrados = datos.colegios;
                    contadorTotal.textContent = datos.colegios.length;
                    filtrar();
                } catch (error) {
                    contadorTotal.textContent = "—";
                    contadorLista.textContent = "0";
                    mostrarEstado("error", error.message || "No se han podido cargar los colegios.");
                }
            };

            inputBusqueda.addEventListener("input", () => {
                estadoBusqueda.textContent = "Buscando...";
                window.clearTimeout(state.timerBusqueda);
                state.timerBusqueda = window.setTimeout(filtrar, 300);
            });

            lista.addEventListener("click", (event) => {
                const boton = event.target.closest("[data-id]");
                if (boton) seleccionarColegio(boton.dataset.id);
            });

            botonVolver.addEventListener("click", () => {
                aplicacion.classList.remove("detalle-visible");
                inputBusqueda.focus();
                window.scrollTo({ top: 0, behavior: "smooth" });
            });

            cargarColegios();
        })();
    </script>
</body>

</html>