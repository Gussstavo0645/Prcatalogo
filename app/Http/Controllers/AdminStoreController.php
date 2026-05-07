<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class AdminStoreController extends Controller
{
    public function index()
    {
        $stores = Store::orderByDesc('id')->get();
        return view('admin.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'hours' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        $data['activo'] = $data['is_active'];
        $data['whatsapp_number'] = preg_replace('/\D/', '', $data['whatsapp_number'] ?? '');

        Store::create($data);

        return redirect()->route('admin.stores.index')
            ->with('ok', 'Tienda creada correctamente');
    }

    public function edit($id)
    {
        $store = Store::findOrFail($id);
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $r, $id)
    {
        $store = Store::findOrFail($id);

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'hours' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'is_active' => 'nullable',
        ]);

        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        $data['activo'] = $data['is_active'];
        $data['whatsapp_number'] = preg_replace('/\D/', '', $data['whatsapp_number'] ?? '');

        $store->update($data);

        return redirect()->route('admin.stores.index')
            ->with('ok', 'Tienda actualizada');
    }

    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return back()->with('ok', 'Tienda eliminada');
    }
}