# Manual de Configuración de Usuario y Roles

## 1. Gestión de Usuarios (Solo Administrador)

### Acceso
- Desde el menú lateral, haz clic en **Administración** (solo visible para administrador)
- Luego haz clic en **Usuarios**

### Ver Usuarios
- Verás lista de todos los usuarios del sistema
- Información: Nombre completo, Usuario, Email, Rol, Estado (Activo/Inactivo)

### Crear un Nuevo Usuario

1. Haz clic en **Crear Usuario**
2. Rellena los campos:
   - **Tipo de Documento**: Cédula
   - **Número de Cédula**: V-12345678 o E-12345678
   - **Nombres**: Primer y segundo nombre
   - **Apellidos**: Primer y segundo apellido
   - **Email**: Correo único
   - **Usuario**: Login único (sin espacios)
   - **Teléfono**: Número de contacto
   - **Cargo**: Puesto en la institución
   - **Rol**: Selecciona rol (ver sección de roles)
3. El sistema genera una contraseña temporal
4. El usuario deberá cambiarla en su primer login
5. Haz clic en **Guardar Usuario**

### Editar un Usuario

1. Haz clic en el usuario a editar
2. Modifica los datos necesarios
3. Puedes cambiar:
   - Email
   - Teléfono
   - Cargo
   - Rol
   - Estado (Activo/Inactivo)
4. Haz clic en **Guardar Cambios**

### Desactivar un Usuario

1. Haz clic en el usuario
2. Cambia Estado a **Inactivo**
3. El usuario no podrá acceder al sistema
4. Guarda cambios

### Cambiar Rol de un Usuario

1. Haz clic en el usuario
2. Selecciona nuevo **Rol** del dropdown
3. Haz clic en **Guardar Cambios**
4. El usuario tendrá nuevos permisos al siguiente login

### Resetear Contraseña de un Usuario

1. Haz clic en el usuario
2. Haz clic en **Resetear Contraseña**
3. Una contraseña temporal se genera
4. Comunica la contraseña al usuario
5. Usuario deberá cambiarla en su próximo login

## 2. Roles y Permisos

### Roles Disponibles

#### 👤 Administrador
- **Permisos**: Acceso total al sistema
- **Funciones**:
  - Gestión de usuarios (crear, editar, eliminar)
  - Crear y editar bienes
  - Eliminar bienes
  - Aprobar movimientos
  - Generar reportes
  - Ver auditoría
  - Configuración del sistema

#### 📋 Gerencia de Bienes Nacionales (gerencia_bn)
- **Permisos**: Gestión completa de bienes
- **Funciones**:
  - Crear bienes con número asignado
  - Editar bienes
  - Aprobar movimientos de bienes
  - Generar actas y reportes
  - Ver auditoría
  - **NO PUEDE**: Eliminar bienes, gestionar usuarios

#### 🔍 Controlador de Inventario
- **Permisos**: Control y supervisión
- **Funciones**:
  - Ver bienes
  - Editar información de bienes
  - Generar reportes
  - Ver auditoría
  - Verificar consistencia del inventario
  - **NO PUEDE**: Crear bienes, eliminar, aprobar movimientos

#### 📝 Registrador
- **Permisos**: Solo registro de datos
- **Funciones**:
  - Crear bienes
  - Ver bienes
  - Ver movimientos
  - **NO PUEDE**: Editar, eliminar, aprobar, generar reportes

#### ✅ Validador de Inventario
- **Permisos**: Verificación de bienes
- **Funciones**:
  - Ver bienes
  - Marcar bienes como verificados
  - Generar reportes de verificación
  - **NO PUEDE**: Crear, editar, eliminar

## 3. Cambiar tu Contraseña

1. Desde cualquier página, haz clic en tu nombre (esquina superior derecha)
2. Haz clic en **Mi Perfil**
3. Haz clic en **Cambiar Contraseña**
4. Ingresa:
   - **Contraseña Actual**: Tu contraseña actual
   - **Nueva Contraseña**: Mínimo 12 caracteres, mayúscula, minúscula, número y carácter especial
   - **Confirmar**: Repite la nueva contraseña
5. Haz clic en **Cambiar Contraseña**

### Requisitos de Contraseña
- Mínimo 12 caracteres
- Al menos 1 mayúscula (A-Z)
- Al menos 1 minúscula (a-z)
- Al menos 1 número (0-9)
- Al menos 1 carácter especial (!@#$%^&*)

### Ejemplo de Contraseña Válida
```
HolaM2026@Bn!
```

## 4. Recuperar Contraseña Olvidada

1. En la pantalla de login, haz clic en **¿Olvidaste tu contraseña?**
2. Ingresa tu **correo electrónico**
3. Recibirás un código de 6 dígitos por correo
4. Ingresa el código y nueva contraseña
5. Listo, ya puedes acceder

## 5. Mi Perfil

### Ver tu Perfil
1. Haz clic en tu nombre (esquina superior derecha)
2. Haz clic en **Mi Perfil**
3. Verás:
   - Tu información personal
   - Rol actual
   - Último acceso
   - Datos de contacto

### Editar tu Perfil
1. Ve a **Mi Perfil**
2. Haz clic en **Editar**
3. Puedes cambiar:
   - Segundo nombre
   - Segundo apellido
   - Teléfono
4. Haz clic en **Guardar Cambios**

## 6. Auditoría de Accesos

### Ver Quién Accedió y Cuándo (Admin)
1. Ve a **Administración** > **Auditoría**
2. Verás tabla de todos los accesos al sistema
3. Información:
   - Usuario que accedió
   - Fecha y hora
   - Acción realizada (crear, editar, eliminar)
   - Objeto (bien, usuario, movimiento)
   - Cambios realizados

## 7. Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| "Contraseña incorrecta" | Contraseña ingresada es errónea | Verifica mayúsculas/minúsculas o usa "Olvidé contraseña" |
| "Usuario no existe" | El usuario no está registrado | Solicita al Administrador crearte cuenta |
| "Acceso denegado" | Tu rol no tiene permisos | Solicita rol apropiado al Administrador |
| "Contraseña muy débil" | No cumple requisitos | Usa mayúscula, minúscula, número y carácter especial |
| "Email ya registrado" | Otro usuario usa ese email | Usa email diferente o verifica con Administrador |

---

**¿Necesitas ayuda? Contacta al Administrador del Sistema.**
