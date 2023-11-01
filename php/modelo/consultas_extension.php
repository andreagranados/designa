<?php
class consultas_extension
{
     //metodo generico para todos los servicios web que consume el modulo designa del modulo extension
    //variables definidas en designa
    function get_datos($recurso,$cond=null,$valor=null){
        $username = getenv('SW_USUARIO');   
        $password = getenv('SW_CLAVE');
        $condicion = ""; 
       
        switch ($recurso) {
            //todos los integrantes interno de pext que son directores
            case 'directores': 
                $url=getenv('SW_URL_EXT_DIR');//http://localhost/extension/1.0/rest/directores
                if(!is_null($cond)){
                    $condicion = "?".$cond."=es_igual_a;".$valor ;
                }
                break;
            case 'codirectores': 
                $url=getenv('SW_URL_EXT_CODIR');//http://localhost/extension/1.0/rest/codirectores
                if(!is_null($cond)){
                    $condicion = "?".$cond."=es_igual_a;".$valor ;
                    //http://localhost/extension/1.0/rest/codirectores?id-pext=es_igual;
                }
                break;    
                //todos los integrantes de los proyectos de extension que estan en integrante_interno_pe
            case 'integrantes': 
                $url=getenv('SW_URL_EXT_INT');//variable de ambiente en designa
                if(!is_null($cond)&&!is_null($valor)){
                    $condicion = "?".$cond."=es_igual_a".";" . trim($valor) ;//ver aqui
                }
                break;
            default:
                break;
        }
       
        $url.=$condicion; 
        
        # Inicializar una sesión cURL
        $curl = curl_init($url);
        

        # Configurar opciones de la solicitud
        curl_setopt_array($curl, [
        CURLOPT_URL => $url, # Define la URL a la que se realiza la solicitud HTTP
        CURLOPT_RETURNTRANSFER => true, # Indica que debe devolver el resultado de la solicitud como una cadena de texto en lugar de mostrarlo directamente en la salida. Es util para capturarlo en una variable
        //CURLOPT_ENCODING => "UNICODE", # Permite especificar la codificación de caracteres que se debe utilizar al recibir la respuesta del servidor
        CURLOPT_ENCODING => "ISO-8859-1", # Permite especificar la codificación de caracteres que se debe utilizar al recibir la respuesta del servidor
        CURLOPT_MAXREDIRS => 10, # Establece el numero maximo de redirecciones que seguira cURL antes de abortar la solicitud
        CURLOPT_TIMEOUT => 30,# Establece el tiempo maximo que esperará cURL para recibir la respuesta antes de abortar la solicitud, en segundos
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, # Especifica la version del protocolo HTTP que se utilizara en la solicitud
        CURLOPT_CUSTOMREQUEST => "GET", # Especifica el tipo de solicitud HTTP que se realizará al servidor
        CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,# Define el tipo de autenticacion que utilizara la solicitud, debe ser la misma que está definida en el proyecto
        CURLOPT_USERPWD => $username . ":" . $password # Establece las credenciales de usuarios que son necesarias la autenticacion
        ]);

        # Realizar la solicitud GET
        $response = curl_exec($curl);
        
        # Verificar si la solicitud fue exitosa
        if ($response === false) {
            # Manejar el error
            $error = curl_error($curl);
            echo 'Error en la solicitud: ' . $error;
        } else {
            # Decodificar la respuesta JSON y transforma los caracteres a UNICODE y quita las barras invertidas.            
            //$data = json_decode($response, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data = json_decode($response, true, JSON_UNESCAPED_SLASHES);
                      
            
            // Verifica si hubo un error
            # Verificar si la decodificación fue exitosa
            if ($data === null) {
                # Manejar el error de decodificación JSON
                $error = json_last_error_msg(). json_last_error();
                echo 'Error al decodificar la respuesta JSON: ' . $error;
            } else {
                # Acceder a los datos de la respuesta
                return $data;
            }
        }
        # Cerrar la sesión cURL
        curl_close($curl);
    }   
}
?>