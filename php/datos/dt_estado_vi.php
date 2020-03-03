<?php
class dt_estado_vi extends toba_datos_tabla
{
	function get_descripciones()
	{
            $sql = "SELECT id_estado, descripcion FROM estado_vi ORDER BY descripcion desc";//ordenado desc para que aparezca Solicitado, Rechazado, Aprobado
            return toba::db('designa')->consultar($sql);
	}

        function get_descripciones_perfil(){
            $perfil = toba::usuario()->get_perfil_datos();
            if ($perfil != null) {//es de una unidad academica
                 $sql="select * from estado_vi where id_estado='S'";//solo retorna Solicitado, la ua solo puede solicitar
            }else{
                 $sql="select * from estado_vi ";
            }
            return toba::db('designa')->consultar($sql);
       }
       //dado un id de estado retorna la descripcion
       function get_descripcion($estado){
           if(isset($estado)){
                $sql="select descripcion from estado_vi where id_estado='".$estado."'";
                $resul= toba::db('designa')->consultar($sql);
                return $resul[0]['descripcion'];
           }else{
               return '';
           }
       }
}

?>