   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container container mt-4">
    <h2 class="mb-4">📂 Categories With No Templates</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categoriesWithNoTemplate as $index => $cat)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $cat['id'] ?? '-' }}</td>
                        <td>{{ $cat['category_name'] ?? '-' }}</td>
                        <td>{{ $cat['reason'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">All categories have templates.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-5 mb-4">🔍 Special Pages & Keywords with Unlive Categories</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($unliveCatSAndKPages as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['cat_id'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">All assigned categories are live.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-5 mb-4">Orphan Pages With No category assign,index and Not redirect </h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orphanErrorPage as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['reason'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">All assigned categories are live.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-5 mb-4">Keyword Page and Special page with index or canonical link and Not redirect </h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kAndSIndexWithNoRedirection as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['reason'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">All assigned categories are live.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-5 mb-4">Page Slug History </h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pageSlugHistoryError as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{{ $item['id'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['reason'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No Page found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@include('layouts.masterscript')
</body>

</html>
