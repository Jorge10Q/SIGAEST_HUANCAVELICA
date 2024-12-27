<?php

include "../../include/conexion.php";
include "../../include/busquedas.php";
include "../../include/funciones.php";
include("../include/verificar_sesion_secretaria.php");
if (!verificar_sesion($conexion)) {
    echo "<script>
				  alert('Error Usted no cuenta con permiso para acceder a esta página');
				  window.location.replace('../login/');
			  </script>";
} else {



    $id_periodo_acad = $_SESSION['periodo'];
    $id_est = $_POST['id_est'];
    $carrera = $_POST['carrera_m'];
    $semestre = $_POST['semestre'];
    $hoy = date("Y-m-d");
    $detalle_matricula = explode(",", $_POST['mat_relacion']);

    //VERIFICAMOS QUE EL ESTUDIANTE NO ESTE MATRICULADO EN ESTE PERIODO
    $busc_matricula = buscarMatriculaByEstudiantePeriodo($conexion, $id_est, $id_periodo_acad);
    $cont_b_matricula = mysqli_num_rows($busc_matricula);

    if ($cont_b_matricula > 0) {
        echo "<script>
			alert('El estudiante ya esta matriculado en este periodo Académico');
			window.history.back();
		</script>
		";
    } else {


        //REGISTRAMOS LA MATRICULA
        $reg_matricula = "INSERT INTO matricula (id_periodo_acad, id_programa_estudio, id_semestre, id_estudiante,licencia, fecha_reg) VALUES ('$id_periodo_acad','$carrera','$semestre','$id_est','','$hoy')";
        $ejecutar_reg_matricula = mysqli_query($conexion, $reg_matricula);
        //buscamos el ultimo registro de la matricula
        $id_matricula = mysqli_insert_id($conexion);

        //actualizamos semestre del estudiante
        $actualizar = "UPDATE estudiante SET id_semestre='$semestre' WHERE id = '$id_est'";
        mysqli_query($conexion, $actualizar);

        //recorremos el array del detalle para buscar datos complementarios y registrar el detalle y las calificaciones
        foreach ($detalle_matricula as $valor) {


            //buscaremos el id de la unidad didactica en la programacion de unidades didacticas
            $busc_prog = buscarProgramacionById($conexion, $valor);
            $res_b_prog = mysqli_fetch_array($busc_prog);
            $id_ud = $res_b_prog['id_unidad_didactica'];

            //buscamos la cantidad de matriculados para ingresar el orden correcto
            $b_cant_mat_detalle_mat = buscarDetalleMatriculaByIdProgramacion($conexion, $valor);
            $cont_r_b_cant_mat_det_mat = mysqli_num_rows($b_cant_mat_detalle_mat);
            $new_orden = $cont_r_b_cant_mat_det_mat + 1;

            //REGISTRAMOS EL DETALLE DE LA MATRICULA
            $reg_det_mat =  "INSERT INTO detalle_matricula_unidad_didactica (id_matricula, orden, id_programacion_ud, recuperacion, mostrar_calificacion) VALUES ('$id_matricula','$new_orden','$valor','',0)";
            $ejecutar_reg_det_mat = mysqli_query($conexion, $reg_det_mat);

            //buscamos el ultimo registro de detalle matricula
            $id_detalle_matricula = mysqli_insert_id($conexion);

            //buscamos la cantidad de indicadores tenemos para calcular el ponderado
            $cont_ind = 0;
            $busc_capacidad_p = buscarCapacidadesByIdUd($conexion, $id_ud);
            while ($res_b_capacidad_p = mysqli_fetch_array($busc_capacidad_p)) {
                //buscamos indicadores de logro de capacidad
                $b_indicador_p = buscarIndicadorLogroCapacidadByIdCapacidad($conexion, $res_b_capacidad_p['id']);
                while ($res_b_capacidad_p = mysqli_fetch_array($b_indicador_p)) {
                    $cont_ind += 1;
                }
            }
            //buscamos la capacidad de la unidad didactica
            $busc_capacidad = buscarCapacidadesByIdUd($conexion, $id_ud);

            $orden = 1; //orden en el que inicia las calificaciones

            while ($res_b_capacidad = mysqli_fetch_array($busc_capacidad)) {
                $id_capacidad = $res_b_capacidad['id'];

                // buscar indicadores de logro de capacidad para saber cuantos calificaciones crearemos
                $b_indicador = buscarIndicadorLogroCapacidadByIdCapacidad($conexion, $id_capacidad);

                while ($res_b_capacidad = mysqli_fetch_array($b_indicador)) {
                    $ponderado_calificaciones = round(100 / $cont_ind);
                    //REGISTRAMOS LAS CALIFICACION SEGUN LA CANTIDAD DE INDICADORES DE LOGRO
                    $reg_calificacion = "INSERT INTO calificaciones (id_detalle_matricula, nro_calificacion, ponderado, mostrar_calificacion) VALUES ('$id_detalle_matricula','$orden','$ponderado_calificaciones',0)";
                    $ejecutar_reg_calificacion = mysqli_query($conexion, $reg_calificacion);
                    
                    $id_calificacion = mysqli_insert_id($conexion);
                    $ponderado_evaluacion = round(100 / 3);
                    //registramos las evaluaciones para las calificaciones - se crearan 3 --> conceptual, procedimental y actitudinal
                    for ($i = 1; $i <= 3; $i++) {
                        if ($i == 1) {
                            $det_eva = "Conceptual";
                        };
                        if ($i == 2) {
                            $det_eva = "Procedimental";
                        };
                        if ($i == 3) {
                            $det_eva = "Actitudinal";
                        };
                        $reg_evaluacion = "INSERT INTO evaluacion (id_calificacion, detalle, ponderado) VALUES ('$id_calificacion','$det_eva','$ponderado_evaluacion')";
                        $ejecutar_reg_evaluacion = mysqli_query($conexion, $reg_evaluacion);

                        $id_evaluacion = mysqli_insert_id($conexion);
                        //buscamos matriculas en la programacion de unidad didactica para saber cuantos criterios agregar 
                        // si no existen matriculas por defecto agregara 5 criterios, haya matriculas, contara los criterios de una de las matriculas
                        $cant_crit_eva = buscar_cantidad_criterios_programacion($conexion, $valor, $det_eva, $orden);
                        

                        $ponderado_c_evaluacion = round(100 / $cant_crit_eva);
                        // registramos los 5 criterios de evaluacion para cada evaluacion
                        //for ($j = 1; $j <= $cant_crit_eva; $j++) {
                            $reg_criterio_evaluacion = "INSERT INTO criterio_evaluacion (id_evaluacion, orden, detalle, ponderado, calificacion) VALUES ('$id_evaluacion',1,'','$ponderado_c_evaluacion','')";
                            $ejecutar_reg_criterio_evaluacion = mysqli_query($conexion, $reg_criterio_evaluacion);
                        //}
                    }
                    $orden = $orden + 1;
                }
            }
            //procedemos a crear el registro de asistencia para cada sesion de la ud programada
            $id_programacion = $valor;
            //buscar silabo
            $b_silabo = buscarSilaboByIdProgramacion($conexion, $id_programacion);
            $r_b_silabo = mysqli_fetch_array($b_silabo);
            $id_silabo = $r_b_silabo['id'];
            //buscar programacion de actividades de silabo
            $b_prog_act_silabo = buscarProgActividadesSilaboByIdSilabo($conexion, $id_silabo);

            while ($r_b_prog_act_silabo = mysqli_fetch_array($b_prog_act_silabo)) {
                $id_prog_act_silabo = $r_b_prog_act_silabo['id'];
                //buscamos las sesiones de aprendizaje para generar las asistencias
                $b_sesion_apre = buscarSesionByIdProgramacionActividades($conexion, $id_prog_act_silabo);
                while ($r_b_sesion_apre = mysqli_fetch_array($b_sesion_apre)) {
                    //generamos asistencia para cada sesion de aprendizaje
                    $id_sesion = $r_b_sesion_apre['id'];
                    $r_asistencia = "INSERT INTO asistencia (id_sesion_aprendizaje, id_estudiante, asistencia) VALUES ('$id_sesion','$id_est','')";
                    $ejecutar_r_asistencia = mysqli_query($conexion, $r_asistencia);
                }
            }



            echo "<script>
                alert('Matricula Exitosa');
                window.location= '../matricula.php'
    			</script>";
        }
    }
}
