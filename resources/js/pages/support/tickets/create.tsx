import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldLabel, FieldLegend, FieldSeparator, FieldSet } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import support from '@/routes/support';
import { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Check, Copy } from 'lucide-react';
import { useState } from 'react';

interface EnumOption {
  value: string;
  label: string;
}

interface CertificateRequest {
  id: number;
  external_id: string;
}

interface Props {
  certificateRequests: CertificateRequest[];
  categories: { value: string; label: string; description?: string }[];
  statuses: EnumOption[];
  priorities: EnumOption[];
  sources: EnumOption[];
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tickets', href: support.tickets.index().url },
  { title: 'Create ticket', href: support.tickets.create().url },
];

const generatePreviewTicketNumber = () => {
  const now = new Date();
  const yy = now.getFullYear().toString().slice(-2);
  const mm = (now.getMonth() + 1).toString().padStart(2, '0');
  const dd = now.getDate().toString().padStart(2, '0');
  const datePart = `${yy}${mm}${dd}`;
  const randomPart = Math.random().toString(36).substring(2, 6).toUpperCase();

  return `TKT-${datePart}-${randomPart}`;
};

export default function Create({
  certificateRequests,
  categories,
  statuses,
  priorities,
  sources
}: Props) {
  const [copied, setCopied] = useState(false);
  const [previewNumber] = useState(generatePreviewTicketNumber());
  const { data, setData, post, processing, errors } = useForm({
    ticket_number: previewNumber,
    requester: '',
    subject: '',
    source: sources[0]?.value || '',
    status: statuses[0]?.value || '',
    priority: priorities[0]?.value || '',
    category: '',
    urgency: '',
    impact: '',
    group: '',
    agent: '',
    description: '',
    certificate_request_id: '',
  });

  const copyToClipboard = () => {
    navigator.clipboard.writeText(data.ticket_number);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post(support.tickets.store().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Support Ticket" />
      <div className="flex h-dvh flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="px-4 py-4">
          <form onSubmit={submit} className="space-y-6 bg-white p-6 rounded-lg border shadow-sm">
            <FieldGroup>
              <FieldGroup>
                <div className="flex justify-between items-start">
                  <div>
                    <FieldLegend className="text-2xl">New Support Request</FieldLegend>
                    <FieldDescription>Fill out the form below to open a new case.</FieldDescription>
                  </div>

                  <div className="flex flex-col items-end gap-2">
                    <span className="text-[10px] font-bold uppercase text-gray-400 tracking-widest">Case Identifier</span>
                    <div className="flex items-center gap-1 bg-gray-50 border rounded-md p-1 pl-3">
                      <code className="text-sm font-mono font-bold text-blue-600">
                        {data.ticket_number}
                      </code>
                      <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 text-gray-400 hover:text-blue-600"
                        onClick={copyToClipboard}
                      >
                        {copied ? <Check className="h-4 w-4 text-green-500" /> : <Copy className="h-4 w-4" />}
                      </Button>
                    </div>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <Field>
                    <FieldLabel>Requester</FieldLabel>
                    <Input
                      placeholder="Search"
                      value={data.requester}
                      onChange={(e) => setData('requester', e.target.value)}
                      required
                    />
                  </Field>
                  <Field>
                    <FieldLabel>Subject</FieldLabel>
                    <Input
                      placeholder="Brief summary of the issue"
                      value={data.subject}
                      onChange={(e) => setData('subject', e.target.value)}
                      required
                    />
                  </Field>
                </div>
                <FieldSeparator />
                <div className="grid grid-cols-2 gap-4">
                  <Field>
                    <FieldLabel>Source</FieldLabel>
                    <Select
                      value={data.source}
                      onValueChange={(v) => setData('source', v)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Source" />
                      </SelectTrigger>
                      <SelectContent>
                        {sources.map((src) => (
                          <SelectItem key={src.value} value={src.value}>
                            {src.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.source && <p className="text-red-500 text-xs mt-1">{errors.source}</p>}
                  </Field>
                  <Field>
                    <FieldLabel>Status *</FieldLabel>
                    <Select
                      value={data.status}
                      onValueChange={(v) => setData('status', v)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Status" />
                      </SelectTrigger>
                      <SelectContent>
                        {statuses.map((s) => (
                          <SelectItem key={s.value} value={s.value}>
                            {s.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.status && <p className="text-red-500 text-xs mt-1">{errors.status}</p>}
                  </Field>
                </div>
                <div className="grid grid-cols-3 gap-4">
                  <Field>
                    <FieldLabel>Urgency</FieldLabel>
                    <Select defaultValue="low" value={data.urgency} onValueChange={(v) => setData('urgency', v)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Urgency" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="medium">Medium</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                      </SelectContent>
                    </Select>
                  </Field>
                  <Field>
                    <FieldLabel>Impact</FieldLabel>
                    <Select defaultValue="low" value={data.impact} onValueChange={(v) => setData('impact', v)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Impact" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="low">Low</SelectItem>
                        <SelectItem value="medium">Medium</SelectItem>
                        <SelectItem value="high">High</SelectItem>
                      </SelectContent>
                    </Select>
                  </Field>
                  <Field>
                    <FieldLabel htmlFor="priority">Priority *</FieldLabel>
                    <Select
                      value={data.priority}
                      onValueChange={(v) => setData('priority', v)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select priority" id="priority" />
                      </SelectTrigger>
                      <SelectContent>
                        {priorities.map((p) => (
                          <SelectItem key={p.value} value={p.value}>
                            {p.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.priority && <p className="text-red-500 text-xs mt-1">{errors.priority}</p>}
                  </Field>
                </div>
              </FieldGroup>

              <FieldSeparator />

              <FieldSet>
                <div className="grid grid-cols-3 gap-4">
                  <Field>
                    <FieldLabel >Group</FieldLabel>
                    <Select defaultValue=" " value={data.group} onValueChange={(v) => setData('group', v)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Group" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value=" ">--</SelectItem>
                        <SelectItem value="applications">Applications Management Team</SelectItem>
                        <SelectItem value="equipments">Equipments Management Team</SelectItem>
                        <SelectItem value="request">Request Management Team</SelectItem>
                      </SelectContent>
                    </Select>
                  </Field>
                  <Field>
                    <FieldLabel>Related Certificate (Optional)</FieldLabel>
                    <Select
                      value={data.certificate_request_id}
                      onValueChange={(v) => setData('certificate_request_id', v)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select a certificate" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="none">None</SelectItem>
                        {certificateRequests.map((cert) => (
                          <SelectItem key={cert.id} value={cert.id.toString()}>
                            Cert #{cert.id} - {cert.external_id}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </Field>
                  <Field>
                    <FieldLabel htmlFor="category">Category *</FieldLabel>
                    <Select
                      value={data.category}
                      onValueChange={(v) => setData('category', v)}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select category" id="category" />
                      </SelectTrigger>
                      <SelectContent>
                        {categories.map((cat) => (
                          <SelectItem key={cat.value} value={cat.value}>
                            {cat.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.category && <p className="text-red-500 text-xs mt-1">{errors.category}</p>}
                  </Field>
                </div>
              </FieldSet>

              <FieldSeparator />

              <FieldSet>
                <FieldGroup>
                  <Field>
                    <FieldLabel htmlFor="description">Description *</FieldLabel>
                    <Textarea
                      id="description"
                      placeholder="Add description"
                      className="resize-none"
                      value={data.description}
                      onChange={(e) => setData('description', e.target.value)}
                      required
                    />
                    {errors.description && <p className="text-red-500 text-xs mt-1">{errors.description}</p>}
                  </Field>
                </FieldGroup>
              </FieldSet>
              <Field orientation="horizontal" className="flex gap-3">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create Ticket'}
                </Button>
                <Button variant="outline" type="button" asChild>
                  <Link href={support.tickets.index().url}>Cancel</Link>
                </Button>
              </Field>
            </FieldGroup>
          </form>
        </div>
      </div>
    </AppLayout>
  );
}