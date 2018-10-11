<?php
class dt_mocovi_credito extends toba_datos_tabla
{
	function get_descripciones()
	{    
            $sql="select * from mocovi_credito ";
            return toba::db('designa')->consultar($sql);
	}
       function get_descripciones_credito()
	{    
            $sql="select c.tipo||a.id_credito||':'||b.nombre||' Desc:'||a.descripcion||'('||'RECIBIDO' ||')' as descripcion
            from mocovi_credito a, mocovi_programa b,mocovi_tipo_credito c, unidad_acad d, mocovi_periodo_presupuestario e
            where a.id_periodo=e.id_periodo
            and e.actual
            and a.id_unidad=d.sigla   
            and a.id_programa=b.id_programa
            and a.id_tipo_credito=c.id_tipo_credito
            and a.id_escalafon='D'
            and a.credito>0
            ";
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            return toba::db('designa')->consultar($sql);
	}
        function get_credito($ua,$anio)
        {
             $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b ,mocovi_periodo_presupuestario c "
                     . " where  a.id_unidad=trim(upper('".$ua."')) and a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo and c.anio=".$anio ;
            
             $resul=toba::db('designa')->consultar($sql);
             
             if($resul[0]['cred'] <>null){
                    $tengo=$resul[0]['cred'];
             }else{$tengo=0;
                      
                }
             return $tengo;   
            
        }
        //obtiene el credito correspondiente al periodo actual de la UA que se haya logueado
        function get_credito_actual(){
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario c, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo 
                         and c.actual
                         and a.id_unidad=d.sigla";
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            $resul=toba::db('designa')->consultar($sql);
            if($resul[0]['cred'] <>null){
                $credito=$resul[0]['cred'];
             }else{
                $credito=0;      
                }
            return $credito;   
        }
        
        function get_credito_ua($estado){
            switch ($estado) {
                case 1:$where=' and c.actual '; break;
                case 2:$where=' and c.presupuestando '; break;
                
            }
            
            $sql="select sum(b.credito) as cred "
                     . " from mocovi_programa a, mocovi_credito b, mocovi_periodo_presupuestario c, unidad_acad d "
                     . " where  a.id_programa=b.id_programa"
                     . " and b.id_periodo=c.id_periodo "
                     . " and a.id_unidad=d.sigla".$where;
            
            $sql = toba::perfil_de_datos()->filtrar($sql);//aplico el perfil de datos
            
            $resul=toba::db('designa')->consultar($sql);
            if($resul[0]['cred'] <>null){
                $credito=$resul[0]['cred'];
             }else{
                $credito=0;      
                }
            return $credito;   
        }

	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['id_periodo'])) {
			$where[] = "t_mpp.anio = ".$filtro['id_periodo'];
		}
		if (isset($filtro['id_unidad'])) {
			$where[] = "t_mc.id_unidad = ".quote($filtro['id_unidad']);
		}
		$sql = "SELECT
			t_mc.id_credito,
			t_mpp.id_periodo as id_periodo_nombre,
			t_mc.id_unidad as id_unidad,
			t_e.descripcion as id_escalafon_nombre,
			t_mtc.tipo as id_tipo_credito_nombre,
			t_mc.descripcion,
			t_mc.credito,
			t_mp.nombre as id_programa_nombre,
                        case when t_mc.documento is not null then  '<a href='||chr(39)||'creditos_dependencia/'||t_mc.documento||chr(39)|| ' target='||chr(39)||'_blank'||chr(39)||'>'||documento||'</a>' else '' end as documento
		FROM
			mocovi_credito as t_mc	
                        LEFT OUTER JOIN mocovi_periodo_presupuestario as t_mpp ON (t_mc.id_periodo = t_mpp.id_periodo)
			LEFT OUTER JOIN escalafon as t_e ON (t_mc.id_escalafon = t_e.id_escalafon)
			LEFT OUTER JOIN mocovi_tipo_credito as t_mtc ON (t_mc.id_tipo_credito = t_mtc.id_tipo_credito)
			LEFT OUTER JOIN mocovi_programa as t_mp ON (t_mc.id_programa = t_mp.id_programa)
                where t_mc.id_escalafon='D'        
		ORDER BY id_tipo_credito_nombre,id_programa_nombre";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
                
		return toba::db('designa')->consultar($sql);
	}

}
?>