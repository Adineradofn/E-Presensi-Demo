@extends('user.app_user')

@section('content')
 <div x-data="{in:false,out:false,loading:true}" x-init="
  fetch('{{ route('absensi.status.today') }}')
    .then(r=>r.json()).then(d=>{ in=d.checkin_enabled; out=d.checkout_enabled; })
    .finally(()=>loading=false);
  setInterval(()=>{ fetch('{{ route('absensi.status.today') }}').then(r=>r.json()).then(d=>{in=d.checkin_enabled; out=d.checkout_enabled;}); }, 30000);
">
  <template x-if="loading"><div class="p-3 rounded bg-gray-100">Memuat status…</div></template>

  <div class="grid sm:grid-cols-2 gap-3" x-show="!loading">
    <form method="POST" action="{{ route('absensi.checkin') }}">@csrf
      <button :disabled="!in" class="w-full px-6 py-4 rounded-xl text-white"
        :class="in?'bg-emerald-600 hover:bg-emerald-700':'bg-gray-300 cursor-not-allowed'">Absen Masuk</button>
    </form>
    <form method="POST" action="{{ route('absensi.checkout') }}">@csrf
      <button :disabled="!out" class="w-full px-6 py-4 rounded-xl text-white"
        :class="out?'bg-indigo-600 hover:bg-indigo-700':'bg-gray-300 cursor-not-allowed'">Absen Pulang</button>
    </form>
  </div>
</div>

@endsection
