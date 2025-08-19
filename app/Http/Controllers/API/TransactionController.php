<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['account', 'entity', 'building', 'unit', 'lease', 'tenant', 'category'])->get();
        return response()->json(['success' => true, 'data' => $transactions], 200);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['account', 'entity', 'building', 'unit', 'lease', 'tenant', 'category'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $transaction], 200);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $validated = $request->validate([
            'entity_id' => 'nullable|exists:entities,id',
            'building_id' => 'nullable|exists:buildings,id',
            'unit_id' => 'nullable|exists:units,id',
            'lease_id' => 'nullable|exists:leases,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'category_id' => 'nullable|exists:transaction_categories,id',
            'status' => 'in:to_categorize,to_validate,validated,archived',
        ]);
        if (isset($validated['status']) && $validated['status'] === 'validated') {
            // Optional: Add additional validation logic if needed
        }
        $transaction->update($validated);
        return response()->json(['success' => true, 'data' => $transaction], 200);
    }

    public function archive($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'archived']);
        return response()->json(['success' => true, 'message' => 'Transaction archived'], 200);
    }
}
