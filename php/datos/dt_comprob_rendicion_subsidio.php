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
  function x(){
      
  }  
}
?>