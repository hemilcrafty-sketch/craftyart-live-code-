<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\Utils\RoleManager;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\NewCategory;
use App\Models\PendingTask;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PendingTaskController extends AppBaseController
{
  public static function store($data, $tableName, $title, $desc, $route, $action = 'add', $recordId = null, $isAdminOrSeoManager = true, $idName = "", $type = 1, $changeLog = [])
  {
    if (!$isAdminOrSeoManager) {

      if (in_array($action, ['update', 'delete']) && $recordId) {
        $existingTask = PendingTask::where('table_name', $tableName)
          ->where('record_id', $recordId)
          ->where('emp_id', auth()->user()->id)
          ->whereIn('status', [0, 2])
          ->first();

        if ($existingTask) {
          if ($existingTask->status == 0) {
            // Pending
            return response()->json(['error' => 'One request already exists in pending.']);
          }
          if ($existingTask->status == 2) {
            // Rejected
            return response()->json(['error' => 'You already have a rejected request.']);
          }
        }
      }


      PendingTask::create([
        'string_id' => StorageUtils::getNewName(),
        'table_name' => $tableName,
        'record_id' => $action === 'update' || $action === 'delete' ? $recordId : null,
        'action' => $action,
        'changes_title' => $title,
        'changes_desc' => $desc,
        'preview_route' => $route,
        'change_log' => json_encode($changeLog),
        'data' => json_encode($data),
        'emp_id' => auth()->user()->id,
        'page_type' => $type,
        'id_name' => $idName
      ]);

      return response()->json(['success' => 'Change submitted for approval.']);
    }

    return self::applyChange($tableName, $action, $data, $recordId);
  }


  public static function updatePendingTask($pendingId, $pendingData, $changeLog = [])
  {
    $pendingTask = PendingTask::find($pendingId);

    if (!$pendingTask) {
      return response()->json(['error' => 'Pending task not found.'], 404);
    }

    $pendingTask->status = 0;
    $pendingTask->data = json_encode($pendingData);
    $pendingTask->change_log = json_encode($changeLog);
    $pendingTask->save();

    return response()->json(['success' => 'Pending task updated successfully.']);
  }

  public static function applyChange($tableName, $action, $data, $recordId = null)
  {
    try {
      $modelClass = '\\App\\Models\\' . Str::studly(Str::singular($tableName));

      if (!class_exists($modelClass)) {
        return response()->json(['error' => "Model for table $tableName not found."], 404);
      }

      switch ($action) {
        case 'add':
          $modelClass::create(
            $data instanceof \Illuminate\Database\Eloquent\Model ? $data->toArray() : $data
          );
          break;

        case 'update':
          if ($recordId) {
            $updateData = $data instanceof \Illuminate\Database\Eloquent\Model
              ? $data->toArray()
              : $data;

            $updateData = Arr::except($updateData, ['created_at', 'updated_at']);

            $modelClass::where('id', $recordId)->update($updateData);
          }
          break;

        case 'delete':
          if ($recordId) {
            $modelClass::where('id', $recordId)->delete();
          }
          break;

        default:
          return response()->json(['error' => 'Invalid action'], 400);
      }

      return response()->json(['success' => ucfirst($action) . 'applied successfully.']);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
    }
  }

public function approve(Request $request)
    {
        try {
            $pendingTask = PendingTask::find($request->id);

            if (!$pendingTask) {
                return response()->json(['error' => 'Pending task not found.'], 404);
            }

            $data = json_decode($pendingTask->data, true);
            if ($pendingTask->table_name === 'NewCategory' && $pendingTask->action === 'update'){
                $model = NewCategory::find($pendingTask->record_id);
                $oldParentCategoryID = $model->parent_category_id;
            }

            $result = self::applyChange(
                $pendingTask->table_name,
                $pendingTask->action,
                $data,
                $pendingTask->record_id
            );

            $responseData = $result->getData(true);

            if (isset($responseData['success'])) {
//                if ($pendingTask->table_name === 'NewCategory' && $pendingTask->action === 'update') {
//                    try {
//                        $model = NewCategory::find($pendingTask->record_id);
//                        if ($model) {
////              $oldParentCategoryID = null;
////                            NewCategoryController::handlePostSaveActions($model, $oldParentCategoryID ?? null);
//                        }
//                    } catch (\Exception $e) {
//                        return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
//                    }
//                }
                $pendingTask->status = 1;
                $pendingTask->save();
                return response()->json(['success' => 'Task approved and applied.']);
            }
            return $result;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
  public function reject(Request $request)
  {
    $pendingTask = PendingTask::find($request->id);

    if (!$pendingTask) {
      return response()->json(['error' => 'Pending task not found.'], 404);
    }

    $pendingTask->status = 2;
    $pendingTask->reason = $request->reason ?? null;
    $pendingTask->save();

    return response()->json(['success' => 'Task rejected successfully.']);
  }

  public function rejecteTask(Request $request)
  {
    if (RoleManager::isAdminOrSeoManager(Auth::user()->user_type)) {
      $datas = PendingTask::where('status', 2)->get();
    } elseif (RoleManager::isSeoExecutive(Auth::user()->user_type)) {
      $datas = PendingTask::where('status', 2)->where('emp_id', Auth::user()->id)->get();
    }

    return view('pending_item/rejecte_task', compact('datas'));
  }


  public function show(Request $request)
  {
    return view('pending_item/show_pending_item')->with('datas', PendingTask::where('status', 0)->get());
  }
}