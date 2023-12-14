<?php
class ci_presupuestar extends toba_ci
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

    function evt__agregar(){
        $this->set_pantalla('pant_detalle');
    }
     //-----------------------------------------------------------------------------------
    //---- cuadro_general -----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
    function conf__cuadro_general(toba_ei_cuadro $cuadro)
        {
        $salida=$this->dep('datos')->tabla('presupuesto')->get_listado_filtro($this->s__where);
        $cuadro->set_datos($salida);
        }

    function evt__cuadro_general__seleccion($datos)
    {
        $this->dep('datos')->tabla('presupuesto')->cargar($datos);
        $this->set_pantalla('pant_detalle');
    }
    //-----------------------------------------------------------------------------------
    //---- Eventos ----------------------------------------------------------------------
    //-----------------------------------------------------------------------------------
 function vista_pdf(toba_vista_pdf $salida){
     $pres=$this->dep('datos')->tabla('presupuesto')->get();
     if($pres['id_estado']<>'I' and $pres['id_estado']<>'R'){//el presupuesto fue enviado por la UA
        $salida->set_papel_orientacion('landscape');
        $salida->inicializar();
        $pdf = $salida->get_pdf();//top,bottom,left,righ
        $pdf->ezSetMargins(75, 50, 20, 20);
        //Configuramos el pie de página. El mismo, tendra el número de página centrado en la página y la fecha ubicada a la derecha. 
        //Primero definimos la plantilla para el número de página.
        $formato = utf8_decode('Página {PAGENUM} de {TOTALPAGENUM}   ');
        $pdf->ezStartPageNumbers(100, 20, 8, 'left', utf8_d_seguro($formato), 1); 
        $usuario = toba::usuario()->get_nombre();
            //Luego definimos la ubicación de la fecha en el pie de página.
        //$pdf->addText(500,20,8,'Generado por usuario: '.$usuario.' '.date('d/m/Y h:i:s a')); 
        $titulo="   ";
        $opciones = array(
            'showLines'=>1,
            'splitRows'=>0,
            'rowGap' => 0,//, the space between the text and the row lines on each row
           // 'lineCol' => (r,g,b) array,// defining the colour of the lines, default, black.
            //'showLines'=>2,//coloca las lineas horizontales
            //'showHeadings' => true,//muestra el nombre de las columnas
            'titleFontSize' => 9,
            'fontSize' => 9,
            //'shadeCol' => array(1,1,1,1,1,1,1,1,1,1,1,1),
            //'shadeCol' => array(0.1,0.1,0.1),//darle color a las filas intercaladamente
            'outerLineThickness' => 0.7,
            'innerLineThickness' => 0.7,
            'xOrientation' => 'center',
            'width' => 800//,
            );
        
        
        $datos_pres=$this->dep('ci_detalle_presupuesto')->dep('datos')->tabla('item_presupuesto')->get_listado($pres['nro_presupuesto']);
        $anio=$this->dep('ci_detalle_presupuesto')->dep('datos')->tabla('mocovi_periodo_presupuestario')->get_anio($pres['id_periodo']);
        $salida->set_nombre_archivo("Presupuesto_".$pres['nro_presupuesto'].".pdf");
        $salida->titulo(utf8_d_seguro("PRESUPUESTO Nº ".$pres['nro_presupuesto']));
        $pdf->ezText("\n\n", 10);
        if($pres['tipo']=='R'){
            $pdf->ezText('                <b>TIPO:</b> REFUERZO ', 10);
        }
        $pdf->ezText('                <b>EXPEDIENTE: </b>'.$pres['nro_expediente'], 10);
        $ua=utf8_decode('UNIDAD ACADÉMICA:');
        $per=utf8_decode('PERÍODO:');
        $pdf->ezText('                <b>'.$ua.' </b>'.$pres['uni_acad'], 10);
        $pdf->ezText('                <b>'.$per.' </b>'.$anio, 10);
        
        $perfil = toba::manejador_sesiones()->get_perfiles_funcionales();
        if(in_array('dependencias',$perfil)){//es la SEAC
            if($pres['id_estado']=='A'){
                $band='UA';
            }
        }else{
                if(in_array('presupuestar_seac',$perfil)){
                    if($pres['id_estado']=='H'){
                        $band='SEAC';
                    }
                }else{
                    if(in_array('presupuestar_seha',$perfil)){
                       if($pres['id_estado']=='P'){
                            $band='SEHA';
                       }
                    }
                }
         }
        if($band=='SEAC'){
            $pdf->ezText('                <b>OBSERVACION SEAC:'.' </b>'.$pres['observacion_seac'], 10);
        } 
      
        $i=0;
        $sum=0;
        $suma=0;
        $sumh=0;
        $cols=array('col1'=>'<b>CAT EST</b>','col2' => '<b>CATMAPU</b>','col3' => '<b>DESDE</b>','col4' => '<b>HASTA</b>','col5' => '<b>DIAS</b>','col6' => '<b>CANT</b>','col7' => '<b>COSTO DIA</b>','col8' => '<b>TOTAL SOLICITADO</b>');
        $opc=array('showLines'=>2,'shaded'=>0,'rowGap' => 3,'width'=>700,'cols'=>array('col1'=>array('width'=>80),'col2'=>array('width'=>80),'col3'=>array('width'=>90),'col4'=>array('width'=>90),'col5'=>array('width'=>40),'col6'=>array('width'=>40),'col7'=>array('width'=>140,'justification'=>'right'),'col8'=>array('width'=>140,'justification'=>'right')));
        $datos=array();
       // print_r($datos_pres);exit;
         
        foreach ($datos_pres as $item) {
            switch ($band) {
                case 'UA':
                    $datos[$i]=array('col1' => $item['cat_est'],'col2' => $item['cat_mapuche1'],'col3' => date("d/m/Y",strtotime($item['desde'])),'col4' => date("d/m/Y",strtotime($item['hasta'])),'col5' => $item['dias'],'col6' => $item['cantidad'],'col7' => number_format($item['costo_diario'],2,',','.'),'col8' => number_format($item['total'],2,',','.'));  
                    $sum=$sum+$item['total'];
                    break;
                case 'SEAC':
                    if($item['check_seac']==1){
                        
                       $cate=$this->dep('datos')->tabla('macheo_categ')->get_cat_equivalente($item['cat_seac']);
                       $datos[$i]=array('col1' => $cate,'col2' => $item['cat_seac'],'col3' => date("d/m/Y",strtotime($item['desde_seac'])),'col4' => date("d/m/Y",strtotime($item['hasta_seac'])),'col5' => $item['dias_seac'],'col6' => $item['cant_seac'],'col7' => number_format($item['costo_dia_seac'],2,',','.'),'col8' => number_format($item['total_seac'],2,',','.'));  
                       $suma=$suma+$item['total_seac']; 
                    }
                    break;
                case 'SEHA':
                    if($item['check_seha']==1){
                        $cate=$this->dep('datos')->tabla('macheo_categ')->get_cat_equivalente($item['cat_seha']);
                        $datos[$i]=array('col1' => $cate,'col2' => $item['cat_seha'],'col3' => date("d/m/Y",strtotime($item['desde_seha'])),'col4' => date("d/m/Y",strtotime($item['hasta_seha'])),'col5' => $item['dias_seha'],'col6' => $item['cant_seha'],'col7' => number_format($item['costo_dia_seha'],2,',','.'),'col8' => number_format($item['total_seha'],2,',','.'));  
                        $sumh=$sumh+$item['total_seha'];
                    }
                    break;
                default:
                    break;
            }
            $i++;
        }
        $pdf->ezText("\n\n", 10);
         switch ($band) {
            case 'UA':$datos1[0]=array('col1'=>'<b> SOLICITUD REFUERZO </b>');
                break;
            case 'SEAC':$datos1[0]=array('col1'=>'<b> AUTORIZADO POR SEAC </b>');
                break;
            case 'SEHA':$datos1[0]=array('col1'=>'<b> AUTORIZADO POR SEHA </b>');
                break;
            default:
                break;
        }
        
        $pdf->ezTable($datos1,array('col1'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>700,'cols'=>array('col1'=>array('justification'=>'center','width'=>700)))); 
        $pdf->ezTable($datos, $cols, '',$opc);
        //agregar el total en nueva tabla
        $datos1[0]=array('col1'=>'<b>TOTAL</b>','col2'=>number_format($sum,2,',','.'));
        switch ($band) {
            case 'UA':
                $datos1[0]=array('col1'=>'<b>TOTAL SOLICITADO</b>','col2'=>'<b>'.strval(number_format($sum,2,',','.')).'</b>');
                break;
            case 'SEAC':
                $datos1[0]=array('col1'=>'<b>TOTAL APROBADO POR SEAC</b>','col2'=>'<b>'.strval(number_format($suma,2,',','.')).'</b>');
                break;
            case 'SEHA':
                $datos1[0]=array('col1'=>'<b>TOTAL APROBADO POR SEHA</b>','col2'=>'<b>'.strval(number_format($sumh,2,',','.')).'</b>');
                break;
            default:
                break;
        }
        
        $pdf->ezTable($datos1,array('col1'=>'','col2'=>''),'',array('showHeadings'=>0,'shaded'=>0,'width'=>700,'cols'=>array('col1'=>array('justification'=>'right','width'=>560),'col2'=>array('justification'=>'right','width'=>140))));
        
        foreach ($pdf->ezPages as $pageNum=>$id){ 
            $pdf->reopenObject($id); //definimos el path a la imagen de logo de la organizacion 
            $imagen = toba::proyecto()->get_path().'/www/img/DTI_LOGO.jpg';
            $imagen2 = toba::proyecto()->get_path().'/www/img/logo_designa.jpg';
            $pdf->addJpegFromFile($imagen, 10, 525, 70, 66); 
            $pdf->addJpegFromFile($imagen2, 680, 535, 130, 40);
            $pdf->addText(500,20,8,'Generado por usuario: '.$usuario.' '.date('d/m/Y h:i:s a')); 
            $pdf->closeObject(); 
         }
     }
     }
   

}
?>