<?php

// =============================================
// CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_asistencia1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_TIMEZONE', 'America/Lima');
define('FOTO_DIR', 'fotos_empleados/');

date_default_timezone_set(APP_TIMEZONE);

// Crear directorio de fotos_empleados si no existe
if (!file_exists(FOTO_DIR)) {
    mkdir(FOTO_DIR, 0777, true);
}

session_start();

// Almacenar notificaciones en sesión para persistencia después de redirección
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = [];
}

if (!isset($_SESSION['employee_data'])) {
    $_SESSION['employee_data'] = null;
}

try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error en el sistema. Por favor intente más tarde.");
}

// Crear tablas si no existen 
$pdo->exec("
    CREATE TABLE IF NOT EXISTS empleados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dni VARCHAR(8) NOT NULL UNIQUE,
        nombres VARCHAR(50) NOT NULL,
        apellidos VARCHAR(50) NOT NULL,
        area VARCHAR(50) NOT NULL,
        puesto VARCHAR(50) NOT NULL,
        inicio_contrato DATE NOT NULL,
        fin_contrato DATE NULL,
        tipo_personal ENUM('Administrativo', 'Docente') NOT NULL DEFAULT 'Administrativo',
        entrada_manana TIME NULL,
        salida_manana TIME NULL,
        entrada_tarde TIME NULL,
        salida_tarde TIME NULL,
        foto VARCHAR(255) NULL,
        estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
        creado_por INT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS registros_asistencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empleado_id INT NOT NULL,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        tipo_registro ENUM('SISTEMA', 'MANUAL') NOT NULL DEFAULT 'SISTEMA',
        registrado_por INT NULL,
        FOREIGN KEY (empleado_id) REFERENCES empleados(id)
    );
    
    CREATE TABLE IF NOT EXISTS permisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empleado_id INT NOT NULL,
        tipo_permiso VARCHAR(50) NOT NULL,
        motivo TEXT NOT NULL,
        fecha_permiso DATE NOT NULL,
        hora_salida TIME NOT NULL,
        hora_retorno TIME NOT NULL,
        hora_registro TIME NOT NULL,
        tipo_registro ENUM('SISTEMA', 'MANUAL') NOT NULL DEFAULT 'SISTEMA',
        registrado_por INT NULL,
        FOREIGN KEY (empleado_id) REFERENCES empleados(id)
    );
    
    CREATE TABLE IF NOT EXISTS usuarios_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombres VARCHAR(50) NOT NULL,
        apellidos VARCHAR(50) NOT NULL,
        area VARCHAR(50) NOT NULL,
        cargo VARCHAR(50) NOT NULL,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'supervisor') NOT NULL,
        estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
        creado_por INT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS configuracion_sistema (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(50) NOT NULL UNIQUE,
        valor VARCHAR(255) NOT NULL,
        descripcion TEXT NULL
    );
    
    CREATE TABLE IF NOT EXISTS historial_cambios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tabla_afectada VARCHAR(50) NOT NULL,
        usuario_afectado VARCHAR(100) NOT NULL,
        accion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
        campo_modificado VARCHAR(50) NULL,
        valor_anterior TEXT NULL,
        valor_nuevo TEXT NULL,
        motivo TEXT NULL,
        modificado_por INT NULL,
        fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// Insertar datos de prueba (solo para desarrollo)
$pdo->exec("
    INSERT IGNORE INTO empleados (id, dni, nombres, apellidos, area, puesto, inicio_contrato, fin_contrato, tipo_personal, foto, estado) VALUES
    (1, '12345678', 'Juan', 'Pérez', 'Académico', 'Profesor', '2025-01-01', '2027-12-31', 'Docente', 'default.png', 'activo');
    
    INSERT IGNORE INTO usuarios_admin (id, nombres, apellidos, area, cargo, usuario, password, rol, estado) VALUES 
    (1, 'Administrador', 'Principal', 'Sistemas', 'Administrador', 'admin', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo');
    
    INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
    ('minutos_tolerancia', '5', 'Minutos de tolerancia para tardanzas');
");

// =============================================
// CLASE PRINCIPAL DEL SISTEMA DE ASISTENCIA
// =============================================
class AttendanceSystem {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function verifyEmployee(string $dni): ?array {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare(
            "SELECT * FROM empleados 
             WHERE dni = ? 
             AND estado = 'activo'
             AND inicio_contrato <= ? 
             AND (fin_contrato >= ? OR fin_contrato IS NULL)"
        );
        $stmt->execute([$dni, $today, $today]);
        return $stmt->fetch() ?: null;
    }
    
    public function registerAttendance(array $employee): array {
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        // Verificar si ya existe un registro para hoy (ya no limitamos a uno)
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM registros_asistencia 
             WHERE empleado_id = ? AND fecha = ?"
        );
        $stmt->execute([$employee['id'], $today]);
        $existing = $stmt->fetch();
        
        // Ya no mostramos advertencia si ya hay registros, permitimos múltiples
        
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO registros_asistencia 
                (empleado_id, fecha, hora, tipo_registro) 
                VALUES (?, ?, ?, 'SISTEMA')"
            );
            
            $stmt->execute([
                $employee['id'],
                $today,
                $currentTime
            ]);
            
            return [
                'success' => true, 
                'message' => 'Registro de asistencia completado',
                'type' => 'success'
            ];
        } catch (PDOException $e) {
            error_log("Error al registrar asistencia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la asistencia', 'type' => 'error'];
        }
    }
    
    public function registerPermission(array $employee, array $requestData): array {
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        try {
            // Validar horas
            if (strtotime($requestData['hora_salida']) >= strtotime($requestData['hora_retorno'])) {
                return ['success' => false, 'message' => 'La hora de retorno debe ser posterior a la hora de salida', 'type' => 'error'];
            }
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO permisos 
                (empleado_id, tipo_permiso, motivo, fecha_permiso, hora_salida, hora_retorno, hora_registro, tipo_registro) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'SISTEMA')"
            );
            
            $stmt->execute([
                $employee['id'],
                $requestData['tipo_permiso'],
                $requestData['motivo'],
                $today,
                $requestData['hora_salida'],
                $requestData['hora_retorno'],
                $currentTime
            ]);
            
            return [
                'success' => true, 
                'message' => 'Permiso registrado con éxito. Código: #' . $this->pdo->lastInsertId(),
                'type' => 'success',
                'codigo' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error al registrar permiso: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar el permiso', 'type' => 'error'];
        }
    }
    
    public function loginAdmin(string $usuario, string $contrasena): array {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ? AND estado = 'activo'");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($contrasena, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['usuario'];
            $_SESSION['admin_role'] = $user['rol'];
            $_SESSION['admin_id'] = $user['id'];
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }
}

// =============================================
// DETERMINAR ESTILO TEMPORAL
// =============================================
function getTemporalStyle() {
    $currentMonth = date('n');
    $currentDay = date('j');
    
    // Halloween: 25/09 al 10/11
    if (($currentMonth == 2 && $currentDay >= 25) || 
        ($currentMonth == 3) || 
        ($currentMonth == 4 && $currentDay <= 10)) {
        return 'halloween';
    }
    
    // Navidad y Año Nuevo: 01/12 al 15/01
    if (($currentMonth == 10 && $currentDay >= 1) || 
        ($currentMonth == 1 && $currentDay <= 15)) {
        return 'navidad';
    }
    
    return 'default';
}

$currentStyle = getTemporalStyle();

// =============================================
// PROCESAMIENTO DEL FORMULARIO
// =============================================
$attendanceSystem = new AttendanceSystem($pdo);

// Recuperar notificaciones y datos de empleado de la sesión
$notification = !empty($_SESSION['notifications']) ? array_shift($_SESSION['notifications']) : null;
$employeeData = $_SESSION['employee_data'] ?? null;

// Limpiar datos de la sesión después de recuperarlos
if ($employeeData) {
    $_SESSION['employee_data'] = null;
}

// Procesar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Procesar login admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $result = $attendanceSystem->loginAdmin($_POST['usuario'], $_POST['contrasena']);
    if ($result['success']) {
        header("Location: admin.php");
        exit;
    } else {
        $_SESSION['notifications'][] = ['message' => $result['message'], 'type' => 'error'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Procesamiento del formulario principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_login'])) {
    if (isset($_POST['dni']) && !isset($_POST['tipo_permiso'])) {
        // Procesamiento del registro normal de asistencia
        $dni = trim($_POST['dni']);
        
        if (empty($dni) || !preg_match('/^\d{8}$/', $dni)) {
            $_SESSION['notifications'][] = ['message' => 'DNI inválido (8 dígitos)', 'type' => 'error'];
        } else {
            $employee = $attendanceSystem->verifyEmployee($dni);
            
            if (!$employee) {
                $_SESSION['notifications'][] = ['message' => 'DNI no registrado o contrato no vigente', 'type' => 'error'];
            } else {
                $_SESSION['employee_data'] = $employee; // Guardar datos del empleado en sesión
                $result = $attendanceSystem->registerAttendance($employee);
                $_SESSION['notifications'][] = $result;
            }
        }
        
        // Prevenir reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
        
    } elseif (isset($_POST['tipo_permiso'])) {
        // Procesamiento de permiso especial
        $dni = trim($_POST['dni']);
        
        if (empty($dni) || !preg_match('/^\d{8}$/', $dni)) {
            $_SESSION['notifications'][] = ['message' => 'DNI inválido (8 dígitos)', 'type' => 'error'];
        } else {
            $employee = $attendanceSystem->verifyEmployee($dni);
            
            if (!$employee) {
                $_SESSION['notifications'][] = ['message' => 'DNI no registrado o contrato no vigente', 'type' => 'error'];
            } else {
                $_SESSION['employee_data'] = $employee; // Guardar datos del empleado en sesión
                $requestData = [
                    'tipo_permiso' => $_POST['tipo_permiso'],
                    'motivo' => $_POST['motivo'],
                    'hora_salida' => $_POST['hora_salida'],
                    'hora_retorno' => $_POST['hora_retorno']
                ];
                
                $result = $attendanceSystem->registerPermission($employee, $requestData);
                $_SESSION['notifications'][] = $result;
            }
        }
        
        // Prevenir reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// =============================================
// MENSAJES POR ESTILO TEMPORAL
// =============================================
$welcomeMessages = [
    'default' => [
        "Ingrese su DNI en el panel",
        "izquierdo para registrar su asistencia"
    ],
    'halloween' => [
        "¡Cuidado con los fantasmas!",
        "Registra tu DNI antes de que desaparezcas..."
    ],
    'navidad' => [
        "🎄 ¡Felices Fiestas! 🎅",
        "Que la alegría de esta temporada llene tu corazón de paz"
    ]
];

$currentMessages = $welcomeMessages[$currentStyle] ?? $welcomeMessages['default'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia | UFR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            /* Paleta de colores principal mejorada para reducir fatiga visual */
            --primary: #8A1538;
            --primary-light: #A42D52;
            --primary-dark: #6D0E2D;
            --secondary: #D4AF37;
            --secondary-light: #E8C766;
            --secondary-dark: #BF9428;
            --success: #4CAF50;
            --error: #F44336;
            --warning: #FFC107;
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
        
        /* Estilos Halloween - Colores más suaves */
        .halloween {
            --primary: #8B0000;
            --primary-light: #A52A2A;
            --primary-dark: #5C0000;
            --secondary: #FF8C00;
            --secondary-light: #FFA54F;
            --secondary-dark: #CD6600;
            --success: #228B22;
            --error: #DC143C;
            --warning: #FFD700;
        }
        
        /* Estilos Navidad y Año Nuevo - Colores más equilibrados */
        .navidad {
            --primary: #B71C1C;
            --primary-light: #D32F2F;
            --primary-dark: #8B0000;
            --secondary: #F5F5F5;
            --secondary-light: #FFFFFF;
            --secondary-dark: #E0E0E0;
            --success: #2E7D32;
            --error: #C62828;
            --warning: #FF8F00;
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
            font-size: 1.25em;
            overflow: hidden;
        }
        
        .app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
            position: relative;
            overflow: hidden;
        }
        
        .left-panel {
            width: 40%;
            background-color: var(--white);
            padding: 3.75rem 5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-md);
            height: 100vh;
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 7.5px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }
        
        .right-panel {
            width: 60%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3.75rem;
            color: var(--white);
            position: relative;
            overflow: hidden;
            height: 100vh;
        }
        
        .right-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .header {
            text-align: center;
            margin-bottom: 3.75rem;
        }
        
        .logo {
            width: 187.5px;
            margin-bottom: 1.875rem;
            filter: brightness(1.2) drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            object-fit: contain;
        }
        
        .header h1 {
            color: var(--primary);
            margin-bottom: 0.625rem;
            font-size: 2.25rem;
            font-weight: 700;
        }
        
        .header p {
            color: var(--gray);
            font-size: 1.125rem;
        }
        
        .form-group {
            margin-bottom: 1.875rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.625rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 1.125rem;
        }
        
        input[type="text"], input[type="password"], input[type="time"], select, textarea {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2.5px solid var(--light);
            border-radius: 10px;
            font-size: 1.25rem;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
        }
        
        input[type="text"]:focus, input[type="password"]:focus, input[type="time"]:focus, select:focus, textarea:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3.75px rgba(138, 21, 56, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 125px;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 1.25rem;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            box-shadow: 0 5px 7.5px rgba(138, 21, 56, 0.2);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2.5px);
            box-shadow: 0 7.5px 15px rgba(138, 21, 56, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
            box-shadow: 0 5px 7.5px rgba(212, 175, 55, 0.2);
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2.5px);
            box-shadow: 0 7.5px 15px rgba(212, 175, 55, 0.3);
        }
        
        .datetime-container {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 100%;
            padding: 0 2.5rem;
        }
        
        .current-date {
            font-size: 1.875rem;
            margin-bottom: 0.625rem;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .current-time {
            font-size: 6.25rem;
            font-weight: 300;
            letter-spacing: 2.5px;
            font-variant-numeric: tabular-nums;
            width: 500px;
            margin: 0 auto;
            line-height: 1;
            font-family: 'Courier New', monospace;
        }
        
        .welcome-container {
            position: absolute;
            bottom: 30%;
            left: 0;
            width: 100%;
            padding: 0 2.5rem;
            text-align: center;
        }
        
        .welcome-message {
            font-size: 2.25rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            text-shadow: 0 2.5px 5px rgba(0,0,0,0.2);
            color: #FFFFFF;
            animation: welcome-color-change 3s infinite alternate;
        }
        
        @keyframes welcome-color-change {
            0% {
                color: #FFFFFF;
            }
            100% {
                color: #FFD700;
            }
        }
        
        .typing-container {
            display: inline-block;
            text-align: center;
            min-height: 5.625rem;
        }
        
        .typing-line {
            display: block;
            height: 1.875rem;
            overflow: hidden;
            margin-bottom: 0.625rem;
        }
        
        .typing-text {
            border-right: 2.5px solid var(--white);
            display: inline-block;
            animation: blink-caret 0.75s step-end infinite;
            color: #FFFFFF;
        }
        
        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: var(--white); }
        }
        
        .employee-card {
            background-color: rgba(247, 241, 241, 1);
            backdrop-filter: blur(10px);
            padding: 1.875rem;
            border-radius: 15px;
            width: 80%;
            max-width: 625px;
            border: 2.5px solid rgba(255, 255, 255, 0.2);
            transition: all 0.5s ease;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1002;
            box-shadow: 0 12.5px 31.25px rgba(0,0,0,0.2);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .employee-card.show {
            opacity: 1;
            visibility: visible;
        }
        
        .employee-photo {
            width: 125px;
            height: 125px;
            border-radius: 50%;
            object-fit: cover;
            border: 3.75px solid var(--white);
            box-shadow: var(--shadow-md);
            margin-bottom: 1.25rem;
        }
        
        .employee-card h3 {
            margin-bottom: 1.25rem;
            border-bottom: 1.25px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.625rem;
            font-size: 1.375rem;
            font-weight: 600;
            width: 100%;
            text-align: center;
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            width: 100%;
        }
        
        .employee-info p {
            margin-bottom: 0.625rem;
            font-weight: 500;
        }
        
        .employee-info strong {
            display: block;
            font-weight: 400;
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }
        
        .footer {
            margin-top: auto;
            text-align: center;
            color: var(--gray);
            font-size: 1rem;
            padding-top: 1.25rem;
            border-top: 1.25px solid var(--light);
        }
        
        .action-buttons {
            margin-top: 1.875rem;
            display: flex;
            gap: 1.25rem;
        }
        
        .action-buttons .btn {
            margin-bottom: 0;
            flex: 1;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 800px;
            overflow: hidden;
            transform: translateY(25px);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 1.875rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.25px solid var(--light);
            background-color: var(--primary);
            color: white;
            position: relative;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.875rem;
            cursor: pointer;
            padding: 0 0.625rem;
            transition: var(--transition);
            position: absolute;
            right: 18.75px;
            top: 18.75px;
        }
        
        .modal-close:hover {
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 1.875rem;
        }
        
        .permission-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }
        
        .permission-form-grid .form-group {
            margin-bottom: 1.25rem;
        }
        
        .permission-form-grid .full-width {
            grid-column: 1 / -1;
        }
        
        .form-row {
            display: flex;
            gap: 1.25rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .notification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(3px);
        }
        
        .notification-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .notification {
            position: fixed;
            top: 25px;
            right: 25px;
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 375px;
            overflow: hidden;
            transform: translateY(-125px);
            transition: all 0.3s ease;
            z-index: 1003;
            opacity: 0;
            visibility: hidden;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .notification-header {
            padding: 1.25rem;
            display: flex;
            align-items: center;
            border-bottom: 1.25px solid var(--light);
        }
        
        .notification-icon {
            width: 37.5px;
            height: 37.5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
        }
        
        .success .notification-icon {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }
        
        .error .notification-icon {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error);
        }
        
        .warning .notification-icon {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.125rem;
        }
        
        .notification-body {
            padding: 1.25rem;
            color: var(--dark);
            font-size: 1.125rem;
        }
        
        .progress-bar {
            height: 5px;
            background-color: rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        
        .progress-bar::after {
            content: '';
            display: block;
            height: 100%;
            animation: progress 2.5s linear forwards;
        }
        
        .success .progress-bar::after {
            background-color: var(--success);
        }
        
        .error .progress-bar::after {
            background-color: var(--error);
        }
        
        .warning .progress-bar::after {
            background-color: var(--warning);
        }
        
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
        
        .codigo-solicitud {
            background-color: var(--primary);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 25px;
            display: inline-block;
            margin-top: 1.25rem;
            font-weight: 600;
            font-size: 1.375rem;
        }
        
        .admin-access-btn {
            position: fixed;
            top: 25px;
            right: 25px;
            background-color: var(--primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        
        .admin-access-btn:hover {
            background-color: var(--primary-dark);
            transform: scale(1.1) rotate(30deg);
        }
        
        /* =============================================
           ESTILOS HALLOWEEN - MEJORADOS
           ============================================= */
        
        .halloween .bg-animation {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0a 100%);
        }
        
        .halloween .bg-circle {
            background: rgba(139, 0, 0, 0.1);
        }
        
        .halloween .right-panel::before {
            background: radial-gradient(circle, rgba(139, 0, 0, 0.3) 0%, rgba(139, 0, 0, 0) 70%);
        }
        
        .halloween .ghost {
            position: absolute;
            width: 80px;
            height: 100px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50% 50% 0 0;
            animation: float-ghost-tenebroso 25s infinite linear;
            z-index: 1;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
            top: -150px;
        }
        
        .halloween .ghost::before {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            background: #333;
            border-radius: 50%;
            top: 25px;
            left: 15px;
            box-shadow: 25px 0 #333;
        }
        
        .halloween .ghost::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            bottom: -15px;
            left: 0;
            box-shadow: 20px 0 rgba(255, 255, 255, 0.9),
                        40px 0 rgba(255, 255, 255, 0.9),
                        60px 0 rgba(255, 255, 255, 0.9);
        }
        
        @keyframes float-ghost-tenebroso {
            0% {
                transform: translateX(-150px) translateY(0) rotate(0deg);
                opacity: 0.3;
            }
            25% {
                opacity: 0.8;
                filter: drop-shadow(0 0 15px rgba(255, 0, 0, 0.7));
            }
            50% {
                opacity: 1;
                filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.8));
            }
            75% {
                opacity: 0.8;
                filter: drop-shadow(0 0 15px rgba(255, 165, 0, 0.7));
            }
            100% {
                transform: translateX(100vw) translateY(100vh) rotate(0deg);
                opacity: 0.3;
            }
        }
        
        .halloween .blood-drop {
            position: absolute;
            width: 20px;
            height: 30px;
            background: #8B0000;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: drip-blood-tenebroso 6s infinite linear;
            z-index: 1;
            filter: drop-shadow(0 0 5px rgba(139, 0, 0, 0.7));
            top: -50px;
        }
        
        .halloween .blood-drop::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 5px;
            width: 10px;
            height: 5px;
            background: #8B0000;
            border-radius: 50%;
            opacity: 0.7;
        }
        
        @keyframes drip-blood-tenebroso {
            0% {
                transform: translateY(-100px) translateX(0) scale(0.3);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            50% {
                opacity: 1;
                transform: translateY(50vh) translateX(10px) scale(1);
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(100vh) translateX(20px) scale(1.2);
                opacity: 0;
            }
        }
        
        .halloween .welcome-message {
            text-shadow: 0 0 10px #8B0000, 0 0 20px #8B0000, 0 0 30px #8B0000;
            animation: spooky-text-tenebroso 2s infinite alternate;
        }
        
        .halloween .current-time {
            color: #FF4500;
            text-shadow: 0 0 10px #8B0000;
        }
        
        .halloween .current-date {
            color: #FFD700;
            text-shadow: 0 0 5px #8B0000;
        }
        
        @keyframes spooky-text-tenebroso {
            0% {
                text-shadow: 0 0 10px #8B0000, 0 0 20px #8B0000, 0 0 30px #8B0000;
                transform: skew(0deg, 0deg);
            }
            25% {
                text-shadow: 0 0 15px #FF0000, 0 0 25px #FF0000, 0 0 35px #FF0000;
                transform: skew(1deg, -1deg);
            }
            50% {
                text-shadow: 0 0 20px #8B0000, 0 0 30px #8B0000, 0 0 40px #8B0000;
                transform: skew(-1deg, 1deg);
            }
            75% {
                text-shadow: 0 0 15px #FF4500, 0 0 25px #FF4500, 0 0 35px #FF4500;
                transform: skew(0.5deg, -0.5deg);
            }
            100% {
                text-shadow: 0 0 10px #8B0000, 0 0 20px #8B0000, 0 0 30px #8B0000;
                transform: skew(-0.5deg, 0.5deg);
            }
        }
        
        .halloween .right-panel {
            animation: halloween-breathing 8s infinite ease-in-out;
        }
        
        @keyframes halloween-breathing {
            0%, 100% {
                background: linear-gradient(135deg, #8B0000, #5C0000);
            }
            50% {
                background: linear-gradient(135deg, #5C0000, #8B0000);
            }
        }
        
        /* =============================================
           ESTILOS NAVIDAD Y AÑO NUEVO - MEJORADOS
           ============================================= */
        
        .navidad .bg-animation {
            background: linear-gradient(135deg, #0d0d0d 0%, #1a0a0a 100%);
        }
        
        .navidad .bg-circle {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .navidad .right-panel::before {
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        }
        
        /* Copos de nieve modernos */
        .navidad .snowflake {
            position: absolute;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            opacity: 0;
            animation: snowfall-modern 12s linear infinite;
            z-index: 1;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
            font-weight: 300;
        }
        
        @keyframes snowfall-modern {
            0% {
                transform: translateY(-100px) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(100vh) translateX(30px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Luces navideñas modernas */
        .navidad .christmas-light {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: christmas-light-modern 3s infinite alternate;
            z-index: 1;
            box-shadow: 0 0 15px currentColor;
            filter: blur(1px);
        }
        
        @keyframes christmas-light-modern {
            0%, 100% {
                opacity: 0.4;
                transform: scale(0.8);
            }
            50% {
                opacity: 1;
                transform: scale(1.3);
            }
        }
        
        /* Estrellas doradas */
        .navidad .gold-star {
            position: absolute;
            color: #FFD700;
            font-size: 1.5rem;
            opacity: 0;
            animation: star-twinkle-modern 4s infinite ease-in-out;
            z-index: 1;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
        }
        
        @keyframes star-twinkle-modern {
            0%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        /* Efecto de texto navideño moderno */
        .navidad .welcome-message {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            animation: christmas-text-modern 4s infinite alternate;
        }
        
        @keyframes christmas-text-modern {
            0% {
                transform: translateY(0px);
            }
            100% {
                transform: translateY(-5px);
            }
        }
        
        .navidad .current-time {
            color: #FFD700;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
            animation: time-glow-modern 3s infinite alternate;
            font-weight: 300;
        }
        
        @keyframes time-glow-modern {
            from {
                text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
            }
            to {
                text-shadow: 0 0 25px rgba(255, 215, 0, 0.6), 0 0 35px rgba(255, 215, 0, 0.4);
            }
        }
        
        .navidad .current-date {
            color: #FFFFFF;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            font-weight: 400;
        }
        
        /* Efecto de respiración navideña moderna */
        .navidad .right-panel {
            animation: christmas-breathing-modern 8s infinite ease-in-out;
            background: linear-gradient(135deg, #B71C1C, #D32F2F, #B71C1C);
            background-size: 200% 200%;
        }
        
        @keyframes christmas-breathing-modern {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }
        
        /* Decoraciones navideñas minimalistas */
        .navidad .christmas-ornament {
            position: absolute;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #FFD700, #b8860b);
            animation: ornament-float-modern 10s infinite ease-in-out;
            z-index: 1;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        @keyframes ornament-float-modern {
            0%, 100% {
                transform: translateY(0px) rotate(0deg) scale(1);
            }
            33% {
                transform: translateY(-15px) rotate(120deg) scale(1.1);
            }
            66% {
                transform: translateY(8px) rotate(240deg) scale(0.9);
            }
        }
        
        /* Cintas decorativas modernas */
        .navidad .ribbon {
            position: absolute;
            width: 100px;
            height: 40px;
            background: linear-gradient(45deg, #b8860b, #daa520, #b8860b);
            transform: rotate(-45deg);
            animation: ribbon-sway-modern 6s infinite ease-in-out;
            z-index: 0;
            opacity: 0.1;
            box-shadow: 0 2px 10px rgba(184, 134, 11, 0.3);
        }
        
        @keyframes ribbon-sway-modern {
            0%, 100% {
                transform: rotate(-45deg) translateX(0px);
            }
            50% {
                transform: rotate(-45deg) translateX(10px);
            }
        }
        
        /* Fuegos artificiales minimalistas */
        .navidad .firework {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            animation: firework-explode 1.5s forwards;
            z-index: 1;
        }
        
        @keyframes firework-explode {
            0% {
                transform: translateY(0) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }
        
        /* Corazones navideños */
        .navidad .heart {
            position: absolute;
            color: #FF6B6B;
            font-size: 1.2rem;
            opacity: 0;
            animation: heart-float 8s linear infinite;
            z-index: 1;
            text-shadow: 0 0 10px rgba(255, 107, 107, 0.7);
        }
        
        @keyframes heart-float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100px) translateX(20px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Campanas navideñas */
        .navidad .bell {
            position: absolute;
            color: #FFD700;
            font-size: 1.5rem;
            opacity: 0;
            animation: bell-ring 6s infinite ease-in-out;
            z-index: 1;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
        }
        
        @keyframes bell-ring {
            0%, 100% {
                transform: rotate(-10deg);
                opacity: 0.7;
            }
            50% {
                transform: rotate(10deg);
                opacity: 1;
            }
        }
        
        /* Mejoras de compatibilidad para background-clip */
        .background-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
        }
        
        /* Mejoras de accesibilidad y contraste */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Mejoras de contraste para mejor legibilidad */
        .high-contrast {
            --primary: #000000;
            --primary-light: #333333;
            --primary-dark: #000000;
            --secondary: #FFFFFF;
            --secondary-light: #F5F5F5;
            --secondary-dark: #CCCCCC;
            --text-dark: #000000;
            --text-light: #333333;
        }
        
        /* =============================================
           MEDIA QUERIES PARA RESPONSIVIDAD
           ============================================= */
        
        @media (max-width: 1200px) {
            .left-panel {
                padding: 3rem 4rem;
            }
            
            .right-panel {
                padding: 3rem;
            }
            
            .current-time {
                font-size: 5.5rem;
                width: 450px;
            }
            
            .welcome-message {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 992px) {
            .app-container {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
                overflow: auto;
            }
            
            .left-panel, .right-panel {
                width: 100%;
                height: auto;
                min-height: 50vh;
                padding: 2.5rem;
            }
            
            .right-panel {
                order: -1;
                min-height: 40vh;
                padding: 2.5rem 1.875rem;
            }
            
            .left-panel {
                min-height: 60vh;
            }
            
            .current-time {
                font-size: 4.375rem;
                width: 375px;
            }
            
            .datetime-container {
                position: relative;
                top: auto;
                transform: none;
                margin-bottom: 2rem;
            }
            
            .welcome-container {
                position: relative;
                bottom: auto;
                margin-top: 2rem;
            }
            
            .employee-card {
                width: 90%;
                top: 50%;
                transform: translate(-50%, -50%);
            }
            
            .notification {
                max-width: 312.5px;
                right: 12.5px;
                top: 12.5px;
            }
            
            .logo {
                width: 150px;
            }
            
            .permission-form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.625rem;
            }
        }
        
        @media (max-width: 768px) {
            .left-panel {
                padding: 2rem;
            }
            
            .right-panel {
                padding: 2rem 1.5rem;
            }
            
            .current-time {
                font-size: 3.75rem;
                width: 320px;
            }
            
            .current-date {
                font-size: 1.5rem;
            }
            
            .welcome-message {
                font-size: 1.75rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .logo {
                width: 130px;
            }
            
            input[type="text"], input[type="password"], input[type="time"], select, textarea {
                font-size: 1.125rem;
                padding: 0.875rem 1.25rem;
            }
            
            .btn {
                font-size: 1.125rem;
                padding: 1.125rem;
            }
        }
        
        @media (max-width: 576px) {
            .left-panel {
                padding: 1.5rem;
            }
            
            .right-panel {
                padding: 1.5rem 1rem;
            }
            
            .current-time {
                font-size: 3.125rem;
                width: 280px;
            }
            
            .current-date {
                font-size: 1.25rem;
            }
            
            .welcome-message {
                font-size: 1.5rem;
            }
            
            .typing-container {
                min-height: 4.375rem;
            }
            
            .logo {
                width: 110px;
            }
            
            .header h1 {
                font-size: 1.75rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .employee-photo {
                width: 100px;
                height: 100px;
            }
            
            .employee-card {
                padding: 1.5rem;
                width: 95%;
            }
            
            .employee-info {
                grid-template-columns: 1fr;
                gap: 0.875rem;
            }
            
            .notification {
                max-width: 90%;
                right: 5%;
                top: 12.5px;
            }
            
            .modal {
                width: 95%;
            }
            
            .modal-header {
                padding: 1.5rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 400px) {
            .left-panel {
                padding: 1rem;
            }
            
            .current-time {
                font-size: 2.5rem;
                width: 240px;
            }
            
            .welcome-message {
                font-size: 1.25rem;
            }
            
            .typing-text {
                font-size: 0.9rem;
            }
            
            .btn {
                font-size: 1rem;
                padding: 1rem;
            }
            
            .action-buttons {
                gap: 0.5rem;
            }
        }
        
        /* Asegurar que el diseño sea responsive en orientación landscape */
        @media (max-height: 600px) and (orientation: landscape) {
            .app-container {
                flex-direction: row;
                height: 100vh;
            }
            
            .left-panel, .right-panel {
                height: 100vh;
                min-height: auto;
            }
            
            .left-panel {
                padding: 1.5rem 2rem;
            }
            
            .right-panel {
                padding: 1.5rem;
            }
            
            .datetime-container {
                position: absolute;
                top: 15%;
                transform: translateX(-50%);
            }
            
            .welcome-container {
                position: absolute;
                bottom: 20%;
            }
            
            .current-time {
                font-size: 3rem;
            }
            
            .welcome-message {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="<?= $currentStyle ?>">
    <!-- Botón de acceso administrador en esquina superior derecha -->
    <div class="admin-access-btn" id="btn-admin-small">
        <i class="fas fa-cog"></i>
    </div>

    <!-- Fondo oscuro transparente -->
    <div class="notification-overlay" id="notification-overlay"></div>

    <!-- Notificación en esquina superior derecha -->
    <?php if ($notification): ?>
        <div class="notification <?= $notification['type'] ?>" id="notification">
            <div class="notification-header">
                <div class="notification-icon">
                    <?php switch($notification['type']) {
                        case 'success': echo '<i class="fas fa-check"></i>'; break;
                        case 'error': echo '<i class="fas fa-exclamation"></i>'; break;
                        case 'warning': echo '<i class="fas fa-exclamation-triangle"></i>'; break;
                    } ?>
                </div>
                <div class="notification-title">
                    <?php switch($notification['type']) {
                        case 'success': echo 'Éxito'; break;
                        case 'error': echo 'Error'; break;
                        case 'warning': echo 'Advertencia'; break;
                    } ?>
                </div>
            </div>
            <div class="notification-body">
                <?= htmlspecialchars($notification['message']) ?>
                <?php if (isset($notification['codigo'])): ?>
                    <div class="codigo-solicitud">Código: #<?= $notification['codigo'] ?></div>
                <?php endif; ?>
            </div>
            <div class="progress-bar"></div>
        </div>
    <?php endif; ?>

    <!-- Tarjeta de empleado centrada -->
    <?php if ($employeeData): ?>
        <div class="employee-card" id="employee-card">
            <?php if (!empty($employeeData['foto'])): ?>
                <img src="<?= FOTO_DIR . htmlspecialchars($employeeData['foto']) ?>" alt="Foto de <?= htmlspecialchars($employeeData['nombres']) ?>" class="employee-photo">
            <?php else: ?>
                <img src="<?= FOTO_DIR ?>default.png" alt="Foto no disponible" class="employee-photo">
            <?php endif; ?>
            
            <h3>Información del Personal</h3>
            <div class="employee-info">
                <div>
                    <strong>Nombre completo</strong>
                    <p><?= htmlspecialchars($employeeData['nombres'] . ' ' . $employeeData['apellidos']) ?></p>
                    
                    <strong>Área</strong>
                    <p><?= htmlspecialchars($employeeData['area']) ?></p>
                </div>
                <div>
                    <strong>Cargo</strong>
                    <p><?= htmlspecialchars($employeeData['puesto']) ?></p>
                    
                    <strong>Tipo</strong>
                    <p><?= htmlspecialchars($employeeData['tipo_personal']) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal de permisos -->
    <div class="modal-overlay" id="modal-permisos">
        <div class="modal">
            <div class="modal-header">
                <h3>Solicitud de Permiso</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-permisos" method="POST">
                    <div class="permission-form-grid">
                        <div class="form-group">
                            <label for="modal-dni-permisos">DNI:</label>
                            <input type="text" id="modal-dni-permisos" name="dni" required 
                                    pattern="[0-9]{8}" title="Ingrese un DNI válido (8 dígitos)"
                                 placeholder="Ingrese su DNI" maxlength="8" inputmode="numeric"
                                 oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo-permiso">Tipo de permiso:</label>
                            <select id="tipo-permiso" name="tipo_permiso" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Personal">Personal</option>
                                <option value="Médico">Médico</option>
                                <option value="Familiar">Familiar</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="motivo">Motivo detallado:</label>
                            <textarea id="motivo" name="motivo" rows="3" required placeholder="Describa el motivo de su permiso"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora-salida">Hora de salida:</label>
                            <input type="time" id="hora-salida" name="hora_salida" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora-retorno">Hora de retorno estimada:</label>
                            <input type="time" id="hora-retorno" name="hora_retorno" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Solicitar Permiso
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de login admin -->
    <div class="modal-overlay" id="modal-login-admin">
        <div class="modal">
            <div class="modal-header">
                <h3>Acceso Administrador</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-login-admin" method="POST">
                    <input type="hidden" name="admin_login" value="1">
                    <div class="form-group">
                        <label for="admin-usuario">Usuario:</label>
                        <input type="text" id="admin-usuario" name="usuario" required 
                               placeholder="Ingrese su usuario">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-contrasena">Contraseña:</label>
                        <input type="password" id="admin-contrasena" name="contrasena" required 
                               placeholder="Ingrese su contraseña">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="app-container">
        <!-- Panel izquierdo - Formulario de registro -->
        <div class="left-panel">
            <div class="header">
                <img src="logo.png" alt="Logo UFR" class="logo">
                
                <h1>Registro de Asistencia</h1>
                <p>Control para Personal Administrativo</p>
            </div>
            
            <form method="POST" action="" id="main-form">
                <div class="form-group">
                    <label for="dni">Número de DNI:</label>
                        <input type="text" id="dni" name="dni" required autofocus
                        pattern="[0-9]{8}" title="Ingrese un DNI válido (8 dígitos)"
                        placeholder="Ingrese su DNI" maxlength="8" inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-fingerprint"></i> Registrar Asistencia
                </button>
            </form>
            
            <div class="action-buttons">
                <a href="#" class="btn btn-secondary" id="btn-permisos">
                    <i class="fas fa-calendar-check"></i> Solicitar Permiso
                </a>
            </div>
            
            <div class="footer">
                <p>Sistema de Asistencia &copy; <?= date('Y') ?> - Universidad Roosevelt</p>
            </div>
        </div>
        
        <!-- Panel derecho - Información -->
        <div class="right-panel">
            <!-- Animación de fondo -->
            <div class="bg-animation" id="bg-animation"></div>
            
            <!-- Elementos decorativos temporales -->
            <?php if ($currentStyle === 'halloween'): ?>
                <!-- Fantasmas -->
                <div class="ghost" style="left: 5%; animation-delay: 0s;"></div>
                <div class="ghost" style="left: 25%; animation-delay: 8s;"></div>
                <div class="ghost" style="left: 45%; animation-delay: 16s;"></div>
                <div class="ghost" style="left: 65%; animation-delay: 4s;"></div>
                <div class="ghost" style="left: 85%; animation-delay: 12s;"></div>
                
                <!-- Gotas de sangre -->
                <div class="blood-drop" style="left: 10%; animation-delay: 1s;"></div>
                <div class="blood-drop" style="left: 30%; animation-delay: 3s;"></div>
                <div class="blood-drop" style="left: 50%; animation-delay: 5s;"></div>
                <div class="blood-drop" style="left: 70%; animation-delay: 2s;"></div>
                <div class="blood-drop" style="left: 90%; animation-delay: 4s;"></div>
                
            <?php elseif ($currentStyle === 'navidad'): ?>
                <!-- Copos de nieve modernos -->
                <div class="snowflake" style="left: 5%; animation-delay: 0s;">❄</div>
                <div class="snowflake" style="left: 15%; animation-delay: 1s;">❄</div>
                <div class="snowflake" style="left: 25%; animation-delay: 2s;">❄</div>
                <div class="snowflake" style="left: 35%; animation-delay: 3s;">❄</div>
                <div class="snowflake" style="left: 45%; animation-delay: 4s;">❄</div>
                <div class="snowflake" style="left: 55%; animation-delay: 5s;">❄</div>
                <div class="snowflake" style="left: 65%; animation-delay: 6s;">❄</div>
                <div class="snowflake" style="left: 75%; animation-delay: 7s;">❄</div>
                <div class="snowflake" style="left: 85%; animation-delay: 8s;">❄</div>
                <div class="snowflake" style="left: 95%; animation-delay: 9s;">❄</div>
                
                <!-- Luces navideñas modernas -->
                <div class="christmas-light" style="top: 10%; left: 10%; background-color: #FFD700; animation-delay: 0s;"></div>
                <div class="christmas-light" style="top: 20%; left: 20%; background-color: #FFFFFF; animation-delay: 0.5s;"></div>
                <div class="christmas-light" style="top: 15%; left: 30%; background-color: #FFD700; animation-delay: 1s;"></div>
                <div class="christmas-light" style="top: 25%; left: 40%; background-color: #FFFFFF; animation-delay: 1.5s;"></div>
                <div class="christmas-light" style="top: 10%; left: 50%; background-color: #FFD700; animation-delay: 2s;"></div>
                <div class="christmas-light" style="top: 20%; left: 60%; background-color: #FFFFFF; animation-delay: 2.5s;"></div>
                <div class="christmas-light" style="top: 15%; left: 70%; background-color: #FFD700; animation-delay: 3s;"></div>
                <div class="christmas-light" style="top: 25%; left: 80%; background-color: #FFFFFF; animation-delay: 3.5s;"></div>
                <div class="christmas-light" style="top: 10%; left: 90%; background-color: #FFD700; animation-delay: 4s;"></div>
                
                <!-- Estrellas doradas -->
                <div class="gold-star" style="top: 8%; left: 8%; animation-delay: 0s;">★</div>
                <div class="gold-star" style="top: 12%; left: 28%; animation-delay: 1s;">★</div>
                <div class="gold-star" style="top: 6%; left: 48%; animation-delay: 2s;">★</div>
                <div class="gold-star" style="top: 9%; left: 68%; animation-delay: 3s;">★</div>
                <div class="gold-star" style="top: 11%; left: 88%; animation-delay: 4s;">★</div>
                
                <!-- Decoraciones navideñas minimalistas -->
                <div class="christmas-ornament" style="top: 5%; left: 5%; animation-delay: 0s;"></div>
                <div class="christmas-ornament" style="top: 8%; left: 25%; animation-delay: 2s;"></div>
                <div class="christmas-ornament" style="top: 12%; left: 45%; animation-delay: 4s;"></div>
                <div class="christmas-ornament" style="top: 6%; left: 65%; animation-delay: 1s;"></div>
                <div class="christmas-ornament" style="top: 10%; left: 85%; animation-delay: 3s;"></div>
                
                <!-- Cintas decorativas modernas -->
                <div class="ribbon" style="top: -20px; left: -20px;"></div>
                <div class="ribbon" style="top: 50%; right: -30px;"></div>
                <div class="ribbon" style="bottom: 30%; left: -40px;"></div>
                
                <!-- Corazones navideños -->
                <div class="heart" style="left: 12%; animation-delay: 0s;">❤</div>
                <div class="heart" style="left: 32%; animation-delay: 2s;">❤</div>
                <div class="heart" style="left: 52%; animation-delay: 4s;">❤</div>
                <div class="heart" style="left: 72%; animation-delay: 1s;">❤</div>
                <div class="heart" style="left: 92%; animation-delay: 3s;">❤</div>
                
                <!-- Campanas navideñas -->
                <div class="bell" style="top: 15%; left: 18%; animation-delay: 0s;">🔔</div>
                <div class="bell" style="top: 22%; left: 38%; animation-delay: 1.5s;">🔔</div>
                <div class="bell" style="top: 18%; left: 58%; animation-delay: 3s;">🔔</div>
                <div class="bell" style="top: 24%; left: 78%; animation-delay: 4.5s;">🔔</div>
                
            <?php endif; ?>
            
            <div class="datetime-container">
                <div class="current-date" id="current-date">
                    <?= strftime('%A, %d de %B de %Y') ?>
                </div>
                <div class="current-time" id="current-time">
                    <?= date('H:i:s') ?>
                </div>
            </div>
            
            <div class="welcome-container">
                <div class="welcome-message">Bienvenido al Control de Asistencia</div>
                <div class="typing-container">
                    <div class="typing-line">
                        <span class="typing-text" id="typing-text-1"></span>
                    </div>
                    <div class="typing-line">
                        <span class="typing-text" id="typing-text-2"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enfocar automáticamente el campo DNI
        document.getElementById('dni').focus();
        
        // Actualizar reloj cada segundo
        function updateClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            const timeStr = now.toLocaleTimeString('es-ES', {
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: false
            });
            
            document.getElementById('current-date').textContent = 
                dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            document.getElementById('current-time').textContent = timeStr;
        }
        
        setInterval(updateClock, 1000);
        updateClock();
        
        // Efecto de máquina de escribir
        const messageLines = <?= json_encode($currentMessages) ?>;
        
        function typeWriter(elementId, text, speed, callback) {
            let i = 0;
            const elem = document.getElementById(elementId);
            elem.innerHTML = '';
            elem.style.borderRight = '2.5px solid var(--white)';
            
            function typing() {
                if (i < text.length) {
                    elem.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(typing, speed);
                } else {
                    elem.style.borderRight = 'none';
                    if (callback) callback();
                }
            }
            
            typing();
        }
        
        // Función para iniciar el efecto de escritura en loop
        function startTypingLoop() {
            typeWriter('typing-text-1', messageLines[0], 50, () => {
                setTimeout(() => {
                    typeWriter('typing-text-2', messageLines[1], 50, () => {
                        // Esperar 2 segundos antes de borrar
                        setTimeout(() => {
                            // Borrar texto
                            document.getElementById('typing-text-1').innerHTML = '';
                            document.getElementById('typing-text-2').innerHTML = '';
                            
                            // Reiniciar el loop después de 1 segundo
                            setTimeout(startTypingLoop, 1000);
                        }, 2000);
                    });
                }, 500);
            });
        }
        
        // Iniciar el efecto después de 1 segundo
        setTimeout(startTypingLoop, 1000);
        
        // Mostrar notificación y tarjeta de empleado si existen
        const notificationOverlay = document.getElementById('notification-overlay');
        const notification = document.getElementById('notification');
        const employeeCard = document.getElementById('employee-card');
        
        // Mostrar overlay si hay notificación o tarjeta de empleado
        if (notification || employeeCard) {
            notificationOverlay.classList.add('show');
        }
        
        // Mostrar notificación si existe
        if (notification) {
            setTimeout(() => {
                notification.classList.add('show');
                
                // Ocultar notificación después de casi 3 segundos
                setTimeout(() => {
                    notification.classList.remove('show');
                    
                    // Si no hay tarjeta de empleado, ocultar overlay también
                    if (!employeeCard) {
                        setTimeout(() => {
                            notificationOverlay.classList.remove('show');
                        }, 300);
                    }
                }, 2500);
            }, 100);
        }
        
        // Mostrar tarjeta de empleado si existe
        if (employeeCard) {
            setTimeout(() => {
                employeeCard.classList.add('show');
                
                // Ocultar tarjeta de empleado después de casi 3 segundos
                setTimeout(() => {
                    employeeCard.classList.remove('show');
                    
                    // Ocultar overlay después de la animación
                    setTimeout(() => {
                        notificationOverlay.classList.remove('show');
                    }, 300);
                }, 2500);
            }, 100);
        }
        
        // Crear animación de fondo con círculos
        function createBackgroundAnimation() {
            const bgAnimation = document.getElementById('bg-animation');
            const colors = ['rgba(255,255,255,0.03)', 'rgba(255,255,255,0.05)', 'rgba(255,255,255,0.07)'];
            
            for (let i = 0; i < 15; i++) {
                const circle = document.createElement('div');
                circle.classList.add('bg-circle');
                
                // Tamaño aleatorio
                const size = Math.random() * 200 + 50;
                circle.style.width = `${size}px`;
                circle.style.height = `${size}px`;
                
                // Posición aleatoria
                circle.style.left = `${Math.random() * 100}%`;
                circle.style.top = `${Math.random() * 100 + 100}%`;
                
                // Color aleatorio
                circle.style.background = colors[Math.floor(Math.random() * colors.length)];
                
                // Duración de animación aleatoria
                const duration = Math.random() * 20 + 10;
                circle.style.animationDuration = `${duration}s`;
                
                // Retraso aleatorio
                circle.style.animationDelay = `${Math.random() * 5}s`;
                
                bgAnimation.appendChild(circle);
            }
        }
        
        // Iniciar animación de fondo
        createBackgroundAnimation();
        
        // Modal de permisos
        const btnPermisos = document.getElementById('btn-permisos');
        const modalPermisos = document.getElementById('modal-permisos');
        const modalClosePermisos = modalPermisos.querySelector('.modal-close');
        const formPermisos = document.getElementById('form-permisos');
        
        // Modal de login admin
        const btnAdminSmall = document.getElementById('btn-admin-small');
        const modalLoginAdmin = document.getElementById('modal-login-admin');
        const modalCloseLoginAdmin = modalLoginAdmin.querySelector('.modal-close');
        const formLoginAdmin = document.getElementById('form-login-admin');
        
        // Función para limpiar formularios de modales
        function clearModalForms() {
            // Limpiar formulario de permisos
            formPermisos.reset();
            
            // Limpiar formulario de login admin
            formLoginAdmin.reset();
        }
        
        // Abrir modal de permisos
        btnPermisos.addEventListener('click', (e) => {
            e.preventDefault();
            modalPermisos.classList.add('show');
            document.getElementById('modal-dni-permisos').focus();
        });
        
        // Cerrar modal de permisos
        modalClosePermisos.addEventListener('click', () => {
            modalPermisos.classList.remove('show');
            clearModalForms();
        });
        
        // Abrir modal de login admin desde botón pequeño
        btnAdminSmall.addEventListener('click', (e) => {
            e.preventDefault();
            modalLoginAdmin.classList.add('show');
            document.getElementById('admin-usuario').focus();
        });
        
        // Cerrar modal de login admin
        modalCloseLoginAdmin.addEventListener('click', () => {
            modalLoginAdmin.classList.remove('show');
            clearModalForms();
        });
        
        // Validar formulario de permisos
        formPermisos.addEventListener('submit', function(e) {
            const horaSalida = document.getElementById('hora-salida').value;
            const horaRetorno = document.getElementById('hora-retorno').value;
            
            // Validar que la hora de retorno sea posterior a la de salida
            if (horaSalida >= horaRetorno) {
                e.preventDefault();
                alert('La hora de retorno debe ser posterior a la hora de salida');
                return false;
            }
            
            return true;
        });
        
        // Auto-rellenar DNI en el modal si ya está en el formulario principal
        btnPermisos.addEventListener('click', function() {
            const dniPrincipal = document.getElementById('dni').value;
            if (dniPrincipal && /^\d{8}$/.test(dniPrincipal)) {
                document.getElementById('modal-dni-permisos').value = dniPrincipal;
            }
        });
        
        // Enfocar automáticamente el campo DNI después de cerrar modales
        modalPermisos.addEventListener('transitionend', function() {
            if (!this.classList.contains('show')) {
                document.getElementById('dni').focus();
            }
        });
        
        modalLoginAdmin.addEventListener('transitionend', function() {
            if (!this.classList.contains('show')) {
                document.getElementById('dni').focus();
            }
        });
        
        // Crear fuegos artificiales para año nuevo
        function createFireworks() {
            const rightPanel = document.querySelector('.right-panel');
            
            setInterval(() => {
                if (document.body.classList.contains('navidad')) {
                    const firework = document.createElement('div');
                    firework.classList.add('firework');
                    
                    // Posición aleatoria
                    const left = Math.random() * 100;
                    firework.style.left = `${left}%`;
                    firework.style.top = '100%';
                    
                    // Color aleatorio
                    const colors = ['#FFD700', '#FFFFFF', '#FF6B6B', '#4FC3F7'];
                    firework.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    
                    // Duración aleatoria
                    const duration = Math.random() * 1 + 0.5;
                    firework.style.animationDuration = `${duration}s`;
                    
                    rightPanel.appendChild(firework);
                    
                    // Eliminar después de la animación
                    setTimeout(() => {
                        firework.remove();
                    }, 1500);
                }
            }, 500);
        }
        
        // Iniciar fuegos artificiales
        createFireworks();
    </script>
</body>
</html>