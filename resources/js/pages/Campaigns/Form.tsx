import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Campaign, BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import campaigns from '@/routes/campaigns';

interface Props {
    campaign?: Campaign;
}

export default function CampaignForm({ campaign }: Props) {
    const isEdit = !!campaign;
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Campaigns', href: campaigns.index().url },
        { title: isEdit ? `Edit: ${campaign.name}` : 'New Campaign', href: isEdit ? campaigns.edit(campaign.id).url : campaigns.create().url },
    ];

    const { data, setData, post, patch, processing, errors, recentlySuccessful } = useForm({
        name: campaign?.name || '',
        description: campaign?.description || '',
        budget: campaign?.budget?.toString() || '',
        start_date: campaign?.start_date || '',
        end_date: campaign?.end_date || '',
        status: campaign?.status || 'draft',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            patch(campaigns.update(campaign.id).url);
        } else {
            post(campaigns.store().url);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? `Edit Campaign: ${campaign.name}` : 'New Campaign'} />
            
            <div className="mx-auto max-w-2xl p-6">
                <div className="mb-6">
                    <h2 className="text-2xl font-bold tracking-tight">
                        {isEdit ? `Edit Campaign: ${campaign.name}` : 'Create New Campaign'}
                    </h2>
                    <p className="text-muted-foreground">
                        {isEdit ? 'Update your campaign details and budget.' : 'Set up a new marketing campaign to track performance.'}
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="name">Campaign Name</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Summer Sale 2024"
                            required
                        />
                        {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Describe the goals and strategy of this campaign..."
                            className="min-h-[100px]"
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="budget">Budget ($)</Label>
                            <Input
                                id="budget"
                                type="number"
                                step="0.01"
                                value={data.budget}
                                onChange={(e) => setData('budget', e.target.value)}
                                placeholder="5000.00"
                            />
                        </div>

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
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="paused">Paused</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                type="date"
                                value={data.start_date}
                                onChange={(e) => setData('start_date', e.target.value)}
                                required
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="end_date">End Date</Label>
                            <Input
                                id="end_date"
                                type="date"
                                value={data.end_date}
                                onChange={(e) => setData('end_date', e.target.value)}
                                required
                            />
                        </div>
                    </div>

                    <div className="flex items-center gap-4 pt-4 border-t">
                        <Button type="submit" disabled={processing}>
                            {isEdit ? 'Update Campaign' : 'Create Campaign'}
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href={campaigns.index().url}>Cancel</Link>
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
