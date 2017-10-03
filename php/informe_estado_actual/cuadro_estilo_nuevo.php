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
        function generar_layout_fila($columnas, $datos, $id_fila,  $clave_fila, $evt_multiples, $objeto_js, $estilo_fila, $formateo)
	{
		$estilo_seleccion = $this->get_estilo_seleccion($clave_fila);

		  //Javascript de seleccion multiple
		$js = $this->get_invocacion_js_eventos_multiples($evt_multiples, $id_fila, $objeto_js);

		 //---> Creo las CELDAS de una FILA <----
		echo "<tr class='$estilo_fila' >\n";

		//---> Creo los EVENTOS de la FILA  previos a las columnas<---
		$this->html_cuadro_celda_evento($id_fila, $clave_fila, true);
		foreach (array_keys($columnas) as $a) {
			//*** 1) Recupero el VALOR
			$valor = "";
			if(isset($columnas[$a]["clave"])) {
				if(isset($datos[$id_fila][$columnas[$a]["clave"]])) {
					$valor_real = $datos[$id_fila][$columnas[$a]["clave"]];
					//-- Hace el saneamiento para evitar inyecci�n XSS
					if (!isset($columnas[$a]['permitir_html']) || $columnas[$a]['permitir_html'] == 0) {
						  $valor_real = texto_plano($valor_real);
					}
				}else{
					$valor_real = null;
					//ATENCION!! hay una columna que no esta disponible!
				}
				//Hay que formatear?
				if(isset($columnas[$a]["formateo"])) {
					$funcion = "formato_" . $columnas[$a]["formateo"];
					//Formateo el valor
					$valor = $formateo->$funcion($valor_real);
				} else {
					$valor = $valor_real;
				}
			}

			//*** 2) La celda posee un vinculo??
			if ($columnas[$a]['usar_vinculo'] )  {
					$valor = $this->get_html_cuadro_celda_vinculo($columnas, $a, $id_fila, $clave_fila, $valor);
			}

			//*** 3) Genero el HTML
			$ancho = "";
			if(isset($columnas[$a]["ancho"])) {
				$ancho = " width='". $columnas[$a]["ancho"] . "'";
			}

		  //Emito el valor de la celda
			echo "<td class='$estilo_seleccion ".$columnas[$a]["estilo"]."' $ancho $js"."style='background-color:'YELLOY'".">\n";
			if (trim($valor) !== '') {
				echo $valor;
			} else {
				echo '&nbsp;';
			}
			echo "</td>\n";
			//Termino la CELDA
		}
		//---> Creo los EVENTOS de la FILA <---
		$this->html_cuadro_celda_evento($id_fila, $clave_fila, false);
		echo "</tr>\n";
	}

       
}

?>