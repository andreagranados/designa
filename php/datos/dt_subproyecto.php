<?php
class dt_subproyecto extends toba_datos_tabla
{
    function get_descripciones(){
       $sql="select * from subproyecto";  
       return toba::db('designa')->consultar($sql);        
    }
        
    function esta($id_programa,$id_proyecto){
        $sql="select * from subproyecto where id_programa=$id_programa and id_proyecto=$id_proyecto";
        $res=toba::db('designa')->consultar($sql);
        if(count($res)>0){
            return true;
        }else{
            return false;
        }
    }    
    function eliminar_subproyecto($id_proy){
        //el proyecto que ingresa como argumento es un programa por tanto 
        //no puede pertenecer a un programa
        $sql="delete from subproyecto where id_proyecto=$id_proy";
        toba::db('designa')->consultar($sql);
        }
    //modifica el estado de todos los subproyectos correspondientes al programa que ingresa como argumento
    function cambiar_estado($id_proy,$estado){
        //el proyecto que ingresa como argumento es un programa 
        $sql="update pinvestigacion set estado='".$estado."' where id_pinv in (select id_proyecto from subproyecto where id_programa=$id_proy)";
        toba::db('designa')->consultar($sql);
    }   
    //modifica los datos de los subproyectos del programa que ingresa como parametro
    function cambia_datos($id_proy,$datos){
        
        $concatenar=" set estado="."'".$datos['estado']."'";
        if(isset($datos['fec_desde'])){//si cambia fecha desde tambien modifica fecha desde de los integrantes de los subproyectos
            $concatenar.=" ,fec_desde='".$datos['fec_desde']."'";
            $sql="update integrante_interno_pi set desde='".$datos['fec_desde']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
            $sql="update integrante_externo_pi set desde='".$datos['fec_desde']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
        }
        if(isset($datos['fec_hasta'])){//si cambia hasta desde tambien modifica fecha hasta de los integrantes de los subproyectos
            $concatenar.=" ,fec_hasta='".$datos['fec_hasta']."'";
            $sql="update integrante_interno_pi set hasta='".$datos['fec_hasta']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
            $sql="update integrante_externo_pi set hasta='".$datos['fec_hasta']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
        }
        if(isset($datos['nro_resol'])){//si cambia nro_resol desde tambien modifica resol de los integrantes de los subproyectos
            $concatenar.=" , nro_resol='".$datos['nro_resol']."'";
            $sql="update integrante_interno_pi set rescd='".$datos['nro_resol']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
            $sql="update integrante_externo_pi set rescd='".$datos['nro_resol']."' where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
        }
        if($datos['check']==1){//si cambio a A el estado del proyecto check viene en 1
            $sql="update integrante_interno_pi set check_inv=1 where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
            $sql="update integrante_externo_pi set check_inv=1 where pinvest in (select id_proyecto from subproyecto where id_programa=$id_proy)";
            toba::db('designa')->consultar($sql);
        }
        if(isset($datos['codigo'])){
            $concatenar.=" , codigo='".$datos['codigo']."'";
        }
//        if(isset($datos['nro_resol'])){
//            $concatenar.=" , nro_resol='".$datos['nro_resol']."'";
//        }
        if(isset($datos['fec_resol'])){
            $concatenar.=" , fec_resol="."'".$datos['fec_resol']."'";
        }
        if(isset($datos['fecha_ord_cs'])){
            $concatenar.=" , fecha_ord_cs="."'".$datos['fecha_ord_cs']."'";
        }
        if(isset($datos['observacion'])){
            $concatenar.=" , observacion="."'".$datos['observacion']."'";
        }
        if(isset($datos['observacionscyt'])){
            $concatenar.=" , observacionscyt="."'".$datos['observacionscyt']."'";
        }
        if(isset($datos['disp_asent'])){
            $concatenar.=" , disp_asent="."'".$datos['disp_asent']."'";
        }
        if(isset($datos['nro_resol_baja'])){
            $concatenar.=" , nro_resol_baja="."'".$datos['nro_resol_baja']."'";
        }
        if(isset($datos['fec_baja'])){
            $concatenar.=" , fec_baja="."'".$datos['fec_baja']."'";
        }
        //el proyecto que ingresa como argumento es un programa 
        $sql="update pinvestigacion ".$concatenar." where id_pinv in (select id_proyecto from subproyecto where id_programa=$id_proy)";
        toba::db('designa')->consultar($sql);
    }  

}

?>