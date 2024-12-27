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


  

$codigo = $_POST['codigo'];
$concepto = $_POST['concepto'];
$monto = floatval($_POST['monto']);
$unidad = $_POST['unidad'];

	$insertar = "INSERT INTO `concepto_ingreso`(`concepto`, `monto`, `codigo`, `unidad`) VALUES ('$concepto',$monto,'$codigo','$unidad')";
	$ejecutar_insetar = mysqli_query($conexion, $insertar);
	if ($ejecutar_insetar) {
			echo "<script>
                alert('Registro Existoso');
                window.location= '../concepto_ingresos.php'
    			</script>";
	}else{
		echo "<script>
			alert('Error al registrar, por favor verifique sus datos');
			window.history.back();
				</script>
			";
	};

mysqli_close($conexion);

}