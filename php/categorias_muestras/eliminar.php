<?php
session_start();   
include "../funtions.php";
	
//CONEXION A DB
$mysqli = connect_mysqli();

$categoria_id = $_POST['categoria_id'];
$fecha_registro = date("Y-m-d H:i:s");
$fecha = date("Y-m-d");
$usuario = $_SESSION['colaborador_id'];

//CONSULTAR NOMBRE
$consulta_nombre = "SELECT nombre
     FROM categoria
	WHERE categoria_id = categoria_id";
$result = $mysqli->query($consulta_nombre);	

$nombre = "";

if($result->num_rows>0){
     $consulta_nombre1 = $result->fetch_assoc();
     $nombre = $consulta_nombre1['nombre'];
}

//CONSULTAMOS SI EL REGISTRO TIENE DATOS
$consulta = "SELECT categoria_id
   FROM muestras 
   WHERE categoria_id = '$categoria_id'";
$result = $mysqli->query($consulta);

if($result->num_rows==0){
	$query = "DELETE FROM categoria 
	    WHERE categoria_id = '$categoria_id'";
	$query = $mysqli->query($query);

	if($query){
       echo 1;
       //INGRESAR REGISTROS EN LA ENTIDAD HISTORIAL 
       $historial_numero = historial();
       $estado_historial = "Eliminar";
       $observacion_historial = "Se ha eliminado el registro $nombre en la entidad Categoria de Muestras con código $categoria_id";
       $modulo = "Categoría Muestras";
       $insert = "INSERT INTO historial 
          VALUES('$historial_numero','0','0','$modulo','$categoria_id','0','0','$fecha','$estado_historial','$observacion_historial','$usuario','$fecha_registro')";	
       $mysqli->query($insert);	 
       /********************************************/ 
   }else{
      echo 2;
   }
}else{
	echo 3;   
}

$result->free();//LIMPIAR RESULTADO
$mysqli->close();//CERRAR CONEXIÓN
?>