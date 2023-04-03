<?php
class ci_p_investigacion extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__columnas;
       
        //-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro)) {
                $filtro->set_datos($this->s__datos_filtro);
		}
            $filtro->columna('uni_acad')->set_condicion_fija('es_igual_a',true)  ;
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
	
        //-----------------------------------------------------------------------------------
	//---- formulario columnas-------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__columnas(toba_ei_formulario $form)
	{
            $form->colapsar();
            $form->set_datos($this->s__columnas);    

	}
        function evt__columnas__modificacion($datos)
        {
            $this->s__columnas = $datos;
        }
	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
                    if($this->s__columnas['cod_regional']==0){
                        $c=array('cod_regional');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                    if($this->s__columnas['disciplina']==0){
                        $c=array('disciplina');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                    if($this->s__columnas['objetivo']==0){
                        $c=array('objetivo');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                    if($this->s__columnas['tipo_inv']==0){
                        $c=array('tipo_inv');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                     if($this->s__columnas['cuildirector']==0){
                        $c=array('cuildirector');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                     if($this->s__columnas['cuilcod']==0){
                        $c=array('cuilcod');
                        $this->dep('cuadro')->eliminar_columnas($c); 
                     }
                    $cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado_filtro($this->s__datos_filtro));
                    //$cuadro->set_titulo(utf8_decode('Listado  ').date('d/m/Y (H:i:s)'));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{           
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
                $this->dep('datos')->tabla('proyecto_adjuntos')->cargar($datos); 
		$this->set_pantalla('pant_edicion');
	}


	function resetear()
	{
		$this->dep('datos')->resetear();
		$this->set_pantalla('pant_seleccion');
	}

	//---- EVENTOS CI -------------------------------------------------------------------

	function evt__agregar()
	{
            $band = $this->dep('datos')->tabla('convocatoria_proyectos')->existe_convocatoria_vigente();
            if($band){
                $this->set_pantalla('pant_edicion');   
            }else{
                toba::notificacion()->agregar('No existen convocatorias vigentes', 'info');   
            }
	}

	function evt__volver()
	{
	     $this->resetear();
             $this->dep('ci_pinv_otros')->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_interno_pi')->resetear();
             $this->dep('ci_pinv_otros')->dep('ci_integrantes_pi')->dep('datos')->tabla('integrante_externo_pi')->resetear();
	}
        function conf__form_encabezado(toba_ei_formulario $form)
	{
            if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->dep('datos')->tabla('pinvestigacion')->get();
                $this->s__nombrep=$pi['denominacion'];
                $this->s__codigo=$pi['codigo'];
                $this->s__ua=$pi['uni_acad'];
                $texto=$pi['denominacion']." (".$pi['codigo'].") de: ".$pi['uni_acad'];
                $form->set_titulo($texto);
            }        
        }
        function vista_pdf(toba_vista_pdf $salida){
         if ($this->dep('datos')->tabla('pinvestigacion')->esta_cargada()) {
                $pi=$this->dep('datos')->tabla('pinvestigacion')->get(); 
                if ($this->dep('datos')->tabla('viatico')->esta_cargada()) {
                   $vi=$this->dep('datos')->tabla('viatico')->get();
                   if($vi['estado']=='A'){//solo si el viatico esta aprobado
                        $dato=array();
                        //configuramos el nombre que tendrá el archivo pdf
                        $salida->set_nombre_archivo("Planilla_Viatico.pdf");
                        //recuperamos el objteo ezPDF para agregar la cabecera y el pie de página 
                        $salida->set_papel_orientacion('portrait');//landscape
                        $salida->inicializar();
                
                        $pdf = $salida->get_pdf();
                              //terc izquierda 
                        $pdf->ezSetMargins(80, 50, 45, 45);
                        //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                        //Primero definimos la plantilla para el número de página.
                        $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM} ');
                        
                        //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                        //$pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                        //Luego definimos la ubicación de la fecha en el pie de página.
                        $pdf->addText(380,20,8,'Mocovi - Designaciones '.date('d/m/Y h:i:s a')); 
                        //Configuración de Título.
                        $salida->titulo(utf8_d_seguro('UNIVERSIDAD NACIONAL DEL COMAHUE'.chr(10).'SECRETARÍA DE CIENCIA Y TÉCNICA'.chr(10).'SOLICITUD DE ANTICIPO DE VIÁTICOS '));    
                        $titulo="   ";
                        $opciones = array(
                            'showLines'=>0,
                            'rowGap' => 1,
                            'showHeadings' => true,
                            'titleFontSize' => 9,
                            'fontSize' => 10,
                            'shadeCol' => array(0.9,0.9,0.9),
                            'outerLineThickness' => 0,//grosor de las lineas exteriores
                            'innerLineThickness' => 0,
                            'xOrientation' => 'center',
                            'width' => 1000,
                            'cols'=>array('col1'=>array('width'=>180,'justification'=>'center'),'col2'=>array('width'=>180,'justification'=>'center'),'col3'=>array('width'=>180,'justification'=>'center'))
                        );

                        $tipo_ac=$this->dep('datos')->tabla('viatico')->get_tipo_actividad($vi['id_viatico']);
                        $mt=$this->dep('datos')->tabla('viatico')->get_medio_transporte($vi['id_viatico']);
                        $desti=$this->dep('datos')->tabla('viatico')->get_destinatario($vi['id_viatico']);
                        $cuil=$this->dep('datos')->tabla('viatico')->get_destinatario_cuil($vi['id_viatico']);
                        $dias=$vi['cant_dias'];
                        $dire=$this->dep('datos')->tabla('pinvestigacion')->get_director($vi['id_proyecto']);
                        $montov=$this->dep('datos')->tabla('montos_viatico')->get_monto_viatico(date("Y-m-d",strtotime($vi['fecha_regreso'])));
                        $fn=$this->dep('datos')->tabla('pinvestigacion')->get_categ($vi['id_proyecto'],$vi['nro_docum_desti']);
                        //el monto del viatico se setea al momento de imprimir la planilla
                        $this->dep('datos')->tabla('viatico')->modifica_monto($vi['id_viatico'],$montov);
                        $pdf->ezText("\n\n\n\n", 10);
                        $pdf->ezText('<b>NOMBRE DEL PROYECTO: </b>'.$pi['denominacion'], 10);
                        $pdf->ezText('<b>'.utf8_d_seguro('UNIDAD ACADÉMICA: ').'</b>'.$pi['uni_acad'], 10);
                        $pdf->ezText('<b>'.utf8_d_seguro('CÓDIGO DEL PROYECTO: ').'</b>'.$pi['codigo'], 10);
                        $pdf->ezText('<b>DIRECTOR DEL PROYECTO: </b>'.$dire, 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('<b>TIPO DE ACTIVIDAD: </b>'.$tipo_ac, 10);
                        $pdf->ezText('<b>NOMBRE DE LA ACTIVIDAD: </b>'.$vi['nombre_actividad'], 10);
                        $pdf->ezText('<b>DESTINATARIO: </b>'.$desti, 10);
                        $pdf->ezText('<b>CUIL: </b>'.$cuil, 10);
                        $pdf->ezText('<b>CBU: </b>'.$vi['cbu'], 10);
                        $pdf->ezText('<b>'.utf8_d_seguro('CATEGORÍA: ') .'</b>'.$fn, 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('<b>DESTINO: </b>'.$vi['destino'], 10);
                        $pdf->ezText('<b>MEDIO DE TRANSPORTE: </b>'.$mt, 10);
                        $pdf->ezText('<b>SALIDA EFECTIVA: </b>'.date("d/m/Y H:i",strtotime($vi['fecha_salida'])).' hs', 10);//date("d/m/Y",strtotime($des['fec_nacim']))
                        $pdf->ezText('<b>REGRESO EFECTIVO: </b>'.date("d/m/Y H:i",strtotime($vi['fecha_regreso'])).' hs' , 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText(utf8_d_seguro('------------------------------------------------------ LIQUIDACIÓN DE GASTOS ------------------------------------------------------'), 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('<b>'.utf8_d_seguro('DÍAS A LIQUIDAR: ').'</b>'.$vi['cant_dias'], 10);
                        $pdf->ezText('<b>'.utf8_d_seguro('VIÁTICOS DIARIOS $: ').'</b> '.number_format($montov,2,',','.'), 10);
                        $pdf->ezText('<b>SON $: </b> '.number_format(($vi['cant_dias']*$montov),2,',','.'), 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('<b>AUTORIZACIONES:</b> ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        
                        $pdf->ezText("\n\n\n", 10);
//                        $pdf->addText(40 ,80,10,'..........................'); 
//                        $pdf->addText(40 ,70,10,'SOLICITANTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT'); 
//                        $pdf->addText(240,80,10,'............................................'); 
//                        $pdf->addText(240,70,10,'DIRECTOR/CO-DIRECTOR'); 
//                        $pdf->addText(240,60,10,'     DEL PROYECTO');
//                        $pdf->addText(450,80,10,'...................................'); 
//                        $pdf->addText(450,70,10,'  SECRETARIO DE'); 
//                        $pdf->addText(450,60,10,utf8_d_seguro('CIENCIA Y TÉCNICA')); 
                        $datos=array();
                        $datos[0]=array('col1'=>'....................................','col2'=>'....................................','col3'=>'....................................');
                        $datos[1]=array('col1'=>$desti,'col2'=>'DIRECTOR/CO-DIRECTOR DEL PROYECTO','col3'=>utf8_d_seguro('SECRETARIO DE CIENCIA Y TÉCNICA'));
                        //$datos=array(array('col1'=>'SOLICITANTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT','col2'=>'DIRECTOR/CO-DIRECTOR DEL PROYECTO','col3'=>utf8_d_seguro('SECRETARIO DE CIENCIA Y TÉCNICA')));
                        $pdf->ezTable($datos, array('col1'=>'','col2'=>'','col3'=>''), '', $opciones);                        
                        //Recorremos cada una de las hojas del documento para agregar el encabezado
                        foreach ($pdf->ezPages as $pageNum=>$id){ 
                            $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
                            //agregamos al documento la imagen y definimos su posición a través de las coordenadas (x,y) y el ancho y el alto.
                            $imagen = toba::proyecto()->get_path().'/www/img/logo_uc.jpg';
                            $imagen2 = toba::proyecto()->get_path().'/www/img/sein.jpg';
                            $pdf->addJpegFromFile($imagen, 40, 715, 70, 66); 
                            $pdf->addJpegFromFile($imagen2, 480, 715, 70, 66);
                            $pdf->closeObject(); 
                        }                             
                   }
                 }
            }     
         }       


}

?>