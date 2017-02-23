<?php
class ci_certificacion_periodo extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__agente;
        //protected $s__where;
        //-----------------------------------------------------------------------------------
	//---- filtro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $form)
	{
            if (isset($this->s__datos_filtro)) {
                $form->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtro__filtrar($datos)
	{
            $this->s__datos_filtro = $datos;
            //$this->s__where = $this->dep('filtro')->get_sql_where();
	}

	function evt__filtro__cancelar()
	{
            unset($this->s__datos_filtro);
            //unset($this->s__where);
	}
//        //-----------------------------------------------------------------------------------
//	//---- filtros ----------------------------------------------------------------------
//	//-----------------------------------------------------------------------------------
//
//	function conf__filtros(toba_ei_filtro $filtro)
//	{
//		if (isset($this->s__datos_filtro)) {
//			$filtro->set_datos($this->s__datos_filtro);
//		}
//	}
//
//	function evt__filtros__filtrar($datos)
//	{
//		$this->s__datos_filtro = $datos;
//                $this->s__where = $this->dep('filtros')->get_sql_where();
//	}
//
//	function evt__filtros__cancelar()
//	{
//            unset($this->s__datos_filtro);
//            unset($this->s__where);
//	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            $this->pantalla()->tab("pant_certif")->desactivar();
            if (isset($this->s__datos_filtro)) {
		$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_docente($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->s__agente=$datos;//[id_docente] => 49
            $this->set_pantalla('pant_certif');
	}
        
        function conf__pant_certif(toba_ei_pantalla $pantalla)
	{
            $this->pantalla()->tab("pant_inicial")->desactivar();
	}
        function vista_pdf(toba_vista_pdf $salida)
        {
            $salida->set_papel_orientacion('landscape');
            $salida->inicializar();
            $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
            $pdf->ezSetMargins(80, 50, 5, 5);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
            $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
            $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
            $pdf->addText(480,20,8,"Sistema MOCOVI-Modulo Designaciones Docentes".date('d/m/Y h:i:s a')); 
            $pdf->addText(80,170,10,"Se extiende el presente certificado el ".date("d/m/Y")." a las ".date("h").":".date("i")." ".date("A").", a pedido del interesado, y a los efectos de ser presentado ante quien corresponda."."\n"); 
            $pdf->addText(750,90,10,"------------------"); 
            $pdf->addText(750,80,10,"Firma y Sello"); 
                //Configuración de Título.
            $salida->titulo(utf8_d_seguro("Planilla de Designación del Docente"));
 
           $opciones = array(
                'showLines'=>1,
                'splitRows'=>0,
                'rowGap' => 1,
                'showHeadings' => true,
                'titleFontSize' => 9,
                'fontSize' => 10,
                'shadeCol' => array(0.9,0.9,0.9),
                'outerLineThickness' => 0,
                'innerLineThickness' => 0,
                'xOrientation' => 'center',
                'width' => 500
            );
           
            $ag=$this->dep('datos')->tabla('docente')->get_agente($this->s__agente['id_docente']);
            $leg=$this->dep('datos')->tabla('docente')->get_legajo($this->s__agente['id_docente']);
            //recupero las designaciones del periodo previamente seleccionado
            $desig=$this->dep('datos')->tabla('docente')->get_designaciones($this->s__agente['id_docente']);
            
            $texto="Docente: <b>".$ag."</b> Legajo ".$leg;
            $pdf->ezText($texto,12);
            $pdf->ezText("\n", 7);
            
            foreach ($desig as $des) {//para cada designacion
                $texto= "Categoria y Dedicacion: <b>".trim($des['cat_estat'])."-".$des['dedic']."</b> Desde: <b>".$des['desde']. "</b> Hasta: <b>".$des['hasta']."</b>";
                $pdf->ezText($texto,12);
                
               
                $texto= "Situacion: <b>".$des['caracter']."</b>";
                $pdf->ezText($texto,12);
                $texto= "Departamento: <b>".$des['depto']."</b>";
                $pdf->ezText($texto,12);
                $texto= "Area: <b>".$des['area']."</b>";
                $pdf->ezText($texto,12);
                $texto= "Orientacion: <b>".$des['orient']."</b>";
                $pdf->ezText($texto,12);
                $desig=$this->dep('datos')->tabla('docente')->get_materias($this->s__agente['id_docente'],$this->s__datos_filtro['anio']);
                $this->s__datos_filtro;
            }
            
            $datos = array(
                array('col1' => 1, 'col2' => 2,'col3' => 2,'col4' => 2,'col5' => 2),
                array('col1' => 3, 'col2' => 4,'col3' => 2,'col4' => 2,'col5' => 2),
              );
            
            $pdf->ezTable($datos, array('col1'=>'Asignatura', 'col2' => 'Carrera','col3' => 'Año','col4' => 'Hs','col5' => 'Módulo'), 'ACTIVIDAD ACADEMICA', $opciones);
            
            $pdf->ezText("\n\n\n", 10);
            
        }
	

	

}
?>