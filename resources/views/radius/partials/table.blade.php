@forelse($sessions as $session)
<tr>
    <td>
        <span class="mono">{{ $session['username'] }}</span>
    </td>
    <td>
        <span class="mono">{{ $session['ip_address'] }}</span>
    </td>
    <td>
        <span style="font-size:12px; color:var(--text-2);">{{ $session['nas_name'] }}</span>
    </td>
    <td>
        <span class="mono-mute">{{ $session['uptime'] }}</span>
    </td>
    <td>
        <span class="mono" style="color:var(--green);">
            {{ $session['download'] }}
        </span>
    </td>
    <td>
        <span class="mono" style="color:var(--sky);">
            {{ $session['upload'] }}
        </span>
    </td>
    <td>
        <span class="mono-mute">{{ $session['rate'] }}</span>
    </td>
    <td>
        <span class="mono-mute" style="font-size:11px;">{{ $session['caller_id'] }}</span>
    </td>
    <td>
        <form action="{{ route('radius.disconnect', $session['nas_id']) }}" method="POST" onsubmit="return confirm('Tendang {{ $session['username'] }} dari jaringan?');">
            @csrf
            <input type="hidden" name="username" value="{{ $session['username'] }}">
            <button class="btn btn-danger-outline btn-sm">
                <i class="fas fa-plug-circle-xmark"></i> Disconnect
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" style="text-align:center; padding:40px; color:var(--text-3);">
        <div style="font-size:40px; margin-bottom:10px;"><i class="fas fa-satellite-dish"></i></div>
        <div>Tidak ada sesi PPPoE aktif yang ditemukan di semua router MikroTik.</div>
    </td>
</tr>
@endforelse
