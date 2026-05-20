<div class="table-responsive">
    <div class="p-2" style="border: 2px silver solid; border-radius: 10px;">
        <p style="display: unset;"><strong class="text-dark">URL : </strong> <a href="{{ $url }}"
                                                                                target="_blank">{{ $url }}</a></p>
    </div>
    @foreach ($densities as $n => $phrases)
    <div class="mt-4 mb-4 text-center">
        <h5>{{ $n }} Word Density</h5>
    </div>
    <table class="table table-hover">
        <thead>
        <tr>
            <th class="bg-secondary text-white">Phrase</th>
            <th class="bg-secondary text-white">Count</th>
            <th class="bg-secondary text-white">Frequency (%)</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($phrases as $phrase => $percentage)
        @php
        $count = round(($percentage * ($totalWords - $n + 1)) / 100);
        @endphp
        <tr>
            <td>{{ $phrase }}</td>
            <td>{{ $count }}</td>
            <td>{{ $percentage }}%</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endforeach
</div>