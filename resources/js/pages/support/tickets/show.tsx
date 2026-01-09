import { Ticket } from '@/components/app/ticket-table/data/schema'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectGroup, SelectLabel, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import AppLayout from '@/layouts/app-layout'
import { getInitials } from '@/lib/utils'
import support from '@/routes/support'
import { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/react'
import { format, formatDate } from 'date-fns'
import { ChevronDown, ChevronLeft, ChevronRight, ForwardIcon, MoreVertical, NotepadTextIcon, Plus, ReplyIcon, Share2, Sparkle, Star, TicketIcon, User } from 'lucide-react'
import { Label } from 'recharts'

interface Props {
    ticket: Ticket
}

export default function Show({ ticket }: Props) {

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Tickets', href: support.tickets.index().url },
        {
            title: `#${ticket.ticket_number}`,
            href: support.tickets.show(ticket.ticket_number).url,
        },
    ]

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            Open: "bg-green-500",
            Pending: "bg-yellow-500",
            Resolved: "bg-blue-500",
            Closed: "bg-gray-500",
        }
        return colors[status] || "bg-gray-500"
    }

    const getPriorityColor = (priority: string) => {
        const colors: Record<string, string> = {
            Low: "bg-blue-500",
            Medium: "bg-yellow-500",
            High: "bg-orange-500",
            Urgent: "bg-red-500",
        }
        return colors[priority] || "bg-gray-500"
    }

    const formatDate = (dateString: string | null | undefined) => {
        if (!dateString) return "Not set"
        try {
            return format(new Date(dateString), "EEE, d MMM yyyy, h:mm a")
        } catch {
            return dateString
        }
    }

    const getInitials = (name: string) => {
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2)
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Ticket #${ticket.ticket_number}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto">
                <div className="flex items-center justify-between border-b bg-background px-6 py-4">
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Badge className="bg-green-100 text-green-700 hover:bg-green-100">New</Badge>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="ghost" size="icon">
                            <Star className="h-4 w-4" />
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost">
                                    Share <Share2 className="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem>Copy link</DropdownMenuItem>
                                <DropdownMenuItem>Share via email</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button variant="ghost">Edit</Button>
                        <Button variant="ghost">Reply</Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost">
                                    Associate <ChevronDown className="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem>Link to ticket</DropdownMenuItem>
                                <DropdownMenuItem>Link to problem</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Button variant="ghost">Close</Button>
                        <Button variant="ghost" size="icon">
                            <MoreVertical className="h-4 w-4" />
                        </Button>
                        <div className="flex gap-1">
                            <Button variant="ghost" size="icon">
                                <ChevronLeft className="h-4 w-4" />
                            </Button>
                            <Button variant="ghost" size="icon">
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div className="flex flex-1 overflow-hidden">
                    {/* Main Content */}
                    <div className="flex-1 overflow-y-auto">
                        <div className="p-6">
                            {/* Ticket Header */}
                            <div className="mb-6 flex items-center gap-4">
                                <Avatar className="h-12 w-12 rounded-md">
                                    <AvatarFallback className="bg-fill-semantic-info-mild text-white rounded-md">
                                        <TicketIcon />
                                    </AvatarFallback>
                                </Avatar>
                                <div className="flex-1">
                                    <h1 className="text-2xl font-semibold">{ticket.subject}</h1>
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span className="font-medium">{ticket.user.name}</span>
                                        <span>reported</span>
                                        <span>{formatDate(ticket.created_at)}</span>
                                        <span>via {ticket.source}</span>
                                    </div>
                                    <div className="mt-1 text-sm text-muted-foreground">Request for: {ticket.user.name}</div>
                                </div>
                            </div>

                            {/* Description */}
                            <Card className="bg-overlay-semantic-info border-boundary-semantic-info-mild px-0 gap-2 mb-6">
                                <CardHeader>
                                    <CardTitle>Description</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="whitespace-pre-wrap text-sm">{ticket.description}</div>
                                </CardContent>
                            </Card>

                            {/* Conversations */}
                            <div>
                                <h3 className="mb-3 text-sm font-semibold">Conversations</h3>
                                <Card className="bg-neutral-50 py-2">
                                    <CardContent>
                                        <div className="flex gap-2">
                                            <Select>
                                                <SelectTrigger 
                                                className="cursor-pointer size-8 data-[placeholder]:text-text-secondary font-bold bg-background w-[180px] [&_svg:not([class*='text-'])]:opacity-100 [&_svg:not([class*='text-'])]:text-text-secondary shadow-none focus-visible:ring-[0px]"
                                                >
                                                        <SelectValue  placeholder="Reply using AI" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value="acknowledge">Acknowledge</SelectItem>
                                                        <SelectItem value="request-info">Request more information</SelectItem>
                                                    </SelectGroup>
                                                    <SelectGroup>
                                                        <SelectLabel>Follow up</SelectLabel>
                                                        <SelectItem value="confirm-close">Request confirmation to close</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>

                                            <Button variant="outline" size="sm" className='cursor-pointer'>
                                            <ReplyIcon className='text-icon-semantic-info'/>
                                                Reply
                                            </Button>
                                            <Button variant="outline" size="sm" className='cursor-pointer'>
                                                <ForwardIcon className='text-icon-semantic-info'/>
                                                Forward
                                            </Button>
                                            <Button variant="outline" size="sm" className='cursor-pointer'>
                                                <NotepadTextIcon className='text-icon-semantic-info'/>
                                                Add note
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>

                    {/* Right Sidebar */}
                    <div className="w-80 border-l bg-muted/30 p-6">
                        <div className="space-y-6">
                            {/* Status Badge */}
                            <div className="rounded-lg border bg-card p-4">
                                <div className="flex items-center justify-center">
                                    <Badge
                                        variant="outline"
                                        className="text-lg font-semibold"
                                        style={{ borderColor: getStatusColor(ticket.status) }}
                                    >
                                        <span className={`mr-2 h-2 w-2 rounded-full ${getStatusColor(ticket.status)}`} />
                                        {ticket.status}
                                    </Badge>
                                </div>
                            </div>

                            {/* Ticket Info */}
                            <div className="space-y-3">
                                <div className="flex items-start justify-between">
                                    <span className="text-sm text-muted-foreground">Priority</span>
                                    <div className="flex items-center gap-1">
                                        <span className={`h-2 w-2 rounded-full ${getPriorityColor(ticket.priority)}`} />
                                        <span className="text-sm font-medium">{ticket.priority}</span>
                                    </div>
                                </div>
                                <Separator />
                                <div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">First response due</span>
                                    </div>
                                    <div className="mt-1 text-sm">{formatDate(ticket.created_at)}</div>
                                </div>
                                <Separator />
                                <div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-muted-foreground">Resolution due</span>
                                    </div>
                                    <div className="mt-1 text-sm">{formatDate(ticket.due_date)}</div>
                                </div>
                                <Separator />
                                <div>
                                    <span className="text-sm text-muted-foreground">Approval</span>
                                    <div className="mt-1 text-sm">Not requested</div>
                                </div>
                            </div>

                            {/* Requester Information */}
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-sm">
                                        <User className="h-4 w-4" />
                                        Requester information
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center gap-3">
                                        <Avatar className="h-10 w-10">
                                            <AvatarImage src="/placeholder.svg" />
                                            <AvatarFallback className="bg-blue-500 text-white">{getInitials(ticket.user.name)}</AvatarFallback>
                                        </Avatar>
                                        <div className="flex-1">
                                            <div className="font-medium text-blue-600">{ticket.user.name}</div>
                                            <Button variant="link" className="h-auto p-0 text-xs text-muted-foreground">
                                                View more details
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Properties */}
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-sm">Properties</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-xs">
                                                Priority <span className="text-red-500">*</span>
                                            </Label>
                                            <Select defaultValue={ticket.priority.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="low">Low</SelectItem>
                                                    <SelectItem value="medium">Medium</SelectItem>
                                                    <SelectItem value="high">High</SelectItem>
                                                    <SelectItem value="urgent">Urgent</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs">
                                                Status <span className="text-red-500">*</span>
                                            </Label>
                                            <Select defaultValue={ticket.status.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="open">Open</SelectItem>
                                                    <SelectItem value="pending">Pending</SelectItem>
                                                    <SelectItem value="resolved">Resolved</SelectItem>
                                                    <SelectItem value="closed">Closed</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-xs">Source</Label>
                                            <Select defaultValue={ticket.source.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="portal">Portal</SelectItem>
                                                    <SelectItem value="email">Email</SelectItem>
                                                    <SelectItem value="phone">Phone</SelectItem>
                                                    <SelectItem value="chat">Chat</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs">Type</Label>
                                            <Select defaultValue={ticket.category.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="incident">Incident</SelectItem>
                                                    <SelectItem value="request">Request</SelectItem>
                                                    <SelectItem value="problem">Problem</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label className="text-xs">Urgency</Label>
                                            <Select defaultValue={ticket.urgency.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="low">Low</SelectItem>
                                                    <SelectItem value="medium">Medium</SelectItem>
                                                    <SelectItem value="high">High</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs">Impact</Label>
                                            <Select defaultValue={ticket.impact.toLowerCase()}>
                                                <SelectTrigger className="h-9">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="low">Low</SelectItem>
                                                    <SelectItem value="medium">Medium</SelectItem>
                                                    <SelectItem value="high">High</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label className="text-xs">Group</Label>
                                        <Select defaultValue={ticket.group || "unassigned"}>
                                            <SelectTrigger className="h-9">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="unassigned">--</SelectItem>
                                                <SelectItem value="support">Support Team</SelectItem>
                                                <SelectItem value="technical">Technical Team</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <Button className="w-full bg-blue-600 hover:bg-blue-700">Update</Button>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}