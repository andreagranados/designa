<?php
require_once("modelo/modelo_uni_acad.php");

use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;


/**
 * @description Operaciones sobre Unidades Academicas
 */
class recurso_unidad implements SIUToba\rest\lib\modelable //esta interface es documentativa, puede no estar
{

	static function _get_modelos(){
		/**
		 * Hay diferencias entre una persona para mostrar o para crear. Por ej, el id.
		 * Ver el codigo fuente de rest_validador para ver las distintas reglas y opciones que llevan
		 */
		$unidad_editar = array(
					'sigla' => array(	'type'     => 'string', 
										'_validar' => array(rest_validador::OBLIGATORIO,rest_validador::TIPO_ALPHA )),

                                        'descripcion' => array(	'type' => 'string'),
                                        'cod_regional' => array('type' => 'string'),
                                        'tipo' => array('type' => 'string')
				);

		$unidad = array_merge(
							array('sigla' => array('type' => 'string',
												'_validar' => array(rest_validador::TIPO_ALPHA))),
							$unidad_editar);
		return $models = array(
			'Unidad' => $unidad,
			'UnidadEditar' => $unidad_editar

		);
	}

	protected function get_spec_unidad($con_imagen = true, $tipo= 'Unidad'){
		/** Notar que hay que modificar la spec si se va a incluir la foto o no, ya que de otro modo
		 * lanzar�a un error cuando falta el campo. */
		$m = $this->_get_modelos();
		if(!$con_imagen){
			unset ($m[$tipo]['imagen']);
		}
		return $m[$tipo];
	}

    /**
	 * Se consume en GET /uni-acad/{id}
     * @summary Retorna datos de una persona. 
 	 * @param_query $con_imagen integer Retornar adem�s la imagen de la persona, por defecto 0
     * @responses 200 {"$ref": "Persona"} Persona
     * @responses 400 No existe la persona
     */
        // rest/uni-acad/CUZA
    function get($sigla)
    {
	    /**Obtengo los datos del modelo*/
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
	$modelo = new modelo_uni_acad($sigla);
        $fila = $modelo->get_datos($incluir_imagen);

        if ($fila) {
                /**Transformci�n al formato de la vista de la API -
                 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
                 * La fila contiene exactamente los campos de la especificaci�n */
                $fila = rest_hidratador::hidratar_fila($this->get_spec_unidad($incluir_imagen), $fila);
        }

	    /**Se escribe la respuesta*/
        rest::response()->get($fila);
    }
 // rest/uni-acad/CUZA/descripcion
    function get_descripcion_list ($sigla){
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
        $modelo = new modelo_uni_acad($sigla);
      
        $fila = $modelo->get_datos_descripcion($incluir_imagen);

		if ($fila) {
			/**Transformci�n al formato de la vista de la API -
			 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
			 * La fila contiene exactamente los campos de la especificaci�n */
			$fila = rest_hidratador::hidratar_fila($this->get_spec_modelo($incluir_imagen), $fila);
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
		$unidades = modelo_uni_acad::get_unidades($where, $order_by, $limit);
                
		/**Transformci�n al formato de la vista de la API
		 * Como buen ciudadano, se agrega un header para facilitar el paginado al cliente*/
		$unidades = rest_hidratador::hidratar($this->get_spec_unidad(false), $unidades);
		$cantidad = modelo_uni_acad::get_cant_unidades($where);
		rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));

		/**Se escribe la respuesta */
		rest::response()->get_list($unidades);

	}

	/**
	 * Esto es un alias. Si bien se aleja del REST puro, se puede utilizar para destacar
	 * una operaci�n o proveer un acceso simplificado a operaciones frecuentes.
	 * Se consume en GET /uni-acad/confoto.
	 * @summary Retorna aquellas personas que tienen la foto cargada
	 * @responses 200 array {"$ref": "Persona"} Persona
	 */
	function get_list__confoto()
	{
		$filtro = $this->get_filtro_get_list();
		$limit = $filtro->get_sql_limit();
		$order_by = $filtro->get_sql_order_by();
		$where = $filtro->get_sql_where() . " AND imagen <> ''";
		$unidades = modelo_uni_acad::get_unidades($where, $order_by, $limit);
		$cantidad = modelo_uni_acad::get_cant_unidades($where);

		$unidades = rest_hidratador::hidratar($this->get_spec_unidad(true), $unidades);

		rest::response()->get($unidades);
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
		$nuevo = modelo_uni_acad::insert($datos_modelo);

		/** Se retorna el id recientemente creado, de acuerdo a las convenciones de la API*/
		$fila = array('id' => $nuevo);
		rest::response()->post($fila);
	}


    /**
	 * Se consume en PUT /uni-acad/{id}
     * @summary Modificar datos de la persona.
     * @param_body $persona PersonaEditar  [required] los datos a editar de la persona
     * @notes Si envia la componente 'imagen' de la persona se actualiza unicamente la imagen (binario base64). La fecha es en formato 'Y-m-d'
     * @responses 404 No se pudo encontrar a la persona
     * @responses 400 El pedido no cumple con las reglas de negocio - validacion erronea.
     */
	function put($sigla)
	{
		/** Valido y traduzco los datos al formato de mi modelo*/
		$datos_modelo = $this->procesar_input_edicion(true);

		$modelo = new modelo_uni_acad($sigla);
		//$errores = $modelo->validar($datos);

		if (isset($datos_modelo['imagen'])) { //por separado ya que es un caso especial
			$ok = $modelo->update_imagen($datos_modelo);
		} else {
			$ok = $modelo->update($datos_modelo);
		}
		if (!$ok) {
			rest::response()->not_found();
		} else {
			rest::response()->put();
		}
	}


    /**
	 * Se consume en DELETE /uni-acad/{id}
     * @summary Eliminar la persona.
     * @notes Cuidado, borra datos de deportes y juegos tambien
     * @responses 404 No se pudo encontrar a la persona
     */
    function delete($sigla)
    {
        $modelo = new modelo_uni_acad($sigla);
        $ok = $modelo->delete();
        if(!$ok){
            rest::response()->not_found();
        }else {
            rest::response()->delete();
        }
    }




    /**
     * Se consume en GET /uni-acad/{id}/juegos
	 * @summary Retorna todos los juego que practica la persona
     * @response_type [ {juego: integer, dia_semana: integer, hora_inicio: string, hora_fin:string}, ]
     * @responses 404 No se pudo encontrar a la persona
     */
    function get_juegos_list($sigla)
    {
	    //se omite hidratador por simplicidad.
		$juegos = modelo_uni_acad::get_juegos($sigla);
		rest::response()->get_list($juegos);
    }

	/**
	 * @return rest_filtro_sql
	 */
	protected function get_filtro_get_list()
	{
		$filtro = new rest_filtro_sql();
		$filtro->agregar_campo("sigla", "unidad_acad.sigla");
		//$filtro->agregar_campo("id_departamento", "area.iddepto");

		//$filtro->agregar_campo_ordenable("descripcion", "area.descripcion");
		//$filtro->agregar_campo_ordenable("idarea", "area.idarea");
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
		$spec_unidad = $this->get_spec_unidad(true, 'UnidadEditar');
		rest_validador::validar($datos, $spec_unidad, $relajar_ocultos);

		/**Transformo el input del usuario a formato del modelo, deshaciendo la hidratacion.
		 * Por ejemplo, cambia el nombre de fecha_nacimiento (vista) a fecha_nac (modelo)
		 * Se pueden requerir otros pasos, en casos mas complejos */
		$datos = rest_hidratador::deshidratar_fila($datos, $spec_unidad);
		return $datos;
	}
       
}
