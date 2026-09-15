<?php

require_once "funciones.php";

$url = "https://datos.madrid.es/egob/catalogo/202311-0-colegios-publicos.json";

$resp = callApi($url);

if($resp !== -1 && $resp !== -2){

    // Convertimos el JSON en un array PHP
    $json = json_decode($resp, true);

    // Los colegios están dentro de @graph
    $colegios = $json["@graph"];

    // Variable para guardar la lista de colegios
    $lista = "";

    // Valor del buscador
    $valorbuscar = "";

    // Recorremos los colegios
    foreach($colegios as $valor){

        // Si se ha realizado una búsqueda
        if(isset($_GET["valor"])){

            $valorbuscar = $_GET["valor"];

            // Comprobamos si el nombre contiene el texto buscado
            if(str_contains(
                strtolower($valor["title"]),
                strtolower($valorbuscar)
            )){

                $lista .= "
                    <a
                        class='colegio'
                        href='" . $_SERVER['PHP_SELF'] . "?id=" . $valor["id"] . "&valor=" . $valorbuscar . "'
                    >
                        " . $valor["title"] . "
                    </a>
                ";
            }

        }else{

            // Si no hay búsqueda, mostramos todos los colegios
            $lista .= "
                <a
                    class='colegio'
                    href='" . $_SERVER['PHP_SELF'] . "?id=" . $valor["id"] . "'
                >
                    " . $valor["title"] . "
                </a>
            ";
        }

        // Si se ha seleccionado un colegio
        if(isset($_GET["id"])){

            if($valor["id"] == $_GET["id"]){

                // Guardamos el colegio seleccionado
                $elemento = $valor;

                // Obtenemos sus coordenadas
                $lat = $valor["location"]["latitude"];
                $lon = $valor["location"]["longitude"];

                // Creamos el enlace de Google Maps
                $googleMaps = "https://www.google.com/maps?q=$lat,$lon";
            }
        }
    }

}else{

    $error = "Ha habido un problema al conectar con la API.";

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Madrid Colegios</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Nuestro CSS -->
    <link rel="stylesheet" href="styles.css">

</head>


<body>

    <main class="contenedor">

        <!-- CABECERA -->

        <header class="cabecera">

            <h1>Madrid Colegios</h1>

            <p>
                Consulta información sobre los colegios públicos de Madrid
            </p>

        </header>


        <!-- BUSCADOR -->

        <section class="buscador">

            <form
                action="<?= $_SERVER["PHP_SELF"] ?>"
                method="get"
            >

                <input
                    type="text"
                    name="valor"
                    value="<?= $valorbuscar ?>"
                    placeholder="Buscar colegio..."
                >

                <button
                    name="buscar"
                    type="submit"
                    value="buscar"
                >
                    Buscar
                </button>

            </form>

        </section>


        <!-- MENSAJE DE ERROR -->

        <?php if(isset($error)){ ?>

            <div class="mensaje-error">

                <?= $error ?>

            </div>

        <?php } ?>


        <!-- APLICACIÓN -->

        <section class="aplicacion">


            <!-- LISTA DE COLEGIOS -->

            <div class="panel-lista">

                <div class="cabecera-lista">

                    <h2>Colegios</h2>

                    <?php if($valorbuscar != ""){ ?>

                        <p>
                            Resultados para:
                            <strong><?= $valorbuscar ?></strong>
                        </p>

                    <?php } else { ?>

                        <p>
                            Selecciona un colegio de la lista
                        </p>

                    <?php } ?>

                </div>


                <div class="lista-colegios">

                    <?= $lista ?>

                </div>

            </div>


            <!-- INFORMACIÓN DEL COLEGIO -->

            <div class="panel-detalle">


                <?php if(isset($_GET["id"]) && isset($elemento)){ ?>


                    <span class="etiqueta">
                        Información del colegio
                    </span>


                    <h2>
                        <?= $elemento["title"] ?>
                    </h2>


                    <!-- LOCALIDAD -->

                    <div class="dato">

                        <span>📍</span>

                        <div>

                            <strong>Localidad</strong>

                            <p>
                                <?= $elemento["address"]["locality"] ?>
                            </p>

                        </div>

                    </div>


                    <!-- CÓDIGO POSTAL -->

                    <div class="dato">

                        <span>📮</span>

                        <div>

                            <strong>Código postal</strong>

                            <p>
                                <?= $elemento["address"]["postal-code"] ?>
                            </p>

                        </div>

                    </div>


                    <!-- DIRECCIÓN -->

                    <div class="dato">

                        <span>🏫</span>

                        <div>

                            <strong>Dirección</strong>

                            <p>
                                <?= $elemento["address"]["street-address"] ?>
                            </p>

                        </div>

                    </div>


                    <!-- GOOGLE MAPS -->

                    <a
                        class="boton-mapa"
                        href="<?= $googleMaps ?>"
                        target="_blank"
                    >
                        🗺️ Ver en Google Maps
                    </a>


                    <!-- DESCRIPCIÓN -->

                    <div class="descripcion">

                        <h3>
                            Descripción
                        </h3>

                        <p>
                            <?= $elemento["organization"]["organization-desc"] ?>
                        </p>

                    </div>


                    <!-- SERVICIOS -->

                    <div class="descripcion">

                        <h3>
                            Servicios
                        </h3>

                        <p>
                            <?= $elemento["organization"]["services"] ?>
                        </p>

                    </div>


                <?php } else { ?>


                    <!-- ESTADO INICIAL -->

                    <div class="sin-seleccionar">

                        <div class="icono-vacio">
                            🏫
                        </div>

                        <h2>
                            Selecciona un colegio
                        </h2>

                        <p>
                            Elige un colegio de la lista para consultar
                            su información.
                        </p>

                    </div>


                <?php } ?>


            </div>

        </section>

    </main>

</body>

</html>