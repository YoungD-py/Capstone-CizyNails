<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Subtype;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = Type::with('subtypes')->get();
        return view('admin.types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types',
            'description' => 'nullable|string',
            'staff_count' => 'required|integer|min:1|max:10',
            'subtypes' => 'nullable|array',
            'subtypes.*.name' => 'required_with:subtypes|string|max:255',
            'subtypes.*.description' => 'nullable|string',
        ]);

        $type = Type::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'staff_count' => $validated['staff_count'],
        ]);

        if (isset($validated['subtypes']) && !empty($validated['subtypes'])) {
            foreach ($validated['subtypes'] as $subtype) {
                $type->subtypes()->create([
                    'name' => $subtype['name'],
                    'description' => $subtype['description'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.types.index')->with('success', 'Type created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Type $type)
    {
        $type->load('subtypes');
        return view('admin.types.show', compact('type'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Type $type)
    {
        $type->load('subtypes');
        return view('admin.types.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type $type)
    {
        // Filter out empty subtypes before validation
        if ($request->has('subtypes')) {
            $subtypes = array_filter($request->subtypes, function($subtype) {
                return !empty($subtype['name']);
            });
            $request->merge(['subtypes' => array_values($subtypes)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:types,name,' . $type->id,
            'description' => 'nullable|string',
            'staff_count' => 'required|integer|min:1|max:10',
            'subtypes' => 'nullable|array',
            'subtypes.*.id' => 'nullable|exists:subtypes,id',
            'subtypes.*.name' => 'required|string|max:255',
            'subtypes.*.description' => 'nullable|string',
        ]);

        $type->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'staff_count' => $validated['staff_count'],
        ]);

        // Handle subtypes
        if (isset($validated['subtypes'])) {
            $subtypeIds = [];
            foreach ($validated['subtypes'] as $subtypeData) {
                if (isset($subtypeData['id']) && $subtypeData['id']) {
                    // Update existing subtype
                    $subtype = Subtype::find($subtypeData['id']);
                    $subtype->update([
                        'name' => $subtypeData['name'],
                        'description' => $subtypeData['description'] ?? null,
                    ]);
                    $subtypeIds[] = $subtype->id;
                } else {
                    // Create new subtype
                    $subtype = $type->subtypes()->create([
                        'name' => $subtypeData['name'],
                        'description' => $subtypeData['description'] ?? null,
                    ]);
                    $subtypeIds[] = $subtype->id;
                }
            }
            // Delete subtypes not in the request
            $type->subtypes()->whereNotIn('id', $subtypeIds)->delete();
        } else {
            // Delete all subtypes if none provided
            $type->subtypes()->delete();
        }

        return redirect()->route('admin.types.index')->with('success', 'Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type $type)
    {
        // Check if any service uses this type
        if ($type->services()->exists()) {
            return back()->with('error', 'Cannot delete type with associated services!');
        }

        $type->delete();
        return redirect()->route('admin.types.index')->with('success', 'Type deleted successfully!');
    }
}

