<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 

	$pacientes_id = $_POST['pacientes_id'];
	
	$query = "SELECT nombre
		FROM pacientes
		WHERE pacientes_id = '$pacientes_id'";
	$result = $mysqli->query($query);

	$result = $insMainModel->getNombreCliente($clientes_id);

	$no_factura = "";
	$cliente = "";	

	if($result->num_rows>=0){
		$factura = $result->fetch_assoc();
		$cliente =$factura['nombre'];
    }	
	
	$datos = array(
		0 => $cliente,							
	);
	echo json_encode($datos);
?>	