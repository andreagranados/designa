<?php
class ci_p_investigacion extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
       

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
	}
	

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('pinvestigacion')->get_listado_filtro($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('pinvestigacion')->cargar($datos);
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
		$this->set_pantalla('pant_edicion');
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
                               
                        $pdf->ezSetMargins(80, 50, 3, 3);
                        //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
                        //Primero definimos la plantilla para el número de página.
                        $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM} ');
                        
                        //Determinamos la ubicación del número página en el pié de pagina definiendo las coordenadas x y, tamaño de letra, posición, texto, pagina inicio 
                        //$pdf->ezStartPageNumbers(300, 20, 8, 'left', utf8_d_seguro($formato), 1); 
                        //Luego definimos la ubicación de la fecha en el pie de página.
                        $pdf->addText(480,20,8,date('d/m/Y h:i:s a')); 
                        //Configuración de Título.
                        $salida->titulo(utf8_d_seguro('UNIVERSIDAD NACIONAL DEL COMAHUE'.chr(10).'SECRETARÍA DE CIENCIA Y TÉCNICA'.chr(10).'SOLICITUD DE ANTICIPO DE VIÁTICOS '));    
                        $titulo="   ";
                        $opciones = array(
                            'splitRows'=>0,
                            'rowGap' => 0,
                            'showHeadings' => true,
                            'titleFontSize' => 9,
                            'fontSize' => 10,
                            'shadeCol' => array(0,0,0),
                            'outerLineThickness' => 0,
                            'innerLineThickness' => 0,
                            'xOrientation' => 'center',
                            'width' => 500
                        );

                        $tipo_ac=$this->dep('datos')->tabla('viatico')->get_tipo_actividad($vi['id_viatico']);
                        $mt=$this->dep('datos')->tabla('viatico')->get_medio_transporte($vi['id_viatico']);
                        $desti=$this->dep('datos')->tabla('viatico')->get_destinatario($vi['id_viatico']);
                        $cuil=$this->dep('datos')->tabla('viatico')->get_destinatario_cuil($vi['id_viatico']);
                        $dias=$vi['cant_dias'];
                        $dire=$this->dep('datos')->tabla('pinvestigacion')->get_director($vi['id_proyecto']);
                        $montov=$this->dep('datos')->tabla('montos_viatico')->get_monto_viatico();
                        $fn=$this->dep('datos')->tabla('pinvestigacion')->get_funcion($vi['id_proyecto'],$vi['id_designacion']);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('                 <b>NOMBRE DEL PROYECTO: </b>'.$pi['denominacion'], 10);
                        $pdf->ezText('                 <b>UNIDAD ACADEMICA: </b>'.$pi['uni_acad'], 10);
                        $pdf->ezText('                 <b>CODIGO DEL PROYECTO: </b>'.$pi['codigo'], 10);
                        $pdf->ezText('                 <b>DIRECTOR DEL PROYECTO: </b>'.$dire, 10);
                        $pdf->ezText('                 <b>TIPO DE ACTIVIDAD: </b>'.$tipo_ac, 10);
                        $pdf->ezText('                 <b>NOMBRE DE LA ACTIVIDAD: </b>'.$vi['nombre_actividad'], 10);
                        $pdf->ezText('                 <b>DESTINATARIO: </b>'.$desti, 10);
                        $pdf->ezText('                 <b>CUIL: </b>'.$cuil, 10);
                        $pdf->ezText('                 <b>FUNCION: </b>'.$fn, 10);
                        $pdf->ezText('                 <b>DESTINO: </b>'.$vi['destino'], 10);
                        $pdf->ezText('                 <b>MEDIO DE TRANSPORTE: </b>'.$mt, 10);
                        $pdf->ezText('                 <b>SALIDA EFECTIVA: </b>'.date("d/m/Y H:i",strtotime($vi['fecha_salida'])).' hs', 10);//date("d/m/Y",strtotime($des['fec_nacim']))
                        $pdf->ezText('                 <b>REGRESO EFECTIVO: </b>'.date("d/m/Y H:i",strtotime($vi['fecha_regreso'])).' hs' , 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('                  ------------------------------------------------------ LIQUIDACION DE GASTOS ------------------------------------------------------', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('                 <b>DIAS A LIQUIDAR: </b>'.$vi['cant_dias'], 10);
                        $pdf->ezText('                 <b>VIATICOS DIARIOS $:</b> '.$montov, 10);
                        $pdf->ezText('                 <b>SON $: </b> '.$vi['cant_dias']*$montov, 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('                 <b>AUTORIZACIONES:</b> ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->ezText('  ', 10);
                        $pdf->addText(40 ,80,10,'......................'); 
                        $pdf->addText(40 ,70,10,'SOLICITANTE'); 
                        $pdf->addText(240,80,10,'....................................'); 
                        $pdf->addText(240,70,10,'DIRECTOR/CO-DIRECTOR'); 
                        $pdf->addText(240,60,10,'     DEL PROYECTO');
                        $pdf->addText(450,80,10,'...................................'); 
                        $pdf->addText(450,70,10,'  SECRETARIO DE'); 
                        $pdf->addText(450,60,10,'CIENCIA Y TECNICA'); 
                        //$pdf->ezText('                 <b>........................                         ..............................................                      ..............................................</b> ', 10);
                        //$pdf->ezText('                 <b>SOLICITANTE                         DIRECTOR/CO-DIRECTOR                       SECRETARIO DE </b> ', 10);
                        //$pdf->ezText('                 <b>                                                     DEL PROYECTO                                      CIENCIA Y TECNICA </b> ', 10);
                   }
                 }
            }     
         }       


}

?>