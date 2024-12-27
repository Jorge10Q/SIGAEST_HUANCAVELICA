
<?php
include '../../../include/conexion.php';
include "../../../include/busquedas.php";
include "../../../include/funciones.php";

$generos_postulantes = array();

$pos_genero = ['Masculino', 'Femenino'];

for ($i = 0; $i < count($pos_genero); $i++){

    $res_b_pos_mod = buscarTodosPostulantesPorGenero($conexion, $i);   

    $cantidad_postulantes = mysqli_num_rows($res_b_pos_mod);

    $generos_postulantes[$pos_genero[$i]] = $cantidad_postulantes;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($generos_postulantes);