<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Laboratorio</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>css/stylelab.css">
    <link rel="shortcut icon" href="<?php echo SERVERURL; ?>img/logo_icono.png">
</head>

<body>
    <div id="header">
        <div class="logo_factura">
            <img src="<?php echo SERVERURL; ?>img/logo_factura.jpg">
        </div>
        <div class="title"><i><?php echo $consulta_registro['eslogan']; ?></i></div>
        <div class="title1">Biopsia N° <?php echo $consulta_registro['numero']; ?></div>
        <div class="title">INFORME DE ANATOMÍA PATOLÓGICA</div>
        <hr />
    </div>

    <div class="clearfix"></div>

    <div id="footer">
        <table style="width: 100px; margin: 0 auto;">
            <tr>
                <td>
                    <div class="item">
                        <img src="<?php echo SERVERURL; ?>img/email.jpg" alt="" />
                        <p><?php echo $consulta_registro['empresa_correo']; ?></p>
                    </div>
                </td>
                <td>
                    <div class="item">
                        <img src="<?php echo SERVERURL; ?>img/telephone.jpg" alt="" />
                        <p><?php echo $consulta_registro['empresa_telefono']; ?></p>
                    </div>
                </td>
                <td>
                    <div class="item">
                        <img src="<?php echo SERVERURL; ?>img/whatsapp.jpg" alt="" />
                        <p><?php echo $consulta_registro['celular']; ?></p>
                    </div>
                </td>
                <td>
                    <div class="item">
                        <img src="<?php echo SERVERURL; ?>img/address.jpg" alt="" />
                        <p><?php echo $consulta_registro['direccion_empresa']; ?></p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-container">
        <div class="left-right-group">
            <div class="left-group">
                <p><b>Registro Número:</b> <?php echo $consulta_registro['numero']; ?></p>
                <p><b>Nombre:</b>
                    <?php 
                    $paciente = $consulta_registro['paciente'];
                    $empresa = "";  
                    if($paciente != ""){
                        $empresa = $paciente;
                    }else{
                        $empresa = $consulta_registro['empresa'];
                    }           

                    echo $empresa; 

                    ?>
                </p>
                <p><b>Edad:</b> <?php 
                    $paciente = $consulta_registro['paciente'];
                    $edad = "";  
                    if($paciente != ""){
                        $edad = $consulta_registro['edad_paciente'];
                    }else{
                        $edad = $consulta_registro['edad'];
                    }

                    echo $edad; 
                ?> <b>Sexo:</b>
                    <?php 
                    $paciente = $consulta_registro['paciente'];
                    $genero = "";  
                    if($paciente != ""){
                        $genero = $consulta_registro['genero_paciente'];
                    }else{
                        $genero = $consulta_registro['genero'];
                    }
                echo $genero; 

                ?></p>
                <p><b>Medico Remitente:</b> <?php 
                    if($consulta_registro['medico_remitente'] == "Sin Registro"){
                        echo $consulta_registro['hospital']; 
                    }else{
                        echo $consulta_registro['medico_remitente'].'/'.$consulta_registro['hospital']; 
                    }                
                ?></p>
                <p><b>Diagnostico Clínico:</b> <?php echo $consulta_registro['diagnostico_clinico']; ?></p>
            </div>
            <div class="right-group">
                <p><b>Sitio Preciso de la Muestra: </b><?php echo $consulta_registro['sitio_muestra']; ?></p>
                <p><b>Fecha de Recibido:</b> <?php echo $consulta_registro['fecha_recibido']; ?></p>
                <p><b>Fecha de la Toma:</b> <?php echo $consulta_registro['fecha_recibido']; ?></p>
                <p><b>Fecha de Emisión de Reporte:</b> <?php echo $consulta_registro['fecha_emision_reporte']; ?></p>
                <p><br /></p>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="diagnostico-group">
            <div class="titulo-diagnostico">DIAGNÓSTICO:</div>
            <?php 
            if($consulta_registro['diagnostico'] != ""){
                echo nl2br($consulta_registro['diagnostico']);
            }
            ?>
        </div>
        <div class="diagnostico-group">
            <b>FACTORES PRONÓSTICOS / PROTOCOLO SEGÚN EL COLEGIO AMERICANO DE PATOLÓGOS:</b><br />
            <?php 
            if($consulta_registro['factores_pronostico'] != ""){
                echo nl2br($consulta_registro['factores_pronostico']);
            }
            ?>
        </div>
        <div class="diagnostico-group">
            <b>DESCRIPCIÓN MACROSCÓPICA:</b><br />
            <?php 
            if($consulta_registro['descripcion_macroscopica'] != ""){
                echo nl2br($consulta_registro['descripcion_macroscopica']);
            }                
            ?>
        </div>
        <div class="diagnostico-group">
            <b>DESCRIPCIÓN MICROSCÓPICA:</b><br />
            <?php 
            if($consulta_registro['descripcion_microscopica'] != ""){
                echo nl2br($consulta_registro['descripcion_microscopica']);
            }                    
            ?>
        </div>
        <div class="diagnostico-group">
            <b>COMENTARIO:</b><br />
            <?php 
            if($consulta_registro['comentario'] != ""){
                echo nl2br($consulta_registro['comentario']);
            }                    
            ?>
        </div>

        <div class="clearfix"></div>

        <div class="imagenes">
            <?php
            if(count($imagenes) != 0){
                echo '<b>IMÁGENES:</b><br/>';
            }            

            // Supongamos que $imagenes es un array con las imágenes obtenidas de la tabla

            for ($i = 0; $i < count($imagenes); $i += 2) {
                echo '<div class="imagen-row">';
                echo '<div class="imagen-item1"><img src="' . $imagenes[$i] . '" alt="Imagen"></div>';
                if ($i + 1 < count($imagenes)) {
                    echo '<div class="imagen-item2"><img src="' . $imagenes[$i + 1] . '" alt="Imagen"></div>';
                }
                
                echo '</div>';
            }

            ?>
        </div>

        <div class="clearfix"></div>

        <div class="signature-group">
            <div class="div_left"><b>Fecha:</b> <?php echo $consulta_registro['fecha_emision_reporte']; ?></div>
            <div class="div_right">
                <img src="<?php echo SERVERURL; ?>img/firma_sello_nombre.png" width="300px" height="120px">
            </div>
        </div>

        <br />
        <br />
        <center><img src="<?php echo $pngAbsoluteFilePath; ?>"></center>
    </div>
</body>

</html>