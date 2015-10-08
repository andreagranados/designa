<?php
class ci_conjuntos extends toba_ci
{
	protected $s__datos_filtro;
             

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
                    $conj=$this->dep('datos')->tabla('conjunto')->get();
                    $res=$this->dep('datos')->tabla('en_conjunto')->materias($conj['id_conjunto']);
                    
                    //$seleccionadas=array(1,5,8);
                    $seleccionadas=array();
                    foreach ($res as $value) {
                        $seleccionadas []= $value['id_materia'];
                    }
                   
                    $conj['id_materia']=$seleccionadas;
                   
                    return $conj;
                   
		}
	}

	
        function evt__formulario__guardar($datos)
        {
            $conj=$this->dep('datos')->tabla('conjunto')->get();
            $this->dep('datos')->tabla('en_conjunto')->borrar_materias($conj['id_conjunto']);
            $x=$datos['id_materia'];
            foreach ($x as $key=>$value) {//para cada materia
                $asig['id_conjunto']=$conj['id_conjunto'];
                $asig['id_materia']=$value;
                 //Sincroniza los cambios del datos_rela cion con la base
                $this->dep('datos')->tabla('en_conjunto')->set($asig);
                $this->dep('datos')->tabla('en_conjunto')->sincronizar();
                $this->dep('datos')->tabla('en_conjunto')->resetear();//Descarta los cambios en el datos_relacion
                
            }
           
        }
	

	

}
?>