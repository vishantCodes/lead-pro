import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Lead, BreadcrumbItem } from '@/types';
import {
    User,
    Calendar,
    Mail,
    Phone,
    MapPin,
    Tag,
    ArrowLeft,
    Edit,
    MessageSquare,
    History,
    Plus,
    BadgeCheck,
    DollarSign,
} from 'lucide-react';
import leads from '@/routes/leads';
import { useState, FormEventHandler } from 'react';

interface Props {
    lead: Lead & {
        clientNotes: any[];
        emailLogs: any[];
    };
    can: {
        edit_lead: boolean;
        delete_lead: boolean;
        assign_lead: boolean;
        convert_lead: boolean;
    };
}

export default function LeadShow({ lead, can }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Leads', href: leads.index().url },
        { title: lead.name, href: leads.show(lead.id).url },
    ];

    const [dialogOpen, setDialogOpen] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        revenue: '',
    });

    const handleConvert: FormEventHandler = (e) => {
        e.preventDefault();
        post(leads.convert(lead.id).url, {
            onSuccess: () => {
                setDialogOpen(false);
                reset();
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Lead: ${lead.name}`} />

            <div className="flex flex-col gap-6 p-6">
                {/* Header Actions */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={leads.index().url}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-2xl font-bold tracking-tight">{lead.name}</h2>
                            <div className="flex items-center gap-2 mt-1">
                                <Badge
                                    variant={
                                        lead.status === 'converted'
                                            ? 'default'
                                            : lead.status === 'lost'
                                            ? 'destructive'
                                            : 'secondary'
                                    }
                                    className="capitalize"
                                >
                                    {lead.status === 'converted' && (
                                        <BadgeCheck className="mr-1 h-3 w-3" />
                                    )}
                                    {lead.status}
                                </Badge>
                                <span className="text-sm text-muted-foreground flex items-center gap-1">
                                    <Calendar className="h-3 w-3" />
                                    Added on {new Date(lead.created_at).toLocaleDateString()}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {can.edit_lead && lead.status !== 'converted' && (
                            <Button variant="outline" asChild>
                                <Link href={leads.edit(lead.id).url}>
                                    <Edit className="mr-2 h-4 w-4" />
                                    Edit
                                </Link>
                            </Button>
                        )}

                        {can.convert_lead && lead.status !== 'converted' && (
                            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button className="gap-2">
                                        <BadgeCheck className="h-4 w-4" />
                                        Convert to Client
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="sm:max-w-md">
                                    <DialogHeader>
                                        <DialogTitle>Convert Lead to Client</DialogTitle>
                                        <DialogDescription>
                                            You are converting <strong>{lead.name}</strong> to a client.
                                            Enter the deal revenue to complete the conversion.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form onSubmit={handleConvert} className="space-y-4 py-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="revenue">Deal Revenue</Label>
                                            <div className="relative">
                                                <DollarSign className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                                <Input
                                                    id="revenue"
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
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={() => setDialogOpen(false)}
                                                disabled={processing}
                                            >
                                                Cancel
                                            </Button>
                                            <Button type="submit" disabled={processing || !data.revenue}>
                                                {processing ? 'Converting…' : 'Confirm Conversion'}
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        )}

                        {lead.status === 'converted' && lead.revenue && (
                            <div className="flex items-center gap-1.5 text-sm text-muted-foreground border rounded-md px-3 py-1.5">
                                <DollarSign className="h-4 w-4 text-green-500" />
                                <span>
                                    Revenue:{' '}
                                    <strong className="text-foreground">
                                        ${Number(lead.revenue).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                    </strong>
                                </span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Converted info banner */}
                {lead.status === 'converted' && lead.converted_at && (
                    <div className="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 dark:bg-green-950/20 dark:border-green-900 px-4 py-3 text-sm text-green-800 dark:text-green-300">
                        <BadgeCheck className="h-4 w-4 shrink-0" />
                        <span>
                            This lead was converted to a client on{' '}
                            <strong>{new Date(lead.converted_at).toLocaleDateString('en-US', { dateStyle: 'long' })}</strong>.
                        </span>
                    </div>
                )}

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Left Column: Key Info */}
                    <Card className="md:col-span-1">
                        <CardHeader>
                            <CardTitle className="text-lg">Contact Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center gap-3">
                                <Mail className="h-4 w-4 text-muted-foreground" />
                                <div className="text-sm">
                                    <p className="font-medium">Email</p>
                                    <p className="text-muted-foreground">{lead.email || 'N/A'}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <Phone className="h-4 w-4 text-muted-foreground" />
                                <div className="text-sm">
                                    <p className="font-medium">Phone</p>
                                    <p className="text-muted-foreground">{lead.phone || 'N/A'}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <MapPin className="h-4 w-4 text-muted-foreground" />
                                <div className="text-sm">
                                    <p className="font-medium">Location</p>
                                    <p className="text-muted-foreground">{lead.state}</p>
                                </div>
                            </div>
                            <Separator />
                            <div className="flex items-center gap-3">
                                <Tag className="h-4 w-4 text-muted-foreground" />
                                <div className="text-sm">
                                    <p className="font-medium">Source</p>
                                    <p className="text-muted-foreground capitalize">{lead.source_type}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <User className="h-4 w-4 text-muted-foreground" />
                                <div className="text-sm">
                                    <p className="font-medium">Assigned To</p>
                                    <p className="text-muted-foreground">{lead.assigned_user?.name || 'Unassigned'}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Right Column: Tabs for more content */}
                    <div className="md:col-span-2">
                        <Tabs defaultValue="notes" className="w-full">
                            <TabsList className="w-full justify-start border-b rounded-none bg-transparent h-auto p-0">
                                <TabsTrigger
                                    value="notes"
                                    className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent py-2"
                                >
                                    <MessageSquare className="mr-2 h-4 w-4" />
                                    Notes
                                </TabsTrigger>
                                <TabsTrigger
                                    value="activity"
                                    className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent py-2"
                                >
                                    <History className="mr-2 h-4 w-4" />
                                    Activity Log
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="notes" className="mt-4">
                                <Card>
                                    <CardContent className="pt-6">
                                        <div className="flex flex-col gap-4">
                                            {lead.clientNotes?.length > 0 ? (
                                                lead.clientNotes.map((note: any) => (
                                                    <div key={note.id} className="border-b pb-4 last:border-0 last:pb-0">
                                                        <div className="flex items-center justify-between mb-2">
                                                            <span className="font-medium">{note.createdBy?.name}</span>
                                                            <span className="text-xs text-muted-foreground">
                                                                {new Date(note.created_at).toLocaleString()}
                                                            </span>
                                                        </div>
                                                        <p className="text-sm text-muted-foreground">{note.note}</p>
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="text-center py-8 text-muted-foreground">
                                                    No notes yet.
                                                </div>
                                            )}
                                            <Button variant="outline" className="mt-2">
                                                <Plus className="mr-2 h-4 w-4" />
                                                Add Note
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="activity" className="mt-4">
                                <Card>
                                    <CardContent className="pt-6">
                                        <div className="text-center py-8 text-muted-foreground">
                                            Activity logging implementation in progress...
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

const Separator = () => <div className="h-[1px] w-full bg-border my-2" />;
