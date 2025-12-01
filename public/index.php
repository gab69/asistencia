<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia | UFR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <link rel="stylesheet" href="public/assets/css/style.css">
     
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
            <?php
                // Usar la función para obtener la foto correcta
                $employeePhoto = getEmployeePhoto($employeeData);
            ?>
            <img src="<?= $employeePhoto ?>" alt="Foto de <?= htmlspecialchars($employeeData['nombres']) ?>" class="employee-photo">
            
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
                    
                    <strong>Hora de registro</strong>
                    <p id="employee-current-time"><?= $horaRegistro ? htmlspecialchars($horaRegistro) : date('H:i:s') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal de permisos - DISEÑO MEJORADO Y COMPACTO -->
    <div class="modal-overlay" id="modal-permisos">
        <div class="modal modal-permiso">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-check"></i> Solicitud de Permiso</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-permisos" method="POST" class="permission-form">
                    <div class="form-group">
                        <label for="modal-dni-permisos">DNI del Empleado:</label>
                        <input type="text" id="modal-dni-permisos" name="dni" required 
                                pattern="[0-9]{8,9}" title="Ingrese un DNI válido (8 o 9 dígitos)"
                             placeholder="Ingrese su DNI" maxlength="9" inputmode="numeric"
                             oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <div class="form-description">Ingrese su número de DNI de 8 o 9 dígitos</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo-permiso">Tipo de Permiso:</label>
                        <select id="tipo-permiso" name="tipo_permiso" required>
                            <option value="">Seleccione el tipo de permiso</option>
                            <option value="Personal">Personal</option>
                            <option value="Médico">Médico</option>
                            <option value="Familiar">Familiar</option>
                            <option value="Comision">Comisión de Servicios</option>
                            <option value="Visita_otra_sede">Visita a la Otra Sede</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo del Permiso:</label>
                        <textarea id="motivo" name="motivo" rows="3" required placeholder="Describa brevemente el motivo de su permiso"></textarea>
                        <div class="form-description">Sea específico sobre el motivo de su solicitud</div>
                    </div>
                    
                    <div class="time-inputs">
                        <div class="form-group">
                            <label for="hora-salida">Hora de Salida:</label>
                            <input type="time" id="hora-salida" name="hora_salida" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora-retorno">Hora de Retorno:</label>
                            <input type="time" id="hora-retorno" name="hora_retorno" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar Solicitud
                        </button>
                    </div>
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
                <img src="public/assets/img/logo.png" alt="Logo UFR" class="logo">
                
                <h1>Registro de Asistencia</h1>
               
            </div>
            
            <form method="POST" action="" id="main-form">
                <div class="form-group">
                    <label for="dni">Número de DNI:</label>
                        <input type="text" id="dni" name="dni" required autofocus
                        pattern="[0-9]{8,9}" title="Ingrese un DNI válido (8 o 9 dígitos)"
                        placeholder="Ingrese su DNI" maxlength="9" inputmode="numeric"
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
                <p>Registro de Asistencia &copy; <?= date('Y') ?> - Universidad Roosevelt</p>
                <p>Desarrollado por @GabRC</p>
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
                
            <?php elseif ($currentStyle === 'amor'): ?>
                <!-- Corazones flotantes -->
                <div class="heart" style="left: 5%; animation-delay: 0s;">❤️</div>
                <div class="heart" style="left: 15%; animation-delay: 1s;">❤️</div>
                <div class="heart" style="left: 25%; animation-delay: 2s;">❤️</div>
                <div class="heart" style="left: 35%; animation-delay: 3s;">❤️</div>
                <div class="heart" style="left: 45%; animation-delay: 4s;">❤️</div>
                <div class="heart" style="left: 55%; animation-delay: 5s;">❤️</div>
                <div class="heart" style="left: 65%; animation-delay: 6s;">❤️</div>
                <div class="heart" style="left: 75%; animation-delay: 7s;">❤️</div>
                <div class="heart" style="left: 85%; animation-delay: 8s;">❤️</div>
                <div class="heart" style="left: 95%; animation-delay: 9s;">❤️</div>
                
                <!-- Latidos de corazón -->
                <div class="heartbeat" style="top: 10%; left: 10%; animation-delay: 0s;"></div>
                <div class="heartbeat" style="top: 20%; left: 30%; animation-delay: 1s;"></div>
                <div class="heartbeat" style="top: 15%; left: 50%; animation-delay: 2s;"></div>
                <div class="heartbeat" style="top: 25%; left: 70%; animation-delay: 3s;"></div>
                <div class="heartbeat" style="top: 10%; left: 90%; animation-delay: 4s;"></div>
                
            <?php elseif ($currentStyle === 'juventud'): ?>
                <!-- Estrellas doradas -->
                <div class="star" style="left: 5%; animation-delay: 0s;">★</div>
                <div class="star" style="left: 15%; animation-delay: 1s;">★</div>
                <div class="star" style="left: 25%; animation-delay: 2s;">★</div>
                <div class="star" style="left: 35%; animation-delay: 3s;">★</div>
                <div class="star" style="left: 45%; animation-delay: 4s;">★</div>
                <div class="star" style="left: 55%; animation-delay: 5s;">★</div>
                <div class="star" style="left: 65%; animation-delay: 6s;">★</div>
                <div class="star" style="left: 75%; animation-delay: 7s;">★</div>
                <div class="star" style="left: 85%; animation-delay: 8s;">★</div>
                <div class="star" style="left: 95%; animation-delay: 9s;">★</div>
                
                <!-- Rayos de energía -->
                <div class="energy-ray" style="top: 40%; left: 10%; width: 250px; animation-delay: 0s;"></div>
                <div class="energy-ray" style="top: 60%; left: 50%; width: 200px; animation-delay: 2s;"></div>
                <div class="energy-ray" style="top: 20%; left: 30%; width: 220px; animation-delay: 4s;"></div>
                <div class="energy-ray" style="top: 80%; left: 20%; width: 180px; animation-delay: 6s;"></div>
                
            <?php elseif ($currentStyle === 'independencia'): ?>
                <!-- Banderas peruanas -->
                <div class="flag" style="left: 5%; animation-delay: 0s;">🇵🇪</div>
                <div class="flag" style="left: 15%; animation-delay: 1s;">🇵🇪</div>
                <div class="flag" style="left: 25%; animation-delay: 2s;">🇵🇪</div>
                <div class="flag" style="left: 35%; animation-delay: 3s;">🇵🇪</div>
                <div class="flag" style="left: 45%; animation-delay: 4s;">🇵🇪</div>
                <div class="flag" style="left: 55%; animation-delay: 5s;">🇵🇪</div>
                <div class="flag" style="left: 65%; animation-delay: 6s;">🇵🇪</div>
                <div class="flag" style="left: 75%; animation-delay: 7s;">🇵🇪</div>
                <div class="flag" style="left: 85%; animation-delay: 8s;">🇵🇪</div>
                <div class="flag" style="left: 95%; animation-delay: 9s;">🇵🇪</div>
                
                <!-- Estrellas blancas -->
                <div class="white-star" style="top: 8%; left: 8%; animation-delay: 0s;">★</div>
                <div class="white-star" style="top: 12%; left: 28%; animation-delay: 1s;">★</div>
                <div class="white-star" style="top: 6%; left: 48%; animation-delay: 2s;">★</div>
                <div class="white-star" style="top: 9%; left: 68%; animation-delay: 3s;">★</div>
                <div class="white-star" style="top: 11%; left: 88%; animation-delay: 4s;">★</div>
                
            <?php endif; ?>
            
            <div class="datetime-container">
                <div class="current-date" id="current-date">
                    <?= date('l, d \d\e F \d\e Y') ?>
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
                    <div class="typing-line segunda-linea">
                        <span class="typing-text" id="typing-text-2"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>







    

    <script>
        // Enfocar automáticamente el campo DNI
        document.getElementById('dni').focus();
        
        // Función para obtener la hora local del cliente (Perú)
        function getLocalTime() {
            const now = new Date();
            
            // Configurar para zona horaria de Perú (UTC-5)
            const peruTime = new Date(now.toLocaleString("en-US", {timeZone: "America/Lima"}));
            
            const hours = String(peruTime.getHours()).padStart(2, '0');
            const minutes = String(peruTime.getMinutes()).padStart(2, '0');
            const seconds = String(peruTime.getSeconds()).padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }
        
        // Función para obtener la fecha local del cliente (Perú)
        function getLocalDate() {
            const now = new Date();
            const peruTime = new Date(now.toLocaleString("en-US", {timeZone: "America/Lima"}));
            
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: 'America/Lima'
            };
            
            return peruTime.toLocaleDateString('es-ES', options);
        }
        
        // Actualizar reloj cada segundo (solo para el reloj principal, no para la tarjeta del empleado)
        function updateClock() {
            const dateStr = getLocalDate();
            const timeStr = getLocalTime();
            
            document.getElementById('current-date').textContent = 
                dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            document.getElementById('current-time').textContent = timeStr;
            
            // Se mantiene estática con la hora exacta del registro
        }
        
        setInterval(updateClock, 1000);
        updateClock();
        
        // Efecto de máquina de escribir
        const messageLines = <?= json_encode($currentMessages) ?>;
        
        function typeWriter(elementId, text, speed, callback) {
            let i = 0;
            const elem = document.getElementById(elementId);
            elem.innerHTML = '';
            elem.style.borderRight = '3px solid var(--white)';
            
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
        
        // Mostrar notificación y tarjeta de empleado 
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
        
        // Mostrar tarjeta de empleado
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
            if (dniPrincipal && /^\d{8,9}$/.test(dniPrincipal)) {
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
        
        
        
        // Manejar tecla Escape para cerrar modales
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (modalPermisos.classList.contains('show')) {
                    modalPermisos.classList.remove('show');
                    clearModalForms();
                }
                if (modalLoginAdmin.classList.contains('show')) {
                    modalLoginAdmin.classList.remove('show');
                    clearModalForms();
                }
            }
        });

        // Establecer horas por defecto en el formulario de permisos
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const horaActual = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            
            // Establecer hora de salida como la hora actual
            document.getElementById('hora-salida').value = horaActual;
            
            // Establecer hora de retorno como 1 hora después
            const horaRetorno = new Date(now.getTime() + 60 * 60 * 1000);
            const horaRetornoStr = horaRetorno.getHours().toString().padStart(2, '0') + ':' + horaRetorno.getMinutes().toString().padStart(2, '0');
            document.getElementById('hora-retorno').value = horaRetornoStr;
        });
    </script>
</body>
</html>