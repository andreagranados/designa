<?php
class ci_de_datos_personales_mapuche extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__datos;
        protected $s__masfiltros;
        
        //-----------------------------------------------------------------------------------
	//---- formulario -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------
	function conf__masfiltros(toba_ei_formulario $form)
	{
            $form->colapsar();
            $this->s__masfiltros['dni']=1;//siempre tildado dni
            $form->set_datos($this->s__masfiltros);    

	}
        function evt__masfiltros__modificacion($datos)
        {
            $this->s__masfiltros = $datos;
        }
       //---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}
        
        function evt__filtro__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
	}
	

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__datos_filtro)) {
                $dia_actual=date(j);//dia del mes sin ceros iniciales
                if($dia_actual>20){
                    toba::notificacion()->agregar(utf8_decode('Mapuche esta en proceso de liquidación. Realice esta operación del 1 al 20 de cada mes.'), 'info');
                }else{
                    if ($this->s__datos_filtro['legajo']) {//con legajo 
                        $this->s__datos=$this->dep('datos')->tabla('docente')->get_listado_con_legajo($this->s__datos_filtro,$this->s__masfiltros);    
                    }else{//sin legajo
                        $this->s__datos=$this->dep('datos')->tabla('docente')->get_listado_sin_legajo($this->s__datos_filtro);    
                    }
                    $cuadro->set_datos($this->s__datos);
                }    
            } 
	}

	function evt__cuadro__seleccion($datos)
	{
            //print_r($datos);exit();( [id_docente] => 3338 [nro_legaj] => 59381 [desc_appat] => OSSES [desc_nombr] => MARIA LAURA [nro_cuil3] => 27 [nro_cuil4] => 23942461 [nro_cuil5] => 4 [nacim] => 1974-11-18 ) 
            //print_r($this->s__datos);exit();
                $d=array();
                $d['id_docente']=$datos['id_docente'];
                $valores=array();
                $valores['legajo']=$datos['nro_legaj'];
                $valores['apellido']=$datos['desc_appat'];
                $valores['nombre']=$datos['desc_nombr'];
                $valores['nro_cuil1']=$datos['nro_cuil3'];
                $valores['nro_cuil']=$datos['nro_cuil4'];
                $valores['nro_cuil2']=$datos['nro_cuil5'];
                $valores['fec_nacim']=$datos['nacim'];
                $valores['tipo_docum']=$datos['tipo_doc'];
                $valores['nro_docum']=$datos['nro_cuil4'];
                $valores['tipo_sexo']=$datos['sexo'];
                $valores['fec_ingreso']=$datos['fec_ingreso'];
                $valores['telefono']=$datos['telefono'];
                $valores['telefono_celular']=$datos['telefono_celular'];
                $valores['correo_institucional']=$datos['correo_electronico'];
                $this->dep('datos')->tabla('docente')->cargar($d);//carga el docente seleccionado
                $this->dep('datos')->tabla('docente')->set($valores);
                $this->dep('datos')->tabla('docente')->sincronizar();
            
	}

	
}

?>