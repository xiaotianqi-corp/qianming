import { columns } from '@/components/app/ticket-table/columns'
import { DataTable } from '@/components/app/ticket-table/data-table'
import { Ticket } from '@/components/app/ticket-table/data/schema'
import { Button } from '@/components/ui/button'
import { Empty, EmptyHeader, EmptyMedia, EmptyTitle, EmptyDescription, EmptyContent } from '@/components/ui/empty'
import AppLayout from '@/layouts/app-layout'
import support from '@/routes/support'
import { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/react'
import { TicketIcon, Eye } from 'lucide-react'

interface Props {
    tickets: Ticket[]
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tickets List - New & My Open Tickets',
        href: support.tickets.index().url,
    },
];

export default function Index({ tickets }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Support Tickets" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto">
                <div className="mx-auto w-full max-w-svw px-4 py-8">
                    {tickets.length === 0 ? (
                        <Empty className="min-h-[60svh] border rounded-xl bg-white">
                            <EmptyHeader>
                                <EmptyMedia variant="icon">
                                    <TicketIcon className="h-10 w-10" />
                                </EmptyMedia>
                                <EmptyTitle>No tickets yet</EmptyTitle>
                                <EmptyDescription>
                                    You haven't created any tickets yet. Get started by creating your first ticket.
                                </EmptyDescription>
                            </EmptyHeader>
                            <EmptyContent>
                                <Button asChild>
                                    <Link href={support.tickets.create().url}>Create first ticket</Link>
                                </Button>
                            </EmptyContent>
                        </Empty>
                    ) : (
                        <div className="overflow-hidden bg-white">
                            <DataTable data={tickets} columns={columns} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    )
}