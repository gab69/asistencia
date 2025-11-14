<?php
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
        dni VARCHAR(9) NOT NULL UNIQUE,
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

// Insertar datos de prueba (solo para desarrollo) - CORREGIDO
$pdo->exec("
   INSERT IGNORE INTO empleados (id, dni, nombres, apellidos, area, puesto, inicio_contrato, fin_contrato, tipo_personal, foto, estado) VALUES
(1, '12345678', 'Juan', 'Pérez', 'Académico', 'Profesor', '2025-01-01', '2099-12-31', 'Docente', '12345678.png', 'activo');
    
    INSERT IGNORE INTO usuarios_admin (id, nombres, apellidos, usuario, password, rol, estado) VALUES 
    (1, 'Administrador', 'Principal', 'admin', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo');
    
    INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
    ('minutos_tolerancia', '5', 'Minutos de tolerancia para tardanzas');
");