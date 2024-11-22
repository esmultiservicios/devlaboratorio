<!--INICIO MODAL PACIENTES-->
<div class="modal fade" id="modal_busqueda_pacientes" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Pacientes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_pacientes">
                    <div class="table-responsive">
                        <table id="dataTablePacientes" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Paciente</th>
                                    <th>Identidad</th>
                                    <th>Expediente</th>
                                    <th>Correo</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL COLABORADORES-->

<!--INICIO MODAL PARA EL INGRESO DE PACIENTES-->
<div class="modal fade" id="modal_pacientes">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Clientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_pacientes" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required readonly id="pacientes_id" name="pacientes_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-6 mb-3">
                            <label for="expedoente">Expediente</label>
                            <input type="number" name="expediente" class="form-control" id="expediente"
                                placeholder="Expediente o Identidad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edad">Edad</label>
                            <input type="text" class="form-control" name="edad_editar" id="edad_editar" maxlength="100"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                readonly="readonly" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8 mb-3">
                            <label for="nombre">Nombre Completo / Empresa <span class="priority">*<span /></label>
                            <input type="text" required id="name" name="name" placeholder="Nombre Completo / Empresa"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3" style="display: none;">
                            <label for="apellido">Apellido <span class="priority">*<span /></label>
                            <input type="text" id="lastname" name="lastname" placeholder="Apellido"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="rtn">Identidad o RTN <span class="priority">*<span /></label>
                            <input type="number" required id="rtn" name="rtn" class="form-control"
                                placeholder="Identidad o RTN" maxlength="14"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="telefono">Edad <span class="priority">*<span /></label>
                            <input type="number" id="edad" name="edad" class="form-control" placeholder="Edad"
                                maxlength="3" required
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefono">Teléfono 1 <span class="priority">*<span /></label>
                            <input type="number" id="telefono1" name="telefono1" class="form-control"
                                placeholder="Primer Teléfono" required maxlength="8"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefono">Teléfono 2</label>
                            <input type="number" id="telefono2" name="telefono2" class="form-control"
                                placeholder="Segundo Teléfono" maxlength="8"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>
                    <div class="form-row" style="display: none;">
                        <div class="col-md-4 mb-3">
                            <label for="sexo">Fecha de Nacimiento <span class="priority">*<span /></label>
                            <input type="date" required id="fecha_nac" name="fecha_nac"
                                value="<?php echo date ("Y-m-d");?>" class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sexo">Profesión <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select id="profesion" name="profesion" class="custom-select" data-toggle="tooltip"
                                    data-placement="top" title="Profesión">
                                    <option value="">Seleccione</option>
                                </select>
                                <div class="input-group-append" id="buscar_profesion_pacientes">
                                    <a data-toggle="modal" href="#" class="btn btn-outline-success" id="servicio_boton">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="telefono">Religión <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select id="religion" name="religion" class="custom-select" data-toggle="tooltip"
                                    data-placement="top" title="Religión">
                                    <option value="">Seleccione</option>
                                </select>
                                <div class="input-group-append" id="buscar_religion_pacientes">
                                    <a data-toggle="modal" href="#" class="btn btn-outline-success" id="servicio_boton">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="sexo">Sexo <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="sexo" name="sexo" required data-live-search="true"
                                    title="Sexo" data-width="100%">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="departamento_id">Departamentos <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="departamento_id" name="departamento_id" required
                                    data-live-search="true" title="Departamentos" data-width="100%">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="municipio_id">Municipios <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="municipio_id" name="municipio_id" required
                                    data-live-search="true" title="Municipios" data-width="100%">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="departamento_id">Tipo Cliente <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="paciente_tipo" name="paciente_tipo" required
                                    data-live-search="true" title="Tipo Cliente" data-width="100%">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion" data-toggle="tooltip"
                                data-placement="top" placeholder="Dirección Exacta" class="form-control" maxlength="150"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="telefono_proveedores">Correo</label>
                            <input type="email" name="correo" id="correo" placeholder="alguien@algo.com"
                                class="form-control" data-toggle="tooltip" data-placement="top"
                                title="Este correo será utilizado para enviar las citas creadas y las reprogramaciones, como las notificaciones de las citas pendientes de los usuarios."
                                maxlength="100" /><label id="validate"></label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formulario_pacientes" type="submit" id="reg">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE PACIENTES-->

<!--INFORMACIÓN DE MUESTRAS-->
<div class="modal fade" id="modal_historico_muestras" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Histórico de Muestras</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-inline" id="form_main_historico_muestras">

                    <div class="col-md-12 mb-3">
                        <input type="hidden" readonly id="pacientes_id_muestras" name="pacientes_id_muestras"
                            class="form-control" />
                        <div class="input-group mb-3">
                            <input type="text" placeholder="Buscar por: Muestra, Tipo de Muestra" data-toggle="tooltip"
                                data-placement="top" title="Buscar por: Muestra, Tipo de Muestra" id="bs_regis"
                                autofocus class="form-control" size="52" />
                        </div>
                    </div>
                </form>

                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="registros overflow-auto" id="detalles-historico-muestras"></div>
                    </div>
                </div>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center" id="pagination-historico-muestras"></ul>
                </nav>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success ml-2" type="submit" id="okay" data-dismiss="modal">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay
                </button>
            </div>
        </div>
    </div>
</div>

<!--INICIO MODAL CIERRE DE CAJA-->
<div class="modal fade" id="modalCierreCaja">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cierre de Caja</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formularioCierreCaja" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">

                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Fecha <span class="priority">*<span /></label>
                            <input type="date" required id="fechaCierreCaja" name="fechaCierreCaja"
                                value="<?php echo date ("Y-m-d");?>" class="form-control" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="generarCierreCaja" form="formularioCierreCaja">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-cash-register fa-lg"></i> Generar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL CIERRE DE CAJA-->

<!--INICIO MODAL PACIENTES-->
<div class="modal fade" id="modal_busqueda_colaboradores" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Colaboradores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_coloboradores">
                    <div class="table-responsive">
                        <table id="dataTableColaboradores" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Colaborador</th>
                                    <th>Identidad</th>
                                    <th>Puesto</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL COLABORADORES-->
<div class="modal fade" id="registrar_colaboradores">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Colaboradores</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_colaboradores" action="" method="POST" data-form=""
                    autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required readonly id="colaborador_id" name="colaborador_id" />
                            <input type="hidden" id="id-registro" name="id-registro" class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-4 mb-3">
                            <label for="expedoente">Nombre <span class="priority">*<span /></label>
                            <input type="text" required name="nombre" id="nombre" maxlength="100"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edad">Apellido <span class="priority">*<span /></label>
                            <input type="text" required name="apellido" id="apellido" maxlength="100"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edad">Identidad <span class="priority">*<span /></label>
                            <input type="text" required name="identidad" id="identidad" maxlength="100"
                                class="form-control" data-toggle="tooltip" data-placement="top"
                                title="Este número de Identidad debe estar exactamente igual al que se registro en Odoo en la ficha del Colaborador" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="nombre">Empresa <span class="priority">*<span /></label>
                            <select id="empresa" name="empresa" class="form-control" data-toggle="tooltip"
                                data-placement="top" title="Seleccione la Empresa" required>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido">Puesto <span class="priority">*<span /></label>
                            <select id="puesto" name="puesto" class="form-control" data-toggle="tooltip"
                                data-placement="top" title="Seleccione el Puesto" required>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha">Estatus <span class="priority">*<span /></label>
                            <select id="estatus" name="estatus" class="form-control" data-toggle="tooltip"
                                data-placement="top" title="Estatus" required>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_colaboradores"
                    form="formulario_colaboradores">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_colaboradores"
                    form="formulario_colaboradores">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Modificar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL COLABORADORES-->

<!--INICIO MODAL MOVIMIENTO DE PRODUCTOS-->
<div class="modal fade" id="modal_movimientos">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Movimiento de Productos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formularioMovimientos" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" id="movimientos_id" name="movimientos_id" class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="categoria">Categoría<span class="priority"> *<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="movimiento_categoria" name="movimiento_categoria"
                                    required data-live-search="true" title="Categoría" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="categoria">Productos<span class="priority"> *<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="movimiento_producto" name="movimiento_producto"
                                    required data-live-search="true" title="Productos" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="categoria">Tipo Operación<span class="priority"> *<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="movimiento_operacion" name="movimiento_operacion"
                                    required data-live-search="true" title="Tipo Operación" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Cantidad <span class="priority">*<span /></label>
                            <input type="number" required id="movimiento_cantidad" name="movimiento_cantidad"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header text-white bg-info mb-3" align="center">
                                    Comentario
                                </div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="col-md-12 mb-3">
                                            <div class="input-group">
                                                <textarea id="comentario" name="comentario" placeholder="Comentario"
                                                    class="form-control" maxlength="1000" rows="7"></textarea>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="btn btn-outline-success fas fa-microphone-alt"
                                                            id="search_comentario_movimientos_start"></i>
                                                        <i class="btn btn-outline-success fas fa-microphone-slash"
                                                            id="search_comentario_movimientos_stop"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <p id="charNum_comentario">1000 Caracteres</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="modal_movimientos" form="formularioMovimientos">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL MOVIMIENTO DE PRODUCTOS-->

<!--INICIO MODAL PARA INGRESO DE PLANTILLAS-->
<div class="modal fade" id="modal_plantillas">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro de Plantillas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formularioPlantillas" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" id="plantillas_id" name="plantillas_id" class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-3 mb-3">
                            <label for="plantilla_atencion">Tipo de Atención <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="plantilla_atencion" name="plantilla_atencion" required
                                    data-live-search="true" title="Tipo de Atención">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label>Asunto <span class="priority">*<span /></label>
                            <input type="text" required name="plantilla_asunto" id="plantilla_asunto" maxlength="100"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Descripción</label>
                            <textarea id="plantilla_descripcion" name="plantilla_descripcion" placeholder="Descripción"
                                class="form-control" maxlength="10000" rows="10" required></textarea>
                            <p id="charNum_plantilla_descripcion">3200 Caracteres</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_plantilla" form="formularioPlantillas">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_plantilla" form="formularioPlantillas">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
                <button class="btn btn-danger ml-2" type="submit" id="delete_plantilla" form="formularioPlantillas">
                    <div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA INGRESO DE PLANTILLAS-->

<!--INICIO MODAL PARA INGRESO DE USUARIOS-->
<div class="modal fade" id="registrar">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro de Usuarios</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" id="id-registro" name="id-registro" class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <div class="picture-container">
                                <div class="picture">
                                    <img src="../img/avatar.jpg" class="picture-src" id="wizardPicturePreview" title="">
                                    <input type="file" id="wizard-picture" class="">
                                </div>
                                <h6 class="">Seleccionar Imagen</h6>

                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="colaborador">Colaborador <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="colaborador" name="colaborador" required
                                    data-live-search="true" title="Colaborador" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estatus">Estatus <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="estatus" name="estatus" required
                                    data-live-search="true" title="Estatus" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>Nickname <span class="priority">*<span /></label>
                            <input type="text" required name="username" id="username" maxlength="100"
                                class="form-control" />
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="email">Email</label>
                            <div class="input-group mb-3">
                                <input type="text" name="email" id="email" class="form-control" placeholder="Email"
                                    required>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-at fa-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="empresa">Empresa <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="empresa" name="empresa" required
                                    data-live-search="true" title="Empresa" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo">Tipo <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="tipo" name="tipo" required data-live-search="true"
                                    title="Tipo" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formulario" type="submit" id="reg_usuarios">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registrar_editar">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro de Usuarios</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_editar" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" id="id-registro1" name="id-registro1" class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-6 mb-3">
                            <label for="colaborador1">Colaborador <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="colaborador1" name="colaborador1" required
                                    data-live-search="true" title="Colaborador" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estatus1">Estatus <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="estatus1" name="estatus1" required
                                    data-live-search="true" title="Estatus" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-12 mb-3">
                            <label>Email <span class="priority">*<span /></label>
                            <input type="email" required name="email1" id="email1" maxlength="100"
                                class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="empresa1">Empresa <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="empresa1" name="empresa1" required
                                    data-live-search="true" title="Empresa" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo1">Tipo <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="tipo1" name="tipo1" required data-live-search="true"
                                    title="Tipo" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formulario_editar" type="submit" id="editar_usuarios">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<!--INICIO MODAL PARA EL INGRESO DE PRECLINICA-->
<div class="modal fade" id="agregar_preclinica">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_agregar_preclinica" data-async data-target="#rating-modal"
                    action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" readonly id="id-registro" name="id-registro"
                                readonly="readonly" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>Expediente <span class="priority">*<span /></label>
                            <input type="number" required id="expediente" placeholder="Expediente o Identidad"
                                name="expediente" class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fecha <span class="priority">*<span /></label>
                            <input type="date" required readonly id="fecha" name="fecha"
                                value="<?php echo date ("Y-m-d");?>" class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Identidad </label>
                            <input type="text" readonly id="identidad" name="identidad" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" required readonly id="nombre" name="nombre" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Profesional</label>
                            <input type="text" readonly id="profesional_consulta" name="profesional_consulta"
                                class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>Presión Arterial (PA)</label>
                            <input type="text" id="pa" name="pa" class="form-control" placeholder="Presión Arterial" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Frecuencia Respiratoria (FR)</label>
                            <input type="number" id="fr" name="fr" class="form-control"
                                placeholder="Frecuencia Respiratoria" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Frecuencia Cardiaca </label>
                            <input type="number" id="fc" name="fc" class="form-control"
                                placeholder="Frecuencia Cardiaca" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>Temperatura</label>
                            <input type="number" id="temperatura" name="temperatura" class="form-control"
                                placeholder="Temperatura" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Peso</label>
                            <input type="text" id="peso" name="peso" class="form-control" placeholder="Peso" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Talla</label>
                            <input type="text" id="talla" name="talla" class="form-control" placeholder="Talla" />
                        </div>
                    </div>
                    <div class="form-row" id="grupo">
                        <div class="col-md-6 mb-3">
                            <label>Consultorio</label>
                            <div class="input-group mb-3">
                                <select id="servicio" name="servicio" class="custom-select" data-toggle="tooltip"
                                    data-placement="top" title="Servicio"></select>
                                <div class="input-group-append" id="buscar_servicios_preclinica">
                                    <a data-toggle="modal" href="#" class="btn btn-outline-success">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Profesional</label>
                            <div class="input-group mb-3">
                                <select id="medico" name="medico" class="custom-select" data-toggle="tooltip"
                                    data-placement="top" title="Profesional"></select>
                                <div class="input-group-append" id="buscar_profesionales_preclinica">
                                    <a data-toggle="modal" href="#" class="btn btn-outline-success">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-search fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Observaciones</label>
                            <input type="text" id="observaciones" name="observaciones" class="form-control"
                                placeholder="Observaciones" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formulario_agregar_preclinica" type="submit"
                    id="reg_preclinica">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-primary ml-2" form="formulario_agregar_preclinica" type="submit"
                    id="edit_preclinica">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE PRECLINICA-->

<!--INICIO MODAL DEPARTAMENTOS-->
<div class="modal fade" id="modal_busqueda_departamentos" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda Departamentos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_departamentos">
                    <div class="table-responsive">
                        <table id="dataTableDepartamentos" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Departamento</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL DEPARTAMENTOS-->

<!--INICIO MODAL ATENCIONES-->
<div class="modal fade" id="modal_busqueda_atenciones_plantillas" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda Atenciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_atenciones_plantillas">
                    <div class="table-responsive">
                        <table id="dataTableAtencionesPlantillas"
                            class="table table-striped table-condensed table-hover" style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Atención</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL ATENCIONES-->

<!--INICIO MODAL TIPO DE MUESTRA-->
<div class="modal fade" id="modal_busqueda_tipo_mmuestra" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda Tipo Muestra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_tipo_mmuestra">
                    <div class="table-responsive">
                        <table id="dataTableTipoMuestra" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Tipo Muestra</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL TIPO DE MUESTRA-->

<!--INICIO MODAL DEPARTAMENTOS-->
<div class="modal fade" id="modal_busqueda_municipios" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda Municipios</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_municipios">
                    <div class="table-responsive">
                        <table id="dataTableMunicipios" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Departamento</th>
                                    <th>Municipio</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL DEPARTAMENTOS-->

<!--INICIO MODAL SERVICIOS-->
<div class="modal fade" id="modal_busqueda_servicios" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Consultorios</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_servicios">
                    <div class="table-responsive">
                        <table id="dataTableServicios" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Consultorio</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL SERVICIOS-->

<!--INICIO MODAL PROFESION-->
<div class="modal fade" id="modal_busqueda_profesion" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Profesiones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_profesion">
                    <div class="table-responsive">
                        <table id="dataTableProfesiones" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Profesión</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PROFESION-->

<!--INICIO MODAL RELIGION-->
<div class="modal fade" id="modal_busqueda_religion" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Religiones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_religion">
                    <div class="table-responsive">
                        <table id="dataTableReligion" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Religión</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL RELIGION-->

<!--INICIO MODAL TABLAS DB-->
<div class="modal fade" id="modal_tablas_db" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Tablas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_tablas_db">
                    <div class="table-responsive">
                        <table id="dataTableTablas" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleccionar</th>
                                    <th>Tabla</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL TABLAS DB-->

<!--INICIO MODAL PACIENTES-->
<div class="modal fade" id="modal_busqueda_hospitales" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Hospitales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_hospitales">
                    <div class="table-responsive">
                        <table id="dataTableHospitales" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Hospital/Clínica</th>
                                    <th>Editar/Clínica</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL CLINICA Y HOSPITALES-->
<div class="modal fade" id="modalHospitales">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Hospitales</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formularioHospitales" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" id="hospitales_id" name="hospitales_id" class="form-control">
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Hospital / Clínica <span class="priority">*<span /></label>
                            <input type="text" name="hospitales" id="hospitales" class="form-control"
                                id="contranaterior" placeholder="Hospital o Clínica" required="required">
                        </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formularioHospitales" type="submit" id="reg_hospitales">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" form="formularioHospitales" type="submit" id="edi_hospitales">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Modificar
                </button>
                <button class="btn btn-danger ml-2" form="formularioHospitales" type="submit" id="delete_hospitales">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-trash fa-lg"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL CLINICA Y HOSPITALES-->

<!--INICIO MODAL PACIENTES-->
<div class="modal fade" id="modal_busqueda_empresa" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Empresas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_empresa">
                    <div class="table-responsive">
                        <table id="dataTableEmpresa" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Empresa</th>
                                    <th>RTN</th>
                                    <th>Dirección</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PACIENTES-->

<!--INICIO MODAL PRODUCTOS-->
<div class="modal fade" id="modal_busqueda_productos_facturas" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Búsqueda de Productos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario_busqueda_productos_facturas">
                    <input type="hidden" id="row" name="row" class="form-control" />
                    <input type="hidden" id="col" name="col" class="form-control" />
                    <div class="table-responsive">
                        <table id="dataTableProductosFacturas" class="table table-striped table-condensed table-hover"
                            style="width:100%">
                            <thead align="center">
                                <tr>
                                    <th>Seleecionar</th>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th>Concentración</th>
                                    <th>Medida</th>
                                    <th>Cantidad</th>
                                    <th>Precio Venta1</th>
                                    <th>Precio Venta2</th>
                                    <th>Precio Venta3</th>
                                    <th>Precio Venta4</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
<!--FIN MODAL PRODUCTOS-->

<!--INICIO MODAL PARA EL INGRESO DE ADENDUMS-->
<div class="modal fade" id="modal_adendum">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Adendum</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal FormularioAjax" id="formularioAdendum" action="" method="POST" data-form=""
                    enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" id="muestras_id" name="muestras_id" />
                            <input type="hidden" required="required" id="atencion_id" name="atencion_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>Número</label>
                            <input type="text" required="required" readonly id="numero_bioxia_adendum"
                                name="numero_bioxia_adendum" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Paciente</label>
                            <input type="text" required="required" readonly id="paciente_bioxia_adendum"
                                name="paciente_bioxia_adendum" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Descripción</label>
                            <textarea id="descripcion_adendum" name="descripcion_adendum" placeholder="Descripción"
                                class="form-control" maxlength="10000" rows="10" required></textarea>
                            <p id="charNum_adendum">10000 Caracteres</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_adendum" form="formularioAdendum">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE ADENDUMS-->

<!--INICIO MODAL PARA EL INGRESO DE PRODUCTOS-->
<div class="modal fade" id="modal_productos">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Productos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal FormularioAjax" id="formulario_productos" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" id="productos_id" name="productos_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>Producto <span class="priority">*<span /></label>
                            <input type="text" required class="form-control" name="nombre" id="nombre"
                                placeholder="Producto o Servicio" maxlength="150"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="categoria">Tipo<span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="categoria" name="categoria" required
                                    data-live-search="true" title="Tipo" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="categoria">Categoria <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="categoria_producto" name="categoria_producto" required
                                    data-live-search="true" title="Categoria" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3" style="display:none;">
                            <label for="concentracion">Concentración <span class="priority">*<span /></label>
                            <input type="text" id="concentracion" name="concentracion" step="0.01"
                                placeholder="Concentración" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="medida">Medida <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="medida" name="medida" required data-live-search="true"
                                    title="Medida" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="almacen">Almacén <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="almacen" name="almacen" required
                                    data-live-search="true" title="Almacén" data-size="10">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Cantidad <span class="priority">*<span /></label>
                            <input type="number" required id="cantidad" name="cantidad" placeholder="Cantidad"
                                class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Precio de Compra <span class="priority">*<span /></label>
                            <input type="number" required id="precio_compra" name="precio_compra" step="0.01"
                                placeholder="Precio Compra" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label>Precio de Venta 1<span class="priority">*<span /></label>
                            <input type="number" required id="precio_venta" name="precio_venta" step="0.01"
                                placeholder="Precio Venta 1" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Precio de Venta 2</label>
                            <input type="number" id="precio_venta2" name="precio_venta2" step="0.01"
                                placeholder="Precio Venta 2" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Precio de Venta 3</label>
                            <input type="number" id="precio_venta3" name="precio_venta3" step="0.01"
                                placeholder="Precio Venta 3" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Precio de Venta 4</label>
                            <input type="number" id="precio_venta4" name="precio_venta4" step="0.01"
                                placeholder="Precio Venta 4" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label>Cantidad Mínima</label>
                            <input type="number" id="cantidad_minima" name="cantidad_minima"
                                placeholder="Cantidad Mínima" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Cantidad Máxima</label>
                            <input type="number" id="cantidad_maxima" name="cantidad_maxima"
                                placeholder="Cantidad Máxima" class="form-control" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label>Descripción</label>
                            <textarea id="descripcion" name="descripcion" placeholder="Descripción" class="form-control"
                                maxlength="150" rows="2"></textarea>
                            <p id="charNum_descripcion">150 Caracteres</p>
                        </div>
                    </div>

                    <div class="form-group custom-control custom-checkbox custom-control-inline">
                        <div class="col-md-5">
                            <label class="switch">
                                <input type="checkbox" id="producto_activo" name="producto_activo" value="1" checked>
                                <div class="slider round"></div>
                            </label>
                            <span class="question mb-2" id="label_producto_activo"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-check-label mr-1" for="defaultCheck1">¿ISV Venta?</label>
                            <label class="switch">
                                <input type="checkbox" id="producto_isv_factura" name="producto_isv_factura" value="1">
                                <div class="slider round"></div>
                            </label>
                            <span class="question mb-2" id="label_producto_isv_factura"></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_producto" form="formulario_productos">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_producto" form="formulario_productos">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
                <button class="btn btn-danger ml-2" type="submit" id="delete_producto" form="formulario_productos">
                    <div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE PRODUCTOS-->

<!--INICIO MODAL PARA EL INGRESO DE ALMACENES-->
<div class="modal fade" id="modal_almacen">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Almacén</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal FormularioAjax" id="formulario_almacen" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" readonly id="almacen_id" name="almacen_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>Almacén <span class="priority">*<span /></label>
                            <input type="text" required class="form-control" name="almacen" id="almacen"
                                placeholder="Almacén" maxlength="30"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ubicacion">Ubicación <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="ubicacion" name="ubicacion" required
                                    data-live-search="true" title="Ubicación" data-size="3">
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_almacen" form="formulario_almacen">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_almacen" form="formulario_almacen">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
                <button class="btn btn-danger ml-2" type="submit" id="delete_almacen" form="formulario_almacen">
                    <div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE ALMACENES-->

<!--INICIO MODAL PARA EL INGRESO DE UBICACION-->
<div class="modal fade" id="modal_ubicacion">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ubicación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal FormularioAjax" id="formulario_ubicacion" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" readonly id="ubicacion_id" name="ubicacion_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label>Ubicación <span class="priority">*<span /></label>
                            <input type="text" required class="form-control" name="ubicacion" id="ubicacion"
                                placeholder="Ubicación	" maxlength="30"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Empresa <span class="priority">*<span /></label>
                            <select id="empresa" name="empresa" class="custom-select" data-toggle="tooltip"
                                data-placement="top" title="Empresa" required>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_ubicacion" form="formulario_ubicacion">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_ubicacion" form="formulario_ubicacion">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
                <button class="btn btn-danger ml-2" type="submit" id="delete_ubicacion" form="formulario_ubicacion">
                    <div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE UBICACION-->

<!--INICIO MODAL PARA EL INGRESO DE MEDIDAS-->
<div class="modal fade" id="modal_medidas">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Medidas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal FormularioAjax" id="formulario_medidas" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" readonly id="medida_id" name="medida_id" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label>Medida <span class="priority">*<span /></label>
                            <input type="text" required id="medidas" name="medidas" placeholder="Medida" readonly
                                class="form-control" maxlength="3"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="apellido_proveedores">Descripción <span class="priority">*<span /></label>
                            <input type="text" required id="descripcion_medidas" name="descripcion_medidas"
                                placeholder="Descripción" readonly class="form-control" maxlength="30"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_medidas" form="formulario_medidas">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="edi_medidas" form="formulario_medidas">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
                <button class="btn btn-danger ml-2" type="submit" id="delete_medidas" form="formulario_medidas">
                    <div class="sb-nav-link-icon"></div><i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA EL INGRESO DE MEDIDAS-->

<!--INICIO MODAL PAGOS FACTURACION---->
<div class="modal fade" id="modal_pagos">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Método de pago</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="row justify-content-center">
                    <div class="col-lg-12 col-12">
                        <div class="card card0">
                            <div class="d-flex" id="wrapper">
                                <!-- Sidebar -->
                                <div class="bg-light border-right" id="sidebar-wrapper"
                                    style="scroll-behavior: smooth;">
                                    <div class="sidebar-heading pt-5 pb-4"><strong>Método de pago</strong></div>
                                    <div class="list-group list-group-flush">

                                        <a data-toggle="tab" href="#menu1" id="tab1"
                                            class="tabs list-group-item bg-light active1">
                                            <div class="list-div my-2">
                                                <div class="fas fa-money-bill-alt fa-lg"></div> &nbsp;&nbsp; Efectivo
                                            </div>
                                        </a>
                                        <a data-toggle="tab" href="#menu2" id="tab2" class="tabs list-group-item">
                                            <div class="list-div my-2">
                                                <div class="far fa-credit-card fa-lg"></div> &nbsp;&nbsp; Tarjeta
                                            </div>
                                        </a>
                                        <a data-toggle="tab" href="#menu3" id="tab3"
                                            class="tabs list-group-item bg-light">
                                            <div class="list-div my-2">
                                                <div class="fas fa-exchange-alt fa-lg"></div> &nbsp;&nbsp; Transferencia
                                            </div>
                                        </a>
                                        <a data-toggle="tab" href="#menu4" id="tab4"
                                            class="tabs list-group-item bg-light">
                                            <div class="list-div my-2">
                                                <div class="fas fa-money-check fa-lg"></div> &nbsp;&nbsp; Cheque
                                            </div>
                                        </a>
                                        <div class="container mt-md-0" style="display: none;">
                                            <p class="mb-0 mt-3">Imprimir Comprobante de Entrega:</p>
                                            <label class="switch mb-2" data-toggle="tooltip" data-placement="top">
                                                <input type="checkbox" id="" name="comprobante_print_switch" value="0">
                                                <div class="slider round"></div>
                                            </label>
                                            <span class="question mb-2" id="label_print_comprobant"></span>
                                        </div>
                                        <div class="container mt-md-0" id="GrupoPagosMultiplesFacturas"
                                            style="display: none;">
                                            <p class="mb-0 mt-3">Pagos Multiples:</p>
                                            <label class="switch mb-2" data-toggle="tooltip" data-placement="top">
                                                <input type="checkbox" id="pagos_multiples_switch"
                                                    name="pagos_multiples_switch" value="0">
                                                <div class="slider round"></div>
                                            </label>
                                            <span class="question mb-2" id="label_pagos_multiples"></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page Content -->
                                <div id="page-content-wrapper" style="scroll-behavior: smooth;">
                                    <div class="row pt-3" id="border-btm">
                                        <div class="col-2">
                                            <i id="menu-toggle1"
                                                class="fas fa-angle-double-left fa-2x menu-toggle1"></i>
                                            <i id="menu-toggle2"
                                                class="fas fa-angle-double-right fa-2x menu-toggle2"></i>
                                        </div>
                                        <div class="col-10">
                                            <div class="row justify-content-right">
                                                <div class="col-12">
                                                    <p class="mb-0 mr-4 mt-4 text-right" id="customer-name-bill"></p>
                                                    <input type="hidden" name="customer_bill_pay" id="customer_bill_pay"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="row justify-content-right">
                                                <div class="col-12">
                                                    <p class="mb-0 mr-4 text-right color-text-white"><b>Pagar</b> <span
                                                            class="top-highlight" id="bill-pay"></span> </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-content">
                                        <div id="menu1" class="tab-pane in active">
                                            <div class="row justify-content-center">
                                                <div class="col-11">
                                                    <div class="form-card">
                                                        <h3 class="mt-0 mb-4 text-center">Ingrese detalles del Pago</h3>
                                                        <form class="FormularioAjax" id="formEfectivoBill"
                                                            action="<?php echo SERVERURL;?>php/facturacion/addPagoEfectivo.php"
                                                            method="POST" data-form="save" autocomplete="off"
                                                            enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_efectivo">Fecha</label>
                                                                        <input type="date" name="fecha_efectivo"
                                                                            id="fecha_efectivo" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="monto_efectivo">Efectivo</label>
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="hidden" name="factura_id_efectivo"
                                                                            id="factura_id_efectivo">
                                                                        <input type="hidden" name="tipo_factura"
                                                                            id="tipo_factura" value="1">
                                                                        <input type="hidden" name="monto_efectivo"
                                                                            id="monto_efectivo" step="0.01"
                                                                            placeholder="0.00">
                                                                        <input type="number" name="efectivo_bill"
                                                                            id="efectivo_bill" class="inputfield"
                                                                            step="0.01" placeholder="0.00" step="0.01">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="input-group" id="grupo_cambio_efectivo">
                                                                        <label for="cambio_efectivo">Cambio</label>
                                                                        <input type="number" readonly
                                                                            name="cambio_efectivo" id="cambio_efectivo"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>Quien Recibe</label>
                                                                    <div class="input-group">
                                                                        <select id="usuario_efectivo"
                                                                            name="usuario_efectivo"
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Usuario que Recibe">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="submit" value="Efectuar Pago"
                                                                        id="pago_efectivo"
                                                                        class="mt-3 pay btn btn-info placeicon"
                                                                        form="formEfectivoBill">
                                                                </div>
                                                            </div>
                                                            <div class="RespuestaAjax"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="menu2" class="tab-pane">
                                            <div class="row justify-content-center">
                                                <div class="col-11">
                                                    <div class="form-card">
                                                        <h3 class="mt-0 mb-4 text-center">Ingrese detalles de la Tarjeta
                                                        </h3>
                                                        <form class="FormularioAjax" id="formTarjetaBill" method="POST"
                                                            data-form="save"
                                                            action="<?php echo SERVERURL;?>php/facturacion/addPagoTarjeta.php"
                                                            autocomplete="off" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_tarjeta">Fecha</label>
                                                                        <input type="date" name="fecha_tarjeta"
                                                                            id="fecha_tarjeta" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label>Número de Tarjeta</label>
                                                                        <input type="hidden" name="factura_id_tarjeta"
                                                                            id="factura_id_tarjeta">
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="text" id="cr_bill" name="cr_bill"
                                                                            class="inputfield" placeholder="XXXX">
                                                                        <input type="number" style="display:none;"
                                                                            name="monto_efectivo"
                                                                            id="monto_efectivo_tarjeta"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Ingrese el monto">
                                                                        <input type="hidden" name="importe"
                                                                            id="importe_tarjeta" class="inputfield"
                                                                            step="0.01" placeholder="0.00">
                                                                        <input type="hidden" name="tipo_factura"
                                                                            id="tipo_factura" value="1">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="input-group">
                                                                        <label> Fecha de Expiración</label>
                                                                        <input type="text" name="exp" id="exp"
                                                                            class="mask inputfield" placeholder="MM/YY">
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="input-group">
                                                                        <label>Número Aprobación</label>
                                                                        <input type="text" name="cvcpwd" id="cvcpwd"
                                                                            class="placeicon inputfield">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Quien Recibe</label>
                                                                    <div class="input-group">
                                                                        <select id="usuario_tarjeta"
                                                                            name="usuario_tarjeta"
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Usuario que Recibe">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="submit" value="Efectuar Pago"
                                                                        id="pago_tarjeta"
                                                                        class="mt-3 pay btn btn-info placeicon"
                                                                        form="formTarjetaBill">
                                                                </div>
                                                            </div>
                                                            <div class="RespuestaAjax"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="menu5" class="tab-pane">
                                            <div class="row justify-content-center">
                                                <div class="col-11">
                                                    <div class="form-card">
                                                        <h6 class="mt-0 mb-4 text-center">Ingrese Pago Mixto</h6>
                                                        <form class="FormularioAjax" id="formMixtoBill"
                                                            action="<?php echo SERVERURL;?>php/facturacion/addPagoMixto.php"
                                                            method="POST" data-form="save" autocomplete="off"
                                                            enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_efectivo_mixto">Fecha</label>
                                                                        <input type="date" name="fecha_efectivo_mixto"
                                                                            id="fecha_efectivo_mixto" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6">
                                                                    <div class="input-group">
                                                                        <label for="monto_efectivo">Efectivo</label>
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="hidden" name="factura_id_mixto"
                                                                            id="factura_id_mixto">
                                                                        <input type="hidden" name="monto_efectivo"
                                                                            id="monto_efectivo_mixto" step="0.01"
                                                                            placeholder="0.00" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Ingrese el monto">
                                                                        <input type="number" name="efectivo_bill"
                                                                            id="efectivo_bill_mixto" class="inputfield"
                                                                            step="0.01" placeholder="0.00" step="0.01">
                                                                        <input type="hidden" readonly
                                                                            name="cambio_efectivo"
                                                                            id="cambio_efectivo_mixto"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <div class="input-group">
                                                                        <label for="monto_tarjeta">Tarjeta</label>
                                                                        <input type="number" readonly
                                                                            name="monto_tarjeta" id="monto_tarjeta"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label>Número de Tarjeta</label>
                                                                        <input type="text" id="cr_bill_mixto"
                                                                            name="cr_bill" class="inputfield"
                                                                            placeholder="XXXX">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="input-group">
                                                                        <label> Fecha de Expiración</label>
                                                                        <input type="text" name="exp" id="exp_mixto"
                                                                            class="mask inputfield" placeholder="MM/YY">
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="input-group">
                                                                        <label>Número Aprobación</label>
                                                                        <input type="text" name="cvcpwd"
                                                                            id="cvcpwd_mixto"
                                                                            class="placeicon inputfield">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Quien Recibe</label>
                                                                    <div class="input-group">
                                                                        <select id="usuario_pago_mixto"
                                                                            name="usuario_pago_mixto"
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Usuario que Recibe">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="submit" value="Efectuar Pago"
                                                                        id="pago_efectivo_mixto"
                                                                        class="mt-3 pay btn btn-info placeicon"
                                                                        form="formMixtoBill">
                                                                </div>
                                                            </div>
                                                            <div class="RespuestaAjax"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="menu3" class="tab-pane">
                                            <div class="row justify-content-center">
                                                <div class="col-11">
                                                    <div class="form-card">
                                                        <h3 class="mt-0 mb-4 text-center">Ingrese detalles de la
                                                            Transferencia</h3>
                                                        <form class="FormularioAjax" id="formTransferenciaBill"
                                                            method="POST" data-form="save"
                                                            action="<?php echo SERVERURL;?>php/facturacion/addPagoTransferencia.php"
                                                            autocomplete="off" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_transferencia">Fecha</label>
                                                                        <input type="date" name="fecha_transferencia"
                                                                            id="fecha_transferencia" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 mb-3">
                                                                    <label>Banco</label>
                                                                    <div class="input-group">
                                                                        <input type="hidden"
                                                                            name="factura_id_transferencia"
                                                                            id="factura_id_transferencia">
                                                                        <select id="bk_nm" name="bk_nm" required
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Banco">
                                                                        </select>
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" name="monto_efectivo"
                                                                            id="monto_efectivo" placeholder="0.00">
                                                                        <input type="number" name="importe"
                                                                            id="importe_transferencia"
                                                                            class="inputfield mt-5" step="0.01"
                                                                            placeholder="0.00" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Ingrese el monto">
                                                                        <input type="hidden" name="tipo_factura"
                                                                            id="tipo_factura_transferencia" value="1"
                                                                            step="0.01" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label>Número de Autorización</label>
                                                                        <input type="text" name="ben_nm" id="ben_nm"
                                                                            class="inputfield"
                                                                            placeholder="Número de Autorización">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12" style="display: none;">
                                                                    <div class="input-group">
                                                                        <input type="text" name="scode"
                                                                            placeholder="ABCDAB1S" class="placeicon"
                                                                            minlength="8" maxlength="11">
                                                                        <label>SWIFT CODE</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Quien Recibe</label>
                                                                    <div class="input-group">
                                                                        <select id="usuario_transferencia"
                                                                            name="usuario_transferencia"
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Usuario que Recibe">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="submit" value="Efectuar Pago"
                                                                        id="pago_transferencia"
                                                                        class="mt-3 pay btn btn-info placeicon"
                                                                        form="formTransferenciaBill">
                                                                </div>
                                                            </div>
                                                            <div class="RespuestaAjax"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="menu4" class="tab-pane">
                                            <div class="row justify-content-center">
                                                <div class="col-11">
                                                    <div class="form-card">
                                                        <h3 class="mt-0 mb-4 text-center">Ingrese detalles del Cheque
                                                        </h3>
                                                        <form class="FormularioAjax" id="formChequeBill" method="POST"
                                                            data-form="save"
                                                            action="<?php echo SERVERURL;?>php/facturacion/addPagoCheque.php"
                                                            autocomplete="off" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_cheque">Fecha</label>
                                                                        <input type="date" name="fecha_cheque"
                                                                            id="fecha_cheque" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label>Banco</label>
                                                                    <div class="input-group">
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" name="factura_id_cheque"
                                                                            id="factura_id_cheque">
                                                                        <select id="bk_nm_chk" name="bk_nm_chk" required
                                                                            data-size="5" class="selectpicker col-12"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Banco">
                                                                        </select>
                                                                        <input type="hidden" name="monto_efectivo"
                                                                            id="monto_efectivo" placeholder="0.00">
                                                                        <input type="number" name="importe"
                                                                            id="importe_cheque" class="inputfield mt-5"
                                                                            step="0.01" placeholder="0.00"
                                                                            data-toggle="tooltip" data-placement="top"
                                                                            title="Ingrese el monto">
                                                                        <input type="hidden" name="tipo_factura"
                                                                            id="tipo_factura_cheque" value="1"
                                                                            step="0.01" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label>Número de Cheque</label>
                                                                        <input type="text" name="check_num"
                                                                            id="check_num" class="inputfield"
                                                                            placeholder="Número de Cheque">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Quien Recibe</label>
                                                                    <div class="input-group">
                                                                        <select id="usuario_cheque"
                                                                            name="usuario_cheque"
                                                                            class="selectpicker col-12" data-size="5"
                                                                            data-width="100%" data-live-search="true"
                                                                            title="Usuario que Recibe">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <input type="submit" value="Efectuar Pago"
                                                                        id="pago_transferencia"
                                                                        class="mt-3 pay btn btn-info placeicon"
                                                                        form="formChequeBill">
                                                                </div>
                                                            </div>
                                                            <div class="RespuestaAjax"></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PAGOS FACTURACION-->

<!--INICIO MODAL PAGOS FACTURACION---->
<div class="modal fade" id="modal_grupo_pagos">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-12">
                    <div class="card card0">
                        <div class="d-flex" id="wrapper">
                            <!-- Sidebar -->
                            <div class="bg-light border-right" id="sidebar-wrapper">
                                <div class="sidebar-heading pt-5 pb-4"><strong>Método de pago</strong></div>
                                <div class="list-group list-group-flush">

                                    <a data-toggle="tab" href="#menuGrupal1" id="tabGrupal1"
                                        class="tabs list-group-item bg-light active1">
                                        <div class="list-div my-2">
                                            <div class="fas fa-money-bill-alt fa-lg"></div> &nbsp;&nbsp; Efectivo
                                        </div>
                                    </a>
                                    <a data-toggle="tab" href="#menuGrupal2" id="tabGrupal2"
                                        class="tabs list-group-item">
                                        <div class="list-div my-2">
                                            <div class="far fa-credit-card fa-lg"></div> &nbsp;&nbsp; Tarjeta
                                        </div>
                                    </a>
                                    <a data-toggle="tab" href="#menuGrupal5" id="tabGrupal5"
                                        class="tabs list-group-item" style="display: none;">
                                        <div class="list-div my-2">
                                            <div class="fa fa-pause fa-lg"></div> &nbsp;&nbsp; Mixto
                                        </div>
                                    </a>
                                    <a data-toggle="tab" href="#menuGrupal3" id="tabGrupal3"
                                        class="tabs list-group-item bg-light">
                                        <div class="list-div my-2">
                                            <div class="fas fa-exchange-alt fa-lg"></div> &nbsp;&nbsp; Transferencia
                                        </div>
                                    </a>
                                    <a data-toggle="tab" href="#menuGrupal4" id="tabGrupal4"
                                        class="tabs list-group-item bg-light">
                                        <div class="list-div my-2">
                                            <div class="fas fa-money-check fa-lg"></div> &nbsp;&nbsp; Cheque
                                        </div>
                                    </a>
                                </div>
                            </div> <!-- Page Content -->
                            <div id="page-content-wrapper">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <div class="row pt-3" id="border-btm">
                                    <div class="col-2">
                                        <i id="menu-toggleGrupal1"
                                            class="fas fa-angle-double-left fa-2x menu-toggleGrupal1"></i>
                                        <i id="menu-toggleGrupal2"
                                            class="fas fa-angle-double-right fa-2x menu-toggleGrupal2"></i>
                                    </div>
                                    <div class="col-10">
                                        <div class="row justify-content-right">
                                            <div class="col-12">
                                                <p class="mb-0 mr-4 mt-4 text-right" id="customer-name-bill-grupal"></p>
                                                <input type="hidden" name="customer_bill_pay"
                                                    id="customer_bill_pay_grupal" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="row justify-content-right">
                                            <div class="col-12">
                                                <p class="mb-0 mr-4 text-right color-text-white"><b>Pagar</b> <span
                                                        class="top-highlight" id="bill-pay-grupal"></span> </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="text-center" id="test"></div>
                                </div>
                                <div class="tab-content">
                                    <div id="menuGrupal1" class="tab-pane in active">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <div class="form-card">
                                                    <h3 class="mt-0 mb-4 text-center">Ingrese detalles del Pago</h3>
                                                    <form class="FormularioAjax" id="formEfectivoBillGrupal"
                                                        action="<?php echo SERVERURL;?>php/facturacion/addGrupoPagoEfectivo.php"
                                                        method="POST" data-form="save" autocomplete="off"
                                                        enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label for="fecha_efectivo">Fecha</label>
                                                                    <input type="date" name="fecha_efectivo"
                                                                        id="fecha_efectivo" class="inputfield"
                                                                        value="<?php echo date("Y-m-d");?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label for="monto_efectivo">Efectivo</label>
                                                                    <input type="hidden" class="comprobante_print_value"
                                                                        name="comprobante_print" value="0">
                                                                    <input type="hidden" class="multiple_pago"
                                                                        name="multiple_pago" value="0">
                                                                    <input type="hidden" name="factura_id_efectivo"
                                                                        id="factura_id_efectivo">
                                                                    <input type="hidden" name="tipo_factura"
                                                                        id="tipo_factura" value="1">
                                                                    <input type="hidden" name="monto_efectivo"
                                                                        id="monto_efectivo" step="0.01"
                                                                        placeholder="0.00">
                                                                    <input type="number" name="efectivo_bill"
                                                                        id="efectivo_bill" class="inputfield"
                                                                        step="0.01" placeholder="0.00" step="0.01">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="input-group" id="grupo_cambio_efectivo">
                                                                    <label for="cambio_efectivo">Cambio</label>
                                                                    <input type="number" readonly name="cambio_efectivo"
                                                                        id="cambio_efectivo" class="inputfield"
                                                                        step="0.01" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>Quien Recibe</label>
                                                                <div class="input-group">
                                                                    <select id="usuario_efectivo"
                                                                        name="usuario_efectivo"
                                                                        class="selectpicker col-12" data-size="5"
                                                                        data-width="100%" data-live-search="true"
                                                                        title="Usuario que Recibe">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="submit" value="Efectuar Pago"
                                                                    id="pago_efectivo_grupal"
                                                                    class="pay btn btn-info placeicon"
                                                                    form="formEfectivoBillGrupal">
                                                            </div>
                                                        </div>
                                                        <div class="RespuestaAjax"></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menuGrupal2" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <div class="form-card">
                                                    <h3 class="mt-0 mb-4 text-center">Ingrese detalles de la Tarjeta
                                                    </h3>
                                                    <form class="FormularioAjax" id="formTarjetaBillGrupal"
                                                        method="POST" data-form="save"
                                                        action="<?php echo SERVERURL;?>php/facturacion/addGrupoPagoTarjeta.php"
                                                        autocomplete="off" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label for="fecha_tarjeta">Fecha</label>
                                                                    <input type="date" name="fecha_tarjeta"
                                                                        id="fecha_tarjeta" class="inputfield"
                                                                        value="<?php echo date("Y-m-d");?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label>Número de Tarjeta</label>
                                                                    <input type="hidden" name="factura_id_tarjeta"
                                                                        id="factura_id_tarjeta">
                                                                    <input type="hidden" class="comprobante_print_value"
                                                                        name="comprobante_print" value="0">
                                                                    <input type="hidden" class="multiple_pago"
                                                                        name="multiple_pago" value="0">
                                                                    <input type="text" id="cr_bill" name="cr_bill"
                                                                        class="inputfield" placeholder="XXXX">
                                                                    <input type="number" style="display:none;"
                                                                        name="monto_efectivo"
                                                                        id="monto_efectivo_tarjeta" class="inputfield"
                                                                        step="0.01" placeholder="0.00"
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="Ingrese el monto">
                                                                    <input type="hidden" name="importe"
                                                                        id="importe_tarjeta" class="inputfield"
                                                                        step="0.01" placeholder="0.00">
                                                                    <input type="hidden" name="tipo_factura"
                                                                        id="tipo_factura" value="1">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="input-group">
                                                                    <label> Fecha de Expiración</label>
                                                                    <input type="text" name="exp" id="exp"
                                                                        class="mask inputfield" placeholder="MM/YY">
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="input-group">
                                                                    <label>Número Aprobación</label>
                                                                    <input type="text" name="cvcpwd" id="cvcpwd"
                                                                        class="placeicon inputfield">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="submit" value="Efectuar Pago"
                                                                    id="pago_tarjeta_grupal"
                                                                    class="pay btn btn-info placeicon"
                                                                    form="formTarjetaBillGrupal">
                                                            </div>
                                                        </div>
                                                        <div class="RespuestaAjax"></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menuGrupal5" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <div class="form-card">
                                                    <h6 class="mt-0 mb-4 text-center">Ingrese Pago Mixto</h6>
                                                    <form class="FormularioAjax" id="formMixtoBillGrupal"
                                                        action="<?php echo SERVERURL;?>php/facturacion/addGrupoPagoMixto.php"
                                                        method="POST" data-form="save" autocomplete="off"
                                                        enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="col-12 col-md-6">
                                                                <div class="col-12">
                                                                    <div class="input-group">
                                                                        <label for="fecha_efectivo_mixto">Fecha</label>
                                                                        <input type="date" name="fecha_efectivo_mixto"
                                                                            id="fecha_efectivo_mixto" class="inputfield"
                                                                            value="<?php echo date("Y-m-d");?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6">
                                                                    <div class="input-group">
                                                                        <label for="monto_efectivo">Efectivo</label>
                                                                        <input type="hidden"
                                                                            class="comprobante_print_value"
                                                                            name="comprobante_print" value="0">
                                                                        <input type="hidden" class="multiple_pago"
                                                                            name="multiple_pago" value="0">
                                                                        <input type="hidden" name="factura_id_mixto"
                                                                            id="factura_id_mixto">
                                                                        <input type="hidden" name="monto_efectivo"
                                                                            id="monto_efectivo_mixto" step="0.01"
                                                                            placeholder="0.00" data-toggle="tooltip"
                                                                            data-placement="top"
                                                                            title="Ingrese el monto">
                                                                        <input type="number" name="efectivo_bill"
                                                                            id="efectivo_bill_mixto" class="inputfield"
                                                                            step="0.01" placeholder="0.00" step="0.01">
                                                                        <input type="hidden" readonly
                                                                            name="cambio_efectivo"
                                                                            id="cambio_efectivo_mixto"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-md-6">
                                                                    <div class="input-group">
                                                                        <label for="monto_tarjeta">Tarjeta</label>
                                                                        <input type="number" readonly
                                                                            name="monto_tarjeta" id="monto_tarjeta"
                                                                            class="inputfield" step="0.01"
                                                                            placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12 col-md-6">
                                                                <div class="input-group">
                                                                    <label for="monto_tarjeta">Tarjeta</label>
                                                                    <input type="number" readonly name="monto_tarjeta"
                                                                        id="monto_tarjeta" class="inputfield"
                                                                        step="0.01" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label>Número de Tarjeta</label>
                                                                    <input type="text" id="cr_bill_mixto" name="cr_bill"
                                                                        class="inputfield" placeholder="XXXX">

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="input-group">
                                                                    <label> Fecha de Expiración</label>
                                                                    <input type="text" name="exp" id="exp_mixto"
                                                                        class="mask inputfield" placeholder="MM/YY">
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="input-group">
                                                                    <label>Número Aprobación</label>
                                                                    <input type="text" name="cvcpwd" id="cvcpwd_mixto"
                                                                        class="placeicon inputfield">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="submit" value="Efectuar Pago"
                                                                    id="pago_efectivo_mixto_grupal"
                                                                    class="pay btn btn-info placeicon"
                                                                    form="formMixtoBillGrupal">
                                                            </div>
                                                        </div>
                                                        <div class="RespuestaAjax"></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menuGrupal3" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <div class="form-card">
                                                    <h3 class="mt-0 mb-4 text-center">Ingrese detalles de la
                                                        Transferencia</h3>
                                                    <form class="FormularioAjax" id="formTransferenciaBillGrupal"
                                                        method="POST" data-form="save"
                                                        action="<?php echo SERVERURL;?>php/facturacion/addGrupoPagoTransferencia.php"
                                                        autocomplete="off" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label for="fecha_transferencia">Fecha</label>
                                                                    <input type="date" name="fecha_transferencia"
                                                                        id="fecha_transferencia" class="inputfield"
                                                                        value="<?php echo date("Y-m-d");?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 mb-3">
                                                                <label>Banco</label>
                                                                <div class="input-group">
                                                                    <input type="hidden" name="factura_id_transferencia"
                                                                        id="factura_id_transferencia">
                                                                    <select id="bk_nm" name="bk_nm" required
                                                                        class="selectpicker col-12" data-size="5"
                                                                        data-width="100%" data-live-search="true"
                                                                        title="Banco">
                                                                    </select>
                                                                    <input type="hidden" class="multiple_pago"
                                                                        name="multiple_pago" value="0">
                                                                    <input type="hidden" class="comprobante_print_value"
                                                                        name="comprobante_print" value="0">
                                                                    <input type="hidden" name="monto_efectivo"
                                                                        id="monto_efectivo" placeholder="0.00">
                                                                    <input type="number" name="importe"
                                                                        id="importe_transferencia"
                                                                        class="inputfield mt-5" step="0.01"
                                                                        placeholder="0.00" data-toggle="tooltip"
                                                                        data-placement="top" title="Ingrese el monto">
                                                                    <input type="hidden" name="tipo_factura"
                                                                        id="tipo_factura_transferencia" value="1"
                                                                        step="0.01" placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label>Número de Autorización</label>
                                                                    <input type="text" name="ben_nm" id="ben_nm"
                                                                        class="inputfield"
                                                                        placeholder="Número de Autorización">
                                                                </div>
                                                            </div>
                                                            <div class="col-12" style="display: none;">
                                                                <div class="input-group">
                                                                    <input type="text" name="scode"
                                                                        placeholder="ABCDAB1S" class="placeicon"
                                                                        minlength="8" maxlength="11">
                                                                    <label>SWIFT CODE</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="submit" value="Efectuar Pago"
                                                                    id="pago_transferencia_grupal"
                                                                    class="pay btn btn-info placeicon"
                                                                    form="formTransferenciaBillGrupal">
                                                            </div>
                                                        </div>
                                                        <div class="RespuestaAjax"></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menuGrupal4" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <div class="form-card">
                                                    <h3 class="mt-0 mb-4 text-center">Ingrese detalles del Cheque</h3>
                                                    <form class="FormularioAjax" id="formChequeBillGrupal" method="POST"
                                                        data-form="save"
                                                        action="<?php echo SERVERURL;?>php/facturacion/addGrupoPagoCheque.php"
                                                        autocomplete="off" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label for="fecha_cheque">Fecha</label>
                                                                    <input type="date" name="fecha_cheque"
                                                                        id="fecha_cheque" class="inputfield"
                                                                        value="<?php echo date("Y-m-d");?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <label>Banco</label>
                                                                <div class="input-group">
                                                                    <input type="hidden" class="multiple_pago"
                                                                        name="multiple_pago" value="0">
                                                                    <input type="hidden" class="comprobante_print_value"
                                                                        name="comprobante_print" value="0">
                                                                    <input type="hidden" name="factura_id_cheque"
                                                                        id="factura_id_cheque">
                                                                    <select id="bk_nm_chk" name="bk_nm_chk" required
                                                                        data-size="5" class="selectpicker col-12"
                                                                        data-width="100%" data-live-search="true"
                                                                        title="Banco">
                                                                    </select>
                                                                    <input type="hidden" name="monto_efectivo"
                                                                        id="monto_efectivo" placeholder="0.00">
                                                                    <input type="number" name="importe"
                                                                        id="importe_cheque" class="inputfield mt-5"
                                                                        step="0.01" placeholder="0.00"
                                                                        data-toggle="tooltip" data-placement="top"
                                                                        title="Ingrese el monto">
                                                                    <input type="hidden" name="tipo_factura"
                                                                        id="tipo_factura_cheque" value="1" step="0.01"
                                                                        placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="input-group">
                                                                    <label>Número de Cheque</label>
                                                                    <input type="text" name="check_num" id="check_num"
                                                                        class="inputfield"
                                                                        placeholder="Número de Cheque">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="submit" value="Efectuar Pago"
                                                                    id="pago_cheque_grupal"
                                                                    class="pay btn btn-info placeicon"
                                                                    form="formChequeBillGrupal">
                                                            </div>
                                                        </div>
                                                        <div class="RespuestaAjax"></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="menu4" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <h3 class="mt-0 mb-4 text-center">Scan the QR code to pay</h3>
                                                <div class="row justify-content-center">
                                                    <div id="qr"> <img src="" width="200px" height="200px"> </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menu4" class="tab-pane">
                                        <div class="row justify-content-center">
                                            <div class="col-11">
                                                <h3 class="mt-0 mb-4 text-center">Otra forma de pago</h3>
                                                <div class="row justify-content-center">
                                                    <div id="qr"> <img src="" width="200px" height="200px"> </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PAGOS FACTURACION-->

<!--INICIO MODAL PARA FORMULARIO DESCENTOS EN FACTURACION-->
<div class="modal fade" id="modalDescuentoFacturacion">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Descuento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="form-horizontal" id="formDescuentoFacturacion" action="" method="POST" data-form=""
                    enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required="required" readonly id="descuento_productos_id"
                                name="descuento_productos_id" />
                            <input type="hidden" required="required" readonly id="row_index" name="row_index"
                                class="form-control" />
                            <input type="hidden" required="required" readonly id="col_index" name="col_index"
                                class="form-control" />
                            <div class="input-group mb-3">
                                <input type="text" required readonly id="pro_descuento_fact" name="pro_descuento_fact"
                                    class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square fa-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8 mb-3">
                            <label for="producto_descuento_fact">Producto <span class="priority">*<span /></label>
                            <input type="text" readonly required id="producto_descuento_fact"
                                name="producto_descuento_fact" placeholder="Producto" class="form-control"
                                maxlength="11"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="precio_descuento_fact">Precio <span class="priority">*<span /></label>
                            <input type="text" readonly required id="precio_descuento_fact" name="precio_descuento_fact"
                                placeholder="Precio" class="form-control" maxlength="30"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                step="0.01" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="porcentaje_descuento_fact">% Descuento <span class="priority">*<span /></label>
                            <input type="text" required id="porcentaje_descuento_fact" name="porcentaje_descuento_fact"
                                placeholder="Porcentaje de Descuento" class="form-control" maxlength="11"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="descuento_fact">Valor Descuento <span class="priority">*<span /></label>
                            <input type="text" required id="descuento_fact" name="descuento_fact"
                                placeholder="Descuento" class="form-control" maxlength="30"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                step="0.01" />
                        </div>
                    </div>
                    <div class="RespuestaAjax"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="guardar btn btn-primary ml-2" type="submit" id="reg_DescuentoFacturacion"
                    form="formDescuentoFacturacion">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA FORMULARIO DESCENTOS EN FACTURACION-->


<!--INICIO ADMISION CLIENTES-->
<div class="modal fade" id="modal_admision_clientes">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro Clientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="FormularioAjax form-horizontal" id="formulario_admision" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header text-white bg-info mb-3" align="center">
                            DATOS DEL CLIENTE
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <button class="nuevo btn btn-dark ml-2" type="submit" id="nuevo_admision">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-file fa-lg"></i> Nuevo
                                    </button>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="cliente_admision">Cliente</label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="cliente_admision" name="cliente_admision"
                                            data-live-search="true" title="Cliente" data-size="10" data-width="100%">
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="name">Nombre Completo <span class="priority">*<span /></label>
                                    <input type="text" required id="name" name="name" placeholder="Nombre Completo"
                                        class="form-control" />
                                </div>
                                <div class="col-md-4 mb-3" style="display: none;">
                                    <label for="lastname">Apellidos <span class="priority">*<span /></label>
                                    <input type="text" id="lastname" name="lastname" placeholder="Apellido"
                                        class="form-control" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="rtn">Identidad o RTN <span class="priority">*<span /></label>
                                    <input type="number" required id="rtn" name="rtn" class="form-control"
                                        placeholder="Identidad o RTN" maxlength="14"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                        value="0" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3" style="display: none;">
                                    <label for="fecha_nac">Fecha de Nacimiento</label>
                                    <input type="date" required id="fecha_nac" name="fecha_nac"
                                        value="<?php echo date ("Y-m-d");?>" class="form-control" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="edad">Edad</label>
                                    <input type="number" id="edad" name="edad" class="form-control" placeholder="Edad"
                                        maxlength="8" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="telefono">Teléfono 1</label>
                                    <input type="number" id="telefono1" name="telefono1" class="form-control"
                                        placeholder="Teléfono" maxlength="8"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="telefono">Teléfono 2</label>
                                    <input type="number" id="telefono2" name="telefono2" class="form-control"
                                        placeholder="Teléfono" maxlength="8"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="genero">Genero <span class="priority">*<span /></label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="genero" name="genero" required
                                            data-live-search="true" title="Genero" data-width="100%">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <label for="direccion">Dirección</label>
                                    <div class="input-group mb-3">
                                        <textarea id="direccion" name="direccion" placeholder="Dirección "
                                            class="form-control" maxlength="100" rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-address-card fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <label for="correo">Correo</label>
                                    <input type="email" name="correo" id="correo" placeholder="alguien@algo.com"
                                        class="form-control" data-toggle="tooltip" data-placement="top"
                                        title="Este correo será utilizado para enviar las citas creadas y las reprogramaciones, como las notificaciones de las citas pendientes de los usuarios."
                                        maxlength="100" /><label id="validate"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header text-white bg-info mb-3" align="center">
                            DATOS DE LA MUESTRA
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <button class="nuevo btn btn-dark ml-2" type="submit" id="nuevo_admision_muestra">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-file fa-lg"></i> Nuevo
                                    </button>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-4 mb-3">
                                    <label for="empresa">Empresa</label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="empresa" name="empresa" data-live-search="true"
                                            title="Empresa" data-size="10">
                                        </select>
                                        <div class="input-group-append" id="buscar_profesion_pacientes">
                                            <a data-toggle="modal" href="#" class="btn btn-outline-success"
                                                id="add_empresa" data-placement="top" title="Registrar Empresa">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-building fa-lg"></i> Registrar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="empresa">Hospital/Clínica</label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="hospital" name="hospital"
                                            data-live-search="true" title="Hospital/Clínica" data-size="10"
                                            data-width="100%">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3" style="display:none;">
                                    <label for="referencia">Referencia</label>
                                    <input type="text" id="referencia" name="referencia" class="form-control"
                                        placeholder="Referencia" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tipo_muestra">Tipo Muestra <span class="priority">*<span /></label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="tipo_muestra" name="tipo_muestra" required
                                            data-live-search="true" title="Tipo Muestra" data-size="10"
                                            data-width="100%">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="remitente">Producto <span class="priority">*<span /></label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="producto" name="producto" required
                                            data-live-search="true" title="Producto" data-size="10" data-width="100%">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="remitente">Remitente <span class="priority">*<span /></label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="remitente" name="remitente" required
                                            data-live-search="true" title="Remitente" data-size="10" data-width="100%">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" style="display:none;">
                                    <label for="categoria">Categoría</label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="categoria" name="categoria"
                                            data-live-search="true" title="Categoría" data-size="10">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="sitio_muestra">Sitio Preciso de la Muestra</label>
                                    <div class="input-group mb-3">
                                        <textarea id="sitio_muestra" name="sitio_muestra"
                                            placeholder="Sitio Preciso de la Muestra" class="form-control"
                                            maxlength="254" rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-file-medical fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="diagnostico_clinico">Diagnostico Clínico</label>
                                    <div class="input-group mb-3">
                                        <textarea id="diagnostico_clinico" name="diagnostico_clinico"
                                            placeholder="Diagnostico Clínico" class="form-control" maxlength="254"
                                            rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-file-medical fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <label for="material_enviado">Material Enviado</label>
                                    <div class="input-group mb-3">
                                        <textarea id="material_enviado" name="material_enviado"
                                            placeholder="Material Enviado" class="form-control" maxlength="254"
                                            rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-file-medical fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="datos_clinicos">Datos Relevantes/Observacion</label>
                                    <div class="input-group mb-3">
                                        <textarea id="datos_clinicos" name="datos_clinicos"
                                            placeholder="Datos Relevantes/Observacion" class="form-control"
                                            maxlength="254" rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-file-medical fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    <label for="mostrar_datos_clinicos" class="form-check-label mr-1">Mostrar Datos
                                        Clínicos</label>
                                    <label class="switch">
                                        <input type="checkbox" id="mostrar_datos_clinicos" name="mostrar_datos_clinicos"
                                            value="1">
                                        <div class="slider round"></div>
                                    </label>
                                    <span class="question mb-2" id="label_empresa_activo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="RespuestaAjax"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="guardar btn btn-primary ml-2" type="submit" id="reg_admision" form="formulario_admision">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_admision_clientes_editar">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro Clientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="FormularioAjax form-horizontal" id="formulario_admision_clientes_editar" action=""
                    method="POST" data-form="" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header text-white bg-info mb-3" align="center">
                            DATOS DEL CLIENTE
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <input type="hidden" required id="pacientes_id" name="pacientes_id"
                                    class="form-control" />
                                <div class="col-md-12 mb-3">
                                    <label for="name">Nombre Completo <span class="priority">*<span /></label>
                                    <input type="text" required id="name" name="name" placeholder="Nombre Completo"
                                        class="form-control" />
                                </div>
                                <div class="col-md-6 mb-3" style="display: none">
                                    <label for="lastname">Apellidos <span class="priority">*<span /></label>
                                    <input type="text" id="lastname" name="lastname" placeholder="Apellido"
                                        class="form-control" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="rtn">Identidad o RTN <span class="priority">*<span /></label>
                                    <input type="number" required id="rtn" name="rtn" class="form-control"
                                        placeholder="Identidad o RTN" maxlength="14"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                        value="0" />
                                </div>
                                <div class="col-md-3 mb-3" style="display: none;">
                                    <label for="fecha_nac">Fecha de Nacimiento</label>
                                    <input type="date" required id="fecha_nac" name="fecha_nac"
                                        value="<?php echo date ("Y-m-d");?>" class="form-control" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="edad">Edad</label>
                                    <input type="number" id="edad" name="edad" class="form-control" placeholder="Edad"
                                        maxlength="8" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="telefono">Teléfono 1</label>
                                    <input type="number" id="telefono1" name="telefono1" class="form-control"
                                        placeholder="Teléfono" maxlength="8"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="telefono">Teléfono 1</label>
                                    <input type="number" id="telefono2" name="telefono2" class="form-control"
                                        placeholder="Teléfono" maxlength="8"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="genero">Genero <span class="priority">*<span /></label>
                                    <div class="input-group mb-3">
                                        <select class="selectpicker" id="genero" name="genero" required
                                            data-live-search="true" title="Genero" data-width="100%">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <label for="direccion">Dirección</label>
                                    <div class="input-group mb-3">
                                        <textarea id="direccion" name="direccion" placeholder="Dirección "
                                            class="form-control" maxlength="100" rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-address-card fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <label for="correo">Correo</label>
                                    <input type="email" name="correo" id="correo" placeholder="alguien@algo.com"
                                        class="form-control" data-toggle="tooltip" data-placement="top"
                                        title="Este correo será utilizado para enviar las citas creadas y las reprogramaciones, como las notificaciones de las citas pendientes de los usuarios."
                                        maxlength="100" /><label id="validate"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="RespuestaAjax"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="editar btn btn-warning ml-2" type="submit" id="edi_admision"
                    form="formulario_admision_clientes_editar">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL ADMISION CLIENTES-->

<!--INICIO ADMISION EMPRESAS-->
<div class="modal fade" id="modal_admision_empesas">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registro Empresas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="FormularioAjax form-horizontal" id="formulario_admision_empresas" action="" method="POST"
                    data-form="" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header text-white bg-info mb-3" align="center">
                            DATOS DE LA EMPRESA
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <button class="nuevo btn btn-dark ml-2" type="submit" id="nuevo_admision_empresa">
                                        <div class="sb-nav-link-icon"></div><i class="fas fa-file fa-lg"></i> Nuevo
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" required id="pacientes_id" name="pacientes_id" placeholder="Empresa"
                                class="form-control" />
                            <div class="form-row">
                                <div class="col-md-8 mb-3">
                                    <label for="name">Empresa <span class="priority">*<span /></label>
                                    <input type="text" required id="empresa" name="empresa" placeholder="Empresa"
                                        class="form-control" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="rtn">RTN <span class="priority">*<span /></label>
                                    <input type="number" required id="rtn" name="rtn" class="form-control"
                                        placeholder="Identidad o RTN" maxlength="14"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                        value="0" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-12 mb-3">
                                    <label for="direccion">Dirección</label>
                                    <div class="input-group mb-3">
                                        <textarea id="direccion" name="direccion" placeholder="Dirección "
                                            class="form-control" maxlength="100" rows="4"></textarea>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <div class="sb-nav-link-icon"></div><i
                                                    class="fas fa-address-card fa-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-3 mb-3">
                                    <label for="telefono">Teléfono</label>
                                    <input type="number" id="telefono1" name="telefono1" class="form-control"
                                        placeholder="Teléfono" maxlength="8"
                                        oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                                </div>
                                <div class="col-md-9 mb-3">
                                    <label for="correo">Correo</label>
                                    <input type="email" name="correo" id="correo" placeholder="alguien@algo.com"
                                        class="form-control" data-toggle="tooltip" data-placement="top"
                                        title="Este correo será utilizado para enviar las citas creadas y las reprogramaciones, como las notificaciones de las citas pendientes de los usuarios."
                                        maxlength="100" /><label id="validate"></label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="RespuestaAjax"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="guardar btn btn-primary ml-2" type="submit" id="reg_admisionemp"
                    form="formulario_admision_empresas">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="editar btn btn-warning ml-2" type="submit" id="edi_admisionemp"
                    form="formulario_admision_empresas">
                    <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL ADMISION EMPRESAS-->

<!-- modal de abonos cxc -->
<div class="modal fade" id="ver_abono_cxc">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Abonos Clientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_ver_abono_cxc">
                    <div class="form-group">
                        <input type="hidden" name="abono_facturas_id" id="abono_facturas_id" class="form-control">
                        <input type="hidden" name="abono_facturas_id" id="abono_tipo" class="form-control">
                        <div class="col-md-12">
                            <div class="overflow-auto">
                                <table id="table-modal-abonos" class="table table-striped table-condensed table-hover"
                                    style="width:100%">
                                    <h5 id="ver_abono_cxcTitle"></h5>
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo Pago</th>
                                            <th>Descripcion</th>
                                            <th>Abono</th>
                                            <th>Usuario que recibe</th>
                                        </tr>
                                    </thead>
                                    <tfoot class="bg-info text-white font-weight-bold">
                                        <tr>
                                            <td colspan='2' class="text-left">Total</td>
                                            <td colspan="1"></td>
                                            <td colspan='1' id='total-footer-modal-cxc' class="text-right"></td>
                                            <td colspan="1"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
<!-- FIN modal de abonos cxc -->