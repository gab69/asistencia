
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
   <link rel="stylesheet" href="public/assets/css/styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-user-shield"></i> <span>Admin</span></h3>
        </div>
        <div class="sidebar-menu">
            <a href="?section=dashboard&report_type=daily&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $reportType === 'daily' ? 'active' : '' ?>">
                <i class="fas fa-calendar-day"></i> <span>Diario</span>
            </a>
            <a href="?section=dashboard&report_type=no_asistencia&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $reportType === 'no_asistencia' ? 'active' : '' ?>">
                <i class="fas fa-user-times"></i> <span>Sin Asistencia</span>
            </a>
            <a href="?section=dashboard&report_type=tardiness&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $reportType === 'tardiness' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> <span>Tardanzas</span>
            </a>
            <a href="?section=dashboard&report_type=permission&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $reportType === 'permission' ? 'active' : '' ?>">
                <i class="fas fa-file-signature"></i> <span>Permisos</span>
            </a>
            
            <?php if ($isSuperAdmin || $isSupervisor): ?>
                <a href="?section=dashboard&report_type=trabajadores" class="menu-item <?= $reportType === 'trabajadores' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> <span>Trabajadores</span>
                </a>
            <?php endif; ?>
            
            <?php if ($isSuperAdmin): ?>
                <a href="?section=dashboard&report_type=administradores" class="menu-item <?= $reportType === 'administradores' ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i> <span>Administradores</span>
                </a>
            <?php endif; ?>
            
            <?php if ($isSuperAdmin || $isSupervisor): ?>
                <a href="?section=dashboard&report_type=history" class="menu-item <?= $reportType === 'history' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i> <span>Historial</span>
                </a>
            <?php endif; ?>
            
            <?php if ($isSuperAdmin): ?>
                <a href="?section=dashboard&report_type=config" class="menu-item <?= $reportType === 'config' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> <span>Configuración</span>
                </a>
            <?php endif; ?>
            
            <?php if ($isSupervisor): ?>
                <a href="?section=dashboard&report_type=perfil" class="menu-item <?= $reportType === 'perfil' ? 'active' : '' ?>">
                    <i class="fas fa-user-edit"></i> <span>Mi Perfil</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>
                <i class="fas fa-clipboard-list"></i> <?= $reportTitle ?>
            </h1>



            <!-- Action Buttons según el tipo de reporte -->
                <div>
                     <?php if (($isSuperAdmin || $isSupervisor) && $reportType === 'daily'): ?>
                <button type="button" class="btn btn-success" onclick="openManualAttendanceModal()">
                    <i class="fas fa-plus"></i> Asistencia
                </button>
            <?php endif; ?>
            
            <?php if (($isSuperAdmin || $isSupervisor) && $reportType === 'permission'): ?>
                <button type="button" class="btn btn-info" onclick="openManualPermissionModal()">
                    <i class="fas fa-plus"></i> Permiso
                </button>
            <?php endif; ?>
            
            <?php if (($isSuperAdmin || $isSupervisor) && $reportType === 'trabajadores'): ?>
                <button type="button" class="btn btn-success" onclick="openAddEmpleadoModal()">
                    <i class="fas fa-plus"></i> Agregar Trabajador
                </button>
            <?php endif; ?>
            
            <?php if ($isSuperAdmin && $reportType === 'administradores'): ?>
                <button type="button" class="btn btn-success" onclick="openAddAdminModal()">
                    <i class="fas fa-plus"></i> Agregar Administrador
                </button>
            <?php endif; ?>
            
            <!-- BOTÓN DE EXPORTAR EXCEL AGREGADO -->
            <?php if ($reportType !== 'config' && $reportType !== 'perfil'): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export_excel' => 1])) ?>" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            <?php endif; ?>
            
            <!-- Botón para limpiar filtros - OCULTO EN CONFIGURACIÓN -->
            <?php if ($reportType !== 'config' && $reportType !== 'perfil'): ?>
                <a href="?section=dashboard&report_type=<?= $reportType ?>" class="btn btn-light">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            <?php endif; ?>
                </div>
           
        
            <div class="user-info">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['admin_username']) ?> 
                (<?= $_SESSION['admin_role'] === 'admin' ? 'Administrador' : 'Supervisor' ?>)</span>
                <a href="index.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="flash-message" class="flash-message flash-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success_message'] ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div id="flash-message" class="flash-message flash-error">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        
        
        <!-- Filtros para todos los reportes excepto configuración y perfil -->
        <?php if ($reportType !== 'config' && $reportType !== 'perfil'): ?>
        <div class="filters-container">
            <form method="get" id="filtersForm">
                <input type="hidden" name="section" value="dashboard">
                <input type="hidden" name="report_type" value="<?= $reportType ?>">
                
                <div class="filters-grid">
                    <?php if (in_array($reportType, ['daily', 'tardiness', 'permission', 'no_asistencia'])): ?>
                        <div class="form-group">
                            <label for="fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= $fechaInicio ?>" onchange="submitFilters()">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= $fechaFin ?>" onchange="submitFilters()">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($reportType === 'history'): ?>
                        <div class="form-group">
                            <label for="historial_fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" id="historial_fecha_inicio" name="historial_fecha_inicio" 
                                   class="form-control" value="<?= $historialFechaInicio ?>" onchange="submitFilters()">
                        </div>
                        
                        <div class="form-group">
                            <label for="historial_fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin</label>
                            <input type="date" id="historial_fecha_fin" name="historial_fecha_fin" 
                                   class="form-control" value="<?= $historialFechaFin ?>" onchange="submitFilters()">
                        </div>
                        
                        <div class="form-group">
                            <label for="historial_tabla"><i class="fas fa-table"></i> Tabla Afectada</label>
                            <select id="historial_tabla" name="historial_tabla" class="form-control" onchange="submitFilters()">
                                <option value="">Todas las tablas</option>
                                <option value="empleados" <?= $historialTabla === 'empleados' ? 'selected' : '' ?>>Empleados</option>
                                <option value="usuarios_admin" <?= $historialTabla === 'usuarios_admin' ? 'selected' : '' ?>>Administradores</option>
                                <option value="permisos" <?= $historialTabla === 'permisos' ? 'selected' : '' ?>>Permisos</option>
                                <option value="registros_asistencia" <?= $historialTabla === 'registros_asistencia' ? 'selected' : '' ?>>Asistencias</option>
                                <option value="configuracion_sistema" <?= $historialTabla === 'configuracion_sistema' ? 'selected' : '' ?>>Configuración</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="historial_accion"><i class="fas fa-bolt"></i> Acción</label>
                            <select id="historial_accion" name="historial_accion" class="form-control" onchange="submitFilters()">
                                <option value="">Todas las acciones</option>
                                <option value="INSERT" <?= $historialAccion === 'INSERT' ? 'selected' : '' ?>>INSERT</option>
                                <option value="UPDATE" <?= $historialAccion === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                                <option value="DELETE" <?= $historialAccion === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="historial_creado_por"><i class="fas fa-user"></i> Modificado Por</label>
                            <input type="text" id="historial_creado_por" name="historial_creado_por" 
                                   class="form-control" value="<?= htmlspecialchars($historialCreadoPor) ?>" 
                                   placeholder="Buscar por usuario..." onchange="submitFilters()">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filtros comunes para Área, Cargo y Búsqueda -->
                    <?php if (in_array($reportType, ['daily', 'tardiness', 'permission', 'no_asistencia', 'trabajadores'])): ?>
                        <div class="form-group">
                            <label for="filter_area"><i class="fas fa-building"></i> Área</label>
                            <select id="filter_area" name="filter_area" class="form-control" onchange="submitFilters()">
                                <option value="">Todas las áreas</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>" <?= $filterArea === $area ? 'selected' : '' ?>><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_cargo"><i class="fas fa-briefcase"></i> Cargo</label>
                            <select id="filter_cargo" name="filter_cargo" class="form-control" onchange="submitFilters()">
                                <option value="">Todos los cargos</option>
                                <!-- Los cargos se cargarán dinámicamente según el área seleccionada -->
                            </select>
                        </div>
                        
                        <!-- NUEVO: Filtro de Tipo de Personal -->
                        <div class="form-group">
                            <label for="filter_tipo_personal"><i class="fas fa-user-tag"></i> Tipo de Personal</label>
                            <select id="filter_tipo_personal" name="filter_tipo_personal" class="form-control" onchange="submitFilters()">
                                <option value="">Todos los tipos</option>
                                <option value="Administrativo" <?= $filterTipoPersonal === 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
                                <option value="Docente" <?= $filterTipoPersonal === 'Docente' ? 'selected' : '' ?>>Docente</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filtro especial para Turno en Sin Asistencia -->
                    <?php if ($reportType === 'no_asistencia'): ?>
                        <div class="form-group">
                            <label for="filter_turno"><i class="fas fa-clock"></i> Turno</label>
                            <select id="filter_turno" name="filter_turno" class="form-control" onchange="submitFilters()">
                                <option value="">Todos los turnos</option>
                                <option value="MAÑANA" <?= $filterTurno === 'MAÑANA' ? 'selected' : '' ?>>Mañana</option>
                                <option value="TARDE" <?= $filterTurno === 'TARDE' ? 'selected' : '' ?>>Tarde</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($reportType, ['trabajadores', 'administradores'])): ?>
                        <div class="form-group">
                            <label for="filter_estado"><i class="fas fa-circle"></i> Estado</label>
                            <select id="filter_estado" name="filter_estado" class="form-control" onchange="submitFilters()">
                                <option value="">Todos los estados</option>
                                <option value="activo" <?= $filterEstado === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $filterEstado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($reportType === 'administradores'): ?>
                        <div class="form-group">
                            <label for="filter_rol"><i class="fas fa-user-tag"></i> Rol</label>
                            <select id="filter_rol" name="filter_rol" class="form-control" onchange="submitFilters()">
                                <option value="">Todos los roles</option>
                                <option value="admin" <?= $filterRol === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="supervisor" <?= $filterRol === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="search_term"><i class="fas fa-search"></i> Buscar</label>
                        <input type="text" id="search_term" name="search_term" class="form-control" placeholder="Buscar en todos los datos..." value="<?= htmlspecialchars($searchTerm) ?>" onchange="submitFilters()">
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Contenido específico para cada tipo de reporte -->
        <?php if ($reportType === 'config'): ?>
            <!-- Sección de Configuración -->
            <div class="card">
                <div class="card-title"><i class="fas fa-cog"></i> Configuración del Sistema</div>
                <form method="post" action="">
                    <div style="max-width: 500px; margin: 0 auto;">
                        <div class="form-group">
                            <label for="minutos_tolerancia">Minutos de Tolerancia para Tardanzas</label>
                            <input type="number" id="minutos_tolerancia" name="minutos_tolerancia" 
                                   class="form-control" value="<?= $minutosTolerancia ?>" 
                                   min="0" max="60" required>
                            <small class="text-muted">Establece los minutos de tolerancia antes de considerar una tardanza (0-60 minutos)</small>
                        </div>
                        <div style="text-align: center; margin-top: 2rem;">
                            <button type="submit" name="actualizar_configuracion" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php elseif ($reportType === 'perfil' && $isSupervisor): ?>
            <!-- Sección de Perfil para Supervisor -->
            <div class="card">
                <div class="card-title"><i class="fas fa-user-edit"></i> Configurar Mi Perfil</div>
                <form method="post" action="">
                    <div style="max-width: 500px; margin: 0 auto;">
                        <div class="form-group">
                            <label for="usuario">Nombre de Usuario</label>
                            <input type="text" id="usuario" name="usuario" 
                                   class="form-control" value="<?= htmlspecialchars($_SESSION['admin_username']) ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Nueva Contraseña (dejar vacío para mantener la actual)</label>
                            <input type="password" id="password" name="password" 
                                   class="form-control" placeholder="Ingrese nueva contraseña">
                            <small class="text-muted">Mínimo 4 caracteres</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_password">Confirmar Nueva Contraseña</label>
                            <input type="password" id="confirmar_password" name="confirmar_password" 
                                   class="form-control" placeholder="Confirme la nueva contraseña">
                        </div>
                        
                        <div style="text-align: center; margin-top: 2rem;">
                            <button type="submit" name="actualizar_perfil_supervisor" class="btn btn-success">
                                <i class="fas fa-save"></i> Actualizar Perfil
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Vista de tabla para todos los demás reportes -->
            <div class="card report-card">
                <div class="report-header">
                    <h2 class="card-title" style="color: white; border: none; margin: 0;"><i class="fas fa-table"></i> Resultados (<span id="total-items"><?= $totalItems ?></span> registros)</h2>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($paginatedData)): ?>
                        <table class="report-table" id="report-table">
                            <thead>
                                <tr>
                                    <?php if (!in_array($reportType, ['areas', 'no_asistencia'])): ?>
                                        <th style="width: 50px">#</th>
                                    <?php endif; ?>
                                    <?php switch ($reportType): 
                                        case 'tardiness': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS Y NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>FECHA</th>
                                            <th>HORA ENTRADA MAÑANA</th>
                                            <th>HORA MARCADA MAÑANA</th>
                                            <th>TARDANZA MAÑANA</th>
                                            <th>HORA ENTRADA TARDE</th>
                                            <th>HORA MARCADA TARDE</th>
                                            <th>TARDANZA TARDE</th>
                                            <?php break; ?>
                                            
                                        <?php case 'permission': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS Y NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>FECHA PERMISO</th>
                                            <th>TIPO PERMISO</th>
                                            <th>MOTIVO</th>
                                            <th>HORA SALIDA</th>
                                            <th>HORA RETORNO</th>
                                            <th>HORA REGISTRO</th>
                                            <th>REGISTRADO POR</th>
                                            <th>ACCIONES</th>
                                            <?php break; ?>
                                            
                                        <?php case 'no_asistencia': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS Y NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>TURNO</th>
                                            <th>TOTAL FALTAS</th>
                                            <th>FECHAS FALTAS</th>
                                            <?php break; ?>
                                            
                                        <?php case 'trabajadores': ?>
                                            <th>FOTO</th>
                                            <th>DNI</th>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>TIPO PERSONAL</th>
                                            <th>ESTADO</th>
                                            <th>INICIO CONTRATO</th>
                                            <th>FIN CONTRATO</th>
                                            <th>CREADO POR</th>
                                            <th>ACCIONES</th>
                                            <?php break; ?>
                                            
                                        <?php case 'administradores': ?>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>USUARIO</th>
                                            <th>ROL</th>
                                            <th>ESTADO</th>
                                            <th>FECHA CREACIÓN</th>
                                            <th>CREADO POR</th>
                                            <th>ACCIONES</th>
                                            <?php break; ?>
                                            
                                        <?php case 'history': ?>
                                            <th>TABLA AFECTADA</th>
                                            <th>USUARIO AFECTADO</th>
                                            <th>ACCIÓN</th>
                                            <th>CAMPO MODIFICADO</th>
                                            <th>VALOR ANTERIOR</th>
                                            <th>VALOR NUEVO</th>
                                            <th>MOTIVO</th>
                                            <th>MODIFICADO POR</th>
                                            <th>FECHA MODIFICACIÓN</th>
                                            <?php break; ?>
                                            
                                        <?php default: // daily ?>
                                            <th>FECHA</th>
                                            <th>DNI</th>
                                            <th>APELLIDOS Y NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>ENTRADA MAÑANA</th>
                                            <th>SALIDA MAÑANA</th>
                                            <th>ENTRADA TARDE</th>
                                            <th>SALIDA TARDE</th>
                                            <th>REGISTROS</th>
                                            <th>REGISTRADO POR</th>
                                    <?php endswitch; ?>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <?php 
                                // CORRECCIÓN: Iniciar contador correctamente para cada página
                                $contador = ($currentPage - 1) * $itemsPerPage + 1;
                                $showCounter = !in_array($reportType, ['areas', 'no_asistencia']);
                                ?>
                                
                                <?php foreach ($paginatedData as $row): ?>
                                    <?php if (isset($row['es_total']) && $row['es_total']): ?>
                                        <!-- Fila de totales para SIN ASISTENCIA -->
                                        <?php if ($reportType === 'no_asistencia'): ?>
                                            <tr class="total-general-row">
                                                <td colspan="<?= $showCounter ? 8 : 7 ?>" style="text-align: right; font-weight: bold;">TOTALES:</td>
                                                <td style="font-weight: bold;">MAÑANA: <?= $row['total_faltas_manana'] ?></td>
                                                <td style="font-weight: bold;">TARDE: <?= $row['total_faltas_tarde'] ?></td>
                                                <td style="font-weight: bold;">TOTAL: <?= $row['total_general'] ?></td>
                                            </tr>
                                        <?php elseif ($reportType === 'tardiness'): ?>
                                            <!-- Fila de totales para TARDANZAS -->
                                            <tr class="total-general-row">
                                                <td colspan="<?= $showCounter ? 7 : 6 ?>" style="text-align: right; font-weight: bold;">TOTALES:</td>
                                                <td style="font-weight: bold;"><?= $row['total_tardanza_manana'] ?></td>
                                                <td colspan="2" style="text-align: right; font-weight: bold;"></td>
                                                <td style="font-weight: bold;"><?= $row['total_tardanza_tarde'] ?></td>
                                                <td style="font-weight: bold;">TOTAL: <?= $row['total_tardanza'] ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    
                                    <tr <?= (isset($row['es_total_area']) ? 'class="total-area-row"' : ($reportType === 'areas' && $row['area'] === 'TOTAL GENERAL' ? 'class="total-general-row"' : '')) ?>>
                                        
                                        <?php if ($showCounter): ?>
                                            <td style="text-align: center;"><?= $contador++ ?></td>
                                        <?php endif; ?>
                                        
                                        <?php switch ($reportType): 
                                            case 'tardiness': ?>
                                                <?php 
                                                // Combinamos tardanzas de mañana y tarde
                                                $tardanzasCombinadas = [];
                                                
                                                // Procesar tardanzas de mañana
                                                foreach ($row['tardanzas_manana'] as $tardanza) {
                                                    $tardanzasCombinadas[$tardanza['fecha']] = [
                                                        'manana' => $tardanza,
                                                        'tarde' => null
                                                    ];
                                                }
                                                
                                                // Procesar tardanzas de tarde
                                                foreach ($row['tardanzas_tarde'] as $tardanza) {
                                                    if (isset($tardanzasCombinadas[$tardanza['fecha']])) {
                                                        $tardanzasCombinadas[$tardanza['fecha']]['tarde'] = $tardanza;
                                                    } else {
                                                        $tardanzasCombinadas[$tardanza['fecha']] = [
                                                            'manana' => null,
                                                            'tarde' => $tardanza
                                                        ];
                                                    }
                                                }
                                                
                                                // Mostrar todas las tardanzas combinadas
                                                $firstRow = true;
                                                foreach ($tardanzasCombinadas as $fecha => $tardanzas): ?>
                                                    <?php if (!$firstRow): ?>
                                                        <tr>
                                                        <?php if ($showCounter): ?>
                                                            <td style="text-align: center;"></td>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center nowrap"><?= $fecha ?></td>
                                                    
                                                    <!-- Datos mañana -->
                                                    <td class="text-center"><?= $tardanzas['manana'] ? $tardanzas['manana']['hora_entrada'] : '-' ?></td>
                                                    <td class="text-center"><?= $tardanzas['manana'] ? $tardanzas['manana']['hora_marcada'] : '-' ?></td>
                                                    <td class="tardanza-cell text-center"><?= $tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-' ?></td>
                                                    
                                                    <!-- Datos tarde -->
                                                    <td class="text-center"><?= $tardanzas['tarde'] ? $tardanzas['tarde']['hora_entrada'] : '-' ?></td>
                                                    <td class="text-center"><?= $tardanzas['tarde'] ? $tardanzas['tarde']['hora_marcada'] : '-' ?></td>
                                                    <td class="tardanza-cell text-center"><?= $tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-' ?></td>
                                                    </tr>
                                                    <?php $firstRow = false; ?>
                                                <?php endforeach; ?>
                                                <?php break; ?>
                                                
                                            <?php case 'permission': ?>
                                                <td><?= htmlspecialchars($row['dni']) ?></td>
                                                <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                <td class="text-center nowrap"><?= date('d/m/Y', strtotime($row['fecha_permiso'])) ?></td>
                                                <td class="permiso-cell text-center"><?= htmlspecialchars($row['tipo_permiso']) ?></td>
                                                <td><?= htmlspecialchars($row['motivo']) ?></td>
                                                <td class="text-center"><?= $row['hora_salida'] ?></td>
                                                <td class="text-center"><?= $row['hora_retorno'] ?></td>
                                                <td class="text-center"><?= $row['hora_registro'] ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['registrado_por']) ?></td>
                                                <td>
                                                    <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                        <button type="button" class="btn btn-secondary btn-sm" onclick="openEditPermissionModal(<?= $row['id'] ?>)">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <?php break; ?>
                                                
                                            <?php case 'no_asistencia': ?>
                                                <!-- CORRECCIÓN: Mostrar solo el turno filtrado -->
                                                <?php if (!empty($row['faltas_manana']) && (empty($filtros['turno']) || $filtros['turno'] === 'MAÑANA')): ?>
                                                    <tr>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-warning">MAÑANA</span>
                                                    </td>
                                                    <td class="text-center"><?= $row['total_faltas_manana'] ?></td>
                                                    <td><?= implode(', ', array_slice($row['faltas_manana'], 0, 5)) . (count($row['faltas_manana']) > 5 ? '...' : '') ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($row['faltas_tarde']) && (empty($filtros['turno']) || $filtros['turno'] === 'TARDE')): ?>
                                                    <tr>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">TARDE</span>
                                                    </td>
                                                    <td class="text-center"><?= $row['total_faltas_tarde'] ?></td>
                                                    <td><?= implode(', ', array_slice($row['faltas_tarde'], 0, 5)) . (count($row['faltas_tarde']) > 5 ? '...' : '') ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                <?php break; ?>
                                                
                                            <?php case 'trabajadores': ?>
                                                <td>
                                                    <?php if (!empty($row['foto']) && file_exists(FOTO_DIR . $row['foto'])): ?>
                                                        <img src="<?= FOTO_DIR . $row['foto'] ?>" class="employee-photo" onclick="openImageModal('<?= FOTO_DIR . $row['foto'] ?>')">
                                                    <?php else: ?>
                                                        <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-user" style="color: #666;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['dni']) ?></td>
                                                <td><?= htmlspecialchars($row['apellidos']) ?></td>
                                                <td><?= htmlspecialchars($row['nombres']) ?></td>
                                                <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                <td><?= htmlspecialchars($row['tipo_personal']) ?></td>
                                                <td>
                                                    <span class="badge <?= $row['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= $row['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['inicio_contrato'] ?></td>
                                                <td><?= $row['fin_contrato'] ?></td>
                                                <td><?= htmlspecialchars($row['creado_por_nombre']) ?></td>
                                                <td>
                                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                        <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                            <button type='button' class='btn btn-secondary btn-sm' onclick='openEditEmpleadoModal(<?= $row['id'] ?>)'>
                                                                <i class='fas fa-edit'></i> Editar
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                            <form method="post" style="display: inline;">
                                                                <input type="hidden" name="empleado_id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="nuevo_estado" value="<?= $row['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                                                <button type="submit" name="cambiar_estado_empleado" class="btn btn-<?= $row['estado'] === 'activo' ? 'warning' : 'success' ?> btn-sm">
                                                                    <i class="fas fa-<?= $row['estado'] === 'activo' ? 'pause' : 'play' ?>"></i>
                                                                    <?= $row['estado'] === 'activo' ? ' Desactivar' : ' Activar' ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <?php break; ?>
                                                
                                            <?php case 'administradores': ?>
                                                <td><?= htmlspecialchars($row['apellidos']) ?></td>
                                                <td><?= htmlspecialchars($row['nombres']) ?></td>
                                                <td><?= htmlspecialchars($row['usuario']) ?></td>
                                                <td>
                                                    <span class="badge <?= $row['rol'] === 'admin' ? 'badge-primary' : 'badge-info' ?>">
                                                        <?= $row['rol'] === 'admin' ? 'Administrador' : 'Supervisor' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= $row['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?></td>
                                                <td><?= htmlspecialchars($row['creado_por_nombre']) ?></td>
                                                <td>
                                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                        <?php if ($isSuperAdmin): ?>
                                                            <button type='button' class='btn btn-secondary btn-sm' onclick='openEditAdminModal(<?= $row['id'] ?>)'>
                                                                <i class='fas fa-edit'></i> Editar
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($isSuperAdmin): ?>
                                                            <form method="post" style="display: inline;">
                                                                <input type="hidden" name="admin_id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="nuevo_estado" value="<?= $row['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                                                <button type="submit" name="cambiar_estado_admin" class="btn btn-<?= $row['estado'] === 'activo' ? 'warning' : 'success' ?> btn-sm">
                                                                    <i class="fas fa-<?= $row['estado'] === 'activo' ? 'pause' : 'play' ?>"></i>
                                                                    <?= $row['estado'] === 'activo' ? ' Desactivar' : ' Activar' ?>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <?php break; ?>
                                                
                                            <?php case 'history': ?>
                                                <td>
                                                    <span class="badge badge-info"><?= htmlspecialchars($row['tabla_afectada']) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($row['usuario_afectado']) ?></td>
                                                <td>
                                                    <span class="badge <?= 
                                                        $row['accion'] === 'INSERT' ? 'badge-success' : 
                                                        ($row['accion'] === 'UPDATE' ? 'badge-warning' : 'badge-danger')
                                                    ?>">
                                                        <?= htmlspecialchars($row['accion']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($row['campo_modificado']) ?></td>
                                                <td><?= htmlspecialchars($row['valor_anterior']) ?></td>
                                                <td><?= htmlspecialchars($row['valor_nuevo']) ?></td>
                                                <td><?= htmlspecialchars($row['motivo']) ?></td>
                                                <td><?= htmlspecialchars($row['modificado_por_nombre']) ?></td>
                                                <td><?= date('d/m/Y H:i:s', strtotime($row['fecha_modificacion'])) ?></td>
                                                <?php break; ?>
                                                
                                            <?php default: // daily ?>
                                                <td class="text-center nowrap"><?= $row['fecha'] ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['dni']) ?></td>
                                                <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                
                                                <!-- Mostrar registros clasificados - SOLO UN REGISTRO POR COLUMNA -->
                                                <td class="text-center">
                                                    <?php if (!empty($row['registros_entrada_manana'])): ?>
                                                        <?= min($row['registros_entrada_manana']) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="text-center">
                                                    <?php if (!empty($row['registros_salida_manana'])): ?>
                                                        <?= min($row['registros_salida_manana']) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="text-center">
                                                    <?php if (!empty($row['registros_entrada_tarde'])): ?>
                                                        <?= min($row['registros_entrada_tarde']) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="text-center">
                                                    <?php if (!empty($row['registros_salida_tarde'])): ?>
                                                        <?= min($row['registros_salida_tarde']) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Nueva columna REGISTROS con todos los registros -->
                                                <td class="text-center">
                                                    <?php if (!empty($row['todos_registros'])): ?>
                                                        <div class="registros-vertical">
                                                            <?php foreach ($row['todos_registros'] as $registro): ?>
                                                                <div class="registro-item"><?= $registro ?></div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Nueva columna REGISTRADO POR con todos los registradores -->
                                                <td class="text-center">
                                                    <?php if (!empty($row['todos_registradores'])): ?>
                                                        <div class="registros-vertical">
                                                            <?php foreach ($row['todos_registradores'] as $registrador): ?>
                                                                <div class="registro-item"><?= htmlspecialchars($registrador) ?></div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                        <?php endswitch; ?>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Fila de totales para permisos -->
                                <?php if ($reportType === 'permission' && isset($permissionTotals) && !empty($permissionTotals)): ?>
                                    <tr class="total-general-row">
                                        <td colspan="<?= $showCounter ? 14 : 13 ?>" style="text-align: center; font-weight: bold;">
                                            TOTALES POR TIPO DE PERMISO:
                                            <?php 
                                            $totalGeneral = 0;
                                            foreach ($permissionTotals as $total): 
                                                $totalGeneral += $total['total'];
                                            ?>
                                                <span style="margin: 0 10px;"><?= htmlspecialchars($total['tipo_permiso']) ?>: <?= $total['total'] ?></span>
                                            <?php endforeach; ?>
                                            <span style="margin: 0 10px;">TOTAL GENERAL: <?= $totalGeneral ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">Primera</a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">Anterior</a>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">Siguiente</a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Última</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                            <p>No se encontraron datos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para agregar empleado - MEJORADO: Diseño horizontal -->
    <div id="addEmpleadoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-user-plus"></i> Agregar Trabajador</h3>
                <span class="close" onclick="closeModal('addEmpleadoModal')">&times;</span>
            </div>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="empleado-form-container">
                        <div class="form-group">
                            <label for="dni">DNI</label>
                            <input type="text" id="dni" name="dni" required class="form-control" placeholder="Ingrese DNI">
                        </div>
                        
                        <div class="form-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" required class="form-control" placeholder="Ingrese nombres">
                        </div>
                        
                        <div class="form-group">
                            <label for="apellidos">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" required class="form-control" placeholder="Ingrese apellidos">
                        </div>
                        
                        <div class="form-group">
                            <label for="area">Área</label>
                            <select id="area" name="area" required class="form-control" onchange="updateEmpleadoCargos()">
                                <option value="">Seleccione área</option>
                                <?php foreach ($AREAS_PREDEFINIDAS as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cargo">Cargo</label>
                            <select id="cargo" name="cargo" required class="form-control">
                                <option value="">Primero seleccione un área</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="inicio_contrato">Inicio de Contrato</label>
                            <input type="date" id="inicio_contrato" name="inicio_contrato" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="fin_contrato">Fin de Contrato</label>
                            <input type="date" id="fin_contrato" name="fin_contrato" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_personal">Tipo de Personal</label>
                            <select id="tipo_personal" name="tipo_personal" required class="form-control" onchange="toggleHorarioFields()">
                                <option value="">Seleccione tipo</option>
                                <option value="Docente">Docente</option>
                                <option value="Administrativo">Administrativo</option>
                            </select>
                        </div>
                        
                        <!-- Campos de horario (solo para administrativos) -->
                        <div id="horarioFields" class="horario-fields hidden">
                            <div class="form-group">
                                <label for="entrada_manana">Entrada Mañana</label>
                                <input type="time" id="entrada_manana" name="entrada_manana" class="form-control" value="08:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="salida_manana">Salida Mañana</label>
                                <input type="time" id="salida_manana" name="salida_manana" class="form-control" value="13:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="entrada_tarde">Entrada Tarde</label>
                                <input type="time" id="entrada_tarde" name="entrada_tarde" class="form-control" value="16:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="salida_tarde">Salida Tarde</label>
                                <input type="time" id="salida_tarde" name="salida_tarde" class="form-control" value="19:00">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="foto">Foto</label>
                            <div class="photo-preview-container">
                                <img id="photoPreview" src="" class="photo-preview" style="display: none;">
                                <div class="photo-upload">
                                    <input type="file" id="foto" name="foto" class="form-control" accept="image/*" onchange="previewPhoto(this)">
                                    <small class="text-muted">Formatos aceptados: JPG, PNG, GIF (Máx. 2MB). Si no sube foto, se usará default.png</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addEmpleadoModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="agregar_empleado">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar empleado - MEJORADO: Diseño horizontal -->
    <div id="editEmpleadoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Editar Trabajador</h3>
                <span class="close" onclick="closeModal('editEmpleadoModal')">&times;</span>
            </div>
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="empleado_id" id="edit_empleado_id">
                
                <div class="modal-body">
                    <div class="empleado-form-container">
                        <div class="form-group">
                            <label for="edit_dni">DNI</label>
                            <input type="text" id="edit_dni" name="dni" required class="form-control" placeholder="Ingrese DNI">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_nombres">Nombres</label>
                            <input type="text" id="edit_nombres" name="nombres" required class="form-control" placeholder="Ingrese nombres">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_apellidos">Apellidos</label>
                            <input type="text" id="edit_apellidos" name="apellidos" required class="form-control" placeholder="Ingrese apellidos">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_area">Área</label>
                            <select id="edit_area" name="area" required class="form-control" onchange="updateEditEmpleadoCargos()">
                                <option value="">Seleccione área</option>
                                <?php foreach ($AREAS_PREDEFINIDAS as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_cargo">Cargo</label>
                            <select id="edit_cargo" name="cargo" required class="form-control">
                                <option value="">Primero seleccione un área</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_inicio_contrato">Inicio de Contrato</label>
                            <input type="date" id="edit_inicio_contrato" name="inicio_contrato" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_fin_contrato">Fin de Contrato</label>
                            <input type="date" id="edit_fin_contrato" name="fin_contrato" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_tipo_personal">Tipo de Personal</label>
                            <select id="edit_tipo_personal" name="tipo_personal" required class="form-control" onchange="toggleEditHorarioFields()">
                                <option value="">Seleccione tipo</option>
                                <option value="Docente">Docente</option>
                                <option value="Administrativo">Administrativo</option>
                            </select>
                        </div>
                        
                        <!-- Campos de horario (solo para administrativos) -->
                        <div id="editHorarioFields" class="horario-fields hidden">
                            <div class="form-group">
                                <label for="edit_entrada_manana">Entrada Mañana</label>
                                <input type="time" id="edit_entrada_manana" name="entrada_manana" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_salida_manana">Salida Mañana</label>
                                <input type="time" id="edit_salida_manana" name="salida_manana" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_entrada_tarde">Entrada Tarde</label>
                                <input type="time" id="edit_entrada_tarde" name="entrada_tarde" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_salida_tarde">Salida Tarde</label>
                                <input type="time" id="edit_salida_tarde" name="salida_tarde" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_foto">Foto</label>
                            <div class="photo-preview-container">
                                <img id="editPhotoPreview" src="" class="photo-preview">
                                <div class="photo-upload">
                                    <input type="file" id="edit_foto" name="foto" class="form-control" accept="image/*" onchange="previewEditPhoto(this)">
                                    <small class="text-muted">Dejar vacío para mantener la foto actual. Formatos: JPG, PNG, GIF (Máx. 2MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editEmpleadoModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="editar_empleado">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para agregar administrador -->
    <div id="addAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-user-plus"></i> Agregar Administrador</h3>
                <span class="close" onclick="closeModal('addAdminModal')">&times;</span>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin_empleado_id">Empleado</label>
                        <select id="admin_empleado_id" name="empleado_id" class="employee-select form-control" required style="width: 100%">
                            <option value="">Buscar empleado por nombre, apellido o DNI...</option>
                            <?php foreach ($employeesList as $emp): ?>
                                <?php if (isset($emp['id']) && isset($emp['nombre_completo']) && isset($emp['dni'])): ?>
                                    <option value="<?= htmlspecialchars($emp['id']) ?>">
                                        <?= htmlspecialchars($emp['nombre_completo']) ?> (<?= htmlspecialchars($emp['dni']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_usuario">Usuario</label>
                        <input type="text" id="admin_usuario" name="usuario" required class="form-control" placeholder="Ingrese nombre de usuario">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Contraseña (mínimo 4 caracteres)</label>
                        <input type="password" id="admin_password" name="password" required class="form-control" placeholder="Ingrese contraseña" minlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_rol">Rol</label>
                        <select id="admin_rol" name="rol" required class="form-control">
                            <option value="">Seleccione rol</option>
                            <option value="admin">Administrador</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addAdminModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="agregar_administrador">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar administrador -->
    <div id="editAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Editar Administrador</h3>
                <span class="close" onclick="closeModal('editAdminModal')">&times;</span>
            </div>
            <form method="post" action="">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_admin_usuario">Usuario</label>
                        <input type="text" id="edit_admin_usuario" name="usuario" required class="form-control" placeholder="Ingrese nombre de usuario">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_admin_password">Contraseña (dejar vacío para mantener la actual)</label>
                        <input type="password" id="edit_admin_password" name="password" class="form-control" placeholder="Ingrese nueva contraseña" minlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_admin_rol">Rol</label>
                        <select id="edit_admin_rol" name="rol" required class="form-control">
                            <option value="">Seleccione rol</option>
                            <option value="admin">Administrador</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editAdminModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="editar_administrador">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para registro manual de asistencia -->
    <div id="manualAttendanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-user-plus"></i> Registrar Asistencia Manual</h3>
                <span class="close" onclick="closeModal('manualAttendanceModal')">&times;</span>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="empleado_id">Empleado</label>
                        <select id="empleado_id" name="empleado_id" class="employee-select form-control" required style="width: 100%">
                            <option value="">Buscar empleado por nombre o DNI...</option>
                            <?php foreach ($employeesList as $emp): ?>
                                <?php if (isset($emp['id']) && isset($emp['nombre_completo']) && isset($emp['dni'])): ?>
                                    <option value="<?= htmlspecialchars($emp['id']) ?>">
                                        <?= htmlspecialchars($emp['nombre_completo']) ?> (<?= htmlspecialchars($emp['dni']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="hora">Hora (HH:MM:SS)</label>
                        <input type="time" id="hora" name="hora" value="<?= date('H:i:s') ?>" step="1" required class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('manualAttendanceModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="registrar_asistencia">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para registro manual de permiso -->
    <div id="manualPermissionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-file-signature"></i> Registrar Permiso Manual</h3>
                <span class="close" onclick="closeModal('manualPermissionModal')">&times;</span>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="permiso_empleado_id">Empleado</label>
                        <select id="permiso_empleado_id" name="empleado_id" class="employee-select form-control" required style="width: 100%">
                            <option value="">Buscar empleado por nombre o DNI...</option>
                            <?php foreach ($employeesList as $emp): ?>
                                <?php if (isset($emp['id']) && isset($emp['nombre_completo']) && isset($emp['dni'])): ?>
                                    <option value="<?= htmlspecialchars($emp['id']) ?>">
                                        <?= htmlspecialchars($emp['nombre_completo']) ?> (<?= htmlspecialchars($emp['dni']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_permiso">Tipo de Permiso</label>
                        <select id="tipo_permiso" name="tipo_permiso" required class="form-control">
                            <option value="">Seleccione tipo de permiso</option>
                            <option value="Personal">Personal</option>
                            <option value="Médico">Médico</option>
                            <option value="Familiar">Familiar</option>
                            <option value="Comision">Comisión de Servicios</option>
                            <option value="Visita_otra_sede">Visita a la Otra Sede</option>
                            <option value="Otros">Otros</option>
                        </select>

                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <textarea id="motivo" name="motivo" rows="3" required placeholder="Describa el motivo del permiso..." class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_permiso">Fecha del Permiso</label>
                        <input type="date" id="fecha_permiso" name="fecha_permiso" value="<?= date('Y-m-d') ?>" required class="form-control">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="hora_salida">Hora de Salida</label>
                            <input type="time" id="hora_salida" name="hora_salida" value="08:00" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="hora_retorno">Hora de Retorno</label>
                            <input type="time" id="hora_retorno" name="hora_retorno" value="13:00" required class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('manualPermissionModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="registrar_permiso_manual">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar permiso -->
    <div id="editPermissionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Editar Permiso</h3>
                <span class="close" onclick="closeModal('editPermissionModal')">&times;</span>
            </div>
            <form method="post" action="">
                <input type="hidden" name="permiso_id" id="edit_permiso_id">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_tipo_permiso">Tipo de Permiso</label>
                        <select id="edit_tipo_permiso" name="tipo_permiso" required class="form-control">
                            <option value="">Seleccione tipo de permiso</option>
                            <option value="Personal">Personal</option>
                            <option value="Médico">Médico</option>
                            <option value="Familiar">Familiar</option>
                            <option value="Comision">Comisión de Servicios</option>
                            <option value="Visita_otra_sede">Visita a la Otra Sede</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_motivo">Motivo</label>
                        <textarea id="edit_motivo" name="motivo" rows="3" required placeholder="Describa el motivo del permiso..." class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_fecha_permiso">Fecha del Permiso</label>
                        <input type="date" id="edit_fecha_permiso" name="fecha_permiso" required class="form-control">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="edit_hora_salida">Hora de Salida</label>
                            <input type="time" id="edit_hora_salida" name="hora_salida" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_hora_retorno">Hora de Retorno</label>
                            <input type="time" id="edit_hora_retorno" name="hora_retorno" required class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editPermissionModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="editar_permiso">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para imagen ampliada - MEJORADO -->
    <div id="imageModal" class="image-modal">
        <span class="close-image" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Función para enviar filtros automáticamente
        function submitFilters() {
            document.getElementById('filtersForm').submit();
        }
        
        // Inicializar Select2 para los selects
        $(document).ready(function() {
            $('.employee-select').select2({
                placeholder: "Buscar empleado por nombre o DNI...",
                minimumInputLength: 2,
                ajax: {
                    url: '?search_employees=1',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term
                        };
                    },
                    processResults: function(data) {
                        var validData = data.filter(function(item) {
                            return item && item.id && item.nombre_completo && item.dni;
                        });
                        
                        return {
                            results: validData.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.nombre_completo + ' (' + item.dni + ')',
                                    nombres: item.nombres,
                                    apellidos: item.apellidos,
                                    area: item.area,
                                    cargo: item.puesto
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            
            // Auto-ocultar mensajes flash después de 2.5 segundos
            setTimeout(function() {
                var flashMessage = document.getElementById('flash-message');
                if (flashMessage) {
                    flashMessage.style.display = 'none';
                }
            }, 2500);
            
            // Control de tiempo de sesión
            let inactivityTime = function() {
                let time;
                const resetTimer = function() {
                    clearTimeout(time);
                    time = setTimeout(() => {
                        // Redirigir al logout después de 15 minutos de inactividad
                        window.location.href = 'index.php';
                    }, 900000); // 15 minutos en milisegundos
                };
                
                // Eventos que resetearán el timer
                window.onload = resetTimer;
                window.onmousemove = resetTimer;
                window.onmousedown = resetTimer;
                window.ontouchstart = resetTimer;
                window.onclick = resetTimer;
                window.onkeypress = resetTimer;
                window.addEventListener('scroll', resetTimer, true);
            };
            
            // Iniciar el control de inactividad
            inactivityTime();
            
            // Cargar cargos automáticamente cuando se selecciona un área
            $('#filter_area').on('change', function() {
                var area = $(this).val();
                if (area) {
                    $.get('?get_cargos_by_area=1&area=' + encodeURIComponent(area), function(cargos) {
                        var cargoSelect = $('#filter_cargo');
                        cargoSelect.empty().append('<option value="">Todos los cargos</option>');
                        cargos.forEach(function(cargo) {
                            cargoSelect.append($('<option>', {
                                value: cargo,
                                text: cargo
                            }));
                        });
                    });
                } else {
                    $('#filter_cargo').empty().append('<option value="">Todos los cargos</option>');
                }
            });
            
            // Inicializar cargos si ya hay un área seleccionada
            var currentArea = $('#filter_area').val();
            if (currentArea) {
                $.get('?get_cargos_by_area=1&area=' + encodeURIComponent(currentArea), function(cargos) {
                    var cargoSelect = $('#filter_cargo');
                    cargoSelect.empty().append('<option value="">Todos los cargos</option>');
                    cargos.forEach(function(cargo) {
                        cargoSelect.append($('<option>', {
                            value: cargo,
                            text: cargo,
                            selected: cargo === '<?= $filterCargo ?>'
                        }));
                    });
                });
            }
            
            // Los filtros se aplican automáticamente al cambiar
            $('#filtersForm select, #filtersForm input').on('change', function() {
                submitFilters();
            });
        });
        
        // Funciones para los modales
        function openAddEmpleadoModal() {
            document.getElementById('addEmpleadoModal').style.display = 'block';
        }
        
        function openEditEmpleadoModal(id) {
            // Hacer una solicitud AJAX para obtener los datos del empleado
            fetch('?get_empleado=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_empleado_id').value = data.id;
                        document.getElementById('edit_dni').value = data.dni || '';
                        document.getElementById('edit_nombres').value = data.nombres || '';
                        document.getElementById('edit_apellidos').value = data.apellidos || '';
                        document.getElementById('edit_area').value = data.area || '';
                        document.getElementById('edit_inicio_contrato').value = data.inicio_contrato || '';
                        document.getElementById('edit_fin_contrato').value = data.fin_contrato || '';
                        document.getElementById('edit_tipo_personal').value = data.tipo_personal || '';
                        
                        // Actualizar cargos según el área
                        updateEditEmpleadoCargos(data.area, data.puesto);
                        
                        // Actualizar campos de horario
                        toggleEditHorarioFields(data.tipo_personal);
                        
                        if (data.entrada_manana) {
                            document.getElementById('edit_entrada_manana').value = data.entrada_manana;
                        }
                        if (data.salida_manana) {
                            document.getElementById('edit_salida_manana').value = data.salida_manana;
                        }
                        if (data.entrada_tarde) {
                            document.getElementById('edit_entrada_tarde').value = data.entrada_tarde;
                        }
                        if (data.salida_tarde) {
                            document.getElementById('edit_salida_tarde').value = data.salida_tarde;
                        }
                        
                        // Actualizar foto
                        if (data.foto) {
                            document.getElementById('editPhotoPreview').src = '<?= FOTO_DIR ?>' + data.foto;
                        } else {
                            document.getElementById('editPhotoPreview').src = '';
                            document.getElementById('editPhotoPreview').style.display = 'none';
                        }
                        
                        document.getElementById('editEmpleadoModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del empleado');
                });
        }
        
        function openAddAdminModal() {
            document.getElementById('addAdminModal').style.display = 'block';
        }
        
        function openEditAdminModal(id) {
            // Hacer una solicitud AJAX para obtener los datos del administrador
            fetch('?get_admin=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_admin_id').value = data.id;
                        document.getElementById('edit_admin_usuario').value = data.usuario || '';
                        document.getElementById('edit_admin_rol').value = data.rol || '';
                        
                        document.getElementById('editAdminModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del administrador');
                });
        }
        
        function openManualAttendanceModal() {
            document.getElementById('manualAttendanceModal').style.display = 'block';
        }
        
        function openManualPermissionModal() {
            document.getElementById('manualPermissionModal').style.display = 'block';
        }
        
        function openEditPermissionModal(id) {
            // Hacer una solicitud AJAX para obtener los datos del permiso
            fetch('?get_permiso=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('edit_permiso_id').value = data.id;
                        document.getElementById('edit_tipo_permiso').value = data.tipo_permiso || '';
                        document.getElementById('edit_motivo').value = data.motivo || '';
                        document.getElementById('edit_fecha_permiso').value = data.fecha_permiso || '';
                        document.getElementById('edit_hora_salida').value = data.hora_salida || '';
                        document.getElementById('edit_hora_retorno').value = data.hora_retorno || '';
                        
                        document.getElementById('editPermissionModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del permiso');
                });
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Función para actualizar cargos de empleado según el área seleccionada (agregar) - CORREGIDA
        function updateEmpleadoCargos() {
            var area = document.getElementById('area').value;
            var cargoSelect = document.getElementById('cargo');
            
            if (area) {
                fetch('?get_cargos_by_area=1&area=' + encodeURIComponent(area))
                    .then(response => response.json())
                    .then(cargos => {
                        cargoSelect.innerHTML = '<option value="">Seleccione cargo</option>';
                        cargos.forEach(cargo => {
                            var option = document.createElement('option');
                            option.value = cargo;
                            option.textContent = cargo;
                            cargoSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        cargoSelect.innerHTML = '<option value="">Error al cargar cargos</option>';
                    });
            } else {
                cargoSelect.innerHTML = '<option value="">Primero seleccione un área</option>';
            }
        }
        
        // Función para actualizar cargos de empleado según el área seleccionada (editar) - CORREGIDA
        function updateEditEmpleadoCargos(area = null, selectedCargo = null) {
            var areaElem = document.getElementById('edit_area');
            var areaValue = area || areaElem.value;
            var cargoSelect = document.getElementById('edit_cargo');
            
            if (areaValue) {
                fetch('?get_cargos_by_area=1&area=' + encodeURIComponent(areaValue))
                    .then(response => response.json())
                    .then(cargos => {
                        cargoSelect.innerHTML = '<option value="">Seleccione cargo</option>';
                        cargos.forEach(cargo => {
                            var option = document.createElement('option');
                            option.value = cargo;
                            option.textContent = cargo;
                            if (selectedCargo && cargo === selectedCargo) {
                                option.selected = true;
                            }
                            cargoSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        cargoSelect.innerHTML = '<option value="">Error al cargar cargos</option>';
                    });
            } else {
                cargoSelect.innerHTML = '<option value="">Primero seleccione un área</option>';
            }
        }
        
        // Mostrar/ocultar campos de horario según tipo de personal
        function toggleHorarioFields() {
            var tipoPersonal = document.getElementById('tipo_personal').value;
            var horarioFields = document.getElementById('horarioFields');
            
            if (tipoPersonal === 'Administrativo') {
                horarioFields.classList.remove('hidden');
            } else {
                horarioFields.classList.add('hidden');
            }
        }
        
        function toggleEditHorarioFields(tipoPersonal = null) {
            var tipoElem = document.getElementById('edit_tipo_personal');
            var tipoValue = tipoPersonal || tipoElem.value;
            var horarioFields = document.getElementById('editHorarioFields');
            
            if (tipoValue === 'Administrativo') {
                horarioFields.classList.remove('hidden');
            } else {
                horarioFields.classList.add('hidden');
            }
        }
        
        // Vista previa de foto
        function previewPhoto(input) {
            var preview = document.getElementById('photoPreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
        
        function previewEditPhoto(input) {
            var preview = document.getElementById('editPhotoPreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Modal para imagen ampliada - MEJORADO
        function openImageModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'flex';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
            if (event.target.className === 'image-modal') {
                closeImageModal();
            }
        }
    </script>
</body>
</html>