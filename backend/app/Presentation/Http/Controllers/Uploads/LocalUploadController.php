<?php

namespace App\Presentation\Http\Controllers\Uploads;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LocalUploadController
{
    public function store(Request $request)
    {
        $user = $request->user();
        
        $docType = $request->input('doc_type');
        $maxSize = ($docType === 'full_passport_pdf') ? 51200 : 10240;
        $maxSizeMB = ($docType === 'full_passport_pdf') ? 50 : 10;
        
        try {
            $data = $request->validate([
                'file' => ['required','file','mimes:pdf,png,jpg,jpeg',"max:{$maxSize}"]
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['file'])) {
                foreach ($errors['file'] as $error) {
                    if (str_contains($error, 'larger than') || str_contains($error, 'max')) {
                        return response()->json([
                            'message' => "File is too large. Maximum file size is {$maxSizeMB}MB. Please compress or resize your file and try again.",
                            'errors' => ['file' => ["The file must not be larger than {$maxSizeMB}MB."]]
                        ], 413);
                    }
                }
            }
            throw $e;
        }
        
        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension());
        $name = Str::uuid()->toString().'.'.$ext;
        $dir = 'uploads/'.$user->id;
        $path = $file->storeAs($dir, $name, 'public');
        
        $baseUrl = env('APP_URL', 'http://127.0.0.1:8000');
        $url = rtrim($baseUrl, '/') . '/storage/' . $path;
        
        return response()->json([
            'file_url' => $url,
            'file_type' => $ext === 'jpeg' ? 'jpg' : $ext,
            'size_bytes' => $file->getSize(),
            'path' => $path,
        ], 201);
    }
}

