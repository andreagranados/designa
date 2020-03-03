<?php
class ci_participacion_investigacion_extension extends toba_ci
{	
	function ini()
	{
            //$sql="select apellido,nro_docum from persona";
            $sql="select * from (select 'I' as tipo,i.denominacion,i.nro_resol as norma,doc.nro_docum,f.descripcion as funcion,pi.carga_horaria,pi.desde,pi.hasta "
                    . " from integrante_interno_pi pi "
                    . " inner join pinvestigacion i on ( pi.pinvest=i.id_pinv ) "
                    . " inner join designacion d on (d.id_designacion=pi.id_designacion)  "
                    . " inner join docente doc on (d.id_docente=doc.id_docente)"
                    . " inner join funcion_investigador f on (f.id_funcion=pi.funcion_p)"
                    . " UNION "
                    ."select 'I' as tipo,i.denominacion,i.nro_resol as norma,p.nro_docum,f.descripcion as funcion,pi.carga_horaria,pi.desde,pi.hasta "
                    . " from integrante_externo_pi pi "
                    . " inner join pinvestigacion i on ( pi.pinvest=i.id_pinv ) "
                    . " inner join persona p on (p.tipo_docum=pi.tipo_docum and p.nro_docum=pi.nro_docum)  "
                    . " inner join funcion_investigador f on (f.id_funcion=pi.funcion_p)"
                    ." )sub"
                    . " order by nro_docum, desde" 
                    ;
        
            $res=toba::db('designa')->consultar($sql);
            $nuevo=array();
            foreach ($res as $key => $value) {
                $res[$key]['denominacion']=  utf8_encode($res[$key]['denominacion']);
                $res[$key]['funcion']=  utf8_encode($res[$key]['funcion']);
            }
            
            echo json_encode($res);
            exit;
            
	}

}
?>