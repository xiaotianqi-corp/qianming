"use client"

import * as React from "react"
import { type Table } from "@tanstack/react-table"
import { Settings2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

export function DataTableViewOptions<TData>({
  table,
}: {
  table: Table<TData>
}) {
  // 🔑 Estado React LOCAL (igual al ejemplo de Radix)
  const [visibility, setVisibility] = React.useState(
    table.getState().columnVisibility
  )

  // 🔄 Sincroniza cuando TanStack cambia
  React.useEffect(() => {
    setVisibility(table.getState().columnVisibility)
  }, [table.getState().columnVisibility])

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="outline"
          size="sm"
          className="ml-auto hidden h-8 lg:flex"
        >
          <Settings2 className="mr-2 h-4 w-4" />
          View
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end">
        {table
          .getAllColumns()
          .filter((column) => column.getCanHide())
          .map((column) => {
            const checked = visibility[column.id] ?? true

            return (
              <DropdownMenuCheckboxItem
                key={column.id}
                checked={checked}
                onCheckedChange={(value) => {
                  const next = {
                    ...visibility,
                    [column.id]: Boolean(value),
                  }
                  setVisibility(next)
                  table.setColumnVisibility(next)
                }}
                className="capitalize"
              >
                {column.id.replace(/_/g, " ")}
              </DropdownMenuCheckboxItem>
            )
          })}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
