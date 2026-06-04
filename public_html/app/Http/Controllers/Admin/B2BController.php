<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\B2B\Models\Company;
use Modules\B2B\Models\RFQ;
use Modules\B2B\Models\Invoice;
use Modules\B2B\Models\CreditTransaction;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    public function companies()
    {
        $companies = Company::with('user')->latest()->paginate(20);
        return view('admin.b2b.companies', compact('companies'));
    }

    public function companyShow(Company $company)
    {
        $company->load('user', 'invoices', 'creditTransactions');
        return view('admin.b2b.company-show', compact('company'));
    }

    public function companyApprove(Company $company)
    {
        $company->update(['status' => 'approved']);
        return back()->with('success', 'Company approved.');
    }

    public function companyReject(Request $request, Company $company)
    {
        $request->validate(['rejection_reason' => 'nullable|string']);
        $company->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);
        return back()->with('success', 'Company rejected.');
    }

    public function rfqs()
    {
        $rfqs = RFQ::with('company')->latest()->paginate(20);
        return view('admin.b2b.rfqs', compact('rfqs'));
    }

    public function rfqShow(RFQ $rfq)
    {
        $rfq->load('company', 'items');
        return view('admin.b2b.rfq-show', compact('rfq'));
    }

    public function rfqUpdateStatus(Request $request, RFQ $rfq)
    {
        $request->validate(['status' => 'required|in:pending,quoted,accepted,rejected']);
        $rfq->update(['status' => $request->status]);
        return back()->with('success', 'RFQ status updated.');
    }

    public function invoices()
    {
        $invoices = Invoice::with('company')->latest()->paginate(20);
        return view('admin.b2b.invoices', compact('invoices'));
    }

    public function invoiceShow(Invoice $invoice)
    {
        $invoice->load('company', 'items');
        return view('admin.b2b.invoice-show', compact('invoice'));
    }
}
