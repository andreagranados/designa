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
            
            //cuando selecciona ese usuario tiene que agregar la novedad de tipo 2 LSGH, subtipo MATE 
            //veo si la designacion seleccionada
            $sql2="select * from novedad t_n "
                        . " where t_n.tipo_nov=2 "
                        . " and t_n.nro_tab10=10 "
                        . " and t_n.sub_tipo='MATE' "
                        . " and t_n.id_designacion=".$datos['id_designacion']
                        ." and t_n.desde='".$datos['desde']."'";
            $res=toba::db('designa')->consultar($sql2);

            if (count($res)==0){//si la designacion no tiene la licencia cargada
                    $sql3="insert into novedad (tipo_nov, desde, hasta, id_designacion, tipo_norma, 
                    tipo_emite, norma_legal, observaciones, nro_tab10, sub_tipo) values(2,'".$datos['desde']."','".$datos['hasta']."',".$datos['id_designacion'].",'NOTA','DECA','MATE','maternidad',10,'MATE')";
                    toba::db('designa')->consultar($sql3);
                    toba::notificacion()->agregar('La licencia se ha importado exitosamente.','info');
                    $sql4="update designacion set nro_540=null,check_presup=0 where id_designacion=".$datos['id_designacion'];
                    toba::db('designa')->consultar($sql4);
            }else{
                toba::notificacion()->agregar(utf8_decode('La designación ya tiene asociada esta licencia'),'info');
                
            }
        }

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>