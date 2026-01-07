import * as React from "react"
import { type Table } from "@tanstack/react-table"
import { Input } from "@/components/ui/input"
import { DataTableViewOptions } from "./data-table-view-options"
import { DataTableTopPagination } from "./data-table-top-pagination"
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/react"
import support from "@/routes/support"
import { TicketIcon } from "lucide-react"

interface DataTableToolbarProps<TData> {
  table: Table<TData>
}

export function DataTableToolbar<TData>({
  table,
}: DataTableToolbarProps<TData>) {
  const [search, setSearch] = React.useState("")

  React.useEffect(() => {
    table.getColumn("subject")?.setFilterValue(search || undefined)
  }, [search, table])

  return (
    <div className="flex items-center justify-between">
      <div className="flex flex-1 items-center gap-2">
        <Input
          placeholder="Search by subject..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="h-8 w-[150px] lg:w-[250px]"
        />
      </div>

      <div className="flex items-center gap-2">
        <DataTableViewOptions table={table} />
        <DataTableTopPagination table={table} />
        <Button size="sm" asChild>
          <Link href={support.tickets.create().url}>
            <TicketIcon/>
            Create ticket
          </Link>
        </Button>
      </div>
    </div>
  )
}
