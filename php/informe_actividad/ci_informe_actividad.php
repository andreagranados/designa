<?php
class ci_informe_actividad extends toba_ci
{
    	protected $s__datos_filtro;
        protected $s__where;
        protected $s__listado;
        protected $s__columnas;
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__columnas(toba_ei_formulario $form)
	{
            $form->set_datos($this->s__columnas);    

	}
        function evt__columnas__modificacion($datos)
        {
            $this->s__columnas = $datos;
        }
         //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
            $filtro->columna('uni_acad')->set_condicion_fija('es_igual_a',true)  ;
            $filtro->columna('anio')->set_condicion_fija('es_igual_a',true)  ;
            $filtro->columna('nro_540')->set_condicion_fija('es_igual_a',true)  ;
            $filtro->columna('iddepto')->set_condicion_fija('es_igual_a',true)  ;
            $filtro->columna('legajo')->set_condicion_fija('es_igual_a',true)  ;
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
                //$columnas componentes obligatoras: clave, titulo
//                $cuadro->limpiar_columnas();
//                $l['clave'] ='nro_docum';
//                $l['titulo'] ='DNI';
//                $c[1] = $l;
//                $l['clave'] ='legajo';
//                $l['titulo'] ='Legajo';
//                $c[0] = $l;
//                $this->dep('cuadro')->agregar_columnas($c);
                
                if($this->s__columnas['nro_docum']==0){
                        $c=array('nro_docum');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                }
                $this->s__listado=$this->dep('datos')->tabla('asignacion_materia')->informe_actividad($this->s__datos_filtro);
                $cuadro->set_datos($this->s__listado);//return $this->s__listado;
		} 
	}
        

        function conf__pant_inicial(toba_ei_pantalla $pantalla){
             if(isset($this->s__datos_filtro['nro_540']) or isset($this->s__datos_filtro['legajo'])){
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
          $bandera=false;
          if(isset($this->s__datos_filtro['legajo'])){ //si filtra por legajo sale anexo 1
                $formato = utf8_decode('Informe de Actividad - Período '.$this->s__datos_filtro['anio']['valor'].' Legajo '.$this->s__datos_filtro['legajo']['valor'].' Página {PAGENUM} de {TOTALPAGENUM}   '); 
                $texto=utf8_decode("<b>Informe de Actividad - Período: ".$this->s__datos_filtro['anio']['valor']." Legajo ".$this->s__datos_filtro['legajo']['valor'].'</b>');
                $anexo='<b>ANEXO I</b>';
                $bandera=true;
          }else{//sino filtro por legajo pregunta si filtro por tkd
              if(isset($this->s__datos_filtro['nro_540'])){
                $formato = utf8_decode('Informe de Actividad TKD # '.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].' Página {PAGENUM} de {TOTALPAGENUM}   ');
                $texto="<b>Informe de Actividad TKD #".$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].'</b>';
                $anexo='<b>ANEXO II</b>';
                $bandera=true;
            }
          }   
         if($bandera){
            if (isset($this->s__listado)){
               
                //configuramos el nombre que tendrá el archivo pdf
                $salida->set_nombre_archivo("Informe_Actividad.pdf");
                //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página              
                $salida->set_papel_orientacion('landscape');
                $salida->inicializar();
                $pdf = $salida->get_pdf();
           
                //modificamos los márgenes de la hoja top, bottom, left, right
                $pdf->ezSetMargins(100, 30, 15, 15);//80,30,15,15
                //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                //Primero definimos la plantilla para el número de página.
                //$formato = utf8_d_seguro('Informe de Actividad TKD # '.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].' Página {PAGENUM} de {TOTALPAGENUM}   ');
                //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                $pdf->ezStartPageNumbers(700, 20, 8, 'left', $formato, 1); 
                //Luego definimos la ubicación de la fecha en el pie de página.
                //$pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
                //Configuración de Título.
                $op=array();
                $op['justification']='center';
                //$pdf->ezText('<b>Informe de Actividad TKD #'.$this->s__datos_filtro['nro_540']['valor']."/".$this->s__datos_filtro['anio']['valor'].'</b>',13,$op);
                $pdf->ezText($texto,13,$op);
                $pdf->ezText('');

                foreach ($this->s__listado as $des) {
                    $departamentos=$this->dep('datos')->tabla('asignacion_materia')->desempeno_en($des['id_designacion'],$this->s__datos_filtro['anio']['valor']);
                    //print_r($departamentos);
                    $leyenda='';
                     if($des['estado']=='L' or $des['estado']=='B' ){
                         $leyenda.=$des['desc_estado'];
                     }
                     $pdf->ezText(' <b>  AGENTE: <i>'.$des['agente'].'</i></b>'.' LEGAJO: '.$des['legajo'].'  '.'<b>'.$des['desig'].'</b>', 11);
                     if($leyenda!=''){
                            $pdf->ezText('    '.'<u>'.$leyenda.'</u>',8,array('justification'=>'justification'));
                     }
                     
                     
                     if(isset($des['mat0'])){
                         $pdf->ezText(utf8_decode(' <b> DESEMPEÑO EN:</b> ').$departamentos,10);
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
                    //$pdf -> Line(20, 20, 150, 200);
                    //$pdf->setLineStyle(5,'round');
                     
                    }
                
                //Recorremos cada una de las hojas del documento para agregar el encabezado
                 foreach ($pdf->ezPages as $pageNum=>$id){ 
                    $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                    //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                    $imagen = toba::proyecto()->get_path().'/www/img/logo_sti.jpg';
                    $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
                    $pdf->addJpegFromFile($imagen, 12, 510, 70, 66); 
                    $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
                    $pdf->addText(700,20,8,date('d/m/Y h:i:s a')); 
                    $pdf->selectFont('./fonts/Helvetica.afm');
                    //$pdf->selectFont('./fonts/Times.afm');
                    $pdf->addText(410,535,12,$anexo); 
                    $pdf->closeObject(); 
                }    
      
            }
            }//si no filtro tkd no genera pdf
        }

	

	//-----------------------------------------------------------------------------------
	//---- Eventos ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

//	function evt__imprimir()
//	{
//            //->activar
//            $this->desactivar();
//	}

}
?>