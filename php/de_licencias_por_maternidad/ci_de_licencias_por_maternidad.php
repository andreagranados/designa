<?php
class ci_de_licencias_por_maternidad extends toba_ci
{
	protected $s__datos_filtro;
        protected $s__anio;
        
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
                    $this->s__anio=$this->s__datos_filtro['anio'];
                    $cuadro->set_datos($this->dep('datos')->tabla('designacion')->get_lic_maternidad($this->s__datos_filtro));
		}
	}

	function evt__cuadro__seleccion($datos)
	{
            
            //cuando selecciona ese usuario tiene que agregar la novedad de tipo 2 LSGH, subtipo MATE para cada designacion
            //$this->dep('datos')->tabla('docente')->cargar($datos);
            $sql="select * from designacion t_d, docente t_do, mocovi_periodo_presupuestario t_p"
                    . "  where t_d.id_docente=t_do.id_docente"
                    . " and t_do.legajo=".$datos['legajo']
                    . " and t_p.anio=".$this->s__anio
                    ." and t_d.desde<=t_p.fecha_fin and (t_d.hasta>=t_p.fecha_inicio or t_d.hasta is null)";
            $res=toba::db('designa')->consultar($sql);
            foreach ($res as $value) {//para cada designacion del legajo seleccionado
                $sql2="select * from novedad t_n "
                        . " where t_n.tipo_nov=2 "
                        . " and t_n.nro_tab10=10 "
                        . " and t_n.sub_tipo='MATE' "
                        . " and t_n.id_designacion=".$value['id_designacion']
                        ." and t_n.desde='".$value['desde']."'";
                $res=toba::db('designa')->consultar($sql2);
                print_r($res);
                if (count($res)==0){//si la designacion no tiene la licencia cargada
                    $sql3="insert into novedad (tipo_nov, desde, hasta, id_designacion, tipo_norma, 
                    tipo_emite, norma_legal, observaciones, nro_tab10, sub_tipo) value(2,'".$datos['desde']."','".$datos['hasta']."',".$value['id_designacion'].",'NOTA',DECA','maternidad',null,10,'MATE')";
                    print_r($sql3);exit();
                    toba::db('designa')->consultar($sql3);
                }
            }
            print_r($res);
	}

	//---- Formulario -------------------------------------------------------------------

	function conf__formulario(toba_ei_formulario $form)
	{
		if ($this->dep('datos')->esta_cargada()) {
			$form->set_datos($this->dep('datos')->tabla('novedad')->get());
		}
	}

	function evt__formulario__alta($datos)
	{
		$this->dep('datos')->tabla('novedad')->set($datos);
		$this->dep('datos')->sincronizar();
		$this->resetear();
	}

	function evt__formulario__modificacion($datos)
	{
		$this->dep('datos')->tabla('novedad')->set($datos);
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