import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Campaign, User, Lead, BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import leads from '@/routes/leads';

interface Props {
    lead: Lead;
    campaigns: Campaign[];
    users: User[];
}

export default function LeadEdit({ lead, campaigns, users }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Leads', href: leads.index().url },
        { title: lead.name, href: leads.show(lead.id).url },
        { title: 'Edit', href: leads.edit(lead.id).url },
    ];

    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        name: lead.name || '',
        email: lead.email || '',
        phone: lead.phone || '',
        state: lead.state || '',
        status: lead.status || 'new',
        source_type: lead.source_type || 'global',
        assigned_to: lead.assigned_to?.toString() || '',
        campaign_id: lead.campaign_id?.toString() || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('leads.update', lead.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Lead: ${lead.name}`} />
            
            <div className="mx-auto max-w-2xl p-6">
                <div className="mb-6">
                    <h2 className="text-2xl font-bold tracking-tight">Edit Lead: {lead.name}</h2>
                    <p className="text-muted-foreground">Update the lead information and status.</p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="name">Full Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">Email Address</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3">
                        <div className="space-y-2">
                            <Label htmlFor="status">Status</Label>
                            <Select 
                                value={data.status} 
                                onValueChange={(value) => setData('status', value as any)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="new">New</SelectItem>
                                    <SelectItem value="contacted">Contacted</SelectItem>
                                    <SelectItem value="qualified">Qualified</SelectItem>
                                    <SelectItem value="converted">Converted</SelectItem>
                                    <SelectItem value="lost">Lost</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="source_type">Source</Label>
                            <Select 
                                value={data.source_type} 
                                onValueChange={(value) => setData('source_type', value as any)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select source" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="global">Global</SelectItem>
                                    <SelectItem value="online">Online</SelectItem>
                                    <SelectItem value="offline">Offline</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.source_type && <p className="text-sm text-destructive">{errors.source_type}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="state">State</Label>
                            <Input
                                id="state"
                                value={data.state}
                                onChange={(e) => setData('state', e.target.value)}
                                required
                            />
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="campaign_id">Campaign</Label>
                            <Select 
                                value={data.campaign_id} 
                                onValueChange={(value) => setData('campaign_id', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select campaign" />
                                </SelectTrigger>
                                <SelectContent>
                                    {campaigns.map((campaign) => (
                                        <SelectItem key={campaign.id} value={campaign.id.toString()}>
                                            {campaign.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="assigned_to">Assign To</Label>
                            <Select 
                                value={data.assigned_to} 
                                onValueChange={(value) => setData('assigned_to', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select user" />
                                </SelectTrigger>
                                <SelectContent>
                                    {users.map((user) => (
                                        <SelectItem key={user.id} value={user.id.toString()}>
                                            {user.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 pt-4 border-t">
                        <Button type="submit" disabled={processing}>
                            Update Lead
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href={leads.index().url}>Cancel</Link>
                        </Button>

                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-muted-foreground">Changes saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
