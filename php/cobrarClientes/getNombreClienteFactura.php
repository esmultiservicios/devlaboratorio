<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 

	$factura_id = $_POST['factura_id'];
	
	$query = "SELECT p.nombre 'nombre'
		FROM facturas AS f
		INNER JOIN pacientes AS p
		ON f.pacientes_id = p.pacientes_id
		WHERE f.facturas_id = '$factura_id'";

	$result = $mysqli->query($query);

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