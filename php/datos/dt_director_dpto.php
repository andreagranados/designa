<?php
class dt_director_dpto extends toba_datos_tabla
{
    function get_descripciones($filtro=array())
    {
        $where="";
        if(isset($filtro['iddepto'])){
            $where=" WHERE iddepto=".$filtro['iddepto'];
        }
	$sql = "SELECT doc.id_docente,trim(doc.apellido)||','||trim(doc.nombre) as agente,doc.legajo,di.desde,di.hasta,di.iddepto,di.resol"
                . " FROM director_dpto di"
                . " LEFT OUTER JOIN docente doc ON (di.id_docente=doc.id_docente)"
                . " $where ORDER BY desde";
	return toba::db('designa')->consultar($sql);
    }
}
?>