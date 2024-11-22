<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Factura</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>css/style_factura.css">
</head>
<body>

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
					<span class="h2"></span>
					<p></p>
					<p></p>
					<p>Correo:</p>
					<p></p>
				</div>
			</td>
			<td class="info_factura">
				<div class="round">
					<span class="h3">Factura</span>
					<p><b>N° Factura:</b> </p>
					<p><b>Fecha:</b> </p>
					<p><b>CAI:</b> </p>
					<p><b>RTN:</b> </p>
					<p><b>Desde:</b> <b>Hasta:</b> </p>
					<p><b>Fecha de Activación:</b> </p>
					<p><b>Fecha Limite de Emisión:</b> </p>
					<p><b>Factura:</b> </p>
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
							<td><label>ID/RTN:</label><p>
							?></p></td>
							<td><label>Expediente:</label><p></p></td>
							<td><label>Teléfono:</label> <p></p></td>
						</tr>
						<tr>
							<td colspan="2"></p></td>
							<td><label>Profesional:</label></p></td>
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

			</tbody>
			<tfoot id="detalle_totales">
				<tr>
					<td colspan="5" class="textright"><span>&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>
					</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Sub-Total</span></td>
					<td class="textright"><span>L.</span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Exonerado</span></td>
					<td class="textright"><span>L.</span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Excento</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Gravado 15%</span></td>
					<td class="textright"><span></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Importe Gravado 18%</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>ISV 15%</span></td>
					<td class="textright"><span></span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>ISV 18%</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
				<tr>
					<td colspan="5" class="textright"><span>Total</span></td>
					<td class="textright"><span>L. </span></td>
				</tr>
		</tfoot>
	</table>
	<div>
	    <p class="nota">
		  </p>
		<p class="nota"><center></center></p>
		<p class="nota"></p>
		<p class="nota">La factura es beneficio de todos "Exíjala"</p>
		<p class="nota">N° correlativo de orden de compra excenta __________________</p>
		<p class="nota">N° correlativo constancia de registro Exonerado __________________</p>
		<p class="nota">N° identificativo del registro de la SAG __________________</p>
		<p class="nota"><center><img src="<?php echo SERVERURL; ?>img/sello_pagado.png" width="235px" height="90px"></p>
		<p class="nota"><center><b>Original:</b> Cliente</center></p>
		<p class="nota"><center><b>Copia:</b> Emisor</center></p>
		<h4 class="label_gracias"></h4>
	</div>

</div>

</body>
</html>
