<?php
// Configuración de tiempo de sesión - REACTIVADO EL TIEMPO DE 15 MINUTOS
ini_set('session.gc_maxlifetime', 900);
session_set_cookie_params(900);

include 'bd.php';
// Crear directorio de fotos si no existe
if (!file_exists(FOTO_DIR)) {
    mkdir(FOTO_DIR, 0777, true);
}

date_default_timezone_set(APP_TIMEZONE);

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
    'Mantenimiento',
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
$isEspectador = ($_SESSION['admin_role'] === 'espectador'); // NUEVO ROL ESPECTADOR

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
        header("Location: ".$_SERVER['PHP_SELF']."?section=trabajadores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al agregar empleado: " . $e->getMessage();
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
        header("Location: ".$_SERVER['PHP_SELF']."?section=trabajadores");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar empleado: " . $e->getMessage();
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
        header("Location: ".$_SERVER['PHP_SELF']."?section=trabajadores");
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

// Procesar agregar administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_admin']) && $isSuperAdmin) {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    // Validar longitud de contraseña (mínimo 4 caracteres)
    if (strlen($password) < 4) {
        $_SESSION['error_message'] = "La contraseña debe tener al menos 4 caracteres";
        header("Location: ".$_SERVER['PHP_SELF']."?section=admins");
        exit;
    }
    
    $password = password_hash($password, PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios_admin (nombres, apellidos, area, cargo, usuario, password, rol, estado, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', ?)");
        $stmt->execute([$nombres, $apellidos, $area, $cargo, $usuario, $password, $rol, $_SESSION['admin_id']]);
        
        // Registrar en historial
        registrarHistorial('usuarios_admin', $usuario, 'INSERT', 'nuevo_admin', null, "$apellidos $nombres", "Creación de nuevo administrador", $pdo);
        
        $_SESSION['success_message'] = "Administrador agregado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=admins");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al agregar administrador: " . $e->getMessage();
    }
}

// Procesar actualización de administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_admin']) && $isSuperAdmin) {
    $admin_id = $_POST['admin_id'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $usuario = $_POST['usuario'];
    $rol = $_POST['rol'];
    
    // Obtener datos anteriores para el historial
    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_anterior = $stmt->fetch();
    
    // Manejar la contraseña (solo actualizar si se proporciona una nueva)
    $password_update = "";
    $params = [$nombres, $apellidos, $area, $cargo, $usuario, $rol, $admin_id];
    
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        
        // Validar longitud de contraseña (mínimo 4 caracteres)
        if (strlen($password) < 4) {
            $_SESSION['error_message'] = "La contraseña debe tener al menos 4 caracteres";
            header("Location: ".$_SERVER['PHP_SELF']."?section=admins");
            exit;
        }
        
        $password = password_hash($password, PASSWORD_DEFAULT);
        $password_update = ", password = ?";
        array_splice($params, 5, 0, [$password]);
    }
    
    try {
        $sql = "UPDATE usuarios_admin SET nombres = ?, apellidos = ?, area = ?, cargo = ?, usuario = ?, rol = ? $password_update WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Registrar en historial
        $cambios = [];
        if ($admin_anterior['nombres'] != $nombres) {
            $cambios[] = "nombres: {$admin_anterior['nombres']} -> $nombres";
        }
        if ($admin_anterior['apellidos'] != $apellidos) {
            $cambios[] = "apellidos: {$admin_anterior['apellidos']} -> $apellidos";
        }
        if ($admin_anterior['area'] != $area) {
            $cambios[] = "area: {$admin_anterior['area']} -> $area";
        }
        if ($admin_anterior['cargo'] != $cargo) {
            $cambios[] = "cargo: {$admin_anterior['cargo']} -> $cargo";
        }
        if ($admin_anterior['usuario'] != $usuario) {
            $cambios[] = "usuario: {$admin_anterior['usuario']} -> $usuario";
        }
        if ($admin_anterior['rol'] != $rol) {
            $cambios[] = "rol: {$admin_anterior['rol']} -> $rol";
        }
        if (!empty($_POST['password'])) {
            $cambios[] = "contraseña: [actualizada]";
        }
        
        if (!empty($cambios)) {
            registrarHistorial('usuarios_admin', $usuario, 'UPDATE', 'datos_admin', $admin_anterior['usuario'], $usuario, "Cambios: " . implode(', ', $cambios), $pdo);
        }
        
        $_SESSION['success_message'] = "Administrador actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=admins");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar administrador: " . $e->getMessage();
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
        header("Location: ".$_SERVER['PHP_SELF']."?section=config");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al actualizar configuración: " . $e->getMessage();
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
        
        $_SESSION['success_message'] = "Estado actualizado correctamente";
        header("Location: ".$_SERVER['PHP_SELF']."?section=admins");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al cambiar estado: " . $e->getMessage();
    }
}

// Función para aplicar filtros a los datos
function aplicarFiltros($data, $filtros) {
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
                    case 'busqueda':
                        $valorFila = strtolower(implode(' ', $row));
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

// Función para exportar a Excel - SOLO si NO es espectador
if (isset($_GET['export_excel']) && !$isEspectador) {
    $section = $_GET['section'] ?? 'dashboard';
    $reportType = $_GET['report_type'] ?? 'daily';
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    $exportType = $_GET['export_type'] ?? 'filtered'; // SOLO 'filtered' ahora
    
    // Obtener filtros actuales
    $filtros = [];
    $filtros['area'] = $_GET['filter_area'] ?? '';
    $filtros['cargo'] = $_GET['filter_cargo'] ?? '';
    $filtros['busqueda'] = $_GET['search_term'] ?? '';
    $filtros['estado'] = $_GET['filter_estado'] ?? '';
    
    // Instanciar el generador de reportes
    $reportGenerator = new ReportGenerator($pdo);
    
    // Obtener datos según la sección
    switch ($section) {
        case 'dashboard':
            switch ($reportType) {
                case 'tardiness':
                    $data = $reportGenerator->getTardinessReport($fechaInicio, $fechaFin);
                    $filename = "Reporte_Tardanzas_{$fechaInicio}_al_{$fechaFin}.xls";
                    break;
                case 'permission':
                    $data = $reportGenerator->getPermissionReport($fechaInicio, $fechaFin);
                    $filename = "Reporte_Permisos_{$fechaInicio}_al_{$fechaFin}.xls";
                    break;
                case 'no_asistencia':
                    $data = $reportGenerator->getEmployeesWithoutAttendance($fechaInicio, $fechaFin);
                    $filename = "Reporte_Sin_Asistencia_{$fechaInicio}_al_{$fechaFin}.xls";
                    break;
                case 'areas':
                    $tipoVista = $_GET['tipo_vista'] ?? 'detallado';
                    $data = $reportGenerator->getAreaReport($fechaInicio, $fechaFin, $tipoVista);
                    $filename = "Reporte_Areas_{$fechaInicio}_al_{$fechaFin}.xls";
                    break;
                default:
                    $data = $reportGenerator->getDailyReport($fechaInicio, $fechaFin);
                    $filename = "Reporte_Diario_{$fechaInicio}_al_{$fechaFin}.xls";
                    break;
            }
            
            // Aplicar filtros si es exportación filtrada
            if (!empty($filtros)) {
                $data = aplicarFiltros($data, $filtros);
                $filename = "Reporte_Filtrado_" . $filename;
            }
            break;
        case 'admins':
            $data = $reportGenerator->getAdmins();
            $filename = "Lista_Administradores.xls";
            
            // Aplicar filtros si es exportación filtrada
            if (!empty($filtros)) {
                $data = aplicarFiltros($data, $filtros);
                $filename = "Lista_Administradores_Filtrado.xls";
            }
            break;
        case 'trabajadores':
            $data = $reportGenerator->getEmployees();
            $filename = "Lista_Trabajadores.xls";
            
            // Aplicar filtros si es exportación filtrada
            if (!empty($filtros)) {
                $data = aplicarFiltros($data, $filtros);
                $filename = "Lista_Trabajadores_Filtrado.xls";
            }
            break;
        case 'history':
            $historialFechaInicio = $_GET['historial_fecha_inicio'] ?? date('Y-m-01');
            $historialFechaFin = $_GET['historial_fecha_fin'] ?? date('Y-m-d');
            $historialTabla = $_GET['historial_tabla'] ?? '';
            $historialAccion = $_GET['historial_accion'] ?? '';
            $historialCreadoPor = $_GET['historial_creado_por'] ?? '';
            $data = $reportGenerator->getHistorial($historialFechaInicio, $historialFechaFin, $historialTabla, $historialAccion, $historialCreadoPor);
            $filename = "Historial_Cambios_{$historialFechaInicio}_al_{$historialFechaFin}.xls";
            break;
        default:
            exit;
    }
    
    // Generar Excel con formato profesional
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<style>";
    echo "table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }";
    echo "th { background-color: #8A1538; color: white; font-weight: bold; padding: 8px; border: 1px solid #ddd; text-align: center; }";
    echo "td { padding: 8px; border: 1px solid #ddd; }";
    echo ".header-info { background-color: #f2f2f2; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; }";
    echo ".university-name { font-size: 18px; font-weight: bold; color: #8A1538; text-align: center; margin-bottom: 10px; }";
    echo ".report-title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 10px; }";
    echo ".report-info { font-size: 12px; margin-bottom: 5px; }";
    echo ".total-row { background-color: #e8f5e9; font-weight: bold; }";
    echo ".total-area-row { background-color: #e3f2fd; font-weight: bold; }";
    echo ".total-general-row { background-color: #fff8e1; font-weight: bold; }";
    echo ".footer { margin-top: 20px; font-size: 10px; color: #666; text-align: center; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    // Encabezado profesional
    echo "<div class='header-info'>";
    echo "<div class='university-name'>UNIVERSIDAD ROOSEVELT</div>";
    echo "<div class='report-title'>SISTEMA DE CONTROL DE ASISTENCIA</div>";
    echo "<div class='report-info'>Fecha de exportación: " . date('d/m/Y H:i:s') . "</div>";
    echo "<div class='report-info'>Exportado por: " . htmlspecialchars($_SESSION['admin_username']) . "</div>";
    
    // Información de filtros aplicados
    if (!empty($filtros)) {
        echo "<div class='report-info' style='color: #8A1538; font-weight: bold;'>FILTROS APLICADOS:</div>";
        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                $nombreCampo = ucfirst(str_replace('_', ' ', $campo));
                echo "<div class='report-info'>- $nombreCampo: " . htmlspecialchars($valor) . "</div>";
            }
        }
    }
    
    switch ($section) {
        case 'dashboard':
            echo "<div class='report-info'>Período: $fechaInicio al $fechaFin</div>";
            switch ($reportType) {
                case 'tardiness':
                    echo "<div class='report-info'>Reporte: Tardanzas</div>";
                    break;
                case 'permission':
                    echo "<div class='report-info'>Reporte: Permisos</div>";
                    break;
                case 'no_asistencia':
                    echo "<div class='report-info'>Reporte: Empleados Sin Asistencia</div>";
                    break;
                case 'areas':
                    echo "<div class='report-info'>Reporte: Por Áreas</div>";
                    break;
                default:
                    echo "<div class='report-info'>Reporte: Diario de Asistencia</div>";
                    break;
            }
            break;
        case 'admins':
            echo "<div class='report-info'>Reporte: Lista de Administradores</div>";
            break;
        case 'trabajadores':
            echo "<div class='report-info'>Reporte: Lista de Trabajadores</div>";
            break;
        case 'history':
            echo "<div class='report-info'>Reporte: Historial de Cambios</div>";
            echo "<div class='report-info'>Período: $historialFechaInicio al $historialFechaFin</div>";
            break;
    }
    echo "</div>";
    
    switch ($section) {
        case 'dashboard':
            switch ($reportType) {
                case 'tardiness':
                    echo "<table>";
                    echo "<tr><th colspan='9' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>REPORTE DE TARDANZAS</th></tr>";
                    echo "<tr>
                            <th>#</th>
                            <th>DNI</th>
                            <th>APELLIDOS</th>
                            <th>NOMBRES</th>
                            <th>ÁREA</th>
                            <th>CARGO</th>
                            <th>FECHA</th>
                            <th>TARDANZA MAÑANA</th>
                            <th>TARDANZA TARDE</th>
                          </tr>";
                    
                    $contador = 1;
                    $totalTardanzaManana = 0;
                    $totalTardanzaTarde = 0;
                    
                    foreach ($data as $row) {
                        if (isset($row['es_total']) && $row['es_total']) {
                            continue;
                        }
                        
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
                            echo "<tr>";
                            echo "<td>" . $contador++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                            echo "<td>" . htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) . "</td>";
                            echo "<td>" . htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                            echo "<td>" . $fecha . "</td>";
                            echo "<td>" . ($tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-') . "</td>";
                            echo "<td>" . ($tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-') . "</td>";
                            echo "</tr>";
                            
                            if ($tardanzas['manana']) {
                                $totalTardanzaManana += $tardanzas['manana']['minutos_tardanza'];
                            }
                            if ($tardanzas['tarde']) {
                                $totalTardanzaTarde += $tardanzas['tarde']['minutos_tardanza'];
                            }
                        }
                    }
                    
                    // Fila de totales
                    echo "<tr class='total-general-row'>";
                    echo "<td colspan='7' style='text-align: right; font-weight: bold;'>TOTALES:</td>";
                    echo "<td style='font-weight: bold;'>" . minutesToTime($totalTardanzaManana) . "</td>";
                    echo "<td style='font-weight: bold;'>" . minutesToTime($totalTardanzaTarde) . "</td>";
                    echo "</tr>";
                    
                    echo "</table>";
                    break;
                    
                case 'no_asistencia':
                    echo "<table>";
                    echo "<tr><th colspan='9' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>REPORTE DE EMPLEADOS SIN ASISTENCIA</th></tr>";
                    echo "<tr>
                            <th>#</th>
                            <th>DNI</th>
                            <th>APELLIDOS</th>
                            <th>NOMBRES</th>
                            <th>ÁREA</th>
                            <th>CARGO</th>
                            <th>TURNO</th>
                            <th>TOTAL FALTAS</th>
                          </tr>";
                    
                    $contador = 1;
                    $totalFaltasManana = 0;
                    $totalFaltasTarde = 0;
                    
                    foreach ($data as $row) {
                        if (isset($row['es_total']) && $row['es_total']) {
                            continue;
                        }
                        
                        if (!empty($row['faltas_manana'])) {
                            echo "<tr>";
                            echo "<td>" . $contador++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                            echo "<td>" . htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) . "</td>";
                            echo "<td>" . htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                            echo "<td>MAÑANA</td>";
                            echo "<td>" . $row['total_faltas_manana'] . "</td>";
                            echo "</tr>";
                            
                            $totalFaltasManana += $row['total_faltas_manana'];
                        }
                        
                        if (!empty($row['faltas_tarde'])) {
                            echo "<tr>";
                            echo "<td>" . $contador++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                            echo "<td>" . htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) . "</td>";
                            echo "<td>" . htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                            echo "<td>TARDE</td>";
                            echo "<td>" . $row['total_faltas_tarde'] . "</td>";
                            echo "</tr>";
                            
                            $totalFaltasTarde += $row['total_faltas_tarde'];
                        }
                    }
                    
                    // Fila de totales
                    echo "<tr class='total-general-row'>";
                    echo "<td colspan='6' style='text-align: right; font-weight: bold;'>TOTALES:</td>";
                    echo "<td style='font-weight: bold;'>MAÑANA</td>";
                    echo "<td style='font-weight: bold;'>$totalFaltasManana</td>";
                    echo "</tr>";
                    echo "<tr class='total-general-row'>";
                    echo "<td colspan='6' style='text-align: right; font-weight: bold;'></td>";
                    echo "<td style='font-weight: bold;'>TARDE</td>";
                    echo "<td style='font-weight: bold;'>$totalFaltasTarde</td>";
                    echo "</tr>";
                    echo "<tr class='total-general-row'>";
                    echo "<td colspan='6' style='text-align: right; font-weight: bold;'></td>";
                    echo "<td style='font-weight: bold;'>TOTAL GENERAL</td>";
                    echo "<td style='font-weight: bold;'>" . ($totalFaltasManana + $totalFaltasTarde) . "</td>";
                    echo "</tr>";
                    
                    echo "</table>";
                    break;
                    
                case 'permission':
                    echo "<table>";
                    echo "<tr><th colspan='14' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>REPORTE DE PERMISOS</th></tr>";
                    echo "<tr>
                            <th>#</th>
                            <th>DNI</th>
                            <th>APELLIDOS</th>
                            <th>NOMBRES</th>
                            <th>ÁREA</th>
                            <th>CARGO</th>
                            <th>FECHA</th>
                            <th>TIPO PERMISO</th>
                            <th>MOTIVO</th>
                            <th>SALIDA</th>
                            <th>RETORNO</th>
                            <th>REGISTRO</th>
                            <th>REGISTRADO POR</th>
                          </tr>";
                    
                    $contador = 1;
                    $totalPermisos = 0;
                    $tiposPermiso = [];
                    
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . $contador++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                        echo "<td>" . htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) . "</td>";
                        echo "<td>" . htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                        echo "<td>" . $row['fecha_permiso'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['tipo_permiso']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motivo']) . "</td>";
                        echo "<td>" . $row['hora_salida'] . "</td>";
                        echo "<td>" . $row['hora_retorno'] . "</td>";
                        echo "<td>" . $row['hora_registro'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['registrado_por']) . "</td>";
                        echo "</tr>";
                        
                        $totalPermisos++;
                        $tipo = $row['tipo_permiso'];
                        if (!isset($tiposPermiso[$tipo])) {
                            $tiposPermiso[$tipo] = 0;
                        }
                        $tiposPermiso[$tipo]++;
                    }
                    
                    // Fila de totales
                    echo "<tr class='total-general-row'>";
                    echo "<td colspan='7' style='text-align: right; font-weight: bold;'>TOTALES:</td>";
                    echo "<td colspan='6' style='font-weight: bold;'>";
                    foreach ($tiposPermiso as $tipo => $cantidad) {
                        echo "$tipo: $cantidad | ";
                    }
                    echo "TOTAL: $totalPermisos";
                    echo "</td>";
                    echo "</tr>";
                    
                    echo "</table>";
                    break;
                    
                case 'areas':
                    echo "<table>";
                    echo "<tr><th colspan='8' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>REPORTE POR ÁREAS</th></tr>";
                    echo "<tr>
                            <th>ÁREA</th>
                            <th>CARGO</th>
                            <th>EMPLEADOS</th>
                            <th>DÍAS</th>
                            <th>ASISTENCIAS</th>
                            <th>PERMISOS</th>
                            <th>FALTAS</th>
                            <th>% ASISTENCIA</th>
                          </tr>";
                    
                    foreach ($data as $row) {
                        $class = '';
                        if (isset($row['es_total_area']) && $row['es_total_area']) {
                            $class = 'total-area-row';
                        } elseif ($row['area'] === 'TOTAL GENERAL') {
                            $class = 'total-general-row';
                        }
                        
                        echo "<tr class='$class'>";
                        echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                        echo "<td>" . $row['empleados'] . "</td>";
                        echo "<td>" . $row['dias_totales'] . "</td>";
                        echo "<td>" . $row['asistencias'] . "</td>";
                        echo "<td>" . $row['permisos'] . "</td>";
                        echo "<td>" . $row['faltas'] . "</td>";
                        echo "<td>" . $row['porcentaje_asistencia'] . "%</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    break;
                    
                default: // daily
                    echo "<table>";
                    echo "<tr><th colspan='14' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>REPORTE DIARIO DE ASISTENCIA</th></tr>";
                    echo "<tr>
                            <th>#</th>
                            <th>FECHA</th>
                            <th>DNI</th>
                            <th>APELLIDOS</th>
                            <th>NOMBRES</th>
                            <th>ÁREA</th>
                            <th>CARGO</th>
                            <th>ENT. MAÑANA</th>
                            <th>SAL. MAÑANA</th>
                            <th>ENT. TARDE</th>
                            <th>SAL. TARDE</th>
                            <th>REGISTROS</th>
                            <th>REGISTRADO POR</th>
                          </tr>";
                    
                    $contador = 1;
                    foreach ($data as $row) {
                        echo "<tr>";
                        echo "<td>" . $contador++ . "</td>";
                        echo "<td>" . $row['fecha'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                        echo "<td>" . htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) . "</td>";
                        echo "<td>" . htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['puesto']) . "</td>";
                        
                        echo "<td>" . (!empty($row['registros_entrada_manana']) ? min($row['registros_entrada_manana']) : '-') . "</td>";
                        echo "<td>" . (!empty($row['registros_salida_manana']) ? min($row['registros_salida_manana']) : '-') . "</td>";
                        echo "<td>" . (!empty($row['registros_entrada_tarde']) ? min($row['registros_entrada_tarde']) : '-') . "</td>";
                        echo "<td>" . (!empty($row['registros_salida_tarde']) ? min($row['registros_salida_tarde']) : '-') . "</td>";
                        
                        echo "<td>";
                        if (!empty($row['todos_registros'])) {
                            foreach ($row['todos_registros'] as $registro) {
                                echo $registro . "<br>";
                            }
                        } else {
                            echo "-";
                        }
                        echo "</td>";
                        
                        echo "<td>";
                        if (!empty($row['todos_registradores'])) {
                            foreach ($row['todos_registradores'] as $registrador) {
                                echo htmlspecialchars($registrador) . "<br>";
                            }
                        } else {
                            echo "-";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    break;
            }
            break;
            
        case 'admins':
            echo "<table>";
            echo "<tr><th colspan='12' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>LISTA DE ADMINISTRADORES</th></tr>";
            echo "<tr>
                    <th>#</th>
                    <th>APELLIDOS</th>
                    <th>NOMBRES</th>
                    <th>ÁREA</th>
                    <th>CARGO</th>
                    <th>USUARIO</th>
                    <th>ROL</th>
                    <th>ESTADO</th>
                    <th>FECHA CREACIÓN</th>
                    <th>HORA</th>
                    <th>CREADO POR</th>
                    <th>ACCIONES</th>
                  </tr>";
            
            $contador = 1;
            foreach ($data as $admin) {
                echo "<tr>";
                echo "<td>" . $contador++ . "</td>";
                echo "<td>" . htmlspecialchars($admin['apellidos']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['nombres']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['area']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['cargo']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['usuario']) . "</td>";
                echo "<td>" . ($admin['rol'] === 'admin' ? 'Administrador' : ($admin['rol'] === 'supervisor' ? 'Supervisor' : 'Espectador')) . "</td>";
                echo "<td>" . ($admin['estado'] === 'activo' ? 'Activo' : 'Inactivo') . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($admin['fecha_creacion'])) . "</td>";
                echo "<td>" . date('H:i:s', strtotime($admin['fecha_creacion'])) . "</td>";
                echo "<td>" . htmlspecialchars($admin['creado_por_nombre']) . "</td>";
                echo "<td>";
                if ($isSuperAdmin && $admin['id'] != $_SESSION['admin_id']) {
                    echo "<button type='button' class='btn btn-secondary btn-sm' onclick='openEditAdminModal(" . $admin['id'] . ")'>";
                    echo "<i class='fas fa-edit'></i> Editar";
                    echo "</button>";
                    echo "<form method='post' style='display: inline; margin-left: 5px;'>";
                    echo "<input type='hidden' name='admin_id' value='" . $admin['id'] . "'>";
                    echo "<input type='hidden' name='nuevo_estado' value='" . ($admin['estado'] === 'activo' ? 'inactivo' : 'activo') . "'>";
                    echo "<button type='submit' name='cambiar_estado_admin' class='btn btn-" . ($admin['estado'] === 'activo' ? 'warning' : 'success') . " btn-sm'>";
                    echo "<i class='fas fa-" . ($admin['estado'] === 'activo' ? 'pause' : 'play') . "'></i>";
                    echo $admin['estado'] === 'activo' ? ' Desactivar' : ' Activar';
                    echo "</button>";
                    echo "</form>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
            
        case 'trabajadores':
            echo "<table>";
            echo "<tr><th colspan='10' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>LISTA DE TRABAJADORES</th></tr>";
            echo "<tr>
                    <th>#</th>
                    <th>FOTO</th>
                    <th>DNI</th>
                    <th>APELLIDOS</th>
                    <th>NOMBRES</th>
                    <th>ÁREA</th>
                    <th>CARGO</th>
                    <th>ESTADO</th>
                    <th>CREADO POR</th>
                    <th>ACCIONES</th>
                  </tr>";
            
            $contador = 1;
            foreach ($data as $empleado) {
                echo "<tr>";
                echo "<td>" . $contador++ . "</td>";
                echo "<td>";
                if (!empty($empleado['foto']) && file_exists(FOTO_DIR . $empleado['foto'])) {
                    echo "<img src='" . FOTO_DIR . $empleado['foto'] . "' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover;'>";
                } else {
                    echo "<div style='width: 50px; height: 50px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;'>";
                    echo "<i class='fas fa-user' style='color: #666;'></i>";
                    echo "</div>";
                }
                echo "</td>";
                echo "<td>" . htmlspecialchars($empleado['dni']) . "</td>";
                echo "<td>" . htmlspecialchars($empleado['apellidos']) . "</td>";
                echo "<td>" . htmlspecialchars($empleado['nombres']) . "</td>";
                echo "<td>" . htmlspecialchars($empleado['area']) . "</td>";
                echo "<td>" . htmlspecialchars($empleado['puesto']) . "</td>";
                echo "<td>";
                echo "<span class='badge " . ($empleado['estado'] === 'activo' ? 'badge-success' : 'badge-danger') . "'>";
                echo $empleado['estado'] === 'activo' ? 'Activo' : 'Inactivo';
                echo "</span>";
                echo "</td>";
                echo "<td>" . htmlspecialchars($empleado['creado_por_nombre']) . "</td>";
                echo "<td>";
                if ($isSuperAdmin || $isSupervisor) {
                    echo "<button type='button' class='btn btn-secondary btn-sm' onclick='openEditEmpleadoModal(" . $empleado['id'] . ")'>";
                    echo "<i class='fas fa-edit'></i> Editar";
                    echo "</button>";
                    echo "<form method='post' style='display: inline; margin-left: 5px;'>";
                    echo "<input type='hidden' name='empleado_id' value='" . $empleado['id'] . "'>";
                    echo "<input type='hidden' name='nuevo_estado' value='" . ($empleado['estado'] === 'activo' ? 'inactivo' : 'activo') . "'>";
                    echo "<button type='submit' name='cambiar_estado_empleado' class='btn btn-" . ($empleado['estado'] === 'activo' ? 'warning' : 'success') . " btn-sm'>";
                    echo "<i class='fas fa-" . ($empleado['estado'] === 'activo' ? 'pause' : 'play') . "'></i>";
                    echo $empleado['estado'] === 'activo' ? ' Desactivar' : ' Activar';
                    echo "</button>";
                    echo "</form>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
            
        case 'history':
            echo "<table>";
            echo "<tr><th colspan='11' style='background-color: #8A1538; color: white; text-align: center; font-size: 16px;'>HISTORIAL DE CAMBIOS</th></tr>";
            echo "<tr>
                    <th>#</th>
                    <th>TABLA AFECTADA</th>
                    <th>USUARIO AFECTADO</th>
                    <th>ACCION</th>
                    <th>CAMPO MODIFICADO</th>
                    <th>VALOR ANTERIOR</th>
                    <th>VALOR NUEVO</th>
                    <th>MOTIVO</th>
                    <th>MODIFICADO POR</th>
                    <th>FECHA MODIFICACION</th>
                    <th>HORA MODIFICACION</th>
                  </tr>";
            
            $contador = 1;
            foreach ($data as $historial) {
                echo "<tr>";
                echo "<td>" . $contador++ . "</td>";
                echo "<td>" . htmlspecialchars($historial['tabla_afectada']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['usuario_afectado']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['accion']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['campo_modificado']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['valor_anterior']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['valor_nuevo']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['motivo']) . "</td>";
                echo "<td>" . htmlspecialchars($historial['modificado_por_nombre']) . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($historial['fecha_modificacion'])) . "</td>";
                echo "<td>" . date('H:i:s', strtotime($historial['fecha_modificacion'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            break;
    }
    
    // Pie de página
    echo "<div class='footer'>";
    echo "Documento generado automáticamente por el Sistema de Control de Asistencia - Universidad Roosevelt<br>";
    echo "Fecha y hora de generación: " . date('d/m/Y H:i:s');
    echo "</div>";
    
    echo "</body>";
    echo "</html>";
    exit;
}

// Clase para generar reportes
class ReportGenerator {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtiene lista de empleados/trabajadores
     */
    public function getEmployees() {
        $stmt = $this->pdo->query("
            SELECT e.id, e.dni, e.nombres, e.apellidos, e.area, e.puesto, e.estado, e.foto, e.tipo_personal, e.inicio_contrato, e.fin_contrato,
                   COALESCE(ua.usuario, 'SISTEMA') AS creado_por_nombre
            FROM empleados e 
            LEFT JOIN usuarios_admin ua ON e.creado_por = ua.id 
            ORDER BY e.apellidos, e.nombres
        ");
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
     * Obtiene reporte diario de asistencia
     */
    public function getDailyReport($fechaInicio, $fechaFin) {
        $params = [$fechaInicio, $fechaFin];
        
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
                WHERE r.fecha BETWEEN ? AND ?
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
     * Obtiene empleados que no marcaron asistencia en un rango de fechas
     */
    public function getEmployeesWithoutAttendance($fechaInicio, $fechaFin) {
        // Obtener todos los empleados activos
        $sql = "SELECT 
                    e.id, e.dni, 
                    CONCAT(e.apellidos, ' ', e.nombres) AS nombre_completo,
                    e.area, e.puesto, e.tipo_personal,
                    e.entrada_manana, e.salida_manana, e.entrada_tarde, e.salida_tarde
                FROM empleados e
                WHERE e.estado = 'activo'
                ORDER BY e.area, e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $empleados = $stmt->fetchAll();
        
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
        
        // Agregar fila de totales (solo 1 fila)
        $result[] = [
            'es_total' => true,
            'total_faltas_manana' => $totalFaltasManana,
            'total_faltas_tarde' => $totalFaltasTarde,
            'total_general' => $totalFaltasManana + $totalFaltasTarde
        ];
        
        return $result;
    }
    
    /**
     * Obtiene reporte de permisos
     */
    public function getPermissionReport($fechaInicio, $fechaFin) {
        $params = [$fechaInicio, $fechaFin];
        
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
                WHERE p.fecha_permiso BETWEEN ? AND ?
                ORDER BY p.fecha_permiso, e.area, e.apellidos, e.nombres";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene totales por tipo de permiso
     */
    public function getPermissionTotals($fechaInicio, $fechaFin) {
        $params = [$fechaInicio, $fechaFin];
        
        $sql = "SELECT 
                    p.tipo_permiso, 
                    COUNT(*) as total
                FROM permisos p
                JOIN empleados e ON p.empleado_id = e.id
                WHERE p.fecha_permiso BETWEEN ? AND ?
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
     * Obtiene reporte de tardanzas
     */
    public function getTardinessReport($fechaInicio, $fechaFin) {
        $minutosTolerancia = getConfig('minutos_tolerancia', $this->pdo) ?: 5;
        
        // Obtener el reporte diario para calcular tardanzas
        $dailyReport = $this->getDailyReport($fechaInicio, $fechaFin);
        
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
            
            // Procesar tardanzas de la mañana
            if (!empty($empleado['registros_entrada_manana']) && $empleado['entrada_manana']) {
                $primerRegistroManana = min($empleado['registros_entrada_manana']);
                $horaEntradaManana = new DateTime($empleado['entrada_manana']);
                $horaRegistroManana = new DateTime($primerRegistroManana);
                
                $diferencia = $horaEntradaManana->diff($horaRegistroManana);
                $minutosTardanza = ($diferencia->h * 60) + $diferencia->i;
                
                // Aplicar tolerancia
                $minutosTardanza = max(0, $minutosTardanza - $minutosTolerancia);
                
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
            
            // Procesar tardanzas de la tarde
            if (!empty($empleado['registros_entrada_tarde']) && $empleado['entrada_tarde']) {
                $primerRegistroTarde = min($empleado['registros_entrada_tarde']);
                $horaEntradaTarde = new DateTime($empleado['entrada_tarde']);
                $horaRegistroTarde = new DateTime($primerRegistroTarde);
                
                $diferencia = $horaEntradaTarde->diff($horaRegistroTarde);
                $minutosTardanza = ($diferencia->h * 60) + $diferencia->i;
                
                // Aplicar tolerancia
                $minutosTardanza = max(0, $minutosTardanza - $minutosTolerancia);
                
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
     * Obtiene reporte por áreas - CORREGIDO
     */
    public function getAreaReport($fechaInicio, $fechaFin, $tipoVista = 'detallado') {
        // Obtener todos los empleados activos
        $sqlEmpleados = "SELECT id, area, puesto, tipo_personal, estado 
                        FROM empleados 
                        WHERE estado = 'activo' 
                        ORDER BY area, puesto";
        $stmtEmpleados = $this->pdo->prepare($sqlEmpleados);
        $stmtEmpleados->execute();
        $empleados = $stmtEmpleados->fetchAll();
        
        // Obtener total de días en el rango
        $start = new DateTime($fechaInicio);
        $end = new DateTime($fechaFin);
        $diasTotales = $end->diff($start)->days + 1;
        
        // Obtener asistencias por empleado
        $sqlAsistencias = "SELECT e.id, COUNT(DISTINCT DATE(r.fecha)) as asistencias
                          FROM empleados e
                          LEFT JOIN registros_asistencia r ON e.id = r.empleado_id AND r.fecha BETWEEN ? AND ?
                          WHERE e.estado = 'activo'
                          GROUP BY e.id";
        $stmtAsist = $this->pdo->prepare($sqlAsistencias);
        $stmtAsist->execute([$fechaInicio, $fechaFin]);
        $asistencias = $stmtAsist->fetchAll();
        
        // Obtener permisos por empleado
        $sqlPermisos = "SELECT e.id, COUNT(DISTINCT p.fecha_permiso) as permisos
                       FROM empleados e
                       LEFT JOIN permisos p ON e.id = p.empleado_id AND p.fecha_permiso BETWEEN ? AND ?
                       WHERE e.estado = 'activo'
                       GROUP BY e.id";
        $stmtPerm = $this->pdo->prepare($sqlPermisos);
        $stmtPerm->execute([$fechaInicio, $fechaFin]);
        $permisos = $stmtPerm->fetchAll();
        
        // Organizar datos por área y cargo
        $areaData = [];
        $totalGeneral = [
            'area' => 'TOTAL GENERAL',
            'puesto' => '',
            'empleados' => 0,
            'dias_totales' => 0,
            'asistencias' => 0,
            'permisos' => 0,
            'faltas' => 0,
            'porcentaje_asistencia' => 0
        ];
        
        // Crear estructura de datos por área y cargo
        foreach ($empleados as $empleado) {
            $area = $empleado['area'];
            $puesto = $empleado['puesto'];
            $key = $area . '_' . $puesto;
            
            if (!isset($areaData[$key])) {
                $areaData[$key] = [
                    'area' => $area,
                    'puesto' => $puesto,
                    'empleados' => 0,
                    'dias_totales' => 0,
                    'asistencias' => 0,
                    'permisos' => 0,
                    'faltas' => 0,
                    'porcentaje_asistencia' => 0
                ];
            }
            
            $areaData[$key]['empleados']++;
            $areaData[$key]['dias_totales'] += $diasTotales;
            $totalGeneral['empleados']++;
            $totalGeneral['dias_totales'] += $diasTotales;
        }
        
        // Procesar asistencias
        $asistenciasPorEmpleado = [];
        foreach ($asistencias as $asist) {
            $asistenciasPorEmpleado[$asist['id']] = $asist['asistencias'];
        }
        
        // Procesar permisos
        $permisosPorEmpleado = [];
        foreach ($permisos as $perm) {
            $permisosPorEmpleado[$perm['id']] = $perm['permisos'];
        }
        
        // Calcular asistencias y permisos por área y cargo
        foreach ($empleados as $empleado) {
            $area = $empleado['area'];
            $puesto = $empleado['puesto'];
            $key = $area . '_' . $puesto;
            
            $asistenciasEmpleado = $asistenciasPorEmpleado[$empleado['id']] ?? 0;
            $permisosEmpleado = $permisosPorEmpleado[$empleado['id']] ?? 0;
            
            $areaData[$key]['asistencias'] += $asistenciasEmpleado;
            $areaData[$key]['permisos'] += $permisosEmpleado;
            $totalGeneral['asistencias'] += $asistenciasEmpleado;
            $totalGeneral['permisos'] += $permisosEmpleado;
        }
        
        // Calcular faltas y porcentajes
        foreach ($areaData as $key => &$data) {
            $data['faltas'] = $data['dias_totales'] - $data['asistencias'] - $data['permisos'];
            $data['porcentaje_asistencia'] = $data['dias_totales'] > 0 ? 
                round(($data['asistencias'] / $data['dias_totales']) * 100, 2) : 0;
        }
        
        $totalGeneral['faltas'] = $totalGeneral['dias_totales'] - $totalGeneral['asistencias'] - $totalGeneral['permisos'];
        $totalGeneral['porcentaje_asistencia'] = $totalGeneral['dias_totales'] > 0 ? 
            round(($totalGeneral['asistencias'] / $totalGeneral['dias_totales']) * 100, 2) : 0;
        
        // Agregar totales por área
        $areaTotals = [];
        foreach ($areaData as $key => $data) {
            $area = $data['area'];
            if (!isset($areaTotals[$area])) {
                $areaTotals[$area] = [
                    'area' => $area,
                    'puesto' => 'TOTAL ' . $area,
                    'empleados' => 0,
                    'dias_totales' => 0,
                    'asistencias' => 0,
                    'permisos' => 0,
                    'faltas' => 0,
                    'porcentaje_asistencia' => 0,
                    'es_total_area' => true
                ];
            }
            
            $areaTotals[$area]['empleados'] += $data['empleados'];
            $areaTotals[$area]['dias_totales'] += $data['dias_totales'];
            $areaTotals[$area]['asistencias'] += $data['asistencias'];
            $areaTotals[$area]['permisos'] += $data['permisos'];
            $areaTotals[$area]['faltas'] += $data['faltas'];
        }
        
        // Calcular porcentajes para totales por área
        foreach ($areaTotals as &$totalArea) {
            $totalArea['porcentaje_asistencia'] = $totalArea['dias_totales'] > 0 ? 
                round(($totalArea['asistencias'] / $totalArea['dias_totales']) * 100, 2) : 0;
        }
        
        // Convertir a array y agregar totales
        $result = array_values($areaData);
        
        // Insertar totales por área después de cada área
        $finalResult = [];
        $currentArea = '';
        
        foreach ($result as $row) {
            if ($row['area'] !== $currentArea) {
                if ($currentArea !== '') {
                    // Agregar total del área anterior
                    $finalResult[] = $areaTotals[$currentArea];
                }
                $currentArea = $row['area'];
            }
            $finalResult[] = $row;
        }
        
        // Agregar el último total de área
        if ($currentArea !== '') {
            $finalResult[] = $areaTotals[$currentArea];
        }
        
        // Agregar fila de total general
        $finalResult[] = $totalGeneral;
        
        // Filtrar según tipo de vista
        if ($tipoVista === 'total') {
            $finalResult = array_filter($finalResult, function($row) {
                return isset($row['es_total_area']) || (isset($row['area']) && $row['area'] === 'TOTAL GENERAL');
            });
        }
        
        return $finalResult;
    }
    
    /**
     * Obtiene lista de administradores con información de creador
     */
    public function getAdmins() {
        $stmt = $this->pdo->query("
            SELECT ua.*, 
                   COALESCE(creador.usuario, 'SISTEMA') AS creado_por_nombre
            FROM usuarios_admin ua 
            LEFT JOIN usuarios_admin creador ON ua.creado_por = creador.id 
            ORDER BY ua.rol, ua.apellidos, ua.nombres
        ");
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
    
    /**
     * Obtiene historial de cambios
     */
    public function getHistorial($fechaInicio = null, $fechaFin = null, $tabla = null, $accion = null, $creadoPor = null) {
        $params = [];
        $where = "WHERE 1=1";
        
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
     * Obtiene lista de cargos por área
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

// Función auxiliar para convertir minutos a tiempo
function minutesToTime($minutes) {
    $horas = floor($minutes / 60);
    $minutos = $minutes % 60;
    return sprintf("%02d:%02d", $horas, $minutos);
}

// Instanciar el generador de reportes
$reportGenerator = new ReportGenerator($pdo);

// Determinar qué sección mostrar
$section = $_GET['section'] ?? 'dashboard';

// Configurar fechas por defecto para reportes - SIEMPRE FECHA ACTUAL
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener minutos de tolerancia
$minutosTolerancia = getConfig('minutos_tolerancia', $pdo) ?: 5;

// Generar el reporte correspondiente si estamos en dashboard
if ($section === 'dashboard') {
    $reportType = $_GET['report_type'] ?? 'daily';
    $tipoVista = $_GET['tipo_vista'] ?? 'detallado';
    
    switch ($reportType) {
        case 'tardiness':
            $reportData = $reportGenerator->getTardinessReport($fechaInicio, $fechaFin);
            $reportTitle = "Reporte de Tardanzas";
            break;
            
        case 'permission':
            $reportData = $reportGenerator->getPermissionReport($fechaInicio, $fechaFin);
            $permissionTotals = $reportGenerator->getPermissionTotals($fechaInicio, $fechaFin);
            $reportTitle = "Reporte de Permisos";
            break;
            
        case 'no_asistencia':
            $reportData = $reportGenerator->getEmployeesWithoutAttendance($fechaInicio, $fechaFin);
            $reportTitle = "Empleados Sin Asistencia";
            break;
            
        case 'areas':
            $reportData = $reportGenerator->getAreaReport($fechaInicio, $fechaFin, $tipoVista);
            $reportTitle = "Reporte por Áreas";
            break;
            
        default: // daily
            $reportData = $reportGenerator->getDailyReport($fechaInicio, $fechaFin);
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

// Obtener datos para otras secciones
if ($section === 'admins') {
    $adminsData = $reportGenerator->getAdmins();
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = 20;
    $totalItems = count($adminsData);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedAdmins = array_slice($adminsData, $offset, $itemsPerPage);
}

if ($section === 'trabajadores') {
    $employeesData = $reportGenerator->getEmployees();
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = 20;
    $totalItems = count($employeesData);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedEmployees = array_slice($employeesData, $offset, $itemsPerPage);
}

if ($section === 'history') {
    $historialFechaInicio = $_GET['historial_fecha_inicio'] ?? date('Y-m-01');
    $historialFechaFin = $_GET['historial_fecha_fin'] ?? date('Y-m-d');
    $historialTabla = $_GET['historial_tabla'] ?? '';
    $historialAccion = $_GET['historial_accion'] ?? '';
    $historialCreadoPor = $_GET['historial_creado_por'] ?? '';
    
    $historialData = $reportGenerator->getHistorial($historialFechaInicio, $historialFechaFin, $historialTabla, $historialAccion, $historialCreadoPor);
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = 20;
    $totalItems = count($historialData);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $paginatedHistorial = array_slice($historialData, $offset, $itemsPerPage);
}

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

// Obtener datos de administrador para edición
if (isset($_GET['get_admin'])) {
    $id = $_GET['get_admin'];
    $admin = $reportGenerator->getAdminById($id);
    header('Content-Type: application/json');
    echo json_encode($admin);
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

// Obtener cargos por área (AJAX)
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
            width: 250px;
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
            margin-left: 250px;
            padding: 1.5rem;
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
        
        /* Nuevos estilos para formulario horizontal de administradores */
        .admin-form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .admin-form-container .form-group:nth-child(odd) {
            grid-column: 1;
        }
        
        .admin-form-container .form-group:nth-child(even) {
            grid-column: 2;
        }
        
        .admin-form-container .form-group:nth-child(7) {
            grid-column: 1 / -1;
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
            
            .admin-form-container {
                grid-template-columns: 1fr;
            }
            
            .admin-form-container .form-group:nth-child(odd),
            .admin-form-container .form-group:nth-child(even) {
                grid-column: 1;
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
            <a href="?section=dashboard&report_type=daily&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $section === 'dashboard' && (!isset($_GET['report_type']) || $_GET['report_type'] === 'daily') ? 'active' : '' ?>">
                <i class="fas fa-calendar-day"></i> <span>Diario</span>
            </a>
            <a href="?section=dashboard&report_type=no_asistencia&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'no_asistencia' ? 'active' : '' ?>">
                <i class="fas fa-user-times"></i> <span>Sin Asistencia</span>
            </a>
            <a href="?section=dashboard&report_type=tardiness&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'tardiness' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> <span>Tardanzas</span>
            </a>
            <a href="?section=dashboard&report_type=permission&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'permission' ? 'active' : '' ?>">
                <i class="fas fa-file-signature"></i> <span>Permisos</span>
            </a>
            
            <?php if (!$isEspectador): ?>
                <a href="?section=dashboard&report_type=areas&fecha_inicio=<?= date('Y-m-d') ?>&fecha_fin=<?= date('Y-m-d') ?>" class="menu-item <?= $section === 'dashboard' && isset($_GET['report_type']) && $_GET['report_type'] === 'areas' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> <span>Por Áreas</span>
                </a>
                
                <?php if ($isSuperAdmin || $isSupervisor): ?>
                    <a href="?section=trabajadores" class="menu-item <?= $section === 'trabajadores' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> <span>Trabajadores</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin || $isSupervisor): ?>
                    <a href="?section=history" class="menu-item <?= $section === 'history' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i> <span>Historial</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                    <a href="?section=admins" class="menu-item <?= $section === 'admins' ? 'active' : '' ?>">
                        <i class="fas fa-users-cog"></i> <span>Administradores</span>
                    </a>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                    <a href="?section=config" class="menu-item <?= $section === 'config' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i> <span>Configuración</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>
                <?php if ($section === 'dashboard'): ?>
                    <i class="fas fa-clipboard-list"></i> <?= $reportTitle ?>
                <?php elseif ($section === 'admins'): ?>
                    <i class="fas fa-users-cog"></i> Administradores
                <?php elseif ($section === 'trabajadores'): ?>
                    <i class="fas fa-users"></i> Trabajadores
                <?php elseif ($section === 'config'): ?>
                    <i class="fas fa-cog"></i> Configuración
                <?php elseif ($section === 'history'): ?>
                    <i class="fas fa-history"></i> Historial de Cambios
                <?php endif; ?>
            </h1>
            <div class="user-info">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['admin_username']) ?> 
                (<?= $_SESSION['admin_role'] === 'admin' ? 'Administrador' : ($_SESSION['admin_role'] === 'supervisor' ? 'Supervisor' : 'Espectador') ?>)</span>
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
            <!-- Filtros para reportes -->
            <div class="filters-container">
                <div class="filters-grid">
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control" value="<?= $fechaInicio ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin</label>
                            <input type="date" id="fecha_fin" class="form-control" value="<?= $fechaFin ?>">
                        </div>
                    </div>
                    
                    <?php if ($reportType === 'areas'): ?>
                        <div class="form-group">
                            <label for="tipo_vista"><i class="fas fa-eye"></i> Tipo de Vista</label>
                            <select id="tipo_vista" class="form-control">
                                <option value="detallado" <?= $tipoVista === 'detallado' ? 'selected' : '' ?>>Vista Detallada</option>
                                <option value="total" <?= $tipoVista === 'total' ? 'selected' : '' ?>>Solo Totales</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <!-- Nuevos filtros para Área, Cargo y Tipo Personal -->
                        <div class="form-group">
                            <label for="filter_area_report"><i class="fas fa-building"></i> Área</label>
                            <select id="filter_area_report" class="form-control" onchange="updateCargoFilter('report')">
                                <option value="">Todas las áreas</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_cargo_report"><i class="fas fa-briefcase"></i> Cargo</label>
                            <select id="filter_cargo_report" class="form-control">
                                <option value="">Todos los cargos</option>
                                <?php foreach ($positions as $position): ?>
                                    <option value="<?= htmlspecialchars($position) ?>"><?= htmlspecialchars($position) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="search_input"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" id="search_input" class="form-control" placeholder="Buscar en la tabla...">
                        </div>
                        
                        <div class="filter-actions">
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
                            
                            <!-- Botón Exportar Excel - SOLO para no espectadores -->
                            <?php if (!$isEspectador): ?>
                                <button type="button" class="btn btn-warning" onclick="exportToExcel('filtered')">
                                    <i class="fas fa-filter"></i> Exportar Filtrado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card report-card">
                <div class="report-header">
                    <h2 class="card-title" style="color: white; border: none; margin: 0;"><i class="fas fa-table"></i> Resultados</h2>
                </div>
                
                <!-- Vista de tabla para reportes -->
                <div class="table-responsive">
                    <?php if (!empty($paginatedData)): ?>
                        <table class="report-table" id="report-table">
                            <thead>
                                <tr>
                                    <?php if ($reportType !== 'areas'): ?>
                                        <th style="width: 50px">#</th>
                                    <?php endif; ?>
                                    <?php switch ($reportType): 
                                        case 'tardiness': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>FECHA</th>
                                            <th>TARDANZA MAÑANA</th>
                                            <th>TARDANZA TARDE</th>
                                            <?php break; ?>
                                            
                                        <?php case 'permission': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>FECHA</th>
                                            <th>TIPO PERMISO</th>
                                            <th>MOTIVO</th>
                                            <th>SALIDA</th>
                                            <th>RETORNO</th>
                                            <th>REGISTRO</th>
                                            <th>REGISTRADO POR</th>
                                            <?php if (!$isEspectador): ?>
                                                <th>ACCIONES</th>
                                            <?php endif; ?>
                                            <?php break; ?>
                                            
                                        <?php case 'no_asistencia': ?>
                                            <th>DNI</th>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>TURNO</th>
                                            <th>TOTAL FALTAS</th>
                                            <?php break; ?>
                                            
                                        <?php case 'areas': ?>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>EMPLEADOS</th>
                                            <th>DÍAS</th>
                                            <th>ASISTENCIAS</th>
                                            <th>PERMISOS</th>
                                            <th>FALTAS</th>
                                            <th>% ASISTENCIA</th>
                                            <?php break; ?>
                                            
                                        <?php default: // daily ?>
                                            <th>FECHA</th>
                                            <th>DNI</th>
                                            <th>APELLIDOS</th>
                                            <th>NOMBRES</th>
                                            <th>ÁREA</th>
                                            <th>CARGO</th>
                                            <th>ENT. MAÑANA</th>
                                            <th>SAL. MAÑANA</th>
                                            <th>ENT. TARDE</th>
                                            <th>SAL. TARDE</th>
                                            <th>REGISTROS</th>
                                            <th>REGISTRADO POR</th>
                                    <?php endswitch; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = ($currentPage - 1) * $itemsPerPage + 1; ?>
                                <?php foreach ($paginatedData as $row): ?>
                                    <?php if (isset($row['es_total']) && $row['es_total']): ?>
                                        <!-- Fila única de totales para SIN ASISTENCIA -->
                                        <?php if ($reportType === 'no_asistencia'): ?>
                                            <tr class="total-general-row">
                                                <td colspan="6" style="text-align: right; font-weight: bold;">TOTALES:</td>
                                                <td style="font-weight: bold;">MAÑANA: <?= $row['total_faltas_manana'] ?></td>
                                                <td style="font-weight: bold;">TARDE: <?= $row['total_faltas_tarde'] ?></td>
                                                <td style="font-weight: bold;">TOTAL: <?= $row['total_general'] ?></td>
                                            </tr>
                                        <?php elseif ($reportType === 'tardiness'): ?>
                                            <!-- Fila única de totales para TARDANZAS -->
                                            <tr class="total-general-row">
                                                <td colspan="6" style="text-align: right; font-weight: bold;">TOTALES:</td>
                                                <td style="font-weight: bold;"><?= $row['total_tardanza_manana'] ?></td>
                                                <td style="font-weight: bold;"><?= $row['total_tardanza_tarde'] ?></td>
                                                <td style="font-weight: bold;">TOTAL: <?= $row['total_tardanza'] ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php continue; ?>
                                    <?php endif; ?>
                                    
                                    <tr <?= (isset($row['es_total_area']) ? 'class="total-area-row"' : ($reportType === 'areas' && $row['area'] === 'TOTAL GENERAL' ? 'class="total-general-row"' : '')) ?>
                                        data-area="<?= htmlspecialchars($row['area'] ?? '') ?>"
                                        data-cargo="<?= htmlspecialchars($row['puesto'] ?? '') ?>">
                                        
                                        <?php if ($reportType !== 'areas'): ?>
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
                                                        <tr data-area="<?= htmlspecialchars($row['area'] ?? '') ?>" data-cargo="<?= htmlspecialchars($row['puesto'] ?? '') ?>">
                                                        <td style="text-align: center;"></td>
                                                    <?php endif; ?>
                                                    
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) ?></td>
                                                    <td><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center nowrap"><?= $fecha ?></td>
                                                    
                                                    <!-- Datos mañana -->
                                                    <td class="tardanza-cell text-center">
                                                        <?= $tardanzas['manana'] ? $tardanzas['manana']['tardanza'] : '-' ?>
                                                    </td>
                                                    
                                                    <!-- Datos tarde -->
                                                    <td class="tardanza-cell text-center">
                                                        <?= $tardanzas['tarde'] ? $tardanzas['tarde']['tardanza'] : '-' ?>
                                                    </td>
                                                    </tr>
                                                    <?php $firstRow = false; ?>
                                                <?php endforeach; ?>
                                                <?php break; ?>
                                                
                                            <?php case 'permission': ?>
                                                <td><?= htmlspecialchars($row['dni']) ?></td>
                                                <td><?= htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) ?></td>
                                                <td><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) ?></td>
                                                <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                <td class="text-center nowrap"><?= date('d/m/Y', strtotime($row['fecha_permiso'])) ?></td>
                                                <td class="permiso-cell text-center"><?= htmlspecialchars($row['tipo_permiso']) ?></td>
                                                <td><?= htmlspecialchars($row['motivo']) ?></td>
                                                <td class="text-center"><?= $row['hora_salida'] ?></td>
                                                <td class="text-center"><?= $row['hora_retorno'] ?></td>
                                                <td class="text-center"><?= $row['hora_registro'] ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['registrado_por']) ?></td>
                                                <?php if (!$isEspectador): ?>
                                                    <td>
                                                        <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                            <button type="button" class="btn btn-secondary btn-sm" onclick="openEditPermissionModal(<?= $row['id'] ?>)">
                                                                <i class="fas fa-edit"></i> Editar
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endif; ?>
                                                <?php break; ?>
                                                
                                            <?php case 'no_asistencia': ?>
                                                <!-- Mostrar faltas por turno -->
                                                <?php if (!empty($row['faltas_manana'])): ?>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) ?></td>
                                                    <td><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-warning">MAÑANA</span>
                                                    </td>
                                                    <td class="text-center"><?= $row['total_faltas_manana'] ?></td>
                                                    </tr><tr data-area="<?= htmlspecialchars($row['area'] ?? '') ?>" data-cargo="<?= htmlspecialchars($row['puesto'] ?? '') ?>"><td style="text-align: center;"><?= $contador++ ?></td>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($row['faltas_tarde'])): ?>
                                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                                    <td><?= htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) ?></td>
                                                    <td><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) ?></td>
                                                    <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                    <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">TARDE</span>
                                                    </td>
                                                    <td class="text-center"><?= $row['total_faltas_tarde'] ?></td>
                                                <?php endif; ?>
                                                <?php break; ?>
                                                
                                            <?php case 'areas': ?>
                                                <td class="area-cell"><?= htmlspecialchars($row['area']) ?></td>
                                                <td><?= htmlspecialchars($row['puesto']) ?></td>
                                                <td class="text-center"><?= $row['empleados'] ?></td>
                                                <td class="text-center"><?= $row['dias_totales'] ?></td>
                                                <td class="text-center"><?= $row['asistencias'] ?></td>
                                                <td class="text-center"><?= $row['permisos'] ?></td>
                                                <td class="text-center"><?= $row['faltas'] ?></td>
                                                <td class="porcentaje-cell <?= 
                                                    $row['porcentaje_asistencia'] >= 90 ? 'porcentaje-alto' : 
                                                    ($row['porcentaje_asistencia'] >= 70 ? 'porcentaje-medio' : 'porcentaje-bajo')
                                                ?>">
                                                    <?= $row['porcentaje_asistencia'] ?>%
                                                </td>
                                                <?php break; ?>
                                                
                                            <?php default: // daily ?>
                                                <td class="text-center nowrap"><?= $row['fecha'] ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['dni']) ?></td>
                                                <td><?= htmlspecialchars(explode(' ', $row['nombre_completo'])[0]) ?></td>
                                                <td><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $row['nombre_completo']), 1))) ?></td>
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
                                        <td colspan="<?= $isEspectador ? '13' : '14' ?>" style="text-align: center; font-weight: bold;">
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

        <?php elseif ($section === 'admins'): ?>
            <!-- Sección de Administradores -->

            <div class="filters-container">
                <div class="filters-grid">
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="search_admin"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" id="search_admin" class="form-control" placeholder="Buscar administradores...">
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_rol_admin"><i class="fas fa-user-tag"></i> Rol</label>
                            <select id="filter_rol_admin" class="form-control">
                                <option value="">Todos los roles</option>
                                <option value="admin">Administrador</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="espectador">Espectador</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_estado_admin"><i class="fas fa-circle"></i> Estado</label>
                            <select id="filter_estado_admin" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <?php if ($isSuperAdmin): ?>
                            <button type="button" class="btn btn-success" onclick="openAddAdminModal()">
                                <i class="fas fa-plus"></i> Agregar Administrador
                            </button>
                        <?php endif; ?>
                        
                        <!-- Botón Exportar Excel - SOLO para no espectadores -->
                        <?php if (!$isEspectador): ?>
                            <button type="button" class="btn btn-warning" onclick="exportToExcel('filtered')">
                                <i class="fas fa-filter"></i> Exportar Filtrado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-table"></i> Lista de Administradores</div>
                <div class="table-responsive">
                    <?php if (!empty($paginatedAdmins)): ?>
                        <table id="admins-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>APELLIDOS</th>
                                    <th>NOMBRES</th>
                                    <th>ÁREA</th>
                                    <th>CARGO</th>
                                    <th>USUARIO</th>
                                    <th>ROL</th>
                                    <th>ESTADO</th>
                                    <th>FECHA CREACIÓN</th>
                                    <th>HORA</th>
                                    <th>CREADO POR</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = ($currentPage - 1) * $itemsPerPage + 1; ?>
                                <?php foreach ($paginatedAdmins as $admin): ?>
                                    <tr data-rol="<?= htmlspecialchars($admin['rol']) ?>" data-estado="<?= htmlspecialchars($admin['estado']) ?>">
                                        <td><?= $contador++ ?></td>
                                        <td><?= htmlspecialchars($admin['apellidos']) ?></td>
                                        <td><?= htmlspecialchars($admin['nombres']) ?></td>
                                        <td><?= htmlspecialchars($admin['area']) ?></td>
                                        <td><?= htmlspecialchars($admin['cargo']) ?></td>
                                        <td><?= htmlspecialchars($admin['usuario']) ?></td>
                                        <td>
                                            <span class="badge <?= $admin['rol'] === 'admin' ? 'badge-success' : ($admin['rol'] === 'supervisor' ? 'badge-info' : 'badge-secondary') ?>">
                                                <?= $admin['rol'] === 'admin' ? 'Administrador' : ($admin['rol'] === 'supervisor' ? 'Supervisor' : 'Espectador') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $admin['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $admin['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($admin['fecha_creacion'])) ?></td>
                                        <td><?= date('H:i:s', strtotime($admin['fecha_creacion'])) ?></td>
                                        <td><?= htmlspecialchars($admin['creado_por_nombre']) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <?php if ($isSuperAdmin && $admin['id'] != $_SESSION['admin_id']): ?>
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="openEditAdminModal(<?= $admin['id'] ?>)">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                        <input type="hidden" name="nuevo_estado" value="<?= $admin['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                                        <button type="submit" name="cambiar_estado_admin" class="btn btn-<?= $admin['estado'] === 'activo' ? 'warning' : 'success' ?> btn-sm">
                                                            <i class="fas fa-<?= $admin['estado'] === 'activo' ? 'pause' : 'play' ?>"></i>
                                                            <?= $admin['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
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
                            <p>No se encontraron administradores</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($section === 'trabajadores'): ?>
            <!-- Sección de Trabajadores -->

            <div class="filters-container">
                <div class="filters-grid">
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="search_trabajador"><i class="fas fa-search"></i> Buscar</label>
                            <input type="text" id="search_trabajador" class="form-control" placeholder="Buscar trabajadores...">
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_area_trabajador"><i class="fas fa-building"></i> Área</label>
                            <select id="filter_area_trabajador" class="form-control" onchange="updateCargoFilter('trabajador')">
                                <option value="">Todas las áreas</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area) ?>"><?= htmlspecialchars($area) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_cargo_trabajador"><i class="fas fa-briefcase"></i> Cargo</label>
                            <select id="filter_cargo_trabajador" class="form-control">
                                <option value="">Todos los cargos</option>
                                <?php foreach ($positions as $position): ?>
                                    <option value="<?= htmlspecialchars($position) ?>"><?= htmlspecialchars($position) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filter_estado_trabajador"><i class="fas fa-circle"></i> Estado</label>
                            <select id="filter_estado_trabajador" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <?php if ($isSuperAdmin || $isSupervisor): ?>
                            <button type="button" class="btn btn-success" onclick="openAddEmpleadoModal()">
                                <i class="fas fa-plus"></i> Agregar Trabajador
                            </button>
                        <?php endif; ?>
                        
                        <!-- Botón Exportar Excel - SOLO para no espectadores -->
                        <?php if (!$isEspectador): ?>
                            <button type="button" class="btn btn-warning" onclick="exportToExcel('filtered')">
                                <i class="fas fa-filter"></i> Exportar Filtrado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-table"></i> Lista de Trabajadores</div>
                <div class="table-responsive">
                    <?php if (!empty($paginatedEmployees)): ?>
                        <table id="trabajadores-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>FOTO</th>
                                    <th>DNI</th>
                                    <th>APELLIDOS</th>
                                    <th>NOMBRES</th>
                                    <th>ÁREA</th>
                                    <th>CARGO</th>
                                    <th>ESTADO</th>
                                    <th>CREADO POR</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = ($currentPage - 1) * $itemsPerPage + 1; ?>
                                <?php foreach ($paginatedEmployees as $empleado): ?>
                                    <tr data-area="<?= htmlspecialchars($empleado['area']) ?>" data-cargo="<?= htmlspecialchars($empleado['puesto']) ?>" data-estado="<?= htmlspecialchars($empleado['estado']) ?>">
                                        <td><?= $contador++ ?></td>
                                        <td>
                                            <?php if (!empty($empleado['foto']) && file_exists(FOTO_DIR . $empleado['foto'])): ?>
                                                <img src="<?= FOTO_DIR . $empleado['foto'] ?>" class="employee-photo" onclick="openImageModal('<?= FOTO_DIR . $empleado['foto'] ?>')">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="color: #666;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($empleado['dni']) ?></td>
                                        <td><?= htmlspecialchars($empleado['apellidos']) ?></td>
                                        <td><?= htmlspecialchars($empleado['nombres']) ?></td>
                                        <td class="area-cell"><?= htmlspecialchars($empleado['area']) ?></td>
                                        <td><?= htmlspecialchars($empleado['puesto']) ?></td>
                                        <td>
                                            <span class="badge <?= $empleado['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= $empleado['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($empleado['creado_por_nombre']) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                    <button type='button' class='btn btn-secondary btn-sm' onclick='openEditEmpleadoModal(<?= $empleado['id'] ?>)'>
                                                        <i class='fas fa-edit'></i> Editar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($isSuperAdmin || $isSupervisor): ?>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="empleado_id" value="<?= $empleado['id'] ?>">
                                                        <input type="hidden" name="nuevo_estado" value="<?= $empleado['estado'] === 'activo' ? 'inactivo' : 'activo' ?>">
                                                        <button type="submit" name="cambiar_estado_empleado" class="btn btn-<?= $empleado['estado'] === 'activo' ? 'warning' : 'success' ?> btn-sm">
                                                            <i class="fas fa-<?= $empleado['estado'] === 'activo' ? 'pause' : 'play' ?>"></i>
                                                            <?= $empleado['estado'] === 'activo' ? ' Desactivar' : ' Activar' ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
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
                            <p>No se encontraron trabajadores</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($section === 'config'): ?>
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

        <?php elseif ($section === 'history'): ?>
            <!-- Sección de Historial -->
            <div class="filters-container">
                <div class="filters-grid">
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="historial_fecha_inicio"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                            <input type="date" id="historial_fecha_inicio" name="historial_fecha_inicio" 
                                   class="form-control" value="<?= $historialFechaInicio ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="historial_fecha_fin"><i class="fas fa-calendar-alt"></i> Fecha Fin</label>
                            <input type="date" id="historial_fecha_fin" name="historial_fecha_fin" 
                                   class="form-control" value="<?= $historialFechaFin ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="historial_tabla"><i class="fas fa-table"></i> Tabla Afectada</label>
                            <select id="historial_tabla" name="historial_tabla" class="form-control">
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
                            <select id="historial_accion" name="historial_accion" class="form-control">
                                <option value="">Todas las acciones</option>
                                <option value="INSERT" <?= $historialAccion === 'INSERT' ? 'selected' : '' ?>>INSERT</option>
                                <option value="UPDATE" <?= $historialAccion === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                                <option value="DELETE" <?= $historialAccion === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <div class="form-group">
                            <label for="historial_creado_por"><i class="fas fa-user"></i> Modificado Por</label>
                            <input type="text" id="historial_creado_por" name="historial_creado_por" 
                                   class="form-control" value="<?= htmlspecialchars($historialCreadoPor) ?>" 
                                   placeholder="Buscar por usuario...">
                        </div>
                        
                        <div class="filter-actions">
                            <button type="button" onclick="applyHistoryFilters()" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                            
                            <!-- Botón Exportar Excel - SOLO para no espectadores -->
                            <?php if (!$isEspectador): ?>
                                <button type="button" class="btn btn-warning" onclick="exportToExcel('filtered')">
                                    <i class="fas fa-file-excel"></i> Exportar Filtrado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fas fa-table"></i> Historial de Cambios</div>
                <div class="table-responsive">
                    <?php if (!empty($paginatedHistorial)): ?>
                        <table id="history-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>TABLA AFECTADA</th>
                                    <th>USUARIO AFECTADO</th>
                                    <th>ACCION</th>
                                    <th>CAMPO MODIFICADO</th>
                                    <th>VALOR ANTERIOR</th>
                                    <th>VALOR NUEVO</th>
                                    <th>MOTIVO</th>
                                    <th>MODIFICADO POR</th>
                                    <th>FECHA MODIFICACION</th>
                                    <th>HORA MODIFICACION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $contador = ($currentPage - 1) * $itemsPerPage + 1; ?>
                                <?php foreach ($paginatedHistorial as $historial): ?>
                                    <tr>
                                        <td><?= $contador++ ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($historial['tabla_afectada']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($historial['usuario_afectado']) ?></td>
                                        <td>
                                            <span class="badge <?= 
                                                $historial['accion'] === 'INSERT' ? 'badge-success' : 
                                                ($historial['accion'] === 'UPDATE' ? 'badge-warning' : 'badge-danger')
                                            ?>">
                                                <?= htmlspecialchars($historial['accion']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($historial['campo_modificado']) ?></td>
                                        <td><?= htmlspecialchars($historial['valor_anterior']) ?></td>
                                        <td><?= htmlspecialchars($historial['valor_nuevo']) ?></td>
                                        <td><?= htmlspecialchars($historial['motivo']) ?></td>
                                        <td><?= htmlspecialchars($historial['modificado_por_nombre']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($historial['fecha_modificacion'])) ?></td>
                                        <td><?= date('H:i:s', strtotime($historial['fecha_modificacion'])) ?></td>
                                    </tr>
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
                            <p>No se encontraron registros en el historial</p>
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

    <!-- Modal para agregar administrador - MEJORADO: Cargos dinámicos por área -->
    <div id="addAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-user-plus"></i> Agregar Administrador</h3>
                <span class="close" onclick="closeModal('addAdminModal')">&times;</span>
            </div>
            <form method="post" action="" onsubmit="return validateAdminForm(this)">
                <div class="modal-body">
                    <div class="admin-form-container">
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
                            <select id="area" name="area" required class="form-control" onchange="updateAdminCargos()">
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
                            <label for="usuario">Usuario</label>
                            <input type="text" id="usuario" name="usuario" required class="form-control" placeholder="Ingrese usuario">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" required class="form-control" placeholder="Ingrese contraseña (mínimo 4 caracteres)" minlength="4">
                            <small class="text-muted">La contraseña debe tener al menos 4 caracteres</small>
                        </div>
                        
                        <div class="form-group form-horizontal-full">
                            <label for="rol">Rol</label>
                            <select id="rol" name="rol" required class="form-control">
                                <option value="">Seleccione rol</option>
                                <option value="admin">Administrador</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="espectador">Espectador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addAdminModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="agregar_admin">Agregar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar administrador - MEJORADO: Cargos dinámicos por área -->
    <div id="editAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Editar Administrador</h3>
                <span class="close" onclick="closeModal('editAdminModal')">&times;</span>
            </div>
            <form method="post" action="" onsubmit="return validateEditAdminForm(this)">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                
                <div class="modal-body">
                    <div class="admin-form-container">
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
                            <select id="edit_area" name="area" required class="form-control" onchange="updateEditAdminCargos()">
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
                            <label for="edit_usuario">Usuario</label>
                            <input type="text" id="edit_usuario" name="usuario" required class="form-control" placeholder="Ingrese usuario">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_password">Nueva Contraseña</label>
                            <input type="password" id="edit_password" name="password" class="form-control" placeholder="Dejar vacío para mantener la actual" minlength="4">
                            <small class="text-muted">Dejar vacío para mantener la contraseña actual. Mínimo 4 caracteres si se cambia.</small>
                        </div>
                        
                        <div class="form-group form-horizontal-full">
                            <label for="edit_rol">Rol</label>
                            <select id="edit_rol" name="rol" required class="form-control">
                                <option value="">Seleccione rol</option>
                                <option value="admin">Administrador</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="espectador">Espectador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editAdminModal')">Cancelar</button>
                    <button type="submit" class="btn btn-success" name="editar_admin">Guardar Cambios</button>
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
                                    text: item.nombre_completo + ' (' + item.dni + ')'
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
            
            // Filtros en tiempo real para reportes
            $('#search_input').on('input', function() {
                filterReportTable();
            });
            
            // Filtros de área y cargo para reportes
            $('#filter_area_report, #filter_cargo_report').on('change', function() {
                filterReportTable();
            });
            
            // Filtros para administradores
            $('#search_admin').on('input', function() {
                filterAdminsTable();
            });
            
            $('#filter_rol_admin, #filter_estado_admin').on('change', function() {
                filterAdminsTable();
            });
            
            // Filtros para trabajadores
            $('#search_trabajador').on('input', function() {
                filterTrabajadoresTable();
            });
            
            $('#filter_area_trabajador, #filter_cargo_trabajador, #filter_estado_trabajador').on('change', function() {
                filterTrabajadoresTable();
            });
            
            // Actualizar reporte al cambiar fechas - AHORA SIEMPRE FECHA ACTUAL
            $('#fecha_inicio, #fecha_fin').on('change', function() {
                var fechaInicio = $('#fecha_inicio').val();
                var fechaFin = $('#fecha_fin').val();
                
                // Si no se selecciona fecha, usar la fecha actual
                if (!fechaInicio) fechaInicio = '<?= date('Y-m-d') ?>';
                if (!fechaFin) fechaFin = '<?= date('Y-m-d') ?>';
                
                // Actualizar la página con las nuevas fechas
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('fecha_inicio', fechaInicio);
                currentUrl.searchParams.set('fecha_fin', fechaFin);
                window.location.href = currentUrl.toString();
            });
            
            // Actualizar vista de áreas
            $('#tipo_vista').on('change', function() {
                var tipoVista = $(this).val();
                window.location.href = '?<?= http_build_query(array_merge($_GET, ['tipo_vista' => 'TIPO_VISTA'])) ?>'
                    .replace('TIPO_VISTA', tipoVista);
            });
            
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
        });
        
        // Función de filtrado para reportes
        function filterReportTable() {
            var searchText = $('#search_input').val().toLowerCase();
            var areaFilter = $('#filter_area_report').val();
            var cargoFilter = $('#filter_cargo_report').val();
            
            $('#report-table tbody tr').each(function() {
                var area = $(this).data('area') || '';
                var cargo = $(this).data('cargo') || '';
                var rowText = $(this).text().toLowerCase();
                
                var matchSearch = searchText === '' || rowText.indexOf(searchText) > -1;
                var matchArea = areaFilter === '' || area === areaFilter;
                var matchCargo = cargoFilter === '' || cargo === cargoFilter;
                
                if (matchSearch && matchArea && matchCargo) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Función de filtrado para administradores
        function filterAdminsTable() {
            var searchText = $('#search_admin').val().toLowerCase();
            var rolFilter = $('#filter_rol_admin').val();
            var estadoFilter = $('#filter_estado_admin').val();
            
            $('#admins-table tbody tr').each(function() {
                var rol = $(this).data('rol') || '';
                var estado = $(this).data('estado') || '';
                var rowText = $(this).text().toLowerCase();
                
                var matchSearch = searchText === '' || rowText.indexOf(searchText) > -1;
                var matchRol = rolFilter === '' || rol === rolFilter;
                var matchEstado = estadoFilter === '' || estado === estadoFilter;
                
                if (matchSearch && matchRol && matchEstado) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Función de filtrado para trabajadores
        function filterTrabajadoresTable() {
            var searchText = $('#search_trabajador').val().toLowerCase();
            var areaFilter = $('#filter_area_trabajador').val();
            var cargoFilter = $('#filter_cargo_trabajador').val();
            var estadoFilter = $('#filter_estado_trabajador').val();
            
            $('#trabajadores-table tbody tr').each(function() {
                var area = $(this).data('area') || '';
                var cargo = $(this).data('cargo') || '';
                var estado = $(this).data('estado') || '';
                var rowText = $(this).text().toLowerCase();
                
                var matchSearch = searchText === '' || rowText.indexOf(searchText) > -1;
                var matchArea = areaFilter === '' || area === areaFilter;
                var matchCargo = cargoFilter === '' || cargo === cargoFilter;
                var matchEstado = estadoFilter === '' || estado === estadoFilter;
                
                if (matchSearch && matchArea && matchCargo && matchEstado) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Aplicar filtros de historial
        function applyHistoryFilters() {
            var fechaInicio = $('#historial_fecha_inicio').val();
            var fechaFin = $('#historial_fecha_fin').val();
            var tabla = $('#historial_tabla').val();
            var accion = $('#historial_accion').val();
            var creadoPor = $('#historial_creado_por').val();
            
            var params = new URLSearchParams();
            params.set('section', 'history');
            if (fechaInicio) params.set('historial_fecha_inicio', fechaInicio);
            if (fechaFin) params.set('historial_fecha_fin', fechaFin);
            if (tabla) params.set('historial_tabla', tabla);
            if (accion) params.set('historial_accion', accion);
            if (creadoPor) params.set('historial_creado_por', creadoPor);
            
            window.location.href = '?' + params.toString();
        }
        
        // Función para exportar a Excel - SOLO si NO es espectador
        function exportToExcel(exportType) {
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('export_excel', '1');
            currentUrl.searchParams.set('export_type', exportType);
            
            // Agregar filtros actuales
            var searchText = $('#search_input').val() || $('#search_admin').val() || $('#search_trabajador').val() || '';
            var areaFilter = $('#filter_area_report').val() || $('#filter_area_trabajador').val() || '';
            var cargoFilter = $('#filter_cargo_report').val() || $('#filter_cargo_trabajador').val() || '';
            var rolFilter = $('#filter_rol_admin').val() || '';
            var estadoFilter = $('#filter_estado_admin').val() || $('#filter_estado_trabajador').val() || '';
            
            if (searchText) currentUrl.searchParams.set('search_term', searchText);
            if (areaFilter) currentUrl.searchParams.set('filter_area', areaFilter);
            if (cargoFilter) currentUrl.searchParams.set('filter_cargo', cargoFilter);
            if (rolFilter) currentUrl.searchParams.set('filter_rol', rolFilter);
            if (estadoFilter) currentUrl.searchParams.set('filter_estado', estadoFilter);
            
            window.location.href = currentUrl.toString();
        }
        
        // Función para actualizar filtro de cargos según el área seleccionada
        function updateCargoFilter(tipo) {
            var areaSelect = document.getElementById('filter_area_' + tipo);
            var cargoSelect = document.getElementById('filter_cargo_' + tipo);
            var area = areaSelect.value;
            
            if (area) {
                fetch('?get_cargos_by_area=1&area=' + encodeURIComponent(area))
                    .then(response => response.json())
                    .then(cargos => {
                        cargoSelect.innerHTML = '<option value="">Todos los cargos</option>';
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
                cargoSelect.innerHTML = '<option value="">Todos los cargos</option>';
                // Cargar todos los cargos disponibles
                <?php 
                $allPositions = [];
                foreach ($CARGOS_POR_AREA as $cargos) {
                    $allPositions = array_merge($allPositions, $cargos);
                }
                $allPositions = array_unique($allPositions);
                ?>
                var allPositions = <?= json_encode($allPositions) ?>;
                allPositions.forEach(cargo => {
                    var option = document.createElement('option');
                    option.value = cargo;
                    option.textContent = cargo;
                    cargoSelect.appendChild(option);
                });
            }
        }
        
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
                        document.getElementById('edit_nombres').value = data.nombres || '';
                        document.getElementById('edit_apellidos').value = data.apellidos || '';
                        document.getElementById('edit_area').value = data.area || '';
                        document.getElementById('edit_usuario').value = data.usuario || '';
                        document.getElementById('edit_rol').value = data.rol || '';
                        
                        // Actualizar cargos según el área
                        updateEditAdminCargos(data.area, data.cargo);
                        
                        document.getElementById('editAdminModal').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del administrador');
                });
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Función para actualizar cargos de empleado según el área seleccionada (agregar)
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
        
        // Función para actualizar cargos de empleado según el área seleccionada (editar)
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
        
        // Función para actualizar cargos de administrador según el área seleccionada (agregar)
        function updateAdminCargos() {
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
        
        // Función para actualizar cargos de administrador según el área seleccionada (editar)
        function updateEditAdminCargos(area = null, selectedCargo = null) {
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
        
        // Validación de formulario de administrador (agregar)
        function validateAdminForm(form) {
            var password = form.password.value;
            
            if (password.length < 4) {
                alert('La contraseña debe tener al menos 4 caracteres');
                form.password.focus();
                return false;
            }
            
            return true;
        }
        
        // Validación de formulario de administrador (editar)
        function validateEditAdminForm(form) {
            var password = form.password.value;
            
            // Solo validar si se está cambiando la contraseña
            if (password !== '' && password.length < 4) {
                alert('La contraseña debe tener al menos 4 caracteres');
                form.password.focus();
                return false;
            }
            
            return true;
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