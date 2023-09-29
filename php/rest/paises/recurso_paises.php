<?php
require_once("modelo/modelo_pais.php");

use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;


/**
 * @description Operaciones sobre Paises
 */
class recurso_paises implements SIUToba\rest\lib\modelable //esta interface es documentativa, puede no estar
{

	static function _get_modelos(){
		/**
		 * Hay diferencias entre una persona para mostrar o para crear. Por ej, el id.
		 * Ver el codigo fuente de rest_validador para ver las distintas reglas y opciones que llevan
		 */
		$pais_editar = array(
					'codigo_pais' => array(	'type'     => 'string', 
										'_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_ALPHANUM  )),
                                        'nombre' => array(	'type' => 'string')
				);

		$pais = array_merge(
							array('codigo_pais' => array('type' => 'string',
												'_validar' => array(rest_validador::TIPO_ALPHANUM ))),
							$pais_editar);
		return $models = array(
			'Pais' => $pais,
			'PaisEditar' => $pais_editar

		);
	}

	protected function get_spec_pais($con_imagen = true, $tipo= 'Pais'){
		/** Notar que hay que modificar la spec si se va a incluir la foto o no, ya que de otro modo
		 * lanzar�a un error cuando falta el campo. */
		$m = $this->_get_modelos();
		if(!$con_imagen){
			unset ($m[$tipo]['imagen']);
		}
		return $m[$tipo];
	}

    /**
	 * Se consume en GET /personas/{id}
     * @summary Retorna datos de una persona. 
 	 * @param_query $con_imagen integer Retornar adem�s la imagen de la persona, por defecto 0
     * @responses 200 {"$ref": "Persona"} Persona
     * @responses 400 No existe la persona
     */
    function get($cod)
    {
	    /**Obtengo los datos del modelo*/
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
	$modelo = new modelo_pais($cod);
        $fila = $modelo->get_datos($incluir_imagen);

        if ($fila) {
                /**Transformci�n al formato de la vista de la API -
                 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
                 * La fila contiene exactamente los campos de la especificaci�n */
                $fila = rest_hidratador::hidratar_fila($this->get_spec_pais($incluir_imagen), $fila);
        }

	    /**Se escribe la respuesta*/
        rest::response()->get($fila);
    }
 
    function get_descripcion_list ($cod){
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
        $modelo = new modelo_pais($cod);
      
        $fila = $modelo->get_datos_descripcion($incluir_imagen);

		if ($fila) {
			/**Transformci�n al formato de la vista de la API -
			 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
			 * La fila contiene exactamente los campos de la especificaci�n */
			$fila = rest_hidratador::hidratar_fila($this->get_spec_pais($incluir_imagen), $fila);
		}

	    /**Se escribe la respuesta*/
        rest::response()->get($fila);
    }

	/**
	 * Se consume en GET /personas
	 *
	 * @param_query $nombre string Se define como 'condicion;valor' donde 'condicion' puede ser contiene|no_contiene|comienza_con|termina_con|es_igual_a|es_distinto_de
	 * @param_query $fecha_nac string Se define como 'condicion;valor' donde 'condicion' puede ser es_menor_que|es_menor_igual_que|es_igual_a|es_distinto_de|es_mayor_igual_que|es_mayor_que|entre
	 *
	 * @param_query $limit integer Limitar a esta cantidad de registros
	 * @param_query $page integer Limitar desde esta pagina
	 * @param_query $order string +/-campo,...
	 * @notes Retorna un header 'Total-Registros' con la cantidad total de registros a paginar
	 * @responses 200 array {"$ref":"Persona"}
	 */
	function get_list()
	{
		/** Se recopilan parametros del usuario con ayuda de un helper - rest_filtro que genera sql*/
		$filtro = $this->get_filtro_get_list();
		$where = $filtro->get_sql_where();
		$limit = $filtro->get_sql_limit();
		$order_by = $filtro->get_sql_order_by();
		/** Se recuperan datos desde el modelo */
                
		$paises = modelo_pais::get_paises($where, $order_by, $limit);
                
		/**Transformci�n al formato de la vista de la API
		 * Como buen ciudadano, se agrega un header para facilitar el paginado al cliente*/
		$paises = rest_hidratador::hidratar($this->get_spec_pais(false), $paises);
		$cantidad = modelo_pais::get_cant_paises($where);
		rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));

		/**Se escribe la respuesta */
		rest::response()->get_list($paises);

	}

	/**
	 * Esto es un alias. Si bien se aleja del REST puro, se puede utilizar para destacar
	 * una operaci�n o proveer un acceso simplificado a operaciones frecuentes.
	 * Se consume en GET /personas/confoto.
	 * @summary Retorna aquellas personas que tienen la foto cargada
	 * @responses 200 array {"$ref": "Persona"} Persona
	 */
	function get_list__confoto()
	{
		$filtro = $this->get_filtro_get_list();
		$limit = $filtro->get_sql_limit();
		$order_by = $filtro->get_sql_order_by();
		$where = $filtro->get_sql_where() . " AND imagen <> ''";
		$paises = modelo_pais::get_paises($where, $order_by, $limit);
		$cantidad = modelo_pais::get_cant_paises($where);

		$paises = rest_hidratador::hidratar($this->get_spec_pais(true), $paises);

		rest::response()->get($paises);
		rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));
	}

	/**
	 * Se consume en POST /personas
	 * @summary Crear una persona
	 * @notes La fecha es en formato 'Y-m-d'</br>
	 * @param_body $persona  PersonaEditar [required] los datos iniciales de la persona
	 * @responses 201 {"id" : "integer"} identificador de la persona agregada
	 * @responses 500 Error en los datos de ingresados para la persona
	 */
	function post_list()
	{
		/** Valido y traduzco los datos al formato de mi modelo*/
		$datos_modelo = $this->procesar_input_edicion();

		/**La validacion del input no reemplaza a las validaciones del modelo (reglas de negocio) */
		//$errores = modelo_persona::validar($datos_modelo);

		/**Aplicaci�n de cambios al modelo*/
		$nuevo = modelo_pais::insert($datos_modelo);

		/** Se retorna el id recientemente creado, de acuerdo a las convenciones de la API*/
		$fila = array('id' => $nuevo);
		rest::response()->post($fila);
	}


        /**
         * Se consume en GET /paises/{id}/provincias
         * @summary Retorna todos los juego que practica la persona
         * @response_type [ {juego: integer, dia_semana: integer, hora_inicio: string, hora_fin:string}, ]
         * @responses 404 No se pudo encontrar a la persona
         */
        function get_provincias_list($cod)
        {
                //se omite hidratador por simplicidad.
                    $prov = modelo_pais::get_provincias($cod);
                    rest::response()->get_list($prov);
        }

	/**
	 * @return rest_filtro_sql
	 */
	protected function get_filtro_get_list()
	{
		$filtro = new rest_filtro_sql();
		$filtro->agregar_campo("codigo", "pais.codigo_pais");
                $filtro->agregar_campo("nombre", "pais.nombre");

		$filtro->agregar_campo_ordenable("nombre", "pais.nombre");
		return $filtro;
	}


	/**
	 * $relajar_ocultos boolean no checkea campos obligatorios cuando no se especifican
	 */
	protected function procesar_input_edicion($relajar_ocultos = false)
	{
		/**Validacion del input del usuario, de acuerdo a la especificacion de la API
		 * La PersonaEditar tiene solo los campos editables, ej: el id no se puede setear
		 */
		$datos = rest::request()->get_body_json();
		$spec_pais = $this->get_spec_pais(true, 'PaisEditar');
		rest_validador::validar($datos, $spec_pais, $relajar_ocultos);

		/**Transformo el input del usuario a formato del modelo, deshaciendo la hidratacion.
		 * Por ejemplo, cambia el nombre de fecha_nacimiento (vista) a fecha_nac (modelo)
		 * Se pueden requerir otros pasos, en casos mas complejos */
		$datos = rest_hidratador::deshidratar_fila($datos, $spec_pais);
		return $datos;
	}
       
}
