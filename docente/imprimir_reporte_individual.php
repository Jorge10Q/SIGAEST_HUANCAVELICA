<?php
include("../include/conexion.php");
include("../include/busquedas.php");
include("../include/funciones.php");
include 'include/verificar_sesion_coordinador.php';
if (!verificar_sesion($conexion)) {
    echo "<script>
                alert('Error Usted no cuenta con permiso para acceder a esta página');
                window.location.replace('index.php');
    		</script>";
} else {

    $id_docente_sesion = buscar_docente_sesion($conexion, $_SESSION['id_sesion'], $_SESSION['token']);
    $b_docente = buscarDocenteById($conexion, $id_docente_sesion);
    $r_b_docente = mysqli_fetch_array($b_docente);

    $id_est = $_POST['id_est'];
    $per_select = $_SESSION['periodo'];

    //buscar matricula de estudiante
    $b_mat = buscarMatriculaByEstudiantePeriodo($conexion, $id_est, $per_select);
    $r_b_mat = mysqli_fetch_array($b_mat);
    $id_mat_est = $r_b_mat['id'];

    $b_estudiante = buscarEstudianteById($conexion, $id_est);
    $r_b_estudiante = mysqli_fetch_array($b_estudiante);

    

    $b_det_mat = buscarDetalleMatriculaByIdMatricula($conexion, $id_mat_est);
    $cant_ud_mat = mysqli_num_rows($b_det_mat);
    $cont_ind_capp = 0;
    while ($r_b_det_mat = mysqli_fetch_array($b_det_mat)) {
        $id_prog = $r_b_det_mat['id_programacion_ud'];
        $b_prog_ud = buscarProgramacionById($conexion, $id_prog);
        $r_b_prog = mysqli_fetch_array($b_prog_ud);
        $id_udd = $r_b_prog['id_unidad_didactica'];
        //BUSCAR UD
        $b_uddd = buscarUdById($conexion, $id_udd);
        $r_b_udd = mysqli_fetch_array($b_uddd);
        //buscar capacidad
        $cont_ind_logro_cap_ud = 0;
        $b_cap_ud = buscarCapacidadesByIdUd($conexion, $id_udd);
        while ($r_b_cap_ud = mysqli_fetch_array($b_cap_ud)) {
            $id_cap_ud = $r_b_cap_ud['id'];
            // buscar indicadores de capacidad
            $b_ind_l_cap_ud = buscarIndicadorLogroCapacidadByIdCapacidad($conexion, $id_cap_ud);
            $cant_id_cap_ud = mysqli_num_rows($b_ind_l_cap_ud);
            $cont_ind_logro_cap_ud += $cant_id_cap_ud;
        }
        $cont_ind_capp += $cont_ind_logro_cap_ud;
    }
    $total_columnas = $cont_ind_capp+ $cant_ud_mat+ 3;
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reportes<?php include("../include/header_title.php"); ?></title>
        <!--icono en el titulo-->
        <link rel="shortcut icon" href="../img/favicon.ico">


        <style>
            p.verticalll {
                /* idéntico a rotateZ(45deg); */

                writing-mode: vertical-lr;
                transform: rotate(180deg);

            }

            .nota_input {
                width: 3em;
            }

            .text_s {
                font-size: 10px;
            }
        </style>
    </head>

    <body class="nav-md">

        <h2 align="center"><b>REPORTE INDIVIDUAL - <?php echo $r_b_estudiante['dni'] . " - " . $r_b_estudiante['apellidos_nombres']; ?></b></h2>

        <table BORDER="1" CELLPADDING="3" CELLSPACING="0" style="width:100%" class="text_s">
            <thead>
                <tr>
                    <th colspan="<?php echo $total_columnas; ?>" bgcolor="#CCC">
                        <center>CALIFICACIONES - UNIDADES DIDACTICAS</center>
                    </th>
                </tr>

                <tr>
                    <?php
                    $b_det_mat = buscarDetalleMatriculaByIdMatricula($conexion, $id_mat_est);
                    while ($r_b_det_mat = mysqli_fetch_array($b_det_mat)) {
                        $id_prog = $r_b_det_mat['id_programacion_ud'];
                        $b_prog_ud = buscarProgramacionById($conexion, $id_prog);
                        $r_b_prog = mysqli_fetch_array($b_prog_ud);
                        $id_udd = $r_b_prog['id_unidad_didactica'];
                        //BUSCAR UD
                        $b_uddd = buscarUdById($conexion, $id_udd);
                        $r_b_udd = mysqli_fetch_array($b_uddd);
                        //buscar capacidad
                        $cont_ind_logro_cap_ud = 0;
                        $b_cap_ud = buscarCapacidadesByIdUd($conexion, $id_udd);
                        while ($r_b_cap_ud = mysqli_fetch_array($b_cap_ud)) {
                            $id_cap_ud = $r_b_cap_ud['id'];
                            // buscar indicadores de capacidad
                            $b_ind_l_cap_ud = buscarIndicadorLogroCapacidadByIdCapacidad($conexion, $id_cap_ud);
                            $cant_id_cap_ud = mysqli_num_rows($b_ind_l_cap_ud);
                            $cont_ind_logro_cap_ud += $cant_id_cap_ud;
                        }

                    ?>
                        <th colspan="<?php echo $cont_ind_logro_cap_ud; ?>">
                            <p class="verticalll"><center><?php echo $r_b_udd['descripcion']; ?></center></p>
                        </th>
                        <th>
                            <p class="verticalll">PROMEDIO</p>
                        </th>
                    <?php
                    }
                    ?>
                    <th>
                        <p class="verticalll">Ptj. Total</p>
                    </th>
                    <th>
                        <p class="verticalll">Ptj. Créditos</p>
                    </th>
                    <th>
                        <p>
                            <center>CONDICIÓN</center>
                        </p>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php

                //buscar estudiante para su id
                $b_est = buscarEstudianteById($conexion, $id_est);
                $r_b_est = mysqli_fetch_array($b_est);

                ?>
                <tr>


                    <?php
                    //buscar si estudiante esta matriculado en una unidad didactica
                    $b_det_mat = buscarDetalleMatriculaByIdMatricula($conexion, $id_mat_est);
                    $suma_califss = 0;
                    $suma_ptj_creditos = 0;
                    $cont_ud_desaprobadas = 0;
                    while ($r_b_det_mat = mysqli_fetch_array($b_det_mat)) {
                        $id_det_mat = $r_b_det_mat['id'];


                        //echo "<td>SI</td>";

                        //buscar las calificaciones
                        $b_calificaciones = buscarCalificacionByIdDetalleMatricula($conexion, $id_det_mat);

                        $suma_calificacion = 0;
                        $cont_calif = 0;
                        while ($r_b_calificacion = mysqli_fetch_array($b_calificaciones)) {

                            $id_calificacion = $r_b_calificacion['id'];
                            //buscamos las evaluaciones
                            $suma_evaluacion = calc_evaluacion($conexion, $id_calificacion);

                            if ($suma_evaluacion != 0) {
                                $cont_calif += 1;
                                $suma_calificacion += $suma_evaluacion;
                                $suma_evaluacion = round($suma_evaluacion);

                                if ($suma_evaluacion > 12) {
                                    echo '<th><center><font color="blue">' . $suma_evaluacion . '</font></center></th>';
                                    //echo '<th><center><input type="number" class="nota_input" style="color:blue;" value="' . $calificacion_final . '" min="0" max="20" disabled></center></th>';
                                } else {
                                    echo '<th><center><font color="red">' . $suma_evaluacion . '</font></center></th>';
                                    //echo 
                                }
                            } else {
                                $suma_evaluacion = "";
                                echo '<th></th>';
                            }
                        }


                        if ($cont_calif > 0) {
                            $calificacion = round($suma_calificacion / $cont_calif);
                        } else {
                            $calificacion = round($suma_calificacion);
                        }
                        if ($calificacion != 0) {
                            $calificacion = round($calificacion);
                        } else {
                            $calificacion = "";
                        }
                        //buscamos si tiene recuperacion
                        if ($r_b_det_mat['recuperacion'] != '') {
                            $calificacion = $r_b_det_mat['recuperacion'];
                        }

                        if ($calificacion > 12) {
                            echo '<th align="center" bgcolor="#BEBBBB"><font color="blue">' . $calificacion . '</font></th>';
                        } else {
                            echo '<th align="center" bgcolor="#BEBBBB"><font color="red">' . $calificacion . '</font></th>';
                            $cont_ud_desaprobadas += 1;
                        }
                        if (is_numeric($calificacion)) {
                            $suma_califss += $calificacion;
                            $suma_ptj_creditos += $calificacion * $r_bb_ud['creditos'];
                        } else {
                            $suma_ptj_creditos += 0 * $r_bb_ud['creditos'];
                        }
                    }
                    echo '<td align="center" ><font color="black">' . $suma_califss . '</font></td>';
                    echo '<td align="center" ><font color="black">' . $suma_ptj_creditos . '</font></td>';
                    if ($cont_ud_desaprobadas == 0) {
                        echo '<td align="center" ><font color="black">Promovido</font></td>';
                    } elseif ($cont_ud_desaprobadas <= $min_ud_desaprobar) {
                        echo '<td align="center" ><font color="black">Repite U.D. del Módulo Profesional</font></td>';
                    } else {
                        echo '<td align="center" ><font color="black">Repite el Módulo Profesional</font></td>';
                    }
                    ?>
                </tr>


            </tbody>
        </table>
        <br>
        <table BORDER="1" CELLPADDING="3" CELLSPACING="0" style="width:100%" class="text_s">
            <thead>
                <tr>
                    <th colspan="34" bgcolor="#CCC">
                        <center>ASISTENCIA</center>
                    </th>
                </tr>
                <tr>
                    <th>UNIDAD DIDÁCTICA</th>


                    <?php
                    for ($i = 1; $i <= 16; $i++) {
                        echo "<th><center>Semana <br>" . $i . "</center></th>";
                    }
                    ?>
                    <th>Inasistencia</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $b_detalle_mat = buscarDetalleMatriculaByIdMatricula($conexion, $id_mat_est);
                while ($r_b_det_mat = mysqli_fetch_array($b_detalle_mat)) {
                    $id_prog = $r_b_det_mat['id_programacion_ud'];
                    echo "<tr>";
                    $b_prog_ud = buscarProgramacionById($conexion, $id_prog);
                    $r_b_prog = mysqli_fetch_array($b_prog_ud);

                    $b_ud = buscarUdById($conexion, $r_b_prog['id_unidad_didactica']);
                    $r_b_ud = mysqli_fetch_array($b_ud);


                    $b_silabo = buscarSilaboByIdProgramacion($conexion, $id_prog);
                    $r_b_silabo = mysqli_fetch_array($b_silabo);
                    $b_prog_act = buscarProgActividadesSilaboByIdSilabo($conexion, $r_b_silabo['id']);

                    echo "<td>" . $r_b_ud['descripcion'] . "</td>";
                    $cont_inasistencia = 0;
                    $cont_asis = 0;
                    while ($res_b_prog_act = mysqli_fetch_array($b_prog_act)) {
                        // buscamos la sesion que corresponde
                        $id_act = $res_b_prog_act['id'];
                        $b_sesion = buscarSesionByIdProgramacionActividades($conexion, $id_act);
                        while ($r_b_sesion = mysqli_fetch_array($b_sesion)) {
                            $b_asistencia = buscarAsistenciaBySesionAndEstudiante($conexion, $r_b_sesion['id'], $id_est);
                            $r_b_asistencia = mysqli_fetch_array($b_asistencia);
                            $cont_asis += mysqli_num_rows($b_asistencia);

                            if ($r_b_asistencia['asistencia'] == "P") {
                                echo "<td><center><font color='blue'>" . $r_b_asistencia['asistencia'] . "</font></center></td>";
                            } elseif ($r_b_asistencia['asistencia'] == "F") {
                                echo "<td><center><font color='red'>" . $r_b_asistencia['asistencia'] . "</font></center></td>";
                                $cont_inasistencia += 1;
                            } else {
                                echo "<td></td>";
                            }
                        }
                    }
                    if ($cont_inasistencia > 0) {
                        $porcent_ina = $cont_inasistencia * 100 / $cont_asis;
                    } else {
                        $porcent_ina = 0;
                    }
                    if (round($porcent_ina) > 29) {
                        echo "<td><center><font color='red'>" . round($porcent_ina) . "%</font></center></td>";
                    } else {
                        echo "<td><center><font color='blue'>" . round($porcent_ina) . "%</font></center></td>";
                    }

                    echo "</tr>";
                }

                ?>
            </tbody>
        </table>
        <?php echo "<script>
			window.print();
      setTimeout(function(){ 
        window.close();
    }, 3000);
		</script>
	"; ?>


    </body>

    </html>
<?php }
