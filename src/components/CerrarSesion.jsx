import { useNavigate } from 'react-router-dom'
import Swal from 'sweetalert2'

export default function CerrarSesion() {
  const navigate = useNavigate()

  const handleLogout = () => {
    Swal.fire({
      icon: 'success',
      title: 'Sesión cerrada',
      text: 'Has cerrado sesión exitosamente',
      timer: 1500,
      showConfirmButton: false
    }).then(() => {
      navigate('/')
    })
  }

  return (
    <button onClick={handleLogout} className="btn btn-danger">
      <i className="fas fa-sign-out-alt me-1"></i>
      Cerrar Sesión
    </button>
  )
}
