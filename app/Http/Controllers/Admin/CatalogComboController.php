<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogo;
use App\Models\CatalogCombo;
use App\Models\CatalogComboItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogComboController extends Controller
{
    public function create(Catalogo $catalog)
    {
        return view('admin.catalogo.combos.create', compact('catalog'));
    }

    public function store(Request $request, Catalogo $catalog)
    {
        $data = $request->validate([
            'combo_code' => 'required|string|max:50',
            'combo_color' => 'required|string|max:20',
            'page_number' => 'required|integer|min:1',
            'position' => 'required|integer|min:1',
            'image' => 'nullable|image|max:4096',

            'items' => 'required|array|min:1',
            'items.*.product_code' => 'required|string|max:50',
            'items.*.product_color' => 'nullable|string|max:20',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $comboCode = trim((string) $data['combo_code']);
        $comboColor = trim((string) $data['combo_color']);

        $comboInvQuery = DB::connection('admin_ml')
            ->table('inventario as i')
            ->where('i.Codprod', $comboCode)
            ->where('i.mesyope', '04/2026')
            ->where('i.tipocatalogo', 'N');

        if ($comboColor !== '') {
            $comboInvQuery->where('i.color', $comboColor);
        }

        $comboInv = $comboInvQuery->select([
            'i.Codprod as code',
            'i.color as color',
            'i.Descripcion as name',
            'i.Precventa as price',
        ])->first();

        if (!$comboInv) {
            return back()->withInput()->withErrors([
                'combo_code' => 'No se encontró ese combo en inventario.',
            ]);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('catalog-combos', 'public');
        }

        DB::transaction(function () use ($catalog, $data, $comboInv, $comboCode, $comboColor, $imagePath) {
            $combo = CatalogCombo::create([
                'catalog_id' => $catalog->id,
                'code' => $comboCode,
                'color' => $comboColor,
                'name' => $comboInv->name ?: 'Combo sin descripción',
                'price' => (float) ($comboInv->price ?? 0),
                'image_path' => $imagePath,
                'page_number' => (int) $data['page_number'],
                'position' => (int) $data['position'],
            ]);

            foreach ($data['items'] as $row) {
                $productCode = trim((string) $row['product_code']);
                $productColor = trim((string) ($row['product_color'] ?? ''));
                $quantity = (int) $row['quantity'];

              $productoInvQuery = DB::connection('admin_ml')
    ->table('inventario as i')
    ->whereRaw('TRIM(i.Codprod) = ?', [$productCode]);

if ($productColor !== '') {
    $productoInvQuery->whereRaw('TRIM(i.color) = ?', [$productColor]);
}

$productoInv = $productoInvQuery->first();

                if (!$productoInv) {
                    throw new \Exception("No existe el producto interno {$productCode}-{$productColor} en inventario.");
                }

                CatalogComboItem::create([
                    'combo_id' => $combo->id,
                    'product_code' => $productCode,
                    'product_color' => $productColor,
                    'quantity' => $quantity,
                ]);
            }
        });

        return back()->with('success', 'Combo creado correctamente.');
    }

    public function destroy($id)
{
    $combo = \App\Models\CatalogCombo::findOrFail($id);
    $combo->delete();

    return response()->json([
        'ok' => true
    ]);
}
}