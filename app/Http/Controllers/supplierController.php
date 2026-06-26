<?php

namespace App\Http\Controllers;
use App\Models\SupplierMaster;

class supplierController extends Controller
{
    public function index()
    {
        $suppliers = SupplierMaster::when(request('search'), function ($query) {
            $query->where('supplier_name', 'like', '%'.request('search').'%')
                ->orWhere('supplier_code', 'like', '%'.request('search').'%')
                ->orWhere('email', 'like', '%'.request('search').'%')
                ->orWhere('phone', 'like', '%'.request('search').'%');
        })
            ->latest('supplier_id')
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }
}
