import { useNavigate } from 'react-router-dom'
import Swal from 'sweetalert2'

export default function Dashboard() {
  const navigate = useNavigate()
  const userData = JSON.parse(localStorage.getItem('usuarioLogueado') || '{}')
  
  const handleLogout = () => {
    Swal.fire({
      icon: 'success',
      title: 'Sesión cerrada',
      timer: 1500,
      showConfirmButton: false
    }).then(() => {
      localStorage.removeItem('usuarioLogueado')
      navigate('/')
    })
  }

  const navegarAPHP = (archivo) => {
    window.location.href = `/${archivo}`
  }

  if (!userData.nombreCompleto && !userData.email) {
    navigate('/')
    return null
  }

  return (
    <div className="container-fluid vh-100 bg-light">
      <header className="py-3">
        <div className="container">
          <div className="card shadow">
            <div className="card-header bg-primary text-white">
              <h4 className="mb-0">Casa Mía Inmobiliaria - Dashboard</h4>
            </div>
            <div className="card-body">
              <h5><strong>Sesión iniciada por:</strong></h5>
              <h4 className="text-primary">{userData.nombreCompleto || userData.email}</h4>
              {userData.tipoUsuario && (
                <span className="badge bg-success fs-6">{userData.tipoUsuario}</span>
              )}
              <div className="mt-3">
                <button onClick={handleLogout} className="btn btn-danger">
                  <i className="fas fa-sign-out-alt me-2"></i>
                  Cerrar Sesión
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main className="container py-4">
        <div className="row">
          <div className="col-md-6 mb-4">
            <div className="card text-center h-100">
              <div className="card-body d-flex flex-column">
                <i className="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 className="card-title">CRUD Usuarios</h5>
                <p className="card-text">Gestiona usuarios del sistema</p>
                <div className="mt-auto">
                  <button 
                    onClick={() => navegarAPHP('registro_usuario.php')} 
                    className="btn btn-primary btn-lg"
                  >
                    <i className="fas fa-user-cog me-2"></i>
                    Gestionar Usuarios
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div className="col-md-6 mb-4">
            <div className="card text-center h-100">
              <div className="card-body d-flex flex-column">
                <i className="fas fa-home fa-3x text-success mb-3"></i>
                <h5 className="card-title">CRUD Propiedades</h5>
                <p className="card-text">Gestiona propiedades del sistema</p>
                <div className="mt-auto">
                  <button 
                    onClick={() => navegarAPHP('crudpropiedades.php')} 
                    className="btn btn-success btn-lg"
                  >
                    <i className="fas fa-building me-2"></i>
                    Gestionar Propiedades
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="row mt-4">
          <div className="col-12">
            <div className="card">
              <div className="card-header bg-info text-white">
                <h5 className="mb-0">
                  <i className="fas fa-info-circle me-2"></i>
                  Información del Sistema
                </h5>
              </div>
              <div className="card-body">
                <div className="row">
                  <div className="col-md-4">
                    <h6 className="text-muted">Usuario Actual:</h6>
                    <p className="mb-0">{userData.nombreCompleto}</p>
                  </div>
                  <div className="col-md-4">
                    <h6 className="text-muted">Tipo de Usuario:</h6>
                    <p className="mb-0">
                      <span className="badge bg-success">{userData.tipoUsuario}</span>
                    </p>
                  </div>
                  <div className="col-md-4">
                    <h6 className="text-muted">Sistema:</h6>
                    <p className="mb-0">Casa Mía Inmobiliaria v1.0</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}