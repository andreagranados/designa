<?php
class ci_ver_rendicion_subsidios extends toba_ci
{
        protected $s__datos_filtro;
        protected $s__where;


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
                $this->s__where = $this->dep('filtros')->get_sql_where();
                //actualiza el estado de los que estan vencidos
               // $this->dep('datos')->tabla('subsidio')->actualiza_vencidos();
	}

	function evt__filtros__cancelar()
	{
		unset($this->s__datos_filtro);
                unset($this->s__where);
	}

	//---- Cuadro -----------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{
            if (isset($this->s__where)) {
                $this->s__datos=$this->dep('datos')->tabla('comprob_rendicion_subsidio')->get_listado_comprobantes($this->s__where);
                foreach ($this->s__datos as $key => $value) {
                    if($this->s__datos[$key]['archivo_comprob']<>null and $this->s__datos[$key]['archivo_comprob']<>''){//tiene valor
                        $fechaHora = idate("Y").idate("m").idate("d").idate("H").idate("i").idate("s");
                        $nomb_ft="http://copia.uncoma.edu.ar:8080/share.cgi/".$this->s__datos[$key]['archivo_comprob']."?ssid=64efc1086e32464ba39452cda68c7f73&fid=64efc1086e32464ba39452cda68c7f73&path=%2F&filename=".$this->s__datos[$key]['archivo_comprob']."&openfolder=normal&ep";
                        $nomb_ft.="?v=".$fechaHora;
                        $this->s__datos[$key]['archivo']="<a href='{$nomb_ft}' target='_blank'>archivo</a>";
                    }
                }
                $cuadro->set_datos($this->s__datos);
            } 
	}

}
?>