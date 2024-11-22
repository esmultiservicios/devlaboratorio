<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Reporte de Muestras Numero: <?php echo $consulta_registro['number']; ?></title>
    <link rel="stylesheet" href="<?php echo SERVERURL; ?>css/style_factura.css">
	<style>
		p, label, span, table{
			font-family: 'Helvetica';
			font-size: 9pt;
			padding-top: 1%;
			word-wrap: break-word;		
		}
		
		.datos_cliente label{
			width: 130px;
			display: inline-block;
		}

		.item{
		  width:120px;
		  text-align:center;
		  display:block;
		  background-color: transparent;
		  border: 1px solid transparent;
		  margin-right: 10px;
		  margin-bottom: 1px;
		  float:left;
		}
		
		.item img {
			width: 40px;
			height: 40px;
			background-color: grey;
		}

		#index-gallery{
		  width:200px;
		}	

		.margenes{
		  margin-left: 10px;
		  margin-right: 10px;
		  margin-top: 10px;
		  margin-bottom: 10px;
		}		

		td{
			word-wrap: break-word;
		}	
			
	</style>
</head>
<body>
<?php echo $anulada; ?>
<div id="page_pdf">
	<table id="factura_head">
		<tr>
			<td class="logo_factura">
				<div>
					<img src="<?php echo SERVERURL; ?>img/logo_factura.jpg" width="240" height="85">
				</div>
			</td>
			<td class="info_empresa">

			</td>			
			<td class="info_factura">
				<div class="round">
					<span class="h3">Para uso de Laboratorio</span>
					<p>N°: <?php echo $consulta_registro['number']; ?></p>
					<p>Recibida: <?php echo $consulta_registro['fecha']; ?></p>
					<p>Estudio Anterior: <?php echo $numero_muestra_anterior; ?></p>					
				</div>
			</td>
		</tr>
	</table>
	<table id="factura_head">
		<tr>
			<td class="info_empresa">
				<div>
					<p><?php echo $consulta_registro['otra_informacion']; ?></p>
					<p class="h2">Solicitud de Biopsia y Citología</p>
				</div>
			</td>
		</tr>
	</table>	
	<div class="container imagen_fondo">
	<table id="factura_cliente">
		<tr>
			<td class="info_cliente">
				<div class="round">
					<span class="h3">Datos Generales</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Nombre:</label><p><?php echo $consulta_registro['paciente']; ?></p></td>
							<td><label>Edad:</label><p><?php echo $anos." años"; ?></p></td>
						</tr>
						<tr>
							<td><label>Fecha:</label><p><?php echo $consulta_registro['fecha']; ?></p></td>
							<td><label>Sexo:</label><p><?php echo $consulta_registro['genero']; ?></p></p></td>
						</tr>
						<tr>
							<td><label>Teléfono:</label><p><?php echo $consulta_registro['telefono']; ?></p></td>
							<td><label>Correo:</label><p><?php echo $consulta_registro['correo']; ?></p></td>
						</tr>
						<tr>
							<td><label>Medico Remitente:</label><p><?php echo $consulta_registro['medico_remitente']; ?></p></td>
						</tr>
						<tr>
							<td><label>Hospital/Clínica:</label><p><?php echo $consulta_registro['hospital']; ?></p></td>
						</tr>						
					</table>
				</div>
			</td>
		</tr>		
	</table>
	
	<table id="factura_cliente">
		<tr>
			<td class="info_factura margenes">
				<div class="round">
					<span class="h3">Información Clínica</span>
					<table class="datos_cliente">
						<tr>
							<td><label>Diagnóstico Clínico:</label><p><?php echo $consulta_registro['diagnostico_clinico']; ?></p></td>
						</tr>
						<tr>
							<td><label>Material Enviado:</label><p><?php echo $consulta_registro['material_eviando']; ?></p></td>
						</tr>
						<tr>
							<td width="40%"><label>Datos Clínicos Relevantes:</label><p><?php
								if ($consulta_registro['mostrar_datos_clinicos'] == 1){
									echo $consulta_registro['datos_clinico'];
								} 
							?></p></td>
						</tr>					
					</table>
				</div>
			</td>
		</tr>		
	</table>	
	
	<div>
		<p class="nota" align="center"><br/><br/><br/><br/><br/><br/></p>
		<p class="nota" align="center"><img src="<?php echo SERVERURL; ?>img/firma.jpg" width="300px" height="120px"></p>
	</div>
	
	<br/>
	<table style="width: 100px; margin: 0 auto;">
		<tr>
			<td>
				<div class="item">
					<img src="<?php echo SERVERURL; ?>img/email.jpg" alt=""/>
					<p><?php echo $consulta_registro['empresa_correo']; ?></p>
				</div>			
			</td>
			<td>
				<div class="item">
					<img src="<?php echo SERVERURL; ?>img/telephone.jpg" alt=""/>
					<p><?php echo $consulta_registro['empresa_telefono']; ?></p>
				</div>				
			</td>
			<td>
				<div class="item">
					<img src="<?php echo SERVERURL; ?>img/whatsapp.jpg" alt=""/>
					<p><?php echo $consulta_registro['celular']; ?></p>
				</div>		
			</td>
			<td>
				<div class="item">
					<img src="<?php echo SERVERURL; ?>img/address.jpg" alt=""/>
					<p><?php echo $consulta_registro['direccion_empresa']; ?></p>
				</div>		
			</td>
		</tr>
	</table>	
	
	</div>	
</div>

</body>
</html>