@php $url = Storage::disk('public')->url($file->path); @endphp
<div class="p-4">
    <div class="mb-3">
        <div class="text-sm text-gray-500">File</div>
        <div class="text-base font-semibold">{{ $file->name }}</div>
        <div class="text-xs text-gray-500">
            {{ $file->original_name }} · {{ $file->mime }} · {{ number_format($file->size/1024,1) }} KB
        </div>
    </div>

    <div class="border rounded overflow-hidden justify-items-center">
        @if($file->is_image)
            <img src="{{ $url }}" alt="{{ $file->name }}" class="max-w-full h-auto block">
        @elseif($file->is_pdf)
            <iframe src="{{ $url }}" class="w-full h-[70vh]" title="PDF preview"></iframe>
        @else
            <div class="p-4 text-sm">
                Preview not available. <a href="{{ $url }}" target="_blank" class="underline">Download</a>
            </div>
        @endif
    </div>
</div>
