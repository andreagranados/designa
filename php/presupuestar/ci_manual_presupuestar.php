<?php
/*buscar el archivo pdf y descargarlo */
header("Content-type:application/pdf");
// It will be called downloaded.pdf
header("Content-Disposition:attachment;filename='Instructivo_Presupuestar.pdf'");
// The PDF source is in original.pdf
readfile("./Instructivo_Presupuestar.pdf");
?>