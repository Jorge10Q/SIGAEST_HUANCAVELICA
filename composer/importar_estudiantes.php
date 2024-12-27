<?php
include "../include/busquedas.php";
include "../include/conexion.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$inputFileName = $_FILES['estudiantes']['tmp_name'];
$fileType = $_FILES['estudiantes']['type'];

if ($fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    /**  Identify the type of $inputFileName  **/
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
    /**  Create a new Reader of the type that has been identified  **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    /**  Load $inputFileName to a Spreadsheet Object  **/
    $spreadsheet = $reader->load($inputFileName);

    // Seleccionar la primera hoja del documento
    $hoja = $spreadsheet->getActiveSheet()->toArray();

    $estudiantes = [];

    foreach ($hoja as $row){
        $dni = $row[0];
        $estudiante = $row[1];
        $genero = $row[2];//numero
        $fecha_nac = $row[3];
        $direccion = $row[4];
        $correo = $row[5];
        $telefono = $row[6];
        $anio_ingreso = $row[7]; 
        $seccion = $row[8];
        $semestre = $row[9];//numero
        $discapacidad = $row[10];
        $programa = $row[11];//numero
        $egresado = $row[12];
        

        if ($dni == null || $estudiante == null || $correo == null || $anio_ingreso == null || $semestre == null || $programa == null || $egresado == null){
            $observacion = "Existe(n) campo(s) vacio(s)";
        }else if (in_array($dni, array_column($estudiantes,'dni'))){
            $observacion = "El dni repite en el archivo";
        }else{
            if($dni == "dni **"){$observacion = "Observaciones";
            }else{
                $existe = buscarEstudianteByDni($conexion,$dni); 
                $cont = mysqli_num_rows($existe);
                if($cont == 0){
                    $pass = $dni;
                    $pass_secure = password_hash($pass, PASSWORD_DEFAULT);
                    $genero = intval($genero);
                    $semestre = intval($semestre);
                    $programa = intval($programa);
                    $insertar = "INSERT INTO estudiante (dni, apellidos_nombres, id_genero, fecha_nac, direccion, correo, telefono, anio_ingreso, egresado, id_programa_estudios, id_semestre, seccion, discapacidad, password, reset_password, token_password) 
                    VALUES ('$dni','$estudiante','$genero', '$fecha_nac', '$direccion', '$correo', '$telefono', '$anio_ingreso', '$egresado', '$programa', '$semestre', '$seccion', '$discapacidad', '$pass_secure', 0, '')";
                    $ejecutar_insetar = mysqli_query($conexion, $insertar);
                    if ($ejecutar_insetar) {
                        $observacion = "Ninguna";
                    }else{
                        $observacion = "Error desconocido";
                    }
                }else{
                    $observacion = "Ya se encuentra registrado en la base de datos";
                }       
            }

        }
        $estudiantes[] = [
            'dni' => $dni,
            'estudiante' => $estudiante,
            'genero' => $genero,
            'fecha_nac' => $fecha_nac,
            'direccion' => $direccion,
            'correo' => $correo,
            'telefono' => $telefono,
            'anio_ingreso' => $anio_ingreso,
            'seccion' => $seccion,
            'semestre' => $semestre,
            'discapacidad' => $discapacidad,
            'programa' => $programa,
            'egresado' => $egresado,
            'observacion' => $observacion
        ];

    }


    $newXlsx = new Spreadsheet();
    $newXlsx->getActiveSheet()
        ->fromArray(
            $estudiantes,  // The data to set
            NULL,        // Array values with this value will not be set
            'A1'         // Top left coordinate of the worksheet range where
                        //    we want to set these values (default is A1)
        );

    $writer = new Xlsx($newXlsx);

    // Definir las cabeceras para forzar la descarga del archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="reporte_migraci¨®n.xlsx"');
    header('Cache-Control: max-age=0');

    // Enviar el archivo al navegador
    $writer->save('php://output');
    exit;
}else{
    echo "<script>
					alert('Se ha subido un documento que no es de tipo Excel. Porfavor suba un documento adecuado!');
					window.history.back();
				</script>
			";
}
?>