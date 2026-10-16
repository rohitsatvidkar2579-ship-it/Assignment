import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../AuthContext'
import { errorMessage } from '../api'

export default function Login() {
  const { user, login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  if (user) return <Navigate to="/projects" replace />

  const submit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(email, password)
      navigate('/projects')
    } catch (err) {
      setError(errorMessage(err))
    } finally {
      setLoading(false)
    }
  }

  return (
    <form
      onSubmit={submit}
      className="mx-auto mt-16 flex max-w-sm flex-col gap-4 rounded-xl bg-white p-8 shadow"
    >
      <h2 className="text-2xl font-bold">Login</h2>
      {error && <p className="alert-error">{error}</p>}
      <label className="label">
        Email
        <input className="field" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
      </label>
      <label className="label">
        Password
        <input className="field" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
      </label>
      <button className="btn" disabled={loading}>{loading ? 'Signing in...' : 'Login'}</button>
    </form>
  )
}
