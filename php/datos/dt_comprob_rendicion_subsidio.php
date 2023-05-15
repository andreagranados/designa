<?php
class dt_comprob_rendicion_subsidio extends toba_datos_tabla
{
  function get_comprobantes($datos){
      $sql="select id, nro_subsidio, id_proyecto, fecha, tipo, punto_venta, nro_comprobante, 
       detalle, razon_social, nro_cuit1, nro_cuit, nro_cuit2, importe, 
       c.id_rubro ,nro_cuit1||'-'||lpad(cast(nro_cuit as text),8,'0')||'-'||nro_cuit2 as cuit,t.descripcion as tipo_desc,r.descripcion as rubro "
              . " ,case when tipo=1 or tipo=2 then lpad(cast(c.punto_venta as text),5,'0')||'-'||lpad(cast(c.nro_comprobante as text),8,'0') else cast (nro_comprobante as text) end as comprobante ,archivo_comprob"
              . " from comprob_rendicion_subsidio c "
              . " left outer join tipo_comp_subsidio t on (c.tipo=t.id_tipo)"
              . " left outer join rubro_presupuesto r on (r.id_rubro=c.id_rubro)"
              . " where nro_subsidio=".$datos['numero']
              . " and id_proyecto=".$datos['id_proyecto'];
      return toba::db('designa')->consultar($sql);
  }
  function cambiar_adj($id,$valor){
        $sql="update comprob_rendicion_subsidio set archivo_comprob='".$valor."' where id=".$id;
        toba::db('designa')->consultar($sql);
    }
  function ya_existe($nro_comp,$pv,$tipo){
      $salida=array();
      if(isset($pv)){//tiene valor el punto de venta
        $sql="select p.codigo,c.nro_comprobante,c.nro_subsidio from comprob_rendicion_subsidio c"
              . " inner join pinvestigacion p on (p.id_pinv=c.id_proyecto)"
              . " where c.nro_comprobante=$nro_comp and c.punto_venta=$pv and c.tipo=$tipo ";  
      }else{//no hay punto de venta porque es recibo, factura exterior, pasaje
          $sql="select p.codigo,c.nro_comprobante,c.nro_subsidio from comprob_rendicion_subsidio c"
              . " inner join pinvestigacion p on (p.id_pinv=c.id_proyecto)"
              . " where c.nro_comprobante=$nro_comp and c.tipo=$tipo ";  
      }    
      $resul=toba::db('designa')->consultar($sql);
      if(count($resul)>0){
          $salida['existe']=true;
          $salida['en']='Proyecto: '.$resul[0]['codigo'].' Nro Sub:'.$resul[0]['nro_subsidio'];
      }else{
          $salida['existe']=false;
      }
      return $salida;
  }  
  function x(){
      
  }  
  function get_listado_comprobantes($filtro=null){
      $where=" WHERE 1=1 ";
      $con="select sigla from unidad_acad ";
      $con = toba::perfil_de_datos()->filtrar($con);
      $resul=toba::db('designa')->consultar($con);
      $pd = toba::manejador_sesiones()->get_perfil_datos();
      if(isset($pd)){//pd solo tiene valor cuando el usuario esta asociado a un perfil de datos
            switch (trim($resul[0]['sigla'])) {
                //FAIN ve lo de FAIN y lo de AUZA
                case 'FAIN': $where.=" and (uni_acad = ".quote($resul[0]['sigla'])." or uni_acad ='AUZA'".")";break;
                case 'ASMA': $where.= " and codigo like '04/S%'";break;//san martin solo ve los S
                default:$where .= " and uni_acad = ".quote($resul[0]['sigla']);      
            }
        }//sino es usuario de la central no filtro a menos que haya elegido
      
      if(!is_null($filtro)){
                $where.=' and '.$filtro;
            }
      $sql="select * from 
          (select 
           i.uni_acad,
           i.codigo,
           id,
           nro_subsidio,
           c.id_proyecto,
           s.estado,
           s.monto,
           fecha,
           c.tipo,
           punto_venta, 
           nro_comprobante, 
           detalle, 
           razon_social,
           nro_cuit1, nro_cuit, nro_cuit2, 
           importe, 
           c.id_rubro ,nro_cuit1||'-'||lpad(cast(nro_cuit as text),8,'0')||'-'||nro_cuit2 as cuit,
           t.descripcion as tipo_desc,
           r.descripcion as rubro "
              . " ,case when c.tipo=1 or c.tipo=2 then lpad(cast(c.punto_venta as text),5,'0')||'-'||lpad(cast(c.nro_comprobante as text),8,'0') else cast (nro_comprobante as text) end as comprobante "
              . ",archivo_comprob"
              . " from comprob_rendicion_subsidio c "
              . " inner join subsidio s on (c.id_proyecto=s.id_proyecto and c.nro_subsidio=s.numero)"
              . " inner join pinvestigacion i on (i.id_pinv=s.id_proyecto)"
              . " left outer join tipo_comp_subsidio t on (c.tipo=t.id_tipo)"
              . " left outer join rubro_presupuesto r on (r.id_rubro=c.id_rubro)"
              . ")sub"
              . " $where"
              . " order by  id_proyecto,nro_subsidio,fecha";
      return toba::db('designa')->consultar($sql);
  }
}
?>