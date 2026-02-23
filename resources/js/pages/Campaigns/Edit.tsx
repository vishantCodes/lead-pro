import CampaignForm from './Form';
import { Campaign } from '@/types';

interface Props {
    campaign: Campaign;
}

export default function CampaignEdit({ campaign }: Props) {
    return <CampaignForm campaign={campaign} />;
}
