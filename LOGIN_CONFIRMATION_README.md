# Configuración de Confirmación de Login por Email

## ¿Qué es esto?

Sistema de confirmación de inicio de sesión por correo electrónico. Cuando un usuario intenta iniciar sesión, debe confirmar su identidad haciendo clic en un botón dentro de un correo enviado a su bandeja de entrada.

## Pasos de Instalación

### 1. Crear la tabla en la base de datos

Ejecuta el siguiente comando SQL en tu gestor de BD (phpMyAdmin, MySQL Workbench, etc.):

```sql
CREATE TABLE IF NOT EXISTS confirmacion_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expiracion DATETIME NOT NULL,
    confirmado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);
```

**Alternativa:** Abre en tu navegador:
```
http://localhost/WEB II/azylu_flores/php/crear_tabla_confirmacion.php
```

### 2. Verificar credenciales SMTP

En los siguientes archivos, asegúrate de que las credenciales SMTP sean correctas:

- `php/login.php` (línea ~30)
- `php/reenviar_confirmacion.php` (línea ~75)

Busca:
```php
$mail->Username = 'a23310150@ceti.mx';
$mail->Password = 'pupu qhdt grgg ojin';
```

**⚠️ Importante:** Estos datos están en el código. Para producción, usa variables de entorno (.env).

### 3. Listo

El sistema está configurado. Los usuarios recibirán ahora un correo de confirmación al intentar iniciar sesión.

## Flujo de Login

1. **Usuario inicia sesión** → login.php valida credenciales
2. **Credenciales correctas** → Se genera un token único y se envía un correo
3. **Usuario ve página de espera** → confirmar_sesion.php (espera confirmación)
4. **Usuario hace clic en correo** → confirmar_login.php valida el token
5. **Token válido** → completar_login.php inicia la sesión y redirige
6. **Sesión activa** → Accede al dashboard

## Archivos Creados/Modificados

### Nuevos archivos:
- `php/confirmar_sesion.php` - Página de espera de confirmación
- `php/confirmar_login.php` - Valida el token del correo
- `php/verificar_confirmacion.php` - Verifica si el correo fue confirmado
- `php/completar_login.php` - Completa el login
- `php/reenviar_confirmacion.php` - Reenvía el correo si es necesario
- `php/crear_tabla_confirmacion.php` - Crea la tabla (ejecutar una sola vez)

### Modificados:
- `php/login.php` - Genera token en lugar de iniciar sesión directamente

## Características de Seguridad

✅ **Tokens únicos** - Cada intento de login genera un token único de 64 caracteres  
✅ **Expiración** - Los tokens expiran en 30 minutos  
✅ **Confirmación única** - Un token solo se puede usar una vez  
✅ **Opción de rechazo** - El usuario puede rechazar intentos sospechosos  
✅ **Reenvío de correos** - Si el usuario no lo recibe, puede reenviar

## Solución de Problemas

### El usuario no recibe el correo
1. Revisa la carpeta de SPAM
2. Verifica que las credenciales SMTP sean correctas
3. Usa el botón "Reenviar correo de confirmación" en la página de espera

### El token dice "expirado"
- Los tokens duran 30 minutos. El usuario debe confirmar dentro de ese tiempo.
- Puede reenviar el correo desde la página de espera.

### Error "Token inválido"
- El token no existe o fue rechazado antes.
- El usuario debe iniciar sesión nuevamente.

## Consideraciones Importantes

- La sesión **NO se inicia** hasta que el usuario confirme el correo
- Si el usuario cierra la ventana, puede volver a entrar a `confirmar_sesion.php` desde `login.html?error=token_expirado`
- Los tokens confirmados se eliminan después de completar el login
- Los tokens no confirmados se mantienen en la BD para auditoria

## Desactivar la Confirmación por Email (Temporal)

Si necesitas desactivar esto para desarrollo, en `login.php` comenta la línea:
```php
enviarConfirmacionLogin($con, $email, $usuario['nombre'], $usuario['id_usuario']);
```

Y descomenta:
```php
// Iniciar sesión directa (sin confirmación)
$_SESSION['usuario'] = $usuario['nombre'];
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['id_rol'] = $usuario['id_rol'];
```
