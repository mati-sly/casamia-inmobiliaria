import { useState } from "react";
import Swal from "sweetalert2";
import { useNavigate, Link } from "react-router-dom";

export default function Registrarme() {
  const [formData, setFormData] = useState({
    tipoUsuario: "", rut: "", nombres: "", apellidoPaterno: "", apellidoMaterno: "",
    fechaNacimiento: "", correo: "", clave: "", confirmarclave: "", sexo: "", cel: ""
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  // ✅ Validaciones compactas
  const validators = {
    rut: (v) => /^[0-9]{7,8}-[0-9Kk]{1}$/.test(v) ? "" : "Formato: 12345678-9",
    correo: (v) => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v) ? "" : "Email inválido",
    clave: (v) => v.length >= 8 && /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])/.test(v) ? "" : "Min 8 chars, may/min/num/especial",
    cel: (v) => /^(\+?56\s?)?9\s?\d{4}\s?\d{4}$/.test(v) ? "" : "Formato: +56 9 1234 5678",
    nombres: (v) => v.trim().length >= 2 ? "" : "Mínimo 2 caracteres",
    apellidoPaterno: (v) => v.trim().length >= 2 ? "" : "Mínimo 2 caracteres",
    apellidoMaterno: (v) => v.trim().length >= 2 ? "" : "Mínimo 2 caracteres",
    confirmarclave: (v) => v === formData.clave ? "" : "No coincide",
    tipoUsuario: (v) => v ? "" : "Seleccione tipo",
    sexo: (v) => v ? "" : "Seleccione sexo",
    fechaNacimiento: (v) => v && new Date().getFullYear() - new Date(v).getFullYear() >= 18 ? "" : "Debe ser mayor de edad"
  };

  // ✅ Formateo automático
  const formatters = {
    rut: (v) => v.replace(/[^0-9kK]/g, '').replace(/^(\d{7,8})([0-9kK])$/, '$1-$2'),
    cel: (v) => v.replace(/[^0-9\s+]/g, '').replace(/^(56)?(\d{8})/, '+56 $2')
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    const formatted = formatters[name] ? formatters[name](value) : value;
    
    setFormData(prev => ({ ...prev, [name]: formatted }));
    
    if (validators[name]) {
      const error = validators[name](formatted);
      setErrors(prev => ({ ...prev, [name]: error }));
    }
    
    // Revalidar confirmación si cambia la clave
    if (name === 'clave' && formData.confirmarclave) {
      setErrors(prev => ({ ...prev, confirmarclave: validators.confirmarclave(formData.confirmarclave) }));
    }
  };

  const isValid = () => Object.keys(formData).every(k => !validators[k] || !validators[k](formData[k]));
  const getClass = (field) => `form-control${formData[field] ? (errors[field] ? ' is-invalid' : ' is-valid') : ''}`;

  // ✅ FUNCIÓN PARA OBTENER URL DINÁMICA
  const getBaseURL = () => {
    const host = window.location.hostname;
    return `http://${host}`;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validar todo
    const newErrors = {};
    Object.keys(validators).forEach(k => {
      const error = validators[k](formData[k]);
      if (error) newErrors[k] = error;
    });
    
    setErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      Swal.fire({ icon: "error", title: "Corrija los errores del formulario" });
      return;
    }

    setLoading(true);
    try {
      // ✅ USAR FUNCIÓN PARA BASE URL Y AÑADIR MÁS DEBUGGING
      const baseURL = getBaseURL();
      console.log('🌐 Intentando registro desde:', baseURL);

      const response = await fetch(`${baseURL}/registro_consulta.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          rut: formData.rut, 
          nombres: formData.nombres, 
          apellidoPaterno: formData.apellidoPaterno,
          apellidoMaterno: formData.apellidoMaterno, 
          fechaNacimiento: formData.fechaNacimiento,
          usuario: formData.correo, 
          clave: formData.clave, 
          sexo: formData.sexo,
          cel: formData.cel, 
          tipoUsuario: formData.tipoUsuario, 
          estado: 1
        })
      });

      console.log('📡 Response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      console.log('📥 Response data:', data);
      
      if (data.success) {
        Swal.fire({
          icon: "success", 
          title: "¡Registro exitoso!",
          html: `<p><strong>¡Bienvenido a Casa Mía!</strong></p><p>Cuenta creada como ${formData.tipoUsuario}</p>`,
          confirmButtonText: "Ir al Login", 
          showCancelButton: true, 
          cancelButtonText: "Quedar aquí"
        }).then(result => result.isConfirmed && navigate("/login"));
      } else {
        const msg = data.message?.includes("usuario") ? "Email ya registrado" : 
                   data.message?.includes("rut") ? "RUT ya registrado" : 
                   data.message || "Error en el registro";
        Swal.fire({ icon: "error", title: "Error", text: msg });
      }
    } catch (error) {
      console.error('❌ Error en registro:', error);
      Swal.fire({ 
        icon: "error", 
        title: "Error de conexión",
        text: `No se pudo conectar con el servidor. Detalles: ${error.message}`
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
      <button 
        onClick={() => window.location.href = `${getBaseURL()}/index.php`}
        className="position-fixed top-0 start-0 m-3 btn btn-outline-primary btn-sm"
      >
        <i className="fas fa-home"></i> Inicio
      </button>

      <div className="col-md-8 col-lg-6">
        <div className="card shadow-lg">
          <div className="card-header bg-primary text-white text-center">
            <h2 className="mb-0"><i className="fas fa-user-plus me-2"></i>Registro</h2>
          </div>
          <div className="card-body p-4">
            <form onSubmit={handleSubmit}>
              {/* Tipo Usuario */}
              <div className="mb-3">
                <label className="form-label"><i className="fas fa-user-tag me-1"></i>Tipo de usuario:</label>
                <select className={getClass('tipoUsuario')} name="tipoUsuario" value={formData.tipoUsuario} onChange={handleChange} disabled={loading}>
                  <option value="">Seleccione</option>
                  <option value="propietario">🏠 Propietario</option>
                  <option value="gestor">👨‍💼 Gestor</option>
                </select>
                {errors.tipoUsuario && <div className="invalid-feedback d-block">{errors.tipoUsuario}</div>}
              </div>

              <div className="row">
                {/* RUT */}
                <div className="col-md-6 mb-3">
                  <label className="form-label"><i className="fas fa-id-card me-1"></i>RUT:</label>
                  <input type="text" className={getClass('rut')} name="rut" placeholder="12345678-9" 
                         value={formData.rut} onChange={handleChange} disabled={loading} maxLength="12" />
                  {errors.rut && <div className="invalid-feedback">{errors.rut}</div>}
                </div>
                
                {/* Email */}
                <div className="col-md-6 mb-3">
                  <label className="form-label"><i className="fas fa-envelope me-1"></i>Email:</label>
                  <input type="email" className={getClass('correo')} name="correo" placeholder="email@ejemplo.com"
                         value={formData.correo} onChange={handleChange} disabled={loading} />
                  {errors.correo && <div className="invalid-feedback">{errors.correo}</div>}
                </div>
              </div>

              <div className="row">
                {/* Nombres */}
                <div className="col-md-4 mb-3">
                  <label className="form-label">Nombres:</label>
                  <input type="text" className={getClass('nombres')} name="nombres" placeholder="Ana María"
                         value={formData.nombres} onChange={handleChange} disabled={loading} />
                  {errors.nombres && <div className="invalid-feedback">{errors.nombres}</div>}
                </div>
                
                {/* Apellido Paterno */}
                <div className="col-md-4 mb-3">
                  <label className="form-label">Ap. Paterno:</label>
                  <input type="text" className={getClass('apellidoPaterno')} name="apellidoPaterno" placeholder="Castillo"
                         value={formData.apellidoPaterno} onChange={handleChange} disabled={loading} />
                  {errors.apellidoPaterno && <div className="invalid-feedback">{errors.apellidoPaterno}</div>}
                </div>
                
                {/* Apellido Materno */}
                <div className="col-md-4 mb-3">
                  <label className="form-label">Ap. Materno:</label>
                  <input type="text" className={getClass('apellidoMaterno')} name="apellidoMaterno" placeholder="Ramírez"
                         value={formData.apellidoMaterno} onChange={handleChange} disabled={loading} />
                  {errors.apellidoMaterno && <div className="invalid-feedback">{errors.apellidoMaterno}</div>}
                </div>
              </div>

              <div className="row">
                {/* Contraseña */}
                <div className="col-md-6 mb-3">
                  <label className="form-label"><i className="fas fa-lock me-1"></i>Contraseña:</label>
                  <input type="password" className={getClass('clave')} name="clave" placeholder="Min 8 caracteres"
                         value={formData.clave} onChange={handleChange} disabled={loading} />
                  {errors.clave && <div className="invalid-feedback">{errors.clave}</div>}
                </div>
                
                {/* Confirmar */}
                <div className="col-md-6 mb-3">
                  <label className="form-label"><i className="fas fa-lock me-1"></i>Confirmar:</label>
                  <input type="password" className={getClass('confirmarclave')} name="confirmarclave" placeholder="Repetir"
                         value={formData.confirmarclave} onChange={handleChange} disabled={loading} />
                  {errors.confirmarclave && <div className="invalid-feedback">{errors.confirmarclave}</div>}
                </div>
              </div>

              <div className="row">
                {/* Fecha */}
                <div className="col-md-4 mb-3">
                  <label className="form-label">F. Nacimiento:</label>
                  <input type="date" className={getClass('fechaNacimiento')} name="fechaNacimiento"
                         value={formData.fechaNacimiento} onChange={handleChange} disabled={loading}
                         max={new Date(new Date().setFullYear(new Date().getFullYear() - 18)).toISOString().split('T')[0]} />
                  {errors.fechaNacimiento && <div className="invalid-feedback">{errors.fechaNacimiento}</div>}
                </div>
                
                {/* Sexo */}
                <div className="col-md-4 mb-3">
                  <label className="form-label"><i className="fas fa-venus-mars me-1"></i>Sexo:</label>
                  <select className={getClass('sexo')} name="sexo" value={formData.sexo} onChange={handleChange} disabled={loading}>
                    <option value="">Seleccione</option>
                    <option value="femenino">Femenino</option>
                    <option value="masculino">Masculino</option>
                  </select>
                  {errors.sexo && <div className="invalid-feedback d-block">{errors.sexo}</div>}
                </div>
                
                {/* Teléfono */}
                <div className="col-md-4 mb-3">
                  <label className="form-label"><i className="fas fa-mobile-alt me-1"></i>Teléfono:</label>
                  <input type="text" className={getClass('cel')} name="cel" placeholder="+56 9 1234 5678"
                         value={formData.cel} onChange={handleChange} disabled={loading} />
                  {errors.cel && <div className="invalid-feedback">{errors.cel}</div>}
                </div>
              </div>

              {/* Botón */}
              <div className="d-grid mt-4">
                <button type="submit" className="btn btn-primary btn-lg" disabled={loading || !isValid()}>
                  {loading ? (
                    <><span className="spinner-border spinner-border-sm me-2"></span>Registrando...</>
                  ) : (
                    <><i className="fas fa-user-plus me-2"></i>Registrarme</>
                  )}
                </button>
              </div>

              {!isValid() && Object.keys(formData).some(k => formData[k]) && (
                <div className="mt-3 text-center">
                  <small className="text-muted">
                    <i className="fas fa-info-circle me-1"></i>Complete todos los campos correctamente
                  </small>
                </div>
              )}
            </form>
          </div>
          <div className="card-footer text-center bg-light">
            <p className="mb-0">¿Ya tienes cuenta? <Link to="/login" className="fw-bold">Inicia sesión</Link></p>
          </div>
        </div>
      </div>
    </div>
  );
}