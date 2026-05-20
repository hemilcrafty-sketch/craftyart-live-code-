    @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
    @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
    @inject('helperController', 'App\Http\Controllers\HelperController')
    @include('layouts.masterhead')
    <div class="main-container designer-access-container">
        <div class="">
            <div class="min-height-200px">
                <div class="card-box">
                    <div style="display: flex; flex-direction: column; height: 90vh; overflow: hidden;">

                        <div class="row justify-content-between mb-2">
                            <div class="col-md-3">

                            </div>

                            <div class="col-md-7">
                                @include('partials.filter_form', [
                                    'action' => route('caricature_history.index'),
                                ])
                            </div>
                        </div>
                        <div class="scroll-wrapper table-responsive tableFixHead"
                            style="max-height: calc(110vh - 220px) !important">
                            <table id="temp_table" style="table-layout: fixed; width: 100%;"
                                class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>name</th>
                                        <th>email</th>
                                        <th>contact no</th>
                                        <th>caricature id</th>
                                        <th>payment id</th>
                                        <th>images</th>
                                        <th>user input</th>
                                        <th class="datatable-nosort">cartoon_image</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allCategories as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->user->name ?? '-' }}</td>
                                            <td>{{ $item->user->email ?? '-' }}</td>
                                            <td>{{ $item->purchase->contact_no ?? $item->user->contact_no ?? '-' }}</td>
                                            <td><a href="{{ $item->page_link }}"target="_blank">{{ $item->caricature_id }}</a></td>
                                            <td>
                                                @if ($item->payment_id)
                                                    <a href="{{ url('caricature_history/payment/' . $item->payment_id) }}"
                                                        target="_blank">

                                                        {{ $item->payment_id }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($item->images))
                                                    @foreach ($item->images as $img)
                                                        <img src="{{ $img }}" alt="Image" width="50"
                                                            height="50" style="border-radius: 5px; margin:2px;">
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No Images</span>
                                                @endif
                                            </td>
                                        
                                            <td>
                                                @if ($item->user_input)
                                                    <img src="{{ $item->user_input }}" alt="Image" width="50"
                                                        height="50" style="border-radius: 5px; margin:2px;">
                                                @else
                                                    <span class="text-muted">No Input</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->cartoon_image)
                                                    <img src="{{ $item->cartoon_image }}" width="60" height="60"
                                                        style="border-radius: 8px;">
                                                @else
                                                    <span class="text-muted">No Cartoon</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No caricature history
                                                found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr class="my-1">
                    @include('partials.pagination', ['items' => $allCategories])
                </div>
            </div>
        </div>
    </div>


    @include('layouts.masterscript')
    </body>

    </html>
