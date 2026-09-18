<table class="sig-table">
    <tr>
        <td style="padding-left: 5px;">
            @if(!empty($onDutyName))
                <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 6px;">For: {{ $onDutyName }}/{{ $onDutyLabel ?? 'KAG. ON DUTY' }}</div>
            @endif
            <div style="font-size: 9.5pt; color: #333; margin-bottom: 28px;">Attested by:</div>
            <div class="sig-block"><div class="sig-name">{{ $captainName ?? 'JOSEFINA C. GURREA' }}</div><div class="sig-title">Punong Barangay</div></div>
        </td>
    </tr>
</table>
