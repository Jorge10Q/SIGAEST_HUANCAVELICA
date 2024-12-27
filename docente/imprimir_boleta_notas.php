<?php

include("../include/conexion.php");
include("../include/busquedas.php");
include("../include/funciones.php");
include("../functions/funciones.php");
require_once('../tcpdf/tcpdf.php');
include("include/verificar_sesion_secretaria.php");

//OPTENIENDO DATOS DEL FORMULARIO
$dni = $_POST['dni'];
$id_periodo = $_POST['periodo'];
$num_comprobante = $_POST['comprobante'];
$res = buscarEstudianteByDni($conexion, $dni);
$cont = mysqli_num_rows($res);

if (!verificar_sesion($conexion) || !verificarDatos($conexion, $dni, $id_periodo)) {
  echo "<script>
                alert('Es probable que el estudiante no se haya matriculado en el periodo seleccionado.');
                window.location.replace('boleta_de_notas.php');
    		</script>";
}else{
    
    if($cont == 0){
    echo "<script>
      alert('El alumno no exíste en la base de datos.');
      window.location.replace('certificado.php');
    </script>";
  }else{
    
    $id_docente_sesion = buscar_docente_sesion($conexion, $_SESSION['id_sesion'], $_SESSION['token']);

    //OPTENCIÓN DE DATOS
    $estudiante_res = buscarEstudianteByDni($conexion, $dni);
    $estudiante_res = mysqli_fetch_array($estudiante_res);
    $estudiante = $estudiante_res["apellidos_nombres"];
    $seccion    = $estudiante_res["seccion"];
    $correo = $estudiante_res['correo'];


    $periodo_ac = buscarPeriodoAcadById($conexion, $id_periodo);
    $periodo = mysqli_fetch_array($periodo_ac);
    $periodo = $periodo['nombre'];

    $usuario = buscarDocenteById($conexion, $id_docente_sesion);
    $usuario = mysqli_fetch_array($usuario);
    $usuario = $usuario['apellidos_nombres'];

    $programa_res = buscarCarrerasById($conexion, $estudiante_res['id_programa_estudios']);
    $programa_res = mysqli_fetch_array($programa_res);
    $programa = $programa_res['nombre'];

    $res_cursos_matriculados = getCalificacionFinalByIdAndPeriodo($conexion, $estudiante_res['id'], $id_periodo);
    
    $director = buscarDirector_All($conexion);
    $director = mysqli_fetch_array($director);
    $nombre_director = $director['apellidos_nombres'];

    $datos_iestp = buscarDatosSistema($conexion);
    $datos_iestp = mysqli_fetch_array($datos_iestp);
    $nombre_insti = str_replace("IESTP ", "", $datos_iestp['titulo']);


    $datos_lugar = buscarDatosGenerales($conexion);
    $datos_lugar = mysqli_fetch_array($datos_lugar);
    $lugar = ucwords(strtolower($datos_lugar['distrito']));

    $sistema = buscarDatosSistema($conexion);
    $sistema = mysqli_fetch_array($sistema);

    $ordMer     = "-";
    $nombre_doc = 'BN_' . $estudiante.'_'.$periodo.'.pdf';

    //CODIGO DE VERIFICACIÓN DE DOCUMENTO
    $codigo = uniqid();
    $url = $sistema['dominio_sistema'];
    $ruta_qr = generarQRBoleta( $url."/verificar.php?codigo=".$codigo ,'BN_'.$estudiante.'_'.$periodo);

    //INICIO DE LA CREACIÓN DE PDF
    class MYPDF extends TCPDF
        {
            // Page footer
            public function Footer()
            {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);
                // Page number
                $this->Cell(0, 10, '´Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }

    //CONFIGURACIÓN PDF
    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Boleta de Notas - " . $estudiante);
    $pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont('helvetica');
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetAutoPageBreak(TRUE,25);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->AddPage('P', 'A4');
    $text_size = 10;
    $contador = 0;

    //TABLA DE LA BOLETA CON LAS NOTAS
    $tabla .= '
        <table border="0.2" cellspacing="0" cellpadding="3">
            <tr bgcolor="#CCCCCC">
                <th width="10%" align="center"><font size="10">N°</font></th>
                <th width="70%" align="center"><font size="10">UNIDAD DIDÁCTICA</font></th>
                <th width="20%" align="center"><font size="10">NOTA FINAL</font></th>
            </tr>
    ';

    // Iterar notas del estudiante

    $acumuladorNota = 0;
    $acumuladorCreditos = 0;
    while($curso_matriculado = mysqli_fetch_array($res_cursos_matriculados)) {
        $contador = $contador + 1;
        $calificacion_final = obtenerCalificacionFinal($conexion,$curso_matriculado['id_detalle_matricula']);

        $puntos = $curso_matriculado['creditos']*$calificacion_final;
        $acumuladorNota += $puntos;
        $acumuladorCreditos += $curso_matriculado['creditos'];

        $color = ($calificacion_final < 13) ? 'color: red;' : '';
        $tabla .= '
            <tr>
                <td align="center"><font size="10">' . $contador. '</font></td>
                <td><font size="10">' . $curso_matriculado['descripcion'] . '</font></td>
                <td align="center" style="'. $color .'"><font size="10">' . $calificacion_final. '</font></td>
            </tr>';
    };
    $promedio =  $acumuladorNota/$acumuladorCreditos;

    //CONTENIDO DE LA CABECERA DEL PDF
    $documento = '
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="33%"><img src="../img/logo.jpg" alt="" height="40px"></td>
                <td width="34%" align="center"><img src="../img/logo_minedu.jpeg" alt="" height="40px"></td>
                <td width="33%" align="right"></td>
            </tr>
            <br />
            <tr>
                <td colspan="3" align="center"><font size="' . $text_size . '"><b>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO</b></font></td>
            </tr>
            <tr>
                <td colspan="3" align="center"><font size="10"><b>"'. $nombre_insti .'"</b></font></td>
            </tr>
            <tr>
                <td colspan="3" align="center"><b>BOLETA DE NOTAS</b></td>
            </tr> 
        </table>
        <br />
        <br />
    ';

    $documento .= '
        <table border="0.2" cellspacing="0" cellpadding="3">
            <tr bgcolor="#CCCCCC">
                <th width="50%" align="center"><font size="10">NOMBRES Y APELLIDOS</font></th>
                <th width="50%" align="center"><font size="10">PROGRAMA DE ESTUDIOS</font></th>
            </tr>
            <tr>
                <td align="center"><font size="10">' . $estudiante . '</font></td>
                <td align="center"><font size="10">' . $programa . '</font></td>
            </tr>

            <tr bgcolor="#CCCCCC">
                <th width="25%" align="center"><font size="10">SEMESTRE</font></th>
                <th width="25%" align="center"><font size="10">SECCIÓN</font></th>
                <th width="25%" align="center"><font size="10">PREMEDIO GENERAL</font></th>
                <th width="25%" align="center"><font size="10">ORDEN DE MERITO</font></th>
            </tr>
            <tr>
                <td align="center"><font size="10">' . $periodo . '</font></td>
                <td align="center"><font size="10">' . $seccion . '</font></td>
                <td align="center"><font size="10">' . number_format($promedio,2) . '</font></td>
                <td align="center"><font size="10">' . $ordMer  . '</font></td>
            </tr>

        </table>
        <br />
        <br />
    ';

    $documento .= $tabla;
    

    // Cerrar la tabla
    $documento .= '</table>';

    //Agregar fecha al documento
    $documento .= '<br /><br />
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td align="rigth">'. $lugar .', '.  obtenerFecha() . '</td>
            </tr>
            <tr>
                <td colspan="2" align="center"><br><br><br><br><br><br><br>...............................................<br>'. $nombre_director .'<br>Director General</td>
            </tr>
            <tr>
            <br><br><br><br>
            <td width="100%" align="rigth"><img src="'. $ruta_qr .'" alt="" height="60px"></td>
        </tr>
        <tr>
            <td width="100%" align="rigth"> Verifíque la integridad del documento, mediante este código QR.</td>
        </tr>
        </table>
    ';

    // Escribir el contenido HTML en el PDF
    $pdf->writeHTML($documento, true, false, true, false, ''); 
    $rutaArchivo = '../documentos/boletas_de_notas/'. $nombre_doc;
    // Guardar el contenido en el archivo
    $pdfContent = $pdf->Output('', 'S');
    // Enviar el PDF al navegador
    file_put_contents($rutaArchivo, $pdfContent);

    $consulta = "INSERT INTO boleta_notas (codigo ,nombre_usuario, dni_estudiante, apellidos_nombres, programa_estudio, periodo_acad ,ruta_documento,num_comprobante) 
    VALUES ('$codigo' ,'$usuario','$dni', '$estudiante' ,'$programa','$periodo','$rutaArchivo','$num_comprobante')";
    mysqli_query($conexion, $consulta);

}};
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
	  
    <title>Estudiantes <?php include ("../include/header_title.php"); ?></title>
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
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                <div class="">
                    <h2 align="center">Boleta de Nota</h2>
                    <a href="boleta_de_notas.php" class="btn btn-danger">Regresar</a>
                    <div class="clearfix"></div>
                </div>
                <div class="">
                  <br>
                  <input type="email" id="correoInput" class="form-control" style="width:300px; margin-bottom:2px;" value="<?= $correo?>">

                  <!-- Agrega un ID al enlace para facilitar la referencia desde JavaScript -->
                  <a href="#" id="enviarCorreoBtn" class="btn btn-success"><i class="fa fa-plus-square"></i> Enviar por Correo</a>
                </div>
                    <iframe src="<?php echo $rutaArchivo ?>" width="100%" height="600px"></iframe>
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

    <script>
    document.getElementById('enviarCorreoBtn').addEventListener('click', function() {
        // Obtiene el valor del campo de entrada
        var correoValue = document.getElementById('correoInput').value;

        // Construye la URL con el valor del correo
        var url = "./login/enviar_boleta_correo.php?documento=<?= $rutaArchivo ?>&dni=<?= $dni ?>&correo=" + encodeURIComponent(correoValue);

        // Redirecciona a la nueva URL
        window.location.href = url;
    });
  </script>

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
    <!-- Datatables -->
    <script src="../Gentella/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../Gentella/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
    <script src="../Gentella/vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
    <script src="../Gentella/vendors/jszip/dist/jszip.min.js"></script>
    <script src="../Gentella/vendors/pdfmake/build/pdfmake.min.js"></script>
    <script src="../Gentella/vendors/pdfmake/build/vfs_fonts.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../Gentella/build/js/custom.min.js"></script>
    <script>
    $(document).ready(function() {
    $('#example').DataTable({
      "language":{
    "processing": "Procesando...",
    "lengthMenu": "Mostrar _MENU_ registros",
    "zeroRecords": "No se encontraron resultados",
    "emptyTable": "Ningún dato disponible en esta tabla",
    "sInfo": "Mostrando del _START_ al _END_ de un total de _TOTAL_ registros",
    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
    "search": "Buscar:",
    "infoThousands": ",",
    "loadingRecords": "Cargando...",
    "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
    },
      }
    });

    } );
    </script>
     <?php mysqli_close($conexion); ?>
  </body>
</html>
