import { z } from "zod"

export const ticketSchema = z.object({
  id: z.number(),
  ticket_number: z.string(),
  subject: z.string(),
  status: z.string(),
  priority: z.string(),
  category: z.string(),
  description: z.string(),
  sub_category: z.string().nullable().optional(),
  item: z.string().nullable().optional(),
  source: z.string(),
  urgency: z.string(),
  impact: z.string(),
  group: z.string().nullable(),
  agent: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
  closed_at: z.string().nullable().optional(),
  due_date: z.string().nullable().optional(),
  user: z.object({
    id: z.number(),
    name: z.string(),
    email: z.string(),
  }),
  certificate: z.object({
    id: z.number(),
    external_id: z.string(),
    status: z.string(),
  }),
})

export type Ticket = z.infer<typeof ticketSchema>