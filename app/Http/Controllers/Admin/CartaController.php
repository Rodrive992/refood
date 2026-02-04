<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CartaCategoria;
use App\Models\CartaItem;

class CartaController extends Controller
{
    private function localId(): int
    {
        $idLocal = auth()->user()->id_local;

        abort_if(empty($idLocal), 403, 'Tu usuario no tiene un local asignado.');

        return (int) $idLocal;
    }

    public function index(Request $request)
    {
        $localId = $this->localId();
        $q = trim((string) $request->get('q', ''));

        // 1) IDs de categorías que matchean por nombre (solo cuando hay búsqueda)
        $catIdsByName = collect();
        if ($q !== '') {
            $catIdsByName = CartaCategoria::query()
                ->where('id_local', $localId)
                ->where('nombre', 'like', "%{$q}%")
                ->pluck('id');
        }

        // 2) Traer items:
        // - que matcheen por nombre/descripcion
        // - o que pertenezcan a categorías que matchean por nombre
        $itemsQuery = CartaItem::query()
            ->where('id_local', $localId);

        if ($q !== '') {
            $itemsQuery->where(function ($query) use ($q, $catIdsByName) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%");

                if ($catIdsByName->isNotEmpty()) {
                    $query->orWhereIn('id_categoria', $catIdsByName);
                }
            });
        }

        $items = $itemsQuery
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_categoria');

        // 3) Categorías:
        // - si no hay búsqueda: todas
        // - si hay búsqueda: las que matchean por nombre o que tengan items en el resultado
        $categoriasQuery = CartaCategoria::query()
            ->where('id_local', $localId);

        if ($q !== '') {
            $catIdsFromItems = $items->keys()
                ->filter(fn($k) => !is_null($k))
                ->values();

            $categoriasQuery->where(function ($query) use ($q, $catIdsFromItems) {
                $query->where('nombre', 'like', "%{$q}%");

                if ($catIdsFromItems->isNotEmpty()) {
                    $query->orWhereIn('id', $catIdsFromItems);
                }
            });
        }

        $categorias = $categoriasQuery
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('admin.carta.index', compact('categorias', 'items', 'q'));
    }

    // ==========================================================
    // CATEGORÍAS
    // ==========================================================

    public function storeCategoria(Request $request)
    {
        $localId = $this->localId();

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('carta_categorias', 'nombre')->where(fn($q) => $q->where('id_local', $localId))
            ],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        CartaCategoria::create([
            'id_local' => $localId,
            'nombre' => $data['nombre'],
            'orden' => $data['orden'] ?? 0,
            'activo' => (int)($data['activo'] ?? 1),
        ]);

        return back()->with('ok', 'Categoría creada.');
    }

    public function updateCategoria(Request $request, CartaCategoria $categoria)
    {
        $localId = $this->localId();
        abort_if((int)$categoria->id_local !== $localId, 404);

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('carta_categorias', 'nombre')
                    ->ignore($categoria->id)
                    ->where(fn($q) => $q->where('id_local', $localId))
            ],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $categoria->update([
            'nombre' => $data['nombre'],
            'orden' => $data['orden'] ?? $categoria->orden,
            'activo' => array_key_exists('activo', $data) ? (int)$data['activo'] : $categoria->activo,
        ]);

        return back()->with('ok', 'Categoría actualizada.');
    }

    public function destroyCategoria(CartaCategoria $categoria)
    {
        $localId = $this->localId();
        abort_if((int)$categoria->id_local !== $localId, 404);

        // opcional: al borrar categoría, items quedan con id_categoria NULL (por tu FK ON DELETE SET NULL)
        $categoria->delete();

        return back()->with('ok', 'Categoría eliminada.');
    }

    public function toggleCategoria(CartaCategoria $categoria)
    {
        $localId = $this->localId();
        abort_if((int)$categoria->id_local !== $localId, 404);

        $categoria->activo = !$categoria->activo;
        $categoria->save();

        return back()->with('ok', 'Estado de categoría actualizado.');
    }

    // ==========================================================
    // ITEMS
    // ==========================================================

    public function storeItem(Request $request)
    {
        $localId = $this->localId();

        $data = $request->validate([
            'id_categoria' => [
                'nullable',
                'integer',
                Rule::exists('carta_categorias', 'id')->where(fn($q) => $q->where('id_local', $localId)),
            ],
            'nombre' => [
                'required',
                'string',
                'max:180',
                Rule::unique('carta_items', 'nombre')->where(fn($q) => $q->where('id_local', $localId))
            ],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        CartaItem::create([
            'id_local' => $localId,
            'id_categoria' => $data['id_categoria'] ?? null,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'costo' => $data['costo'] ?? null,
            'orden' => $data['orden'] ?? 0,
            'activo' => (int)($data['activo'] ?? 1),
        ]);

        return back()->with('ok', 'Item creado.');
    }

    public function updateItem(Request $request, CartaItem $item)
    {
        $localId = $this->localId();
        abort_if((int)$item->id_local !== $localId, 404);

        $data = $request->validate([
            'id_categoria' => [
                'nullable',
                'integer',
                Rule::exists('carta_categorias', 'id')->where(fn($q) => $q->where('id_local', $localId)),
            ],
            'nombre' => [
                'required',
                'string',
                'max:180',
                Rule::unique('carta_items', 'nombre')
                    ->ignore($item->id)
                    ->where(fn($q) => $q->where('id_local', $localId))
            ],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'id_categoria' => $data['id_categoria'] ?? null,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'costo' => $data['costo'] ?? null,
            'orden' => $data['orden'] ?? $item->orden,
            'activo' => array_key_exists('activo', $data) ? (int)$data['activo'] : $item->activo,
        ]);

        return back()->with('ok', 'Item actualizado.');
    }

    public function destroyItem(CartaItem $item)
    {
        $localId = $this->localId();
        abort_if((int)$item->id_local !== $localId, 404);

        $item->delete();

        return back()->with('ok', 'Item eliminado.');
    }

    public function toggleItem(CartaItem $item)
    {
        $localId = $this->localId();
        abort_if((int)$item->id_local !== $localId, 404);

        $item->activo = !$item->activo;
        $item->save();

        return back()->with('ok', 'Estado de item actualizado.');
    }
}
