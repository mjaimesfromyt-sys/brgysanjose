{{--
    Part-to-whole as a single horizontal stacked bar.

    Deliberately NOT a pie/donut: a donut is unreadable when segments are close
    in size, and these four counts routinely are.

    $segments : [['label' => 'Pending', 'value' => 12], ...]  (fixed order —
                a status keeps its colour slot even when its count is zero)
    $caption  : short description used for the accessible label
--}}
@php
    $vizTotal = collect($segments)->sum('value');
@endphp

<div class="viz">
    @if ($vizTotal === 0)
        <p class="text-muted mb-0">No document requests yet.</p>
    @else
        <div class="viz-stack" role="img"
             aria-label="{{ $caption ?? 'Breakdown' }}: {{ collect($segments)->map(fn ($s) => "{$s['label']} {$s['value']}")->join(', ') }}">
            @foreach ($segments as $i => $seg)
                @continue($seg['value'] <= 0)
                <div class="viz-stack__seg"
                     style="--seg: var(--series-{{ $i + 1 }}); width: {{ round($seg['value'] / $vizTotal * 100, 2) }}%"
                     title="{{ $seg['label'] }}: {{ number_format($seg['value']) }} ({{ round($seg['value'] / $vizTotal * 100) }}%)">
                </div>
            @endforeach
        </div>

        {{-- Legend carries identity AND value, so meaning never rests on colour
             alone — and it is the contrast relief for the lighter slots. --}}
        <ul class="viz-legend">
            @foreach ($segments as $i => $seg)
                <li>
                    <span class="viz-legend__swatch" style="--seg: var(--series-{{ $i + 1 }})"></span>
                    <span>{{ $seg['label'] }}</span>
                    <span class="viz-legend__value">{{ number_format($seg['value']) }}</span>
                </li>
            @endforeach
        </ul>

        <details class="viz-table">
            <summary>View as table</summary>
            <table>
                <thead><tr><th>Status</th><th>Requests</th><th>Share</th></tr></thead>
                <tbody>
                    @foreach ($segments as $seg)
                        <tr>
                            <td>{{ $seg['label'] }}</td>
                            <td>{{ number_format($seg['value']) }}</td>
                            <td>{{ round($seg['value'] / $vizTotal * 100) }}%</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th>{{ number_format($vizTotal) }}</th>
                        <th>100%</th>
                    </tr>
                </tbody>
            </table>
        </details>
    @endif
</div>
