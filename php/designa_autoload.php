<?php
/**
 * Esta clase fue y será generada automáticamente. NO EDITAR A MANO.
 * @ignore
 */
class designa_autoload 
{
	static function existe_clase($nombre)
	{
		return isset(self::$clases[$nombre]);
	}

	static function cargar($nombre)
	{
		if (self::existe_clase($nombre)) { 
			 require_once(dirname(__FILE__) .'/'. self::$clases[$nombre]); 
		}
	}

	static protected $clases = array(
		'designa_ci' => 'extension_toba/componentes/designa_ci.php',
		'designa_cn' => 'extension_toba/componentes/designa_cn.php',
		'designa_datos_relacion' => 'extension_toba/componentes/designa_datos_relacion.php',
		'designa_datos_tabla' => 'extension_toba/componentes/designa_datos_tabla.php',
		'designa_ei_arbol' => 'extension_toba/componentes/designa_ei_arbol.php',
		'designa_ei_archivos' => 'extension_toba/componentes/designa_ei_archivos.php',
		'designa_ei_calendario' => 'extension_toba/componentes/designa_ei_calendario.php',
		'designa_ei_codigo' => 'extension_toba/componentes/designa_ei_codigo.php',
		'designa_ei_cuadro' => 'extension_toba/componentes/designa_ei_cuadro.php',
		'designa_ei_esquema' => 'extension_toba/componentes/designa_ei_esquema.php',
		'designa_ei_filtro' => 'extension_toba/componentes/designa_ei_filtro.php',
		'designa_ei_firma' => 'extension_toba/componentes/designa_ei_firma.php',
		'designa_ei_formulario' => 'extension_toba/componentes/designa_ei_formulario.php',
		'designa_ei_formulario_ml' => 'extension_toba/componentes/designa_ei_formulario_ml.php',
		'designa_ei_grafico' => 'extension_toba/componentes/designa_ei_grafico.php',
		'designa_ei_mapa' => 'extension_toba/componentes/designa_ei_mapa.php',
		'designa_servicio_web' => 'extension_toba/componentes/designa_servicio_web.php',
		'designa_comando' => 'extension_toba/designa_comando.php',
		'designa_modelo' => 'extension_toba/designa_modelo.php',
	);
}
?>