<?php
// =============================================
// CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_asistencia');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_TIMEZONE', 'America/Lima');
define('UNIVERSIDAD_NOMBRE', 'UNIVERSIDAD FRANKLIN ROOSEVELT');
define('FOTO_DIR', 'fotos_empleados/');

// Definir áreas y cargos preestablecidos
$AREAS_PREDEFINIDAS = [
    'Admisión',
    'Administración',
    'Archivo Central',
    'Asesoría Legal - Secretaría General',
    'Asuntos académicos',
    'Biblioteca',
    'Bienes patrimoniales',
    'Bienestar Universitario',
    'Calidad Educativa y Acreditación',
    'Colocación Laboral',
    'Contabilidad y Finanzas',
    'Cosmiatría',
    'Decanatura de CCSS',
    'Decanatura de Ciencias empresariales y Derecho',
    'Defensoría Universitaria',
    'Docentes',
    'E.P. Administración',
    'E.P. Derecho',
    'E.P. Enfermería',
    'E.P. Estomatología',
    'E.P. Farmacia y Bioquímica',
    'E.P. Medicina',
    'E.P. Obstetricia',
    'E.P. Psicología',
    'Escuela de Postgrado',
    'Gerencia',
    'Informes',
    'Instituto de Investigación',
    'Laboratorio',
    'Limpieza',
    'Logística',
    'Maintenance',
    'Marketing',
    'Mesa de partes',
    'Otros',
    'Planeación',
    'Produccion de Bienes y Servicios',
    'Rectorado',
    'Registros académicos',
    'Talento Humano',
    'Tesorería',
    'TIC',
    'Vicerrectorado Académico',
    'Vigilancia'
];


$CARGOS_POR_AREA = [
    'Admisión' => ['Jefe', 'Asistente', 'Apoyo', 'Practicante'],
    'Administración' => ['Director General de Administración', 'Asistente de Direccion General de Administración', 'Apoyo', 'Practicante'],
    'Archivo Central' => ['Responsable de archivo central', 'asistente de archivo central', 'Apoyo', 'Practicante'],
    'Asesoría Legal - Secretaría General' => ['Asesor(a) Legal', 'Secretario General', 'Asistente de asesoría legal', 'Asistente de grados y títulos', 'Asistente de Secretaría General', 'Responsable de Grados y Títulos', 'Apoyo', 'Practicante'],
    'Asuntos académicos' => ['Jefe', 'Docente TC', 'Asistente', 'Apoyo', 'Practicante'],
    'Biblioteca' => ['Jefe', 'Docente TC', 'Responsable', 'Asistente', 'Apoyo', 'Practicante'],
    'Bienes patrimoniales' => ['Jefe', 'analista', 'asistente', 'Apoyo', 'practicante'],
    'Bienestar Universitario' => ['Jefe', 'Docente TC', 'Asistente', 'Responsable Deportes', 'Responsable Cultura', 'Responsable Psicopedagogico', 'Responsable de becas', 'Responsable de tópico', 'Asistente social', 'Apoyo', 'Practicante'],
    'Calidad Educativa y Acreditación' => ['Director', 'Coordinador', 'Docente TC', 'Especialista', 'Asistente', 'Apoyo', 'Practicante'],
    'Colocación Laboral' => ['Responsable de colocación laboral', 'Docente TC', 'apoyo', 'asistente', 'practicante'],
    'Contabilidad y Finanzas' => ['Jefe', 'Contador I', 'Contador II', 'Contador III', 'Asistente', 'Auxiliar', 'Apoyo', 'Practicante'],
    'Cosmiatría' => ['Jefe', 'analista', 'Docente', 'asistente', 'Apoyo', 'practicante'],
    'Decanatura de CCSS' => ['Decano (a)', 'asistente', 'apoyo', 'practicante'],
    'Decanatura de Ciencias empresariales y Derecho' => ['Decano (a)', 'asistente', 'apoyo', 'practicante'],
    'Defensoría Universitaria' => ['Jefe', 'Docente TC', 'Asistente', 'Apoyo', 'Practicante'],
    'Docentes' => ['DTC', 'DTP','JP','DL','JPL'],
    'E.P. Administración' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Derecho' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Enfermería' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Estomatología' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Farmacia y Bioquímica' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Medicina' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Obstetricia' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'E.P. Psicología' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Docente TP', 'Apoyo', 'Practicante'],
    'Escuela de Postgrado' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Apoyo proveduría', 'Asesora de ventas', 'Apoyo', 'Practicante'],
    'Gerencia' => ['Gerente General', 'Asistente de Gerencia', 'Apoyo', 'Practicante', 'Secretaria'],
    'Informes' => ['Jefe', 'Asistente', 'Apoyo', 'Practicante'],
    'Instituto de Investigación' => ['Director', 'Coordinador', 'Docente TC Asistente', 'Apoyo', 'Practicante'],
    'Laboratorio' => ['Jefe', 'analista', 'asistente', 'apoyo', 'practicante'],
    'Limpieza' => ['Jefe', 'supervisor', 'operario', 'Apoyo'],
    'Logística' => ['Jefe', 'analista', 'asistente', 'Apoyo', 'practicante'],
    'Mantenimiento' => ['Jefe', 'supervisor', 'operario', 'responsable de unidades dentales', 'Encargado de áreas verdes', 'apoyo'],
    'Marketing' => ['Jefe', 'Docente TC', 'Asistente', 'Gestor de contenido', 'Diseñador', 'Community Manager', 'Promotor de colegio', 'Telemarketing', 'Apoyo', 'Practicante'],
    'Mesa de partes' => ['Jefe', 'analista', 'asistente', 'apoyo', 'practicante'],
    'Otros' => ['Otros'],
    'Planeación' => ['Jefe', 'analista', 'asistente', 'Apoyo', 'practicante'],
    'Produccion de Bienes y Servicios' => ['Jefe', 'analista', 'asistente', 'Apoyo', 'practicante'],
    'Rectorado' => ['Rector(a)', 'Asistente', 'Apoyo', 'Practicante'],
    'Registros académicos' => ['Jefe', 'Docente TC', 'Asistente', 'Apoyo', 'Practicante'],
    'Talento Humano' => ['Jefe', 'analista', 'asistente', 'Apoyo', 'practicante'],
    'Tesorería' => ['Jefe', 'Cajera', 'Asistente', 'Apoyo', 'Practicante'],
    'TIC' => ['Jefe de TIC', 'Asistente de TIC', 'Encargado de oficina de TIC', 'Apoyo', 'Practicante'],
    'Vicerrectorado Académico' => ['Vicerrector', 'Asistente', 'Apoyo', 'Practicante'],
    'Vigilancia' => ['Jefe', 'supervisor', 'operario', 'apoyo']
];

// Crear directorio de fotos si no existe
if (!file_exists(FOTO_DIR)) {
    mkdir(FOTO_DIR, 0777, true);
}

date_default_timezone_set(APP_TIMEZONE);

// Verificar sesión de administrador
session_start();
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

// Procesar ingreso manual de asistencia (solo para superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_asistencia']) && $isSuperAdmin) {
    $empleado_id = $_POST['empleado_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO registros_asistencia (empleado_id, fecha, hora) VALUES (?, ?, ?)");
        $stmt->execute([$empleado_id, $fecha, $hora]);
        
        $_SESSION['success_message'] = "Asistencia registrada correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?report_type=consolidated_person");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al registrar asistencia: " . $e->getMessage();
    }
}

// Procesar agregar nuevo empleado (solo para superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_empleado']) && $isSuperAdmin) {
    $dni = trim($_POST['dni']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $area = trim($_POST['area']);
    $puesto = trim($_POST['puesto']);
    $inicio_contrato = $_POST['inicio_contrato'];
    $fin_contrato = $_POST['fin_contrato'];
    $tipo_personal = $_POST['tipo_personal'];
    $entrada_manana = $_POST['entrada_manana'] ?? null;
    $salida_manana = $_POST['salida_manana'] ?? null;
    $entrada_tarde = $_POST['entrada_tarde'] ?? null;
    $salida_tarde = $_POST['salida_tarde'] ?? null;
    
    // Validar datos obligatorios
    if (empty($dni) || empty($nombres) || empty($apellidos) || empty($area) || empty($puesto) || 
        empty($inicio_contrato) || empty($fin_contrato) || empty($tipo_personal)) {
        $_SESSION['error_message'] = "Todos los campos obligatorios deben ser completados";
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    }
    
    // Procesar foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = FOTO_DIR . $dni . '.' . $ext;
            
            // Mover archivo subido
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto)) {
                $_SESSION['error_message'] = "Error al subir la foto";
                header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Formato de imagen no válido. Use JPEG, PNG o GIF";
            header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
            exit;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO empleados (dni, nombres, apellidos, area, puesto, inicio_contrato, fin_contrato, tipo_personal, entrada_manana, salida_manana, entrada_tarde, salida_tarde, foto) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$dni, $nombres, $apellidos, $area, $puesto, $inicio_contrato, $fin_contrato, $tipo_personal, $entrada_manana, $salida_manana, $entrada_tarde, $salida_tarde, $foto]);
        
        $_SESSION['success_message'] = "Empleado agregado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    } catch (PDOException $e) {
        // Eliminar foto si se subió pero falló la inserción
        if ($foto && file_exists($foto)) {
            unlink($foto);
        }
        
        // Verificar si es error de duplicado de DNI
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error_message'] = "Error: El DNI ya existe en el sistema";
        } else {
            $_SESSION['error_message'] = "Error al agregar empleado: " . $e->getMessage();
        }
        
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    }
}

// Procesar editar empleado (solo para superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_empleado']) && $isSuperAdmin) {
    $id = $_POST['id'];
    $dni = trim($_POST['dni']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $area = trim($_POST['area']);
    $puesto = trim($_POST['puesto']);
    $inicio_contrato = $_POST['inicio_contrato'];
    $fin_contrato = $_POST['fin_contrato'];
    $tipo_personal = $_POST['tipo_personal'];
    $entrada_manana = $_POST['entrada_manana'] ?? null;
    $salida_manana = $_POST['salida_manana'] ?? null;
    $entrada_tarde = $_POST['entrada_tarde'] ?? null;
    $salida_tarde = $_POST['salida_tarde'] ?? null;
    
    // Validar datos obligatorios
    if (empty($dni) || empty($nombres) || empty($apellidos) || empty($area) || empty($puesto) || 
        empty($inicio_contrato) || empty($fin_contrato) || empty($tipo_personal)) {
        $_SESSION['error_message'] = "Todos los campos obligatorios deben ser completados";
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    }
    
    // Obtener empleado actual para la foto
    $stmt = $pdo->prepare("SELECT foto, dni as dni_actual FROM empleados WHERE id = ?");
    $stmt->execute([$id]);
    $empleado = $stmt->fetch();
    $foto = $empleado['foto'];
    $dni_actual = $empleado['dni_actual'];
    
    // Procesar nueva foto si se subió
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Eliminar foto anterior si existe
            if ($foto && file_exists($foto)) {
                unlink($foto);
            }
            
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = FOTO_DIR . $dni . '.' . $ext;
            
            // Mover archivo subido
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto)) {
                $_SESSION['error_message'] = "Error al subir la foto";
                header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Formato de imagen no válido. Use JPEG, PNG o GIF";
            header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
            exit;
        }
    } elseif ($foto && $dni != $dni_actual) {
        // Si el DNI cambió pero no la foto, renombrar el archivo
        $ext = pathinfo($foto, PATHINFO_EXTENSION);
        $nueva_foto = FOTO_DIR . $dni . '.' . $ext;
        
        if (rename($foto, $nueva_foto)) {
            $foto = $nueva_foto;
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE empleados SET dni = ?, nombres = ?, apellidos = ?, area = ?, puesto = ?, inicio_contrato = ?, fin_contrato = ?, tipo_personal = ?, entrada_manana = ?, salida_manana = ?, entrada_tarde = ?, salida_tarde = ?, foto = ? WHERE id = ?");
        $stmt->execute([$dni, $nombres, $apellidos, $area, $puesto, $inicio_contrato, $fin_contrato, $tipo_personal, $entrada_manana, $salida_manana, $entrada_tarde, $salida_tarde, $foto, $id]);
        
        $_SESSION['success_message'] = "Empleado actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar empleado: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    }
}

// Procesar eliminar empleado (solo para superadmin)
if (isset($_GET['eliminar_empleado']) && $isSuperAdmin) {
    $id = $_GET['eliminar_empleado'];
    
    try {
        // Iniciar transacción para asegurar que todas las operaciones se completen
        $pdo->beginTransaction();
        
        // Obtener foto para eliminarla
        $stmt = $pdo->prepare("SELECT foto FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        $empleado = $stmt->fetch();
        
        // Eliminar registros de asistencia relacionados
        $stmt = $pdo->prepare("DELETE FROM registros_asistencia WHERE empleado_id = ?");
        $stmt->execute([$id]);
        
        // Eliminar permisos relacionados
        $stmt = $pdo->prepare("DELETE FROM permisos WHERE empleado_id = ?");
        $stmt->execute([$id]);
        
        // Eliminar empleado
        $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        
        // Eliminar foto si existe
        if ($empleado && $empleado['foto'] && file_exists($empleado['foto'])) {
            unlink($empleado['foto']);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Empleado eliminado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error al eliminar empleado: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']."?section=empleados");
        exit;
    }
}

// Procesar agregar administrador (solo para superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_admin']) && $isSuperAdmin) {
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    
    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        $_SESSION['error_message'] = "Todos los campos deben ser completados";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
    
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios_admin (usuario, contrasena, rol) VALUES (?, ?, ?)");
        $stmt->execute([$usuario, $contrasena_hash, $rol]);
        
        $_SESSION['success_message'] = "Administrador agregado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al agregar administrador: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
}

// Procesar editar administrador (solo para superadmin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_admin']) && $isSuperAdmin) {
    $id = $_POST['id'];
    $usuario = trim($_POST['usuario']);
    $rol = $_POST['rol'];
    
    if (empty($usuario) || empty($rol)) {
        $_SESSION['error_message'] = "Todos los campos deben ser completados";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
    
    try {
        // Si se proporcionó una nueva contraseña
        if (!empty($_POST['contrasena'])) {
            $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ?, contrasena = ?, rol = ? WHERE id = ?");
            $stmt->execute([$usuario, $contrasena, $rol, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = ?, rol = ? WHERE id = ?");
            $stmt->execute([$usuario, $rol, $id]);
        }
        
        $_SESSION['success_message'] = "Administrador actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar administrador: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
}

// Procesar eliminar administrador (solo para superadmin)
if (isset($_GET['eliminar_admin']) && $isSuperAdmin) {
    $id = $_GET['eliminar_admin'];
    
    // No permitir eliminarse a sí mismo
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error_message'] = "No puedes eliminarte a ti mismo";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios_admin WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Administrador eliminado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar administrador: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']."?section=administradores");
        exit;
    }
}

// Clase para generar reportes
class ReportGenerator {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtiene reporte diario de asistencia
     */
    public function getDailyReport($fechaInicio, $fechaFin, $area = null, $cargo = null, $tipoPersonal = null, $busqueda = null) {
        $params = [$fechaInicio, $fechaFin];
        $where = "WHERE r.fecha BETWEEN ? AND ?";
        
        if ($area) {
            $where .= " AND e.area = ?";
            $params[] = $area;
        }
        
        if ($cargo) {
            $where .= " AND e.puesto = ?";
            $params[] = $cargo;
        }
        
        if ($tipoPersonal) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $tipoPersonal;
        }
        
        if ($busqueda) {
            $where .= " AND (e.dni LIKE ? OR CONCAT(e.nombres, ' ', e.apellidos) LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = "SELECT 
                    e.id, e.dni, 
                    CONCAT(e.nombres, ' ', e.apellidos) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal,
                    e.entrada_manana, e.salida_manana, e.entrada_tarde, e.salida_tarde,
                    r.fecha,
                    GROUP_CONCAT(DISTINCT TIME(r.hora) ORDER BY r.hora SEPARATOR '|') AS registros
                FROM empleados e
                LEFT JOIN registros_asistencia r ON e.id = r.empleado_id
                $where
                GROUP BY e.id, r.fecha
                ORDER BY r.fecha, e.area, e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        
        // Procesar los registros para aplicar los criterios horarios
        foreach ($result as &$row) {
            $registros = explode('|', $row['registros'] ?? '');
            $row['registros_dia'] = [];
            $row['entrada_manana_reg'] = null;
            $row['salida_manana_reg'] = null;
            $row['entrada_tarde_reg'] = null;
            $row['salida_tarde_reg'] = null;
            
            foreach ($registros as $hora) {
                if (empty($hora)) continue;
                
                $horaTime = strtotime($hora);
                
                // Solo para personal administrativo
                if ($row['tipo_personal'] === 'Administrativo') {
                    // Entrada mañana (1:00 AM - Salida mañana)
                    if ($horaTime >= strtotime('01:00:00') && $horaTime < strtotime($row['salida_manana'])) {
                        if (!$row['entrada_manana_reg'] || $horaTime < strtotime($row['entrada_manana_reg'])) {
                            $row['entrada_manana_reg'] = $hora;
                        }
                    }
                    
                    // Salida mañana (Salida mañana - 15:00)
                    if ($horaTime >= strtotime($row['salida_manana']) && $horaTime < strtotime('15:00:00')) {
                        if (!$row['salida_manana_reg'] || $horaTime > strtotime($row['salida_manana_reg'])) {
                            $row['salida_manana_reg'] = $hora;
                        }
                    }
                    
                    // Entrada tarde (15:00 - Salida tarde)
                    if ($horaTime >= strtotime('15:00:00') && $horaTime < strtotime($row['salida_tarde'])) {
                        if (!$row['entrada_tarde_reg'] || $horaTime < strtotime($row['entrada_tarde_reg'])) {
                            $row['entrada_tarde_reg'] = $hora;
                        }
                    }
                    
                    // Salida tarde (Salida tarde - 23:59)
                    if ($horaTime >= strtotime($row['salida_tarde']) && $horaTime <= strtotime('23:59:59')) {
                        if (!$row['salida_tarde_reg'] || $horaTime > strtotime($row['salida_tarde_reg'])) {
                            $row['salida_tarde_reg'] = $hora;
                        }
                    }
                }
                
                $row['registros_dia'][] = $hora;
            }
            
            unset($row['registros']);
        }
        
        return $result;
    }
    
    /**
     * Obtiene reporte de tardanzas
     */
    public function getTardinessReport($fechaInicio, $fechaFin, $area = null, $cargo = null, $tipoPersonal = null, $busqueda = null) {
        $params = [$fechaInicio, $fechaFin];
        $where = "WHERE r.fecha BETWEEN ? AND ? AND e.tipo_personal = 'Administrativo'";
        
        if ($area) {
            $where .= " AND e.area = ?";
            $params[] = $area;
        }
        
        if ($cargo) {
            $where .= " AND e.puesto = ?";
            $params[] = $cargo;
        }
        
        if ($busqueda) {
            $where .= " AND (e.dni LIKE ? OR CONCAT(e.nombres, ' ', e.apellidos) LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = "SELECT 
                    e.id, e.dni, 
                    CONCAT(e.nombres, ' ', e.apellidos) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal,
                    e.entrada_manana, e.salida_manana, e.entrada_tarde, e.salida_tarde,
                    r.fecha, TIME(r.hora) AS hora
                FROM empleados e
                JOIN registros_asistencia r ON e.id = r.empleado_id
                $where
                ORDER BY e.area, e.apellidos, e.nombres, r.fecha, r.hora";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();
        
        // Procesamos los registros para calcular tardanzas
        $result = [];
        $currentEmpleado = null;
        $currentFecha = null;
        
        foreach ($registros as $reg) {
            // Si cambiamos de empleado, inicializamos
            if (!$currentEmpleado || $currentEmpleado['id'] != $reg['id']) {
                if ($currentEmpleado) {
                    $result[] = $currentEmpleado;
                }
                
                $currentEmpleado = [
                    'id' => $reg['id'],
                    'dni' => $reg['dni'],
                    'nombre_completo' => $reg['nombre_completo'],
                    'area' => $reg['area'],
                    'puesto' => $reg['puesto'],
                    'tipo_personal' => $reg['tipo_personal'],
                    'entrada_manana' => $reg['entrada_manana'],
                    'salida_manana' => $reg['salida_manana'],
                    'entrada_tarde' => $reg['entrada_tarde'],
                    'salida_tarde' => $reg['salida_tarde'],
                    'tardanzas_manana' => [],
                    'tardanzas_tarde' => []
                ];
                $currentFecha = null;
            }
            
            // Si cambiamos de fecha, reiniciamos el control para el empleado
            if (!$currentFecha || $currentFecha != $reg['fecha']) {
                $currentFecha = $reg['fecha'];
                $mananaRegistrada = false;
                $tardeRegistrada = false;
            }
            
            $horaRegistro = strtotime($reg['hora']);
            $horaEntradaManana = strtotime($reg['entrada_manana']);
            $horaEntradaTarde = strtotime($reg['entrada_tarde']);
            
            // Verificar tardanza mañana (solo primera vez que registra en el rango)
            if (!$mananaRegistrada && $horaRegistro >= $horaEntradaManana && $horaRegistro < strtotime($reg['salida_manana'])) {
                $segundosTardanza = max(0, ($horaRegistro - $horaEntradaManana - 300)); // 5 minutos de tolerancia
                if ($segundosTardanza > 0) {
                    $minutos = floor($segundosTardanza / 60);
                    $segundos = $segundosTardanza % 60;
                    $tardanzaFormato = sprintf("%02d:%02d", $minutos, $segundos);
                    
                    $currentEmpleado['tardanzas_manana'][] = [
                        'fecha' => $reg['fecha'],
                        'hora_entrada' => $reg['entrada_manana'],
                        'hora_marcada' => $reg['hora'],
                        'tardanza' => $tardanzaFormato
                    ];
                }
                $mananaRegistrada = true;
            }
            
            // Verificar tardanza tarde (solo primera vez que registra en el rango)
            if (!$tardeRegistrada && $horaRegistro >= $horaEntradaTarde && $horaRegistro < strtotime($reg['salida_tarde'])) {
                $segundosTardanza = max(0, ($horaRegistro - $horaEntradaTarde - 300)); // 5 minutos de tolerancia
                if ($segundosTardanza > 0) {
                    $minutos = floor($segundosTardanza / 60);
                    $segundos = $segundosTardanza % 60;
                    $tardanzaFormato = sprintf("%02d:%02d", $minutos, $segundos);
                    
                    $currentEmpleado['tardanzas_tarde'][] = [
                        'fecha' => $reg['fecha'],
                        'hora_entrada' => $reg['entrada_tarde'],
                        'hora_marcada' => $reg['hora'],
                        'tardanza' => $tardanzaFormato
                    ];
                }
                $tardeRegistrada = true;
            }
        }
        
        // Añadir el último empleado procesado
        if ($currentEmpleado) {
            $result[] = $currentEmpleado;
        }
        
        return $result;
    }
    
    /**
     * Obtiene reporte de permisos
     */
    public function getPermissionReport($fechaInicio, $fechaFin, $area = null, $cargo = null, $tipoPersonal = null, $busqueda = null) {
        $params = [$fechaInicio, $fechaFin];
        $where = "WHERE p.fecha_permiso BETWEEN ? AND ?";
        
        if ($area) {
            $where .= " AND e.area = ?";
            $params[] = $area;
        }
        
        if ($cargo) {
            $where .= " AND e.puesto = ?";
            $params[] = $cargo;
        }
        
        if ($tipoPersonal) {
            $where .= " AND e.tipo_personal = ?";
            $params[] = $tipoPersonal;
        }
        
        if ($busqueda) {
            $where .= " AND (e.dni LIKE ? OR CONCAT(e.nombres, ' ', e.apellidos) LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = "SELECT 
                    p.id, p.fecha_permiso, p.tipo_permiso, p.motivo, 
                    p.hora_salida, p.hora_retorno, p.hora_registro,
                    e.id AS empleado_id, e.dni, 
                    CONCAT(e.nombres, ' ', e.apellidos) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal
                FROM permisos p
                JOIN empleados e ON p.empleado_id = e.id
                $where
                ORDER BY p.fecha_permiso, e.area, e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene reporte consolidado por áreas
     */
    public function getConsolidatedAreaReport($fechaInicio, $fechaFin) {
        // Obtenemos todos los días en el rango (incluyendo sábados y domingos)
        $dias = $this->getAllDays($fechaInicio, $fechaFin);
        $totalDias = count($dias);
        
        // Obtenemos todas las áreas
        $areas = $this->getAreas();
        $result = [];
        
        foreach ($areas as $area) {
            // Obtenemos empleados por área
            $sqlEmpleados = "SELECT id, puesto, tipo_personal FROM empleados WHERE area = ?";
            $stmtEmp = $this->pdo->prepare($sqlEmpleados);
            $stmtEmp->execute([$area]);
            $empleados = $stmtEmp->fetchAll();
            
            if (empty($empleados)) continue;
            
            // Agrupar por cargo
            $cargos = [];
            foreach ($empleados as $emp) {
                $cargos[$emp['puesto']] = ($cargos[$emp['puesto']] ?? 0) + 1;
            }
            
            // IDs de empleados para usar en las consultas
            $empleadosIds = array_column($empleados, 'id');
            $placeholders = implode(',', array_fill(0, count($empleadosIds), '?'));
            
            // Calculamos asistencias por área
            $sqlAsistencias = "SELECT COUNT(DISTINCT CONCAT(empleado_id, '|', fecha)) 
                               FROM registros_asistencia 
                               WHERE empleado_id IN ($placeholders)
                               AND fecha BETWEEN ? AND ?";
            $stmtAsist = $this->pdo->prepare($sqlAsistencias);
            $stmtAsist->execute(array_merge($empleadosIds, [$fechaInicio, $fechaFin]));
            $totalAsistencias = $stmtAsist->fetchColumn();
            
            // Calculamos permisos por área
            $sqlPermisos = "SELECT COUNT(DISTINCT CONCAT(empleado_id, '|', fecha_permiso)) 
                            FROM permisos 
                            WHERE empleado_id IN ($placeholders)
                            AND fecha_permiso BETWEEN ? AND ?";
            $stmtPerm = $this->pdo->prepare($sqlPermisos);
            $stmtPerm->execute(array_merge($empleadosIds, [$fechaInicio, $fechaFin]));
            $totalPermisos = $stmtPerm->fetchColumn();
            
            // Calculamos tardanzas por área (solo para administrativos)
            $sqlTardanzas = "SELECT COUNT(*) FROM (
                                SELECT ra.empleado_id, ra.fecha, MIN(TIME(ra.hora)) as hora
                                FROM registros_asistencia ra
                                JOIN empleados e ON ra.empleado_id = e.id
                                WHERE ra.empleado_id IN ($placeholders)
                                AND ra.fecha BETWEEN ? AND ?
                                AND e.tipo_personal = 'Administrativo'
                                GROUP BY ra.empleado_id, ra.fecha
                            ) AS tardanzas";
            $stmtTard = $this->pdo->prepare($sqlTardanzas);
            $stmtTard->execute(array_merge($empleadosIds, [$fechaInicio, $fechaFin]));
            $totalTardanzas = $stmtTard->fetchColumn();
            
            // Calculamos faltas (días - asistencias, sin considerar permisos)
            $totalFaltas = ($totalDias * count($empleados)) - $totalAsistencias;
            
            foreach ($cargos as $cargo => $cantidad) {
                $result[] = [
                    'area' => $area,
                    'cargo' => $cargo,
                    'total_empleados' => $cantidad,
                    'total_dias' => $totalDias,
                    'total_asistencias' => round($totalAsistencias * ($cantidad / count($empleados))),
                    'total_permisos' => round($totalPermisos * ($cantidad / count($empleados))),
                    'total_tardanzas' => round($totalTardanzas * ($cantidad / count($empleados))),
                    'total_faltas' => round($totalFaltas * ($cantidad / count($empleados))),
                    'porcentaje_asistencia' => round(($totalAsistencias / ($totalDias * count($empleados))) * 100, 2)
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Obtiene reporte consolidado por persona
     */
    public function getConsolidatedPersonReport($fechaInicio, $fechaFin, $area = null, $cargo = null, $tipoPersonal = null, $busqueda = null) {
        // Obtenemos todos los días en el rango (incluyendo sábados y domingos)
        $dias = $this->getAllDays($fechaInicio, $fechaFin);
        $totalDias = count($dias);
        
        // Obtenemos empleados con filtros
        $params = [];
        $where = "WHERE 1=1";
        
        if ($area) {
            $where .= " AND area = ?";
            $params[] = $area;
        }
        
        if ($cargo) {
            $where .= " AND puesto = ?";
            $params[] = $cargo;
        }
        
        if ($tipoPersonal) {
            $where .= " AND tipo_personal = ?";
            $params[] = $tipoPersonal;
        }
        
        if ($busqueda) {
            $where .= " AND (dni LIKE ? OR CONCAT(nombres, ' ', apellidos) LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = "SELECT 
                    id, dni, CONCAT(nombres, ' ', apellidos) AS nombre_completo, 
                    area, puesto, tipo_personal
                FROM empleados $where ORDER BY area, apellidos, nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $empleados = $stmt->fetchAll();
        
        $result = [];
        foreach ($empleados as $empleado) {
            // Asistencias
            $sqlAsistencias = "SELECT COUNT(DISTINCT fecha) FROM registros_asistencia 
                              WHERE empleado_id = ? AND fecha BETWEEN ? AND ?";
            $stmtAsist = $this->pdo->prepare($sqlAsistencias);
            $stmtAsist->execute([$empleado['id'], $fechaInicio, $fechaFin]);
            $asistencias = $stmtAsist->fetchColumn();
            
            // Permisos
            $sqlPermisos = "SELECT COUNT(DISTINCT fecha_permiso) FROM permisos 
                           WHERE empleado_id = ? AND fecha_permiso BETWEEN ? AND ?";
            $stmtPerm = $this->pdo->prepare($sqlPermisos);
            $stmtPerm->execute([$empleado['id'], $fechaInicio, $fechaFin]);
            $permisos = $stmtPerm->fetchColumn();
            
            // Tardanzas (solo para administrativos)
            $tardanzas = 0;
            if ($empleado['tipo_personal'] === 'Administrativo') {
                $sqlTardanzas = "SELECT COUNT(*) FROM (
                                    SELECT ra.empleado_id, ra.fecha, MIN(TIME(ra.hora)) as hora
                                    FROM registros_asistencia ra
                                    JOIN empleados e ON ra.empleado_id = e.id
                                    WHERE ra.empleado_id = ?
                                    AND ra.fecha BETWEEN ? AND ?
                                    AND e.tipo_personal = 'Administrativo'
                                    GROUP BY ra.empleado_id, ra.fecha
                                ) AS tardanzas";
                $stmtTard = $this->pdo->prepare($sqlTardanzas);
                $stmtTard->execute([$empleado['id'], $fechaInicio, $fechaFin]);
                $tardanzas = $stmtTard->fetchColumn();
            }
            
            // Faltas (días - asistencias, sin considerar permisos)
            $faltas = $totalDias - $asistencias;
            
            $result[] = [
                'id' => $empleado['id'],
                'dni' => $empleado['dni'],
                'nombre_completo' => $empleado['nombre_completo'],
                'area' => $empleado['area'],
                'puesto' => $empleado['puesto'],
                'tipo_personal' => $empleado['tipo_personal'],
                'total_dias' => $totalDias,
                'asistencias' => $asistencias,
                'permisos' => $permisos,
                'faltas' => $faltas,
                'tardanzas' => $tardanzas,
                'porcentaje_asistencia' => round(($asistencias / $totalDias) * 100, 2),
                'detalle_asistencias' => $this->getAttendanceDetail($empleado['id'], $fechaInicio, $fechaFin)
            ];
        }
        
        return $result;
    }
    
    /**
     * Obtiene detalle de asistencia por persona
     */
    public function getAttendanceDetail($empleadoId, $fechaInicio, $fechaFin) {
        // Obtenemos todos los días en el rango
        $dias = $this->getAllDays($fechaInicio, $fechaFin);
        
        // Obtenemos los registros de asistencia
        $sqlAsistencias = "SELECT fecha, TIME(hora) as hora FROM registros_asistencia 
                          WHERE empleado_id = ? AND fecha BETWEEN ? AND ?
                          ORDER BY fecha, hora";
        $stmtAsist = $this->pdo->prepare($sqlAsistencias);
        $stmtAsist->execute([$empleadoId, $fechaInicio, $fechaFin]);
        $asistencias = $stmtAsist->fetchAll();
        
        // Obtenemos los permisos
        $sqlPermisos = "SELECT fecha_permiso, tipo_permiso FROM permisos 
                       WHERE empleado_id = ? AND fecha_permiso BETWEEN ? AND ?";
        $stmtPerm = $this->pdo->prepare($sqlPermisos);
        $stmtPerm->execute([$empleadoId, $fechaInicio, $fechaFin]);
        $permisos = $stmtPerm->fetchAll();
        
        // Obtenemos datos del empleado
        $sqlEmpleado = "SELECT tipo_personal FROM empleados WHERE id = ?";
        $stmtEmp = $this->pdo->prepare($sqlEmpleado);
        $stmtEmp->execute([$empleadoId]);
        $empleado = $stmtEmp->fetch();
        
        // Procesamos cada día
        $detalle = [];
        foreach ($dias as $dia) {
            $registrosDia = array_filter($asistencias, function($a) use ($dia) {
                return $a['fecha'] == $dia;
            });
            
            $permisoDia = array_filter($permisos, function($p) use ($dia) {
                return $p['fecha_permiso'] == $dia;
            });
            
            $item = [
                'fecha' => $dia,
                'tipo' => 'Asistencia',
                'registros' => array_column($registrosDia, 'hora'),
                'permiso' => null
            ];
            
            // Verificar si tiene permiso
            if (!empty($permisoDia)) {
                $permiso = reset($permisoDia);
                $item['tipo'] = 'Permiso';
                $item['permiso'] = $permiso['tipo_permiso'];
            } 
            // Verificar si es falta
            elseif (empty($registrosDia)) {
                $item['tipo'] = 'Falta';
            }
            
            $detalle[] = $item;
        }
        
        return $detalle;
    }
    
    /**
     * Obtiene lista de todos los días entre dos fechas (incluyendo sábados y domingos)
     */
    private function getAllDays($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('+1 day'); // Incluir el día final
        
        $days = [];
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
        foreach ($period as $date) {
            $days[] = $date->format('Y-m-d');
        }
        
        return $days;
    }
    
    /**
     * Obtiene lista de áreas
     */
    public function getAreas() {
        $stmt = $this->pdo->query("SELECT DISTINCT area FROM empleados ORDER BY area");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * Obtiene lista de cargos
     */
    public function getPositions() {
        $stmt = $this->pdo->query("SELECT DISTINCT puesto FROM empleados ORDER BY puesto");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * Obtiene lista de tipos de personal
     */
    public function getPersonalTypes() {
        $stmt = $this->pdo->query("SELECT DISTINCT tipo_personal FROM empleados ORDER BY tipo_personal");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * Obtiene lista de empleados para el select
     */
    public function getEmployees() {
        $stmt = $this->pdo->query("SELECT id, dni, nombres, apellidos, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM empleados WHERE nombres IS NOT NULL AND apellidos IS NOT NULL ORDER BY apellidos, nombres");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene lista completa de empleados con todos los datos
     */
    public function getAllEmployees() {
        $stmt = $this->pdo->query("SELECT * FROM empleados WHERE nombres IS NOT NULL AND apellidos IS NOT NULL ORDER BY apellidos, nombres");
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
     * Busca empleados por término (para el autocompletado)
     */
    public function searchEmployees($term) {
        $stmt = $this->pdo->prepare("SELECT id, dni, CONCAT(nombres, ' ', apellidos) AS nombre_completo 
                                    FROM empleados 
                                    WHERE (dni LIKE ? OR CONCAT(nombres, ' ', apellidos) LIKE ?)
                                    AND nombres IS NOT NULL 
                                    AND apellidos IS NOT NULL
                                    ORDER BY apellidos, nombres
                                    LIMIT 10");
        $stmt->execute(["%$term%", "%$term%"]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene lista de administradores
     */
    public function getAdmins() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios_admin ORDER BY rol, usuario");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un administrador por ID
     */
    public function getAdminById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

// Instanciar el generador de reportes
$reportGenerator = new ReportGenerator($pdo);

// Determinar qué sección mostrar
$section = $_GET['section'] ?? 'dashboard';

// Procesar filtros comunes para reportes
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$area = $_GET['area'] ?? null;
$cargo = $_GET['cargo'] ?? null;
$tipoPersonal = $_GET['tipo_personal'] ?? null;
$busqueda = $_GET['busqueda'] ?? null;

// Generar el reporte correspondiente si estamos en dashboard
if ($section === 'dashboard') {
    $reportType = $_GET['report_type'] ?? 'daily';
    
    switch ($reportType) {
        case 'tardiness':
            $reportData = $reportGenerator->getTardinessReport($fechaInicio, $fechaFin, $area, $cargo, $tipoPersonal, $busqueda);
            $reportTitle = "Reporte de Tardanzas";
            break;
            
        case 'permission':
            $reportData = $reportGenerator->getPermissionReport($fechaInicio, $fechaFin, $area, $cargo, $tipoPersonal, $busqueda);
            $reportTitle = "Reporte de Permisos";
            break;
            
        case 'consolidated_area':
            $reportData = $reportGenerator->getConsolidatedAreaReport($fechaInicio, $fechaFin);
            $reportTitle = "Reporte Consolidado por Áreas";
            break;
            
        case 'consolidated_person':
            $reportData = $reportGenerator->getConsolidatedPersonReport($fechaInicio, $fechaFin, $area, $cargo, $tipoPersonal, $busqueda);
            $reportTitle = "Reporte Consolidado por Persona";
            break;
            
        default: // daily
            $reportData = $reportGenerator->getDailyReport($fechaInicio, $fechaFin, $area, $cargo, $tipoPersonal, $busqueda);
            $reportTitle = "Reporte Diario de Asistencia";
            break;
    }
    
    // Configurar paginación para reportes
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = 20;
    $totalItems = count($reportData);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedData = array_slice($reportData, $offset, $itemsPerPage);
}

// Obtener datos para sección de empleados
if ($section === 'empleados' && $isSuperAdmin) {
    $employees = $reportGenerator->getAllEmployees();
    
    // Si estamos editando, obtener datos del empleado
    $editingEmployee = null;
    if (isset($_GET['editar_empleado'])) {
        $editingEmployee = $reportGenerator->getEmployeeById($_GET['editar_empleado']);
    }
}

// Obtener datos para sección de administradores
if ($section === 'administradores' && $isSuperAdmin) {
    $admins = $reportGenerator->getAdmins();
    
    // Si estamos editando, obtener datos del administrador
    $editingAdmin = null;
    if (isset($_GET['editar_admin'])) {
        $editingAdmin = $reportGenerator->getAdminById($_GET['editar_admin']);
    }
}

// Obtener listas para filtros
$areas = $reportGenerator->getAreas();
$positions = $reportGenerator->getPositions();
$personalTypes = $reportGenerator->getPersonalTypes();
$employeesList = $reportGenerator->getEmployees();

// Función para exportar a Excel
if (isset($_GET['export_excel']) && $section === 'dashboard') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=reporte_asistencia_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Reporte</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>';
    echo 'body { font-family: Arial, sans-serif; font-size: 12px; color: #333333; }';
    echo 'table { border-collapse: collapse; width: 100%; mso-border-alt: solid black .5pt; }';
    echo 'th, td { border: 1.5pt solid black; text-align: left; padding: 8px; mso-border-alt: solid black .5pt; }';
    echo 'th { background-color: #8A1538; color: white; font-weight: bold; text-align: center; vertical-align: middle; }';
    echo '.title { font-size: 18px; font-weight: bold; color: #8A1538; margin-bottom: 15px; text-align: center; }';
    echo '.subtitle { font-size: 14px; margin-bottom: 15px; text-align: center; }';
    echo '.filter { font-size: 12px; margin-bottom: 8px; padding: 5px; background-color: #f9f9f9; border: 1px solid #ddd; }';
    echo '.filter strong { color: #8A1538; }';
    echo '.header-info { margin-bottom: 20px; border: 1.5pt solid black; padding: 10px; background-color: #f5f5f5; }';
    echo '.logo { text-align: center; margin-bottom: 15px; }';
    echo '.university { font-weight: bold; font-size: 16px; text-align: center; margin-bottom: 5px; color: #8A1538; }';
    echo '.report-title { font-weight: bold; font-size: 16px; text-align: center; margin-bottom: 10px; color: #8A1538; }';
    echo '.period { font-size: 14px; text-align: center; margin-bottom: 15px; font-weight: bold; }';
    echo '.generated { font-size: 11px; text-align: right; margin-top: 20px; color: #666; padding: 5px; border-top: 1.5pt solid black; }';
    echo '.badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; border: 1px solid #ccc; }';
    echo '.badge-success { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }';
    echo '.badge-warning { background-color: #fff8e1; color: #f57c00; border-color: #ffecb3; }';
    echo '.badge-danger { background-color: #ffebee; color: #d32f2f; border-color: #ffcdd2; }';
    echo '.badge-info { background-color: #e3f2fd; color: #1565c0; border-color: #bbdefb; }';
    echo '.detail-table { width: 95%; margin: 0 auto; border: 1.5pt solid black; }';
    echo '.detail-table th { background-color: #6a0dad; font-size: 11px; }';
    echo '.detail-row td { padding: 0 !important; border: none !important; }';
    echo '.text-center { text-align: center; }';
    echo '.text-right { text-align: right; }';
    echo '.bg-light { background-color: #f9f9f9; }';
    echo '.nowrap { white-space: nowrap; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    // Encabezado del reporte
    echo '<div class="logo">';
    echo '<div class="university">' . UNIVERSIDAD_NOMBRE . '</div>';
    echo '<div class="report-title">' . $reportTitle . '</div>';
    echo '<div class="period">Periodo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . '</div>';
    echo '</div>';
    
    // Filtros aplicados
    echo '<div class="header-info">';
    echo '<div style="font-weight: bold; margin-bottom: 10px; color: #8A1538;">FILTROS APLICADOS:</div>';
    if ($area) echo '<div class="filter"><strong>Área:</strong> ' . htmlspecialchars($area) . '</div>';
    if ($cargo) echo '<div class="filter"><strong>Cargo:</strong> ' . htmlspecialchars($cargo) . '</div>';
    if ($tipoPersonal) echo '<div class="filter"><strong>Tipo de Personal:</strong> ' . htmlspecialchars($tipoPersonal) . '</div>';
    if ($busqueda) echo '<div class="filter"><strong>Búsqueda:</strong> ' . htmlspecialchars($busqueda) . '</div>';
    echo '</div>';
    
    // Contenido del reporte
    echo '<table>';
    
    // Encabezados según tipo de reporte
    switch ($reportType) {
        case 'tardiness':
            echo '<tr>
                <th>DNI</th>
                <th>NOMBRE</th>
                <th>FECHA</th>
                <th>HORA ENTRADA MAÑANA</th>
                <th>HORA MARCADA MAÑANA</th>
                <th>TARDANZA MAÑANA</th>
                <th>HORA ENTRADA TARDE</th>
                <th>HORA MARCADA TARDE</th>
                <th>TARDANZA TARDE</th>
            </tr>';
            break;
            
        case 'permission':
            echo '<tr>
                <th>DNI</th>
                <th>NOMBRE</th>
                <th>ÁREA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>FECHA</th>
                <th>TIPO PERMISO</th>
                <th>MOTIVO</th>
                <th>SALIDA</th>
                <th>RETORNO</th>
                <th>REGISTRO</th>
            </tr>';
            break;
            
        case 'consolidated_area':
            echo '<tr>
                <th>ÁREA</th>
                <th>CARGO</th>
                <th>EMPLEADOS</th>
                <th>DÍAS</th>
                <th>ASISTENCIAS</th>
                <th>PERMISOS</th>
                <th>TARDANZAS</th>
                <th>FALTAS</th>
                <th>% ASISTENCIA</th>
            </tr>';
            break;
            
        case 'consolidated_person':
            echo '<tr>
                <th>DNI</th>
                <th>NOMBRE</th>
                <th>ÁREA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>DÍAS</th>
                <th>ASISTENCIAS</th>
                <th>PERMISOS</th>
                <th>FALTAS</th>
                <th>TARDANZAS</th>
                <th>% ASISTENCIA</th>
            </tr>';
            break;
            
        default: // daily
            echo '<tr>
                <th>FECHA</th>
                <th>DNI</th>
                <th>NOMBRE</th>
                <th>ÁREA</th>
                <th>CARGO</th>
                <th>TIPO</th>
                <th>ENT. MAÑANA</th>
                <th>SAL. MAÑANA</th>
                <th>ENT. TARDE</th>
                <th>SAL. TARDE</th>
                <th>REGISTROS</th>
            </tr>';
            break;
    }
    
    // Datos según tipo de reporte
    $rowCount = 0;
    foreach ($reportData as $row) {
        $rowClass = ($rowCount % 2 == 0) ? 'bg-light' : '';
        echo '<tr class="' . $rowClass . '">';
        
        switch ($reportType) {
            case 'tardiness':
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
                foreach ($tardanzasCombinadas as $fecha => $tardanzas) {
                    echo '<td class="text-center">' . htmlspecialchars($row['dni']) . '</td>
                    <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
                    <td class="text-center nowrap">' . $fecha . '</td>
                    
                    <!-- Datos mañana -->
                    <td class="text-center">' . ($tardanzas['manana'] ? $tardanzas['manana']['hora_entrada'] : '-') . '</td>
                    <td class="text-center">' . ($tardanzas['manana'] ? $tardanzas['manana']['hora_marcada'] : '-') . '</td>
                    <td class="text-center" style="color: ' . (($tardanzas['manana'] && $tardanzas['manana']['tardanza'] != '00:00') ? '#d32f2f' : '#2e7d32') . ';">' . ($tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-') . '</td>
                    
                    <!-- Datos tarde -->
                    <td class="text-center">' . ($tardanzas['tarde'] ? $tardanzas['tarde']['hora_entrada'] : '-') . '</td>
                    <td class="text-center">' . ($tardanzas['tarde'] ? $tardanzas['tarde']['hora_marcada'] : '-') . '</td>
                    <td class="text-center" style="color: ' . (($tardanzas['tarde'] && $tardanzas['tarde']['tardanza'] != '00:00') ? '#d32f2f' : '#2e7d32') . ';">' . ($tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-') . '</td>
                    </tr><tr class="' . $rowClass . '">';
                }
                break;
                
            case 'permission':
                echo '<td class="text-center">' . htmlspecialchars($row['dni']) . '</td>
                    <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
                    <td>' . htmlspecialchars($row['area']) . '</td>
                    <td>' . htmlspecialchars($row['puesto']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['tipo_personal']) . '</td>
                    <td class="text-center nowrap">' . date('d/m/Y', strtotime($row['fecha_permiso'])) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['tipo_permiso']) . '</td>
                    <td>' . htmlspecialchars($row['motivo']) . '</td>
                    <td class="text-center">' . $row['hora_salida'] . '</td>
                    <td class="text-center">' . $row['hora_retorno'] . '</td>
                    <td class="text-center">' . $row['hora_registro'] . '</td>';
                break;
                
            case 'consolidated_area':
                echo '<td>' . htmlspecialchars($row['area']) . '</td>
                    <td>' . htmlspecialchars($row['cargo']) . '</td>
                    <td class="text-center">' . $row['total_empleados'] . '</td>
                    <td class="text-center">' . $row['total_dias'] . '</td>
                    <td class="text-center">' . $row['total_asistencias'] . '</td>
                    <td class="text-center">' . $row['total_permisos'] . '</td>
                    <td class="text-center">' . $row['total_tardanzas'] . '</td>
                    <td class="text-center">' . $row['total_faltas'] . '</td>
                    <td class="text-center" style="font-weight: bold; color: ' . ($row['porcentaje_asistencia'] >= 90 ? '#2e7d32' : ($row['porcentaje_asistencia'] >= 70 ? '#f57c00' : '#d32f2f')) . ';">' . $row['porcentaje_asistencia'] . '%</td>';
                break;
                
            case 'consolidated_person':
                echo '<td class="text-center">' . htmlspecialchars($row['dni']) . '</td>
                    <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
                    <td>' . htmlspecialchars($row['area']) . '</td>
                    <td>' . htmlspecialchars($row['puesto']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['tipo_personal']) . '</td>
                    <td class="text-center">' . $row['total_dias'] . '</td>
                    <td class="text-center">' . $row['asistencias'] . '</td>
                    <td class="text-center">' . $row['permisos'] . '</td>
                    <td class="text-center">' . $row['faltas'] . '</td>
                    <td class="text-center">' . $row['tardanzas'] . '</td>
                    <td class="text-center" style="font-weight: bold; color: ' . ($row['porcentaje_asistencia'] >= 90 ? '#2e7d32' : ($row['porcentaje_asistencia'] >= 70 ? '#f57c00' : '#d32f2f')) . ';">' . $row['porcentaje_asistencia'] . '%</td>';
                break;
                
            default: // daily
                echo '<td class="text-center nowrap">' . $row['fecha'] . '</td>
                    <td class="text-center">' . htmlspecialchars($row['dni']) . '</td>
                    <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
                    <td>' . htmlspecialchars($row['area']) . '</td>
                    <td>' . htmlspecialchars($row['puesto']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['tipo_personal']) . '</td>';
                
                if ($row['tipo_personal'] === 'Administrativo') {
                    echo '<td class="text-center">' . ($row['entrada_manana_reg'] ?? '-') . '</td>
                        <td class="text-center">' . ($row['salida_manana_reg'] ?? '-') . '</td>
                        <td class="text-center">' . ($row['entrada_tarde_reg'] ?? '-') . '</td>
                        <td class="text-center">' . ($row['salida_tarde_reg'] ?? '-') . '</td>';
                } else {
                    echo '<td class="text-center">-</td><td class="text-center">-</td><td class="text-center">-</td><td class="text-center">-</td>';
                }
                
                echo '<td class="text-center">' . (!empty($row['registros_dia']) ? implode(', ', $row['registros_dia']) : '-') . '</td>';
                break;
        }
        
        echo '</tr>';
        
        // Detalle para reporte consolidado por persona
        if ($reportType === 'consolidated_person' && isset($row['detalle_asistencias'])) {
            echo '<tr class="detail-row">
                <td colspan="12" style="border: 1.5pt solid black; padding: 10px;">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Registros</th>
                                <th>Permiso</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($row['detalle_asistencias'] as $detalle) {
                echo '<tr>
                    <td class="text-center nowrap">' . $detalle['fecha'] . '</td>
                    <td class="text-center">';
                
                if ($detalle['tipo'] === 'Asistencia') {
                    echo '<span class="badge badge-success">Asistencia</span>';
                } elseif ($detalle['tipo'] === 'Permiso') {
                    echo '<span class="badge badge-info">Permiso</span>';
                } else {
                    echo '<span class="badge badge-danger">Falta</span>';
                }
                
                echo '</td>
                    <td class="text-center">' . (!empty($detalle['registros']) ? implode(', ', $detalle['registros']) : '-') . '</td>
                    <td class="text-center">' . ($detalle['permiso'] ?? '-') . '</td>
                </tr>';
            }
            
            echo '</tbody>
                    </table>
                </td>
            </tr>';
        }
        
        $rowCount++;
    }
    
    echo '</table>';
    
    // Pie de página
    echo '<div class="generated">Generado el ' . date('d/m/Y H:i:s') . ' | Sistema de Asistencia</div>';
    echo '</body></html>';
    exit;
}

// Procesar búsqueda de empleados para el modal (AJAX)
if (isset($_GET['search_employees'])) {
    $term = $_GET['term'] ?? '';
    $results = $reportGenerator->searchEmployees($term);
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// Obtener datos de empleado para edición
if (isset($_GET['get_employee'])) {
    $id = $_GET['get_employee'];
    $employee = $reportGenerator->getEmployeeById($id);
    header('Content-Type: application/json');
    echo json_encode($employee);
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Asistencia</title>
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
            --success: #4CAF50;
            --error: #F44336;
            --warning: #FFC107;
            --info: #2196F3;
            --dark: #212121;
            --light: #F5F5F5;
            --white: #FFFFFF;
            --gray: #9E9E9E;
            --text-dark: #333333;
            --text-light: #757575;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            position: fixed;
            height: 100%;
            padding: 1.5rem 0;
            transition: var(--transition);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 1.5rem;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: var(--primary);
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info .btn-logout {
            background-color: var(--error);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        .user-info .btn-logout:hover {
            background-color: #d32f2f;
        }
        
        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--text-dark);
        }
        
        select, input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            background-color: var(--white);
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(138, 21, 56, 0.2);
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.7rem 1.25rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #388E3C;
        }
        
        .btn-excel {
            background-color: #1D6F42;
        }
        
        .btn-excel:hover {
            background-color: #165834;
        }
        
        .btn-danger {
            background-color: var(--error);
        }
        
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 1rem;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            font-size: 0.9rem;
        }
        
        th, td {
            padding: 0.6rem;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            font-size: 0.85rem;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: rgba(138, 21, 56, 0.05);
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: var(--success);
        }
        
        .badge-warning {
            background-color: #fff8e1;
            color: var(--warning);
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: var(--error);
        }
        
        .badge-info {
            background-color: #e3f2fd;
            color: var(--info);
        }
        
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            width: 80px;
        }
        
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            gap: 0.5rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .detail-row {
            background-color: #f9f9f9;
        }
        
        .detail-row td {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        
        .detail-table {
            width: 100%;
            margin-top: 0.5rem;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .detail-table th, .detail-table td {
            padding: 0.4rem;
            border: 1px solid #ddd;
        }
        
        .detail-table th {
            background-color: #9b2020ff;
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
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 800px;
            position: relative;
            top: 10%;
            transform: translateY(-10%);
        }
        
        .modal-lg {
            max-width: 95%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 1.25rem;
            color: var(--primary);
            margin: 0;
        }
        
        .close {
            color: #aaa;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        .employee-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin-bottom: 1rem;
        }
        
        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f0f0f0;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd;
}

/* Estilos para el grid de formulario */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.search-container {
    margin-bottom: 1rem;
}

.search-input {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: 'Poppins', sans-serif;
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(138, 21, 56, 0.2);
}

/* Estilos para mensajes flash */
.flash-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    z-index: 1100;
    box-shadow: var(--shadow-lg);
    animation: fadeIn 0.3s, fadeOut 0.5s 1.5s forwards;
}

.flash-success {
    background-color: var(--success);
}

.flash-error {
    background-color: var(--error);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(-20px); }
}

/* Estilos para confirmación de eliminación */
.confirm-modal {
    text-align: center;
}

.confirm-modal .modal-content {
    max-width: 500px;
}

.confirm-modal .icon {
    font-size: 3rem;
    color: var(--error);
    margin-bottom: 1rem;
}

.confirm-modal .message {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}

.confirm-modal .buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Estilos para registros verticales */
.registros-vertical {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.registro-item {
    padding: 0.25rem 0.5rem;
    background-color: #f0f0f0;
    border-radius: 4px;
    font-size: 0.8rem;
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
    
    .filters-form {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}

@media (max-width: 768px) {
    .filters-form {
        grid-template-columns: 1fr 1fr;
    }
    
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
     .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .filters-form {
        grid-template-columns: 1fr;
    }
    
    .main-content {
        padding: 1rem;
    }
    
    .modal-content {
        margin: 15% auto;
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
            <a href="?section=dashboard&report_type=daily" class="menu-item <?= $section === 'dashboard' && (!isset($_GET['report_type']) || $_GET['report_type'] === 'daily') ? 'active' : '' ?>">
                <i class="fas fa-calendar-day"></i> <span>Diario</span>
            </a>
            <a href="?section=dashboard&report_type=tardiness" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'tardiness' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> <span>Tardanzas</span>
            </a>
             <a href="?section=dashboard&report_type=permission" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'permission' ? 'active' : '' ?>">
                <i class="fas fa-file-signature"></i> <span>Permisos</span>
            </a>
           <a href="?section=dashboard&report_type=consolidated_area" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'consolidated_area' ? 'active' : '' ?>">
    <i class="fas fa-building"></i> <span>Por Áreas</span>
</a>
                
           
            <a href="?section=dashboard&report_type=consolidated_person" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'consolidated_person' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>Por Personas</span>
            </a>
            
            <?php if ($isSuperAdmin): ?>
                <a href="?section=empleados" class="menu-item <?= $section === 'empleados' ? 'active' : '' ?>">
                     <i class="fas fa-user-tie"></i> <span>Empleados</span>
                </a>
                <a href="?section=administradores" class="menu-item <?= $section === 'administradores' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i> <span>Administradores</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>
                <?php if ($section === 'dashboard'): ?>
                    <i class="fas fa-clipboard-list"></i> <?= $reportTitle ?>
                <?php elseif ($section === 'empleados'): ?>
                    <i class="fas fa-user-tie"></i> Gestión de Empleados
                <?php elseif ($section === 'administradores'): ?>
                    <i class="fas fa-users-cog"></i> Gestión de Administradores
                <?php endif; ?>
            </h1>
            <div class="user-info">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['admin_username']) ?> (<?= $_SESSION['admin_role'] === 'admin' ? 'Administrador' : 'Supervisor' ?>)</span>
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
        
        <?php if ($section === 'dashboard'): ?>
            <div class="card">
                <form method="get" id="filter-form">
                    <input type="hidden" name="section" value="dashboard">
                    <input type="hidden" name="report_type" value="<?= $reportType ?>">
                    
                    <div class="filters-form">
                        <div class="form-group">
                            <label for="fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>" required>
                        </div>

                        <div class="form-group">
    <label for="fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin</label>
    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>" required>
</div>
<div class="form-group">
    <label for="area"><i class="fas fa-sitemap"></i> Área</label>
    <select id="area" name="area">
        <option value="">Todas</option>
        <?php foreach ($AREAS_PREDEFINIDAS as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>" <?= $area === $a ? 'selected' : '' ?>>
                <?= htmlspecialchars($a) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="cargo"><i class="fas fa-briefcase"></i> Cargo</label>
    <select id="cargo" name="cargo">
        <option value="">Todos</option>
        <?php 
        // Si hay un área seleccionada, mostrar solo los cargos de esa área
        if ($area && isset($CARGOS_POR_AREA[$area])) {
            foreach ($CARGOS_POR_AREA[$area] as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= $cargo === $p ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p) ?>
                </option>
            <?php endforeach;
        } else {
            // Mostrar todos los cargos si no hay área seleccionada
            $todos_los_cargos = [];
            foreach ($CARGOS_POR_AREA as $cargos) {
                $todos_los_cargos = array_merge($todos_los_cargos, $cargos);
            }
            $todos_los_cargos = array_unique($todos_los_cargos);
            sort($todos_los_cargos);
            
            foreach ($todos_los_cargos as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>" <?= $cargo === $p ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p) ?>
                </option>
            <?php endforeach;
        }
        ?>
    </select>
</div>

<div class="form-group">
    <label for="tipo_personal"><i class="fas fa-user-tag"></i> Tipo Personal</label>
    <select id="tipo_personal" name="tipo_personal">
        <option value="">Todos</option>
        <option value="Docente" <?= $tipoPersonal === 'Docente' ? 'selected' : '' ?>>Docente</option>
        <option value="Administrativo" <?= $tipoPersonal === 'Administrativo' ? 'selected' : '' ?>>Administrativo</option>
    </select>
</div>

<div class="form-group">
    <label for="busqueda"><i class="fas fa-search"></i> Buscar</label>
    <input type="text" id="busqueda" name="busqueda" value="<?= htmlspecialchars($busqueda ?? '') ?>" placeholder="DNI o nombre">
</div>
</div>

<div class="action-buttons">
    <?php if ($isSuperAdmin && $reportType === 'consolidated_person'): ?>
        <button type="button" class="btn btn-success" onclick="openManualAttendanceModal()">
            <i class="fas fa-plus"></i> Registrar Asistencia
        </button>
    <?php endif; ?>
    
    <a href="?<?= http_build_query(array_merge($_GET, ['export_excel' => 1])) ?>" class="btn btn-excel">
        <i class="fas fa-file-excel"></i> Exportar Excel
    </a>
</div>
</form>
</div>

<div class="card">
    <h2 class="card-title"><i class="fas fa-table"></i> Resultados</h2>
    
    <div class="table-responsive">
        <?php if (!empty($paginatedData)): ?>
            <table>
                <thead>
                    <tr>
                        <?php switch ($reportType): 
                            case 'tardiness': ?>
                                <th>DNI</th>
                                <th>NOMBRE</th>
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
                                <th>NOMBRE</th>
                                <th>ÁREA</th>
                                <th>CARGO</th>
                                <th>TIPO</th>
                                <th>FECHA</th>
                                <th>TIPO PERMISO</th>
                                <th>MOTIVO</th>
                                <th>SALIDA</th>
                                <th>RETORNO</th>
                                <th>REGISTRO</th>
                                <?php break; ?>
                                
                            <?php case 'consolidated_area': ?>
                                <th>ÁREA</th>
                                <th>CARGO</th>
                                <th>EMPLEADOS</th>
                                <th>DÍAS</th>
                                <th>ASISTENCIAS</th>
                                <th>PERMISOS</th>
                                <th>TARDANZAS</th>
                                <th>FALTAS</th>
                                <th>% ASISTENCIA</th>
                                <?php break; ?>
                                
                            <?php case 'consolidated_person': ?>
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>ÁREA</th>
                                <th>CARGO</th>
                                <th>TIPO</th>
                                <th>DÍAS</th>
                                <th>ASISTENCIAS</th>
                                <th>PERMISOS</th>
                                <th>FALTAS</th>
                                <th>TARDANZAS</th>
                                <th>% ASISTENCIA</th>
                                <?php break; ?>
                                
                            <?php default: // daily ?>
                                <th>FECHA</th>
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>ÁREA</th>
                                <th>CARGO</th>
                                <th>TIPO</th>
                                <th>ENT. MAÑANA</th>
                                <th>SAL. MAÑANA</th>
                                <th>ENT. TARDE</th>
                                <th>SAL. TARDE</th>
                                <th>REGISTROS</th>
                        <?php endswitch; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedData as $row): ?>
                        <tr>
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
                                    foreach ($tardanzasCombinadas as $fecha => $tardanzas): ?>
                                        <td><?= htmlspecialchars($row['dni']) ?></td>
                                        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                        <td><?= $fecha ?></td>
                                        
                                        <!-- Datos mañana -->
                                        <td><?= $tardanzas['manana'] ? $tardanzas['manana']['hora_entrada'] : '-' ?></td>
                                        <td><?= $tardanzas['manana'] ? $tardanzas['manana']['hora_marcada'] : '-' ?></td>
                                        <td><?= $tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-' ?></td>
                                        
                                        <!-- Datos tarde -->
                                        <td><?= $tardanzas['tarde'] ? $tardanzas['tarde']['hora_entrada'] : '-' ?></td>
                                        <td><?= $tardanzas['tarde'] ? $tardanzas['tarde']['hora_marcada'] : '-' ?></td>
                                        <td><?= $tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-' ?></td>
                                        </tr><tr>
                                    <?php endforeach; ?>
                                    <?php break; ?>
                                    
                                <?php case 'permission': ?>
                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($row['area']) ?></td>
                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_personal']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_permiso'])) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_permiso']) ?></td>
                                    <td><?= htmlspecialchars($row['motivo']) ?></td>
                                    <td><?= $row['hora_salida'] ?></td>
                                    <td><?= $row['hora_retorno'] ?></td>
                                    <td><?= $row['hora_registro'] ?></td>
                                    <?php break; ?>
                                    
                                <?php case 'consolidated_area': ?>
                                    <td><?= htmlspecialchars($row['area']) ?></td>
                                    <td><?= htmlspecialchars($row['cargo']) ?></td>
                                    <td><?= $row['total_empleados'] ?></td>
                                    <td><?= $row['total_dias'] ?></td>
                                    <td><?= $row['total_asistencias'] ?></td>
                                    <td><?= $row['total_permisos'] ?></td>
                                    <td><?= $row['total_tardanzas'] ?></td>
                                    <td><?= $row['total_faltas'] ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span><?= $row['porcentaje_asistencia'] ?>%</span>
                                            <div class="progress-bar">
                                                <div class="progress-bar-fill" style="width: <?= $row['porcentaje_asistencia'] ?>%; background: <?= 
                                                    $row['porcentaje_asistencia'] >= 90 ? '#4CAF50' : 
                                                    ($row['porcentaje_asistencia'] >= 70 ? '#FFC107' : '#F44336')
                                                ?>;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php break; ?>
                                    
                                <?php case 'consolidated_person': ?>
                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($row['area']) ?></td>
                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_personal']) ?></td>
                                    <td><?= $row['total_dias'] ?></td>
                                    <td><?= $row['asistencias'] ?></td>
                                    <td><?= $row['permisos'] ?></td>
                                    <td><?= $row['faltas'] ?></td>
                                    <td><?= $row['tardanzas'] ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span><?= $row['porcentaje_asistencia'] ?>%</span>
                                            <div class="progress-bar">
                                                <div class="progress-bar-fill" style="width: <?= $row['porcentaje_asistencia'] ?>%; background: <?= 
                                                    $row['porcentaje_asistencia'] >= 90 ? '#4CAF50' : 
                                                    ($row['porcentaje_asistencia'] >= 70 ? '#FFC107' : '#F44336')
                                                ?>;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php break; ?>
                                    
                                <?php default: // daily ?>
                                    <td><?= $row['fecha'] ?></td>
                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                    <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($row['area']) ?></td>
                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_personal']) ?></td>
                                    
                                    <?php if ($row['tipo_personal'] === 'Administrativo'): ?>
                                        <td><?= ($row['entrada_manana_reg'] ?? '-') ?></td>
                                        <td><?= ($row['salida_manana_reg'] ?? '-') ?></td>
                                        <td><?= ($row['entrada_tarde_reg'] ?? '-') ?></td>
                                        <td><?= ($row['salida_tarde_reg'] ?? '-') ?></td>
                                    <?php else: ?>
                                        <td>-</td><td>-</td><td>-</td><td>-</td>
                                    <?php endif; ?>
                                    
                                    <td>
                                        <?php if (!empty($row['registros_dia'])): ?>
                                            <div class="registros-vertical">
                                                <?php foreach ($row['registros_dia'] as $registro): ?>
                                                    <div class="registro-item"><?= $registro ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                            <?php endswitch; ?>
                        </tr>
                        
                        <?php if ($reportType === 'consolidated_person' && isset($row['detalle_asistencias'])): ?>
                        <tr class="detail-row">
                            <td colspan="12">
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Registros</th>
                                            <th>Permiso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($row['detalle_asistencias'] as $detalle): ?>
                                        <tr>
                                            <td><?= $detalle['fecha'] ?></td>
                                            <td>
                                                <?php if ($detalle['tipo'] === 'Asistencia'): ?>
                                                    <span class="badge badge-success">Asistencia</span>
                                                <?php elseif ($detalle['tipo'] === 'Permiso'): ?>
                                                    <span class="badge badge-info">Permiso</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Falta</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($detalle['registros'])): ?>
                                                    <div class="registros-vertical">
                                                        <?php foreach ($detalle['registros'] as $registro): ?>
                                                            <div class="registro-item"><?= $registro ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $detalle['permiso'] ?? '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
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
        <p>No se encontraron datos con los filtros seleccionados</p>
    </div>
<?php endif; ?>
</div>
</div>
<?php elseif ($section === 'empleados' && $isSuperAdmin): ?>
<div class="card">
    <div class="action-buttons">
        <button type="button" class="btn btn-success" onclick="openAddEmployeeModal()">
            <i class="fas fa-user-plus"></i> Agregar Empleado
        </button>
    </div>
    
    <div class="search-container">
        <input type="text" id="search-employee" class="search-input" placeholder="Buscar empleado por nombre o DNI..." onkeyup="filterEmployees()">
    </div>
    
    <h2 class="card-title"><i class="fas fa-user-tie"></i> Lista de Empleados</h2>
    
    <div class="table-responsive">
        <?php if (!empty($employees)): ?>
            <table id="employees-table">
                <thead>
                    <tr>
                        <th>FOTO</th>
                        <th>DNI</th>
                        <th>NOMBRES</th>
                        <th>APELLIDOS</th>
                        <th>ÁREA</th>
                        <th>CARGO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <?php if (isset($emp['dni']) && isset($emp['nombres']) && isset($emp['apellidos'])): ?>
                            <tr class="employee-row" data-search="<?= strtolower(htmlspecialchars($emp['dni'] . ' ' . $emp['nombres'] . ' ' . $emp['apellidos'])) ?>">
                                <td>
                                    <?php if (!empty($emp['foto']) && file_exists($emp['foto'])): ?>
                                        <img src="<?= $emp['foto'] ?>" alt="Foto" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle" style="font-size: 2rem; color: #ccc;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($emp['dni']) ?></td>
                                <td><?= htmlspecialchars($emp['nombres']) ?></td>
                                <td><?= htmlspecialchars($emp['apellidos']) ?></td>
                                <td><?= htmlspecialchars($emp['area'] ?? '') ?></td>
                                <td><?= htmlspecialchars($emp['puesto'] ?? '') ?></td>
                                <td>
                                    <button type="button" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="openEditEmployeeModal(<?= $emp['id'] ?>)">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="confirmDeleteEmployee(<?= $emp['id'] ?>, '<?= htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']) ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                <p>No hay empleados registrados</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php elseif ($section === 'administradores' && $isSuperAdmin): ?>
<div class="card">
    <div class="action-buttons">
        <button type="button" class="btn btn-success" onclick="openAddAdminModal()">
            <i class="fas fa-user-plus"></i> Agregar Administrador
        </button>
    </div>
    
    <div class="search-container">
        <input type="text" id="search-admin" class="search-input" placeholder="Buscar administrador por nombre..." onkeyup="filterAdmins()">
    </div>
    
    <h2 class="card-title"><i class="fas fa-users-cog"></i> Lista de Administradores</h2>
    
    <div class="table-responsive">
        <?php if (!empty($admins)): ?>
            <table id="admins-table">
                <thead>
                    <tr>
                        <th>USUARIO</th>
                        <th>ROL</th>
                        <th>FECHA CREACIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr class="admin-row" data-search="<?= strtolower(htmlspecialchars($admin['usuario'])) ?>">
                            <td><?= htmlspecialchars($admin['usuario']) ?></td>
                            <td><?= $admin['rol'] === 'admin' ? 'Administrador' : 'Supervisor' ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($admin['fecha_creacion'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="openEditAdminModal(<?= $admin['id'] ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                    <button type="button" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="confirmDeleteAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['usuario']) ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-info">Tu cuenta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>
                <p>No hay administradores registrados</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
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
                    <select id="empleado_id" name="empleado_id" class="employee-select" required style="width: 100%">
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
                    <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" name="hora" value="<?= date('H:i') ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('manualAttendanceModal')">Cancelar</button>
                <button type="submit" class="btn btn-success" name="registrar_asistencia">Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar/editar empleado -->
<div id="employeeModal" class="modal">
    <div class="modal-content modal-lg" style="top: 15%; transform: translateY(-15%);">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-plus"></i> 
                <span id="employeeModalTitle">Agregar Nuevo Empleado</span>
            </h3>
            <span class="close" onclick="closeModal('employeeModal')">&times;</span>
        </div>
        <form method="post" action="" enctype="multipart/form-data" id="employeeForm">
            <input type="hidden" name="id" id="employee_id" value="">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div style="text-align: center; grid-column: 1 / -1;">
                        <img id="employeePhotoPreview" src="" alt="Foto" class="employee-photo" style="display: none;">
                        <i id="employeePhotoPlaceholder" class="fas fa-user-circle employee-photo" style="font-size: 100px; color: #ccc;"></i>
                        
                        <div class="form-group">
                            <label for="foto">Foto (Opcional)</label>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewEmployeePhoto(this)">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <input type="text" id="dni" name="dni" required maxlength="8" pattern="[0-9]{8}" title="Ingrese un DNI válido (8 dígitos)" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="nombres">Nombres</label>
                        <input type="text" id="nombres" name="nombres" required value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="area">Área</label>
                        <select id="area" name="area" required>
                            <option value="">Seleccione un área</option>
                            <?php foreach ($AREAS_PREDEFINIDAS as $a): ?>
                                <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="puesto">Cargo</label>
                        <select id="puesto" name="puesto" required>
                            <option value="">Seleccione un cargo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="inicio_contrato">Inicio de Contrato</label>
                        <input type="date" id="inicio_contrato" name="inicio_contrato" required value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="fin_contrato">Fin de Contrato</label>
                        <input type="date" id="fin_contrato" name="fin_contrato" required value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_personal">Tipo de Personal</label>
                        <select id="tipo_personal" name="tipo_personal" required>
                            <option value="Docente">Docente</option>
                            <option value="Administrativo">Administrativo</option>
                        </select>
                    </div>
                    
                    <div id="horarios-container" style="grid-column: 1 / -1; display: none;">
                        <h4>Horarios</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="form-group">
                                <label for="entrada_manana">Entrada Mañana</label>
                                <input type="time" id="entrada_manana" name="entrada_manana" value="08:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="salida_manana">Salida Mañana</label>
                                <input type="time" id="salida_manana" name="salida_manana" value="13:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="entrada_tarde">Entrada Tarde</label>
                                <input type="time" id="entrada_tarde" name="entrada_tarde" value="14:00">
                            </div>
                            
                            <div class="form-group">
                                <label for="salida_tarde">Salida Tarde</label>
                                <input type="time" id="salida_tarde" name="salida_tarde" value="18:00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('employeeModal')">Cancelar</button>
                <button type="submit" class="btn btn-success" name="agregar_empleado" id="employeeSubmitBtn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar/editar administrador -->
<div id="adminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-plus"></i> 
                <span id="adminModalTitle">Agregar Administrador</span>
            </h3>
            <span class="close" onclick="closeModal('adminModal')">&times;</span>
        </div>
        <form method="post" action="" id="adminForm">
            <input type="hidden" name="id" id="admin_id" value="">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required value="">
                </div>
                
                <div class="form-group">
                    <label for="contrasena" id="contrasenaLabel">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" required>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adminModal')">Cancelar</button>
                <button type="submit" class="btn btn-success" name="agregar_admin" id="adminSubmitBtn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para eliminar empleado -->
<div id="confirmDeleteEmployeeModal" class="modal">
    <div class="modal-content confirm-modal">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Eliminación</h3>
            <span class="close" onclick="closeModal('confirmDeleteEmployeeModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="message">
                ¿Estás seguro de eliminar al empleado <strong id="deleteEmployeeName"></strong>?
            </div>
            <div class="buttons">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirmDeleteEmployeeModal')">Cancelar</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteEmployeeBtn">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar administrador -->
<div id="confirmDeleteAdminModal" class="modal">
    <div class="modal-content confirm-modal">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Eliminación</h3>
            <span class="close" onclick="closeModal('confirmDeleteAdminModal')">&times;</span>
        </div>

         <div class="modal-body">
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="message">
                ¿Estás seguro de eliminar al administrador <strong id="deleteAdminName"></strong>?
            </div>
            <div class="buttons">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirmDeleteAdminModal')">Cancelar</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteAdminBtn">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    // Inicializar Select2 para el select de empleados
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
                    // Validar que los datos tengan la estructura esperada
                    var validData = data.filter(function(item) {
                        return item && item.id && item.nombre_completo && item.dni;
                    });
                    
                    return {
                        results: validData.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nombre_completo + ' (' + item.dni + ')'
                            };
                        })
                    };
                },
                cache: true
            }
        });
        
        // Actualizar cargos cuando se cambie el área
        $('#area').change(function() {
            var area = $(this).val();
            var cargoSelect = $('#puesto');
            cargoSelect.empty().append('<option value="">Seleccione un cargo</option>');
            
            if (area && window.CARGOS_POR_AREA && window.CARGOS_POR_AREA[area]) {
                window.CARGOS_POR_AREA[area].forEach(function(cargo) {
                    cargoSelect.append('<option value="' + cargo + '">' + cargo + '</option>');
                });
            }
        });
        
        // Mostrar/ocultar horarios según tipo de personal
        $('#tipo_personal').change(function() {
            if ($(this).val() === 'Administrativo') {
                $('#horarios-container').show();
            } else {
                $('#horarios-container').hide();
            }
        });
        
        // Auto-ocultar mensajes flash después de 1.5 segundos
        setTimeout(function() {
            var flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.style.display = 'none';
            }
        }, 1500);
    });
    
    // Pasar las variables PHP a JavaScript
    var CARGOS_POR_AREA = <?= json_encode($CARGOS_POR_AREA) ?>;
    
    // Actualizar automáticamente al cambiar filtros
    document.querySelectorAll('#filter-form select, #filter-form input').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
    
    // Para el campo de búsqueda, esperar un poco después de escribir
    let searchTimeout;
    document.getElementById('busqueda').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filter-form').submit();
        }, 500);
    });
    
    // Filtrar empleados en la tabla
    function filterEmployees() {
        const input = document.getElementById('search-employee');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('#employees-table .employee-row');
        
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search');
            if (searchText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Filtrar administradores en la tabla
    function filterAdmins() {
        const input = document.getElementById('search-admin');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('#admins-table .admin-row');
        
        rows.forEach(row => {
            const searchText = row.getAttribute('data-search');
            if (searchText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Funciones para los modales
    function openManualAttendanceModal() {
        document.getElementById('manualAttendanceModal').style.display = 'block';
    }
    
    function openAddEmployeeModal() {
        document.getElementById('employeeModalTitle').textContent = 'Agregar Nuevo Empleado';
        document.getElementById('employeeForm').reset();
        document.getElementById('employee_id').value = '';
        document.getElementById('employeeSubmitBtn').name = 'agregar_empleado';
        document.getElementById('employeePhotoPreview').style.display = 'none';
        document.getElementById('employeePhotoPlaceholder').style.display = 'block';
        
        // Resetear el select de cargos
        $('#puesto').empty().append('<option value="">Seleccione un cargo</option>');
        
        document.getElementById('employeeModal').style.display = 'block';
    }
    
    function openEditEmployeeModal(id) {
        // Hacer una solicitud AJAX para obtener los datos del empleado
        fetch('?get_employee=' + id)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('employeeModalTitle').textContent = 'Editar Empleado';
                    document.getElementById('employee_id').value = data.id;
                    document.getElementById('dni').value = data.dni || '';
                    document.getElementById('nombres').value = data.nombres || '';
                    document.getElementById('apellidos').value = data.apellidos || '';
                    document.getElementById('area').value = data.area || '';
                    
                    // Actualizar cargos según el área seleccionada
                    var area = data.area || '';
                    var cargoSelect = $('#puesto');
                    cargoSelect.empty().append('<option value="">Seleccione un cargo</option>');
                    
                    if (area && window.CARGOS_POR_AREA && window.CARGOS_POR_AREA[area]) {
                        window.CARGOS_POR_AREA[area].forEach(function(cargo) {
                            cargoSelect.append('<option value="' + cargo + '" ' + (cargo === data.puesto ? 'selected' : '') + '>' + cargo + '</option>');
                        });
                    } else if (data.puesto) {
                        cargoSelect.append('<option value="' + data.puesto + '" selected>' + data.puesto + '</option>');
                    }
                    
                    document.getElementById('inicio_contrato').value = data.inicio_contrato || '';
                    document.getElementById('fin_contrato').value = data.fin_contrato || '';
                    document.getElementById('tipo_personal').value = data.tipo_personal || 'Docente';
                    
                    // Mostrar/ocultar horarios según tipo de personal
                    if (data.tipo_personal === 'Administrativo') {
                        document.getElementById('horarios-container').style.display = 'grid';
                        document.getElementById('entrada_manana').value = data.entrada_manana || '08:00';
                        document.getElementById('salida_manana').value = data.salida_manana || '13:00';
                        document.getElementById('entrada_tarde').value = data.entrada_tarde || '14:00';
                        document.getElementById('salida_tarde').value = data.salida_tarde || '18:00';
                    } else {
                        document.getElementById('horarios-container').style.display = 'none';
                    }
                    
                    // Mostrar foto si existe
                    if (data.foto && data.foto !== 'null') {
                        document.getElementById('employeePhotoPreview').src = data.foto;
                        document.getElementById('employeePhotoPreview').style.display = 'block';
                        document.getElementById('employeePhotoPlaceholder').style.display = 'none';
                    } else {
                        document.getElementById('employeePhotoPreview').style.display = 'none';
                        document.getElementById('employeePhotoPlaceholder').style.display = 'block';
                    }
                    
                    document.getElementById('employeeSubmitBtn').name = 'editar_empleado';
                    document.getElementById('employeeModal').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del empleado');
            });
    }
    
    function previewEmployeePhoto(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('employeePhotoPreview').src = e.target.result;
                document.getElementById('employeePhotoPreview').style.display = 'block';
                document.getElementById('employeePhotoPlaceholder').style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function openAddAdminModal() {
        document.getElementById('adminModalTitle').textContent = 'Agregar Administrador';
        document.getElementById('adminForm').reset();
        document.getElementById('admin_id').value = '';
        document.getElementById('contrasenaLabel').textContent = 'Contraseña';
        document.getElementById('contrasena').required = true;
        document.getElementById('adminSubmitBtn').name = 'agregar_admin';
        document.getElementById('adminModal').style.display = 'block';
    }
    
    function openEditAdminModal(id) {
        // Hacer una solicitud AJAX para obtener los datos del administrador
        fetch('?get_admin=' + id)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('adminModalTitle').textContent = 'Editar Administrador';
                    document.getElementById('admin_id').value = data.id;
                    document.getElementById('usuario').value = data.usuario || '';
                    document.getElementById('rol').value = data.rol || 'admin';
                    document.getElementById('contrasenaLabel').textContent = 'Nueva Contraseña (dejar en blanco para no cambiar)';
                    document.getElementById('contrasena').required = false;
                    document.getElementById('adminSubmitBtn').name = 'editar_admin';
                    document.getElementById('adminModal').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del administrador');
            });
    }
    
    function confirmDeleteEmployee(id, name) {
        document.getElementById('deleteEmployeeName').textContent = name;
        document.getElementById('confirmDeleteEmployeeBtn').href = '?section=empleados&eliminar_empleado=' + id;
        document.getElementById('confirmDeleteEmployeeModal').style.display = 'block';
    }
    
    function confirmDeleteAdmin(id, name) {
        document.getElementById('deleteAdminName').textContent = name;
        document.getElementById('confirmDeleteAdminBtn').href = '?section=administradores&eliminar_admin=' + id;
        document.getElementById('confirmDeleteAdminModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Cerrar modal al hacer clic fuera de él
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    }
</script>
</body>
</html>