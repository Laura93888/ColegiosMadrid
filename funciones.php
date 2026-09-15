<?php

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

    // Ejecutamos la petición
    $resp = curl_exec($ch);

    // Comprobamos si cURL ha funcionado
    if(curl_errno($ch) == 0){

        // Obtenemos el código HTTP
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Si todo ha ido bien, devolvemos la respuesta
        if($http_code == 200){

            return $resp;

        }else{

            return -1;
        }

    }else{

        return -2;
    }
}

