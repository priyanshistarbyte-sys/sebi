<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardFileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'files.*' => [
                'required','file','max:10240',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp,image/gif,image/svg+xml'
            ],
        ], ['files.*.max' => 'Each file must be 10MB or less.']);

        $user = $request->user();
        $cid  = session('active_company_id');
        $periodStr = session('active_period') ?: now()->format('Y-m');
        try {
            [$yy, $mm] = array_map('intval', explode('-', $periodStr));
            $period = Carbon::create($yy, $mm, 1)->startOfMonth();
        } catch (\Throwable $e) {
            $period = now()->startOfMonth();
        }
        $basePath = 'uploads/'.($cid ?: 'general').'/'.$period->format('Y/m');

        foreach ((array)$request->file('files') as $upload) {
            if (!$upload) continue;

            $mime = $upload->getMimeType();
            $ext  = strtolower($upload->getClientOriginalExtension());
            $orig = $upload->getClientOriginalName();
            $name = pathinfo($orig, PATHINFO_FILENAME);
            $stored   = $upload->store($basePath, 'public');

            $res = File::create([
                'company_id'    => $cid,
                'uploaded_by'   => $user->id,
                'period'        => $period,
                'name'          => $name,
                'original_name' => $orig,
                'path'          => $stored,
                'mime'          => $mime,
                'ext'           => $ext,
                'size'          => $upload->getSize(),
                'is_image'      => Str::startsWith($mime, 'image/'),
                'is_pdf'        => $mime === 'application/pdf',
            ]);
        }

        return back()->with('status', 'Files uploaded for '.$period->isoFormat('MMMM YYYY').'.');
    }

    public function update(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);
        $data = $request->validate(['name' => 'required|string|max:190']);
        $file->update(['name' => $data['name']]);
        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);
        Storage::disk('public')->delete($file->path);
        $file->delete();
        return back()->with('status', 'File deleted.');
    }

    public function preview(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);
        return view('admin.dashboard.partials.file-preview', ['file' => $file]);
    }

    private function authorizeFile(Request $request, File $file): void
    {
        $user = $request->user();
        $cid  = session('active_company_id');
        if (!$user->is_admin) {
            abort_unless((int)$file->company_id === (int)$cid, 403);
        } elseif ($cid) {
            abort_unless((int)$file->company_id === (int)$cid, 403);
        }
    }
}
