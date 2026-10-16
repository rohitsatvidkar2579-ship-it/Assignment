import { useCallback, useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, ArrowRightLeft, Pencil, Plus, Trash2 } from 'lucide-react'
import api, { errorMessage } from '../api'
import { useAuth } from '../AuthContext'
import Modal from '../components/Modal'
import TaskForm from './TaskForm'

const STATUSES = ['TODO', 'IN_PROGRESS', 'DONE', 'OVERDUE', 'CLOSED']

const PRIORITY_STYLES = {
  LOW: 'bg-green-100 text-green-800',
  MEDIUM: 'bg-amber-100 text-amber-800',
  HIGH: 'bg-red-100 text-red-800',
}

const STATUS_STYLES = {
  TODO: 'bg-slate-100 text-slate-700',
  IN_PROGRESS: 'bg-blue-100 text-blue-800',
  DONE: 'bg-green-100 text-green-800',
  OVERDUE: 'bg-red-100 text-red-800',
  CLOSED: 'bg-slate-200 text-slate-500',
}

export default function ProjectDetails() {
  const { id } = useParams()
  const { isAdmin } = useAuth()
  const [project, setProject] = useState(null)
  const [error, setError] = useState('')
  const [modal, setModal] = useState(null)
  const [statusTask, setStatusTask] = useState(null)
  const [nextStatus, setNextStatus] = useState('')
  const [saving, setSaving] = useState(false)

  const load = useCallback(async () => {
    try {
      const { data } = await api.get(`/projects/${id}`)
      setProject(data.data)
    } catch (err) {
      setError(errorMessage(err))
    }
  }, [id])

  useEffect(() => {
    load()
  }, [load])

  const openStatus = (task) => {
    setStatusTask(task)
    setNextStatus(task.status)
  }

  const updateStatus = async () => {
    setError('')
    setSaving(true)
    try {
      await api.patch(`/tasks/${statusTask.id}/status`, { status: nextStatus })
    } catch (err) {
      setError(errorMessage(err))
    }
    setSaving(false)
    setStatusTask(null)
    load()
  }

  const remove = async (task) => {
    if (!window.confirm(`Delete task "${task.title}"?`)) return
    setError('')
    try {
      await api.delete(`/tasks/${task.id}`)
    } catch (err) {
      setError(errorMessage(err))
    }
    load()
  }

  const closeModal = () => setModal(null)

  if (!project) return error ? <p className="alert-error">{error}</p> : <p>Loading...</p>

  return (
    <>
      <Link to="/projects" className="mb-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
        <ArrowLeft size={14} /> Back to projects
      </Link>

      <div className="rounded-xl bg-white p-6 shadow-sm">
        <div className="mb-4 flex items-start justify-between gap-3">
          <div>
            <h2 className="text-2xl font-bold">{project.name}</h2>
            <p className="mt-1 text-sm text-slate-600">{project.description}</p>
          </div>
          {isAdmin && (
            <button className="btn shrink-0" onClick={() => setModal({ task: null })}>
              <Plus size={16} /> Create task
            </button>
          )}
        </div>

        {error && <p className="alert-error mb-4">{error}</p>}

        {project.tasks.length === 0 ? (
          <p className="rounded-lg border-2 border-dashed border-slate-200 py-10 text-center text-slate-500">
            No tasks to show.
          </p>
        ) : (
          <div className="overflow-x-auto rounded-lg border border-slate-200">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                  <th className="px-4 py-3">Title</th>
                  <th className="px-4 py-3">Assignee</th>
                  <th className="px-4 py-3">Priority</th>
                  <th className="px-4 py-3">Due date</th>
                  <th className="px-4 py-3">Status</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {project.tasks.map((t) => {
                  return (
                    <tr key={t.id} className="align-top">
                      <td className="px-4 py-3">
                        <div className="font-semibold">{t.title}</div>
                        <div className="text-xs text-slate-500">{t.description}</div>
                      </td>
                      <td className="px-4 py-3">{t.assignee?.name || '—'}</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${PRIORITY_STYLES[t.priority]}`}>
                          {t.priority}
                        </span>
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap">{t.due_date}</td>
                      <td className="px-4 py-3">
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${STATUS_STYLES[t.status]}`}>
                          {t.status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex justify-end gap-1">
                          {t.status !== 'CLOSED' && (
                            <button
                              title="Change status"
                              className="rounded-lg p-2 text-slate-500 hover:bg-green-50 hover:text-green-600"
                              onClick={() => openStatus(t)}
                            >
                              <ArrowRightLeft size={16} />
                            </button>
                          )}
                          {isAdmin && (
                            <>
                              <button
                                title="Edit task"
                                className="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600"
                                onClick={() => setModal({ task: t })}
                              >
                                <Pencil size={16} />
                              </button>
                              <button
                                title="Delete task"
                                className="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-600"
                                onClick={() => remove(t)}
                              >
                                <Trash2 size={16} />
                              </button>
                            </>
                          )}
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {statusTask && (
        <Modal title="Change status" onClose={() => setStatusTask(null)}>
          <p className="mb-3 text-sm text-slate-600">{statusTask.title}</p>
          <select className="field" value={nextStatus} onChange={(e) => setNextStatus(e.target.value)}>
            {STATUSES.map((s) => (
              <option key={s} value={s}>{s}</option>
            ))}
          </select>
          <div className="mt-5 flex justify-end gap-2">
            <button className="btn btn-secondary" onClick={() => setStatusTask(null)}>Cancel</button>
            <button
              className="btn"
              disabled={saving || nextStatus === statusTask.status}
              onClick={updateStatus}
            >
              {saving ? 'Updating...' : 'Update'}
            </button>
          </div>
        </Modal>
      )}

      {modal && (
        <Modal title={modal.task ? 'Edit task' : 'Create task'} onClose={closeModal}>
          <TaskForm
            projectId={project.id}
            task={modal.task}
            onCancel={closeModal}
            onSaved={() => {
              closeModal()
              load()
            }}
          />
        </Modal>
      )}
    </>
  )
}
