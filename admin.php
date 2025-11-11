<?php
// Configuración de tiempo de sesión 
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
            
            // CORRECCIÓN: Aplicar filtro de turno ANTES de agregar al resultado
            $mostrarEmpleado = false;
            $mostrarManana = false;
            $mostrarTarde = false;
            
            if (!empty($filtros['turno'])) {
                if ($filtros['turno'] === 'MAÑANA' && !empty($faltasManana)) {
                    $mostrarEmpleado = true;
                    $mostrarManana = true;
                } elseif ($filtros['turno'] === 'TARDE' && !empty($faltasTarde)) {
                    $mostrarEmpleado = true;
                    $mostrarTarde = true;
                }
            } else {
                // Sin filtro de turno, mostrar ambos si tienen faltas
                $mostrarEmpleado = (!empty($faltasManana) || !empty($faltasTarde));
                $mostrarManana = !empty($faltasManana);
                $mostrarTarde = !empty($faltasTarde);
            }
            
            if ($mostrarEmpleado) {
                $empleadoData = [
                    'id' => $empleado['id'],
                    'dni' => $empleado['dni'],
                    'nombre_completo' => $empleado['nombre_completo'],
                    'area' => $empleado['area'],
                    'puesto' => $empleado['puesto'],
                    'tipo_personal' => $empleado['tipo_personal'],
                    'faltas_manana' => $mostrarManana ? $faltasManana : [],
                    'faltas_tarde' => $mostrarTarde ? $faltasTarde : [],
                    'total_faltas_manana' => $mostrarManana ? count($faltasManana) : 0,
                    'total_faltas_tarde' => $mostrarTarde ? count($faltasTarde) : 0
                ];
                
                $result[] = $empleadoData;
                
                $totalFaltasManana += $mostrarManana ? count($faltasManana) : 0;
                $totalFaltasTarde += $mostrarTarde ? count($faltasTarde) : 0;
            }
        }
        
        // Agregar fila de totales
        if (!empty($result)) {
            $result[] = [
                'es_total' => true,
                'total_faltas_manana' => $totalFaltasManana,
                'total_faltas_tarde' => $totalFaltasTarde,
                'total_general' => $totalFaltasManana + $totalFaltasTarde
            ];
        }
        
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
    
    // CORRECCIÓN: Procesar foto - Si no se sube imagen, usar default.png
    $foto = 'default.png'; // Valor por defecto
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
    
    // CORRECCIÓN: Procesar foto - Si no se sube nueva imagen, mantener la actual
    $foto = $empleado_anterior['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Eliminar foto anterior si existe y no es default.png
        if ($foto && $foto !== 'default.png' && file_exists(FOTO_DIR . $foto)) {
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
// =============================================
// Exportar a Excel CON DISEÑO PROFESIONAL MEJORADO
// =============================================
include "config/excel.php";


//Aplicar filtros en el servidor para TODOS los datos
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



// =============================================
// HTML DEL ADMIN
// =============================================
include "public/admin.php";
?>
