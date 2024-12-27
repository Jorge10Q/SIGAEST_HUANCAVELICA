<?php
include("../include/conexion.php");
include("../include/busquedas.php");
include("../include/funciones.php");
include("../empresa/include/consultas.php");

include("include/verificar_sesion_administrador.php");

if (!verificar_sesion($conexion)) {
  echo "<script>
          window.location.replace('../docente/login/');
    		</script>";
} else {

  $id_docente_sesion = buscar_docente_sesion($conexion, $_SESSION['id_sesion'], $_SESSION['token']);
  $b_docente = buscarDocenteById($conexion, $id_docente_sesion);
  $r_b_docente = mysqli_fetch_array($b_docente);


?>
  <!DOCTYPE html>
  <html lang="es">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio<?php include("../include/header_title.php"); ?></title>
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
    <!-- bootstrap-progressbar -->
    <link href="../Gentella/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../Gentella/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet" />
    <!-- bootstrap-daterangepicker -->
    <link href="../Gentella/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="../Gentella/build/css/custom.min.css" rel="stylesheet">
  </head>
  <style>
    .img_fondo {
      width: 100%;
      height: 400px;
      object-fit: cover;
      background-repeat: no-repeat;
      background-position: center;
    }
  </style>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php
        include("include/menu_administrador.php"); ?>
        <!-- page content -->
        <div class="right_col main" role="main">
          <!-- top tiles -->
          <div class="row tile_count">
            <div class="col-md-8 col-sm-7 col-xs-12 tile_count">
              <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count blur">
                <span class="count_top green"><i class="fa fa-child"></i> Empresas</span>
                <div class="count"><?php
                                    $b_est = buscarEmpresas($conexion);
                                    $count_est = mysqli_num_rows($b_est);
                                    echo $count_est; ?></div>
                <span class="count_bottom"><a href="empresas.php"><i class="green">Ver</i></a></span>
              </div>

              <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count blur">
                <span class="count_top green"><i class="fa fa-child"></i> Convocatorias</span>
                <div class="count"><?php
                                    $b_est = buscarOfertaLaboralEmpresa($conexion);
                                    $count_est = mysqli_num_rows($b_est);
                                    $b_est_2 = buscarOfertaLaboralAdministrador($conexion);
                                    $count_est_2 = mysqli_num_rows($b_est_2);
                                    echo $count_est + $count_est_2; ?></div>
                <span class="count_bottom"><a href="convocatorias.php"><i class="green">Ver</i></a></span>
              </div>
            </div>
            <div class="col-md-4 col-sm-5 col-xs-12">
              <?php
              //anuncios o encuestas
              $res_anuncion = buscarAnunciosActivos($conexion);
              $cantidad_anuncio = mysqli_num_rows($res_anuncion);
              //capacitaciones
              $res_capacitaciones = buscarCapacitacionesActivos($conexion);
              $cantidad_capacitaciones = mysqli_num_rows($res_capacitaciones);

              $mostrar = true;
              if ($cantidad_anuncio != 0) {
                while ($anuncio = mysqli_fetch_array($res_anuncion)) {
                  $anuncio_cargo = $anuncio['usuarios'];
                  $cargos_seleccionados = explode('-', $anuncio_cargo);
                  if (in_array($r_b_docente['id_cargo'], $cargos_seleccionados)) {
                    $mostrar = false;
              ?>
                    <div class="row">
                      <div>
                        <div class="x_panel " style="background-color: #37809f30;">
                          <div class="x_title">
                            <div class="">
                              <h2>
                                <i class="fa fa-bullhorn blue">
                                  <b><?php echo $anuncio['tipo'] ?></b>
                                </i>
                              </h2>
                            </div class="">
                            <ul class="panel_toolbox" style="list-style: none;">
                              <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                              </li>
                            </ul>
                            <div class="clearfix"></div>
                          </div>
                          <div class="x_content">
                            <div class="row">
                              <b style="font-size: 14px;color: #37809f;"><?php echo $anuncio['titulo'] ?></b>
                              <p style="text-align: justify;
														  font-size: 14px;">
                                <?php echo $anuncio['descripcion'] ?>
                              </p>
                            </div>
                            <?php if ($anuncio['enlace'] !== "") { ?>
                              <div class="text-right"><a class="btn btn-success" href="<?php echo $anuncio['enlace'] ?>" target="_blank">Ir al enlace</a></div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php }
                }
              }
              if ($cantidad_capacitaciones != 0) {
                while ($capacitaciones = mysqli_fetch_array($res_capacitaciones)) {
                  $capacitacion_cargo = $capacitaciones['usuarios'];
                  $cargos_seleccionados = explode('-', $capacitacion_cargo);
                  if (in_array($r_b_docente['id_cargo'], $cargos_seleccionados)) {
                    $mostrar = false;
                  ?>
                    <div class="row">
                      <div>
                        <div class="x_panel" style="background-color: #37809f30;">
                          <div class="x_title">
                            <div class="">
                              <h2>
                                <i class="fa fa-bullhorn blue">
                                  <b>CAPACITACIÓN</b>
                                </i>
                              </h2>
                            </div class="">
                            <ul class="panel_toolbox" style="list-style: none;">
                              <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                              </li>
                            </ul>
                            <div class="clearfix"></div>
                          </div>
                          <div class="x_content">
                            <div class="row">
                              <b style="font-size: 14px;color: #37809f;"><?php echo $capacitaciones['tema'] ?></b>
                              <p style="text-align: justify;
														  font-size: 14px;">
                                <?php echo $capacitaciones['descripcion'] ?>
                              </p>
                              <span><strong>BAJO EL SIGUIENTE DETALLE:</strong></span>
                              <li><strong>FECHA Y HORA:</strong> <?php echo convertirFormatoFecha($capacitaciones['fecha']) . ' - ' . convertirHora($capacitaciones['hora']) ?></li>
                              <li><strong>DURACIÓN:</strong> <?php echo $capacitaciones['duracion'] ?> minutos</li>
                              <li><strong>PONENTE(S):</strong> <?php echo $capacitaciones['ponentes'] ?></li>
                            </div>
                            <?php if ($capacitaciones['enlace'] !== "") { ?>
                              <div class="text-right"><a class="btn btn-success" href="<?php echo $capacitaciones['enlace'] ?>" target="_blank">Ir al enlace</a></div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>
              <?php }
                }
              }  ?>
            </div>

          </div>
          <?php
          $res_recursos = buscarRecursos($conexion);
          $recursos = mysqli_fetch_array($res_recursos);
          if (isset($recursos['img_fondo']) && $mostrar) {
          ?>
            <img src="<?php echo $recursos['img_fondo'] ?>" alt="" class="img_fondo">

          <?php } ?>
        </div>
        <?php
        include("../include/footer.php");
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
    <!-- Chart.js -->
    <script src="../Gentella/vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../Gentella/vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../Gentella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../Gentella/vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../Gentella/vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../Gentella/vendors/Flot/jquery.flot.js"></script>
    <script src="../Gentella/vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../Gentella/vendors/Flot/jquery.flot.time.js"></script>
    <script src="../Gentella/vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../Gentella/vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../Gentella/vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../Gentella/vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../Gentella/vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../Gentella/vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../Gentella/vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../Gentella/vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../Gentella/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../Gentella/vendors/moment/min/moment.min.js"></script>
    <script src="../Gentella/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../Gentella/build/js/custom.min.js"></script>

  </body>

  </html>
<?php
}