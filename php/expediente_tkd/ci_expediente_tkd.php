<?php
class ci_expediente_tkd extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__where;
        protected $s__mostrar;


	//---- Filtro -----------------------------------------------------------------------

	function conf__filtros(toba_ei_filtro $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
	}

	function evt__filtros__filtrar($datos)
	{
		$this->s__datos_filtro = $datos;
                $this->s__mostrar=1;
                $this->s__where = $this->dep('filtros')->get_sql_where();
	}

	function evt__filtros__cancelar()
	{
		unset($this->s__datos_filtro);
                $this->s__mostrar=0;
	}

	//---- Cuadro -----------------------------------------------------------------------

//	function conf__cuadro(toba_ei_cuadro $cuadro)
//	{
//		if (isset($this->s__datos_filtro)) {
//                    $cuadro->set_datos($this->dep('datos')->tabla('impresion_540')->get_listado_filtro($this->s__where));
//		} 
//	}

//	function evt__cuadro__seleccion($datos)
//	{
//		$this->dep('datos')->cargar($datos);
//	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
            if($this->s__mostrar==1){
               $this->dep('formulario')->descolapsar(); 
            }else{
               $this->dep('formulario')->colapsar(); 
            }
            if (isset($this->s__where)) {
                
                $res=$this->dep('datos')->tabla('impresion_540')->get_listado_filtro($this->s__where);
                $form->set_datos($res[0]);
                $datos=array();
                $datos['id']=$res[0]['id'];
                $this->dep('datos')->tabla('impresion_540')->cargar($datos);
                
            }
	}

	
	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('impresion_540')->set($datos);
		$this->dep('datos')->tabla('impresion_540')->sincronizar();
		$this->resetear();
	}

	
	function evt__formulario__cancelar()
	{
		$this->resetear();
                unset($this->s__where);
                $this->s__mostrar=0;
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>