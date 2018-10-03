<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_titulo extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT codc_titul, desc_titul FROM titulo ORDER BY desc_titul";
		return toba::db('designa')->consultar($sql);
	}

	function get_listado()
	{
		$sql = "SELECT
			t_t.codc_titul,
			t_t.nro_tab3,
			t_t.codc_nivel,
			t_t.desc_titul
		FROM
			titulo as t_t
		ORDER BY codc_nivel,desc_titul";
		return toba::db('designa')->consultar($sql);
	}
        function get_titulos($filtro=null){
            if(!is_null($filtro)){
                $where=' and '.$filtro;
            }else{
                $where='';
            }
          
            $udia=dt_mocovi_periodo_presupuestario::ultimo_dia_periodo(1);//utlimo deia del periodo actual
            $pdia=dt_mocovi_periodo_presupuestario::primer_dia_periodo(1);
            $sql="select distinct agente,legajo,desc_titul,codc_nivel,fec_emisi,otorgante from (select distinct trim(apellido)||', '||trim(d.nombre) as agente,d.legajo,uni_acad,codc_nivel,i.desc_titul,fec_emisi,e.nombre as otorgante,case when (s.desde<='".$udia."' and (s.hasta is null or s.hasta>='".$pdia."')) then 1 else 0 end as vigente
                  from titulos_docente t
                    LEFT OUTER JOIN docente d ON (t.id_docente=d.id_docente)
                    LEFT OUTER JOIN designacion s ON (s.id_docente=d.id_docente)
                    LEFT OUTER JOIN titulo i ON (t.codc_titul=i.codc_titul)
                    LEFT OUTER JOIN entidad_otorgante e ON (t.codc_entot=e.cod_entidad)
                 )sub, unidad_acad u
                     where sub.uni_acad=u.sigla
                     $where
                order by agente";
            
            $sql = toba::perfil_de_datos()->filtrar($sql);    
            return toba::db('designa')->consultar($sql);
        }
       

}
?>