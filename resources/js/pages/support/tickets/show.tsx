import { Head, Link } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import axios from 'axios'

interface User {
  id: number
  name: string
  email: string
}

interface CertificateRequest {
  id: number
  external_id: string
  status: string
}

interface Ticket {
  id: number
  category: string
  status: string
  priority: string
  description: string
  created_at: string
  updated_at: string
  user?: User
  certificate_request?: CertificateRequest
}

export default function Show() {
  const { supportTicket } = useParams<{ supportTicket: string }>()
  const [ticket, setTicket] = useState<Ticket | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!supportTicket) return

    axios.get(`/api/support/tickets/${supportTicket}`)
      .then(response => {
        setTicket(response.data)
        setLoading(false)
      })
      .catch(error => {
        console.error('Error fetching ticket:', error)
        setLoading(false)
      })
  }, [supportTicket])

  if (loading) {
    return (
      <>
        <Head title="Loading..." />
        <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
          <p className="text-gray-500">Loading...</p>
        </div>
      </>
    )
  }

  if (!ticket) {
    return (
      <>
        <Head title="Ticket Not Found" />
        <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
          <p className="text-red-500">Ticket not found</p>
          <Link href="/support/tickets" className="mt-4 text-blue-600 hover:text-blue-800">
            ← Back to tickets
          </Link>
        </div>
      </>
    )
  }

  return (
    <>
      <Head title={`Ticket #${ticket.id}`} />

      <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="mb-6">
          <Link
            href="/support/tickets"
            className="text-sm text-blue-600 hover:text-blue-800"
          >
            ← Back to tickets
          </Link>
        </div>

        <div className="overflow-hidden rounded-lg bg-white shadow">
          {/* Header */}
          <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div className="flex items-center justify-between">
              <h1 className="text-2xl font-bold">Ticket #{ticket.id}</h1>
              <div className="flex gap-2">
                <span
                  className={`inline-flex rounded-full px-3 py-1 text-sm font-semibold ${
                    ticket.status === 'open'
                      ? 'bg-green-100 text-green-800'
                      : ticket.status === 'in_progress'
                        ? 'bg-blue-100 text-blue-800'
                        : ticket.status === 'waiting_provider'
                          ? 'bg-yellow-100 text-yellow-800'
                          : 'bg-gray-100 text-gray-800'
                  }`}
                >
                  {ticket.status}
                </span>
                <span
                  className={`inline-flex rounded-full px-3 py-1 text-sm font-semibold ${
                    ticket.priority === 'high'
                      ? 'bg-red-100 text-red-800'
                      : ticket.priority === 'medium'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800'
                  }`}
                >
                  {ticket.priority}
                </span>
              </div>
            </div>
          </div>

          {/* Details */}
          <div className="px-6 py-4">
            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <dt className="text-sm font-medium text-gray-500">Category</dt>
                <dd className="mt-1 text-sm text-gray-900">{ticket.category}</dd>
              </div>
              {ticket.user && (
                <div>
                  <dt className="text-sm font-medium text-gray-500">Created By</dt>
                  <dd className="mt-1 text-sm text-gray-900">
                    {ticket.user.name} ({ticket.user.email})
                  </dd>
                </div>
              )}
              <div>
                <dt className="text-sm font-medium text-gray-500">Created At</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {new Date(ticket.created_at).toLocaleString()}
                </dd>
              </div>
              <div>
                <dt className="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd className="mt-1 text-sm text-gray-900">
                  {new Date(ticket.updated_at).toLocaleString()}
                </dd>
              </div>
              {ticket.certificate_request && (
                <div className="sm:col-span-2">
                  <dt className="text-sm font-medium text-gray-500">Related Certificate</dt>
                  <dd className="mt-1 text-sm text-gray-900">
                    Certificate #{ticket.certificate_request.id} - {ticket.certificate_request.external_id}
                    <span className="ml-2 text-xs text-gray-500">
                      ({ticket.certificate_request.status})
                    </span>
                  </dd>
                </div>
              )}
            </dl>
          </div>

          {/* Description */}
          <div className="border-t border-gray-200 px-6 py-4">
            <h3 className="mb-2 text-sm font-medium text-gray-900">Description</h3>
            <p className="whitespace-pre-wrap text-sm text-gray-700">{ticket.description}</p>
          </div>
        </div>
      </div>
    </>
  )
}