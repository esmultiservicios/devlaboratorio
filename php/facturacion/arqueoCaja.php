<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Factura</title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>css/style_arqueo.css">
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
					<span class="h4">INVERSIONES MEDICAS PALORE S. DE R.L.</span>
					<p>RTN: 05019023517779</p>
					<p>CIERRE DIARIO DE CAJA NO. XXXXX</p>
					<p>CAJERO: JOSE MANUEL LOPEZ CUBAS</p>
					<p>Fecha</p>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_head">
		<tr>
			<td class="info_detalles_facturas">
				<div>
					<p>VENTA GOLBAL DEL DÍA</p>
					<p>CONGADO</p>
					<p>CREDITOS</p>
				</div>
			</td>
			<td class="info_detalles_facturas">
				<div>
					<span class="h2">CORRELTATIVO CAI</span>
					<p>DESDE</p>
					<p>HASTA</p>
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Transacciones en Efectivo</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Contado:</label></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>Credito:</label></td>
							<td><label></label><p>L. 500.00</p></td>
						</tr>
						<tr>
							<td><label>Total Efectivo Recibido:</label></td>
							<td><p>L. 1,500.00</p></td>
						</tr>
					</table>
				</div>
			</td>
			<td class="info_cliente1">
				<div class="round">
					<span class="h3">Transacciones POS</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Facturación POS:</label></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>Cuentas por Cobrar:</label></td>
							<td><label></label><p>L. 500.00</p></td>
						</tr>
						<tr>
							<td><label>Total Cobros POS:</label></td>
							<td><p>L. 1,500.00</p></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Créditos Otorgados</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Total Créditos Otorgados:</label></td>
							<td><p>L. 1,250.00</p></td>
						</tr>
					</table>
				</div>
			</td>
			<td class="info_cliente1">
				<div class="round">
					<span class="h3">Lotes</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Total Créditos Otorgados:</label></td>
							<td><p>L. 1,250.00</p></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>

	<table id="factura_detalle">
			<thead>
				<tr>
					<th width="100%">Desgloce Billetes y Monedas</th>
				</tr>
			</thead>
	</table>

	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Transacciones en Efectivo</span>
					<table class="datos_cliente">
						<thead>
							<tr>
								<th width="33.33%">Denominación</th>
								<th width="33.33%">Cantidad</th>
								<th width="33.33%">Total</th>
							</tr>
						</thead>
						<tr>
							<td><label>1</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>2</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>3</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
					</table>
				</div>
			</td>
			<td class="info_cliente1">
				<div class="round">
					<span class="h3">Transacciones POS</span>
					<table class="datos_cliente">
						<thead>
							<tr>
								<th width="33.33%">Denominación</th>
								<th width="33.33%">Cantidad</th>
								<th width="33.33%">Total</th>
							</tr>
						</thead>
						<tr>
							<td><label>1</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>2</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
						<tr>
							<td><label>3</label></td>
							<td><p>L. 1,000.00</p></td>
							<td><p>L. 1,000.00</p></td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
</div>

</body>
</html>
