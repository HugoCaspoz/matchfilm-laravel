document.addEventListener('DOMContentLoaded', function() {
    // Función para aceptar invitaciones
    window.acceptInvitation = function(notificationId) {
        const button = event.target;
        const originalText = button.innerHTML;
        
        // Mostrar loading
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aceptando...';
        
        fetch(`/notifications/${notificationId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                showNotification(data.message, 'success');
                // Recargar la página para actualizar la vista
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Error al aceptar la invitación', 'error');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al aceptar la invitación', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        });
    };

    // Función para declinar invitaciones
    window.declineInvitation = function(notificationId) {
        if (confirm('¿Estás seguro de que quieres declinar esta invitación?')) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            // Mostrar loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Declinando...';
            
            fetch(`/notifications/${notificationId}/decline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showNotification(data.message, 'success');
                    // Recargar la página para actualizar la vista
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Error al declinar la invitación', 'error');
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al declinar la invitación', 'error');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    };

    // Función para mostrar notificaciones toast
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `toast-notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Mostrar la notificación
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Auto-actualizar notificaciones cada 30 segundos
    setInterval(() => {
        fetch('/notifications/count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error al actualizar notificaciones:', error));
    }, 30000);
});