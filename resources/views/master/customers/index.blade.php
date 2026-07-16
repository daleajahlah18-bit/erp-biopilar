
@extends("layouts.app")
@section("title", "Master Customer")
@section("page_title", "Data Customer")
@section("page_subtitle", "Manajemen Pelanggan")

@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Customer</h5>
        <a href="{{ route("master.customers.create") }}" class="btn-primary-custom px-3 py-2 text-decoration-none">
            <i class="bi bi-plus-lg"></i> Tambah Customer
        </a>
    </div>
    <div class="card-body">
        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-custom">
                <thead class="">
                    <tr>
                        <th>@sortablelink('code', 'Kode')</th>
                        <th>@sortablelink('name', 'Nama Customer')</th>
                        <th>@sortablelink('pic', 'PIC')</th>
                        <th>@sortablelink('telepon', 'Telepon')</th>
                        <th>@sortablelink('terms_(hari)', 'Terms (Hari)')</th>
                        <th>@sortablelink('status', 'Status')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $item)
                        <tr>
                            <td>{{ $item->customer_code }}</td>
                            <td>{{ $item->customer_name }}</td>
                            <td>{{ $item->customer_pic }}</td>
                            <td>{{ $item->customer_phone }}</td>
                            <td>{{ $item->payment_terms }} Hari</td>
                            <td>
                                @if($item->status == "Active")
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route("master.customers.edit", $item->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route("master.customers.destroy", $item->id) }}" method="POST" class="d-inline" onsubmit="event.preventDefault(); confirmDelete(() => this.submit(), 'Hapus Data?', 'Hapus data ini?')">
                                    @csrf @method("DELETE")
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
