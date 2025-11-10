<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController
{
    public function index(Request $request)
    {
        $q = Audit::query();
        if ($t = $request->query('entity_type')) $q->where('entity_type', $t);
        if ($id = $request->query('entity_id')) $q->where('entity_id', $id);
        if ($a = $request->query('action')) $q->where('action', $a);
        return $q->orderByDesc('created_at')->paginate(50);
    }
}

