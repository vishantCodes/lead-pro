import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Lead, BreadcrumbItem } from '@/types';
import { ColumnDef } from '@tanstack/react-table';
import { BadgeCheck, DollarSign, MoreHorizontal, Plus, UserCircle } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import leads from '@/routes/leads';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Leads',
        href: leads.index().url,
    },
];

interface Props {
    leads: {
        data: Lead[];
        meta: any;
        links: any;
    };
    can: {
        create_lead: boolean;
        edit_leads: boolean;
        delete_leads: boolean;
        assign_leads: boolean;
        convert_leads: boolean;
    };
}

/** Small inline convert dialog used in the index table */
function ConvertDialog({ lead, onClose }: { lead: Lead; onClose: () => void }) {
    const { data, setData, post, processing, errors, reset } = useForm({ revenue: '' });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(leads.convert(lead.id).url, {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <DialogContent className="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Convert Lead to Client</DialogTitle>
                <DialogDescription>
                    Convert <strong>{lead.name}</strong> to a client by entering the deal revenue.
                </DialogDescription>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4 py-2">
                <div className="space-y-2">
                    <Label htmlFor="index-revenue">Deal Revenue</Label>
                    <div className="relative">
                        <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            id="index-revenue"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            className="pl-9"
                            value={data.revenue}
                            onChange={(e) => setData('revenue', e.target.value)}
                            required
                        />
                    </div>
                    {errors.revenue && (
                        <p className="text-sm text-destructive">{errors.revenue}</p>
                    )}
                </div>
                <DialogFooter className="pt-2">
                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                        Cancel
                    </Button>
                    <Button type="submit" disabled={processing || !data.revenue}>
                        {processing ? 'Converting…' : 'Confirm Conversion'}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    );
}

export default function LeadsIndex({ leads: leadsData, can }: Props) {
    const [convertingLead, setConvertingLead] = useState<Lead | null>(null);

    const columns: ColumnDef<Lead>[] = [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => (
                <div className="flex flex-col">
                    <span className="font-medium">{row.getValue('name')}</span>
                    <span className="text-xs text-muted-foreground">{row.original.email}</span>
                </div>
            ),
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                return (
                    <Badge
                        variant={
                            status === 'converted'
                                ? 'default'
                                : status === 'lost'
                                ? 'destructive'
                                : 'secondary'
                        }
                        className="capitalize"
                    >
                        {status === 'converted' && <BadgeCheck className="mr-1 h-3 w-3" />}
                        {status}
                    </Badge>
                );
            },
        },
        {
            accessorKey: 'source_type',
            header: 'Source',
            cell: ({ row }) => <span className="capitalize">{row.getValue('source_type')}</span>,
        },
        {
            accessorKey: 'assigned_to',
            header: 'Assigned To',
            cell: ({ row }) => {
                const user = row.original.assigned_user;
                return user ? (
                    <div className="flex items-center gap-2">
                        <UserCircle className="h-4 w-4 text-muted-foreground" />
                        <span className="text-sm">{user.name}</span>
                    </div>
                ) : (
                    <span className="text-xs text-muted-foreground italic">Unassigned</span>
                );
            },
        },
        {
            accessorKey: 'created_at',
            header: 'Created',
            cell: ({ row }) => new Date(row.getValue('created_at')).toLocaleDateString(),
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                const lead = row.original;
                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuItem asChild>
                                <Link href={leads.show(lead.id).url}>View details</Link>
                            </DropdownMenuItem>
                            {can.edit_leads && lead.status !== 'converted' && (
                                <DropdownMenuItem asChild>
                                    <Link href={leads.edit(lead.id).url}>Edit lead</Link>
                                </DropdownMenuItem>
                            )}
                            {can.convert_leads && lead.status !== 'converted' && (
                                <>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        className="text-green-600 dark:text-green-400 focus:text-green-600 dark:focus:text-green-400"
                                        onSelect={() => setConvertingLead(lead)}
                                    >
                                        <BadgeCheck className="mr-2 h-4 w-4" />
                                        Convert to Client
                                    </DropdownMenuItem>
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Leads" />

            {/* Convert Dialog (portal-rendered, outside table) */}
            <Dialog
                open={!!convertingLead}
                onOpenChange={(open) => { if (!open) setConvertingLead(null); }}
            >
                {convertingLead && (
                    <ConvertDialog
                        lead={convertingLead}
                        onClose={() => setConvertingLead(null)}
                    />
                )}
            </Dialog>

            <div className="flex flex-1 flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Leads</h2>
                        <p className="text-muted-foreground">Manage your prospective clients and track conversions.</p>
                    </div>
                    {can.create_lead && (
                        <Button asChild>
                            <Link href={leads.create().url}>
                                <Plus className="mr-2 h-4 w-4" />
                                Add Lead
                            </Link>
                        </Button>
                    )}
                </div>

                <DataTable
                    columns={columns}
                    data={leadsData.data || []}
                    searchKey="name"
                />
            </div>
        </AppLayout>
    );
}
