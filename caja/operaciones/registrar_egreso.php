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

	$id_docente_sesion = buscar_docente_sesion($conexion, $_SESSION['id_sesion'], $_SESSION['token']);
	$usuario = buscarDocenteById($conexion, $id_docente_sesion);
	$usuario = mysqli_fetch_array($usuario);
	$usuario = $usuario['apellidos_nombres'];

	$empresa = $_POST['ruc'];
	$tipo = $_POST['tipo'];
	$concepto = $_POST['concepto'];
	$serie = $_POST['serie'];
	$numero = $serie. "-" .$_POST['numero'];
	$receptor = $_POST['usuario'];
	$fecha = $_POST['fecha'];
	$monto = floatval($_POST['monto']);


	$insertar = "INSERT INTO `egresos`(`empresa`, `ruc`,`concepto`,`tipo_comprobante`, `comprobante`, `fecha_pago`,`fecha_registro`,`monto_total`,`estado`,`responsable`) 
	VALUES ('$receptor','$empresa','$concepto','$tipo','$numero','$fecha', CURRENT_TIMESTAMP() ,'$monto', 'PAGADO','$usuario')";
	$ejecutar_insetar = mysqli_query($conexion, $insertar);
	if ($ejecutar_insetar) {
			echo "<script>
				alert('Registro Existoso');
				window.location= '../movimientos.php'
				</script>";
	}else{
		echo "<script>
			alert('Error al registrar, es probable que el codigo ya esté registrado');
			window.history.back();
				</script>
			";
	};
	
	mysqli_close($conexion);

}