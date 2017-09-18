<?php
class cuadro_estilo_nuevo extends designa_ei_cuadro
{
    function html_cuadro(&$filas)
	{
		//Si existen cortes de control y el layout es tabular, el encabezado de la tabla ya se genero
		if( ! $this->_cuadro->tabla_datos_es_general() ){
			$this->html_cuadro_inicio();
		}
		//-- Se puede por api cambiar a que los titulos de las columnas se muestren antes que los cortes, en ese caso se evita hacerlo aqui
		if (! $this->_cuadro->debe_mostrar_titulos_columnas_cc()) {
			$this->html_cuadro_cabecera_columnas();
		}
		$par = false;
		$formateo = $this->_cuadro->get_instancia_clase_formateo('html');
		$layout_cant_columnas = $this->_cuadro->get_layout_cant_columnas();
		$i = 0;
		if (!is_null($layout_cant_columnas)) {
			echo "<tr>";
		}

		$columnas = $this->_cuadro->get_columnas();
		$datos = $this->_cuadro->get_datos();
		$objeto_js = $this->_cuadro->get_id_objeto_js();
		$evt_multiples = $this->_cuadro->get_eventos_multiples();

		foreach($filas as $f)
		{
			if (!is_null($layout_cant_columnas) && ($i % $layout_cant_columnas == 0)) {
				$ancho = floor(100 / (count($filas) / $layout_cant_columnas));
				echo "<td><table class='ei-cuadro-agrupador-filas' width='$ancho%' >";
			}
			$estilo_fila = $par ? 'ei-cuadro-celda-par' : 'ei-cuadro-celda-impar';
			$clave_fila = $this->_cuadro->get_clave_fila($f);

			//Genero el html de la fila, junto con sus eventos y vinculos
			$this->generar_layout_fila($columnas, $datos, $f, $clave_fila, $evt_multiples, $objeto_js, $estilo_fila, $formateo);
			$par = !$par;
			if (isset($layout_cant_columnas) && $i % $layout_cant_columnas == $layout_cant_columnas-1) {
				echo "</table></td>";
			}
			$i++;
		}
		
		if (isset($layout_cant_columnas)) {
			echo "</tr>";
		}
		if( ! $this->_cuadro->tabla_datos_es_general() ){
			$this->html_acumulador_usuario();
			$this->html_cuadro_fin();
		}
	}


}

?>