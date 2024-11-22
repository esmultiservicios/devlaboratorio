<?php
session_start();
include "../funtions.php";

//CONEXION A DB
$mysqli = connect_mysqli();

if(isset($_POST['cliente_admision'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
	if($_POST['cliente_admision'] == ""){
		$cliente_admision = 0;
	}else{
		$cliente_admision = $_POST['cliente_admision'];
	}
}else{
	$cliente_admision = 0;
}

$nombre = cleanString($_POST['name']);
$apellido = "";
$identidad = $_POST['rtn'];

//CONSULTAR IDENTIDAD DEL USUARIO
if($identidad == 0){
	$flag_identidad = true;
	while($flag_identidad){
	   $d=rand(1,99999999);
	   $query_identidadRand = "SELECT pacientes_id
	       FROM pacientes
		   WHERE identidad = '$d'";
	   $result_identidad = $mysqli->query($query_identidadRand);
	   if($result_identidad->num_rows==0){
		  $identidad = $d;
		  $flag_identidad = false;
	   }else{
		  $flag_identidad = true;
	   }
	}
}

$fecha_nacimiento = $_POST['fecha_nac'];
$edad = $_POST['edad'];
$telefono1 = $_POST['telefono1'];
$telefono2 = $_POST['telefono2'];
$genero = $_POST['genero'];
$departamento_id = 0;
$municipio_id = 0;
$localidad = cleanString($_POST['direccion']);
$correo = strtolower(cleanString($_POST['correo']));
$fecha = date("Y-m-d");
$religion_id = 0;
$profesion_id = 0;
$paciente_tipo = 1;//1. CLIENTE 2. EMPRESA
$usuario = $_SESSION['colaborador_id'];
$estado = 1; //1. Activo 2. Inactivo
$fecha_registro = date("Y-m-d H:i:s");

if($cliente_admision == 0){//NO SE SELECCIONO NINGUN CLIENTE
	//CONSULTAMOS SI EXISTE EL PACIENTE ANTES DE ALMACENARLO
	$select = "SELECT pacientes_id
		FROM pacientes
		WHERE identidad = '$identidad' AND nombre = '$nombre' AND apellido = '$apellido' AND genero = '$genero'";
	$result = $mysqli->query($select) or die($mysqli->error);

	if($result->num_rows==0)//RREGISTRO NO EXISTE PROCEDEMOS A ALMACENARLO
	{
		$pacientes_id = correlativo('pacientes_id ', 'pacientes');
		$expediente = correlativo('expediente ', 'pacientes');
		$insert = "INSERT INTO pacientes VALUES ('$pacientes_id','$expediente','$identidad','$nombre','$apellido','$genero','$telefono1','$telefono2','$fecha_nacimiento','$edad','$correo','$fecha','$departamento_id','$municipio_id','$localidad','$religion_id','$profesion_id','$usuario','$estado','$paciente_tipo','$fecha_registro')";
		$query = $mysqli->query($insert);
	}else{
		$consulta2 = $result->fetch_assoc();
		$cliente_admision_id = $consulta2['pacientes_id'];
		$pacientes_id = $cliente_admision_id;
		$update = "UPDATE pacientes
						SET
							telefono1 = '$telefono1'
							,edad = '$edad'
							,email = '$correo'
						WHERE pacientes_id = '$cliente_admision_id'";

		$query = $mysqli->query($update);
	}
}else{
	$update = "UPDATE pacientes
					SET
						telefono1 = '$telefono1'
						,edad = '$edad'
						,email = '$correo'
				WHERE pacientes_id = '$cliente_admision'";
	$pacientes_id = $cliente_admision;
	$query = $mysqli->query($update);
}

if($query){
	//AGREGAMOS LOS DATOS DE LA MUESTRA
	//DATOS DE LA MUESTRA
	$servicio_id = 1;

	if(isset($_POST['remitente'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['remitente'] == ""){
			$colaborador_id = 0;
		}else{
			$colaborador_id = $_POST['remitente'];
		}
	}else{
		$colaborador_id = 0;
	}

	if(isset($_POST['tipo_muestra'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['tipo_muestra'] == ""){
			$tipo_muestra_id = 0;
		}else{
			$tipo_muestra_id = $_POST['tipo_muestra'];
		}
	}else{
		$tipo_muestra_id = 0;
	}

	$referencia = cleanString($_POST['referencia']);
	$estado_muestra = 0;
	$identidad = $_POST['rtn'];
	$sitio_muestra = cleanString($_POST['sitio_muestra']);
	$diagnostico_clinico = cleanString($_POST['diagnostico_clinico']);
	$material_enviado = cleanString($_POST['material_enviado']);
	$datos_clinicos = cleanString($_POST['datos_clinicos']);

	if(isset($_POST['mostrar_datos_clinicos'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['mostrar_datos_clinicos'] == ""){
			$mostrar_datos_clinicos = 2;
		}else{
			$mostrar_datos_clinicos = $_POST['mostrar_datos_clinicos'];
		}
	}else{
		$mostrar_datos_clinicos = 2;
	}

	if(isset($_POST['empresa'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['empresa'] == ""){
			$empresa = 0;
		}else{
			$empresa = $_POST['empresa'];
		}
	}else{
		$empresa = 0;
	}

	if(isset($_POST['hospital'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['hospital'] == ""){
			$hospital_clinica = 0;
		}else{
			$hospital_clinica = $_POST['hospital'];
		}
	}else{
		$hospital_clinica = 0;
	}

	if(isset($_POST['categoria'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['categoria'] == ""){
			$categoria_muestras = 0;
		}else{
			$categoria_muestras = $_POST['categoria'];
		}
	}else{
		$categoria_muestras = 0;
	}

	if(isset($_POST['producto'])){//COMPRUEBO SI LA VARIABLE ESTA DIFINIDA
		if($_POST['producto'] == ""){
			$producto = 0;
		}else{
			$producto = $_POST['producto'];
		}
	}else{
		$producto = 0;
	}

	//CONSULTAMOS LA SECUENCIA PARA LA ENTIDAD
	$estado_secuencia = 1;
	$query_secuencia = "SELECT *
		FROM secuencias_muestas
		WHERE tipo_muestra_id = '$tipo_muestra_id' AND estado = '$estado_secuencia'";
	$result_secuencia = $mysqli->query($query_secuencia) or die($mysqli->error);

	$number = "";
	$flag = false;

	if($result_secuencia->num_rows>=0){
		$flag = true;
		$consulta = $result_secuencia->fetch_assoc();
		$prefijo = $consulta["prefijo"];
		$sufijo = $consulta["sufijo"];
		$relleno = $consulta["relleno"];
		$incremento = $consulta["incremento"];
		$siguiente = $consulta["siguiente"];
		$secuencias_id = $consulta["secuencias_id"];

		$numero = str_pad($consulta['siguiente'], $consulta['relleno'], "0", STR_PAD_LEFT);
		$año_actual = date("Y");
		$mes_actual = date("m");
		$dia_actual = date("d");
		$dia_semana = date("N");

		$prefijo = str_replace("@año_actual", $año_actual, $prefijo);
		$prefijo = str_replace("@mes_actual", $mes_actual, $prefijo);
		$prefijo = str_replace("@dia_actual", $dia_actual, $prefijo);
		$prefijo = str_replace("@dia_semana", $dia_semana, $prefijo);

		$number .= $prefijo.$numero;

		$sufijo = str_replace("@año_actual", $año_actual, $sufijo);
		$sufijo = str_replace("@mes_actual", $mes_actual, $sufijo);
		$sufijo = str_replace("@dia_actual", $dia_actual, $sufijo);
		$sufijo = str_replace("@dia_semana", $dia_semana, $sufijo);

		$number .= $sufijo;
	}

	$pacientes_id_muestra = $pacientes_id;

	if($empresa != 0){
		$pacientes_id_muestra = $empresa;
	}

	$existe = 0;
	$tipoPacienteConsulta = "";

	//CONSULTAMOS EL LIMITE DE LAS MUESTAS POR CLIENTE
	$queryLimiteMuestras = "SELECT limite
		FROM limite_muetras";
	$resultLimiteMuestras = $mysqli->query($queryLimiteMuestras) or die($mysqli->error);

	if($resultLimiteMuestras->num_rows==0){
		$existe = 0;
		$tipoPacienteConsulta = "el Cliente";
	}else{
		$valoresLimite = $resultLimiteMuestras->fetch_assoc();
		$limiteMuestras = $valoresLimite['limite'];

		//VERIFICAMOS SI EL CLIENTE ES UNA Empresa
		if($empresa == ""){
			//VERIFICAMOS QUE NO EXISTA LA MUESTRA DE ESTE Paciente
			$queryMuestraCliente = "SELECT muestras_id
						FROM muestras
						WHERE pacientes_id = '$cliente_admision' AND estado = 0 AND tipo_muestra_id = '$tipo_muestra_id'";
			$resultMuestrasCliente = $mysqli->query($queryMuestraCliente) or die($mysqli->error);

			if($resultMuestrasCliente->num_rows>=$limiteMuestras){
				$existe = 1;
				$tipoPacienteConsulta = "el Cliente";
			}
		}else{
			//CONSULTAMOS SI EXISTE EL REGISTRO EN muestras_hospitales
			$queryMuestraEmpresa = "SELECT mh.muestras_hospitales_id
						FROM muestras_hospitales AS mh
						INNER JOIN muestras As m
						ON mh.muestras_id = m.muestras_id
						WHERE m.pacientes_id = '$empresa' AND mh.pacientes_id = '$cliente_admision' AND m.estado = 0 AND m.tipo_muestra_id = '$tipo_muestra_id'";
			$resultMuestrasEmpresa = $mysqli->query($queryMuestraEmpresa) or die($mysqli->error);

			if($resultMuestrasEmpresa->num_rows>=$limiteMuestras){
				$existe = 1;
				$tipoPacienteConsulta = "La empresa";
			}
		}
	}

	if($existe == 0){
		$muestras_id  = correlativo('muestras_id', 'muestras');
		$insert = "INSERT INTO muestras			VALUES('$muestras_id','$pacientes_id_muestra','$secuencias_id','$servicio_id','$usuario','$tipo_muestra_id','$number','$referencia','$fecha','$estado_muestra','$sitio_muestra','$diagnostico_clinico','$material_enviado','$datos_clinicos','$mostrar_datos_clinicos','$hospital_clinica','$categoria_muestras','$usuario','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);

		if($flag){
			$siguiente += $incremento;

			//ACTUAIZAMOS EL NUMERO SIGUIENTE EN EL ADMINISTRADOR DE SECUENCIAS
			$update_secuencia = "UPDATE secuencias_muestas
				SET
					siguiente = '$siguiente'
				WHERE secuencias_id = '$secuencias_id'";
			$mysqli->query($update_secuencia) or die($mysqli->error);
		}

		//OBTENER NOMBRE DEL CLIENTE EN EL REGISTRO DE LA MUESTRA
		$query_cliente_muestra = "SELECT CONCAT(p.nombre, ' ', p.apellido) AS 'cliente'
			FROM muestras AS m
			INNER JOIN pacientes AS p
			ON m.pacientes_id = p.pacientes_id
			WHERE m.muestras_id = 1";
		$result_cliente_muestra = $mysqli->query($query_cliente_muestra) or die($mysqli->error);

		$cliente_muestra = "";

		if($result_cliente_muestra->num_rows>0){
			$valores2 = $result_cliente_muestra->fetch_assoc();

			$cliente_muestra = $valores2['cliente'];
		}

		//AGREGAMOS LA PREFACTURA
		$nombre_producto = "";
		$precio_venta = 0;
		$isv = 0;

		if($producto != 0){
			//OBTENER EL NOMBRE DEL PRODUCTO
			$query_producto = "SELECT nombre, precio_venta, isv FROM productos WHERE productos_id = '$producto'";
			$result_producto = $mysqli->query($query_producto) or die($mysqli->error);

			if($result_producto->num_rows>0){
				$valores2 = $result_producto->fetch_assoc();

				$nombre_producto = $valores2['nombre'];
				$precio_venta = $valores2['precio_venta'];
				$isv = $valores2['isv'];
			}

		}else{
			$producto = "";
		}

		//SI EL CLIENTE ES EMPRESA, muestras_hospitales
		if($empresa !=0 && $pacientes_id != 0){
			//ALMACENAMOS EL CLIENTE DEL ANALISIS QUE ENVIA LA EMPRESA O LABORATORIO
			$muestras_hospitales_id = correlativo('muestras_hospitales_id', 'muestras_hospitales');
			$insert = "INSERT INTO muestras_hospitales
				VALUES('$muestras_hospitales_id','$empresa','$pacientes_id','$muestras_id','$fecha','$usuario','$fecha_registro')";
			$query = $mysqli->query($insert) or die($mysqli->error);
		}

		/*********************************************************************************************************************************************************************/
		$consultar_colaborador = "SELECT CONCAT(nombre, ' ', apellido) AS 'colaborador'
			FROM colaboradores
			WHERE colaborador_id = '$usuario'";
		$resultColaborador = $mysqli->query($consultar_colaborador);
		$consultaColaborador = $resultColaborador->fetch_assoc();
		$NombreColaborador = $consultaColaborador['colaborador'];

		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nuevo cliente: $nombre $apellido, por el usuario: $NombreColaborador";
		$modulo = "Clientes";
		$insert = "INSERT INTO historial
			VALUES('$historial_numero','0','0','$modulo','$pacientes_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/

		/*********************************************************************************************************************************************************************/
		//INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL
		$historial_numero = historial();
		$estado_historial = "Agregar";
		$observacion_historial = "Se ha agregado un nueva muestra con un diagnostico: $diagnostico_clinico";
		$modulo = "Muestras";
		$insert = "INSERT INTO historial
			VALUES('$historial_numero','0','0','$modulo','$muestras_id','$usuario','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";
		$mysqli->query($insert) or die($mysqli->error);
		/*********************************************************************************************************************************************************************/

		//CONSULTAMOS EL TIPO DE PRECIO QUE DEBE ENTREGARSE
		$consultaTipoPrecio = "SELECT ap.precio AS 'precio'
			FROM administrador_precios AS ap
			WHERE ap.hospitales_id = '$hospital_clinica'";
		$resultTipoPrecio = $mysqli->query($consultaTipoPrecio) or die($mysqli->error);

		$TipoPrecio = "";

		if($resultTipoPrecio->num_rows>0){
			$valores2TipoPrecio = $resultTipoPrecio->fetch_assoc();
			$TipoPrecio = $valores2TipoPrecio['precio'];
		}

		//CONSULTAMOS LOS PRECIOS DE LOS productos
		$consultaPrecios = "SELECT precio_venta, precio_venta2, precio_venta3, precio_venta4
			FROM productos
			WHERE productos_id = '$producto'";
		$resulPrecios = $mysqli->query($consultaPrecios) or die($mysqli->error);

		$precio1 = 0;
		$precio2 = 0;
		$precio3 = 0;
		$precio4 = 0;

		if($resulPrecios->num_rows>0){
			$valores2Precos = $resulPrecios->fetch_assoc();
			$precio1 = $valores2Precos['precio_venta'];
			$precio2 = $valores2Precos['precio_venta2'];
			$precio3 = $valores2Precos['precio_venta3'];
			$precio4 = $valores2Precos['precio_venta4'];
		}

		if($TipoPrecio == "Precio1"){
			$precio_venta = $precio1;
		}else if($TipoPrecio == "Precio2"){
			$precio_venta = $precio2;
		}else if($TipoPrecio == "Precio3"){
			$precio_venta = $precio3;
		}if($TipoPrecio == "Precio4"){
			$precio_venta = $precio4;
		}

		$datos = array(
			0 => "Almacenado",
			1 => "Registro Almacenado Correctamente",
			2 => "success",
			3 => "btn-primary",
			4 => "formulario_admision",
			5 => "Registro",
			6 => "formPacientesAdmision",
			7 => "modal_admision_clientes",
			8 => "",
			9 => "Guardar",
			10 => $muestras_id,
			11 => $producto,
			12 => $nombre_producto,
			13 => $precio_venta,
			14 => $isv
		);
	}else{
		$datos = array(
			0 => "Error",
			1 => "Lo sentimos $tipoPacienteConsulta, cuenta con este tipo de muestra, por favor debe procesar por completo la factura antes de agregar otra muestra de este tipo.",
			2 => "error",
			3 => "btn-danger",
			4 => "",
			5 => "",
		);
	}
}else{
	$datos = array(
		0 => "Error",
		1 => "No se puedo almacenar este registro, los datos son incorrectos por favor corregir",
		2 => "error",
		3 => "btn-danger",
		4 => "",
		5 => "",
	);
}

echo json_encode($datos);