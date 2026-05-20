<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Creator\Designer\DesignerType;
use App\Models\Creator\Designer\DesignerGoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Freelancer designer settings: types, goals (crafty_creator). */
class DesignerSystemSettingsController extends Controller
{
    public function typesIndex()
    {
        $types = DesignerType::orderBy('sort_order')->get();
        return view('creator.designer.designer_settings.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DesignerType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);
        return redirect()->back()->with('success', 'Type created.');
    }

    public function updateType(Request $request, $id)
    {
        $type = DesignerType::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $type->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? $type->sort_order,
        ]);
        return redirect()->back()->with('success', 'Type updated.');
    }

    public function deleteType($id)
    {
        DesignerType::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Type deleted.');
    }

    public function toggleTypeActive($id)
    {
        $type = DesignerType::findOrFail($id);
        $type->is_active = !$type->is_active;
        $type->save();
        return redirect()->back()->with('success', 'Type status updated.');
    }

    public function goalsIndex()
    {
        $goals = DesignerGoal::orderBy('sort_order')->get();
        return view('creator.designer.designer_settings.goals', compact('goals'));
    }

    public function storeGoal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DesignerGoal::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);
        return redirect()->back()->with('success', 'Goal created.');
    }

    public function updateGoal(Request $request, $id)
    {
        $goal = DesignerGoal::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $goal->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? $goal->sort_order,
        ]);
        return redirect()->back()->with('success', 'Goal updated.');
    }

    public function deleteGoal($id)
    {
        DesignerGoal::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Goal deleted.');
    }

    public function toggleGoalActive($id)
    {
        $goal = DesignerGoal::findOrFail($id);
        $goal->is_active = !$goal->is_active;
        $goal->save();
        return redirect()->back()->with('success', 'Goal status updated.');
    }
}
