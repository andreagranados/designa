<?php
/*buscar el archivo pdf y descargarlo */
header("Content-type:application/pdf");
// It will be called downloaded.pdf
header("Content-Disposition:attachment;filename=Mocovi_Designaciones_PI.pdf");
// The PDF source is in original.pdf
readfile("./Mocovi_Designaciones_PI.pdf");
?>