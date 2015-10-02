<?php
class dt_mocovi_programa extends toba_datos_tabla
{
	function get_descripciones($id_ua=null)
	{
            $where="";
            if(isset($id_ua)){
                $where=" where id_unidad='".$id_ua."'";
            }	
            $sql = "SELECT id_programa, nombre FROM mocovi_programa $where ORDER BY nombre";
           
            return toba::db('designa')->consultar($sql);
	}
        //trae el programa por defecto de la UA correspondiente
        function programa_defecto()
        {                 
            $sql="select m_p.id_programa from mocovi_programa m_p ,mocovi_tipo_programa m_t, unidad_acad t_u where m_p.id_tipo_programa=m_t.id_tipo_programa and m_t.id_tipo_programa=1 and m_p.id_unidad=t_u.sigla";
            $sql = toba::perfil_de_datos()->filtrar($sql);
            $resul = toba::db('designa')->consultar($sql);
            return $resul[0]['id_programa'];
                   
        }

}

?>