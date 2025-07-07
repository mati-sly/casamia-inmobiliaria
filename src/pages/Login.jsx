import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import Swal from "sweetalert2";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showLogin, setShowLogin] = useState(false);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  // 🆕 FUNCIÓN PARA OBTENER URLs DINÁMICAS
  const getBaseURL = () => {
    const host = window.location.hostname;
    return `http://${host}`;
  };

  // 🆕 FUNCIÓN PARA VOLVER AL INDEX (FLUJO BIDIRECCIONAL)
  const volverAlIndex = () => {
    const origenURL = localStorage.getItem('origen_url');
    const baseURL = getBaseURL();
    
    console.log('🏠 Volviendo al index desde:', origenURL || 'URL por defecto');
    
    if (origenURL && origenURL.includes('index.php')) {
      // Si hay URL de origen guardada, usar esa
      localStorage.removeItem('origen_url');
      window.location.href = origenURL;
    } else {
      // Si no, usar index.php con la IP actual
      window.location.href = `${baseURL}/index.php`;
    }
  };

  const validarEmail = (email) => {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    
    // Validaciones
    if (!email.trim()) {
      Swal.fire({
        icon: "error",
        title: "Debes ingresar un email"
      });
      return;
    }

    if (!validarEmail(email)) {
      Swal.fire({
        icon: "error",
        title: "Debes ingresar un email válido",
        text: "ejemplo@gmail.com"
      });
      return;
    }

    if (!password.trim()) {
      Swal.fire({
        icon: "error",
        title: "Debes ingresar una contraseña"
      });
      return;
    }

    setLoading(true);

    try {
      // 🆕 USAR BASE URL DINÁMICA PARA CONEXIÓN CON PHP
      const baseURL = getBaseURL();
      
      console.log('🌐 Intentando login desde:', baseURL);

      const response = await fetch(`${baseURL}/login_consulta.php`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
          email: email, 
          password: password 
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Login exitoso
        Swal.fire({
          icon: "success",
          title: "¡Bienvenido!",
          html: `
            <div style="text-align: center;">
              <p><strong>Hola ${data.usuario.nombreCompleto}</strong></p>
              <p>Has iniciado sesión correctamente en Casa Mía Inmobiliaria</p>
              <p><small>Tipo de usuario: ${data.usuario.tipoUsuario}</small></p>
              <div style="margin-top: 15px;">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 3em;"></i>
              </div>
            </div>
          `,
          timer: 3000,
          timerProgressBar: true,
          showConfirmButton: false,
          willClose: () => {
            // Guardar datos de usuario en localStorage
            localStorage.setItem("usuarioLogueado", JSON.stringify({
              id: data.usuario.id,
              email: email,
              nombreCompleto: data.usuario.nombreCompleto,
              tipoUsuario: data.usuario.tipoUsuario
            }));
            
            // 🎯 REDIRECCIÓN AUTOMÁTICA SEGÚN TIPO DE USUARIO
            if (data.usuario.tipoUsuario === 'propietario') {
              // PROPIETARIOS van directo a sus propiedades PHP
              console.log('👤 Propietario detectado → usuarioPropiedades.php');
              window.location.href = `${baseURL}/usuarioPropiedades.php`;
            } else if (data.usuario.tipoUsuario === 'gestor') {
              // GESTORES van a gestor.php
              console.log('👨‍💼 Gestor detectado → gestor.php');
              window.location.href = `${baseURL}/gestor.php`;
            } else if (data.usuario.tipoUsuario === 'admin') {
              // ADMINS van al dashboard React
              console.log('👨‍💼 Admin detectado → Dashboard React');
              navigate("/dashboard");
            } else {
              // Otros tipos van al dashboard por defecto
              console.log('❓ Tipo desconocido → Dashboard React');
              navigate("/dashboard");
            }
          }
        });
      } else {
        // Error en login
        Swal.fire({
          icon: "error",
          title: "Error de autenticación",
          text: data.message
        });
      }

    } catch (error) {
      console.error("Error en el login:", error);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor"
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
      {/* Botones de navegación */}
      <div className="position-fixed top-0 start-0 m-3">
        <div className="d-flex gap-2">
          {/* 🆕 BOTÓN MEJORADO PARA VOLVER AL INDEX */}
          <button 
            onClick={volverAlIndex}
            className="btn btn-outline-success btn-sm"
            title="Volver al inicio"
          >
            <i className="fas fa-home"></i> Volver al Inicio
          </button>
          
          {!showLogin && (
            <button
              className="btn btn-outline-primary btn-sm"
              onClick={() => setShowLogin(true)}
              title="Mostrar formulario de login"
            >
              <i className="fas fa-eye"></i> Mostrar Login
            </button>
          )}
          {showLogin && (
            <button
              className="btn btn-outline-secondary btn-sm"
              onClick={() => setShowLogin(false)}
              title="Ocultar formulario de login"
            >
              <i className="fas fa-eye-slash"></i> Ocultar Login
            </button>
          )}
        </div>
      </div>

      {/* Formulario de Login */}
      <div className={`container ${showLogin ? 'animate__animated animate__fadeIn' : 'd-none'}`}>
        <div className="row justify-content-center">
          <div className="col-md-6 col-lg-5">
            <div className="card shadow-lg">
              <div className="card-header bg-primary text-white">
                <h2 className="text-center mb-0">
                  <i className="fas fa-lock me-2"></i>
                  Casa Mía Inmobiliaria
                </h2>
              </div>
              <div className="card-body p-4">
                <div className="row align-items-center">
                  <div className="col-md-4 text-center mb-3 mb-md-0">
                    <div className="text-primary">
                      <i className="fas fa-user-circle fa-5x"></i>
                    </div>
                  </div>
                  <div className="col-md-8">
                    <form onSubmit={handleLogin}>
                      <div className="mb-3">
                        <label htmlFor="email" className="form-label">
                          <h5 className="text-primary">Usuario:</h5>
                        </label>
                        <input
                          type="email"
                          className="form-control form-control-lg"
                          id="email"
                          placeholder="Ingresar correo electrónico"
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          required
                          disabled={loading}
                        />
                      </div>

                      <div className="mb-3">
                        <label htmlFor="password" className="form-label">
                          <h5 className="text-primary">Contraseña:</h5>
                        </label>
                        <input
                          type="password"
                          className="form-control form-control-lg"
                          id="password"
                          placeholder="Ingresar contraseña"
                          value={password}
                          onChange={(e) => setPassword(e.target.value)}
                          required
                          disabled={loading}
                        />
                      </div>

                      <div className="d-grid">
                        <button
                          type="submit"
                          className="btn btn-primary btn-lg"
                          disabled={loading}
                        >
                          {loading ? (
                            <>
                              <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                              Ingresando...
                            </>
                          ) : (
                            <>
                              <i className="fas fa-sign-in-alt me-2"></i>
                              Ingresar
                            </>
                          )}
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <div className="card-footer bg-light">
                <div className="text-center">
                  <div className="mb-2">
                    <i className="fas fa-user-plus me-1"></i>
                    ¿No tienes cuenta?{" "}
                    <Link to="/registrarme" className="text-decoration-none fw-bold">
                      Regístrate aquí
                    </Link>
                  </div>
                  <div className="mb-2">
                    <i className="fas fa-key me-1"></i>
                    ¿Olvidaste tu contraseña?{" "}
                    <Link to="/recuperar" className="text-decoration-none fw-bold">
                      Recuperar Contraseña
                    </Link>
                  </div>
                  {/* 🆕 BOTÓN MEJORADO EN EL FOOTER */}
                  <div className="mt-3 pt-2 border-top">
                    <button 
                      onClick={volverAlIndex}
                      className="btn btn-outline-secondary btn-sm"
                    >
                      <i className="fas fa-arrow-left me-1"></i>
                      Volver al sitio principal
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Mensaje inicial cuando está oculto */}
      {!showLogin && (
        <div className="text-center">
          <div className="mb-4">
            <i className="fas fa-home fa-5x text-primary"></i>
          </div>
          <h1 className="text-primary mb-3">Bienvenido a Casa Mía</h1>
          <p className="text-muted mb-4">
            Sistema de gestión inmobiliaria
          </p>
          <div className="d-flex gap-2 justify-content-center">
            <Link to="/registrarme" className="btn btn-outline-primary">
              <i className="fas fa-user-plus me-1"></i>
              Crear Cuenta
            </Link>
            {/* 🆕 BOTÓN MEJORADO EN LA VISTA INICIAL */}
            <button 
              onClick={volverAlIndex}
              className="btn btn-success"
            >
              <i className="fas fa-home me-1"></i>
              Ver Propiedades
            </button>
          </div>

          {/* Información visual del flujo */}
          <div className="mt-5 p-4 bg-white rounded shadow-sm">
            <h6 className="text-muted mb-3">
              <i className="fas fa-info-circle me-2"></i>
              Información del Sistema
            </h6>
            <div className="row">
              <div className="col-md-4">
                <div className="p-3 border-start border-primary border-3">
                  <h6 className="text-primary">
                    <i className="fas fa-user-tie me-2"></i>
                    Administradores
                  </h6>
                  <p className="small text-muted mb-1">Acceden al dashboard React completo</p>
                  <small>✅ Gestión de usuarios y propiedades</small>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 border-start border-warning border-3">
                  <h6 className="text-warning">
                    <i className="fas fa-user-cog me-2"></i>
                    Gestores
                  </h6>
                  <p className="small text-muted mb-1">Acceden directo a gestor.php</p>
                  <small>✅ Gestión intermedia de propiedades</small>
                </div>
              </div>
              <div className="col-md-4">
                <div className="p-3 border-start border-success border-3">
                  <h6 className="text-success">
                    <i className="fas fa-home me-2"></i>
                    Propietarios
                  </h6>
                  <p className="small text-muted mb-1">Acceden directo a usuarioPropiedades.php</p>
                  <small>✅ Gestión personal de inmuebles</small>
                </div>
              </div>
            </div>
            {/* 🆕 INFORMACIÓN DE DEBUG */}
            <div className="mt-3 p-2 bg-info bg-opacity-10 rounded">
             
            </div>
          </div>
        </div>
      )}
    </div>
  );
}