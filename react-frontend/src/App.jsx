import { Link, Navigate, NavLink, Route, Routes } from 'react-router-dom'
import { LogOut } from 'lucide-react'
import { useAuth } from './AuthContext'
import Login from './pages/Login'
import Projects from './pages/Projects'
import ProjectDetails from './pages/ProjectDetails'
import Users from './pages/Users'

function Protected({ children, adminOnly = false }) {
  const { user, isAdmin } = useAuth()
  if (!user) return <Navigate to="/login" replace />
  if (adminOnly && !isAdmin) return <Navigate to="/projects" replace />
  return children
}

const navClass = ({ isActive }) =>
  `rounded-lg px-3 py-1.5 ${isActive ? 'bg-slate-600' : 'hover:bg-slate-700'}`

function Header() {
  const { user, logout, isAdmin } = useAuth()
  if (!user) return null
  return (
    <header className="sticky top-0 z-10 flex h-14 items-center justify-between bg-slate-800 px-6 text-white shadow">
      <div className="flex items-center gap-6">
        <Link to="/projects" className="text-lg font-bold">Task Manager</Link>
        <nav className="flex gap-1 text-sm">
          <NavLink to="/projects" className={navClass}>Projects</NavLink>
          {isAdmin && <NavLink to="/users" className={navClass}>Users</NavLink>}
        </nav>
      </div>
      <div className="flex items-center gap-3 text-sm">
        <span>{user.name}</span>
        <span className="rounded-full bg-slate-600 px-2 py-0.5 text-xs uppercase">{user.role}</span>
        <button
          onClick={logout}
          className="inline-flex items-center gap-1 rounded-lg bg-slate-600 px-3 py-1.5 hover:bg-slate-500"
        >
          <LogOut size={14} /> Logout
        </button>
      </div>
    </header>
  )
}

export default function App() {
  return (
    <>
      <Header />
      <main className="mx-auto max-w-5xl px-4 py-8">
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/projects" element={<Protected><Projects /></Protected>} />
          <Route path="/projects/:id" element={<Protected><ProjectDetails /></Protected>} />
          <Route path="/users" element={<Protected adminOnly><Users /></Protected>} />
          <Route path="*" element={<Navigate to="/projects" replace />} />
        </Routes>
      </main>
    </>
  )
}
