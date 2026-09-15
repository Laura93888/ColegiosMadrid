# 🏫 Madrid Colegios

Aplicación web desarrollada con **PHP** que permite consultar información sobre los colegios públicos de Madrid utilizando una **API pública de datos abiertos del Ayuntamiento de Madrid**.

El proyecto está enfocado en el consumo de APIs, el tratamiento de datos en formato **JSON** y la generación dinámica de contenido con PHP.

---

## 📌 Funcionalidades

* Consulta de colegios públicos de Madrid mediante una API.
* Procesamiento de la respuesta en formato JSON.
* Listado dinámico de colegios.
* Búsqueda de colegios por nombre.
* Consulta de información detallada de cada colegio.
* Visualización de localidad, código postal y dirección.
* Obtención de coordenadas geográficas.
* Enlace directo a Google Maps.
* Mensaje cuando no se encuentra ningún colegio.
* Gestión básica de errores durante la conexión con la API.
* Diseño responsive para diferentes tamaños de pantalla.

---

## 🛠️ Tecnologías utilizadas

* **PHP**
* **JSON**
* **cURL**
* **HTML5**
* **CSS3**
* **Bootstrap**
* **GET** para realizar búsquedas y seleccionar colegios.

---

## 🔄 Trabajo con API y JSON

La aplicación obtiene los datos de los colegios mediante una petición HTTP realizada con **cURL**.

La respuesta de la API se recibe en formato JSON y posteriormente se convierte a un array asociativo de PHP mediante:

$json = json_decode($resp, true);

Los colegios se encuentran dentro de la propiedad `@graph` de la respuesta:

$colegios = $json["@graph"];


A partir de estos datos se recorren los colegios y se muestran dinámicamente en la aplicación.

También se trabaja con **datos anidados**, por ejemplo, para obtener la localidad o las coordenadas:

$valor["address"]["locality"]

$valor["location"]["latitude"]

$valor["location"]["longitude"]

Esto permite utilizar diferentes partes de la información proporcionada por la API para construir la interfaz.

---

## 🔎 Búsqueda y filtrado

La búsqueda se realiza mediante un formulario `GET`.

El texto introducido por el usuario se compara con el nombre de cada colegio utilizando `str_contains()`:

if(!str_contains(
    strtolower($valor["title"]),
    strtolower($valorbuscar)
)){
    continue;
}


De esta forma, se muestran únicamente los colegios cuyo nombre contiene el texto introducido.

Si no se encuentra ningún resultado, la aplicación muestra un mensaje informativo al usuario.

---

## 📍 Localización

Los datos proporcionados por la API incluyen las coordenadas geográficas de los colegios.

A partir de la latitud y longitud se genera dinámicamente un enlace a Google Maps:

$googleMaps = "https://www.google.com/maps?q=$lat,$lon";

Así, el usuario puede consultar directamente la ubicación del centro. 
