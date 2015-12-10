<?php
class dt_docente extends toba_datos_tabla
{
        function get_horas_docencia($id_doc,$udia,$pdia){
           //simple 10 hs
            //parcial 20 hs
            //exclusiva 40 hs
            $sql="select sum (case when dedic=1 then 10  else case when dedic=2 then 20 else 40 end end ) as hd from designacion t_d 
                    where id_docente=".$id_doc.       
                    " and desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)      ";
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['hd'] != null){
                $hd=$res[0]['hd'];
            }else{
                $hd=0;
            }
            return $hd;
        }
        function get_horas_gestion($id_doc,$udia,$pdia){
            $sql="select sum (case when cargo_gestion is not null then 40  else 0 end ) as hg
                   from designacion t_d 
                    where id_docente=".$id_doc.       
                    " and desde <= '".$udia."' and (hasta >= '".$pdia."' or hasta is null)      ";
           
            $res=toba::db('designa')->consultar($sql);
            
            if($res[0]['hg'] != null){
                $hg=$res[0]['hg'];
            }else{
                $hg=0;
            }
            return $hg;
        }
        function get_horas_pinv($id_doc,$udia,$pdia){
             $sql="select sum (carga_horaria) as hi from designacion t_d,integrante_interno_pi t_p
                    where t_d.id_docente=".$id_doc.       
                    " and t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)      "
                     . " and t_d.id_designacion=t_p.id_designacion ";
             
            $res=toba::db('designa')->consultar($sql);
            
            if($res[0]['hi'] != null){
                $hi=$res[0]['hi'];
            }else{
                $hi=0;
            }
            return $hi;
        }
        function get_horas_ext($id_doc,$udia,$pdia){
             $sql="select sum (carga_horaria) as hi from designacion t_d,integrante_interno_pe t_p
                    where t_d.id_docente=".$id_doc.       
                    " and t_d.desde <= '".$udia."' and (t_d.hasta >= '".$pdia."' or t_d.hasta is null)      "
                     . " and t_d.id_designacion=t_p.id_designacion ";
            $res=toba::db('designa')->consultar($sql);
            if($res[0]['hi'] != null){
                $hi=$res[0]['hi'];
            }else{
                $hi=0;
            }
            return $hi;
        }
        function get_listado($where=null)
	{
            if(!is_null($where)){
                $where='Where '.$where;
            }else{
                $where='';
            }
 
	    $sql = "SELECT distinct 
			t_d.id_docente,
			t_d.legajo,
			t_d.apellido,
			t_d.nombre,
			t_d.nro_tabla,
			t_d.tipo_docum,
			t_d.nro_docum,
			t_d.fec_nacim,
			cast (cast(t_d.nro_cuil1 as text)||cast(t_d.nro_cuil as text)||cast(  t_d.nro_cuil2 as text) as numeric) as cuil,
			t_d.nro_cuil,
			t_d.nro_cuil2,
			t_d.tipo_sexo,
			t_d.fec_ingreso,
			t_p.descripcion_pcia as pcia_nacim_nombre,
			t_p1.nombre as pais_nacim_nombre
			
                        
		FROM
			docente as t_d LEFT OUTER JOIN provincia as t_p ON (t_d.pcia_nacim = t_p.codigo_pcia)
			LEFT OUTER JOIN pais as t_p1 ON (t_d.pais_nacim = t_p1.codigo_pais)
                         $where            
		ORDER BY t_d.apellido,t_d.nombre";
            
                return toba::db('designa')->consultar($sql);
                
	}


	function get_descripciones()
	{
		$sql = "SELECT distinct id_docente, apellido||', '||nombre||'-'||tipo_docum||':'||nro_docum as nombre FROM docente where nro_docum is not null ORDER BY nombre";
		return toba::db('designa')->consultar($sql);
	}

        

}
?>