<?php
// getEstado.php
// Devuelve opciones para el select Estado de Facturación

header('Content-Type: text/html; charset=UTF-8');

echo "
    <option value='1'>Pendientes</option>
    <option value='2'>Procesadas</option>
    <option value='4'>Crédito</option>
    <option value='3'>Anuladas</option>
";