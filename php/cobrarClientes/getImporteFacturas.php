<?php	
	session_start();   
	include "../funtions.php";

	//CONEXION A DB
	$mysqli = connect_mysqli(); 

	$factura_id = $_POST['factura_id'];
	
	$query = "SELECT importe
		FROM facturas
		WHERE facturas_id = '$factura_id'";
	$result = $mysqli->query($query);

	$no_factura = "";
	$importe = "";	

	if($result->num_rows>=0){
		$factura = $result->fetch_assoc();
		$importe = number_format($factura['importe'],2);
    }	
	
	$datos = array(
		0 => $importe,							
	);
	echo json_encode($datos);
?>	