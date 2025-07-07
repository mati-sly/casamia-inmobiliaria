import { doc, getDoc, setDoc } from "firebase/firestore"
import { db } from "./firebase"

export const getUserData = async (uid) => {
  try {
    const ref = doc(db, "usuarios", uid)
    const snapshot = await getDoc(ref)
    
    if (snapshot.exists()) {
      return snapshot.data()
    } else {
      throw new Error("Usuario no encontrado en Firestore")
    }
  } catch (error) {
    console.error("Error al obtener datos del usuario:", error)
    throw error
  }
}

export const saveUserData = async (uid, data) => {
  try {
    const userData = {
      ...data,
      fechaRegistro: new Date().toISOString(),
      activo: true,
      emailVerificado: false
    }
    
    await setDoc(doc(db, "usuarios", uid), userData)
  } catch (error) {
    console.error("Error al guardar usuario:", error)
    throw error
  }
}
