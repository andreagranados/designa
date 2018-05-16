<?php
require_once 'dt_mocovi_periodo_presupuestario.php';
class dt_suplente extends toba_datos_tabla
{
    function get_descripciones()
    {
	$sql = "SELECT * FROM suplente ";
        return toba::db('designa')->consultar($sql);
    }
    //retorna true si la designacion existe con caracter de suplente
    function existe($id_desig){
        $sql="select * from suplente where id_desig_suplente=$id_desig";
        $res=toba::db('designa')->consultar($sql);
      
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }
    function get_suplencias($filtro=null){
        $where=' ';
        $where2=' ';
        if(!is_null($filtro)){
            if(isset ($filtro['anio'])){
             $pdia = dt_mocovi_periodo_presupuestario::primer_dia_periodo_anio($filtro['anio']['valor']);
             $udia = dt_mocovi_periodo_presupuestario::ultimo_dia_periodo_anio($filtro['anio']['valor']);
             $where =" where d.desde <= '".$udia."' and (d.hasta >= '".$pdia."' or d.hasta is null)";
            }
            if(isset ($filtro['uni_acad'])){
             $where2 .=" and uni_acad='".$filtro['uni_acad']['valor']."'";     
             }
            
        }
        
        $sql="select * from (select distinct d.uni_acad,d.id_designacion,doc2.apellido||', '||doc2.nombre as agente1, doc2.legajo,d.carac as caracs,d.desde as desde_s,d.hasta as hasta_s,d.cat_estat||d.dedic as categoria_s,doc.apellido||', '||doc.nombre as agente2,doc.legajo,e.cat_estat||e.dedic as categoria,e.desde,e.hasta,e.carac,t.desc_corta as novedad,n.desde as desden,n.hasta as hastan
                from suplente s
                LEFT OUTER JOIN designacion d ON (s.id_desig_suplente=d.id_designacion)
                LEFT OUTER JOIN designacion e ON (s.id_desig=e.id_designacion)
                LEFT OUTER JOIN docente doc ON (e.id_docente=doc.id_docente)
                LEFT OUTER JOIN docente doc2 ON (d.id_docente=doc2.id_docente)
                LEFT OUTER JOIN novedad n ON (n.id_designacion=e.id_designacion and n.tipo_nov in (2,5) and (d.desde>=n.desde and d.hasta<=n.hasta) )"//periodo de la suplencia dentro del periodo de la licencia
                ." LEFT OUTER JOIN tipo_novedad t ON (n.tipo_nov=t.id_tipo)
                
                $where)sub, unidad_acad u"
                . " where sub.uni_acad=u.sigla $where2"
                . " order by agente1";
        $sql = toba::perfil_de_datos()->filtrar($sql);  
        return toba::db('designa')->consultar($sql);
            
    }
}

?>