@extends('admin.layouts.app')
@section('title', 'تقرير العملاء')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-users text-pink"></i> تقرير العملاء</h3>
            <p class="text-muted mb-0">نشاط العملاء وتحليل الطلبات والإنفاق</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.users.export', request()->all()) }}" class="btn btn-success rounded-pill"><i class="fas fa-file-excel"></i> تصدير Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-right"></i> العودة</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">بحث</label>
                    <input type="text" name="search" class="form-control rounded-3" placeholder="ابحث بالاسم أو البريد..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-pink rounded-pill"><i class="fas fa-search"></i> بحث</button>
                    <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>العميل</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>عدد الطلبات</th>
                        <th>إجمالي الإنفاق</th>
                        <th>تاريخ التسجيل</th>
                        <th class="pe-3">آخر دخول</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-3 fw-bold">{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-pink bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                    <i class="fas fa-user text-pink small"></i>
                                </div>
                                <span class="fw-bold">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td><span class="badge bg-pink rounded-pill fw-bold">{{ $user->orders_count }}</span></td>
                        <td class="fw-bold {{ $user->total_spent > 1000 ? 'text-success' : '' }}">{{ number_format($user->total_spent ?? 0, 2) }} ₪</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="pe-3">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">لا يوجد عملاء</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-transparent border-0 px-3 py-2">{{ $users->links() }}</div>
        @endif
    </div>
</div>

<style>
.text-pink { color: #d97a8c !important; }
.bg-pink { background: #d97a8c !important; }
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c56174; color: #fff; }
</style>
@endsection
