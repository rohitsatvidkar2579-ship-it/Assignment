import { useEffect, useState } from 'react'
import api, { errorMessage } from '../api'

export default function TaskForm({ projectId, task, onSaved, onCancel }) {
  const [users, setUsers] = useState([])
  const [error, setError] = useState('')
  const [saving, setSaving] = useState(false)
  const [form, setForm] = useState({
    title: task?.title ?? '',
    description: task?.description ?? '',
    priority: task?.priority ?? 'MEDIUM',
    due_date: task?.due_date ?? '',
    assigned_to: task?.assigned_to ?? '',
  })

  useEffect(() => {
    api.get(`/projects/${projectId}`)
      .then(({ data }) => setUsers(data.data.members))
      .catch((err) => setError(errorMessage(err)))
  }, [projectId])

  const set = (field) => (e) => setForm({ ...form, [field]: e.target.value })

  const submit = async (e) => {
    e.preventDefault()
    setError('')
    setSaving(true)
    const payload = { ...form, assigned_to: form.assigned_to || null }
    try {
      if (task) {
        await api.put(`/tasks/${task.id}`, payload)
      } else {
        await api.post(`/projects/${projectId}/tasks`, payload)
      }
      onSaved()
    } catch (err) {
      setError(errorMessage(err))
      setSaving(false)
    }
  }

  return (
    <form onSubmit={submit} className="flex flex-col gap-4">
      {error && <p className="alert-error">{error}</p>}
      <label className="label">
        Title
        <input className="field" value={form.title} onChange={set('title')} required autoFocus />
      </label>
      <label className="label">
        Description
        <textarea className="field" rows="3" value={form.description} onChange={set('description')} />
      </label>
      <div className="grid grid-cols-2 gap-3">
        <label className="label">
          Priority
          <select className="field" value={form.priority} onChange={set('priority')}>
            <option value="LOW">LOW</option>
            <option value="MEDIUM">MEDIUM</option>
            <option value="HIGH">HIGH</option>
          </select>
        </label>
        <label className="label">
          Due date
          <input className="field" type="date" value={form.due_date} onChange={set('due_date')} required />
        </label>
      </div>
      <label className="label">
        Assign to
        <select className="field" value={form.assigned_to} onChange={set('assigned_to')}>
          <option value="">Unassigned</option>
          {users.map((u) => (
            <option key={u.id} value={u.id}>{u.name} ({u.role})</option>
          ))}
        </select>
      </label>
      <div className="flex justify-end gap-2">
        <button type="button" className="btn btn-secondary" onClick={onCancel}>Cancel</button>
        <button className="btn" disabled={saving}>
          {saving ? 'Saving...' : task ? 'Save changes' : 'Create task'}
        </button>
      </div>
    </form>
  )
}
