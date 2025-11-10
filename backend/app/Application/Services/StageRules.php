<?php

namespace App\Application\Services;

use App\Models\ApplicationDocument;

class StageRules
{
    public function requiredDocs(string $stageKey): array
    {
        return match ($stageKey) {
            'stage1' => ['high_school_cert','passport_first_page'],
            'stage2' => ['visa_receipt','full_passport_pdf','embassy_noc_receipt','signed_offer_letter','passport_photo'],
            'stage3' => ['single_entry_fee_payment','passport_first_page','bank_statement','yellow_fever_card','eval_pdf'],
            'stage4' => ['e_visa_pdf','signed_offer_letter','annual_fee_slip','one_way_ticket','airport_form','accommodation_form'],
            default => [],
        };
    }

    public function hasAllRequired(int $applicationId, string $stageKey): bool
    {
        $required = $this->requiredDocs($stageKey);
        if (!$required) return true;
        $existing = ApplicationDocument::where('application_id', $applicationId)
            ->where('stage_key', $stageKey)
            ->where('status', '!=', 'needs_reupload')
            ->pluck('doc_type')->all();
        foreach ($required as $r) if (!in_array($r, $existing, true)) return false;
        return true;
    }
}

