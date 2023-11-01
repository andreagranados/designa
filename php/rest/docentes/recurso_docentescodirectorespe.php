<?php
require_once("modelo/modelo_docente.php");

use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;


/**
 * @description Operaciones sobre Departamentos
 */
class recurso_docentescodirectorespe implements SIUToba\rest\lib\modelable //esta interface es documentativa, puede no estar
{

	static function _get_modelos(){
		/**
		 * Hay diferencias entre una persona para mostrar o para crear. Por ej, el id.
		 * Ver el codigo fuente de rest_validador para ver las distintas reglas y opciones que llevan
		 */
		$docente_editar = array(
                                        'id_designacion' => array('type' => 'integer',
                                                                '_validar' => array(
                                                                rest_validador::OBLIGATORIO,
                                                                rest_validador::TIPO_INT)
                                        
                                        ),
					'id_docente' => array(	'type'     => 'integer', 
										'_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_INT )),
                                        
                                        'apellido' => array(	'type'     => 'string'),
                                        'nombre' => array(	'type'     => 'string'),
                                        'legajo' => array(	'type'     => 'integer'),
                                        'tipo_docum' => array(	'type'     => 'string'),
                                        'nro_docum' => array(	'type'     => 'integer'),
                                        'uni_acad' => array('type' => 'string'),
                                        'correo_institucional' => array('type' => 'string'),
                                        'telefono_celular' => array('type' => 'string')
                                        
				);

		$docente = array_merge(
							array('id_designacion' => array('type' => 'integer',
												'_validar' => array(rest_validador::TIPO_INT))),
							$docente_editar);
		return $models = array(
			'Docente' => $docente,
			'DocenteEditar' => $docente_editar

		);
	}

	protected function get_spec_docente($con_imagen = true, $tipo= 'Docente'){
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

    //http://localhost/designa/1.0/rest/docentes/docentescodirectorespe/3
    function get($id_docente)
    {
		//toba::logger()->debug("Usuario: " . rest::app()->usuario->get_usuario());
	    /**Obtengo los datos del modelo*/
        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
	$modelo = new modelo_docente($id_docente);
        $fila = $modelo->get_datos($incluir_imagen);

        if ($fila) {
                /**Transformci�n al formato de la vista de la API -
                 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
                 * La fila contiene exactamente los campos de la especificaci�n */
                $fila = rest_hidratador::hidratar_fila($this->get_spec_docente($incluir_imagen), $fila);
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
                //var_dump($where);exit;//recupero todo lo que viene como valor
		$order_by = $filtro->get_sql_order_by();
		/** Se recuperan datos desde el modelo */
		$docentes = modelo_docente::get_docentes_codirectorespe($where, $order_by, $limit);

		/**Transformci�n al formato de la vista de la API
		 * Como buen ciudadano, se agrega un header para facilitar el paginado al cliente*/
		$docentes = rest_hidratador::hidratar($this->get_spec_docente(false), $docentes);
                $cantidad = modelo_docente::get_cant_docentes_codirectorespe($where);
		rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));
		/**Se escribe la respuesta */
		rest::response()->get_list($docentes);
	}

      
	/**
	 * @return rest_filtro_sql
	 */
	protected function get_filtro_get_list()
	{
		$filtro = new rest_filtro_sql();
                $filtro->agregar_campo("id-pext", "id_pext");
		
                $filtro->agregar_campo_ordenable("apellido", "docente.apellido");
		$filtro->agregar_campo_ordenable("nombre", "docente.nombre");
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
		$spec_docente = $this->get_spec_docente(true, 'DocenteEditar');
		rest_validador::validar($datos, $spec_docente, $relajar_ocultos);

		/**Transformo el input del usuario a formato del modelo, deshaciendo la hidratacion.
		 * Por ejemplo, cambia el nombre de fecha_nacimiento (vista) a fecha_nac (modelo)
		 * Se pueden requerir otros pasos, en casos mas complejos */
		$datos = rest_hidratador::deshidratar_fila($datos, $spec_docente);
		return $datos;
	}
       
}
