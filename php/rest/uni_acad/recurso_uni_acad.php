<?php
require_once("modelo/modelo_uni_acad.php");

use SIUToba\rest\lib\rest_hidratador;
use SIUToba\rest\lib\rest_validador;
use SIUToba\rest\rest;
use SIUToba\rest\lib\rest_filtro_sql;
/**
 * @description Operaciones sobre Unidades Academicas
 */
class recurso_uni_acad implements SIUToba\rest\lib\modelable //esta interface es documentativa, puede no estar
{
    static function _get_modelos(){
		/**
		 * Hay diferencias entre una persona para mostrar o para crear. Por ej, el id.
		 * Ver el codigo fuente de rest_validador para ver las distintas reglas y opciones que llevan
		 */
		$uni_acad_editar = array(
					'sigla' => array('type'   => 'string','_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_LONGITUD => array('min' => 1, 'max' => 4))),
					'descripcion' => array(	'type'     => 'string', 
										'_validar' => array(rest_validador::OBLIGATORIO,
															rest_validador::TIPO_LONGITUD => array('min' => 1, 'max' => 30)))
					
				);
		
		$ua = array_merge(
							array('id' => array('type' => 'string',
												'_validar' => array(rest_validador::TIPO_LONGITUD => array('min' => 1, 'max' => 4)))),
							$uni_acad_editar);
		return $models = array(
			'UA' => $ua,
			'UAEditar' => $uni_acad_editar

		);
	}
        protected function get_spec_uniacad($con_imagen = true, $tipo= 'Persona'){
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
//    function get($id_persona)
//    {
//		//toba::logger()->debug("Usuario: " . rest::app()->usuario->get_usuario());
//	    /**Obtengo los datos del modelo*/
//        $incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
//		$modelo = new modelo_persona($id_persona);
//        $fila = $modelo->get_datos($incluir_imagen);
//
//		if ($fila) {
//			/**Transformci�n al formato de la vista de la API -
//			 * Si faltan campos se generar�n 'undefined_index'. Si sobran, no se incluyen.
//			 * La fila contiene exactamente los campos de la especificaci�n */
//			$fila = rest_hidratador::hidratar_fila($this->get_spec_persona($incluir_imagen), $fila);
//		}
//
//	    /**Se escribe la respuesta*/
//        rest::response()->get($fila);
//    }
    function get($sigla)
    {
         /**Obtengo los datos del modelo*/
        //$incluir_imagen = (bool) rest::request()->get('con_imagen', 0);
        $modelo = new modelo_uni_acad($sigla);
        $fila = $modelo->get_datos();
        /**Se escribe la respuesta*/
        rest::response()->get($fila);        
    }

    function delete($sigla)
    {
        $modelo = new modelo_uni_acad($sigla);
        $ok = $modelo->delete();
        $errores = array();
        if (!$ok) {
            rest::response()->not_found();
        } else {
            rest::response()->delete($errores);
        }
    }

    function put($sigla)
    {
        $datos = rest::request()->get_body_json();
        $modelo = new modelo_uni_acad($sigla);
        $ok = $modelo->update($datos);
        if (!$ok) {
            rest::response()->not_found();
        } else {
            rest::response()->put();
        }
    }
    // Equivale a GET /rest: retorna el recurso como un conjunto
    function post_list()
    {
        $datos = rest::request()->get_body_json();
        $nuevo = modelo_uni_acad::insert($datos);
        $fila = array('id' => $nuevo);
        rest::response()->post($fila);
    }

    function get_list()
    {
        /** Se recopilan parametros del usuario con ayuda de un helper - rest_filtro que genera sql*/
        $filtro = $this->get_filtro_get_list();
        $where = $filtro->get_sql_where();
        $limit = $filtro->get_sql_limit();
        $order_by = $filtro->get_sql_order_by();
        
        /** Se recuperan datos desde el modelo */
        $uas = modelo_uni_acad::get_uni_academicas($where,$order_by, $limit);
        $cantidad = modelo_uni_acad::get_cant_ua($where);
	rest::response()->add_headers(array('Cantidad-Registros' => $cantidad));

	/**Se escribe la respuesta */
	rest::response()->get_list($uas);
        //rest::response()->get($ua);
    }
}
?>