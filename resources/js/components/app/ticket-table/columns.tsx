"use client"

import { type ColumnDef } from "@tanstack/react-table"
import { type Ticket } from "./data/schema"
import { statuses, priorities } from "./data/data"
import { DataTableColumnHeader } from "./data-table-column-header"
import { Checkbox } from "@/components/ui/checkbox"
import { Link } from "@inertiajs/react"
import support from "@/routes/support"
import { format } from "date-fns"
import { Badge } from "@/components/ui/badge"

export const columns: ColumnDef<Ticket>[] = [
  {
    id: "select",
    header: ({ table }) => (
      <Checkbox
        checked={table.getIsAllPageRowsSelected()}
        onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
        aria-label="Select all"
        className="data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600 data-[state=checked]:text-white dark:data-[state=checked]:border-blue-700 dark:data-[state=checked]:bg-blue-700"
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        checked={row.getIsSelected()}
        onCheckedChange={(value) => row.toggleSelected(!!value)}
        aria-label="Select row"
        className="data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600 data-[state=checked]:text-white dark:data-[state=checked]:border-blue-700 dark:data-[state=checked]:bg-blue-700"
      />
    ),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "subject",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Subject" />
    ),
    filterFn: (row, _columnId, value: string) => {
      if (!value) return true
    
      const search = value.toLowerCase()
    
      const subject = String(row.original.subject ?? "").toLowerCase()
      const ticketNumber = String(row.original.ticket_number ?? "").toLowerCase()
    
      return (
        subject.includes(search) ||
        ticketNumber.includes(search)
      )
    },    
    cell: ({ row }) => {
      const ticket = row.original
      const subject = row.getValue("subject") as string
      const formattedSubject =
        subject.charAt(0).toUpperCase() + subject.slice(1)

      return (
        <div className="flex flex-col gap-0.5">
          <Link
            href={support.tickets.show(ticket.ticket_number).url}
            className="font-bold hover:text-blue-800 flex items-center gap-2 group"
          >
            <span className="max-w-[300px] truncate text-[14px] font-semibold group-hover:underline">
              {formattedSubject}
            </span>
            <span className="text-[10px] font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-500 italic">
              #{ticket.ticket_number}
            </span>
          </Link>
        </div>
      )
    },
  },
  {
    id: "requester",
    accessorFn: (row) => row.user.name,
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Requester" />
    ),
    cell: ({ row }) => (
      <div className="flex flex-col">
        <span className="text-[13px] capitalize text-slate-600 font-medium">
          {row.original.user.name}
        </span>
        <span className="text-[11px] text-muted-foreground">
          {row.original.user.email}
        </span>
      </div>
    ),
  },
  {
    accessorKey: "status",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Status" />
    ),
    cell: ({ row }) => {
      const status = statuses.find(
        (s) => s.value === row.getValue("status")
      )
  
      if (!status) return null
  
      return (
        <Badge
          variant="outline"
          className={`flex items-center gap-1.5 text-[12px] w-full rounded-full font-medium ${status.className}`}
        >
          {status.icon && <status.icon className="h-3.5 w-3.5" />}
          {status.label}
        </Badge>
      )
    },
    filterFn: (row, id, value: string[]) =>
      value?.length ? value.includes(row.getValue(id)) : true,
  },  
  {
    accessorKey: "priority",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Priority" />
    ),
    cell: ({ row }) => {
      const priority = priorities.find(
        (p) => p.value === row.getValue("priority")
      )
  
      if (!priority) return null
  
      return (
        <Badge
          variant="outline"
          className={`flex items-center gap-1.5 text-[12px] w-full rounded-full font-medium ${priority.className}`}
        >
          {priority.icon && <priority.icon className="h-3.5 w-3.5" />}
          {priority.label}
        </Badge>
      )
    },
    filterFn: (row, id, value: string[]) =>
      value?.length ? value.includes(row.getValue(id)) : true,
  },
  {
    accessorKey: "agent",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Assigned to" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("agent") || "Unassigned"}
      </span>
    ),
  },
  {
    accessorKey: "group",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Department" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("group") || "--"}
      </span>
    ),
  },
  {
    accessorKey: "source",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Source" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("source")}
      </span>
    ),
  },
  {
    accessorKey: "category",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Category" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("category")}
      </span>
    ),
    filterFn: (row, id, value: string[]) => {
      return value?.length
        ? value.includes(row.getValue(id))
        : true
    }
  },
  {
    accessorKey: "impact",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Impact" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("impact")}
      </span>
    ),
  },
  {
    accessorKey: "urgency",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Urgency" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] capitalize text-slate-600 font-medium">
        {row.getValue("urgency")}
      </span>
    ),
  },
  {
    accessorKey: "created_at",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Created Date" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] text-slate-600 font-medium">
        {format(new Date(row.getValue("created_at")), "PPp")}
      </span>
    ),
  },
  {
    accessorKey: "updated_at",
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Last Modified Date" />
    ),
    cell: ({ row }) => (
      <span className="text-[13px] text-slate-600 font-medium">
        {format(new Date(row.getValue("updated_at")), "PPp")}
      </span>
    ),
  },
]
