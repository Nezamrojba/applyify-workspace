<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $setting = Setting::where('key', "modules.{$module}.enabled")->first();

        if (!$setting || !($setting->value === true || $setting->value === '1' || $setting->value === 1)) {
            return response()->json([
                'message' => "Module '{$module}' is disabled",
                'module' => $module
            ], 403);
        }

        return $next($request);
    }
}
