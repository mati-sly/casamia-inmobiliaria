import { Routes, Route } from 'react-router-dom'
import Login from '../pages/Login'
import Registrarme from '../pages/Registrarme'
import Recuperar from '../pages/Recuperar'
import Home from '../pages/Home'
import Dashboard from '../pages/Dashboard'
import ProtectedRoute from './ProtectedRoute'

export default function AppRouter() {
  return (
    <Routes>
      <Route path="/" element={<Login />} />
      <Route path="/login" element={<Login />} />
      <Route path="/registrarme" element={<Registrarme />} />
      <Route path="/recuperar" element={<Recuperar />} />
      <Route path="/dashboard" element={<Dashboard />} />
      <Route path="/home" element={
        <ProtectedRoute>
          <Home />
        </ProtectedRoute>
      } />
    </Routes>
  )
}
