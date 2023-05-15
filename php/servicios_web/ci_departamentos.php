<?php
class ci_departamentos extends toba_ci
{	
    function ini()
    {
        // Verifica si se ha proporcionado un token de autenticación en la URL
        $token = $_GET['token'];
        if (!$token) {
            // Si no se ha proporcionado un token, muestra un error
            #header('Content-Type: application/json');
            print_r( json_encode([
                'error' => 'Falta el token de autenticación.'
            ]));
            return;
        }

        // Verifica si el token proporcionado es válido
        if (!$this->verificar_token($token)) {
            // Si el token no es válido, muestra un error
           // header('Content-Type: application/json'); Los headers ya están asignados en toba
            print_r( json_encode([
                'error' => 'Token de autenticación inválido.'
            ]));
            return;
        }

        // Obtiene el parámetro "ua" de la URL y trata de evitar la inyección de SQL
        $legajo = toba::db('designa')->quote($_GET['ua']);

        // Ejecuta una consulta para seleccionar todos los datos de la tabla "departamento"
        // utilizando el parámetro obtenido
        $query = "SELECT * FROM departamento WHERE idunidad_academica = '".$ua."'";
        $result = toba::db('designa')->consultar($query);

        // Establece el encabezado "Content-Type" a "application/json"
        #header('Content-Type: application/json');

        // Convierte el resultado de la consulta a un formato JSON y lo muestra en pantalla
        echo(json_encode($result));
        exit;
    }

}
?>