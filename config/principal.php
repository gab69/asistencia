<?php
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
        
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM registros_asistencia 
             WHERE empleado_id = ? AND fecha = ?"
        );
        $stmt->execute([$employee['id'], $today]);
        $existing = $stmt->fetch();
        
   
        
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
                'type' => 'success',
                'hora_registro' => $currentTime // Agregar la hora del registro
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
                'codigo' => $this->pdo->lastInsertId(),
                'hora_registro' => $currentTime // Agregar la hora del registro
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
    
    // Halloween: 25/09 al 02/11
    if (($currentMonth == 9 && $currentDay >= 25) || 
        ($currentMonth == 10) || 
        ($currentMonth == 11 && $currentDay <= 2)) {
        return 'halloween';
    }
    
    // Navidad y Año Nuevo: 01/12 al 05/01
    if (($currentMonth == 12 && $currentDay >= 1) || 
        ($currentMonth == 1 && $currentDay <= 5)) {
        return 'navidad';
    }
    
    // Día del Amor y la Amistad: 01/02 al 28/02
    if ($currentMonth == 2 && $currentDay >= 1 && $currentDay <= 28) {
        return 'amor';
    }
    
    // Día de la Juventud: 01/09 al 30/09
    if ($currentMonth == 9 && $currentDay >= 1 && $currentDay <= 30) {
        return 'juventud';
    }
    
    // Día de la Independencia: 01/07 al 31/07
    if ($currentMonth == 07) {
        return 'independencia';
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
$horaRegistro = $_SESSION['hora_registro'] ?? null; // almacenar la hora del registro

// Limpiar datos de la sesión después de recuperarlos
if ($employeeData) {
    $_SESSION['employee_data'] = null;
    $_SESSION['hora_registro'] = null; // Limpiar también la hora del registro
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
        
        if (empty($dni) || !preg_match('/^\d{9}$/', $dni)) {
            $_SESSION['notifications'][] = ['message' => 'DNI inválido (8 dígitos)', 'type' => 'error'];
        } else {
            $employee = $attendanceSystem->verifyEmployee($dni);
            
            if (!$employee) {
                $_SESSION['notifications'][] = ['message' => 'DNI no registrado o contrato no vigente', 'type' => 'error'];
            } else {
                $_SESSION['employee_data'] = $employee; // Guardar datos del empleado en sesión
                $result = $attendanceSystem->registerAttendance($employee);
                $_SESSION['notifications'][] = $result;
                // Guardar la hora del registro en la sesión
                if ($result['success'] && isset($result['hora_registro'])) {
                    $_SESSION['hora_registro'] = $result['hora_registro'];
                }
            }
        }
        
        // Prevenir reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
        
    } elseif (isset($_POST['tipo_permiso'])) {
        // Procesamiento de permiso especial
        $dni = trim($_POST['dni']);
        
        if (empty($dni) || !preg_match('/^\d{9}$/', $dni)) {
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
                // Guardar la hora del registro en la sesión
                if ($result['success'] && isset($result['hora_registro'])) {
                    $_SESSION['hora_registro'] = $result['hora_registro'];
                }
            }
        }
        
        // Prevenir reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// =============================================
// FUNCIÓN PARA VERIFICAR SI LA IMAGEN EXISTE
// =============================================
function getEmployeePhoto($employeeData) {
    if (!empty($employeeData['foto'])) {
        $photoPath = FOTO_DIR . $employeeData['foto'];
        // Verificar si el archivo existe físicamente
        if (file_exists($photoPath) && is_file($photoPath)) {
            return FOTO_DIR . htmlspecialchars($employeeData['foto']);
        }
    }
    // Si no existe la foto, usar la imagen por defecto
    return FOTO_DIR . 'default.png';
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
        "👻 ¡Tu DNI o tu alma! 👻",
        "Los fantasmas de RRHH te están observando..."
    ],
    'navidad' => [
        "🎄 ¿Carbón o aguinaldo? 🎄",
        "Depende de cuántas veces has llegado tarde este mes..."
    ],
    'amor' => [
        "❤️ Tu único amor verdadero debería ser... ❤️",
        "¡Llegar a tiempo los lunes por la mañana!"
    ],
    'juventud' => [
        "🔥 ¡La juventud no es excusa! 🔥",
        "Tu DNI espera... como esa crush que nunca te contesta"
    ],
    'independencia' => [
        "¡Que tu puntualidad",
    "sea tan épica como nuestra historia!"
    ]
];
$currentMessages = $welcomeMessages[$currentStyle] ?? $welcomeMessages['default'];