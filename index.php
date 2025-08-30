<?php

// =============================================
// CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_asistencia');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_TIMEZONE', 'America/Lima');
define('FOTOS_EMPLEADOS_DIR', 'fotos_empleados/');

date_default_timezone_set(APP_TIMEZONE);

// Crear directorio de fotos_empleados si no existe
if (!file_exists(FOTOS_EMPLEADOS_DIR)) {
    mkdir(FOTOS_EMPLEADOS_DIR, 0777, true);
}

session_start();

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

// Crear tablas si no existen (solo para desarrollo)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS empleados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dni VARCHAR(8) NOT NULL UNIQUE,
        nombres VARCHAR(50) NOT NULL,
        apellidos VARCHAR(50) NOT NULL,
        area VARCHAR(50) NOT NULL,
        puesto VARCHAR(50) NOT NULL,
        inicio_contrato DATE NOT NULL,
        fin_contrato DATE NOT NULL,
        tipo_personal ENUM('Administrativo', 'Docente') NOT NULL DEFAULT 'Administrativo',
        entrada_manana TIME NULL,
        salida_manana TIME NULL,
        entrada_tarde TIME NULL,
        salida_tarde TIME NULL,
        foto VARCHAR(255) NULL
    );
    
    CREATE TABLE IF NOT EXISTS registros_asistencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empleado_id INT NOT NULL,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
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
        FOREIGN KEY (empleado_id) REFERENCES empleados(id)
    );
    
    CREATE TABLE IF NOT EXISTS usuarios_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        contrasena VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'supervisor') NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// Insertar datos de prueba (solo para desarrollo)
$pdo->exec("
    INSERT IGNORE INTO empleados VALUES
    (1, '12345678', 'Juan', 'Pérez', 'Académico', 'Profesor', '2025-01-01', '2027-12-31', 'Docente', NULL, NULL, NULL, NULL, 'fotos_empleados/12345678.png'),
    (2, '23456789', 'María', 'Gómez', 'Administración', 'Secretaria', '2025-01-15', '2026-12-31', 'Administrativo', '08:30:00', '12:30:00', '13:30:00', '17:30:00', 'fotos_empleados/23456789.png'),
    (3, '34567890', 'Carlos', 'López', 'TI', 'Desarrollador', '2025-02-01', '2027-06-30', 'Administrativo', '09:00:00', '13:00:00', '14:00:00', '18:00:00', 'fotos_empleados/34567890.png'),
    (4, '45678901', 'Ana', 'Rodríguez', 'Contabilidad', 'Contadora', '2025-03-10', '2027-03-10', 'Administrativo', '08:00:00', '12:00:00', '13:00:00', '17:00:00', 'fotos_empleados/45678901.png'),
    (5, '56789012', 'Luis', 'Martínez', 'Recursos Humanos', 'Analista', '2025-01-01', '2026-12-31', 'Administrativo', '08:30:00', '12:30:00', '13:30:00', '17:30:00', 'fotos_empleados/56789012.png'),
    (6, '67890123', 'Sofía', 'Hernández', 'Marketing', 'Especialista', '2025-04-01', '2027-04-01', 'Administrativo', '09:00:00', '13:00:00', '14:00:00', '18:00:00', 'fotos_empleados/67890123.png'),
    (7, '78901234', 'Pedro', 'Díaz', 'Logística', 'Coordinador', '2025-05-15', '2026-05-15', 'Administrativo', '08:00:00', '12:00:00', '13:00:00', '17:00:00', 'fotos_empleados/78901234.png'),
    (8, '89012345', 'Laura', 'Torres', 'Académico', 'Investigadora', '2025-06-01', '2027-06-01', 'Docente', NULL, NULL, NULL, NULL, 'fotos_empleados/89012345.png'),
    (9, '90123456', 'Jorge', 'Ramírez', 'TI', 'Soporte Técnico', '2025-07-01', '2027-07-01', 'Administrativo', '09:00:00', '13:00:00', '14:00:00', '18:00:00', 'fotos_empleados/90123456.png'),
    (10, '01234567', 'Carmen', 'Vargas', 'Administración', 'Asistente', '2025-08-01', '2027-08-01', 'Administrativo', '08:00:00', '12:00:00', '13:00:00', '17:00:00', 'fotos_empleados/01234567.png');
    
    INSERT IGNORE INTO usuarios_admin VALUES 
    (1, 'admin', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
    (2, 'supervisor', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', NOW());
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
             AND inicio_contrato <= ? 
             AND fin_contrato >= ?"
        );
        $stmt->execute([$dni, $today, $today]);
        return $stmt->fetch() ?: null;
    }
    
    public function registerAttendance(array $employee): array {
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        // Para docentes, registrar simplemente la hora sin validación
        if ($employee['tipo_personal'] === 'Docente') {
            try {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO registros_asistencia 
                    (empleado_id, fecha, hora) 
                    VALUES (?, ?, ?)"
                );
                
                $stmt->execute([
                    $employee['id'],
                    $today,
                    $currentTime
                ]);
                
                return [
                    'success' => true, 
                    'message' => 'Registro de asistencia completado (Docente)',
                    'type' => 'success'
                ];
            } catch (PDOException $e) {
                error_log("Error al registrar asistencia docente: " . $e->getMessage());
                return ['success' => false, 'message' => 'Error al registrar la asistencia', 'type' => 'error'];
            }
        }
        
        // Para personal administrativo, validar horarios
        if (empty($employee['entrada_manana']) ){
            return ['success' => false, 'message' => 'Horario no configurado para este empleado', 'type' => 'error'];
        }
        
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO registros_asistencia 
                (empleado_id, fecha, hora) 
                VALUES (?, ?, ?)"
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
                (empleado_id, tipo_permiso, motivo, fecha_permiso, hora_salida, hora_retorno, hora_registro) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
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
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($contrasena, $user['contrasena'])) {
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
// PROCESAMIENTO DEL FORMULARIO
// =============================================
$attendanceSystem = new AttendanceSystem($pdo);
$notification = null;
$employeeData = null;

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
        $notification = ['message' => $result['message'], 'type' => 'error'];
    }
}

// Procesamiento del formulario principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['admin_login'])) {
    if (isset($_POST['dni']) && !isset($_POST['tipo_permiso'])) {
        // Procesamiento del registro normal de asistencia
        $dni = trim($_POST['dni']);
        
        if (empty($dni) || !preg_match('/^\d{8}$/', $dni)) {
            $notification = ['message' => 'DNI inválido (8 dígitos)', 'type' => 'error'];
        } else {
            $employee = $attendanceSystem->verifyEmployee($dni);
            
            if (!$employee) {
                $notification = ['message' => 'DNI no registrado o contrato no vigente', 'type' => 'error'];
            } else {
                $employeeData = $employee; // Guardar datos del empleado
                $result = $attendanceSystem->registerAttendance($employee);
                $notification = $result;
            }
        }
    } elseif (isset($_POST['tipo_permiso'])) {
        // Procesamiento de permiso especial
        $dni = trim($_POST['dni']);
        
        if (empty($dni) || !preg_match('/^\d{8}$/', $dni)) {
            $notification = ['message' => 'DNI inválido (8 dígitos)', 'type' => 'error'];
        } else {
            $employee = $attendanceSystem->verifyEmployee($dni);
            
            if (!$employee) {
                $notification = ['message' => 'DNI no registrado o contrato no vigente', 'type' => 'error'];
            } else {
                $employeeData = $employee; // Guardar datos del empleado
                $requestData = [
                    'tipo_permiso' => $_POST['tipo_permiso'],
                    'motivo' => $_POST['motivo'],
                    'hora_salida' => $_POST['hora_salida'],
                    'hora_retorno' => $_POST['hora_retorno']
                ];
                
                $result = $attendanceSystem->registerPermission($employee, $requestData);
                $notification = $result;
            }
        }
    }
}
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
        
        .app-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .left-panel {
            width: 40%;
            background-color: var(--white);
            padding: 3rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow-md);
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }
        
        .right-panel {
            width: 60%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            color: var(--white);
            position: relative;
            overflow: hidden;
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
            margin-bottom: 3rem;
        }
        
        .logo {
            width: 150px;
            margin-bottom: 1.5rem;
            filter: brightness(1.2) drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            object-fit: contain;
        }
        
        .header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .header p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        
        input[type="text"], input[type="password"], input[type="time"], select, textarea {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 2px solid var(--light);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
        }
        
        input[type="text"]:focus, input[type="password"]:focus, input[type="time"]:focus, select:focus, textarea:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3px rgba(138, 21, 56, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 1rem;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 1rem;
            text-align: center;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            box-shadow: 0 4px 6px rgba(138, 21, 56, 0.2);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(138, 21, 56, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
            box-shadow: 0 4px 6px rgba(212, 175, 55, 0.2);
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(212, 175, 55, 0.3);
        }
        
        .datetime-container {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 100%;
            padding: 0 2rem;
        }
        
        .current-date {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .current-time {
            font-size: 5rem;
            font-weight: 300;
            letter-spacing: 2px;
            font-variant-numeric: tabular-nums;
            width: 400px;
            margin: 0 auto;
            line-height: 1;
            font-family: 'Courier New', monospace;
        }
        
        .welcome-container {
            position: absolute;
            bottom: 30%;
            left: 0;
            width: 100%;
            padding: 0 2rem;
            text-align: center;
        }
        
        .welcome-message {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .typing-container {
            display: inline-block;
            text-align: center;
            min-height: 4.5em;
        }
        
        .typing-line {
            display: block;
            height: 1.5em;
            overflow: hidden;
            margin-bottom: 0.5em;
        }
        
        .typing-text {
            border-right: 2px solid var(--white);
            display: inline-block;
            animation: blink-caret 0.75s step-end infinite;
        }
        
        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: var(--white); }
        }
        
        .employee-card {
            background-color: rgba(247, 241, 241, 1);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 12px;
            width: 80%;
            max-width: 500px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.5s ease;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1002;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .employee-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--white);
            box-shadow: var(--shadow-md);
            margin-bottom: 1rem;
        }
        
        .employee-card h3 {
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            text-align: center;
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            width: 100%;
        }
        
        .employee-info p {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .employee-info strong {
            display: block;
            font-weight: 400;
            font-size: 0.8rem;
            opacity: 0.8;
            margin-bottom: 0.2rem;
        }
        
        .footer {
            margin-top: auto;
            text-align: center;
            color: var(--gray);
            font-size: 0.8rem;
            padding-top: 1rem;
            border-top: 1px solid var(--light);
        }
        
        .action-buttons {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
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
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 500px;
            overflow: hidden;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--light);
            background-color: var(--primary);
            color: white;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 0.5rem;
            transition: var(--transition);
        }
        
        .modal-close:hover {
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
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
            top: 20px;
            right: 20px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 300px;
            overflow: hidden;
            transform: translateY(-100px);
            transition: all 0.3s ease;
            z-index: 1003;
            opacity: 0;
        }
        
        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .notification-header {
            padding: 1rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--light);
        }
        
        .notification-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.8rem;
            font-size: 1rem;
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
            font-size: 0.9rem;
        }
        
        .notification-body {
            padding: 1rem;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .progress-bar {
            height: 4px;
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
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        @media (max-width: 992px) {
            .app-container {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }
            
            .left-panel, .right-panel {
                width: 100%;
                height: auto;
                min-height: 50vh;
                padding: 2rem;
            }
            
            .right-panel {
                order: -1;
                min-height: 40vh;
                padding: 2rem 1.5rem;
            }
            
            .left-panel {
                min-height: 60vh;
            }
            
            .current-time {
                font-size: 3.5rem;
                width: 300px;
            }
            
            .datetime-container {
                top: 15%;
            }
            
            .welcome-container {
                bottom: 25%;
            }
            
            .employee-card {
                width: 90%;
                top: 50%;
                transform: translate(-50%, -50%);
            }
            
            .notification {
                max-width: 250px;
                right: 10px;
                top: 10px;
            }
            
            .logo {
                width: 120px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .notification-container {
                flex-direction: column;
                align-items: center;
            }
        }
        
        @media (max-width: 576px) {
            .left-panel {
                padding: 2rem;
            }
            
            .current-time {
                font-size: 2.5rem;
                width: 250px;
            }
            
            .current-date {
                font-size: 1.2rem;
            }
            
            .welcome-message {
                font-size: 1.5rem;
            }
            
            .typing-container {
                min-height: 3.5em;
            }
            
            .logo {
                width: 100px;
            }
            
            .employee-photo {
                width: 80px;
                height: 80px;
            }
            
            .notification {
                max-width: 90%;
                right: 5%;
                top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo oscuro transparente -->
    <div class="notification-overlay <?= ($notification || $employeeData) ? 'show' : '' ?>" id="notification-overlay"></div>

    <!-- Notificación en esquina superior derecha -->
    <?php if ($notification): ?>
        <div class="notification <?= $notification['type'] ?> show" id="notification">
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
                <img src="<?= htmlspecialchars($employeeData['foto']) ?>" alt="Foto de <?= htmlspecialchars($employeeData['nombres']) ?>" class="employee-photo">
            <?php else: ?>
                <img src="https://via.placeholder.com/100" alt="Foto no disponible" class="employee-photo">
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
                    
                    <strong>Contrato</strong>
                    <p><?= date('d/m/Y', strtotime($employeeData['inicio_contrato'])) ?> - <?= date('d/m/Y', strtotime($employeeData['fin_contrato'])) ?></p>
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
                    <div class="form-group">
                        <label for="modal-dni-permisos">DNI:</label>
                        <input type="text" id="modal-dni-permisos" name="dni" required 
                               pattern="[0-9]{8}" title="Ingrese un DNI válido (8 dígitos)"
                               placeholder="Ingrese su DNI" maxlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo-permiso">Tipo de permiso:</label>
                        <select id="tipo-permiso" name="tipo_permiso" required>
                            <option value="">Seleccione una opción</option>
                            <option value="comision_servicios">Comisión de Servicios</option>
                            <option value="permiso_personal">Permiso Personal</option>
                            <option value="atencion_medica">Atención Médica</option>
                            <option value="capacitacion">Capacitación</option>
                            <option value="visita al otro campus">Visita al otro campus</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo detallado:</label>
                        <textarea id="motivo" name="motivo" rows="3" required placeholder="Describa el motivo de su permiso"></textarea>
                    </div>
                    
                    <div class="form-row">
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
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="dni">Número de DNI:</label>
                    <input type="text" id="dni" name="dni" required 
                           pattern="[0-9]{8}" title="Ingrese un DNI válido (8 dígitos)"
                           placeholder="Ingrese su DNI" maxlength="8">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-fingerprint"></i> Registrar Asistencia
                </button>
            </form>
            
            <div class="action-buttons">
                <a href="#" class="btn btn-secondary" id="btn-permisos">
                    <i class="fas fa-calendar-check"></i> Solicitar Permiso
                </a>
                <a href="#" class="btn btn-secondary" id="btn-admin">
                    <i class="fas fa-user-shield"></i> Acceso Administrador
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
        const messageLines = [
            "Ingrese su DNI en el panel",
            "izquierdo para registrar su asistencia"
        ];
        
        function typeWriter(elementId, text, speed, callback) {
            let i = 0;
            const elem = document.getElementById(elementId);
            elem.innerHTML = '';
            elem.style.borderRight = '2px solid var(--white)';
            
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
        
        if (notification) {
            // Mostrar overlay y notificación
            setTimeout(() => {
                notificationOverlay.classList.add('show');
                notification.classList.add('show');
            }, 100);
            
            // Ocultar notificación después de 2 segundos
            setTimeout(() =>{
                notification.classList.remove('show');
                
                // Ocultar overlay y tarjeta de empleado
                setTimeout(() => {
                    notificationOverlay.classList.remove('show');
                    if (employeeCard) {
                        employeeCard.style.display = 'none';
                    }
                }, 300);
            }, 2000);
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
        const btnAdmin = document.getElementById('btn-admin');
        const modalLoginAdmin = document.getElementById('modal-login-admin');
        const modalCloseLoginAdmin = modalLoginAdmin.querySelector('.modal-close');
        const formLoginAdmin = document.getElementById('form-login-admin');
        
        // Abrir modal de permisos
        btnPermisos.addEventListener('click', () => {
            modalPermisos.classList.add('show');
            document.getElementById('modal-dni-permisos').focus();
        });
        
        // Cerrar modal de permisos
        modalClosePermisos.addEventListener('click', () => {
            modalPermisos.classList.remove('show');
        });
        
        // Abrir modal de login admin
        btnAdmin.addEventListener('click', (e) => {
            e.preventDefault();
            modalLoginAdmin.classList.add('show');
            document.getElementById('admin-usuario').focus();
        });
        
        // Cerrar modal de login admin
        modalCloseLoginAdmin.addEventListener('click', () => {
            modalLoginAdmin.classList.remove('show');
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
        
        // Cerrar modal al hacer clic fuera del contenido
        modalPermisos.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
        
        modalLoginAdmin.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    </script>
</body>
</html>