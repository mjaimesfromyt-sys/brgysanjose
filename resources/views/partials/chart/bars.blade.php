{{--
    Magnitude comparison as horizontal bars. Categories are nominal (facility
    names), so every bar takes the SAME slot-1 hue — colouring them by value
    would re-encode what bar length already shows.

    Single series, so no legend box. Value sits at the bar tip.

    $rows : [['label' => 'Barangay Hall', 'value' => 12], ...]
--}}
@php
    $vizMax = max(1, collect($rows)->max('value') ?: 0);
@endphp

<div class="viz">
    @if (collect($rows)->sum('value') === 0)
        <p class="text-muted mb-0">No approved bookings yet.</p>
    @else
        <div class="viz-rows" role="img"
             aria-label="Approved bookings per facility: {{ collect($rows)->map(fn ($r) => "{$r['label']} {$r['value']}")->join(', ') }}">
            @foreach ($rows as $row)
                <div class="viz-rows__row">
                    <span class="viz-rows__label" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                    <span class="viz-rows__track">
                        <span class="viz-rows__bar"
                              style="width: {{ $row['value'] === 0 ? 0 : max(2, round($row['value'] / $vizMax * 100, 2)) }}%"
                              title="{{ $row['label'] }}: {{ $row['value'] }} approved {{ Str::plural('booking', $row['value']) }}">
                        </span>
                    </span>
                    <span class="viz-rows__value">{{ number_format($row['value']) }}</span>
                </div>
            @endforeach
        </div>

        <details class="viz-table">
            <summary>View as table</summary>
            <table>
                <thead><tr><th>Facility</th><th>Approved bookings</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>{{ number_format($row['value']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    @endif
</div>
