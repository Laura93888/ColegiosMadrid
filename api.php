<?php

require_once "funciones.php";

header("Content-Type: application/json; charset=UTF-8");

$url = "https://datos.madrid.es/egob/catalogo/202311-0-colegios-publicos.json";
$resp = callApi($url);

if($resp === -1 || $resp === -2){
    http_response_code(502);

    echo json_encode([
        "error" => "No se ha podido obtener la información de los colegios. Inténtalo de nuevo."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$json = json_decode($resp, true);

if(!is_array($json) || !isset($json["@graph"]) || !is_array($json["@graph"])){
    http_response_code(502);

    echo json_encode([
        "error" => "La respuesta de la fuente de datos no tiene un formato válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$colegios = decodeHtmlEntitiesRecursive($json["@graph"]);

echo json_encode([
    "colegios" => $colegios
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);