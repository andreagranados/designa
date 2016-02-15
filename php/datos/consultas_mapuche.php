<?php

class consultas_mapuche
{
 function get_dh01($documentos){
 	$sql="select * from mapuche.dh01 where nro_docum in( $documentos)";
 	$datos_mapuche = toba::db('mapuche')->consultar($sql);
 	return $datos_mapuche;
 	}
 //recupero los cargos docentes correspondientes al periodo y a la UA
 function get_cargos($ua,$udia,$pdia){
 	
 	
 	$where="";
 	if(isset($ua)){
 		$where=" and b.codc_uacad='".$ua."'";
 		}
 	//recupero las licencias del periodo no remuneradas
 	$sql="select b.nro_licencia,b.nro_legaj,b.nro_cargo,fec_desde,fec_hasta,codn_tipo_lic 
		into temp lic
		from  mapuche.dh05 b, mapuche.dl02 c
		where b.nrovarlicencia=c.nrovarlicencia
		and c.es_remunerada=false
		and b.fec_desde <= '".$udia."' and (b.fec_hasta >= '".$pdia."' or b.fec_hasta is null)";	
		
 	toba::db('mapuche')->consultar($sql);
 	
 	$sql=" select distinct a.*, case when nro_licencia is null then 'NO' else 'SI' end as lic  from (select b.nro_cargo,b.chkstopliq,b.codc_uacad,b.codc_categ,b.codc_carac,b.fec_alta,b.fec_baja,b.nro_legaj,a.desc_appat,a.desc_nombr
 	 from mapuche.dh03 b,mapuche.dh01 a, mapuche.dh11 c
 	where b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null)
 	and b.codc_categ=c.codc_categ
	and c.tipo_escal='D'
 	and a.nro_legaj=b.nro_legaj".$where." )a ".
 	" LEFT OUTER JOIN lic b
 	                                ON (a.nro_cargo=b.nro_cargo or a.nro_legaj=b.nro_legaj)";
 	
 	
 	$datos_mapuche = toba::db('mapuche')->consultar($sql);
 	
 	return $datos_mapuche;
 	}

}

?>