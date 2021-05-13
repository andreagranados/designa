<?php
class ci_certificacion_periodo extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__agente;
        
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
            $this->s__agente=$datos;
            $this->s__datos_filtro = $datos;
	}

	function evt__filtro__cancelar()
	{
            unset($this->s__datos_filtro);
          
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
		$cuadro->set_datos($this->dep('datos')->tabla('docente')->get_docente($this->s__datos_filtro));
		} 
	}

        function vista_pdf(toba_vista_pdf $salida)
        {
            $salida->set_papel_orientacion('portrait');
            $salida->inicializar();
            $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
            //$pdf->ezSetMargins(80, 50, 5, 5);
            $pdf->ezSetMargins(30, 30, 50, 30);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
            $formato = 'Página {PAGENUM} de {TOTALPAGENUM}';
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
//            $pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
//                //Luego definimos la ubicación de la fecha en el pie de página.
//            $pdf->addText(480,20,8,"Sistema MOCOVI-Modulo Designaciones Docentes".date('d/m/Y h:i:s a')); 
//            $pdf->addText(80,170,10,"Se extiende el presente certificado el ".date("d/m/Y")." a las ".date("h").":".date("i")." ".date("A").", a pedido del interesado, y a los efectos de ser presentado ante quien corresponda."."\n"); 
//            $pdf->addText(750,90,10,"------------------"); 
//            $pdf->addText(750,80,10,"Firma y Sello"); 
                //Configuración de Título.
            
           $salida->titulo(utf8_d_seguro(utf8_decode("Planilla de Designación del Docente - Período ".$this->s__datos_filtro['anio'])));
 
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
                'width' => 500,
            );
           
            $ag=$this->dep('datos')->tabla('docente')->get_agente($this->s__agente['id_docente']);
            $leg=$this->dep('datos')->tabla('docente')->get_legajo($this->s__agente['id_docente']);
            $salida->set_nombre_archivo('Certif_'.$leg.'_'.$this->s__datos_filtro['anio'].".pdf");
           //recupero las designaciones del periodo previamente seleccionado
            $desig=$this->dep('datos')->tabla('docente')->get_designaciones_periodo($this->s__agente['id_docente'],$this->s__datos_filtro['anio']);
            $pdf->ezText("\n", 7);
            $texto="Docente: <b>".$ag."</b> Legajo ".$leg;
            $pdf->ezText($texto,12);
            $pdf->ezText("\n", 7);
            
            foreach ($desig as $des) {//para cada designacion
                if($des['hasta'] == null){
                    $hasta='-';
                }else{
                    $hasta=date_format(date_create($des['hasta']),'d/m/Y');
                }
                $texto= utf8_decode("Categoría y Dedicación: <b>".trim($des['cat_estat'])."-".$des['dedic']."</b> Desde: <b>".date_format(date_create($des['desde']),'d/m/Y'). "</b> Hasta: <b>".$hasta."</b>");
                $pdf->ezText($texto,12);
                $texto='Normativa: '.$des['norma_ultima'].', '.$des['norma_ant'];
                $pdf->ezText($texto,12);
                $texto= utf8_decode("Situación: <b>".$des['caracter']."</b>");
                $pdf->ezText($texto,12);
                $texto= "Departamento: <b>".$des['depto']."</b>";
                $pdf->ezText($texto,12);
                $texto= utf8_decode("Área:")." <b>".$des['area']."</b>";
                $pdf->ezText($texto,12);
                $texto= utf8_decode("Orientación:")." <b>".$des['orient']."</b>";
                $pdf->ezText($texto,12);
                if(isset($des['lic'])){
                    $texto= utf8_decode("Lic:")." <b>".$des['lic']."</b>";
                    $pdf->ezText($texto,12);
                }
                $pdf->ezText("\n", 7);
                $mate=$this->dep('datos')->tabla('asignacion_materia')->get_listado_desig_cert($des['id_designacion'],$this->s__datos_filtro['anio']);
                $i=0;
                $datos='';
                foreach ($mate as $ma) {//busco todas las materias correspondientes al año previamente seleccionado
                        $datos[$i]=array('col1' => $ma['desc_materia'], 'col2' => $ma['carrera'],'col3' => $ma['periodo'],'col4' => $ma['rol'],'col5' => $ma['carga_horaria'],'col6' => $ma['moddes']);
                        $i++;
                }
               
                
                $pdf->ezTable($datos, array('col1'=>'Asignatura', 'col2' => 'Carrera','col3' => utf8_decode('Período'),'col4' => 'Rol','col5' => 'Hs','col6' => utf8_decode('Módulo')), 'ACTIVIDAD ACADEMICA', $opciones);
                $pdf->ezText("\n", 7);
            }
            //busco la actividad en investigacion
            $inve=$this->dep('datos')->tabla('integrante_interno_pi')->get_proyinv_docente($this->s__agente['id_docente'],$this->s__datos_filtro['anio']);
            $i=0;
            $datosi=array();
            foreach ($inve as $in) {
                $datosi[$i]=array('col1'=>'<b>Proyecto: </b>'.$in['denominacion']);
                $i++;
                $datosi[$i]=array('col1'=>'<b>'.utf8_decode('Código: ').'</b>'.$in['codigo']);
                $i++;
                $datosi[$i]=array('col1'=>'<b>'.utf8_decode('Función: ').'</b>'.$in['funcion_p'].'<b>'.utf8_decode('Categoría: ').'</b> '.$in['categ']);
                $i++;
                $datosi[$i]=array('col1'=>'<b>Desde: </b>'.date_format(date_create($in['desde']),'d/m/Y').'<b> Hasta: </b>'.date_format(date_create($in['hasta']),'d/m/Y'));
                $i++;
                $datosi[$i]=array('col1'=>'<b>Hs Semanales: </b>'.$in['carga_horaria']);
                $i++;
              }
            $titulo=array();
            $titulo[0]=array('dato'=>utf8_decode('<b>INVESTIGACION</b>'));
            $pdf->ezTable($titulo,array('dato'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>500,'cols'=>array('dato'=>array('justification'=>'center'))));
            $pdf->ezTable($datosi,array('col1'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>500,'cols'=>array('dato'=>array('justification'=>'center'))));
             
            $pdf->ezText("\n\n\n", 10);
            foreach ($pdf->ezPages as $pageNum=>$id){ 
                   $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                   $pdf->addText(200,15,8,utf8_decode("Sistema MOCOVI-Módulo Designaciones Docentes").date('d/m/Y h:i:s a'));
                   $pdf->closeObject(); 
                }  
        }
	

	

}
?>