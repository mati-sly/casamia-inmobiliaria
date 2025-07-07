import { useAuth } from "../context/AuthContext"

export default function Home() {
  const { user, userData } = useAuth()

  return (
    <div className="container mt-5">
      <div className="row justify-content-center">
        <div className="col-md-8">
          <div className="card">
            <div className="card-header bg-success text-white">
              <h2 className="mb-0">¡Bienvenido!</h2>
            </div>
            <div className="card-body">
              <h4>Hola {userData?.nombres || user?.email}</h4>
              <p>Has iniciado sesión correctamente en el sistema.</p>
              <p><strong>Tipo de usuario:</strong> {userData?.tipo || 'No definido'}</p>
              <p><strong>Email:</strong> {user?.email}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
