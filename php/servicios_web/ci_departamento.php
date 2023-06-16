<?php
class ci_departamento extends toba_ci
{
    function ini()
    {
        header('Content-Type: application/json');
        $sql="select * from departamento";         
        $res=toba::db('designa')->consultar($sql);
        foreach ($res as $key => $value) {
                $res[$key]['descripcion']= utf8_encode($res[$key]['descripcion']);
            }
        //var_dump($res);
        echo( json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        exit;
    }
}
?>