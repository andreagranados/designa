<?php
class ci_articulo73 extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__nombre_archivo;
        protected $s__pdf;
        protected $tamano_byte=6292456;
        protected $tamano_mega=6;



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
	}
        function evt__filtro__cancelar($datos)
	{
            unset($this->s__datos_filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('articulo_73')->get_listado($this->s__datos_filtro));            
                    }
//            $ar['id_designacion']=3347;
//            $this->dep('cuadro')->seleccionar($ar);
            
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $this->set_pantalla('pant_visualizacion');
        }
        function evt__cuadro__ver_acta($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->resetear();//limpia
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $desig=$this->dep('datos')->tabla('articulo_73')->get();
            //----
            $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
            if (isset($fp_imagen)) {//tiene un adjunto
                $this->dep('cuadro')->evento('pdf_acta')->set_nivel_de_fila( 1 );//permite que el boton pdf_acta aparezca
                $this->dep('cuadro')->evento('pdf_acta')->set_etiqueta ('Acta'.$desig['id_designacion']);
                $this->s__pdf='acta';
            }else{//no tiene adjunto
                toba::notificacion()->agregar('no tiene adjunto', "info");
            }
            
            //---
            
            //$this->dep('cuadro')->evento('pdf_acta')->ocultar();
            //$ar['id_designacion']=3347;
           //$this->dep('cuadro')->evento('pdf_acta')->set_parametros($ar)      ;
          
        }

        function evt__cuadro__ver_resol($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->resetear();//limpia
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $desig=$this->dep('datos')->tabla('articulo_73')->get();
            $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
            if (isset($fp_imagen)) {//tiene un adjunto
                $this->dep('cuadro')->evento('pdf_resol')->set_nivel_de_fila( 1 );//1 o 0
                $this->dep('cuadro')->evento('pdf_resol')->set_etiqueta ('Resol'.$desig['id_designacion']);
                $this->s__pdf='resol';    
            }else{//no tiene adjunto
                toba::notificacion()->agregar('no tiene adjunto', "info");
            }
            
        }
        
        function vista_pdf(toba_vista_pdf $salida){
                       
//            $vinculo_cuadro = $this->dep('cuadro')->evento('pdf_acta')->vinculo();
//            print_r($vinculo_cuadro);exit();
           //echo($this->dep('cuadro')->evento('pdf_acta')->esta_sobre_fila());exit();
           if ($this->dep('datos')->tabla('articulo_73')->esta_cargada()){
               $artic = $this->dep('datos')->tabla('articulo_73')->get();
               if($this->s__pdf=='acta'){
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        header("Content-Disposition:attachment;filename='acta.pdf'");
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    }
               }else{
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('resolucion');
                    if (isset($fp_imagen)) {
                        header("Content-type:applicattion/pdf");
                        header("Content-Disposition:attachment;filename='resolucion.pdf'");
                        echo(stream_get_contents($fp_imagen)) ;exit;
                    }
               }
                
             }
                     
        }
       
	function evt__cuadro__check($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $art=$this->dep('datos')->tabla('articulo_73')->get();
            
            if($art['check_academica']==1){
                $datos['check_academica']=false;
                $mensaje="Ha sido deschequeado correctamente";
            }else{
                $datos['check_academica']=true;
                $mensaje="Ha sido chequeado correctamente";
            }
            
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            toba::notificacion()->agregar($mensaje, 'info');
	}
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
                if ($this->dep('datos')->tabla('articulo_73')->esta_cargada()) {
                    $datos=$this->dep('datos')->tabla('articulo_73')->get();
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    
                    if (isset($fp_imagen)) {
                        $temp_nombre = md5(uniqid(time())).'.pdf';
                        $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                        //print_r($temp_archivo['path']);
                         //-- Se pasa el contenido al archivo temporal
                        $temp_fp = fopen($temp_archivo['path'], 'w');
                        stream_copy_to_stream($fp_imagen, $temp_fp);
                        fclose($temp_fp);
                         //-- Se muestra la imagen temporal
                        $tamano = round(filesize($temp_archivo['path']) / 1024);
                        //$datos['imagen_vista_previa'] = "<a href='{$temp_archivo['url']}' >acta</a>";
                        //print_r($temp_archivo['url']);/designa/1.0/temp/3334acta.pdf
                        //definimos el path a la imagen de logo de la organizacion 
                        //$ruta='/designa/1.0/temp/adjunto.jpg';
                        //$datos['imagen_vista_previa'] = "<img src='{$ruta}' alt=''>";
			$datos['imagen_vista_previa'] = "<a target='_blank' href='{$temp_archivo['url']}' >acta</a>";
		  	$datos['acta'] = 'tamano: '.$tamano. ' KB';
                                                
                      } else {
                        $datos['acta'] = null;  
                        }
                    $fp_imagenr = $this->dep('datos')->tabla('articulo_73')->get_blob('resolucion');
                    if (isset($fp_imagenr)) {
                        $temp_nombre = md5(uniqid(time())).'.pdf';
                        $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                         //-- Se pasa el contenido al archivo temporal
                        $temp_fp = fopen($temp_archivo['path'], 'w');
                        stream_copy_to_stream($fp_imagenr, $temp_fp);
                        fclose($temp_fp);
                         //-- Se muestra la imagen temporal
                        $tamano = round(filesize($temp_archivo['path']) / 1024);
                        $datos['imagen_vista_previar'] = "<a target='_blank' href='{$temp_archivo['url']}' >resolucion</a>";
		  	$datos['resolucion'] = 'tamano: '.$tamano. ' KB';                         
                      } else{
                         $datos['resolucion'] = null;   
                      }
                    //$datos['cat_est_reg']='ASD3';
                    $auxi=trim($datos['cat_est_reg']).$datos['dedic_reg'];
                    $datos['cat_est_reg']=$auxi;
                    return $datos;
                  
		}
	}

	function evt__formulario__alta($datos)
	{
         $tiene=$this->dep('datos')->tabla('designacion')->tiene_dao($datos['id_designacion']);
         if($tiene==1){
            $auxi=$datos['cat_est_reg'];
            $datos['cat_est_reg']=substr($auxi, 0,strlen($auxi)-1 );
            $datos['dedic_reg']=substr($auxi, strlen($auxi)-1,strlen($auxi) );
            $datos['nro_tab12']=12;
            $datos['nro_tab11']=11;
            $datos['check_academica']=false;
            $datos['pase_superior']=false;
            $dao=$this->dep('datos')->tabla('designacion')->get_dao($datos['id_designacion']);
            if(count($dao)>0){//guardo departamento, area y orientacion de la designacion previamente seleccionada
                $datos['id_departamento']=$dao[0]['id_departamento'];    
                $datos['id_area']=$dao[0]['id_area'];    
                $datos['id_orientacion']=$dao[0]['id_orientacion'];    
            }
                       
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            //-----------acta-----------------------
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                if($datos['acta']['size']>$this->tamano_byte){
                    toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');
                    $fp=null;
                }else{
                    $fp = fopen($datos['acta']['tmp_name'], 'rb');
                    $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
                }
              
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',null);
            }
            //-----------resolucion-----------------------
            if (is_array($datos['resolucion'])) {
                 if($datos['resolucion']['size']>$this->tamano_byte){
                    toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');
                    $fp=null;
                }else{
                    $fp = fopen($datos['resolucion']['tmp_name'], 'rb');
                    $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',$fp);
                }
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',null);
            }
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
         }else{
             toba::notificacion()->agregar('La designacion debe tener departamento, area y orientacion', 'error');
         } 
	}

	function evt__formulario__baja()
	{
            $this->dep('datos')->tabla('articulo_73')->eliminar_todo();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
        }

	function evt__formulario__modificacion($datos)
	{
         $tiene=$this->dep('datos')->tabla('designacion')->tiene_dao($datos['id_designacion']);
         if($tiene==1){
            $auxi=$datos['cat_est_reg'];
            $datos['cat_est_reg']=substr($auxi, 0,strlen($auxi)-1 );
            $datos['dedic_reg']=substr($auxi, strlen($auxi)-1,strlen($auxi) );
            $datos['nro_tab12']=12;
            $datos['nro_tab11']=11;
            $datos['check_academica']=false;
            $datos['pase_superior']=false;
            
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                if($datos['acta']['size']>0 ){
                    if($datos['acta']['size']>$this->tamano_byte ){
                        toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');  
                        $fp=null;
                    }
                    else{$fp = fopen($datos['acta']['tmp_name'], 'rb');}
                }else{
                    $fp=null;
                }
                
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
               // fclose($fp); esto borra el archivo!!!!
            }
            if (is_array($datos['resolucion'])) {
                if($datos['resolucion']['size']>0){
                    if($datos['resolucion']['size']>$this->tamano_byte ){
                        toba::notificacion()->agregar('El tamaño del archivo debe ser menor a '.$this->tamano_mega.'MB', 'error');  
                        $fp=null;
                    }
                    else{
                        $fp = fopen($datos['resolucion']['tmp_name'], 'rb');
                    }
                }else{
                    $fp=null;
                }
                $this->dep('datos')->tabla('articulo_73')->set_blob('resolucion',$fp);
            }
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            $this->resetear();
            $this->set_pantalla('pant_inicial');
         }else{
             toba::notificacion()->agregar('La designacion debe tener departamento, area y orientacion', 'error');
         }
	}


        function resetear()
	{
            $this->dep('datos')->resetear();
	}

	function evt__agregar()
	{
            $this->resetear();
            $this->set_pantalla('pant_visualizacion');
                        
	}

	function evt__volver()
	{
            $this->resetear();
            $this->set_pantalla('pant_inicial');
	}
//        function conf__pant_inicial()
//        {
//            echo "<tr height='20'>".
//		"<td align='left' valign='botton' colspan='3'>".
//				"<table>".
//				"<tr>".
//					"<td></td>".
//					"<td lign='right'>";
//                                                $ruta='AP_Articulo73.pdf';
//						echo "<a target='_blank' href='$ruta' title='Descargar Acta Paritaria - Articulo 73'>".toba_recurso::imagen_proyecto('Down.png', true)."</a>";
//						
//				echo "</td>".
//					"<td style='font-size:20px;'>Presionando el &iacute;cono se obtendr&aacute el Acta de Paritaria - Art&iacuteculo 73. </td>".
//				"</tr>".
//			"</table>".
//		"</td>".
//	"</tr>";
//        }
	
	

}
?>