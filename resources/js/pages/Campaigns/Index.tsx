import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Campaign, BreadcrumbItem } from '@/types';
import { ColumnDef } from '@tanstack/react-table';
import { MoreHorizontal, Plus, Calendar } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import campaigns from '@/routes/campaigns';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Campaigns',
        href: campaigns.index().url,
    },
];

interface Props {
    campaigns: {
        data: Campaign[];
        meta: any;
        links: any;
    };
}

export default function CampaignsIndex({ campaigns: campaignsData }: Props) {
    const columns: ColumnDef<Campaign>[] = [
        {
            accessorKey: 'name',
            header: 'Campaign Name',
            cell: ({ row }) => <span className="font-medium">{row.getValue('name')}</span>,
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                const variants: Record<string, "default" | "secondary" | "destructive" | "outline"> = {
                    active: 'default',
                    draft: 'secondary',
                    paused: 'outline',
                    completed: 'secondary',
                };
                return (
                    <Badge variant={variants[status] || 'secondary'} className="capitalize">
                        {status}
                    </Badge>
                );
            },
        },
        {
            accessorKey: 'budget',
            header: 'Budget',
            cell: ({ row }) => {
                const amount = row.getValue('budget') as number;
                return amount ? `$${new Intl.NumberFormat().format(amount)}` : <span className="text-muted-foreground italic text-xs">No budget</span>;
            },
        },
        {
            accessorKey: 'start_date',
            header: 'Duration',
            cell: ({ row }) => (
                <div className="flex flex-col text-xs text-muted-foreground">
                    <span>From: {new Date(row.original.start_date).toLocaleDateString()}</span>
                    <span>To: {new Date(row.original.end_date).toLocaleDateString()}</span>
                </div>
            ),
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                const campaign = row.original;
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
                                <Link href={campaigns.show(campaign.id).url}>View performance</Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                                <Link href={campaigns.edit(campaign.id).url}>Edit settings</Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem className="text-destructive">Archive</DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Campaigns" />
            <div className="flex flex-1 flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Campaigns</h2>
                        <p className="text-muted-foreground">Monitor and manage your marketing efforts.</p>
                    </div>
                    <Button asChild>
                        <Link href={campaigns.create().url}>
                            <Plus className="mr-2 h-4 w-4" />
                            New Campaign
                        </Link>
                    </Button>
                </div>
                
                <DataTable 
                    columns={columns} 
                    data={campaignsData.data || []} 
                    searchKey="name" 
                />
            </div>
        </AppLayout>
    );
}
