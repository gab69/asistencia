<?php
// Configuración de tiempo de sesión - REACTIVADO EL TIEMPO DE 15 MINUTOS
ini_set('session.gc_maxlifetime', 900);
session_set_cookie_params(900);

include 'database/bd.php';
// Crear directorio de fotos si no existe
if (!file_exists(FOTO_DIR)) {
    mkdir(FOTO_DIR, 0777, true);
}

date_default_timezone_set(APP_TIMEZONE);

// Definir áreas y cargos preestablecidos
include "config/area_cargo.php";

// Verificar sesión de administrador
session_start();

// VERIFICACIÓN DE TIEMPO DE INACTIVIDAD DE 15 MINUTOS REACTIVADA
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // Sesión expirada
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualizar tiempo de actividad

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

// Conexión a la base de datos
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar permisos según rol
$isSuperAdmin = ($_SESSION['admin_role'] === 'admin');
$isSupervisor = ($_SESSION['admin_role'] === 'supervisor');



// Obtener configuración del sistema
function getConfig($clave, $pdo) {
    $stmt = $pdo->prepare("SELECT valor FROM configuracion_sistema WHERE clave = ?");
    $stmt->execute([$clave]);
    $result = $stmt->fetch();
    return $result ? $result['valor'] : null;
}

// Actualizar configuración del sistema
function updateConfig($clave, $valor, $pdo) {
    $stmt = $pdo->prepare("UPDATE configuracion_sistema SET valor = ? WHERE clave = ?");
    return $stmt->execute([$valor, $clave]);
}

// Registrar en el historial
function registrarHistorial($tabla, $usuarioAfectado, $accion, $campo, $valorAnterior, $valorNuevo, $motivo, $pdo) {
    $stmt = $pdo->prepare("INSERT INTO historial_cambios (tabla_afectada, usuario_afectado, accion, campo_modificado, valor_anterior, valor_nuevo, motivo, modificado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$tabla, $usuarioAfectado, $accion, $campo, $valorAnterior, $valorNuevo, $motivo, $_SESSION['admin_id']]);
}

// Clase para generar reportes
class ReportGenerator {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtiene lista de empleados/trabajadores con filtros
     */
    public function getEmployees($filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['area'])) {
            $where .= " AND e.area = ?";
            $params[] = $filtros['area'];
        }
        
        if (!empty($filtros['cargo'])) {
            $where .= " AND e.puesto = ?";
            $params[] = $filtros['cargo'];
        }
        
        if (!empty($filtros['estado'])) {
            $where .= " AND e.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['tipo_personal'])) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $filtros['tipo_personal'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ? OR e.area LIKE ? OR e.puesto LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT e.id, e.dni, e.nombres, e.apellidos, e.area, e.puesto, e.estado, e.foto, e.tipo_personal, e.inicio_contrato, e.fin_contrato,
                       COALESCE(ua.usuario, 'SISTEMA') AS creado_por_nombre
                FROM empleados e 
                LEFT JOIN usuarios_admin ua ON e.creado_por = ua.id 
                $where
                ORDER BY e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene lista de administradores con filtros
     */
    public function getAdministradores($filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['rol'])) {
            $where .= " AND ua.rol = ?";
            $params[] = $filtros['rol'];
        }
        
        if (!empty($filtros['estado'])) {
            $where .= " AND ua.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (ua.usuario LIKE ? OR ua.nombres LIKE ? OR ua.apellidos LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT ua.id, ua.nombres, ua.apellidos, ua.usuario, ua.rol, ua.estado, ua.fecha_creacion,
                       COALESCE(uc.usuario, 'SISTEMA') AS creado_por_nombre
                FROM usuarios_admin ua 
                LEFT JOIN usuarios_admin uc ON ua.creado_por = uc.id 
                $where
                ORDER BY ua.apellidos, ua.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un empleado por ID
     */
    public function getEmployeeById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene un administrador por ID
     */
    public function getAdminById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene reporte diario de asistencia con filtros
     */
    public function getDailyReport($fechaInicio, $fechaFin, $filtros = []) {
        $where = "WHERE r.fecha BETWEEN ? AND ?";
        $params = [$fechaInicio, $fechaFin];
        
        // Aplicar filtros
        if (!empty($filtros['area'])) {
            $where .= " AND e.area = ?";
            $params[] = $filtros['area'];
        }
        
        if (!empty($filtros['cargo'])) {
            $where .= " AND e.puesto = ?";
            $params[] = $filtros['cargo'];
        }
        
        if (!empty($filtros['tipo_personal'])) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $filtros['tipo_personal'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ? OR e.area LIKE ? OR e.puesto LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT 
                    e.id, e.dni, 
                    CONCAT(e.apellidos, ' ', e.nombres) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal,
                    e.entrada_manana, e.salida_manana, e.entrada_tarde, e.salida_tarde,
                    r.fecha,
                    TIME(r.hora) AS hora,
                    CASE 
                        WHEN r.tipo_registro = 'SISTEMA' THEN 'SISTEMA'
                        ELSE COALESCE(ua.usuario, 'SISTEMA')
                    END AS registrado_por
                FROM empleados e
                LEFT JOIN registros_asistencia r ON e.id = r.empleado_id
                LEFT JOIN usuarios_admin ua ON r.registrado_por = ua.id
                $where
                ORDER BY r.fecha, e.area, e.apellidos, e.nombres, r.hora";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        
        // Agrupar por empleado y fecha y clasificar registros
        $groupedData = [];
        foreach ($result as $row) {
            $key = $row['id'] . '_' . $row['fecha'];
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'id' => $row['id'],
                    'dni' => $row['dni'],
                    'nombre_completo' => $row['nombre_completo'],
                    'area' => $row['area'],
                    'puesto' => $row['puesto'],
                    'tipo_personal' => $row['tipo_personal'],
                    'entrada_manana' => $row['entrada_manana'],
                    'salida_manana' => $row['salida_manana'],
                    'entrada_tarde' => $row['entrada_tarde'],
                    'salida_tarde' => $row['salida_tarde'],
                    'fecha' => $row['fecha'],
                    'registros_entrada_manana' => [],
                    'registros_salida_manana' => [],
                    'registros_entrada_tarde' => [],
                    'registros_salida_tarde' => [],
                    'todos_registros' => [],
                    'todos_registradores' => []
                ];
            }
            
            if ($row['hora']) {
                // Clasificar el registro según el horario
                $horaRegistro = $row['hora'];
                $salidaManana = $row['salida_manana'] ?: '13:00:00';
                $entradaTarde = $row['entrada_tarde'] ?: '14:00:00';
                $salidaTarde = $row['salida_tarde'] ?: '18:00:00';
                
                if ($horaRegistro <= $salidaManana) {
                    $groupedData[$key]['registros_entrada_manana'][] = $horaRegistro;
                } elseif ($horaRegistro <= '14:30:00') {
                    $groupedData[$key]['registros_salida_manana'][] = $horaRegistro;
                } elseif ($horaRegistro <= $salidaTarde) {
                    $groupedData[$key]['registros_entrada_tarde'][] = $horaRegistro;
                } else {
                    $groupedData[$key]['registros_salida_tarde'][] = $horaRegistro;
                }
                
                // Agregar a todos los registros y registradores
                $groupedData[$key]['todos_registros'][] = $horaRegistro;
                $groupedData[$key]['todos_registradores'][] = $row['registrado_por'];
            }
        }
        
        return array_values($groupedData);
    }
    
    /**
     * Obtiene empleados que no marcaron asistencia en un rango de fechas con filtros
     */
    public function getEmployeesWithoutAttendance($fechaInicio, $fechaFin, $filtros = []) {
        $where = "WHERE e.estado = 'activo'";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['area'])) {
            $where .= " AND e.area = ?";
            $params[] = $filtros['area'];
        }
        
        if (!empty($filtros['cargo'])) {
            $where .= " AND e.puesto = ?";
            $params[] = $filtros['cargo'];
        }
        
        if (!empty($filtros['tipo_personal'])) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $filtros['tipo_personal'];
        }
        
        if (!empty($filtros['turno'])) {
            // Filtro por turno - se aplicará después en el procesamiento
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ? OR e.area LIKE ? OR e.puesto LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Obtener todos los empleados activos
        $sqlEmpleados = "SELECT 
                    e.id, e.dni, 
                    CONCAT(e.apellidos, ' ', e.nombres) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal,
                    e.entrada_manana, e.salida_manana, e.entrada_tarde, e.salida_tarde
                FROM empleados e
                $where
                ORDER BY e.area, e.apellidos, e.nombres";
        
        $stmtEmpleados = $this->pdo->prepare($sqlEmpleados);
        $stmtEmpleados->execute($params);
        $empleados = $stmtEmpleados->fetchAll();
        
        // Obtener días con asistencia por empleado
        $sqlAsistencias = "SELECT empleado_id, DATE(fecha) as fecha, TIME(hora) as hora
                          FROM registros_asistencia 
                          WHERE fecha BETWEEN ? AND ?
                          ORDER BY empleado_id, fecha, hora";
        $stmtAsist = $this->pdo->prepare($sqlAsistencias);
        $stmtAsist->execute([$fechaInicio, $fechaFin]);
        $asistencias = $stmtAsist->fetchAll();
        
        // Obtener permisos por empleado
        $sqlPermisos = "SELECT empleado_id, fecha_permiso 
                       FROM permisos 
                       WHERE fecha_permiso BETWEEN ? AND ?";
        $stmtPerm = $this->pdo->prepare($sqlPermisos);
        $stmtPerm->execute([$fechaInicio, $fechaFin]);
        $permisos = $stmtPerm->fetchAll();
        
        // Organizar asistencias y permisos por empleado y fecha
        $asistenciasPorEmpleado = [];
        $permisosPorEmpleado = [];
        
        foreach ($asistencias as $asistencia) {
            $empleadoId = $asistencia['empleado_id'];
            $fecha = $asistencia['fecha'];
            $hora = $asistencia['hora'];
            
            if (!isset($asistenciasPorEmpleado[$empleadoId])) {
                $asistenciasPorEmpleado[$empleadoId] = [];
            }
            if (!isset($asistenciasPorEmpleado[$empleadoId][$fecha])) {
                $asistenciasPorEmpleado[$empleadoId][$fecha] = [];
            }
            $asistenciasPorEmpleado[$empleadoId][$fecha][] = $hora;
        }
        
        foreach ($permisos as $permiso) {
            $empleadoId = $permiso['empleado_id'];
            $fecha = $permiso['fecha_permiso'];
            if (!isset($permisosPorEmpleado[$empleadoId])) {
                $permisosPorEmpleado[$empleadoId] = [];
            }
            $permisosPorEmpleado[$empleadoId][$fecha] = true;
        }
        
        // Generar lista de fechas en el rango
        $fechas = [];
        $start = new DateTime($fechaInicio);
        $end = new DateTime($fechaFin);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
        
        foreach ($period as $date) {
            $fechas[] = $date->format('Y-m-d');
        }
        
        // Encontrar empleados sin asistencia
        $result = [];
        $totalFaltasManana = 0;
        $totalFaltasTarde = 0;
        
        foreach ($empleados as $empleado) {
            $empleadoId = $empleado['id'];
            $faltasManana = [];
            $faltasTarde = [];
            
            foreach ($fechas as $fecha) {
                // Verificar si el empleado no tiene asistencia ni permiso en esta fecha
                $tieneAsistencia = isset($asistenciasPorEmpleado[$empleadoId][$fecha]);
                $tienePermiso = isset($permisosPorEmpleado[$empleadoId][$fecha]);
                
                if (!$tieneAsistencia && !$tienePermiso) {
                    // Sin asistencia todo el día
                    $faltasManana[] = $fecha;
                    $faltasTarde[] = $fecha;
                } elseif ($tieneAsistencia && !$tienePermiso) {
                    // Tiene asistencia, verificar turnos
                    $horas = $asistenciasPorEmpleado[$empleadoId][$fecha];
                    $tieneManana = false;
                    $tieneTarde = false;
                    
                    foreach ($horas as $hora) {
                        if ($hora <= '13:00:00') {
                            $tieneManana = true;
                        }
                        if ($hora >= '14:00:00' && $hora <= '23:59:59') {
                            $tieneTarde = true;
                        }
                    }
                    
                    if (!$tieneManana) {
                        $faltasManana[] = $fecha;
                    }
                    if (!$tieneTarde) {
                        $faltasTarde[] = $fecha;
                    }
                }
            }
            
            if (!empty($faltasManana) || !empty($faltasTarde)) {
                $empleado['faltas_manana'] = $faltasManana;
                $empleado['faltas_tarde'] = $faltasTarde;
                $empleado['total_faltas_manana'] = count($faltasManana);
                $empleado['total_faltas_tarde'] = count($faltasTarde);
                $result[] = $empleado;
                
                $totalFaltasManana += count($faltasManana);
                $totalFaltasTarde += count($faltasTarde);
            }
        }
        
        // Aplicar filtro por turno si está presente
        if (!empty($filtros['turno'])) {
            $result = array_filter($result, function($row) use ($filtros) {
                if ($filtros['turno'] === 'MAÑANA') {
                    return !empty($row['faltas_manana']);
                } elseif ($filtros['turno'] === 'TARDE') {
                    return !empty($row['faltas_tarde']);
                }
                return true;
            });
        }
        
        // Agregar fila de totales
        $result[] = [
            'es_total' => true,
            'total_faltas_manana' => $totalFaltasManana,
            'total_faltas_tarde' => $totalFaltasTarde,
            'total_general' => $totalFaltasManana + $totalFaltasTarde
        ];
        
        return $result;
    }
    
    /**
     * Obtiene reporte de permisos con filtros
     */
    public function getPermissionReport($fechaInicio, $fechaFin, $filtros = []) {
        $where = "WHERE p.fecha_permiso BETWEEN ? AND ?";
        $params = [$fechaInicio, $fechaFin];
        
        // Aplicar filtros
        if (!empty($filtros['area'])) {
            $where .= " AND e.area = ?";
            $params[] = $filtros['area'];
        }
        
        if (!empty($filtros['cargo'])) {
            $where .= " AND e.puesto = ?";
            $params[] = $filtros['cargo'];
        }
        
        if (!empty($filtros['tipo_personal'])) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $filtros['tipo_personal'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ? OR e.area LIKE ? OR e.puesto LIKE ? OR p.tipo_permiso LIKE ? OR p.motivo LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT 
                    p.id, p.fecha_permiso, p.tipo_permiso, p.motivo, 
                    p.hora_salida, p.hora_retorno, p.hora_registro,
                    CASE 
                        WHEN p.tipo_registro = 'SISTEMA' THEN 'SISTEMA'
                        ELSE COALESCE(ua.usuario, 'SISTEMA')
                    END AS registrado_por,
                    e.id AS empleado_id, e.dni, 
                    CONCAT(e.apellidos, ' ', e.nombres) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal
                FROM permisos p
                JOIN empleados e ON p.empleado_id = e.id
                LEFT JOIN usuarios_admin ua ON p.registrado_por = ua.id
                $where
                ORDER BY p.fecha_permiso, e.area, e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene totales por tipo de permiso
     */
    public function getPermissionTotals($fechaInicio, $fechaFin, $filtros = []) {
        $where = "WHERE p.fecha_permiso BETWEEN ? AND ?";
        $params = [$fechaInicio, $fechaFin];
        
        // Aplicar filtros
        if (!empty($filtros['area'])) {
            $where .= " AND e.area = ?";
            $params[] = $filtros['area'];
        }
        
        if (!empty($filtros['cargo'])) {
            $where .= " AND e.puesto = ?";
            $params[] = $filtros['cargo'];
        }
        
        if (!empty($filtros['tipo_personal'])) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $filtros['tipo_personal'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (e.dni LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ? OR e.area LIKE ? OR e.puesto LIKE ? OR p.tipo_permiso LIKE ? OR p.motivo LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT 
                    p.tipo_permiso, 
                    COUNT(*) as total
                FROM permisos p
                JOIN empleados e ON p.empleado_id = e.id
                $where
                GROUP BY p.tipo_permiso
                ORDER BY total DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un permiso por ID
     */
    public function getPermissionById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM permisos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene reporte de tardanzas - CORREGIDO: Solo cuenta tardanzas después de la hora de entrada + tolerancia
     */
    public function getTardinessReport($fechaInicio, $fechaFin, $filtros = []) {
        $minutosTolerancia = getConfig('minutos_tolerancia', $this->pdo) ?: 5;
        
        // Obtener el reporte diario para calcular tardanzas
        $dailyReport = $this->getDailyReport($fechaInicio, $fechaFin, $filtros);
        
        $result = [];
        $totalTardanzaManana = 0;
        $totalTardanzaTarde = 0;
        
        foreach ($dailyReport as $empleado) {
            // Solo procesar empleados administrativos
            if ($empleado['tipo_personal'] !== 'Administrativo') {
                continue;
            }
            
            $tardanzas = [
                'id' => $empleado['id'],
                'dni' => $empleado['dni'],
                'nombre_completo' => $empleado['nombre_completo'],
                'area' => $empleado['area'],
                'puesto' => $empleado['puesto'],
                'tipo_personal' => $empleado['tipo_personal'],
                'entrada_manana' => $empleado['entrada_manana'],
                'entrada_tarde' => $empleado['entrada_tarde'],
                'tardanzas_manana' => [],
                'tardanzas_tarde' => [],
                'minutos_tolerancia' => $minutosTolerancia
            ];
            
            // Procesar tardanzas de la mañana - CORREGIDO: Solo cuenta después de la hora de entrada + tolerancia
            if (!empty($empleado['registros_entrada_manana']) && $empleado['entrada_manana']) {
                $primerRegistroManana = min($empleado['registros_entrada_manana']);
                $horaEntradaManana = new DateTime($empleado['entrada_manana']);
                $horaRegistroManana = new DateTime($primerRegistroManana);
                
                // Calcular diferencia en minutos
                $diferencia = $horaEntradaManana->diff($horaRegistroManana);
                $minutosDiferencia = ($diferencia->h * 60) + $diferencia->i;
                
                // CORRECCIÓN: Solo contar como tardanza si el registro es DESPUÉS de la hora de entrada + tolerancia
                if ($horaRegistroManana > $horaEntradaManana) {
                    $minutosTardanza = max(0, $minutosDiferencia - $minutosTolerancia);
                    
                    if ($minutosTardanza > 0) {
                        $horas = floor($minutosTardanza / 60);
                        $minutos = $minutosTardanza % 60;
                        
                        $tardanzaFormato = sprintf("%02d:%02d", $horas, $minutos);
                        
                        $tardanzas['tardanzas_manana'][] = [
                            'fecha' => $empleado['fecha'],
                            'hora_entrada' => $empleado['entrada_manana'],
                            'hora_marcada' => $primerRegistroManana,
                            'tardanza' => $tardanzaFormato,
                            'minutos_tardanza' => $minutosTardanza
                        ];
                        
                        $totalTardanzaManana += $minutosTardanza;
                    }
                }
            }
            
            // Procesar tardanzas de la tarde - CORREGIDO: Solo cuenta después de la hora de entrada + tolerancia
            if (!empty($empleado['registros_entrada_tarde']) && $empleado['entrada_tarde']) {
                $primerRegistroTarde = min($empleado['registros_entrada_tarde']);
                $horaEntradaTarde = new DateTime($empleado['entrada_tarde']);
                $horaRegistroTarde = new DateTime($primerRegistroTarde);
                
                // Calcular diferencia en minutos
                $diferencia = $horaEntradaTarde->diff($horaRegistroTarde);
                $minutosDiferencia = ($diferencia->h * 60) + $diferencia->i;
                
                // CORRECCIÓN: Solo contar como tardanza si el registro es DESPUÉS de la hora de entrada + tolerancia
                if ($horaRegistroTarde > $horaEntradaTarde) {
                    $minutosTardanza = max(0, $minutosDiferencia - $minutosTolerancia);
                    
                    if ($minutosTardanza > 0) {
                        $horas = floor($minutosTardanza / 60);
                        $minutos = $minutosTardanza % 60;
                        
                        $tardanzaFormato = sprintf("%02d:%02d", $horas, $minutos);
                        
                        $tardanzas['tardanzas_tarde'][] = [
                            'fecha' => $empleado['fecha'],
                            'hora_entrada' => $empleado['entrada_tarde'],
                            'hora_marcada' => $primerRegistroTarde,
                            'tardanza' => $tardanzaFormato,
                            'minutos_tardanza' => $minutosTardanza
                        ];
                        
                        $totalTardanzaTarde += $minutosTardanza;
                    }
                }
            }
            
            // Solo agregar si tiene tardanzas
            if (!empty($tardanzas['tardanzas_manana']) || !empty($tardanzas['tardanzas_tarde'])) {
                $result[] = $tardanzas;
            }
        }
        
        // Calcular totales
        $totalTardanza = $totalTardanzaManana + $totalTardanzaTarde;
        
        // Convertir minutos a formato HH:MM
        $result[] = [
            'total_tardanza_manana' => $this->minutesToTime($totalTardanzaManana),
            'total_tardanza_tarde' => $this->minutesToTime($totalTardanzaTarde),
            'total_tardanza' => $this->minutesToTime($totalTardanza),
            'es_total' => true
        ];
        
        return $result;
    }
    
    /**
     * Convierte minutos a formato HH:MM
     */
    private function minutesToTime($minutes) {
        $horas = floor($minutes / 60);
        $minutos = $minutes % 60;
        return sprintf("%02d:%02d", $horas, $minutos);
    }
    
    /**
     * Obtiene historial de cambios - CORREGIDO: Hora correcta
     */
    public function getHistorial($fechaInicio = null, $fechaFin = null, $tabla = null, $accion = null, $creadoPor = null, $filtros = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if ($fechaInicio) {
            $where .= " AND DATE(h.fecha_modificacion) >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $where .= " AND DATE(h.fecha_modificacion) <= ?";
            $params[] = $fechaFin;
        }
        
        if ($tabla) {
            $where .= " AND h.tabla_afectada = ?";
            $params[] = $tabla;
        }
        
        if ($accion) {
            $where .= " AND h.accion = ?";
            $params[] = $accion;
        }
        
        if ($creadoPor) {
            $where .= " AND ua.usuario LIKE ?";
            $params[] = "%$creadoPor%";
        }
        
        // Aplicar filtros de búsqueda
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (h.tabla_afectada LIKE ? OR h.usuario_afectado LIKE ? OR h.accion LIKE ? OR h.campo_modificado LIKE ? OR h.valor_anterior LIKE ? OR h.valor_nuevo LIKE ? OR h.motivo LIKE ? OR ua.usuario LIKE ?)";
            $searchTerm = "%{$filtros['busqueda']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT h.*, ua.usuario AS modificado_por_nombre
                FROM historial_cambios h
                LEFT JOIN usuarios_admin ua ON h.modificado_por = ua.id
                $where
                ORDER BY h.fecha_modificacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene lista de áreas
     */
    public function getAreas() {
        global $AREAS_PREDEFINIDAS;
        return $AREAS_PREDEFINIDAS;
    }
    
    /**
     * Obtiene lista de cargos por área - CORREGIDO: Función mejorada
     */
    public function getPositionsByArea($area) {
        global $CARGOS_POR_AREA;
        return isset($CARGOS_POR_AREA[$area]) ? $CARGOS_POR_AREA[$area] : ['Otros'];
    }
    
    /**
     * Obtiene lista de cargos
     */
    public function getPositions() {
        global $CARGOS_POR_AREA;
        $positions = [];
        foreach ($CARGOS_POR_AREA as $cargos) {
            $positions = array_merge($positions, $cargos);
        }
        return array_unique($positions);
    }
    
    /**
     * Obtiene lista de empleados para el select
     */
    public function getEmployeesForSelect() {
        $stmt = $this->pdo->query("SELECT id, dni, nombres, apellidos, CONCAT(apellidos, ' ', nombres) AS nombre_completo FROM empleados WHERE nombres IS NOT NULL AND apellidos IS NOT NULL AND estado = 'activo' ORDER BY apellidos, nombres");
        return $stmt->fetchAll();
    }
    
    /**
     * Busca empleados por término (para el autocompletado)
     */
    public function searchEmployees($term) {
        $stmt = $this->pdo->prepare("SELECT id, dni, CONCAT(apellidos, ' ', nombres) AS nombre_completo 
                                    FROM empleados 
                                    WHERE (dni LIKE ? OR CONCAT(apellidos, ' ', nombres) LIKE ?)
                                    AND nombres IS NOT NULL 
                                    AND apellidos IS NOT NULL
                                    AND estado = 'activo'
                                    ORDER BY apellidos, nombres
                                    LIMIT 10");
        $stmt->execute(["%$term%", "%$term%"]);
        return $stmt->fetchAll();
    }
}

// Instanciar el generador de reportes ANTES de cualquier uso
$reportGenerator = new ReportGenerator($pdo);

// Procesar agregar empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_empleado']) && ($isSuperAdmin || $isSupervisor)) {
    $dni = $_POST['dni'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $inicio_contrato = $_POST['inicio_contrato'];
    $fin_contrato = $_POST['fin_contrato'];
    $tipo_personal = $_POST['tipo_personal'];
    
    // Verificar si el DNI ya existe
    $stmt = $pdo->prepare("SELECT id FROM empleados WHERE dni = ?");
    $stmt->execute([$dni]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Ya existe un empleado con el DNI $dni";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=trabajadores");
        exit;
    }
    
    // Campos de horario (solo para administrativos)
    $entrada_manana = ($tipo_personal === 'Administrativo') ? ($_POST['entrada_manana'] ?: '08:00:00') : null;
    $salida_manana = ($tipo_personal === 'Administrativo') ? ($_POST['salida_manana'] ?: '13:00:00') : null;
    $entrada_tarde = ($tipo_personal === 'Administrativo') ? ($_POST['entrada_tarde'] ?: '16:00:00') : null;
    $salida_tarde = ($tipo_personal === 'Administrativo') ? ($_POST['salida_tarde'] ?: '19:00:00') : null;
    
    // Procesar foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['foto']['tmp_name'], FOTO_DIR . $foto);
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO empleados (dni, nombres, apellidos, area, puesto, inicio_contrato, fin_contrato, tipo_personal, entrada_manana, salida_manana, entrada_tarde, salida_tarde, foto, estado, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)");
        $stmt->execute([$dni, $nombres, $apellidos, $area, $cargo, $inicio_contrato, $fin_contrato, $tipo_personal, $entrada_manana, $salida_manana, $entrada_tarde, $salida_tarde, $foto, $_SESSION['admin_id']]);
        
        // Registrar en historial
        registrarHistorial('empleados', $dni, 'INSERT', 'nuevo_empleado', null, "$apellidos $nombres", "Creación de nuevo empleado", $pdo);
        
        $_SESSION['success_message'] = "Empleado agregado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=trabajadores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al agregar empleado: " . $e->getMessage();
    }
}

// Procesar agregar administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_administrador']) && $isSuperAdmin) {
    $empleado_id = $_POST['empleado_id'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Ya existe un usuario con el nombre de usuario $usuario";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    }
    
    // Verificar si el empleado ya tiene una cuenta de administrador
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE id = ?");
    $stmt->execute([$empleado_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Este empleado ya tiene una cuenta de administrador";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    }
    
    // Verificar longitud de contraseña
    if (strlen($password) < 4) {
        $_SESSION['error_message'] = "Error: La contraseña debe tener al menos 4 caracteres";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    }
    
    // Obtener datos del empleado
    $stmt = $pdo->prepare("SELECT nombres, apellidos FROM empleados WHERE id = ?");
    $stmt->execute([$empleado_id]);
    $empleado = $stmt->fetch();
    
    if (!$empleado) {
        $_SESSION['error_message'] = "Error: Empleado no encontrado";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    }
    
    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios_admin (id, nombres, apellidos, usuario, password, rol, estado, creado_por) VALUES (?, ?, ?, ?, ?, ?, 'activo', ?)");
        $stmt->execute([$empleado_id, $empleado['nombres'], $empleado['apellidos'], $usuario, $password_hash, $rol, $_SESSION['admin_id']]);
        
        // Registrar en historial
        registrarHistorial('usuarios_admin', $usuario, 'INSERT', 'nuevo_admin', null, "$usuario ($rol)", "Creación de nuevo administrador", $pdo);
        
        $_SESSION['success_message'] = "Administrador agregado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al agregar administrador: " . $e->getMessage();
    }
}

// Procesar edición de empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_empleado']) && ($isSuperAdmin || $isSupervisor)) {
    $empleado_id = $_POST['empleado_id'];
    $dni = $_POST['dni'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $inicio_contrato = $_POST['inicio_contrato'];
    $fin_contrato = $_POST['fin_contrato'];
    $tipo_personal = $_POST['tipo_personal'];
    
    // Verificar si el DNI ya existe (excluyendo el empleado actual)
    $stmt = $pdo->prepare("SELECT id FROM empleados WHERE dni = ? AND id != ?");
    $stmt->execute([$dni, $empleado_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Ya existe otro empleado con el DNI $dni";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=trabajadores");
        exit;
    }
    
    // Campos de horario (solo para administrativos)
    $entrada_manana = ($tipo_personal === 'Administrativo') ? ($_POST['entrada_manana'] ?: '08:00:00') : null;
    $salida_manana = ($tipo_personal === 'Administrativo') ? ($_POST['salida_manana'] ?: '13:00:00') : null;
    $entrada_tarde = ($tipo_personal === 'Administrativo') ? ($_POST['entrada_tarde'] ?: '16:00:00') : null;
    $salida_tarde = ($tipo_personal === 'Administrativo') ? ($_POST['salida_tarde'] ?: '19:00:00') : null;
    
    // Obtener datos anteriores para el historial
    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
    $stmt->execute([$empleado_id]);
    $empleado_anterior = $stmt->fetch();
    
    // Procesar foto
    $foto = $empleado_anterior['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Eliminar foto anterior si existe
        if ($foto && file_exists(FOTO_DIR . $foto)) {
            unlink(FOTO_DIR . $foto);
        }
        
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['foto']['tmp_name'], FOTO_DIR . $foto);
    }
    
    try {
        $sql = "UPDATE empleados SET dni = ?, nombres = ?, apellidos = ?, area = ?, puesto = ?, inicio_contrato = ?, fin_contrato = ?, tipo_personal = ?, entrada_manana = ?, salida_manana = ?, entrada_tarde = ?, salida_tarde = ?, foto = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dni, $nombres, $apellidos, $area, $cargo, $inicio_contrato, $fin_contrato, $tipo_personal, $entrada_manana, $salida_manana, $entrada_tarde, $salida_tarde, $foto, $empleado_id]);
        
        // Registrar en historial
        $cambios = [];
        if ($empleado_anterior['dni'] != $dni) {
            $cambios[] = "dni: {$empleado_anterior['dni']} -> $dni";
        }
        if ($empleado_anterior['nombres'] != $nombres) {
            $cambios[] = "nombres: {$empleado_anterior['nombres']} -> $nombres";
        }
        if ($empleado_anterior['apellidos'] != $apellidos) {
            $cambios[] = "apellidos: {$empleado_anterior['apellidos']} -> $apellidos";
        }
        if ($empleado_anterior['area'] != $area) {
            $cambios[] = "area: {$empleado_anterior['area']} -> $area";
        }
        if ($empleado_anterior['puesto'] != $cargo) {
            $cambios[] = "puesto: {$empleado_anterior['puesto']} -> $cargo";
        }
        if ($empleado_anterior['inicio_contrato'] != $inicio_contrato) {
            $cambios[] = "inicio_contrato: {$empleado_anterior['inicio_contrato']} -> $inicio_contrato";
        }
        if ($empleado_anterior['fin_contrato'] != $fin_contrato) {
            $cambios[] = "fin_contrato: {$empleado_anterior['fin_contrato']} -> $fin_contrato";
        }
        if ($empleado_anterior['tipo_personal'] != $tipo_personal) {
            $cambios[] = "tipo_personal: {$empleado_anterior['tipo_personal']} -> $tipo_personal";
        }
        if ($foto != $empleado_anterior['foto']) {
            $cambios[] = "foto: [actualizada]";
        }
        
        if (!empty($cambios)) {
            registrarHistorial('empleados', $dni, 'UPDATE', 'datos_empleado', $empleado_anterior['dni'], $dni, "Cambios: " . implode(', ', $cambios), $pdo);
        }
        
        $_SESSION['success_message'] = "Empleado actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=trabajadores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar empleado: " . $e->getMessage();
    }
}

// Procesar edición de administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_administrador']) && $isSuperAdmin) {
    $admin_id = $_POST['admin_id'];
    $usuario = $_POST['usuario'];
    $rol = $_POST['rol'];
    $password = $_POST['password'];
    
    // Verificar si el usuario ya existe (excluyendo el actual)
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ? AND id != ?");
    $stmt->execute([$usuario, $admin_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Ya existe otro usuario con el nombre de usuario $usuario";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    }
    
    // Obtener datos anteriores para el historial
    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_anterior = $stmt->fetch();
    
    try {
        if (!empty($password)) {
            // Verificar longitud de contraseña
            if (strlen($password) < 4) {
                $_SESSION['error_message'] = "Error: La contraseña debe tener al menos 4 caracteres";
                header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
                exit;
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ?, password = ?, rol = ? WHERE id = ?");
            $stmt->execute([$usuario, $password_hash, $rol, $admin_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ?, rol = ? WHERE id = ?");
            $stmt->execute([$usuario, $rol, $admin_id]);
        }
        
        // Registrar en historial
        $cambios = [];
        if ($admin_anterior['usuario'] != $usuario) {
            $cambios[] = "usuario: {$admin_anterior['usuario']} -> $usuario";
        }
        if ($admin_anterior['rol'] != $rol) {
            $cambios[] = "rol: {$admin_anterior['rol']} -> $rol";
        }
        if (!empty($password)) {
            $cambios[] = "password: [actualizada]";
        }
        
        if (!empty($cambios)) {
            registrarHistorial('usuarios_admin', $usuario, 'UPDATE', 'datos_admin', $admin_anterior['usuario'], $usuario, "Cambios: " . implode(', ', $cambios), $pdo);
        }
        
        $_SESSION['success_message'] = "Administrador actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar administrador: " . $e->getMessage();
    }
}

// Procesar cambio de estado de empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado_empleado']) && ($isSuperAdmin || $isSupervisor)) {
    $empleado_id = $_POST['empleado_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        // Obtener datos anteriores
        $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
        $stmt->execute([$empleado_id]);
        $empleado_anterior = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE empleados SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $empleado_id]);
        
        // Registrar en historial
        registrarHistorial('empleados', $empleado_anterior['dni'], 'UPDATE', 'estado', $empleado_anterior['estado'], $nuevo_estado, "Cambio de estado de empleado", $pdo);
        
        $_SESSION['success_message'] = "Estado del empleado actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=trabajadores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al cambiar estado: " . $e->getMessage();
    }
}

// Procesar cambio de estado de administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado_admin']) && $isSuperAdmin) {
    $admin_id = $_POST['admin_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        // Obtener datos anteriores
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin_anterior = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE usuarios_admin SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $admin_id]);
        
        // Registrar en historial
        registrarHistorial('usuarios_admin', $admin_anterior['usuario'], 'UPDATE', 'estado', $admin_anterior['estado'], $nuevo_estado, "Cambio de estado de administrador", $pdo);
        
        $_SESSION['success_message'] = "Estado del administrador actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al cambiar estado: " . $e->getMessage();
    }
}

// Procesar ingreso manual de asistencia (solo para superadmin y supervisor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_asistencia']) && ($isSuperAdmin || $isSupervisor)) {
    $empleado_id = $_POST['empleado_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO registros_asistencia (empleado_id, fecha, hora, registrado_por, tipo_registro) VALUES (?, ?, ?, ?, 'MANUAL')");
        $stmt->execute([$empleado_id, $fecha, $hora, $_SESSION['admin_id']]);
        
        // Registrar en historial
        $stmtEmpleado = $pdo->prepare("SELECT CONCAT(apellidos, ' ', nombres) as nombre FROM empleados WHERE id = ?");
        $stmtEmpleado->execute([$empleado_id]);
        $empleado = $stmtEmpleado->fetch();
        registrarHistorial('registros_asistencia', $empleado['nombre'], 'INSERT', 'asistencia_manual', null, "$fecha $hora", "Registro manual de asistencia", $pdo);
        
        $_SESSION['success_message'] = "Asistencia registrada correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=daily");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al registrar asistencia: " . $e->getMessage();
    }
}

// Procesar registro manual de permiso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_permiso_manual']) && ($isSuperAdmin || $isSupervisor)) {
    $empleado_id = $_POST['empleado_id'];
    $tipo_permiso = $_POST['tipo_permiso'];
    $motivo = $_POST['motivo'];
    $fecha_permiso = $_POST['fecha_permiso'];
    $hora_salida = $_POST['hora_salida'];
    $hora_retorno = $_POST['hora_retorno'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO permisos (empleado_id, tipo_permiso, motivo, fecha_permiso, hora_salida, hora_retorno, hora_registro, registrado_por, tipo_registro) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, 'MANUAL')");
        $stmt->execute([$empleado_id, $tipo_permiso, $motivo, $fecha_permiso, $hora_salida, $hora_retorno, $_SESSION['admin_id']]);
        
        // Registrar en historial
        $stmtEmpleado = $pdo->prepare("SELECT CONCAT(apellidos, ' ', nombres) as nombre FROM empleados WHERE id = ?");
        $stmtEmpleado->execute([$empleado_id]);
        $empleado = $stmtEmpleado->fetch();
        registrarHistorial('permisos', $empleado['nombre'], 'INSERT', 'permiso_manual', null, "$tipo_permiso - $fecha_permiso", $motivo, $pdo);
        
        $_SESSION['success_message'] = "Permiso registrado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=permission");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al registrar permiso: " . $e->getMessage();
    }
}

// Procesar actualización de permiso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_permiso']) && ($isSuperAdmin || $isSupervisor)) {
    $permiso_id = $_POST['permiso_id'];
    $tipo_permiso = $_POST['tipo_permiso'];
    $motivo = $_POST['motivo'];
    $fecha_permiso = $_POST['fecha_permiso'];
    $hora_salida = $_POST['hora_salida'];
    $hora_retorno = $_POST['hora_retorno'];
    
    try {
        // Obtener datos anteriores para el historial
        $stmt = $pdo->prepare("SELECT * FROM permisos WHERE id = ?");
        $stmt->execute([$permiso_id]);
        $permiso_anterior = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE permisos SET tipo_permiso = ?, motivo = ?, fecha_permiso = ?, hora_salida = ?, hora_retorno = ? WHERE id = ?");
        $stmt->execute([$tipo_permiso, $motivo, $fecha_permiso, $hora_salida, $hora_retorno, $permiso_id]);
        
        // Registrar en historial
        $cambios = [];
        if ($permiso_anterior['tipo_permiso'] != $tipo_permiso) {
            $cambios[] = "tipo_permiso: {$permiso_anterior['tipo_permiso']} -> $tipo_permiso";
        }
        if ($permiso_anterior['fecha_permiso'] != $fecha_permiso) {
            $cambios[] = "fecha_permiso: {$permiso_anterior['fecha_permiso']} -> $fecha_permiso";
        }
        if ($permiso_anterior['hora_salida'] != $hora_salida) {
            $cambios[] = "hora_salida: {$permiso_anterior['hora_salida']} -> $hora_salida";
        }
        if ($permiso_anterior['hora_retorno'] != $hora_retorno) {
            $cambios[] = "hora_retorno: {$permiso_anterior['hora_retorno']} -> $hora_retorno";
        }
        
        if (!empty($cambios)) {
            registrarHistorial('permisos', $permiso_anterior['empleado_id'], 'UPDATE', 'datos_permiso', $permiso_anterior['tipo_permiso'], $tipo_permiso, "Cambios: " . implode(', ', $cambios), $pdo);
        }
        
        $_SESSION['success_message'] = "Permiso actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=permission");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar permiso: " . $e->getMessage();
    }
}

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_configuracion']) && $isSuperAdmin) {
    $minutos_tolerancia = $_POST['minutos_tolerancia'];
    
    try {
        $valor_anterior = getConfig('minutos_tolerancia', $pdo);
        updateConfig('minutos_tolerancia', $minutos_tolerancia, $pdo);
        
        // Registrar en historial
        registrarHistorial('configuracion_sistema', 'Sistema', 'UPDATE', 'minutos_tolerancia', $valor_anterior, $minutos_tolerancia, "Actualización de minutos de tolerancia", $pdo);
        
        $_SESSION['success_message'] = "Configuración actualizada correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=config");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar configuración: " . $e->getMessage();
    }
}

// Procesar actualización de perfil de supervisor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil_supervisor']) && $isSupervisor) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validaciones
    if (empty($usuario)) {
        $_SESSION['error_message'] = "El nombre de usuario no puede estar vacío";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=perfil");
        exit;
    }
    
    if (!empty($password)) {
        if (strlen($password) < 4) {
            $_SESSION['error_message'] = "La contraseña debe tener al menos 4 caracteres";
            header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=perfil");
            exit;
        }
        
        if ($password !== $confirmar_password) {
            $_SESSION['error_message'] = "Las contraseñas no coinciden";
            header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=perfil");
            exit;
        }
    }
    
    // Verificar si el usuario ya existe (excluyendo el actual)
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ? AND id != ?");
    $stmt->execute([$usuario, $_SESSION['admin_id']]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "Error: Ya existe otro usuario con el nombre de usuario $usuario";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=perfil");
        exit;
    }
    
    try {
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ?, password = ? WHERE id = ?");
            $stmt->execute([$usuario, $password_hash, $_SESSION['admin_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ? WHERE id = ?");
            $stmt->execute([$usuario, $_SESSION['admin_id']]);
        }
        
        // Actualizar la sesión
        $_SESSION['admin_username'] = $usuario;
        
        // Registrar en historial
        registrarHistorial('usuarios_admin', $usuario, 'UPDATE', 'perfil_supervisor', $_SESSION['admin_username'], $usuario, "Actualización de perfil de supervisor", $pdo);
        
        $_SESSION['success_message'] = "Perfil actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=dashboard&report_type=perfil");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar perfil: " . $e->getMessage();
    }
}

// NUEVA FUNCIÓN: Exportar a Excel CON DISEÑO PROFESIONAL MEJORADO
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
    
    // Abrir output
    $output = fopen("php://output", "w");
    
    // Escribir encabezado corporativo MEJORADO
    fwrite($output, "UNIVERSIDAD ROOSEVELT\t\t\t\t\t\t\t\n");
    fwrite($output, "Sistema de Control de Asistencia\t\t\t\t\t\t\t\n");
    fwrite($output, "$reportTitle\t\t\t\t\t\t\t\n");
    fwrite($output, "\t\t\t\t\t\t\t\n");
    
    // Información de exportación
    fwrite($output, "Exportado por: " . $_SESSION['admin_username'] . "\t\t\t\t\t\t\t\n");
    fwrite($output, "Fecha de exportación: " . date('d/m/Y H:i:s') . "\t\t\t\t\t\t\t\n");
    fwrite($output, "Período del reporte: " . date('d/m/Y', strtotime($fechaInicio)) . " - " . date('d/m/Y', strtotime($fechaFin)) . "\t\t\t\t\t\t\t\n");
    fwrite($output, "\t\t\t\t\t\t\t\n");
    
    // Información de filtros aplicados - MEJORADO
    $filtrosAplicados = [];
    if (!empty($filterArea)) {
        $filtrosAplicados[] = "Área: $filterArea";
    }
    if (!empty($filterCargo)) {
        $filtrosAplicados[] = "Cargo: $filterCargo";
    }
    if (!empty($filterEstado)) {
        $filtrosAplicados[] = "Estado: $filterEstado";
    }
    if (!empty($filterRol)) {
        $filtrosAplicados[] = "Rol: $filterRol";
    }
    if (!empty($filterTurno)) {
        $filtrosAplicados[] = "Turno: $filterTurno";
    }
    if (!empty($filterTipoPersonal)) {
        $filtrosAplicados[] = "Tipo Personal: $filterTipoPersonal";
    }
    if (!empty($searchTerm)) {
        $filtrosAplicados[] = "Búsqueda: $searchTerm";
    }
    
    if (!empty($filtrosAplicados)) {
        fwrite($output, "FILTROS APLICADOS:\t\t\t\t\t\t\t\n");
        foreach ($filtrosAplicados as $filtro) {
            fwrite($output, "• $filtro\t\t\t\t\t\t\t\n");
        }
        fwrite($output, "\t\t\t\t\t\t\t\n");
    }
    
    // Escribir headers según el tipo de reporte con numeración - MEJORADO
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
    
    fputcsv($output, $headers, "\t");
    
    // Escribir datos con numeración - MEJORADO
    $contador = 1;
    foreach ($allData as $row) {
        if (isset($row['es_total']) && $row['es_total']) {
            // Escribir fila de totales con formato mejorado
            switch ($reportType) {
                case 'no_asistencia':
                    fputcsv($output, ['', '', '', '', '', '', 'TOTALES:', 'MAÑANA: ' . $row['total_faltas_manana'], 'TARDE: ' . $row['total_faltas_tarde'], 'TOTAL: ' . $row['total_general']], "\t");
                    break;
                case 'tardiness':
                    fputcsv($output, ['', '', '', '', '', '', 'TOTALES:', '', $row['total_tardanza_manana'], '', '', $row['total_tardanza_tarde'], 'TOTAL: ' . $row['total_tardanza']], "\t");
                    break;
                case 'permission':
                    // Total general para permisos
                    $totalGeneral = 0;
                    $totalesTexto = [];
                    foreach ($permissionTotals as $total) {
                        $totalesTexto[] = $total['tipo_permiso'] . ': ' . $total['total'];
                        $totalGeneral += $total['total'];
                    }
                    $totalesTexto[] = 'TOTAL GENERAL: ' . $totalGeneral;
                    fputcsv($output, ['', '', '', '', '', '', 'TOTALES:', implode(' | ', $totalesTexto)], "\t");
                    break;
            }
            continue;
        }
        
        switch ($reportType) {
            case 'tardiness':
                // Combinar tardanzas de mañana y tarde
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
                foreach ($tardanzasCombinadas as $fecha => $tardanzas) {
                    fputcsv($output, [
                        $contador++,
                        $row['dni'],
                        $row['nombre_completo'],
                        $row['area'],
                        $row['puesto'],
                        $fecha,
                        $tardanzas['manana'] ? $tardanzas['manana']['hora_entrada'] : '-',
                        $tardanzas['manana'] ? $tardanzas['manana']['hora_marcada'] : '-',
                        $tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-',
                        $tardanzas['tarde'] ? $tardanzas['tarde']['hora_entrada'] : '-',
                        $tardanzas['tarde'] ? $tardanzas['tarde']['hora_marcada'] : '-',
                        $tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-'
                    ], "\t");
                }
                break;
                
            case 'permission':
                fputcsv($output, [
                    $contador++,
                    $row['dni'],
                    $row['nombre_completo'],
                    $row['area'],
                    $row['puesto'],
                    $row['fecha_permiso'],
                    $row['tipo_permiso'],
                    $row['motivo'],
                    $row['hora_salida'],
                    $row['hora_retorno'],
                    $row['hora_registro'],
                    $row['registrado_por']
                ], "\t");
                break;
                
            case 'no_asistencia':
                // Mostrar faltas por turno
                if (!empty($row['faltas_manana'])) {
                    fputcsv($output, [
                        $contador++,
                        $row['dni'],
                        $row['nombre_completo'],
                        $row['area'],
                        $row['puesto'],
                        'MAÑANA',
                        $row['total_faltas_manana'],
                        implode(', ', array_slice($row['faltas_manana'], 0, 10)) . (count($row['faltas_manana']) > 10 ? '...' : '')
                    ], "\t");
                }
                
                if (!empty($row['faltas_tarde'])) {
                    fputcsv($output, [
                        $contador++,
                        $row['dni'],
                        $row['nombre_completo'],
                        $row['area'],
                        $row['puesto'],
                        'TARDE',
                        $row['total_faltas_tarde'],
                        implode(', ', array_slice($row['faltas_tarde'], 0, 10)) . (count($row['faltas_tarde']) > 10 ? '...' : '')
                    ], "\t");
                }
                break;
                
            case 'trabajadores':
                fputcsv($output, [
                    $contador++,
                    $row['dni'],
                    $row['apellidos'],
                    $row['nombres'],
                    $row['area'],
                    $row['puesto'],
                    $row['tipo_personal'],
                    $row['estado'],
                    $row['inicio_contrato'],
                    $row['fin_contrato'],
                    $row['creado_por_nombre']
                ], "\t");
                break;
                
            case 'administradores':
                fputcsv($output, [
                    $contador++,
                    $row['apellidos'],
                    $row['nombres'],
                    $row['usuario'],
                    $row['rol'],
                    $row['estado'],
                    $row['fecha_creacion'],
                    $row['creado_por_nombre']
                ], "\t");
                break;
                
            case 'history':
                fputcsv($output, [
                    $contador++,
                    $row['tabla_afectada'],
                    $row['usuario_afectado'],
                    $row['accion'],
                    $row['campo_modificado'],
                    $row['valor_anterior'],
                    $row['valor_nuevo'],
                    $row['motivo'],
                    $row['modificado_por_nombre'],
                    $row['fecha_modificacion']
                ], "\t");
                break;
                
            default: // daily
                fputcsv($output, [
                    $contador++,
                    $row['fecha'],
                    $row['dni'],
                    $row['nombre_completo'],
                    $row['area'],
                    $row['puesto'],
                    !empty($row['registros_entrada_manana']) ? min($row['registros_entrada_manana']) : '-',
                    !empty($row['registros_salida_manana']) ? min($row['registros_salida_manana']) : '-',
                    !empty($row['registros_entrada_tarde']) ? min($row['registros_entrada_tarde']) : '-',
                    !empty($row['registros_salida_tarde']) ? min($row['registros_salida_tarde']) : '-',
                    !empty($row['todos_registros']) ? implode(', ', $row['todos_registros']) : '-',
                    !empty($row['todos_registradores']) ? implode(', ', $row['todos_registradores']) : '-'
                ], "\t");
                break;
        }
    }
    
    // Escribir pie de página MEJORADO
    fwrite($output, "\t\t\t\t\t\t\t\n");
    fwrite($output, "------------------------------------------------------------------------------------------------------------------------------------\t\t\t\t\t\t\t\n");
    fwrite($output, "Documento generado automáticamente por el Sistema de Control de Asistencia - Universidad Roosevelt\t\t\t\t\t\t\t\n");
    fwrite($output, "Fecha de generación: " . date('d/m/Y H:i:s') . "\t\t\t\t\t\t\t\n");
    
    fclose($output);
    exit;
}

// NUEVA FUNCIÓN: Aplicar filtros en el servidor para TODOS los datos
function aplicarFiltrosServidor($data, $filtros) {
    if (empty($filtros) || empty($data)) {
        return $data;
    }
    
    return array_filter($data, function($row) use ($filtros) {
        $pasaFiltro = true;
        
        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                $valorFila = '';
                
                // Obtener el valor de la fila según el campo
                switch ($campo) {
                    case 'area':
                        $valorFila = $row['area'] ?? '';
                        break;
                    case 'cargo':
                        $valorFila = $row['puesto'] ?? '';
                        break;
                    case 'estado':
                        $valorFila = $row['estado'] ?? '';
                        break;
                    case 'rol':
                        $valorFila = $row['rol'] ?? '';
                        break;
                    case 'turno':
                        $valorFila = $row['turno'] ?? '';
                        break;
                    case 'tipo_personal':
                        $valorFila = $row['tipo_personal'] ?? '';
                        break;
                    case 'busqueda':
                        // Buscar en todos los campos de texto
                        $valorFila = strtolower(implode(' ', array_filter($row, function($v) {
                            return is_string($v) && !empty($v);
                        })));
                        break;
                }
                
                if ($campo === 'busqueda') {
                    if (strpos($valorFila, strtolower($valor)) === false) {
                        $pasaFiltro = false;
                        break;
                    }
                } else {
                    if ($valorFila !== $valor) {
                        $pasaFiltro = false;
                        break;
                    }
                }
            }
        }
        
        return $pasaFiltro;
    });
}

// Función auxiliar para convertir minutos a tiempo
function minutesToTime($minutes) {
    $horas = floor($minutes / 60);
    $minutos = $minutes % 60;
    return sprintf("%02d:%02d", $horas, $minutos);
}

// Determinar qué sección mostrar - AHORA TODO ES DASHBOARD
$section = 'dashboard';
$reportType = $_GET['report_type'] ?? 'daily';

// Configurar fechas por defecto para reportes - SIEMPRE FECHA ACTUAL
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener minutos de tolerancia
$minutosTolerancia = getConfig('minutos_tolerancia', $pdo) ?: 5;

// Obtener filtros actuales - AHORA SE APLICAN EN EL SERVIDOR
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

// Inicializar variables para evitar warnings
$allData = [];
$permissionTotals = [];
$historialFechaInicio = $_GET['historial_fecha_inicio'] ?? date('Y-m-01');
$historialFechaFin = $_GET['historial_fecha_fin'] ?? date('Y-m-d');
$historialTabla = $_GET['historial_tabla'] ?? '';
$historialAccion = $_GET['historial_accion'] ?? '';
$historialCreadoPor = $_GET['historial_creado_por'] ?? '';

switch ($reportType) {
    case 'tardiness':
        $allData = $reportGenerator->getTardinessReport($fechaInicio, $fechaFin, $filtros);
        $reportTitle = "Reporte de Tardanzas";
        break;
        
    case 'permission':
        $allData = $reportGenerator->getPermissionReport($fechaInicio, $fechaFin, $filtros);
        $permissionTotals = $reportGenerator->getPermissionTotals($fechaInicio, $fechaFin, $filtros);
        $reportTitle = "Reporte de Permisos";
        break;
        
    case 'no_asistencia':
        $allData = $reportGenerator->getEmployeesWithoutAttendance($fechaInicio, $fechaFin, $filtros);
        $reportTitle = "Empleados Sin Asistencia";
        break;
        
    case 'trabajadores':
        $allData = $reportGenerator->getEmployees($filtros);
        $reportTitle = "Lista de Trabajadores";
        break;
        
    case 'administradores':
        $allData = $reportGenerator->getAdministradores($filtros);
        $reportTitle = "Lista de Administradores";
        break;
        
    case 'history':
        $allData = $reportGenerator->getHistorial($historialFechaInicio, $historialFechaFin, $historialTabla, $historialAccion, $historialCreadoPor, $filtros);
        $reportTitle = "Historial de Cambios";
        break;
        
    case 'config':
        $reportTitle = "Configuración del Sistema";
        break;
        
    case 'perfil':
        $reportTitle = "Configurar Perfil";
        break;
        
    default: // daily
        $allData = $reportGenerator->getDailyReport($fechaInicio, $fechaFin, $filtros);
        $reportTitle = "Reporte Diario de Asistencia";
        break;
}

// Configurar paginación para todos los reportes
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
// Para "Sin Asistencia" usar 40 elementos por página, para otros 20
$itemsPerPage = ($reportType === 'no_asistencia') ? 40 : 20;
$totalItems = is_array($allData) ? count($allData) : 0;
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;
$paginatedData = is_array($allData) ? array_slice($allData, $offset, $itemsPerPage) : [];

// Obtener listas para filtros
$areas = $reportGenerator->getAreas();
$positions = $reportGenerator->getPositions();
$employeesList = $reportGenerator->getEmployeesForSelect();

// Procesar búsqueda de empleados para el modal (AJAX)
if (isset($_GET['search_employees'])) {
    $term = $_GET['term'] ?? '';
    $results = $reportGenerator->searchEmployees($term);
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// Obtener datos de permiso para edición
if (isset($_GET['get_permiso'])) {
    $id = $_GET['get_permiso'];
    $permiso = $reportGenerator->getPermissionById($id);
    header('Content-Type: application/json');
    echo json_encode($permiso);
    exit;
}

// Obtener datos de empleado para edición
if (isset($_GET['get_empleado'])) {
    $id = $_GET['get_empleado'];
    $empleado = $reportGenerator->getEmployeeById($id);
    header('Content-Type: application/json');
    echo json_encode($empleado);
    exit;
}

// Obtener datos de administrador para edición
if (isset($_GET['get_admin'])) {
    $id = $_GET['get_admin'];
    $admin = $reportGenerator->getAdminById($id);
    header('Content-Type: application/json');
    echo json_encode($admin);
    exit;
}

// Obtener cargos por área (AJAX) - CORREGIDO: Función mejorada
if (isset($_GET['get_cargos_by_area'])) {
    $area = $_GET['area'] ?? '';
    $cargos = $reportGenerator->getPositionsByArea($area);
    header('Content-Type: application/json');
    echo json_encode($cargos);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        :root {
            --primary: #8A1538;
            --primary-light: #A42D52;
            --primary-dark: #6D0E2D;
            --secondary: #D4AF37;
            --secondary-light: #E8C766;
            --secondary-dark: #BF9428;
            --success: #28a745;
            --error: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #FFFFFF;
            --gray: #6c757d;
            --text-dark: #212529;
            --text-light: #6c757d;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
            --transition: all 0.3s ease;
            --border-radius: 0.375rem;
            --border-radius-lg: 0.5rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .sidebar {
            width: 200px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            position: fixed;
            height: 100%;
            padding: 1.5rem 0;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-header h3 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary);
            transform: translateX(5px);
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
            transition: var(--transition);
        }
        
        .menu-item:hover i {
            transform: scale(1.1);
        }
        
        .main-content {
            margin-left: 200px;
            padding: 1rem;
            transition: var(--transition);
            min-height: 100vh;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.25rem 1.5rem;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        
        .header:hover {
            box-shadow: var(--shadow-md);
        }
        
        .header h1 {
            color: var(--primary);
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .user-info .btn-logout {
            background: linear-gradient(135deg, var(--error) 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.85rem;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
        }
        
        .user-info .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--light);
            font-weight: 600;
        }
        
        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--dark);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--error) 0%, #e74c3c 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ffd351 100%);
            color: var(--dark);
        }
        
        .btn-info {
            background: linear-gradient(135deg, var(--info) 0%, #5bc0de 100%);
        }
        
        .btn-light {
            background: var(--light);
            color: var(--dark);
            border: 1px solid #dee2e6;
        }
        
        .btn-excel {
            background: linear-gradient(135deg, #1d6f42 0%, #28a745 100%);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-start;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        
        .filters-container {
            background: var(--white);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            gap: 0.75rem;
            align-items: end;
        }
        
        .filter-group .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(138, 21, 56, 0.25);
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            max-height: 600px;
            overflow-y: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            font-size: 0.85rem;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            font-size: 0.8rem;
            z-index: 10;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: rgba(138, 21, 56, 0.05);
            transition: var(--transition);
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }
        
        .badge-success {
            background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
            color: white;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ffd351 100%);
            color: var(--dark);
        }
        
        .badge-danger {
            background: linear-gradient(135deg, var(--error) 0%, #e74c3c 100%);
            color: white;
        }
        
        .badge-info {
            background: linear-gradient(135deg, var(--info) 0%, #5bc0de 100%);
            color: white;
        }
        
        .badge-secondary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
            color: var(--dark);
        }
        
        .badge-light {
            background: var(--light);
            color: var(--dark);
            border: 1px solid #dee2e6;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 0.5rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.6rem 0.9rem;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--primary);
            font-size: 0.85rem;
            transition: var(--transition);
        }
        
        .pagination a:hover {
            background-color: #f0f0f0;
            transform: translateY(-1px);
        }
        
        .pagination .active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-color: var(--primary);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            width: 95%;
            max-width: 1200px;
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-lg {
            max-width: 95%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }
        
        .close {
            color: #aaa;
            font-size: 1.75rem;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .close:hover {
            color: #333;
            transform: scale(1.1);
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 2px solid var(--light);
        }
        
        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 45px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 41px;
            padding-left: 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }
        
        /* Estilos para mensajes flash */
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            color: white;
            z-index: 1100;
            box-shadow: var(--shadow-lg);
            animation: slideInRight 0.3s, slideOutRight 0.5s 2.5s forwards;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        .flash-success {
            background: linear-gradient(135deg, var(--success) 0%, #34ce57 100%);
        }
        
        .flash-error {
            background: linear-gradient(135deg, var(--error) 0%, #e74c3c 100%);
        }
        
        /* Estilos para registros verticales */
        .registros-vertical {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .registro-item {
            padding: 0.35rem 0.5rem;
            background-color: #f0f0f0;
            border-radius: 4px;
            font-size: 0.8rem;
            transition: var(--transition);
        }
        
        .registro-item:hover {
            background-color: #e0e0e0;
            transform: translateX(2px);
        }
        
        /* Estilos para filas de totales */
        .total-row {
            background-color: #e8f5e9 !important;
            font-weight: bold;
        }
        
        .total-area-row {
            background-color: #e3f2fd !important;
            font-weight: bold;
        }
        
        .total-general-row {
            background-color: #fff8e1 !important;
            font-weight: bold;
        }
        
        /* Estilos mejorados para reportes profesionales */
        .report-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-left: 4px solid var(--primary);
        }
        
        .report-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
        }
        
        .report-table {
            font-size: 0.85rem;
        }
        
        .report-table th {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .report-table tr:hover {
            background-color: rgba(138, 21, 56, 0.08);
        }
        
        .tardanza-cell {
            font-weight: 600;
            color: #d32f2f;
        }
        
        .permiso-cell {
            background-color: rgba(33, 150, 243, 0.1);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .area-cell {
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-weight: 500;
        }
        
        /* Estilos para porcentajes de asistencia */
        .porcentaje-cell {
            font-weight: 600;
            text-align: center;
        }
        
        .porcentaje-alto {
            color: #4CAF50;
        }
        
        .porcentaje-medio {
            color: #FF9800;
        }
        
        .porcentaje-bajo {
            color: #F44336;
        }
        
        /* Estilos para imagen ampliada - MEJORADO */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
        }
        
        .image-modal-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            animation: zoom 0.6s;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        
        @keyframes zoom {
            from {transform: scale(0.8); opacity: 0;}
            to {transform: scale(1); opacity: 1;}
        }
        
        .close-image {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
            z-index: 1003;
        }
        
        .close-image:hover {
            color: #bbb;
            transform: scale(1.1);
        }
        
        .employee-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .employee-photo:hover {
            transform: scale(1.1);
        }
        
        .horario-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--info);
        }
        
        .horario-fields.hidden {
            display: none;
        }
        
        /* NUEVO: Estilos para formularios horizontales en modales de empleados */
        .empleado-form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .empleado-form-container .form-group:nth-child(odd) {
            grid-column: 1;
        }
        
        .empleado-form-container .form-group:nth-child(even) {
            grid-column: 2;
        }
        
        .empleado-form-container .form-group:nth-child(9),
        .empleado-form-container .form-group:nth-child(10) {
            grid-column: 1 / -1;
        }
        
        .photo-preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border: 2px dashed #dee2e6;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }
        
        .photo-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header span, .menu-item span {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 0.75rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .empleado-form-container {
                grid-template-columns: 1fr;
            }
            
            .empleado-form-container .form-group:nth-child(odd),
            .empleado-form-container .form-group:nth-child(even) {
                grid-column: 1;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .action-buttons {
                justify-content: flex-start;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
            
            .form-horizontal {
                grid-template-columns: 1fr;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-group {
                flex-direction: column;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .horario-fields {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }
            
            .modal-content {
                margin: 15% auto;
                padding: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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
        
        <!-- Action Buttons según el tipo de reporte -->
        <div class="action-buttons">
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
            
            <!-- Botón para limpiar filtros -->
            <a href="?section=dashboard&report_type=<?= $reportType ?>" class="btn btn-light">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </div>
        
        <!-- Filtros para todos los reportes -->
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
                                    <?php if (!in_array($reportType, ['areas'])): ?>
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
                                $showCounter = !in_array($reportType, ['areas']);
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
                                                <!-- Mostrar faltas por turno -->
                                                <?php if (!empty($row['faltas_manana'])): ?>
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
                                                    <?php if ($showCounter): ?>
                                                        <tr><td style="text-align: center;"><?= $contador++ ?></td>
                                                    <?php else: ?>
                                                        <tr>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($row['faltas_tarde'])): ?>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">TARDE</span>
                                                    </td>
                                                    <td class="text-center"><?= $row['total_faltas_tarde'] ?></td>
                                                    <td><?= implode(', ', array_slice($row['faltas_tarde'], 0, 5)) . (count($row['faltas_tarde']) > 5 ? '...' : '') ?></td>
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
                                    <small class="text-muted">Formatos aceptados: JPG, PNG, GIF (Máx. 2MB)</small>
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