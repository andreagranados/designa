<?php
class ci_de_datos_personales_mapuche extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__datos;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtros)
	{
		if (isset($this->s__datos_filtro)) {
			$filtros->set_datos($this->s__datos_filtro);
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
                unset($this->s__datos);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__where)) {
                    $this->s__datos=$this->dep('datos')->tabla('docente')->get_listado_sin_legajo($this->s__where);
                    
                    $cuadro->set_datos($this->s__datos);
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
            //print_r($datos);exit();// ( [id_docente] => 3338 [nro_legaj] => 59381 [desc_appat] => OSSES [desc_nombr] => MARIA LAURA [nro_cuil3] => 27 [nro_cuil4] => 23942461 [nro_cuil5] => 4 [nacim] => 1974-11-18 ) 
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
                $this->dep('datos')->tabla('docente')->cargar($d);//carga el docente seleccionado
                $this->dep('datos')->tabla('docente')->set($valores);
                $this->dep('datos')->tabla('docente')->sincronizar();
            
	}

	
}

?>