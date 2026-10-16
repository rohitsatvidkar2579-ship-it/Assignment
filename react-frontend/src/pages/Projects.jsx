import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Eye, Plus } from 'lucide-react'
import api, { errorMessage } from '../api'
import { useAuth } from '../AuthContext'
import Modal from '../components/Modal'
import MultiSelect from '../components/MultiSelect'

const EMPTY = { name: '', description: '', user_ids: [] }

export default function Projects() {
  const { isAdmin } = useAuth()
  const [projects, setProjects] = useState([])
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [formError, setFormError] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form, setForm] = useState(EMPTY)

  const load = useCallback(async () => {
    try {
      const { data } = await api.get('/projects')
      setProjects(data.data)
    } catch (err) {
      setError(errorMessage(err))
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    load()
  }, [load])

  const openForm = async () => {
    setForm(EMPTY)
    setFormError('')
    setShowForm(true)
    try {
      const { data } = await api.get('/users')
      setUsers(data.data)
    } catch (err) {
      setFormError(errorMessage(err))
    }
  }

  const create = async (e) => {
    e.preventDefault()
    setFormError('')
    setSaving(true)
    try {
      await api.post('/projects', form)
      setShowForm(false)
      load()
    } catch (err) {
      setFormError(errorMessage(err))
    }
    setSaving(false)
  }

  return (
    <>
      <div className="mb-5 flex items-center justify-between">
        <h2 className="text-2xl font-bold">Projects</h2>
        {isAdmin && (
          <button className="btn" onClick={openForm}><Plus size={16} /> Add project</button>
        )}
      </div>

      {error && <p className="alert-error mb-4">{error}</p>}

      {loading ? (
        <p>Loading...</p>
      ) : projects.length === 0 ? (
        <p className="text-slate-500">No projects found.</p>
      ) : (
        <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
          <table className="w-full text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase text-slate-500">
              <tr>
                <th className="px-4 py-3">Project</th>
                <th className="px-4 py-3">Description</th>
                <th className="px-4 py-3">Members</th>
                <th className="px-4 py-3">Tasks</th>
                <th className="px-4 py-3">Created by</th>
                <th className="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {projects.map((p) => (
                <tr key={p.id} className="hover:bg-slate-50">
                  <td className="px-4 py-3 font-semibold">{p.name}</td>
                  <td className="px-4 py-3 text-slate-600">{p.description || '—'}</td>
                  <td className="px-4 py-3">
                    <div className="flex flex-wrap gap-1">
                      {p.members?.length
                        ? p.members.map((m) => (
                            <span key={m.id} className="rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-800">
                              {m.name}
                            </span>
                          ))
                        : '—'}
                    </div>
                  </td>
                  <td className="px-4 py-3">{p.tasks_count}</td>
                  <td className="px-4 py-3">{p.creator?.name}</td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end">
                      <Link
                        to={`/projects/${p.id}`}
                        title="View project"
                        className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600"
                      >
                        <Eye size={16} />
                      </Link>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {showForm && (
        <Modal title="Add project" onClose={() => setShowForm(false)}>
          <form onSubmit={create} className="flex flex-col gap-4">
            {formError && <p className="alert-error">{formError}</p>}
            <label className="label">
              Project name
              <input
                className="field"
                value={form.name}
                onChange={(e) => setForm({ ...form, name: e.target.value })}
                required
                autoFocus
              />
            </label>
            <label className="label">
              Description
              <textarea
                className="field"
                rows="3"
                value={form.description}
                onChange={(e) => setForm({ ...form, description: e.target.value })}
              />
            </label>
            <div className="label">
              Members
              <MultiSelect
                placeholder="Select users"
                options={users.map((u) => ({ value: u.id, label: `${u.name} (${u.role})` }))}
                value={form.user_ids}
                onChange={(user_ids) => setForm({ ...form, user_ids })}
              />
            </div>
            <div className="flex justify-end gap-2">
              <button type="button" className="btn btn-secondary" onClick={() => setShowForm(false)}>Cancel</button>
              <button className="btn" disabled={saving}>{saving ? 'Saving...' : 'Add project'}</button>
            </div>
          </form>
        </Modal>
      )}
    </>
  )
}
