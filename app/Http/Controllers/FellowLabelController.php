<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FellowLabel;

class FellowLabelController extends Controller
{
    public function index()
    {
        $labels = FellowLabel::orderBy('name')->get();
        $header_title = 'Fellow Labels – Settings';
        return view('admin.settings.fellow_labels.index', compact('labels', 'header_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:80|unique:fellow_labels,name',
            'color' => 'required|string|max:20',
        ]);

        FellowLabel::create([
            'name'        => $request->name,
            'color'       => $request->color,
            'description' => $request->description,
            'is_active'   => 1,
        ]);

        return redirect('admin/settings/fellow-labels')->with('success', 'Label "' . $request->name . '" created.');
    }

    public function update(Request $request, $id)
    {
        $label = FellowLabel::findOrFail($id);
        $request->validate([
            'name'  => 'required|string|max:80|unique:fellow_labels,name,' . $id,
            'color' => 'required|string|max:20',
        ]);

        $label->update([
            'name'        => $request->name,
            'color'       => $request->color,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect('admin/settings/fellow-labels')->with('success', 'Label updated.');
    }

    public function destroy($id)
    {
        FellowLabel::findOrFail($id)->delete();
        return redirect('admin/settings/fellow-labels')->with('success', 'Label deleted.');
    }
}
