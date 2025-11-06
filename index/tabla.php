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
(1, '12345678', 'Juan', 'Pérez', 'Académico', 'Profesor', '2025-01-01', '2027-12-31', 'Docente', '12345678.png', 'activo'),
(2, '23456789', 'María', 'González', 'Académico', 'Profesor', '2024-03-15', '2026-03-14', 'Docente', '23456789.png', 'activo'),
(3, '34567890', 'Carlos', 'Rodríguez', 'Administrativo', 'Jefe de Área', '2023-06-01', '2025-05-31', 'Administrativo', '34567890.png', 'activo'),
(4, '45678901', 'Ana', 'López', 'Académico', 'Coordinador', '2024-01-10', '2026-01-09', 'Docente', '45678901.png', 'activo'),
(5, '56789012', 'Luis', 'Martínez', 'Tecnología', 'Analista de Sistemas', '2024-02-20', '2025-08-19', 'Administrativo', '56789012.png', 'activo'),
(6, '67890123', 'Laura', 'Hernández', 'Recursos Humanos', 'Especialista RRHH', '2023-11-01', '2025-10-31', 'Administrativo', '67890123.png', 'activo'),
(7, '78901234', 'Pedro', 'Sánchez', 'Académico', 'Profesor', '2024-04-01', '2026-03-31', 'Docente', '78901234.png', 'inactivo'),
(8, '89012345', 'Carmen', 'Díaz', 'Finanzas', 'Contador', '2023-09-15', '2025-09-14', 'Administrativo', '89012345.png', 'activo'),
(9, '90123456', 'Jorge', 'Ramírez', 'Mantenimiento', 'Supervisor', '2024-01-05', '2025-12-31', 'Administrativo', '90123456.png', 'activo'),
(10, '01234567', 'Sofia', 'Torres', 'Académico', 'Asistente Académico', '2024-03-01', '2026-02-28', 'Docente', '01234567.png', 'activo'),
(11, '11223344', 'Miguel', 'Castro', 'Dirección', 'Director', '2022-08-01', '2025-07-31', 'Administrativo', '11223344.png', 'activo'),
(12, '22334455', 'Elena', 'Ruiz', 'Académico', 'Profesor', '2024-05-10', '2026-05-09', 'Docente', '22334455.png', 'activo'),
(13, '33445566', 'Fernando', 'Ortega', 'Administrativo', 'Asistente', '2024-02-15', '2025-08-14', 'Administrativo', '33445566.png', 'activo'),
(14, '44556677', 'Gabriela', 'Mendoza', 'Académico', 'Investigador', '2023-12-01', '2025-11-30', 'Docente', '44556677.png', 'activo'),
(15, '55667788', 'Ricardo', 'Silva', 'Tecnología', 'Programador', '2024-01-20', '2025-07-19', 'Administrativo', '55667788.png', 'activo'),
(16, '66778899', 'Patricia', 'Vargas', 'Recursos Humanos', 'Reclutador', '2024-03-01', '2026-02-28', 'Administrativo', '66778899.png', 'activo'),
(17, '77889900', 'Roberto', 'Ríos', 'Académico', 'Profesor', '2023-10-15', '2025-10-14', 'Docente', '77889900.png', 'inactivo'),
(18, '88990011', 'Isabel', 'Flores', 'Finanzas', 'Auditor', '2024-04-05', '2026-04-04', 'Administrativo', '88990011.png', 'activo'),
(19, '99001122', 'Daniel', 'Morales', 'Mantenimiento', 'Técnico', '2024-02-10', '2025-08-09', 'Administrativo', '99001122.png', 'activo'),
(20, '10111213', 'Adriana', 'Guerrero', 'Académico', 'Coordinador', '2023-11-20', '2025-11-19', 'Docente', '10111213.png', 'activo'),
(21, '12131415', 'José', 'Santos', 'Dirección', 'Subdirector', '2022-09-01', '2025-08-31', 'Administrativo', '12131415.png', 'activo'),
(22, '13141516', 'Lucía', 'Cruz', 'Académico', 'Profesor', '2024-06-01', '2026-05-31', 'Docente', '13141516.png', 'activo'),
(23, '14151617', 'Raúl', 'Reyes', 'Administrativo', 'Secretario', '2024-03-10', '2025-09-09', 'Administrativo', '14151617.png', 'activo'),
(24, '15161718', 'Verónica', 'Aguilar', 'Académico', 'Tutor', '2023-12-15', '2025-12-14', 'Docente', '15161718.png', 'activo'),
(25, '16171819', 'Francisco', 'Paredes', 'Tecnología', 'Soporte Técnico', '2024-01-25', '2025-07-24', 'Administrativo', '16171819.png', 'activo'),
(26, '17181920', 'Diana', 'Cortés', 'Recursos Humanos', 'Capacitador', '2024-04-15', '2026-04-14', 'Administrativo', '17181920.png', 'activo'),
(27, '18192021', 'Sergio', 'Miranda', 'Académico', 'Profesor', '2023-08-01', '2025-07-31', 'Docente', '18192021.png', 'activo'),
(28, '19202122', 'Teresa', 'Romero', 'Finanzas', 'Analista Financiero', '2024-02-28', '2025-08-27', 'Administrativo', '19202122.png', 'activo'),
(29, '20212223', 'Mario', 'Suárez', 'Mantenimiento', 'Electricista', '2024-03-05', '2025-09-04', 'Administrativo', '20212223.png', 'activo'),
(30, '21222324', 'Olga', 'Navarro', 'Académico', 'Asesor', '2023-10-10', '2025-10-09', 'Docente', '21222324.png', 'activo'),
(31, '22232425', 'Alberto', 'Molina', 'Dirección', 'Coordinador General', '2022-07-15', '2025-07-14', 'Administrativo', '22232425.png', 'activo'),
(32, '23242526', 'Rosa', 'Campos', 'Académico', 'Profesor', '2024-05-20', '2026-05-19', 'Docente', '23242526.png', 'activo'),
(33, '24252627', 'Javier', 'Delgado', 'Administrativo', 'Archivista', '2024-01-15', '2025-07-14', 'Administrativo', '24252627.png', 'activo'),
(34, '25262728', 'Beatriz', 'Peña', 'Académico', 'Investigador', '2023-11-05', '2025-11-04', 'Docente', '25262728.png', 'activo'),
(35, '26272829', 'Arturo', 'Cárdenas', 'Tecnología', 'DBA', '2024-02-01', '2025-07-31', 'Administrativo', '26272829.png', 'activo'),
(36, '27282930', 'Lorena', 'Rojas', 'Recursos Humanos', 'Beneficios', '2024-03-20', '2026-03-19', 'Administrativo', '27282930.png', 'activo'),
(37, '28293031', 'Héctor', 'Salazar', 'Académico', 'Profesor', '2023-09-10', '2025-09-09', 'Docente', '28293031.png', 'inactivo'),
(38, '29303132', 'Silvia', 'Vega', 'Finanzas', 'Tesorero', '2024-04-10', '2026-04-09', 'Administrativo', '29303132.png', 'activo'),
(39, '30313233', 'Rodrigo', 'Mejía', 'Mantenimiento', 'Plomero', '2024-01-30', '2025-07-29', 'Administrativo', '30313233.png', 'activo'),
(40, '31323334', 'Claudia', 'Orozco', 'Académico', 'Coordinador', '2023-12-20', '2025-12-19', 'Docente', '31323334.png', 'activo'),
(41, '32333435', 'Eduardo', 'Valdez', 'Dirección', 'Gerente', '2022-06-01', '2025-05-31', 'Administrativo', '32333435.png', 'activo'),
(42, '33343536', 'Monica', 'Franco', 'Académico', 'Profesor', '2024-06-15', '2026-06-14', 'Docente', '33343536.png', 'activo'),
(43, '34353637', 'Oscar', 'Lara', 'Administrativo', 'Recepcionista', '2024-02-05', '2025-08-04', 'Administrativo', '34353637.png', 'activo'),
(44, '35363738', 'Alicia', 'Cervantes', 'Académico', 'Tutor', '2023-10-25', '2025-10-24', 'Docente', '35363738.png', 'activo'),
(45, '36373839', 'Felipe', 'Barrera', 'Tecnología', 'Desarrollador', '2024-01-10', '2025-07-09', 'Administrativo', '36373839.png', 'activo'),
(46, '37383940', 'Gloria', 'Acosta', 'Recursos Humanos', 'Relaciones Laborales', '2024-03-25', '2026-03-24', 'Administrativo', '37383940.png', 'activo'),
(47, '38394041', 'Victor', 'Montes', 'Académico', 'Profesor', '2023-07-20', '2025-07-19', 'Docente', '38394041.png', 'activo'),
(48, '39404142', 'Margarita', 'Rosas', 'Finanzas', 'Contralor', '2024-04-20', '2026-04-19', 'Administrativo', '39404142.png', 'activo'),
(49, '40414243', 'Ramiro', 'Gallegos', 'Mantenimiento', 'Jardinero', '2024-02-15', '2025-08-14', 'Administrativo', '40414243.png', 'activo'),
(50, '41424344', 'Natalia', 'Serrano', 'Académico', 'Asistente', '2023-11-15', '2025-11-14', 'Docente', '41424344.png', 'activo');
    
    INSERT IGNORE INTO usuarios_admin (id, nombres, apellidos, usuario, password, rol, estado) VALUES 
    (1, 'Administrador', 'Principal', 'admin', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo');
    
    INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
    ('minutos_tolerancia', '5', 'Minutos de tolerancia para tardanzas');
");