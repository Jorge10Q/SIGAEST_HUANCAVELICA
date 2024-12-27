<?php
include("../include/conexion.php");
include("../include/busquedas.php");
include("../include/funciones.php");

include ("include/verificar_sesion_secretaria.php");

if (!verificar_sesion($conexion)) {
  echo "<script>
                alert('Error Usted no cuenta con permiso para acceder a esta página');
                window.location.replace('index.php');
    		</script>";
} else {

  $id_docente_sesion = buscar_docente_sesion($conexion, $_SESSION['id_sesion'], $_SESSION['token']);

  $id_estudiante = $_GET['id'];

  $b_estudiante = buscarEstudianteById($conexion, $id_estudiante);
  $estudiante = mysqli_fetch_array($b_estudiante);
  
  $res_programa = buscarCarrerasById($conexion, $estudiante['id_programa_estudios']);
  $programa=mysqli_fetch_array($res_programa);

  $res_info_socio = buscarInfoSocioByIdEstudiante($conexion, $id_estudiante);
  $existe_registro = mysqli_num_rows($res_info_socio);
  $info_socio = mysqli_fetch_array($res_info_socio);

  if(!$existe_registro){
    echo "<script>
        alert('El alumno aún no registró su información socioeconómica.');
        window.history.back();
        </script>";
    exit;
  }

    $fecha_nacimiento = $estudiante['fecha_nac'];

    // Convertir la fecha de nacimiento en un objeto DateTime
    $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);

    // Fecha actual
    $fecha_actual = new DateTime();

    // Calcular la diferencia entre la fecha actual y la fecha de nacimiento
    $diferencia = $fecha_nacimiento_obj->diff($fecha_actual);

    // Obtener la edad del estudiante
    $edad = $diferencia->y;

?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="es-ES">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	  
    <title>Docente <?php include ("../include/header_title.php"); ?></title>
    <!--icono en el titulo-->
    <link rel="shortcut icon" href="../img/favicon.ico">
    <!-- Bootstrap -->
    <link href="../Gentella/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../Gentella/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../Gentella/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../Gentella/vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- Datatables -->
    <link href="../Gentella/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="../Gentella/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="../Gentella/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="../Gentella/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="../Gentella/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../Gentella/build/css/custom.min.css" rel="stylesheet">

  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <!--menu-->
          <?php 
          include ("include/menu_secretaria.php"); ?>
            <!-- page content -->
            <div class="right_col" role="main">
                <div class="">
                    <div class="clearfix"></div>
                    <div class="row">
						<center><h4><b>INFORMACIÓN SOCIOECONÓMICA</b></h4></center>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <h5><b>1. DATOS GENERALES</b></h5>
                                    <hr>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">Apellidos y nombres:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" name="ap_nombres" readonly
                                            value="<?php echo $estudiante['apellidos_nombres'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">Correo electrónico:
                                        </label>
                                        <div class="">
                                            <input type="email" class="form-control" name="correo" readonly
                                            value="<?php echo $estudiante['correo'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">N° de celular:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" name="celular" readonly
                                            value="<?php echo $estudiante['telefono'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">Domicilio:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" name="domicilio" readonly
                                            value="<?php echo $estudiante['direccion'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">Fecha de nacimiento:
                                        </label>
                                        <div class="">
                                            <input type="date" class="form-control" name="fecha_nac" readonly
                                            value="<?php echo $estudiante['fecha_nac'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 col-sm-6 col-xs-6">
                                        <label class="control-label">Edad:
                                        </label>
                                        <div class="">
                                            <input type="number" class="form-control" name="edad" readonly
                                            value="<?php echo $edad; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 col-sm-6 col-xs-6">
                                        <label class="control-label">Género:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php if($estudiante['id_genero']==1){
                                                echo "Masculino";
                                            }else{
                                                echo "Femenino";
                                            } ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <h5><b>2. ASPECTO FAMILIAR</b></h5>
                                    <hr>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Con quién o quienes vives?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['familiares'] ?>">
                                        </div>
                                        <br>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Cuántas personas viven contigo?:
                                        </label>
                                        <div class="">
                                            <input type="number" class="form-control" name="cant_fami" readonly
                                            value="<?php echo $info_socio['cant_familiares'] ?>">
                                        </div>
                                        <br>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Qué problemas presentan en el hogar?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['problema_hogar'] ?>">
                                        </div>
                                        <br>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Con qué familiar tienes mayor confinaza?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['familiar_confianza'] ?>">
                                        </div>
                                        <br><br><br><br>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <h5><b>3. ASPECTO LABORAL</b></h5>
                                    <hr>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Quiénes trabajan en tu familia?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['trabajo_familiares'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Cuánto de ingreso mensual persiven en casa?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['rango_ingreso'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿En que priorizan los ingresos?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['ingreso_prio'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Si usted trabaja, que tipo de tabajo realiza?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['tipo_trabajo'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿En que gastas lo que te pagan?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['gasto_trabajo'] ?>">
                                        </div>
                                        <br><br><br><br>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                <h5><b>4. ASPECTO VIVIENDA</b></h5>
                                <hr>
                                    <div class="">
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">Vives en:
                                            </label>
                                            <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['vivienda'] ?>">
                                        </div>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">De que esta construido la vivienda:
                                            </label>
                                            <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['tipo_vivienda'] ?>">
                                        </div>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">La vivienda tiene servicios de:
                                            </label>
                                            <div class="">
                                                <input type="text" class="form-control" readonly
                                                value="<?php echo $info_socio['servicios'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="">
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">En casa tienes:
                                            </label>
                                            <div class="">
                                                <input type="text" class="form-control" readonly
                                                value="<?php echo $info_socio['equipos_electronicos'] ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">En casa cuentan con vehiculo:
                                            </label>
                                            <div class="">
                                                <input type="text" class="form-control" readonly
                                                value="<?php echo $info_socio['vehiculos'] ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            <label class="control-label">¿A cuantos minutos vives del instituto?:
                                            </label>
                                            <div class="">
                                                <input type="text" class="form-control" readonly
                                                value="<?php echo $info_socio['minutos_casa_insti'] ?>">
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <h5><b>5. ASPECTO SALUD</b></h5>
                                    <hr>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Presentas alguna dificultad física?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['dificultad_fisica'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Tienes seguro de salud?:
                                        </label>
                                        <div class="">
                                            <input type="text" class="form-control" readonly
                                            value="<?php echo $info_socio['seguro_salud'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                        <label class="control-label">¿Sufres de alguna enferemedad que requiera tratemiento?:
                                        </label>
                                        <div class="">
                                            <textarea class="form-control" name="enfermedad_descripcion" id="" rows="5" readonly>
                                            <?php echo trim($info_socio['enfermedad']) ?>
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        <!-- /page content -->

         <!-- footer content -->
         <?php
        include ("../include/footer.php"); 
        ?>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
   <script src="../Gentella/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../Gentella/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../Gentella/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../Gentella/vendors/nprogress/nprogress.js"></script>
    <!-- iCheck -->
    <script src="../Gentella/vendors/iCheck/icheck.min.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../Gentella/build/js/custom.min.js"></script>

     <?php mysqli_close($conexion); ?>
  </body>
</html>
<?php
}
