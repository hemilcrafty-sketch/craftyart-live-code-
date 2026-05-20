@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
{{-- @include('layouts.masterhead') --}}

<style>
    body {
        background-color: #f7f8fa;
        font-family: 'Inter', sans-serif;
    }

    .card-box {
        background: #fff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .caricature-item {
        background: #ffffff;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 35px;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
    }

    .caricature-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-weight: 600;
        color: #3c4858;
        border-bottom: 2px solid #007bff30;
        display: inline-block;
        padding-bottom: 4px;
        margin-bottom: 15px;
    }

    .image-box {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: #f8f9fb;
        padding: 10px;
        transition: all 0.3s ease;
    }

    .image-box img {
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image-box img:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .img-fluid {
        max-height: 350px;
        object-fit: contain;
    }

    .header-title {
        font-size: 22px;
        font-weight: 700;
        color: #333;
        margin-bottom: 25px;
    }

    .meta-info {
        background: #eef2f7;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 14px;
        color: #555;
    }
</style>

<div class="main-container p-4">
    <div class="card-box">
        <h4 class="header-title">
            🖼️ Caricature Details for Payment ID:
            <span class="text-primary">{{ $payment_id }}</span>
        </h4>

        @foreach ($records as $item)
            <div class="caricature-item">
                <div class="meta-info mb-3">
                    <strong>ID:</strong> {{ $item->id }} &nbsp; | &nbsp;
                    <strong>Caricature ID:</strong> <a href="{{ $item->page_link }}"target="_blank">{{ $item->caricature_id }}</a>
                </div>

                <div class="row g-4">
                    {{-- Uploaded Images --}}
                    <div class="col-md-4 text-center">
                        <h6 class="section-title">Caricature</h6>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            @if (!empty($item->images))
                                @foreach ($item->images as $img)
                                    <div class="image-box" style="width: 45%; display:inline-block;">
                                        <img src="{{ $img }}" class="img-fluid" alt="Uploaded Image">
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted py-5">No Images</p>
                            @endif
                        </div>
                    </div>

                    {{-- User Input --}}
                    <div class="col-md-4 text-center">
                        <h6 class="section-title">User Input</h6>
                        <div class="image-box">
                            @if ($item->user_input)
                                <img src="{{ $item->user_input }}" class="img-fluid" alt="User Input">
                            @else
                                <p class="text-muted py-5">No User Input</p>
                            @endif
                        </div>
                    </div>


                    {{-- Cartoon Image --}}
                    <div class="col-md-4 text-center">
                        <h6 class="section-title">Cartoon Image</h6>
                        <div class="image-box">
                            @if ($item->cartoon_image)
                                <img src="{{ $item->cartoon_image }}" class="img-fluid" alt="Cartoon Image">
                            @else
                                <p class="text-muted py-5">No Cartoon Image</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('layouts.masterscript')
</body>

</html>
