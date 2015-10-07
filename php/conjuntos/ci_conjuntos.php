<?php
class ci_conjuntos extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__datos_filtro_2;
       

        function get_materias(){
            $sql="select id_materia,cod_carrera||'-'||desc_materia||'('||cod_siu||')' as descripcion from materia t_m, plan_estudio t_p, unidad_acad t_u"
                    . " where t_m.id_plan=t_p.id_plan "
                    . " and t_p.uni_acad=t_u.sigla";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $sql=$sql." order by descripcion";
            return toba::db('designa')->consultar($sql);
        }
	//---- Filtro -----------------------------------------------------------------------

	function conf__filtro(toba_ei_formulario $filtro)
	{
		if (isset($this->s__datos_filtro)) {
			$filtro->set_datos($this->s__datos_filtro);
		}
                $this->pantalla()->tab("pant_conjunto")->desactivar();
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
			$cuadro->set_datos($this->dep('datos')->tabla('conjunto')->get_listado($this->s__datos_filtro));
		} 
	}

	function evt__cuadro__seleccion($datos)
	{
		$this->dep('datos')->tabla('conjunto')->cargar($datos);
                $this->set_pantalla('pant_conjunto');
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->tabla('conjunto')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('conjunto')->get());
		}
	}

	

	

	function resetear()
	{
		$this->dep('datos')->resetear();
	}

	//-----------------------------------------------------------------------------------
	//---- filtro_mat -------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__filtro_mat(toba_ei_filtro $filtro)
	{
            if (isset($this->s__datos_filtro_2)) {
               	$filtro->set_datos($this->s__datos_filtro_2);
	     }
             //Retorna el valor o estado actual del ef         
            $datos=$filtro->columna('id_materia')->get_estado();//Array ( [condicion] => en_conjunto [valor] => Array ( [0] => 15 [1] => 1 ) ) 
            print_r($datos['valor']);
            $filtro->columna('id_materia')->pasar_a_derecha();
            
           
	}
       
//asocia las materias al grupo seleccionado previamente
	function evt__filtro_mat__guardar($datos)
	{
           // print_r($datos['id_materia']['valor']);//Array ( [id_materia] => Array ( [condicion] => en_conjunto [valor] => Array ( [0] => 1600 [1] => 1660 ) ) ) 
            $this->s__datos_filtro_2 = $datos;
            $conj=$this->dep('datos')->tabla('conjunto')->get();
            foreach ($datos['id_materia']['valor'] as $key=>$value) {//para cada materia
                $asig['id_conjunto']=$conj['id_conjunto'];
                $asig['id_materia']=$value;
                $this->dep('datos')->tabla('en_conjunto')->set($asig);
                $this->dep('datos')->tabla('en_conjunto')->sincronizar();
                
            }
            
	}

}
?>