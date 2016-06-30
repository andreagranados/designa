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
        	if (isset($this->s__datos_filtro)) {
                    $cuadro->set_datos($this->dep('datos')->tabla('articulo_73')->get_listado($this->s__datos_filtro));            
                 } 
	}

	function evt__cuadro__seleccion($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $this->s__mostrar=1;
	}
        //queremos previsualizar el pdf. Vamos a previsualizar en otra pantalla y no en la misma
        function evt__cuadro__pdf_acta($datos)
	{
            $this->dep('datos')->tabla('articulo_73')->cargar($datos);
            $pdf = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
            if (isset($pdf)) {
                $temp_nombre = $datos['id_designacion'].'acta'.'.pdf';
                $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                $pdf_temp = manipulacion_pdf::crear_archivo_temporal_pdf($this->s__temp_archivo_pdf, $pdf);
//			$this->s__nombre_archivo = $datos['acta']['name'];
//			$img = toba::proyecto()->get_www_temp($this->s__nombre_archivo);
//			// Mover los archivos subidos al servidor del directorio temporal PHP a uno propio.
//			move_uploaded_file($datos['archivo']['tmp_name'], $img['path']);
		}
         //       $this->set_pantalla('pant_visualizacion');
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
                }else{
                    $this->dep('formulario')->colapsar();
                }
                if ($this->dep('datos')->tabla('articulo_73')->esta_cargada()) {
                    $datos=$this->dep('datos')->tabla('articulo_73')->get();
                    //$fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta',$datos['x_dbr_clave']);
                    $fp_imagen = $this->dep('datos')->tabla('articulo_73')->get_blob('acta');
                    
                    if (isset($fp_imagen)) {
                        $temp_nombre = $datos['id_designacion'].'acta'.'.pdf';
                        $temp_archivo = toba::proyecto()->get_www_temp($temp_nombre);      
                        //print_r($temp_archivo['path']);
                         //-- Se pasa el contenido al archivo temporal
                        $temp_fp = fopen($temp_archivo['path'], 'w');
                        stream_copy_to_stream($fp_imagen, $temp_fp);
                        fclose($temp_fp);
                         //-- Se muestra la imagen temporal
                        $tamaño = round(filesize($temp_archivo['path']) / 1024);
                        $datos['acta'] = "<a href='{$temp_archivo['url']}' >acta</a>";
                                                
                      } else {
                        $datos['acta'] = null;  
                    }
                    $form->set_datos($datos);
                  
		}
	}

	function evt__formulario__alta($datos)
	{
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
                fclose($fp);
            }else{
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',null);
            }
            
            $this->dep('datos')->tabla('articulo_73')->sincronizar();
            //$this->resetear();
	}

	function evt__formulario__baja()
	{
            $this->dep('datos')->tabla('articulo_73')->eliminar_todo();
            $this->resetear();
            $this->s__mostrar=0;
	}

//	function evt__formulario__modificacion($datos)
//	{
//            $auxi=$datos['cat_est_reg'];
//            $datos['cat_est_reg']=substr($auxi, 0,strlen($auxi)-1 );
//            $datos['dedic_reg']=substr($auxi, strlen($auxi)-1,strlen($auxi) );
//            $this->dep('datos')->tabla('articulo_73')->set($datos);
//            //carga archivo
//            // Cargar archivo
//            if (is_array($datos['acta'])) {
//                //Se subio una imagen
//                $fp = fopen($datos['acta']['tmp_name'], 'rb');
//                $this->dep('datos')->tabla('articulo_73')->set_blob('acta', $fp);
//            }
//            $this->dep('datos')->tabla('articulo_73')->sincronizar();
//	}

	function evt__formulario__cancelar()
	{
            $this->resetear();
            $this->s__mostrar=0;
	}
        function resetear()
	{
            $this->dep('datos')->resetear();
	}

	function evt__agregar()
	{
            $this->resetear();
            $this->s__mostrar=1;
                        
	}

	

}
?>