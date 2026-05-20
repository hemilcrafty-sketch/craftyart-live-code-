<div class="mt-4 mb-4 text-center">
    <span style="font-size: 20px;">Primary Keyword : - </span> <span style="font-size: 20px;font-weight: bold">{{ $keyword }}</span>
</div>

<table class="table table-hover">
    <thead>
    <tr>
        <th class="bg-secondary text-white">Check</th>
        <th class="bg-secondary text-white">Status</th>
        <th class="bg-secondary text-white">Count</th>
    </tr>
    </thead>
    <tbody>
    @foreach($checks as $item)
    <tr>
        <td>{{ $item['label'] }}</td>
        <td>
            @if($item['count'] > 0)
            <span class="badge bg-success">✔</span>
            @else
            <span class="badge bg-danger">✘</span>
            @endif
        </td>
        <td>{{ $item['count'] }}</td>
    </tr>
    @endforeach
    </tbody>
</table>