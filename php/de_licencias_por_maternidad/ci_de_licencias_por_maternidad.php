<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
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
	{//cuando selecciona ese usuario tiene que agregar la novedad de tipo 2 LSGH, subtipo MATE 
            $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($this->s__anio);
            $sql='select * from designacion where id_designacion='.$datos['id_designacion'];
            $des=toba::db('designa')->consultar($sql);
            $seguir=true;
            if(isset($des[0]['hasta'])){
               if($datos['desde']>$des[0]['hasta']){
                   $seguir=false;
               }
            }else{
                if($datos['desde']>$udia){
                   $seguir=false;
               }
            }
            if($seguir){//el periodo de la lic esta dentro del periodo de la designacion
                if($datos['desde']<$des[0]['desde']){
                    $f_desde=$des[0]['desde'];
                }else{
                    $f_desde=$datos['desde'];
                }
                if(isset($des[0]['hasta'])){
                    if($datos['hasta']>$des[0]['hasta']){
                        $f_hasta=$des[0]['hasta'];
                    }else{
                        $f_hasta=$datos['hasta'];
                    }
                }else{
                    if($datos['hasta']>$udia){
                        $f_hasta=$udia;
                    }else{
                        $f_hasta=$datos['hasta'];
                    }
                }

                //veo si la designacion seleccionada
                $band=$this->dep('datos')->tabla('novedad')->tiene_lic_mate($f_desde,$datos['id_designacion']);
                if (!$band){//si la designacion no tiene la licencia cargada
                    $this->dep('datos')->tabla('novedad')->cargar_lic_mate($f_desde,$f_hasta,$datos['id_designacion']);
                    toba::notificacion()->agregar('La licencia se ha importado exitosamente.','info');
                }else{
                    toba::notificacion()->agregar(utf8_decode('La designación ya tiene asociada esta licencia'),'info');
                }
            }    
        }

	
	function resetear()
	{
		$this->dep('datos')->resetear();
	}

}

?>