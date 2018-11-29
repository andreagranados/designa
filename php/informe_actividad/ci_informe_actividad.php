<?php
class ci_informe_actividad extends toba_ci
{
    	protected $s__datos_filtro;
        protected $s__where;
        protected $s__listado;
         //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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
            unset($this->s__listado);
	}
	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                $this->s__listado=$this->dep('datos')->tabla('asignacion_materia')->informe_actividad($this->s__datos_filtro);
               // print_r($this->s__listado);
                $cuadro->set_datos($this->s__listado);    
		} 
	}
        
//        function vista_pdf(toba_vista_pdf $salida)
//        {
//           if (isset($this->s__listado)){
//               
//                //configuramos el nombre que tendrá el archivo pdf
//                $salida->set_nombre_archivo("Informe_Actividad.pdf");
//                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página              
//                $salida->set_papel_orientacion('landscape');
//                $salida->inicializar();
//                $pdf = $salida->get_pdf();
//           
//                //modificamos los márgenes de la hoja top, bottom, left, right
//                $pdf->ezSetMargins(80, 30, 3, 3);
//                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
//                //Primero definimos la plantilla para el número de página.
//                $formato = utf8_d_seguro('TKD # '.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].' Página {PAGENUM} de {TOTALPAGENUM}   '.'M:Materia - I:Investigación - E:Extensión - P:Postgrado - T:Tutorías - O:Otros');
//                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
//                $pdf->ezStartPageNumbers(400, 20, 8, 'left', $formato, 1); 
//                //Luego definimos la ubicación de la fecha en el pie de página.
//                $pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
//                //Configuración de Título.
//                $salida->titulo("Informe de Actividad TKD #".$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor']);
//                $titulo=" ";
//                //-- Cuadro con datos
//                $opciones = array(
//                    'splitRows'=>0,
//                    'showLines'=>2,
//                    'rowGap' => 0.7,//ancho de las filas
//                    'showHeadings' => true,
//                    'titleFontSize' => 10,
//                    'fontSize' => 8,
//                    'shadeCol' => array(0.9,3,0.9),
//                    'outerLineThickness' => 2,
//                    'innerLineThickness' => 0.7,
//                    'xOrientation' => 'center',
//                    'width' => 820
//                    );
//                $i=0;
//                foreach ($this->s__listado as $des) {
//                        $datos[$i]=array('col2' => $des['agente'], 'col3' => trim($des['legajo']) ,'col4' => $des['desig'],'col5' => $des['mat0'],'col6' => $des['mat1'],'col7' => $des['mat2'],'col8' => $des['mat3'],'col10' => $des['mat4'],'col11' => $des['mat5'],'col12' => $des['investig'],'col13' => $des['extens'],'col14' => $des['postgrado'],'col15' => $des['tutorias'],'col16' => $des['otros']);
//                        $i++;  
//                    }
//                
//               //genera la tabla de datos
//               
//                $pdf->ezTable($datos, array( 'col2'=>'<b>Agente</b>','col3' => '<b>Legajo</b>','col4' => '<b>Desig</b>','col5' => '<b>M1</b>','col6' => '<b>M2</b>','col7' => '<b>M3</b>','col8' => '<b>M4</b>','col10' =>'<b>M5</b>','col11' => '<b>M6</b>','col12' => '<b>I</b>','col13' => '<b>E</b>','col14' => '<b>P</b>','col15' => '<b>T</b>','col16' => '<b>O</b>'), $titulo, $opciones);
//                //agrega texto al pdf. Los primeros 2 parametros son las coordenadas (x,y) el tercero es el tamaño de la letra, y el cuarto el string a agregar
//                //$pdf->addText(350,600,10,'Informe de ticket de designaciones.'); 
//                //Encabezado: Logo Organización - Nombre 
//                //Recorremos cada una de las hojas del documento para agregar el encabezado
//                 foreach ($pdf->ezPages as $pageNum=>$id){ 
//                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
//                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
//                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
//                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
//                    $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
//                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
//                    $pdf->closeObject(); 
//                }    
//      
//            }
//        }
        function conf__pant_inicial(toba_ei_pantalla $pantalla){
             if(isset($this->s__datos_filtro['nro_540'])){
                  if (isset($this->s__listado)){
                      $this->evento('imprimir')->mostrar();
                  }else{
                      $this->evento('imprimir')->ocultar();
                  }
             }else{
                 $this->evento('imprimir')->ocultar();
             }
             
         }
        function vista_pdf(toba_vista_pdf $salida)
        {
          if(isset($this->s__datos_filtro['nro_540'])){
            if (isset($this->s__listado)){
               
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Informe_Actividad.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página              
                $salida->set_papel_orientacion('landscape');
                $salida->inicializar();
                $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
                $pdf->ezSetMargins(80, 30, 15, 15);
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                $formato = utf8_d_seguro('Informe de Actividad TKD # '.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].' Página {PAGENUM} de {TOTALPAGENUM}   ');
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(400, 20, 8, 'left', $formato, 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                //$pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $op=array();
                $op['justification']='center';
                $pdf->ezText('<b>Informe de Actividad TKD #'.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].'</b>',13,$op);
                $pdf->ezText('');

                foreach ($this->s__listado as $des) {
                    $leyenda='';
                     if($des['estado']=='L'){
                         $leyenda.=$des['desc_estado'];
                     }
                     $pdf->ezText(' <b>  AGENTE: <i>'.$des['agente'].'</i></b>'.' LEGAJO: '.$des['legajo'].'  '.$des['desig'], 11);
                     if($leyenda!=''){
                            $pdf->ezText('<u>'.'   '.$leyenda.'</u>',8,array('justification'=>'justification'));
                     }
                     if(isset($des['mat0'])){
                         $materias=$des['mat0'];
                         if(isset($des['mat1'])){
                             $materias.='<b>, </b>'.$des['mat1'];
                         }
                         if(isset($des['mat2'])){
                             $materias.='<b>, </b>'.$des['mat2'];
                         }
                         if(isset($des['mat3'])){
                             $materias.='<b>, </b>'.$des['mat3'];
                         }
                         if(isset($des['mat4'])){
                             $materias.='<b>, </b>'.$des['mat4'];
                         }
                         if(isset($des['mat5'])){
                             $materias.='<b>, </b>'.$des['mat5'];
                         }
                         $pdf->ezText('   <b>MATERIAS: </b>'.$materias, 10);
                     }
                     if(isset($des['investig']) && $des['investig']!=''){
                         $pdf->ezText('   <b>INVESTIGACION: </b>'.$des['investig'], 10);
                     }   
                     if(isset($des['extens']) && $des['extens']!=''){
                         $pdf->ezText('   <b>EXTENSION: </b>'.$des['extens'], 10);
                     }   
                     if(isset($des['postgrado']) && $des['postgrado']!=''){
                         $pdf->ezText('   <b>POSGRADO: </b>'.$des['postgrado'], 10);
                     }
                     if(isset($des['tutorias']) && $des['tutorias']!=''){
                         $pdf->ezText('   <b>TUTORIAS: </b>'.$des['tutorias'], 10);
                     }
                     if(isset($des['otros']) && $des['otros']!=''){
                         $pdf->ezText('   <b>OTROS: </b>'.$des['otros'], 10);
                     }
                     $pdf->ezText('---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------',10);
 
                    //$pdf->setLineStyle(5,'round');
                     
                    }
                
               //genera la tabla de datos
                
                //Recorremos cada una de las hojas del documento para agregar el encabezado
                 foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                    $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                    $pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
                    $pdf->closeObject(); 
                }    
      
            }
            }//si no filtro tkd no genera pdf
        }

	

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function evt__imprimir()
	{
            //->activar
            $this->desactivar();
	}

}
?>