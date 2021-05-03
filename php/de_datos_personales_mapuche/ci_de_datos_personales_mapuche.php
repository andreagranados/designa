<?php
class ci_de_datos_personales_mapuche extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__datos;

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
                if ($this->s__datos_filtro['legajo']) {//con legajo 
                    $this->s__datos=$this->dep('datos')->tabla('docente')->get_listado_con_legajo($this->s__datos_filtro);    
                }else{//sin legajo
                    $this->s__datos=$this->dep('datos')->tabla('docente')->get_listado_sin_legajo($this->s__datos_filtro);    
                }
                $cuadro->set_datos($this->s__datos);
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