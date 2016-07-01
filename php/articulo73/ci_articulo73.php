<?php
class ci_articulo73 extends toba_ci
{
        protected $s__mostrar;
        protected $s__datos_filtro;
        protected $s__nombre_archivo;
       
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
//            if($this->s__mostrar==1){//si presiono el boton alta entonces colapso el cuadro
//              $cuadro->colapsar();  
//            }else{
//                $cuadro->descolapsar();
                if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('articulo_73')->get_listado($this->s__datos_filtro));            
//                }
            }
        	
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $this->set_pantalla('pant_visualizacion');
            $this->s__mostrar=1;
	}
        //queremos previsualizar el pdf. Vamos a previsualizar en otra pantalla y no en la misma
        function evt__cuadro__pdf_acta($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $datos=$this->dep('datos')->tabla('articulo_73')->get();
            $parametros['id_designacion']=$datos['id_designacion'];
            $parametros['tipo']='acta';
            toba::vinculador()->navegar_a('designa',3742,$parametros);                       
	}
        
	function evt__cuadro__check($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $datos['check_academica']=true;
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            toba::notificacion()->agregar('Ha sido checkeado correctamente', 'info');
	}
	//-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
                if($this->s__mostrar==1){// si presiono el boton alta entonces muestra el formulario para dar de alta un nuevo registro
                    $this->dep('formulario')->descolapsar();
                    $form->ef('id_designacion')->set_obligatorio('true');
                    $form->ef('antiguedad')->set_obligatorio('true');
                    $form->ef('modo_ingreso')->set_obligatorio('true');
                    $form->ef('continuidad')->set_obligatorio('true');
                    $form->ef('modo_ingreso')->set_obligatorio('true');
                    $form->ef('nro_resolucion')->set_obligatorio('true');
                    $form->ef('cat_est_reg')->set_obligatorio('true');
                }else{
                    $this->dep('formulario')->colapsar();
                }
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
                    //$form->set_datos($datos);
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
            
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                $fp = fopen($datos['acta']['tmp_name'], 'rb');
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
               // fclose($fp); esto borra el archivo!!!!
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',null);
            }
            
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
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
            $this->s__mostrar=0;
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
                if($datos['acta']['size']>0){
                    $fp = fopen($datos['acta']['tmp_name'], 'rb');
                }else{
                    $fp=null;
                }
                
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
               // fclose($fp); esto borra el archivo!!!!
            }
            
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
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
            $this->s__mostrar=1;
            $this->set_pantalla('pant_visualizacion');
                        
	}

	function evt__volver()
	{
            $this->resetear();
            $this->s__mostrar=0;
            $this->set_pantalla('pant_inicial');
	}

}
?>