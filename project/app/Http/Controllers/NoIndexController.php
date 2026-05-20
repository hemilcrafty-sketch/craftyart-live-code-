<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\HelperController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\Caricature\Attire;
use App\Models\Caricature\CaricatureCategory;
use App\Models\Category;
use App\Models\NewCategory;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\VirtualCategory;
use Illuminate\Http\Request;
use App\Models\Design;

class NoIndexController extends ApiController
{
    function checkNoindex(Request $request): array|string
    {

        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $id = $request->get('id');
        if ($id == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Parameters missing"));
        }

        if (str_starts_with($id, '/')) {
            $id = substr($id, 1);
        }

        $id = HelperController::extractAndRemoveTrailingNumber($id)['string'];

        $data = null;
        $redirectUrl = null;

        if (str_starts_with($id, "k/")) {
            $id = str_replace("k/", "", $id);
            $data = SpecialKeyword::where('name', $id)->where('status', '1')->first();
            $redirectUrl = PageSlugHistoryController::findOne($id,0);

        } else if (str_starts_with($id, "templates/p/")) {
            $id = str_replace("templates/p/", "", $id);
            $string_id = explode('-', $id)[0];
            $data = Design::where("string_id", $string_id)->first();
            if (!$data) {
                if (is_numeric($id)) {
                    $data = Design::where("id", $id)->first();
                }
            }
            if ($data && $data->id_name !== $id) {
                $redirectUrl = "https://www.craftyartapp.com/templates/p/$data->id_name";
            } else {
                $redirectUrl = PageSlugHistoryController::findOne($id,5);
            }
        } else if (str_starts_with($id, "templates/")) {
            $id = str_replace("templates/", "", $id);

            $models = [
                ["model" => NewCategory::class, "type" => 3],
                ["model" => Category::class, "type" => 4],
                ["model" => VirtualCategory::class, "type" => 6],
            ];

            foreach ($models as $modelData) {
                $data = $modelData['model']::where('id_name', $id)->first();
                if ($data) {
                    $redirectUrl = PageSlugHistoryController::findOne($id, $modelData['type']);
                    break;
                }
            }

            if (!$data) {
                foreach ($models as $modelData) {
                    $redirectUrl = PageSlugHistoryController::findOne($id, $modelData['type']);
                    if ($redirectUrl) break;
                }
            }
        } else if (str_starts_with($id, "caricature/p/")) {
            $id = str_replace("caricature/p/", "", $id);
            $string_id = explode('-', $id)[0];
            $data = Attire::where("string_id", $string_id)->first();
            if (!$data) if (is_numeric($id)) $data = Attire::where("id", $id)->first();
            if ($data && $data->id_name !== $id) {
                $redirectUrl = "https://www.craftyartapp.com/templates/p/$data->id_name";
            }
        } else if (str_starts_with($id, "caricature/")) {
            $id = str_replace("caricature/", "", $id);
            $data = CaricatureCategory::whereCatLink($id)->first();
        }  else {
            $data = SpecialPage::wherePageSlug($id)->first();
            $redirectUrl = PageSlugHistoryController::findOne($id,1);

            if (!$data && !$redirectUrl && $id && (str_starts_with($id, "blog/") || $id === "blog")) {
                $id = str_replace("blog/", "", $id);
                $redirectUrl = PageSlugHistoryController::findOne($id,2);
                if (!$redirectUrl) {
                    $redirectUrl = PageSlugHistoryController::findOne("blog",2);
                }
            }
        }

        if ($redirectUrl) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", ["redirectUrl" => $redirectUrl]));
        }

        if ($data && ($data->no_index == 1 || $data->status == 0)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", ["noIndex" => true]));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Error", ["id" => $id]));

    }
}

