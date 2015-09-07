<?php
class ci_control_presupuesto extends toba_ci
{
	protected $s__datos_filtro;

        //calculo el credito asignado a la facultad que ingresa como argumento
        function credito ($ua){
             $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b where a.id_unidad=upper('".$ua."') and a.id_programa=b.id_programa" ;
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;
            
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

	function evt__filtro__cancelar()
	{
		unset($this->s__datos_filtro);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
		if (isset($this->s__datos_filtro)) {
                    //busca todas las designaciones que estan dentro del periodo vigente, que tienen numero de 540 y ademas tienen el numero de la norma legal
                    //a presupuesto no le interesa chequear nada que no tenga norma legal
                    
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_presup($this->s__datos_filtro));
		} else {
                    
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_presup());
		}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('designacion')->cargar($datos);
                $datos['check_presup']=1;
                $this->dep('datos')->tabla('designacion')->set($datos);//modifica el check de presupuesto de esa designacion
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('designacion')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('designacion')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('designacion')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__baja()
	{
		$this->dep('datos')->eliminar_todo();
		$this->resetear();
	}

	function evt__formulario__cancelar()
	{
		$this->resetear();
	}

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>