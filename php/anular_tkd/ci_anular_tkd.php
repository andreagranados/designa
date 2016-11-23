<?php
class ci_anular_tkd extends toba_ci
{
        protected $s__where;
        protected $s__datos_filtro;
	protected $s__datos;
	//-----------------------------------------------------------------------------------
	//---- filtros ----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

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
	}

	function evt__filtros__cancelar()
	{
            unset($this->s__where);
            unset($this->s__datos_filtro);
	}

	//-----------------------------------------------------------------------------------
	//---- cuadro -----------------------------------------------------------------------
	//-----------------------------------------------------------------------------------

	function conf__cuadro(toba_ei_cuadro $cuadro)
	{   
            if (isset($this->s__where)) {
                $this->s__datos=$this->dep('datos')->tabla('designacion')->get_designaciones($this->s__where);
                $cuadro->set_datos($this->s__datos);
	     }else{
                $cuadro->evento('anular')->ocultar();
             }
	}

	function evt__cuadro__anular($datos)
	{
             //print_r($this->s__datos_filtro);//uni_acad = 'FAIF' AND	nro_540 = '481'3,1
            $sele=array();
            foreach ($this->s__datos as $key => $value) {
                    $sele[]=$value['id_designacion']; 
             }
            $comma_separated = implode(',', $sele);
            
            $sql="update impresion_540 set estado='A' where id=".$this->s__datos_filtro['nro_540']['valor'];
            toba::db('designa')->consultar($sql);
            $sql="insert into designacionh select * from designacion where id_designacion in (".$comma_separated .") ";
            toba::db('designa')->consultar($sql);
            $sql="update designacion set nro_540=null where id_designacion in (".$comma_separated .") ";
            toba::db('designa')->consultar($sql);
            
	}

}
?>