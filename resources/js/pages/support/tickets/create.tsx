import { Head } from '@inertiajs/react'
import { FormEventHandler, useEffect, useState } from 'react'
import axios from 'axios'
import { router } from '@inertiajs/react'

interface CertificateRequest {
  id: number
  external_id: string
}

export default function Create() {
  const [certificateRequests, setCertificateRequests] = useState<CertificateRequest[]>([])
  const [loading, setLoading] = useState(true)
  const [processing, setProcessing] = useState(false)
  
  const [formData, setFormData] = useState({
    category: '',
    priority: 'medium',
    description: '',
    certificate_request_id: '',
  })
  
  const [errors, setErrors] = useState<Record<string, string>>({})

  const categories = ['identity', 'payment', 'issuance', 'technical']
  const priorities = ['low', 'medium', 'high']

  useEffect(() => {
    // Fetch certificate requests from backend
    axios.get('/api/public/orders')
      .then(response => {
        // Extract certificate requests from orders
        const certs: CertificateRequest[] = []
        response.data.forEach((order: any) => {
          order.items?.forEach((item: any) => {
            if (item.certificate_request) {
              certs.push(item.certificate_request)
            }
          })
        })
        setCertificateRequests(certs)
        setLoading(false)
      })
      .catch(error => {
        console.error('Error fetching certificate requests:', error)
        setLoading(false)
      })
  }, [])

  const submit: FormEventHandler = (e) => {
    e.preventDefault()
    setProcessing(true)
    setErrors({})

    axios.post('/api/support/tickets', formData)
      .then(() => {
        router.visit('/support/tickets')
      })
      .catch(error => {
        if (error.response?.data?.errors) {
          setErrors(error.response.data.errors)
        }
        setProcessing(false)
      })
  }

  if (loading) {
    return (
      <>
        <Head title="Create Support Ticket" />
        <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
          <p className="text-gray-500">Loading...</p>
        </div>
      </>
    )
  }

  return (
    <>
      <Head title="Create Support Ticket" />

      <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 className="mb-6 text-3xl font-bold">Create Support Ticket</h1>

        <form onSubmit={submit} className="space-y-6 rounded-lg bg-white p-6 shadow">
          {/* Category */}
          <div>
            <label htmlFor="category" className="block text-sm font-medium text-gray-700">
              Category
            </label>
            <select
              id="category"
              value={formData.category}
              onChange={(e) => setFormData({ ...formData, category: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Select a category</option>
              {categories.map((cat) => (
                <option key={cat} value={cat}>
                  {cat}
                </option>
              ))}
            </select>
            {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
          </div>

          {/* Priority */}
          <div>
            <label htmlFor="priority" className="block text-sm font-medium text-gray-700">
              Priority
            </label>
            <select
              id="priority"
              value={formData.priority}
              onChange={(e) => setFormData({ ...formData, priority: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              {priorities.map((pri) => (
                <option key={pri} value={pri}>
                  {pri}
                </option>
              ))}
            </select>
            {errors.priority && <p className="mt-1 text-sm text-red-600">{errors.priority}</p>}
          </div>

          {/* Certificate Request (optional) */}
          <div>
            <label
              htmlFor="certificate_request_id"
              className="block text-sm font-medium text-gray-700"
            >
              Related Certificate (Optional)
            </label>
            <select
              id="certificate_request_id"
              value={formData.certificate_request_id}
              onChange={(e) => setFormData({ ...formData, certificate_request_id: e.target.value })}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">None</option>
              {certificateRequests.map((cert) => (
                <option key={cert.id} value={cert.id}>
                  Certificate #{cert.id} - {cert.external_id}
                </option>
              ))}
            </select>
            {errors.certificate_request_id && (
              <p className="mt-1 text-sm text-red-600">{errors.certificate_request_id}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label htmlFor="description" className="block text-sm font-medium text-gray-700">
              Description
            </label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows={5}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Describe your issue..."
            />
            {errors.description && (
              <p className="mt-1 text-sm text-red-600">{errors.description}</p>
            )}
          </div>

          {/* Buttons */}
          <div className="flex gap-3">
            <button
              type="submit"
              disabled={processing}
              className="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {processing ? 'Creating...' : 'Create Ticket'}
            </button>
            <a
              href="/support/tickets"
              className="rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </a>
          </div>
        </form>
      </div>
    </>
  )
}