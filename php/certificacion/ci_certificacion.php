<?php
class ci_certificacion extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__agente;
        protected $s__where;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
                $this->s__where = $this->dep('filtros')->get_sql_where();
	}

	function evt__filtros__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__where);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_listado($this->s__where));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->s__agente=$datos;//[id_docente] => 49
            $this->set_pantalla('pant_certif');
	}

	
	//-----------------------------------------------------------------------------------
	//---- Configuraciones --------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__pant_certif(toba_ei_pantalla $pantalla)
	{
            $this->pantalla()->tab("pant_edicion")->desactivar();
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
            $pdf->addText(80,60,8,"Se extiende el presente certificado el ".date("d/m/Y")." a las ".date("h").":".date("i")." ".date("A").", a pedido del interesado, y a los efectos de ser presentado ante quien corresponda."."\n"); 
                //Configuración de Título.
            $salida->titulo(utf8_d_seguro("Certificado de Actividades Académicas"));
                
               
            $titulo=" ";
            //-- Cuadro con datos
            $opciones = array(
                    'splitRows'=>0,
                    'shaded'=>1,
                    'showLines'=>1,
                    'rowGap' => 1,
                    'showHeadings' => true,
                    'titleFontSize' => 9,
                    'fontSize' => 6,
                    'shadeCol' => array(0.9,3,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9),
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 800
                    );
            
            $ag=$this->dep('datos')->tabla('docente')->get_agente($this->s__agente['id_docente']);
            $leg=$this->dep('datos')->tabla('docente')->get_legajo($this->s__agente['id_docente']);
            $desig=$this->dep('datos')->tabla('docente')->get_designaciones($this->s__agente['id_docente']);
                 
            $per=  utf8_decode('período');
            
            $pdf->addText(70,480,10,"<b>CERTIFICO QUE: </b>".$ag." Legajo ".$leg." se ".utf8_decode('desempeña/nó')." como personal de la Universidad Nacional del Comahue como:"."\n"); 
            
            $x=80;
            $y=460;
            
            
            foreach ($desig as $des) {
               
               $pdf->addText($x,$y,10,"<b>".trim($des['cat'])."</b> ".trim($des['caracter'])." (Departamento: ".trim($des['depto']).") con ".utf8_decode('dedicación').trim($des['ded'])." durante el $per ". date_format(date_create($des['desde']),'d/m/Y')." al ".date_format(date_create($des['hasta']),'d/m/Y')."\n"); 
               $lic=$this->dep('datos')->tabla('designacion')->get_licencias($des['id_designacion']);
               $y=$y-20;
               foreach ($lic as $value) {
                   $pdf->addText($x+5,$y,10,"*".$value['descripcion']." desde ".$value['desde']." hasta ".$value['hasta']);
                   $y=$y-20;
               }
               
               $pdf->addText($x,$y,10," en las siguientes materias: ");
               $y=$y-20;
               $mat=$this->dep('datos')->tabla('asignacion_materia')->get_listado_desig($des['id_designacion']);
               foreach ($mat as $value) {
                   $pdf->addText($x+5,$y,10,"*".$value['desc_materia']." durante el ".$value['id_periodo']." del ".utf8_decode('año')." ".$value['anio']);
                   $y=$y-20;
               }
            }
            //$pdf->ezTable($datos, array('col1'=>'UA','col2'=>'Categoria','col3'=>'Dedicacion','col4'=>'Caracter','col5'=>'Desde','col6'=>'Hasta'), $titulo, $opciones);
            //$pdf->addText(80,$y,10,"<b>Se extiende el presente certificado el ".date("d/m/Y")." a las ".date("h").":".date("i")." ".date("A").", a pedido del interesado, y a los efectos de ser presentado ante quien corresponda."."\n"); 
            
            
        }

}
?>