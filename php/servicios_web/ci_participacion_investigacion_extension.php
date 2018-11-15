<?php
class ci_participacion_investigacion_extension extends toba_ci
{	
	function ini()
	{
            $sql="select * from persona ";
            $res=toba::db('designa')->consultar($sql);
            echo json_encode($res);
            exit;
            
	}

}
?>