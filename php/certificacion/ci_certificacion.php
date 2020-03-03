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
            $this->pantalla()->tab("pant_certif")->desactivar();
            if (isset($this->s__datos_filtro)) {
			$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_docentes_propios($this->s__where));
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
            $pdf->addText(80,170,10,"Se extiende el presente certificado el ".date("d/m/Y")." a las ".date("h").":".date("i")." ".date("A").", a pedido del interesado, y a los efectos de ser presentado ante quien corresponda."."\n"); 
            $pdf->addText(750,90,10,"------------------"); 
            $pdf->addText(750,80,10,"Firma y Sello"); 
                //Configuración de Título.
            $salida->titulo(utf8_d_seguro("Certificado de Actividades Académicas"));
 
            $titulo=" ";
            //-- Cuadro con datos
            $opciones = array(
                    'splitRows'=>0,
                    'showLines'=>0,
                    'rowGap' => 1,
                    'showHeadings' => true,
                    'titleFontSize' => 9,
                    'fontSize' => 10,
                    'shadeCol' => array(0.9,3,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9,0.9),
                    'outerLineThickness' => 0.7,
                    'innerLineThickness' => 0.7,
                    'xOrientation' => 'center',
                    'width' => 700
                    );
            
            $ag=$this->dep('datos')->tabla('docente')->get_agente($this->s__agente['id_docente']);
            $leg=$this->dep('datos')->tabla('docente')->get_legajo($this->s__agente['id_docente']);
            $desig=$this->dep('datos')->tabla('docente')->get_designaciones($this->s__agente['id_docente']);
                 
            $i=0;
            
            $texto="<b>CERTIFICO QUE: </b>".$ag." Legajo ".$leg." se ".utf8_decode('desempeña/ó')." en la Universidad Nacional del Comahue como:"."\n";
            $datos[$i]=array('col1' => $texto);
            $i++;
                 
            
            foreach ($desig as $des) {
               $norma="";
               if($des['tipo_norma']!=null){
                   $norma=", ".$des['tipo_norma'].$des['nro_norma']."/".date('Y',strtotime($des['fecha'])).", ";
               }
               if($des['hasta']==null){
                   $hasta="";
               }else{
                   $hasta=" hasta ".date_format(date_create($des['hasta']),'d/m/Y');
               }
               $texto="<b>".trim($des['cat'])."</b> ".trim($des['caracter'])." (".trim($des['ua'])." Departamento: ".trim($des['depto']).") con ".utf8_decode('dedicación ').trim($des['ded']).$norma." desde ". date_format(date_create($des['desde']),'d/m/Y').$hasta;
               $datos[$i]=array('col1' => $texto);
               $i++;
               $lic=$this->dep('datos')->tabla('designacion')->get_licencias($des['id_designacion']);
               
               foreach ($lic as $value) {
                   $texto="<i>".trim($value['descripcion'])." desde ".date_format(date_create($value['desde']),'d/m/Y')." hasta ".date_format(date_create($value['hasta']),'d/m/Y')."</i>)";
                   $datos[$i]=array('col1' => $texto);
                   $i++;
               }
               
               $primera=true;
               $mat=$this->dep('datos')->tabla('asignacion_materia')->get_listado_desig($des['id_designacion']);
               foreach ($mat as $value) {
                   if($primera){
                        $texto="    en las siguientes materias: ";
                        $datos[$i]=array('col1' => $texto);
                        $primera=false;
                   }
                   $i++;
                   
                   $texto="       *".$value['desc_materia']." ".$value['id_periodo']." ".utf8_decode('año')." ".$value['anio'];
                   $datos[$i]=array('col1' => $texto); 
               }
               
               $datos[$i]=array('col1' => '   ');
               $i++;
               
            }
            $pdf->ezTable($datos, array('col1'=>''), $titulo, $opciones);
            
            //Recorremos cada una de las hojas del documento para agregar el encabezado
            foreach ($pdf->ezPages as $pageNum=>$id){ 
                $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                //definimos el path a la imagen de logo de la organizacion
                $imagen = toba::proyecto()->get_path().'/www/img/logo-unc.jpg';
                $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                $pdf->addJpegFromFile($imagen, 20, 515, 70, 66); 
                $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                $pdf->closeObject();                  
            }
            
        }

}
?>