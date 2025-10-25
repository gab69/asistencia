
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
