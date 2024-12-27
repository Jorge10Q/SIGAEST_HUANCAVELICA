<?php
include "../../include/conexion.php";
include "../../include/busquedas.php";
include "../../include/funciones.php";
include("../include/verificar_sesion_caja.php");
if (!verificar_sesion($conexion)) {
	echo "<script>
				  alert('Error Usted no cuenta con permiso para acceder a esta página');
				  window.location.replace('../login/');
			  </script>";
  }else {


  
$id = $_POST['id'];
$codigo = $_POST['codigo'];
$concepto = $_POST['concepto'];
$monto = floatval($_POST['monto']);
$unidad = $_POST['unidad'];

	$insertar = "UPDATE concepto_ingreso SET concepto='$concepto',monto=$monto,codigo= '$codigo',unidad= '$unidad' WHERE id = $id";
	$ejecutar_insetar = mysqli_query($conexion, $insertar);
	if ($ejecutar_insetar) {
			echo "<script>
                alert('Actualización Existosa');
                window.location= '../concepto_ingresos.php'
    			</script>";
	}else{
		echo "<script>
			alert('Error al registrar');
			window.history.back();
				</script>
			";
	};

mysqli_close($conexion);

}