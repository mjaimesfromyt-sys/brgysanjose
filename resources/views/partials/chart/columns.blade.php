{{--
    Trend over time as columns. Single series, so no legend box — the card
    title says what is plotted.

    $points : [['label' => 'Feb', 'full' => 'February 2026', 'value' => 3], ...]
--}}
@php
    $vizPeak = collect($points)->max('value') ?: 0;
    // Round the axis up to a clean number so ticks read 0 / 4 / 8 rather than 0 / 3.5 / 7.
    $vizMax  = $vizPeak <= 4 ? 4 : (int) (ceil($vizPeak / 4) * 4);
@endphp

<div class="viz">
    <div class="viz-cols">
        <div class="viz-cols__ticks" aria-hidden="true">
            <span>{{ $vizMax }}</span>
            <span>{{ intdiv($vizMax, 2) }}</span>
            <span>0</span>
        </div>

        <div class="viz-cols__plot">
            <div class="viz-cols__grid" aria-hidden="true">
                <span style="top: 0"></span>
                <span style="top: 50%"></span>
            </div>

            <div class="viz-cols__bars" role="img"
                 aria-label="Requests per month: {{ collect($points)->map(fn ($p) => "{$p['full']} {$p['value']}")->join(', ') }}">
                @foreach ($points as $point)
                    <div class="viz-cols__slot">
                        <div class="viz-cols__bar {{ $point['value'] === 0 ? 'is-zero' : '' }}"
                             style="height: {{ $point['value'] === 0 ? 2 : max(2, round($point['value'] / $vizMax * 100, 2)) }}{{ $point['value'] === 0 ? 'px' : '%' }}"
                             title="{{ $point['full'] }}: {{ $point['value'] }} {{ Str::plural('request', $point['value']) }}">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="viz-cols__labels" aria-hidden="true">
                @foreach ($points as $point)
                    <span>{{ $point['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <details class="viz-table">
        <summary>View as table</summary>
        <table>
            <thead><tr><th>Month</th><th>Requests</th></tr></thead>
            <tbody>
                @foreach ($points as $point)
                    <tr>
                        <td>{{ $point['full'] }}</td>
                        <td>{{ number_format($point['value']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </details>
</div>
