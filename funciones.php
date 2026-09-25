<?php

//LLAMAMOS A LA API PARA OBETENER LOS DATOS
function callApi($url){

    // Inicializamos cURL con la URL de la petición
    $ch = curl_init($url);

    // Indicamos que esperamos una respuesta en JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);

    // Permitimos seguir redirecciones
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Guardamos la respuesta en una variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    // Ejecutamos la petición
    $resp = curl_exec($ch);

    // Comprobamos si cURL ha funcionado
    $curl_error = curl_errno($ch);

    if($curl_error == 0){

        // Obtenemos el código HTTP
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Si todo ha ido bien, devolvemos la respuesta
        if($http_code == 200){

            curl_close($ch);
            return $resp;

        }else{

            curl_close($ch);
            return -1;
        }

    }else{

        curl_close($ch);
        return -2;
    }
}


/**
 * Decodifica entidades HTML en todos los textos de una respuesta de la API.
 */
function decodeHtmlEntitiesRecursive($value){

    if(is_string($value)){
        $decoded = $value;

        // Algunas entradas del catálogo llegan doblemente codificadas
        // (por ejemplo, &amp;uuml;). Repetimos hasta que no haya cambios.
        for($i = 0; $i < 3; $i++){
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, "UTF-8");

            if($next === $decoded){
                break;
            }

            $decoded = $next;
        }

        return $decoded;
    }

    if(is_array($value)){
        foreach($value as $key => $item){
            $value[$key] = decodeHtmlEntitiesRecursive($item);
        }
    }

    return $value;
}

