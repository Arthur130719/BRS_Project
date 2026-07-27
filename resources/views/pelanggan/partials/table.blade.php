<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox" id="selectAllCheckbox"></th>
                <th style="width:40px;">#</th>
                <th><a href="#" class="sortable" data-sort="nama">Nama / Username PPPoE <i class="fas fa-sort"></i></a></th>
                <th><a href="#" class="sortable" data-sort="paket_harga">Paket <i class="fas fa-sort"></i></a></th>
                <th><a href="#" class="sortable" data-sort="nas_id">NAS <i class="fas fa-sort"></i></a></th>
                <th><a href="#" class="sortable" data-sort="ip_address">IP Address <i class="fas fa-sort"></i></a></th>
                <th><a href="#" class="sortable" data-sort="status">Status <i class="fas fa-sort"></i></a></th>
                <th><a href="#" class="sortable" data-sort="tgl_jatuh_tempo">Jatuh Tempo <i class="fas fa-sort"></i></a></th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggans as $i => $p)
            <tr>
                {{-- Checkbox --}}
                <td><input type="checkbox" class="row-checkbox" value="{{ $p->id }}" onchange="updateBulkGenerate()"></td>

                {{-- No --}}
                <td class="mono-mute">{{ $pelanggans->firstItem() + $i }}</td>

                {{-- Nama / Username --}}
                <td>
                    <div style="font-weight:500;color:var(--text-1);">{{ $p->nama }}</div>
                    <div class="mono-mute" style="margin-top:2px;">{{ $p->username_pppoe }}</div>
                </td>

                {{-- Paket --}}
                <td>
                    @if($p->paket)
                        <a href="#" 
                           onclick="document.getElementById('paketFilter').value='{{ $p->paket->id }}'; document.getElementById('paketFilter').dispatchEvent(new Event('change')); return false;" 
                           style="font-size:13px;color:var(--indigo);text-decoration:underline;font-weight:500;">
                            {{ $p->paket->nama }}
                        </a>
                        <div class="mono-mute" style="font-size:11px;">
                            {{ $p->paket->kecepatan_down }}↓ / {{ $p->paket->kecepatan_up }}↑ Mbps
                            @if($p->paket->mikrotik_profile)
                            &bull; <span>{{ $p->paket->mikrotik_profile }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-mute">—</span>
                    @endif
                </td>

                {{-- NAS --}}
                <td>
                    @if($p->nas)
                        <a href="#" 
                           onclick="document.getElementById('nasFilter').value='{{ $p->nas->id }}'; document.getElementById('nasFilter').dispatchEvent(new Event('change')); return false;" 
                           style="font-size:12px;color:var(--indigo);text-decoration:underline;font-weight:500;">
                            {{ $p->nas->nama }}
                        </a>
                    @else
                        <span class="text-mute">—</span>
                    @endif
                </td>

                {{-- IP Address --}}
                <td>
                    @if($p->ip_address)
                        <span class="mono">{{ $p->ip_address }}</span>
                    @else
                        <span class="mono-mute">—</span>
                    @endif
                </td>

                {{-- Status Badge --}}
                <td>{!! $p->status_badge !!}</td>

                {{-- Jatuh Tempo --}}
                <td>
                    @if($p->expiry)
                        @php 
                            $isExpired = $p->expiry->isPast(); 
                            $sisaHari = (int) now()->startOfDay()->diffInDays($p->expiry->startOfDay(), false);
                        @endphp
                        <span class="mono"
                              style="color: {{ $isExpired ? 'var(--red)' : ($sisaHari <= 7 && $sisaHari >= 0 ? 'var(--amber)' : 'var(--text-2)') }}">
                            {{ $p->expiry->format('d M Y') }}
                        </span>
                        @if($isExpired)
                            <div style="font-size:10px;color:var(--red);margin-top:2px;">Sudah lewat</div>
                        @elseif($sisaHari <= 7 && $sisaHari >= 0)
                            <div style="font-size:10px;color:var(--amber);margin-top:2px;">{{ $sisaHari }} hari lagi</div>
                        @endif
                    @else
                        <span class="mono-mute">—</span>
                    @endif
                </td>

                {{-- Aksi --}}
                <td style="text-align:right;">
                    <div style="display:inline-flex;gap:4px;align-items:center;">
                        {{-- Detail --}}
                        <a href="{{ route('pelanggan.show', $p->id) }}" class="btn btn-ghost btn-xs">
                            <i class="fas fa-eye"></i> Detail
                        </a>

                        {{-- Edit — admin & kasir --}}
                        @if(auth()->user()->hasRole(['admin', 'kasir']))
                            <a href="{{ route('pelanggan.edit', $p->id) }}" class="btn btn-ghost btn-xs">
                                <i class="fas fa-pen"></i>
                            </a>
                        @endif

                        {{-- Suspend / Aktifkan toggle — admin & kasir --}}
                        @if(auth()->user()->hasRole(['admin', 'kasir']))
                            @if($p->status === 'active')
                                <form method="POST" action="{{ route('pelanggan.suspend', $p->id) }}" style="display:contents;">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-warning btn-xs"
                                            onclick="return confirm('Isolir pelanggan {{ addslashes($p->nama) }}?')">
                                        <i class="fas fa-lock"></i> Isolir
                                    </button>
                                </form>
                            @elseif($p->status === 'suspend')
                                <form method="POST" action="{{ route('pelanggan.aktifkan', $p->id) }}" style="display:contents;">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-success btn-xs"
                                            onclick="return confirm('Aktifkan kembali pelanggan {{ addslashes($p->nama) }}?')">
                                        <i class="fas fa-lock-open"></i> Aktifkan
                                    </button>
                                </form>
                            @endif
                        @endif

                        {{-- Hapus — admin & kasir --}}
                        @if(auth()->user()->hasRole(['admin', 'kasir']))
                            <form method="POST" action="{{ route('pelanggan.destroy', $p->id) }}" style="display:contents;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-danger btn-xs"
                                        onclick="return confirm('Hapus pelanggan {{ addslashes($p->nama) }}? Tindakan ini tidak dapat dibatalkan!')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>Belum ada pelanggan</h3>
                        <p>
                            @if(request()->hasAny(['search','status','paket_id','nas_id']))
                                Tidak ada data yang cocok dengan filter pencarian Anda.
                            @else
                                Mulai tambahkan pelanggan dengan klik tombol "Tambah Pelanggan".
                            @endif
                        </p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Pagination ── --}}
@if($pelanggans->hasPages())
    {{ $pelanggans->appends(request()->except('page'))->links() }}
@endif
