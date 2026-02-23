import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Campaign, User, BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import leads from '@/routes/leads';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Leads', href: leads.index().url },
    { title: 'Create Lead', href: leads.create().url },
];

interface Props {
    campaigns: Campaign[];
    users: User[];
}

export default function LeadCreate({ campaigns, users }: Props) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        name: '',
        email: '',
        phone: '',
        state: '',
        source_type: 'global',
        assigned_to: '',
        campaign_id: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(leads.store().url);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Lead" />
            
            <div className="mx-auto max-w-2xl p-6">
                <div className="mb-6">
                    <h2 className="text-2xl font-bold tracking-tight">Create New Lead</h2>
                    <p className="text-muted-foreground">Fill in the details to add a new lead to your system.</p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="name">Full Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="John Doe"
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
                                placeholder="john@example.com"
                            />
                            {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="phone">Phone Number</Label>
                            <Input
                                id="phone"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder="+1 (555) 000-0000"
                            />
                            {errors.phone && <p className="text-sm text-destructive">{errors.phone}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="state">State/Region</Label>
                            <Input
                                id="state"
                                value={data.state}
                                onChange={(e) => setData('state', e.target.value)}
                                placeholder="California"
                                required
                            />
                            {errors.state && <p className="text-sm text-destructive">{errors.state}</p>}
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3">
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
                            {errors.campaign_id && <p className="text-sm text-destructive">{errors.campaign_id}</p>}
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
                            {errors.assigned_to && <p className="text-sm text-destructive">{errors.assigned_to}</p>}
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Create Lead
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
                            <p className="text-sm text-muted-foreground">Saved successfully.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
