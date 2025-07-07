# 🏠 Casa Mía - Sistema Inmobiliario

<div align="center">

**Sistema completo de gestión inmobiliaria con arquitectura híbrida React + PHP**

*Desarrollado con tecnologías modernas para una experiencia de usuario excepcional*

---

**React** • **PHP** • **MySQL** • **Vite** • **Bootstrap**

---

[📖 Documentación](#-instalación) • [🐛 Issues](https://github.com/mati-sly/casamia-inmobiliaria/issues) • [⭐ Dar Estrella](https://github.com/mati-sly/casamia-inmobiliaria)

</div>

---

## 📋 Índice

```
🎯  Características del Sistema
🛠️  Stack Tecnológico  
🏗️  Arquitectura
🔧  Instalación
⚙️  Configuración
🌐  URLs y Endpoints
👥  Roles de Usuario
📂  Estructura del Proyecto
🚀  Deployment
🤝  Contribuir
📄  Licencia
```

---

## 🎯 Características del Sistema

### ✨ **Funcionalidades Principales**

<table>
<tr>
<td width="50%">

**🔐 Autenticación Segura**
- Login con hash bcrypt
- Sesiones PHP integradas  
- Roles diferenciados
- Validaciones en tiempo real

**🏠 Gestión de Propiedades**
- CRUD completo implementado
- Galería de imágenes múltiples
- Filtros avanzados
- Sistema de ubicaciones

</td>
<td width="50%">

**👥 Gestión de Usuarios**
- Registro automático
- Perfiles por rol
- Validaciones automáticas
- Panel administrativo

**🎨 Interfaz Moderna**
- Dashboard con React
- Diseño responsive
- Proxy Vite transparente
- Bootstrap 5 integrado

</td>
</tr>
</table>

### 🌟 **Experiencia de Usuario**

> - **📱 Responsive**: Adaptable a móviles, tablets y desktop
> - **⚡ Validaciones Automáticas**: Feedback visual en tiempo real
> - **🔄 Formateo Inteligente**: RUT y teléfonos se formatean automáticamente  
> - **🎭 Interfaz Elegante**: Bootstrap 5 con iconografía FontAwesome
> - **🌐 Sistema Híbrido**: React para UI moderna + PHP para backend robusto

---

## 🛠️ Stack Tecnológico

### **Frontend**

```yaml
Framework:          React 18.2.0
Bundler:           Vite 4.1.0
Navegación:        React Router DOM 6.8.1
Estilos:           Bootstrap 5.3.0
Notificaciones:    SweetAlert2 11.7.3
Iconos:            FontAwesome 6.x
```

### **Backend**

```yaml
Lenguaje:          PHP 7.4+
Base de Datos:     MySQL 8.0
Conexión BD:       MySQLi nativo
Servidor Web:      Apache 2.4
Autenticación:     Sesiones PHP + bcrypt
```

### **Desarrollo**

```yaml
Entorno:           Node.js 16+
Gestor Paquetes:   npm 8+
Control Versiones: Git 2.x
Comunicación:      Vite Proxy
```

---

## 🏗️ Arquitectura del Sistema

### **🔗 Flujo de Comunicación**

```
┌─────────────────────┐         Proxy Vite         ┌─────────────────────┐
│                     │ ◄─────────────────────────► │                     │
│   React Frontend    │                            │    PHP Backend      │
│   (Puerto 3000)     │                            │    (Puerto 80)      │
│                     │                            │                     │
│ • Login.jsx         │                            │ • login_consulta.php│
│ • Dashboard.jsx     │                            │ • crudpropiedades   │ 
│ • Registrarme.jsx   │                            │ • registro_usuario  │
│ • React Router      │                            │ • crudusuarios      │
│                     │                            │                     │
└─────────────────────┘                            └─────────────────────┘
         │                                                  │
         │                                                  │ MySQLi
         ▼                                                  ▼
┌─────────────────────┐                            ┌─────────────────────┐
│   Vite Dev Server   │                            │   MySQL Database    │
│                     │                            │                     │
│ • Hot Reload        │                            │ • usuarios          │
│ • Proxy Config      │                            │ • propiedades       │
│ • Asset Bundling    │                            │ • galeria           │
│                     │                            │ • ubicaciones       │
└─────────────────────┘                            └─────────────────────┘
```

### **⚙️ Proxy Configuration**

```javascript
// vite.config.js - Configuración implementada
proxy: {
  '/crudpropiedades.php':   'http://localhost',
  '/registro_consulta.php': 'http://localhost', 
  '/login_consulta.php':    'http://localhost',
  '/crudusuarios.php':      'http://localhost'
}
```

---

## 🔧 Instalación

### **📋 Requisitos del Sistema**

```bash
✅ PHP 7.4+ (mysqli, json, mbstring)
✅ MySQL 8.0 / MariaDB 10.3+
✅ Apache 2.4 / Nginx
✅ Node.js 16+ & npm 8+
✅ Git
```

### **⚡ Proceso de Instalación**

```bash
# 1️⃣ Clonar repositorio
git clone https://github.com/mati-sly/casamia-inmobiliaria.git
cd casamia-inmobiliaria

# 2️⃣ Crear base de datos
mysql -u root -p -e "CREATE DATABASE inmobiliaria_casamia CHARACTER SET utf8mb4;"

# 3️⃣ Importar estructura  
mysql -u root -p inmobiliaria_casamia < database/structure.sql

# 4️⃣ Configurar conexión
cp setup/config.template.php setup/config.php
nano setup/config.php  # Editar credenciales

# 5️⃣ Instalar dependencias
npm install

# 6️⃣ Configurar permisos
chmod 755 setup/ && chmod 777 IMG/usuarios/ propiedades/

# 7️⃣ Iniciar desarrollo
npm run dev  # React: puerto 3000
```

---

## ⚙️ Configuración

### **🔧 Base de Datos**

```php
// setup/config.php
function conectar() {
    $servidor = "localhost";
    $usuario = "tu_usuario";        // ← Cambiar
    $password = "tu_password";      // ← Cambiar
    $bd = "inmobiliaria_casamia";
    
    $conexion = new mysqli($servidor, $usuario, $password, $bd);
    $conexion->set_charset("utf8");
    return $conexion;
}
```

### **🌐 Proxy Vite**

```javascript
// vite.config.js
export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 3000,
    proxy: {
      '/**.php': { target: 'http://localhost' },
      '/IMG': { target: 'http://localhost' }
    }
  }
})
```

---

## 🌐 URLs y Endpoints

### **📍 URLs Principales**

| **Servicio** | **URL** | **Descripción** |
|--------------|---------|-----------------|
| **React App** | `http://localhost:3000` | Frontend principal |
| **PHP Backend** | `http://localhost` | APIs y páginas PHP |
| **Login** | `http://localhost:3000/login` | Autenticación |
| **Registro** | `http://localhost:3000/registrarme` | Registro usuarios |
| **Dashboard** | `http://localhost:3000/dashboard` | Panel admin |

### **🔌 Endpoints API**

```http
POST /login_consulta.php          # Autenticación
POST /registro_consulta.php       # Registro de usuarios
GET  /crudpropiedades.php         # Listar propiedades
POST /crudpropiedades.php         # Crear propiedades
GET  /registro_usuario.php        # Gestión usuarios
POST /crudusuarios.php            # CRUD usuarios
```

---

## 👥 Roles de Usuario

### **🔑 Permisos por Rol**

<table>
<tr>
<td width="33%">

**👨‍💼 Admin**
- ✅ Acceso completo
- ✅ Gestión usuarios  
- ✅ CRUD propiedades
- ✅ Dashboard React
- ✅ Configuraciones

</td>
<td width="33%">

**🏢 Gestor**
- ✅ Gestión propiedades
- ✅ Atención clientes
- ✅ Panel gestor.php
- ❌ Gestión usuarios
- ❌ Configuraciones

</td>
<td width="34%">

**🏠 Propietario**
- ✅ Sus propiedades
- ✅ Actualizar perfil
- ✅ Panel propietario
- ❌ Otras propiedades
- ❌ Administración

</td>
</tr>
</table>

---

## 📂 Estructura del Proyecto

```
casamia-inmobiliaria/
├── 📁 src/                          # Código React
│   ├── components/
│   │   ├── Login.jsx               # ✅ Sistema de login
│   │   ├── Dashboard.jsx           # ✅ Panel administrativo  
│   │   └── Registrarme.jsx         # ✅ Registro usuarios
│   └── main.jsx                    # ✅ Punto de entrada
├── 📁 setup/                       # Configuración PHP
│   ├── config.template.php         # ✅ Template configuración
│   └── config.php                  # ✅ Config real (gitignored)
├── 📁 css/, js/, IMG/              # ✅ Assets estáticos
├── 📁 propiedades/                 # ✅ Imágenes propiedades
├── 📄 index.php                    # ✅ Página principal
├── 📄 crudpropiedades.php         # ✅ API propiedades
├── 📄 registro_usuario.php        # ✅ CRUD usuarios
├── 📄 login_consulta.php          # ✅ API autenticación
├── 📄 registro_consulta.php       # ✅ API registro
├── 📄 vite.config.js              # ✅ Config Vite + Proxy
├── 📄 package.json                # ✅ Dependencias Node
└── 📄 .gitignore                  # ✅ Archivos ignorados
```

---

## 🚀 Deployment

### **🌐 Producción**

```bash
# 1️⃣ Build de React
npm run build

# 2️⃣ Configurar Apache Virtual Host
sudo nano /etc/apache2/sites-available/casamia.conf

# 3️⃣ Habilitar sitio
sudo a2ensite casamia.conf
sudo systemctl reload apache2

# 4️⃣ Configurar SSL (opcional)
sudo certbot --apache -d tu-dominio.com
```

### **⚙️ Configuración Apache**

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    DocumentRoot /var/www/html/casamia-inmobiliaria
    
    <Directory /var/www/html/casamia-inmobiliaria>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 🤝 Contribuir

### **🔧 Setup Desarrollo**

```bash
# Fork y clonar
git clone https://github.com/TU-USUARIO/casamia-inmobiliaria.git

# Crear rama
git checkout -b feature/nueva-funcionalidad

# Desarrollar y commitear
git commit -m "✨ feat: descripción del cambio"

# Push y PR
git push origin feature/nueva-funcionalidad
```

### **📝 Convenciones**

```
✨ feat:     Nueva funcionalidad
🐛 fix:      Corrección de bug  
📝 docs:     Documentación
🎨 style:    Cambios de estilo
♻️ refactor: Refactorización
🧪 test:     Tests
```

---

## 📄 Licencia

<div align="center">

### **MIT License**

**Copyright © 2024 Matías & Alma**

*Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software...*

---

### **👨‍💻 Desarrollado por**

**Matías & Alma**

*Sistema de gestión inmobiliaria profesional*

---

⭐ **¡Dale una estrella si te gusta el proyecto!** ⭐

</div>
