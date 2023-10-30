<?php
require_once("modelo/modelo_localidad.php");

use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;


/**
 * @description Operaciones sobre Provincias
 */
class recurso_localidades implements SIUToba\rest\lib\modelable //esta interface es documentativa, puede no estar
{

	static function _get_modelos(){
		/**
		 * Hay diferencias entre una persona para mostrar o para crear. Por ej, el id.
		 * Ver el codigo fuente de rest_validador para ver las distintas reglas y opciones que llevan
		 */
		$localidad_editar = array(
					'id' => array(	'type'     => 'integer', 
										'_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_INT  )),
                                        'id_provincia' => array(	'type'     => 'integer', 
										'_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_INT  )),
                                        'localidad' => array(	'type' => 'string')
				);

		$localidad = array_merge(
							array('id' => array('type' => 'integer',
												'_validar' => array(rest_validador::TIPO_INT ))),
							$localidad_editar);
		return $models = array(
			'Localidad' => $localidad,
			'LocalidadEditar' => $localidad_editar

		);
	}

	protected function get_spec_localidad($con_imagen = true, $tipo= 'Localidad'){
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
	$modelo = new modelo_localidad($cod);
        $fila = $modelo->get_datos($incluir_imagen);

        if ($fila) {
                /**Transformci�n al formato de la vista de la API -
                 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
                 * La fila contiene exactamente los campos de la especificaci�n */
                $fila = rest_hidratador::hidratar_fila($this->get_spec_localidad($incluir_imagen), $fila);
        }

	    /**Se escribe la respuesta*/
        rest::response()->get($fila);
    }
 
    function get_descripcion_list ($cod){
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
        $modelo = new modelo_localidad($cod);
      
        $fila = $modelo->get_datos_descripcion($incluir_imagen);

		if ($fila) {
			/**Transformci�n al formato de la vista de la API -
			 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
			 * La fila contiene exactamente los campos de la especificaci�n */
			$fila = rest_hidratador::hidratar_fila($this->get_spec_localidad($incluir_imagen), $fila);
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
                
		$localidad = modelo_localidad::get_localidades($where, $order_by, $limit);
		/**Transformci�n al formato de la vista de la API
		 * Como buen ciudadano, se agrega un header para facilitar el paginado al cliente*/
		$localidad = rest_hidratador::hidratar($this->get_spec_localidad(false), $localidad);
		$cantidad = modelo_localidad::get_cant_localidades($where);
		rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));

		/**Se escribe la respuesta */
		rest::response()->get_list($localidad);

	}

      
	/**
	 * @return rest_filtro_sql
	 */
	protected function get_filtro_get_list()
	{
		$filtro = new rest_filtro_sql();
		$filtro->agregar_campo("nombre", "localidad.localidad");
                $filtro->agregar_campo("idprovincia","localidad.id_provincia");

		$filtro->agregar_campo_ordenable("nombre", "localidad.localidad");
		return $filtro;
	}


       
}
