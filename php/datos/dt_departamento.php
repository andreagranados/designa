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
	function get_listado($filtro=array())
	{
		$where = array();
		if (isset($filtro['iddepto'])) {
			$where[] = "iddepto = ".quote($filtro['iddepto']);
		}
		$sql = "SELECT
			t_d.iddepto,
			t_ua.descripcion as idunidad_academica_nombre,
			t_d.descripcion
		FROM
			departamento as t_d,
			unidad_acad as t_ua
		WHERE
				t_d.idunidad_academica = t_ua.sigla
		ORDER BY descripcion";
		if (count($where)>0) {
			$sql = sql_concatenar_where($sql, $where);
		}
		return toba::db('designa')->consultar($sql);
	}

        function get_listado_filtro($where=null)
        {
            if(!is_null($where)){
                    $where=' WHERE '.$where;
                }else{
                    $where='';
                }
            $sql="select * from departamento".$where. " order by descripcion"; 
            
            return toba::db('designa')->consultar($sql);
        }
        //retorna true si el departamento que ingresa como parametro tiene areas y false en caso contrario
        function tiene_areas($id_dpto){
            $sql = "select * from departamento where iddepto=".$id_dpto;  
            $res = toba::db('designa')->consultar($sql);
            
            if(count($res[0])>0){
                return true;
            }else{
                return false;
            }
        }
        function get_listado_completo($where=null){
            
            if(!is_null($where)){
                $where=' WHERE '.$where;
                }else{
                    $where='';
                }
                
            $sql="select a.descripcion as departamento,b.descripcion as area,c.descripcion as orientacion"
                    . " from (select * from departamento".$where.")a ,"
                    ."area b, orientacion c"
                    ." where a.iddepto=b.iddepto "
                    . " and b.idarea=c.idarea "
                    . " order by a.descripcion,b.descripcion,c.descripcion";
            
            $sql2=" CREATE LOCAL TEMP TABLE auxi(
                        departamento character(100),
                        area character(100),
                        orientacion character(100)
                    );";
            toba::db('designa')->consultar($sql2);
            $res=toba::db('designa')->consultar($sql);
           
            $i=1;
            $dep=$res[0]['departamento'];
            $area=$res[0]['area'];
            $orien=$res[0]['orientacion'];
            $sql3=" insert into auxi values ('".$dep."','".$area."','".$orien."')";
            toba::db('designa')->consultar($sql3);
            
            while ($i<count($res)) {
                if($res[$i]['departamento']==$dep){
                    $depi="";
                }else{
                    $dep=$res[$i]['departamento'];
                    $depi=$res[$i]['departamento'];
                }
                if($res[$i]['area']==$area){
                    $areai="";
                }else{
                    $area=$res[$i]['area'];
                    $areai=$res[$i]['area'];
                }
                if($res[$i]['orientacion']==$orien){
                    $orieni="";
                }else{
                    $orien=$res[$i]['orientacion'];
                    $orieni=$res[$i]['orientacion'];
                }
                //$sql3=" insert into auxi values ('".$depi."','".$areai."','".$orieni."')";
                $sql3=" insert into auxi values ('".$depi."','".$areai."','".$orieni."')";
                toba::db('designa')->consultar($sql3);
                $i=$i+1;
            }
            $sql4="select * from auxi";
            $res=toba::db('designa')->consultar($sql4);
            
            return $res;
            
        }

}
?>