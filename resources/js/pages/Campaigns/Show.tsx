import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Campaign, Lead, BreadcrumbItem } from '@/types';
import { Edit, ArrowLeft, Users, Target, Activity, CheckCircle2 } from 'lucide-react';
import campaigns from '@/routes/campaigns';
import leads from '@/routes/leads';
import { DataTable } from '@/components/data-table';
import { ColumnDef } from '@tanstack/react-table';

interface Props {
    campaign: Campaign & {
        leads: Lead[];
        tasks: any[];
    };
    metrics: {
        total_leads: number;
        converted_leads: number;
        total_tasks: number;
        completed_tasks: number;
        conversion_rate: number;
    };
}

export default function CampaignShow({ campaign, metrics }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Campaigns', href: campaigns.index().url },
        { title: campaign.name, href: campaigns.show(campaign.id).url },
    ];

    const leadColumns: ColumnDef<Lead>[] = [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => (
                <Link 
                    href={leads.show(row.original.id).url}
                    className="font-medium text-primary hover:underline"
                >
                    {row.getValue('name')}
                </Link>
            ),
        },
        {
            accessorKey: 'email',
            header: 'Email',
        },
        {
            accessorKey: 'status',
            header: 'Status',
            cell: ({ row }) => {
                const status = row.getValue('status') as string;
                return (
                    <Badge variant={status === 'converted' ? 'default' : 'secondary'}>
                        {status}
                    </Badge>
                );
            },
        },
        {
            id: 'assigned_to',
            header: 'Assigned To',
            cell: ({ row }) => row.original.assigned_user?.name || 'Unassigned',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${campaign.name} - Campaign Details`} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={campaigns.index().url}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{campaign.name}</h1>
                            <div className="flex items-center gap-2 mt-1">
                                <Badge variant={campaign.status === 'active' ? 'default' : 'secondary'}>
                                    {campaign.status}
                                </Badge>
                                <span className="text-sm text-muted-foreground">
                                    {campaign.start_date} - {campaign.end_date}
                                </span>
                            </div>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={campaigns.edit(campaign.id).url}>
                            <Edit className="mr-2 h-4 w-4" />
                            Edit Campaign
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Leads</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.total_leads}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Conversions</CardTitle>
                            <Target className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.converted_leads}</div>
                            <p className="text-xs text-muted-foreground">
                                {metrics.conversion_rate.toFixed(1)}% conversion rate
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tasks Progress</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {metrics.completed_tasks} / {metrics.total_tasks}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Budget</CardTitle>
                            <div className="text-xs font-bold text-muted-foreground">$</div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">${campaign.budget || 0}</div>
                        </CardContent>
                    </Card>
                </div>

                <Tabs defaultValue="leads" className="w-full">
                    <TabsList>
                        <TabsTrigger value="leads">Leads ({campaign.leads.length})</TabsTrigger>
                        <TabsTrigger value="tasks">Tasks ({campaign.tasks.length})</TabsTrigger>
                        <TabsTrigger value="details">Details</TabsTrigger>
                    </TabsList>
                    <TabsContent value="leads" className="mt-4 border rounded-lg bg-card">
                        <DataTable columns={leadColumns} data={campaign.leads} />
                    </TabsContent>
                    <TabsContent value="tasks" className="mt-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Campaign Tasks</CardTitle>
                                <CardDescription>Manage and track tasks related to this campaign.</CardDescription>
                            </CardHeader>
                            <CardContent>
                                {campaign.tasks.length > 0 ? (
                                    <div className="space-y-4">
                                        {campaign.tasks.map((task) => (
                                            <div key={task.id} className="flex items-center justify-between p-4 border rounded-lg">
                                                <div className="flex items-center gap-3">
                                                    <CheckCircle2 className={`h-5 w-5 ${task.status === 'completed' ? 'text-green-500' : 'text-muted-foreground'}`} />
                                                    <div>
                                                        <p className="font-medium">{task.title}</p>
                                                        <p className="text-sm text-muted-foreground">
                                                            Assigned to: {task.assigned_user?.name || 'Unassigned'}
                                                        </p>
                                                    </div>
                                                </div>
                                                <Badge>{task.status}</Badge>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="flex flex-col items-center justify-center py-10 text-center">
                                        <Activity className="h-10 w-10 text-muted-foreground mb-4" />
                                        <p className="text-muted-foreground">No tasks created for this campaign yet.</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="details" className="mt-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>Campaign Overview</CardTitle>
                                <CardDescription>Detailed information and description.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <h4 className="font-semibold text-sm uppercase text-muted-foreground mb-1">Description</h4>
                                    <p className="text-sm whitespace-pre-wrap">{campaign.description || 'No description provided.'}</p>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-semibold text-sm uppercase text-muted-foreground mb-1">Start Date</h4>
                                        <p>{campaign.start_date}</p>
                                    </div>
                                    <div>
                                        <h4 className="font-semibold text-sm uppercase text-muted-foreground mb-1">End Date</h4>
                                        <p>{campaign.end_date}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
