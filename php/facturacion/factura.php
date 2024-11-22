<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Factura</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>css/style_factura.css">
	<link rel="shortcut icon" href="<?php echo SERVERURL; ?>img/logo_icono.png">
</head>
<body>
<?php echo $anulada; ?>
<?php
  if (SISTEMA_PRUEBA=="SI"){ //CAJA
?>
    <span class="container-fluid prueba-sistema">SISTEMA DE PRUEBA</span>
<?php
  }
?>
<div id="page_pdf">
	<table id="factura_head">
		<tr>
			<td class="logo_factura">
				<div>
					<img src="<?php echo SERVERURL; ?>img/logo_factura.jpg" width="250px" height="100px">
				</div>
			</td>
			<td class="info_empresa">
				<div>
					<span class="h2"><?php echo $consulta_registro['empresa']; ?></span>
					<p><?php echo $consulta_registro['direccion_empresa']; ?></p>
					<p>Teléfono: <?php echo $consulta_registro['empresa_telefono']; ?></p>
					<p>Correo: <?php echo $consulta_registro['empresa_correo']; ?></p>
					<p><?php echo $consulta_registro['otra_informacion']; ?></p>
				</div>
			</td>
			<td class="info_factura">
				<div class="round">
				<span class="h3"><?php 
						if ($consulta_registro['documento'] === "Factura Electronica") {
							echo "Factura";
						} else {
							echo "Factura Proforma";
						}?></span>
					<p> <?php 
						echo "<b>N° Factura:</b> ".$consulta_registro['prefijo'].''.str_pad($consulta_registro['numero_factura'], $consulta_registro['relleno'], "0", STR_PAD_LEFT);
					?></p>
					<p><?php 
						echo "<b>Fecha:</b> ".$consulta_registro['fecha'].' '.date('g:i a',strtotime($consulta_registro['hora']));
						?></p>
					<p><?php 
						if ($consulta_registro['documento'] === "Factura Electronica") {
							echo "<b>CAI:</b> ".$consulta_registro['cai'];
						} else {
							echo "";
						}					
					 ?></p>
					<p><?php 
						echo "<b>RTN:</b> ".$consulta_registro['rtn'];
					?></p>
					<p><?php 
						if ($consulta_registro['documento'] === "Factura Electronica") {
							echo "<b>Desde:</b> ".$consulta_registro['prefijo'].''.$consulta_registro['rango_inicial']." <b>Hasta</b> ".$consulta_registro['prefijo'].''.$consulta_registro['rango_final'];
						} else {
							echo "";
						}					
					?></p>
					<p><?php 
						if ($consulta_registro['documento'] === "Factura Electronica") {
							echo "<b>Fecha Activacion:</b> ".$consulta_registro['fecha_activacion'];
						} else {
							echo "";
						}					
					?></p>
					<p><?php 
						if ($consulta_registro['documento'] === "Factura Electronica") {
							echo "<b>Fecha Limite de Emisión:</b> ".$consulta_registro['fecha_limite'];
						} else {
							echo "";
						}					
					?></p>
					<p><b>Factura:</b> <?php echo $consulta_registro['tipo_documento']; ?></p>
					<?php
						if($consulta_registro['referencia'] != "" || $consulta_registro['referencia'] != null){
							echo "<p><b>N° Muestra:</b> ".$consulta_registro['referencia']."</p>";
						}
					?>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Cliente</span>
					<table class="datos_cliente">
						<tr>
							<td><label>ID/RTN:</label><p><?php
									if(strlen($consulta_registro['identidad'])<10){
										echo "";
									}else{
										echo $consulta_registro['identidad'];
									}
							?></p></td>
							<td><label>Expediente:</label><p><?php echo $consulta_registro['expediente']; ?></p></td>
							<td><label>Teléfono:</label> <p><?php
								if(strlen($consulta_registro['identidad'])<8){
									echo "";
								}else{
									echo $consulta_registro['tel_paciente'];
								}

							?></p></td>
						</tr>
						<tr>
							<td colspan="2"><label>Nombre:</label><p><?php echo $consulta_registro['paciente']; ?></p></td>
							<!--<td><label>Usuario:</label> <p><?php echo $consulta_registro['profesional']; ?></p></td>-->
						</tr>
					</table>
				</div>
			</td>

		</tr>
	</table>

	<table id="factura_detalle">
			<thead>
				<tr>
					<th width="2.66%">N°</th>
					<th width="40.66%">Nombre Producto</th>
					<th width="6.66%" class="textleft">Cantidad</th>
					<th width="16.66%" class="textright">Precio</th>
					<th width="16.66%" class="textright">Descuento</th>
					<th width="16.66%" class="textright">Importe</th>
				</tr>
			</thead>
			<tbody id="detalle_productos">
				<?php
					$total_antes_isv = 0;
					$total_despues_isv = 0;
					$total = 0;
					$total_ = 0;
					$neto = 0;
					$descuentos = 0;
					$isv_neto = 0;
					$importe_gravado = 0;
					$importe_excento = 0;
					$subtotal = 0;
					$i = 1;					

					while($registro_detalles = $result_factura_detalle->fetch_assoc()){
						$total_ = 0;
						$importe = 0;

						$total += ($registro_detalles["precio"] * $registro_detalles["cantidad"]);
						$total_ = ($registro_detalles["precio"] * $registro_detalles["cantidad"]) - $registro_detalles["descuento"];
						$descuentos += $registro_detalles["descuento"];
						$isv_neto += $registro_detalles["isv_valor"];
						$importe += ($registro_detalles["precio"] * $registro_detalles["cantidad"] - $registro_detalles["descuento"]);
						$subtotal += $importe;

						if($registro_detalles["paciente"] == ""){
							$producto = $registro_detalles["producto"];
						}else{
							$producto = $registro_detalles["paciente"]."-".$registro_detalles["producto"];
						}

						if($registro_detalles["isv_valor"] > 0){
							$importe_gravado += ($registro_detalles["precio"] * $registro_detalles["cantidad"]) -$registro_detalles["descuento"];
						}else{
							$importe_excento += ($registro_detalles["precio"] * $registro_detalles["cantidad"]) - $registro_detalles["descuento"];
						}

						echo '
						  <tr>
							<td>'.$i.'</td>
							<td>'.$producto.'</td>
							<td class="textleft">'.$registro_detalles["cantidad"].'</td>
							<td class="textright">L. '.number_format($registro_detalles["precio"],2).'</td>
							<td class="textright">L. '.number_format($descuentos,2).'</td>
							<td class="textright">L. '.number_format($total_,2).'</td>
						  </tr>
						';

						$i++;
					}
					$total_despues_isv = ($total + $isv_neto) - $descuentos;
				?>
			</tbody>
			<tfoot id="detalle_totales">
				<tr>
					<td colspan="5" class="textright"><span>&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe</span></td>
					<td class="textright"><span>L. <?php echo number_format($total,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span><?php
						if ($consulta_registro['edad'] >= 60)
							echo "Descuentos y Rebajas Otorgados Tercera Edad";
						else
							echo "Descuentos y Rebajas Otorgados";
					?>
					</span></td>
					<td class="textright"><span>L. <?php echo number_format($descuentos,2); ?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Sub-Total</span></td>
					<td class="textright"><span>L. <?php echo number_format($subtotal,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Exonerado</span></td>
					<td class="textright"><span>L. <?php echo number_format(0,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Excento</span></td>
					<td class="textright"><span>L. <?php echo number_format($importe_excento,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Gravado 15%</span></td>
					<td class="textright"><span><?php echo number_format($importe_gravado,2); ?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Gravado 18%</span></td>
					<td class="textright"><span>L. <?php echo number_format(0,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>ISV 15%</span></td>
					<td class="textright"><span><?php echo number_format($isv_neto,2); ?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>ISV 18%</span></td>
					<td class="textright"><span>L. <?php echo number_format(0,2);?></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span style="font-size:13px; font-weight: bold;">Total</span></td>
					<td class="textright"><span style="font-size:13px; font-weight: bold;">L. <?php echo number_format($total_despues_isv,2); ?></span></td>
				</tr>
				<?php 
					if ($consulta_registro['documento'] === "Factura Proforma") {
						// Ahora agregamos la fila con los datos de la factura proforma
						echo '<tr>';
						echo '<td colspan="5" class="textright"><span style="font-size:13px; font-weight: bold;">Abono</span></td>';
						echo '<td class="textright"><span style="font-size:13px; font-weight: bold;">L. ' . number_format($abono,2) . '</span></td>';
						echo '</tr>';
						echo '<tr>';
						echo '<td colspan="5" class="textright"><span style="font-size:13px; font-weight: bold;">Saldo</span></td>';
						echo '<td class="textright"><span style="font-size:13px; font-weight: bold;">L. ' . number_format((float)$total_despues_isv - (float)$abono,2) . '</span></td>';
						echo '</tr>';
					}
				?>
		</tfoot>
	</table>
	<div>
	    <p class="nota"><?php
			if($consulta_registro["notas"] != ""){
				echo "<p class='h3'><b>Nota:</b> ".$consulta_registro["notas"]."</p>";
			}
		?></p>
		<p class="nota"><center><?php echo convertir($total_despues_isv);?></center></p>
		<p class="nota"></p>
		<p class="nota">La factura es beneficio de todos "Exíjala"</p>
		<p class="nota">N° correlativo de orden de compra excenta __________________</p>
		<p class="nota">N° correlativo constancia de registro Exonerado __________________</p>
		<p class="nota">N° identificativo del registro de la SAG __________________</p>
		<?php
			if($consulta_registro["estado"] == 2){
		?>
		<p class="nota"><center><img src="<?php echo SERVERURL; ?>img/sello_pagado.png" width="235px" height="90px"></p>
		<?php
			}
		?>
		<p class="nota"><center><b>Original:</b> Cliente</center></p>
		<p class="nota"><center><b>Copia:</b> Emisor</center></p>
		<h4 class="label_gracias"><?php  echo $consulta_registro["eslogan"]?></h4>
	</div>

</div>

</body>
</html>
