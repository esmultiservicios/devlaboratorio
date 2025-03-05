<?php
  if (SISTEMA_PRUEBA=="SI"){ //CAJA
?>
<span class="container-fluid prueba-sistema">SISTEMA DE PRUEBA</span>
<?php
  }
?>

<br /><br />
<div class="footer">
    <div class="row">
        <div class="col-4" style="border-right: 0.5mm ridge rgb(255, 255, 255);">
            <div class="row">
                <div class="col-11 text-right">
                    <small>Copyright &copy; 2017 - <?php echo date("Y");?></small>
                </div>
                <div class="col-1 text-left">
                    <span></span>
                </div>
            </div>
        </div>
        <div class="col-4" style="border-right: 0.5mm ridge rgb(255, 255, 255);">
            <div class="row">
                <div class="col-11 text-left">
                    <div style="text-align: center;">
                        <img src="<?php echo SERVERURL; ?>img/logo_icono.png" width="35px" height="35px">
                    </div>
                </div>
                <div class="col-1 text-right">
                    <small></small>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="row">
                <div class="col-11 text-center">
                    <span class="version" id="version"></span>
                </div>
                <div class="col-1 text-left">
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</div>