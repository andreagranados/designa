<?php

class consultas_mapuche
{
 
 function get_cargos($ua,$udia,$pdia){
 	
 	//recupero los cargos correspondientes al periodo y a la UA
 	$where="";
 	if(isset($ua)){
 		$where=" and b.codc_uacad='".$ua."'";
 		}
 	$sql=" select b.nro_cargo,b.codc_uacad,b.codc_categ,b.codc_carac,b.fec_alta,b.fec_baja,b.nro_cargo,b.nro_legaj,a.desc_appat,a.desc_nombr
 	 from mapuche.dh03 b,mapuche.dh01 a, mapuche.dh11 c
 	where b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null)
 	and b.codc_categ=c.codc_categ
	and c.tipo_escal='D'
 	and a.nro_legaj=b.nro_legaj".$where;
 	
 	
 	$datos_mapuche = toba::db('mapuche')->consultar($sql);
 	
 	return $datos_mapuche;
 	}

}

?>