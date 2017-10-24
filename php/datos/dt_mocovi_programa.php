<?php
class dt_mocovi_programa extends toba_datos_tabla
{
        function get_imputacion($id_prog){
            $sql=//" select '<b>'||nombre||'</b>'||' Programa: '||trim(to_char(programa ,'00'))||' SubPrograma: '||trim(to_char(sub_programa ,'00'))||' Actividad: '||trim(to_char(actividad ,'00'))||' Dependencia: '||trim(to_char(area ,'000'))||' SubDependencia: '||trim(to_char(sub_area ,'000'))||' SubSubDependencia:'||trim(to_char(sub_sub_area ,'000'))||' Fuente: '||trim(cast(fuente as text)) as impu"
                    "select '<b>'||nombre||'</b>'||' Programa: '||case when programa is not null then trim(to_char(programa ,'00'))else '' end ||' SubPrograma: '||case when sub_programa is not null then trim(to_char(sub_programa ,'00')) else '' end ||' Actividad: '||case when actividad is not null then trim(to_char(actividad ,'00')) else '' end ||' Dependencia: '||trim(to_char(area ,'000'))||' SubDependencia: '||trim(to_char(sub_area ,'000'))||' SubSubDependencia:'||trim(to_char(sub_sub_area ,'000'))||' Fuente: '||trim(cast(fuente as text)) as impu"
                    . " from mocovi_programa"
                    . " where id_programa=$id_prog";
            $res = toba::db('designa')->consultar($sql);
           
            if(count($res)>0){
                return $res[0]['impu'];
            }
        }
        function get_descripciones($ua=null)
	{
            $where="";
            if(isset($ua)){
                $where=" where id_unidad='".$ua."'";
            }	
            $sql = "SELECT distinct id_programa, nombre FROM mocovi_programa $where ORDER BY nombre";
            
            return toba::db('designa')->consultar($sql);
	}
        function programas_ua($id_ua=null)
        {
            $where ="";         
            if(isset($id_ua)){
                    $where=" and id_unidad='".$id_ua."'";        
                }
            $sql="select distinct t_p.id_programa,t_p.nombre as programa_nombre "
                    . " from mocovi_programa t_p, unidad_acad t_u "
                    . " where t_p.id_unidad=t_u.sigla $where";
            $sql = toba::perfil_de_datos()->filtrar($sql);
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