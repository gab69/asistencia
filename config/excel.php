<?php
if (isset($_GET['export_excel'])) {
    $reportType = $_GET['report_type'] ?? 'daily';
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
    // Obtener filtros actuales
    $filtros = [];
    $searchTerm = $_GET['search_term'] ?? '';
    $filterArea = $_GET['filter_area'] ?? '';
    $filterCargo = $_GET['filter_cargo'] ?? '';
    $filterEstado = $_GET['filter_estado'] ?? '';
    $filterRol = $_GET['filter_rol'] ?? '';
    $filterTurno = $_GET['filter_turno'] ?? '';
    $filterTipoPersonal = $_GET['filter_tipo_personal'] ?? '';
    
    if ($searchTerm) $filtros['busqueda'] = $searchTerm;
    if ($filterArea) $filtros['area'] = $filterArea;
    if ($filterCargo) $filtros['cargo'] = $filterCargo;
    if ($filterEstado) $filtros['estado'] = $filterEstado;
    if ($filterRol) $filtros['rol'] = $filterRol;
    if ($filterTurno) $filtros['turno'] = $filterTurno;
    if ($filterTipoPersonal) $filtros['tipo_personal'] = $filterTipoPersonal;
    
    // Obtener todos los datos sin paginación
    switch ($reportType) {
        case 'tardiness':
            $allData = $reportGenerator->getTardinessReport($fechaInicio, $fechaFin, $filtros);
            $filename = "Reporte_Tardanzas_" . date('Y-m-d') . ".xls";
            $reportTitle = "REPORTE DE TARDANZAS";
            break;
            
        case 'permission':
            $allData = $reportGenerator->getPermissionReport($fechaInicio, $fechaFin, $filtros);
            $permissionTotals = $reportGenerator->getPermissionTotals($fechaInicio, $fechaFin, $filtros);
            $filename = "Reporte_Permisos_" . date('Y-m-d') . ".xls";
            $reportTitle = "REPORTE DE PERMISOS";
            break;
            
        case 'no_asistencia':
            $allData = $reportGenerator->getEmployeesWithoutAttendance($fechaInicio, $fechaFin, $filtros);
            $filename = "Empleados_Sin_Asistencia_" . date('Y-m-d') . ".xls";
            $reportTitle = "EMPLEADOS SIN ASISTENCIA";
            break;
            
        case 'trabajadores':
            $allData = $reportGenerator->getEmployees($filtros);
            $filename = "Lista_Trabajadores_" . date('Y-m-d') . ".xls";
            $reportTitle = "LISTA DE TRABAJADORES";
            break;
            
        case 'administradores':
            $allData = $reportGenerator->getAdministradores($filtros);
            $filename = "Lista_Administradores_" . date('Y-m-d') . ".xls";
            $reportTitle = "LISTA DE ADMINISTRADORES";
            break;
            
        case 'history':
            $historialFechaInicio = $_GET['historial_fecha_inicio'] ?? date('Y-m-01');
            $historialFechaFin = $_GET['historial_fecha_fin'] ?? date('Y-m-d');
            $historialTabla = $_GET['historial_tabla'] ?? '';
            $historialAccion = $_GET['historial_accion'] ?? '';
            $historialCreadoPor = $_GET['historial_creado_por'] ?? '';
            
            $allData = $reportGenerator->getHistorial($historialFechaInicio, $historialFechaFin, $historialTabla, $historialAccion, $historialCreadoPor, $filtros);
            $filename = "Historial_Cambios_" . date('Y-m-d') . ".xls";
            $reportTitle = "HISTORIAL DE CAMBIOS";
            break;
            
        default: // daily
            $allData = $reportGenerator->getDailyReport($fechaInicio, $fechaFin, $filtros);
            $filename = "Reporte_Diario_" . date('Y-m-d') . ".xls";
            $reportTitle = "REPORTE DIARIO DE ASISTENCIA";
            break;
    }
    
    // Configurar headers para descarga
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");
    
    // Generar contenido HTML con diseño corporativo 
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . $reportTitle . ' - Universidad Roosevelt</title>
        <style>
            /* ESTILOS CORPORATIVOS UNIVERSIDAD ROOSEVELT */
            body {
                font-family: "Arial", sans-serif;
                margin: 20px;
                color: #343a40;
            }
            
            .header {
                border-bottom: 3px solid #8A1538;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            
            .logo-section {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .university-name {
                font-size: 24px;
                font-weight: bold;
                color: #8A1538;
                margin-left: 10px;
            }
            
            .report-title {
                font-size: 20px;
                font-weight: bold;
                color: #8A1538;
                text-align: center;
                margin: 15px 0;
                padding: 10px;
                background-color: #f8f9fa;
                border-left: 4px solid #D4AF37;
            }
            
            .info-section {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
                border-left: 4px solid #8A1538;
            }
            
            .info-row {
                display: flex;
                margin-bottom: 5px;
            }
            
            .info-label {
                font-weight: bold;
                color: #8A1538;
                min-width: 150px;
            }
            
            .filters-section {
                background-color: #fff3cd;
                padding: 12px;
                border-radius: 5px;
                margin-bottom: 20px;
                border-left: 4px solid #ffc107;
            }
            
            .filters-title {
                font-weight: bold;
                color: #856404;
                margin-bottom: 8px;
            }
            
            .filter-item {
                margin-left: 15px;
                margin-bottom: 3px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            
            th {
                background-color: #8A1538;
                color: white;
                font-weight: bold;
                padding: 12px 8px;
                text-align: left;
                border: 1px solid #6D0E2D;
            }
            
            td {
                padding: 10px 8px;
                border: 1px solid #dee2e6;
            }
            
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            
            tr:hover {
                background-color: #e9ecef;
            }
            
            .total-row {
                background-color: #D4AF37 !important;
                color: #343a40;
                font-weight: bold;
            }
            
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 2px solid #8A1538;
                text-align: center;
                color: #6c757d;
                font-size: 12px;
            }
            
            .accent-gold {
                color: #D4AF37;
                font-weight: bold;
            }
            
            .accent-burgundy {
                color: #8A1538;
                font-weight: bold;
            }
            
            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
            }
            
            .badge-success {
                background-color: #28a745;
                color: white;
            }
            
            .badge-warning {
                background-color: #ffc107;
                color: #212529;
            }
            
            .badge-danger {
                background-color: #dc3545;
                color: white;
            }
            
            .badge-info {
                background-color: #17a2b8;
                color: white;
            }
            
            .vertical-list {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            
            .vertical-item {
                padding: 2px 0;
                border-bottom: 1px dotted #dee2e6;
            }
            
            .vertical-item:last-child {
                border-bottom: none;
            }
        </style>
    </head>
    <body>';
    
    // Encabezado corporativo
    $html .= '
    <div class="header">
        <div class="logo-section">
            <div class="university-name">UNIVERSIDAD ROOSEVELT</div>
        </div>
        <div class="report-title">' . $reportTitle . '</div>
    </div>';
    
    // Información de exportación
    $html .= '
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Exportado por:</div>
            <div>' . $_SESSION['admin_username'] . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de exportación:</div>
            <div>' . date('d/m/Y H:i:s') . '</div>
        </div>
        <div class="info-row">
            <div class="info-label">Período del reporte:</div>
            <div>' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . '</div>
        </div>
    </div>';
    
    // Información de filtros aplicados
    $filtrosAplicados = [];
    if (!empty($filterArea)) $filtrosAplicados[] = "Área: <span class=\"accent-burgundy\">$filterArea</span>";
    if (!empty($filterCargo)) $filtrosAplicados[] = "Cargo: <span class=\"accent-burgundy\">$filterCargo</span>";
    if (!empty($filterEstado)) $filtrosAplicados[] = "Estado: <span class=\"accent-burgundy\">$filterEstado</span>";
    if (!empty($filterRol)) $filtrosAplicados[] = "Rol: <span class=\"accent-burgundy\">$filterRol</span>";
    if (!empty($filterTurno)) $filtrosAplicados[] = "Turno: <span class=\"accent-burgundy\">$filterTurno</span>";
    if (!empty($filterTipoPersonal)) $filtrosAplicados[] = "Tipo Personal: <span class=\"accent-burgundy\">$filterTipoPersonal</span>";
    if (!empty($searchTerm)) $filtrosAplicados[] = "Búsqueda: <span class=\"accent-burgundy\">$searchTerm</span>";
    
    if (!empty($filtrosAplicados)) {
        $html .= '
        <div class="filters-section">
            <div class="filters-title">FILTROS APLICADOS:</div>';
        foreach ($filtrosAplicados as $filtro) {
            $html .= '<div class="filter-item">• ' . $filtro . '</div>';
        }
        $html .= '</div>';
    }
    
    // Generar tabla según el tipo de reporte
    $html .= '<table>';
    
    // Headers de la tabla
    $headers = [];
    switch ($reportType) {
        case 'tardiness':
            $headers = ['#', 'DNI', 'APELLIDOS Y NOMBRES', 'ÁREA', 'CARGO', 'FECHA', 'HORA ENTRADA MAÑANA', 'HORA MARCADA MAÑANA', 'TARDANZA MAÑANA', 'HORA ENTRADA TARDE', 'HORA MARCADA TARDE', 'TARDANZA TARDE'];
            break;
            
        case 'permission':
            $headers = ['#', 'DNI', 'APELLIDOS Y NOMBRES', 'ÁREA', 'CARGO', 'FECHA PERMISO', 'TIPO PERMISO', 'MOTIVO', 'HORA SALIDA', 'HORA RETORNO', 'HORA REGISTRO', 'REGISTRADO POR'];
            break;
            
        case 'no_asistencia':
            $headers = ['#', 'DNI', 'APELLIDOS Y NOMBRES', 'ÁREA', 'CARGO', 'TURNO', 'TOTAL FALTAS', 'FECHAS FALTAS'];
            break;
            
        case 'trabajadores':
            $headers = ['#', 'DNI', 'APELLIDOS', 'NOMBRES', 'ÁREA', 'CARGO', 'TIPO PERSONAL', 'ESTADO', 'INICIO CONTRATO', 'FIN CONTRATO', 'CREADO POR'];
            break;
            
        case 'administradores':
            $headers = ['#', 'APELLIDOS', 'NOMBRES', 'USUARIO', 'ROL', 'ESTADO', 'FECHA CREACIÓN', 'CREADO POR'];
            break;
            
        case 'history':
            $headers = ['#', 'TABLA AFECTADA', 'USUARIO AFECTADO', 'ACCIÓN', 'CAMPO MODIFICADO', 'VALOR ANTERIOR', 'VALOR NUEVO', 'MOTIVO', 'MODIFICADO POR', 'FECHA MODIFICACIÓN'];
            break;
            
        default: // daily
            $headers = ['#', 'FECHA', 'DNI', 'APELLIDOS Y NOMBRES', 'ÁREA', 'CARGO', 'ENTRADA MAÑANA', 'SALIDA MAÑANA', 'ENTRADA TARDE', 'SALIDA TARDE', 'REGISTROS', 'REGISTRADO POR'];
            break;
    }
    
    $html .= '<thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    
    // Datos de la tabla
    $contador = 1;
    foreach ($allData as $row) {
        if (isset($row['es_total']) && $row['es_total']) {
            // Fila de totales
            $html .= '<tr class="total-row">';
            switch ($reportType) {
                case 'no_asistencia':
                    $html .= '<td colspan="6">TOTALES:</td>';
                    $html .= '<td>MAÑANA: ' . $row['total_faltas_manana'] . '</td>';
                    $html .= '<td>TARDE: ' . $row['total_faltas_tarde'] . '</td>';
                    $html .= '<td>TOTAL: ' . $row['total_general'] . '</td>';
                    break;
                case 'tardiness':
                    $html .= '<td colspan="8">TOTALES:</td>';
                    $html .= '<td>' . $row['total_tardanza_manana'] . '</td>';
                    $html .= '<td></td>';
                    $html .= '<td>' . $row['total_tardanza_tarde'] . '</td>';
                    $html .= '<td>TOTAL: ' . $row['total_tardanza'] . '</td>';
                    break;
                case 'permission':
                    $totalGeneral = 0;
                    $totalesTexto = [];
                    foreach ($permissionTotals as $total) {
                        $totalesTexto[] = $total['tipo_permiso'] . ': ' . $total['total'];
                        $totalGeneral += $total['total'];
                    }
                    $totalesTexto[] = 'TOTAL GENERAL: ' . $totalGeneral;
                    $html .= '<td colspan="7">TOTALES:</td>';
                    $html .= '<td colspan="4">' . implode(' | ', $totalesTexto) . '</td>';
                    break;
            }
            $html .= '</tr>';
            continue;
        }
        
        $html .= '<tr>';
        switch ($reportType) {
            case 'tardiness':
                // Combinar tardanzas de mañana y tarde
                $tardanzasCombinadas = [];
                
                foreach ($row['tardanzas_manana'] as $tardanza) {
                    $tardanzasCombinadas[$tardanza['fecha']] = [
                        'manana' => $tardanza,
                        'tarde' => null
                    ];
                }
                
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
                
                foreach ($tardanzasCombinadas as $fecha => $tardanzas) {
                    $html .= '<tr>';
                    $html .= '<td>' . $contador++ . '</td>';
                    $html .= '<td>' . $row['dni'] . '</td>';
                    $html .= '<td>' . $row['nombre_completo'] . '</td>';
                    $html .= '<td>' . $row['area'] . '</td>';
                    $html .= '<td>' . $row['puesto'] . '</td>';
                    $html .= '<td>' . $fecha . '</td>';
                    $html .= '<td>' . ($tardanzas['manana'] ? $tardanzas['manana']['hora_entrada'] : '-') . '</td>';
                    $html .= '<td>' . ($tardanzas['manana'] ? $tardanzas['manana']['hora_marcada'] : '-') . '</td>';
                    $html .= '<td>' . ($tardanzas['manana'] ? '<span class="badge badge-warning">' . $tardanzas['manana']['tardanza'] . '</span>' : '-') . '</td>';
                    $html .= '<td>' . ($tardanzas['tarde'] ? $tardanzas['tarde']['hora_entrada'] : '-') . '</td>';
                    $html .= '<td>' . ($tardanzas['tarde'] ? $tardanzas['tarde']['hora_marcada'] : '-') . '</td>';
                    $html .= '<td>' . ($tardanzas['tarde'] ? '<span class="badge badge-warning">' . $tardanzas['tarde']['tardanza'] . '</span>' : '-') . '</td>';
                    $html .= '</tr>';
                }
                break;
                
            case 'permission':
                $html .= '<td>' . $contador++ . '</td>';
                $html .= '<td>' . $row['dni'] . '</td>';
                $html .= '<td>' . $row['nombre_completo'] . '</td>';
                $html .= '<td>' . $row['area'] . '</td>';
                $html .= '<td>' . $row['puesto'] . '</td>';
                $html .= '<td>' . $row['fecha_permiso'] . '</td>';
                $html .= '<td><span class="badge badge-info">' . $row['tipo_permiso'] . '</span></td>';
                $html .= '<td>' . $row['motivo'] . '</td>';
                $html .= '<td>' . $row['hora_salida'] . '</td>';
                $html .= '<td>' . $row['hora_retorno'] . '</td>';
                $html .= '<td>' . $row['hora_registro'] . '</td>';
                $html .= '<td>' . $row['registrado_por'] . '</td>';
                $html .= '</tr>';
                break;
                
            case 'no_asistencia':
                if (!empty($row['faltas_manana'])) {
                    $html .= '<tr>';
                    $html .= '<td>' . $contador++ . '</td>';
                    $html .= '<td>' . $row['dni'] . '</td>';
                    $html .= '<td>' . $row['nombre_completo'] . '</td>';
                    $html .= '<td>' . $row['area'] . '</td>';
                    $html .= '<td>' . $row['puesto'] . '</td>';
                    $html .= '<td><span class="badge badge-warning">MAÑANA</span></td>';
                    $html .= '<td>' . $row['total_faltas_manana'] . '</td>';
                    // MODIFICADO: FECHAS FALTAS separadas por comas
                    $html .= '<td>';
                    if (!empty($row['faltas_manana'])) {
                        $html .= implode(', ', array_slice($row['faltas_manana'], 0, 10));
                        if (count($row['faltas_manana']) > 10) {
                            $html .= ', ...';
                        }
                    } else {
                        $html .= '-';
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                
                if (!empty($row['faltas_tarde'])) {
                    $html .= '<tr>';
                    $html .= '<td>' . $contador++ . '</td>';
                    $html .= '<td>' . $row['dni'] . '</td>';
                    $html .= '<td>' . $row['nombre_completo'] . '</td>';
                    $html .= '<td>' . $row['area'] . '</td>';
                    $html .= '<td>' . $row['puesto'] . '</td>';
                    $html .= '<td><span class="badge badge-warning">TARDE</span></td>';
                    $html .= '<td>' . $row['total_faltas_tarde'] . '</td>';
                    // MODIFICADO: FECHAS FALTAS separadas por comas
                    $html .= '<td>';
                    if (!empty($row['faltas_tarde'])) {
                        $html .= implode(', ', array_slice($row['faltas_tarde'], 0, 10));
                        if (count($row['faltas_tarde']) > 10) {
                            $html .= ', ...';
                        }
                    } else {
                        $html .= '-';
                    }
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                break;
                
            case 'trabajadores':
                $estadoBadge = $row['estado'] == 'Activo' ? 'badge-success' : 'badge-danger';
                $html .= '<td>' . $contador++ . '</td>';
                $html .= '<td>' . $row['dni'] . '</td>';
                $html .= '<td>' . $row['apellidos'] . '</td>';
                $html .= '<td>' . $row['nombres'] . '</td>';
                $html .= '<td>' . $row['area'] . '</td>';
                $html .= '<td>' . $row['puesto'] . '</td>';
                $html .= '<td>' . $row['tipo_personal'] . '</td>';
                $html .= '<td><span class="badge ' . $estadoBadge . '">' . $row['estado'] . '</span></td>';
                $html .= '<td>' . $row['inicio_contrato'] . '</td>';
                $html .= '<td>' . $row['fin_contrato'] . '</td>';
                $html .= '<td>' . $row['creado_por_nombre'] . '</td>';
                $html .= '</tr>';
                break;
                
            case 'administradores':
                $estadoBadge = $row['estado'] == 'Activo' ? 'badge-success' : 'badge-danger';
                $html .= '<td>' . $contador++ . '</td>';
                $html .= '<td>' . $row['apellidos'] . '</td>';
                $html .= '<td>' . $row['nombres'] . '</td>';
                $html .= '<td>' . $row['usuario'] . '</td>';
                $html .= '<td>' . $row['rol'] . '</td>';
                $html .= '<td><span class="badge ' . $estadoBadge . '">' . $row['estado'] . '</span></td>';
                $html .= '<td>' . $row['fecha_creacion'] . '</td>';
                $html .= '<td>' . $row['creado_por_nombre'] . '</td>';
                $html .= '</tr>';
                break;
                
            case 'history':
                $accionBadge = '';
                switch ($row['accion']) {
                    case 'INSERT': $accionBadge = 'badge-success'; break;
                    case 'UPDATE': $accionBadge = 'badge-warning'; break;
                    case 'DELETE': $accionBadge = 'badge-danger'; break;
                    default: $accionBadge = 'badge-info'; break;
                }
                $html .= '<td>' . $contador++ . '</td>';
                $html .= '<td>' . $row['tabla_afectada'] . '</td>';
                $html .= '<td>' . $row['usuario_afectado'] . '</td>';
                $html .= '<td><span class="badge ' . $accionBadge . '">' . $row['accion'] . '</span></td>';
                $html .= '<td>' . $row['campo_modificado'] . '</td>';
                $html .= '<td>' . $row['valor_anterior'] . '</td>';
                $html .= '<td>' . $row['valor_nuevo'] . '</td>';
                $html .= '<td>' . $row['motivo'] . '</td>';
                $html .= '<td>' . $row['modificado_por_nombre'] . '</td>';
                $html .= '<td>' . $row['fecha_modificacion'] . '</td>';
                $html .= '</tr>';
                break;
                
            default: // daily
                $html .= '<td>' . $contador++ . '</td>';
                $html .= '<td>' . $row['fecha'] . '</td>';
                $html .= '<td>' . $row['dni'] . '</td>';
                $html .= '<td>' . $row['nombre_completo'] . '</td>';
                $html .= '<td>' . $row['area'] . '</td>';
                $html .= '<td>' . $row['puesto'] . '</td>';
                $html .= '<td>' . (!empty($row['registros_entrada_manana']) ? min($row['registros_entrada_manana']) : '-') . '</td>';
                $html .= '<td>' . (!empty($row['registros_salida_manana']) ? min($row['registros_salida_manana']) : '-') . '</td>';
                $html .= '<td>' . (!empty($row['registros_entrada_tarde']) ? min($row['registros_entrada_tarde']) : '-') . '</td>';
                $html .= '<td>' . (!empty($row['registros_salida_tarde']) ? min($row['registros_salida_tarde']) : '-') . '</td>';
                // MODIFICADO: REGISTROS separados por comas
                $html .= '<td>';
                if (!empty($row['todos_registros'])) {
                    $html .= implode(', ', $row['todos_registros']);
                } else {
                    $html .= '-';
                }
                $html .= '</td>';
                // MODIFICADO: REGISTRADO POR separados por comas
                $html .= '<td>';
                if (!empty($row['todos_registradores'])) {
                    $html .= implode(', ', $row['todos_registradores']);
                } else {
                    $html .= '-';
                }
                $html .= '</td>';
                $html .= '</tr>';
                break;
        }
    }
    
    $html .= '</tbody></table>';
    
    // Pie de página
    $html .= '
    <div class="footer">
        <p>Documento generado automáticamente por el <span class="accent-burgundy">Sistema de Control de Asistencia - Universidad Roosevelt</span></p>
        <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
        <p><small>Confidencial - Uso interno</small></p>
    </div>
    
    </body>
    </html>';
    
    // Enviar el contenido HTML
    echo $html;
    exit;
}