<?php
class ci_articulo73 extends toba_ci
{
        protected $s__mostrar;
        protected $s__datos_filtro;
        
       
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
                    //print_r($fp_imagen);//Resource id #115
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
                        //$datos['acta'] = 'Tamaño: '.$tamaño. ' KB';
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
            $this->dep('datos')->tabla('articulo_73')->set($datos);
            if (is_array($datos['acta'])) {//si adjunto un pdf entonces "pdf" viene con los datos del archivo adjuntado
                //$s__temp_archivo = $datos['acta']['tmp_name'];//C:\Windows\Temp\php9A45.tmp
                 // Almacena un 'file pointer' en un campo binario o blob de la tabla.
                //print_r('paso');print_r($datos['acta']);//Array ( [name] => TC051168.pdf [type] => application/pdf [tmp_name] => C:\Windows\Temp\phpE148.tmp [error] => 0 [size] => 656209 )
                $fp = fopen($datos['acta']['tmp_name'], 'rb');
                $this->dep('datos')->tabla('articulo_73')->set_blob('acta',$fp);
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