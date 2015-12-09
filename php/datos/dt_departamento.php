<?php
class dt_departamento extends toba_datos_tabla
{
	function get_descripciones()
	{
		$sql = "SELECT iddepto, descripcion FROM departamento ORDER BY descripcion";
		return toba::db('designa')->consultar($sql);
	}

        function get_departamentos($id_ua=null)
	{
		$where ="";
                            
                if(isset($id_ua)){
                    $where=" and idunidad_academica='".$id_ua."'";
                    
                }
                $sql = "SELECT t_d.iddepto, t_d.descripcion "
                        . " FROM departamento t_d,unidad_acad t_u "
                        . " WHERE t_u.sigla=t_d.idunidad_academica $where";
                
                $con="select sigla,descripcion from unidad_acad ";
                $con = toba::perfil_de_datos()->filtrar($con);
                $resul=toba::db('designa')->consultar($con);
                
                if((trim($resul[0]['sigla'])<>'CRUB') && (trim($resul[0]['sigla'])<>'FACA') && (trim($resul[0]['sigla'])<>'ASMA')){
                    $sql = toba::perfil_de_datos()->filtrar($sql);
                }
                
                
		$resul = toba::db('designa')->consultar($sql);
                return $resul;
        }
}
?>