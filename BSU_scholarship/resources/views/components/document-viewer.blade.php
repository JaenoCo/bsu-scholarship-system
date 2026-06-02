<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - {{ $document->document_name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .viewer-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            background: #dc2626;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .viewer-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f3f4f6; /* Gray background for image viewer */
            position: relative;
        }
        iframe {
            width: 100%;
            height: 100%;
            flex: 1;
            border: none;
        }
        .image-viewer {
            width: 100%;
            height: 100%;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: auto;
        }
        .image-viewer img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .fallback-message {
            padding: 2rem;
            text-align: center;
            background: #f3f4f6;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="viewer-header">
            <div>
                <h1 class="text-xl font-bold">{{ $document->document_name }}</h1>
                <p class="text-sm opacity-90">{{ $document->getFileTypeDisplayName() }} • {{ $document->getFileSizeFormatted() }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ asset('storage/' . ltrim($document->file_path, '/')) }}" 
                   download 
                   class="bg-white text-red-700 px-4 py-2 rounded hover:bg-gray-100 transition-colors font-semibold">
                    Download
                </a>
                <button onclick="window.close()" 
                        class="bg-white text-red-700 px-4 py-2 rounded hover:bg-gray-100 transition-colors font-semibold">
                    Close
                </button>
            </div>
        </div>
        
        <div class="viewer-content">
            @if(isset($viewerType) && $viewerType === 'image')
                <div class="image-viewer">
                    <img src="{{ $fileUrl }}" alt="{{ $document->document_name }}">
                </div>
            @elseif(isset($viewerType) && $viewerType === 'pdf')
                 <iframe src="{{ $fileUrl }}" type="application/pdf"></iframe>
            @elseif($isLocalhost || empty($viewers))
                <!-- Localhost or no viewers available - show download option -->
                <div class="fallback-message">
                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-700 mb-2">Document Viewer</h2>
                    <p class="text-gray-600 mb-4">
                        @if($isLocalhost)
                            Online document viewers are not available on localhost. Please download the document to view it.
                        @else
                            Document viewers require a publicly accessible URL. Please download the document to view it.
                        @endif
                    </p>
                    <a href="{{ $downloadUrl }}" 
                       download 
                       class="bg-red-700 text-white px-6 py-3 rounded-lg hover:bg-red-800 transition-colors font-semibold inline-block">
                        Download Document
                    </a>
                </div>
            @else
                <!-- Try viewers in order (iframe-based viewers like Google/Office) -->
                @foreach($viewers as $index => $viewer)
                    <iframe src="{{ $viewer['url'] }}" 
                            id="docViewer{{ $index }}" 
                            class="viewer-iframe {{ $index > 0 ? 'hidden' : '' }}"></iframe>
                @endforeach
            @endif
        </div>
    </div>
    @if(!$isLocalhost && !empty($viewers))
    <!-- Viewers loaded -->
    @endif
</body>
</html>

