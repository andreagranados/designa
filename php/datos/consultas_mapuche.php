<?php

class consultas_mapuche
{
	
  function get_antiguedad_del_docente($legajo){
  	$sql="select a.nro_legaj,trunc(max(impp_conce)) as antig
		from mapuche.dh21h a, mapuche.dh03 b
		where (a.nro_liqui>=465 and a.nro_liqui<=473)
		and a.codn_conce=413
		and a.nro_cargo=b.nro_cargo
		and a.tipoescalafon='D'
		and a.nro_legaj=$legajo
		group by a.nro_legaj  	";
	
	$res= toba::db('mapuche')->consultar($sql);
	if(count($res)>0){
		return $res[0]['antig'];
	}else{
		return 0;
		}
 	
 }
 function get_antiguedad_docente($legajos){
	$sql="select a.nro_legaj,trunc(max(impp_conce)) as antig
		from mapuche.dh21h a, mapuche.dh03 b
		where (a.nro_liqui>=465 and a.nro_liqui<=473)
		and a.codn_conce=413
		and a.nro_cargo=b.nro_cargo
		and a.tipoescalafon='D'
		and a.nro_legaj in ($legajos)
		group by a.nro_legaj ";
	
	return toba::db('mapuche')->consultar($sql);
 	
 	}
 function get_dh01($documentos){
 	$sql="select * from mapuche.dh01 where nro_docum in($documentos)";
 	$datos_mapuche = toba::db('mapuche')->consultar($sql);
 	return $datos_mapuche;
 	}
 function get_lic_maternidad($ua,$udia,$pdia){
 	//recupero las licencias por maternidad del periodo y ua ingresadas
 	$sql="select distinct b.nro_licencia,case when b.nro_legaj is null then a.nro_legaj else b.nro_legaj end as nro_legaj,b.nro_cargo,fec_desde,fec_hasta,codn_tipo_lic as tipo_lic
		from  mapuche.dh05 b, mapuche.dl02 c, mapuche.dh03 a
		where b.nrovarlicencia=c.nrovarlicencia
		and (c.codn_tipo_lic='MATE' or c.codn_tipo_lic='MAT2' or c.codn_tipo_lic='MAT3' or c.codn_tipo_lic='MAT4')
		and b.fec_desde <= '".$udia."' and (b.fec_hasta >= '".$pdia."' or b.fec_hasta is null)
		and (b.nro_cargo=a.nro_cargo or b.nro_legaj=a.nro_legaj)
		and a.codc_uacad='".$ua."'";	
	
	return toba::db('mapuche')->consultar($sql);
 	
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