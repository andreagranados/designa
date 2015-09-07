<?php
class ci_informe_estado_actual extends toba_ci
{
	protected $s__datos_filtro;

//en el combo solo aparece la facultad correspondiente al usuario logueado
        function get_ua(){
             $usuario = toba::usuario()->get_id();
             $sql="select * from unidad_acad where sigla=upper('".$usuario."')";
             $resul=toba::db('designa')->consultar($sql);
             for ($i = 0; $i <= count($resul) - 1; $i++) {
                    $resul[$i]['descripcion'] = utf8_decode($resul[$i]['descripcion']);
                                   
                }
             return $resul;
        }
        function credito ($ua){
             $sql="select sum(b.credito) as cred from mocovi_programa a, mocovi_credito b where a.id_unidad=upper('".$ua."') and a.id_programa=b.id_programa" ;
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;
             print_r('tengo');
            
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
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_estactual($this->s__datos_filtro));
		} else {
			$cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_listado_estactual());
		}
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->cargar($datos);
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