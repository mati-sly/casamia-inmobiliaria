import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import Swal from "sweetalert2";

export default function Recuperar() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  // 🆕 FUNCIÓN PARA OBTENER BASE URL DINÁMICAMENTE
  const getBaseURL = () => {
    const host = window.location.hostname;
    return `http://${host}`;
  };

  // 🆕 FUNCIÓN PARA VOLVER AL INDEX
  const volverAlIndex = () => {
    const origenURL = localStorage.getItem('origen_url');
    const baseURL = getBaseURL();
    
    if (origenURL && origenURL.includes('index.php')) {
      localStorage.removeItem('origen_url');
      window.location.href = origenURL;
    } else {
      window.location.href = `${baseURL}/index.php`;
    }
  };

  const validarEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // Función para hacer fetch y parsear JSON
  const fetchJson = async (url, options) => {
    try {
      const res = await fetch(url, options);
      const text = await res.text();
      try {
        const data = JSON.parse(text);
        return { ok: res.ok, data };
      } catch {
        throw new Error("Respuesta no JSON del servidor:\n" + text);
      }
    } catch (error) {
      throw new Error("Error de conexión: " + error.message);
    }
  };

  const handleReset = async (e) => {
    e.preventDefault();

    const emailTrimmed = email.trim();

    // Validar email
    if (!emailTrimmed) {
      Swal.fire({
        icon: "error",
        title: "Email requerido",
        text: "Por favor ingresa tu correo electrónico"
      });
      return;
    }

    if (!validarEmail(emailTrimmed)) {
      Swal.fire({
        icon: "error",
        title: "Email inválido",
        text: "Por favor ingresa un correo electrónico válido"
      });
      return;
    }

    setLoading(true);

    try {
      // 🆕 DEFINIR BASE URL
      const baseURL = getBaseURL();
      console.log('🔗 Enviando código desde:', baseURL);

      // 1. Solicitar código al backend PHP
      let { ok, data } = await fetchJson(`${baseURL}/codigo_recuperacion.php`, {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo: emailTrimmed }),
      });

      if (!ok || !data.success) {
        setLoading(false);
        return Swal.fire({
          icon: "error",
          title: "Error",
          text: data?.message || "No se pudo enviar el código."
        });
      }

      // 2. Mostrar mensaje de éxito
      await Swal.fire({
        icon: "success",
        title: "¡Código enviado!",
        html: `
          <div style="text-align: center;">
            <p><strong>Se ha enviado un código de 6 dígitos a:</strong></p>
            <p style="color: #007bff; font-weight: bold;">${emailTrimmed}</p>
            <hr>
            <p style="color: #666; font-size: 0.9em;">
              <i class="fas fa-clock"></i> El código expira en 2 minutos<br>
              <i class="fas fa-exclamation-triangle"></i> Revisa tu carpeta de SPAM si no lo ves
            </p>
          </div>
        `,
        confirmButtonText: "Continuar",
        timer: 5000,
        timerProgressBar: true
      });

      // 3. Pedir código con temporizador
      const totalSeconds = 120;
      let timerInterval;

      const { value: codigo } = await Swal.fire({
        title: "Ingresa tu código",
        html: `
          <div style="text-align: center;">
            <p>Código enviado a: <strong>${emailTrimmed}</strong></p>
            <p style="color: #dc3545;">Tiempo restante: <span id="timer">${totalSeconds}</span> segundos</p>
          </div>
        `,
        input: "text",
        inputPlaceholder: "000000",
        inputAttributes: {
          maxlength: 6,
          style: "text-align: center; font-size: 2em; letter-spacing: 0.5em; font-weight: bold;"
        },
        showCancelButton: true,
        confirmButtonText: "Verificar Código",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        inputValidator: (value) => {
          if (!/^\d{6}$/.test(value)) {
            return "Ingresa exactamente 6 dígitos";
          }
        },
        timer: totalSeconds * 1000,
        timerProgressBar: true,
        didOpen: () => {
          const timerElement = document.getElementById('timer');
          timerInterval = setInterval(() => {
            const timeLeft = Math.ceil(Swal.getTimerLeft() / 1000);
            if (timerElement) {
              timerElement.textContent = timeLeft;
              if (timeLeft <= 30) {
                timerElement.style.color = '#dc3545';
                timerElement.style.fontWeight = 'bold';
              }
            }
          }, 1000);
        },
        willClose: () => {
          clearInterval(timerInterval);
        }
      });

      if (!codigo) {
        setLoading(false);
        return;
      }

      // 4. Verificar código - 🆕 USAR BASE URL
      ({ ok, data } = await fetchJson(`${baseURL}/codigo_recuperacion.php`, {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo: emailTrimmed, codigo }),
      }));

      if (!ok || !data.success) {
        setLoading(false);
        return Swal.fire({
          icon: "error",
          title: "Código inválido",
          text: data?.message || "El código no coincide o ha expirado."
        });
      }

      // 5. Pedir nueva contraseña
      const { value: formValues } = await Swal.fire({
        title: "Nueva contraseña",
        html: `
          <div style="text-align: left;">
            <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nueva contraseña:</label>
              <input type="password" id="nueva-pass" class="swal2-input" placeholder="Mínimo 6 caracteres">
            </div>
            <div style="margin-bottom: 15px;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Confirmar contraseña:</label>
              <input type="password" id="confirmar-pass" class="swal2-input" placeholder="Confirma tu contraseña">
            </div>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.9em;">
              <strong>Requisitos:</strong>
              <ul style="margin: 5px 0; padding-left: 20px;">
                <li>Mínimo 6 caracteres</li>
                <li>Se recomienda usar mayúsculas y minúsculas</li>
                <li>Incluir números y símbolos</li>
              </ul>
            </div>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Cambiar Contraseña",
        cancelButtonText: "Cancelar",
        allowOutsideClick: false,
        preConfirm: () => {
          const nuevaPass = document.getElementById('nueva-pass').value;
          const confirmarPass = document.getElementById('confirmar-pass').value;
          
          if (!nuevaPass || nuevaPass.length < 6) {
            Swal.showValidationMessage('La contraseña debe tener al menos 6 caracteres');
            return false;
          }
          
          if (nuevaPass !== confirmarPass) {
            Swal.showValidationMessage('Las contraseñas no coinciden');
            return false;
          }
          
          return nuevaPass;
        }
      });

      if (!formValues) {
        setLoading(false);
        return;
      }

      // 6. Cambiar contraseña - 🆕 USAR BASE URL
      ({ ok, data } = await fetchJson(`${baseURL}/codigo_recuperacion.php`, {
        method: "POST",
        credentials: 'include',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ 
          correo: emailTrimmed, 
          codigo, 
          nueva_contrasena: formValues 
        }),
      }));

      if (!ok || !data.success) {
        setLoading(false);
        return Swal.fire({
          icon: "error",
          title: "Error",
          text: data?.message || "No se pudo cambiar la contraseña."
        });
      }

      // 7. Éxito
      Swal.fire({
        icon: "success",
        title: "¡Contraseña actualizada!",
        html: `
          <div style="text-align: center;">
            <p><strong>Tu contraseña ha sido cambiada exitosamente.</strong></p>
            <p>Ya puedes iniciar sesión con tu nueva contraseña.</p>
            <div style="margin-top: 20px;">
              <i class="fas fa-check-circle" style="color: #28a745; font-size: 3em;"></i>
            </div>
          </div>
        `,
        confirmButtonText: "Ir al Login",
        allowOutsideClick: false
      }).then(() => {
        // 🆕 REDIRIGIR AL LOGIN REACT CORRECTAMENTE
        navigate("/login");
      });

      setEmail("");

    } catch (error) {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor. Verifica tu conexión."
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container-fluid vh-100 d-flex align-items-center justify-content-center" 
         style={{background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)"}}>
      
      {/* 🆕 BOTÓN PARA VOLVER AL INDEX */}
      <div className="position-fixed top-0 start-0 m-3">
        <button 
          onClick={volverAlIndex}
          className="btn btn-outline-light btn-sm"
          title="Volver al inicio"
        >
          <i className="fas fa-home"></i> Volver al Inicio
        </button>
      </div>

      <div className="row justify-content-center w-100">
        <div className="col-md-6 col-lg-4">
          <div className="card shadow-lg border-0">
            <div className="card-header bg-warning text-dark text-center">
              <h2 className="mb-0">
                <i className="fas fa-key me-2"></i>
                Recuperar Contraseña
              </h2>
            </div>
            <div className="card-body p-4">
              <div className="text-center mb-4">
                <div className="mb-3">
                  <i className="fas fa-home fa-3x text-warning"></i>
                </div>
                <h5 className="text-primary">Casa Mía Inmobiliaria</h5>
                <p className="text-muted">
                  Ingresa tu correo electrónico registrado y te enviaremos un código de verificación.
                </p>
              </div>

              <form onSubmit={handleReset}>
                <div className="mb-3">
                  <label htmlFor="email" className="form-label">
                    <i className="fas fa-envelope me-1"></i>
                    Correo Electrónico
                  </label>
                  <input
                    type="email"
                    className="form-control form-control-lg"
                    id="email"
                    placeholder="tu@email.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    disabled={loading}
                  />
                </div>

                <div className="d-grid gap-2">
                  <button
                    type="submit"
                    className="btn btn-warning btn-lg"
                    disabled={loading}
                  >
                    {loading ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2"></span>
                        Enviando...
                      </>
                    ) : (
                      <>
                        <i className="fas fa-paper-plane me-2"></i>
                        Enviar Código
                      </>
                    )}
                  </button>
                  
                  {/* 🆕 BOTONES MEJORADOS */}
                  <Link to="/login" className="btn btn-outline-secondary">
                    <i className="fas fa-arrow-left me-2"></i>
                    Volver al Login
                  </Link>
                  
                  <button 
                    type="button"
                    onClick={volverAlIndex}
                    className="btn btn-outline-primary btn-sm"
                  >
                    <i className="fas fa-home me-1"></i>
                    Ir al Inicio
                  </button>
                </div>
              </form>
            </div>
            <div className="card-footer bg-light text-center">
              <small className="text-muted">
                <i className="fas fa-info-circle me-1"></i>
                El código expira en 2 minutos
              </small>
              {/* 🆕 INFORMACIÓN DE DEBUG */}
              <div className="mt-2">
                <small className="text-info">
                  <i className="fas fa-server me-1"></i>
                  
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}