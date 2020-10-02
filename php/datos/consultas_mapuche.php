<?php

class consultas_mapuche
{
  function get_docentes_categ_dias($ua,$udia,$pdia){
     $sql="select  desc_appat,desc_nombr,nro_legaj,nro_docum,case when codc_uacad='IBMP' then 'ESCM' else codc_uacad end as codc_uacad ,codc_categ,sum(case when chkstopliq=1 then 0 else case when (dias_des-dias_lic)>=0 then (dias_des-dias_lic) else 0 end end )as dias
            from
            (select distinct b.desc_appat,b.desc_nombr,b.nro_legaj,b.nro_docum,a.codc_uacad,a.nro_cargo,a.codc_categ,a.fec_alta,a.fec_baja,a.chkstopliq,
             sum(case when l.nro_licencia is null or (l.nro_licencia is not null and m.es_remunerada) then 0 else (case when (l.fec_desde>'".$udia."' or (l.fec_hasta is not null and l.fec_hasta<'".$pdia."')) then 0 else (case when l.fec_desde<='".$pdia."' then ( case when (l.fec_hasta is null or l.fec_hasta>='".$udia."' ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((l.fec_hasta-'".$pdia."')+1) end ) else (case when (l.fec_hasta is null or l.fec_hasta>='".$udia."' ) then ((('".$udia."')-l.fec_desde+1)) else ((l.fec_hasta-l.fec_desde+1)) end ) end )end)end) as dias_lic,
             case when a.fec_alta<='".$pdia."' then ( case when (a.fec_baja>='".$udia."' or a.fec_baja is null ) then (((cast('".$udia."' as date)-cast('".$pdia."' as date))+1)) else ((a.fec_baja-'".$pdia."')+1) end ) else (case when (a.fec_baja>='".$udia."' or a.fec_baja is null) then ((('".$udia."')-a.fec_alta+1)) else ((a.fec_baja-a.fec_alta+1)) end ) end as dias_des 
            from mapuche.dh03 a
            inner join  mapuche.dh01 b on ( a.nro_legaj=b.nro_legaj)
            inner join  mapuche.dh11 c on (c.codc_categ=a.codc_categ)
            left outer join mapuche.dh05 l on ((a.nro_cargo=l.nro_cargo or a.nro_legaj=l.nro_legaj ) and l.fec_desde <= '".$udia."' and (l.fec_hasta >= '".$pdia."' or l.fec_hasta is null))
            left outer join mapuche.dl02 m on ( l.nrovarlicencia = m.nrovarlicencia )
            WHERE tipo_escal='D' 
            and a.fec_alta <= '".$udia."' and (a.fec_baja >= '".$pdia."' or a.fec_baja is null)
            --and a.nro_legaj=52816
            and a.codc_uacad='".$ua."'
            and b.tipo_estad<>'P'
            and a.codc_categ<>'SCAT' and a.codc_categ<>'DOCL'
            group by b.desc_appat,b.desc_nombr,b.nro_legaj,b.nro_docum,a.codc_uacad,a.nro_cargo,a.codc_categ,a.fec_alta,a.fec_baja,a.chkstopliq  
            )sub    
            group by desc_appat,desc_nombr,nro_legaj,nro_docum,codc_uacad,codc_categ  "; 
     return toba::db('mapuche')->consultar($sql);
  }	
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
// function get_dh01($documentos){
// 	$sql="select * from mapuche.dh01 where nro_docum in($documentos)";
// 	$datos_mapuche = toba::db('mapuche')->consultar($sql);
// 	return $datos_mapuche;
// 	}
 function get_dh01($documentos){
    $sql="select a.*,b.fec_ingreso,c.telefono_celular,c.telefono from mapuche.dh01 a"
            . " left outer join mapuche.dh09 b on (a.nro_legaj=b.nro_legaj) "
            . " left outer join mapuche.dha1 c on (a.nro_legaj=c.nro_persona and domprincipal) "
            . " where a.nro_docum in($documentos)";
    $datos_mapuche = toba::db('mapuche')->consultar($sql);
    return $datos_mapuche;
}
 function get_lic_maternidad($ua,$udia,$pdia){
 	//recupero las licencias por maternidad del periodo y ua ingresadas
 	$sql="select distinct b.nro_licencia,case when b.nro_legaj is null then a.nro_legaj else b.nro_legaj end as nro_legaj,b.nro_cargo,fec_desde,fec_hasta,codn_tipo_lic as tipo_lic
		from  mapuche.dh05 b, mapuche.dl02 c, mapuche.dh03 a
		where b.nrovarlicencia=c.nrovarlicencia
		and (c.codn_tipo_lic='MATE' or c.codn_tipo_lic='MAT2' or c.codn_tipo_lic='MAT3' or c.codn_tipo_lic='MAT4' or c.codn_tipo_lic='MD90' or c.codn_tipo_lic='48a6' or c.codn_tipo_lic='MATD')
		and b.fec_desde <= '".$udia."' and (b.fec_hasta >= '".$pdia."' or b.fec_hasta is null)
		and (b.nro_cargo=a.nro_cargo or b.nro_legaj=a.nro_legaj)
		and a.codc_uacad='".$ua."'";	
	
	return toba::db('mapuche')->consultar($sql);
 	
 	}	
 //recupero los cargos docentes correspondientes al periodo y a la UA
 function get_cargos($ua,$udia,$pdia){
 	
 	
 	$where="";
 	if(isset($ua)){
 		if($ua=='FADE'){
 	          $where=" and (b.codc_uacad='".$ua."' or b.codc_uacad='SESO')";
 	        }else{
 	          $where=" and b.codc_uacad='".$ua."'";
 	        }
 		
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
 	
     function get_cargos_imputaciones($ua,$udia,$pdia,$cargos){
 	$where="";
 	if(isset($ua)){
 		if($ua=='FADE'){
 	          $where=" and (b.codc_uacad='".$ua."' or b.codc_uacad='SESO')";
 	        }else{
 	          $where=" and b.codc_uacad='".$ua."'";
 	        }
 		
 		}
       
	$sql=" select distinct a.*  from (select b.nro_cargo,b.chkstopliq,b.codc_uacad,b.codc_categ,b.codc_carac,b.fec_alta,b.fec_baja,b.nro_legaj,a.desc_appat,a.desc_nombr,tipo_ejercicio||'.'||trim(to_char(codn_grupo_presup,'0000'))||'-'||trim(to_char(codn_area ,'000'))||'-'||trim(to_char(codn_subar,'000'))||'-'||trim(to_char(codn_subsubar,'000'))||'-'||trim(to_char(codn_fuent,'00'))||'-'||trim(to_char(codn_progr,'00'))||'-'||trim(to_char(codn_subpr,'00'))||'-'||trim(to_char(codn_proye,'00'))||'-'||trim(to_char(codn_activ,'00'))||'-'||trim(to_char(codn_obra,'00'))||'-'||codn_final||'-'||codn_funci as imputacion,codn_area,codn_subar,codn_subsubar,codn_fuent,porc_ipres
 	 from mapuche.dh03 b
 	 LEFT OUTER JOIN mapuche.dh01 a ON (a.nro_legaj=b.nro_legaj)
 	 LEFT OUTER JOIN mapuche.dh11 c ON (b.codc_categ=c.codc_categ)
 	 LEFT OUTER JOIN mapuche.dh24 d ON (b.nro_cargo=d.nro_cargo)
 	
 	where b.fec_alta <= '".$udia."' and (b.fec_baja >= '".$pdia."' or b.fec_baja is null)
 	and c.tipo_escal='D'
	and b.nro_cargo in($cargos)
	 ".$where." )a " ;
 	
        $datos_mapuche = toba::db('mapuche')->consultar($sql);
 	
 	return $datos_mapuche;
     }
     

}

?>