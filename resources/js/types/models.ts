import { User } from './auth';

export interface Campaign {
    id: number;
    tenant_id: number;
    name: string;
    budget: number | null;
    start_date: string;
    end_date: string;
    status: 'draft' | 'active' | 'paused' | 'completed';
    description: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
}

export interface Lead {
    id: number;
    tenant_id: number;
    name: string;
    phone: string | null;
    email: string | null;
    state: string;
    source_type: 'global' | 'online' | 'offline';
    status: 'new' | 'contacted' | 'qualified' | 'converted' | 'lost';
    assigned_to: number | null;
    campaign_id: number | null;
    converted_at: string | null;
    created_by: number | null;
    revenue: number | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    
    // Relationships (optional loading)
    assigned_user?: User;
    campaign?: Campaign;
}

export interface DashboardStats {
    total_leads: number;
    active_campaigns: number;
    total_revenue: number;
    conversion_rate: number;
    leads_growth: number;
    revenue_growth: number;
}

export interface ConversionChartData {
    name: string;
    leads: number;
    conversions: number;
}
