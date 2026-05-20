<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Feedback;
use App\Models\Contact;
use App\Models\ContactUs;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FeedbackController extends AppBaseController
{

    public function index()
    {
    }

    public function showFeedbacks(Feedback $feedback)
    {
        return view('feedback/feedback')->with('feedbackArray', Feedback::all()->sortDesc());
    }


    public function showContacts(Request $request)
    {
        if ($request->ajax()) {
            $page = $request->input('page', 1);
            $perPage = 20;
            $contacts = ContactUs::select('id', 'user_id')
                ->with('userData')
                ->orderBy('id', 'DESC')
                ->get()
                ->unique('user_id');
            $total = $contacts->count();
            $contactsForCurrentPage = $contacts->forPage($page, $perPage);
            $contactArray = [];
            foreach ($contactsForCurrentPage as $contactRow) {
                $user = HelperController::getUserClass($contactRow->user_id);
                if ($user) {
                    $contactRow->user = $user;
                    $contactArray[] = $contactRow;
                }
            }

            $paginatedContacts = new LengthAwarePaginator(
                $contactArray,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json([
                'contacts' => $contactArray,
                'next_page_url' => $paginatedContacts->nextPageUrl(),
            ]);
        }

        return view('feedback/contact')->with('contactArray', []);
    }


    public function getFeedback(Feedback $feedback, $id)
    {
        $feedbackRow = Feedback::find($id);
        return response()->json([
            'success' => $feedbackRow->feedback
        ]);
    }

    public function getContact(Contact $contact, $id)
    {
        $contactRow = Contact::find($id);
        return response()->json([
            'success' => $contactRow->message
        ]);
    }

    public function getChatData(Request $request)
    {
        $datas = ContactUs::where('user_id', $request->get('user_id'))->get();
        if ($datas->count() == 0) {
            return "";
        }
        $output = '';
        foreach ($datas as $item) {

            $html = '';

            if ($item->from_user == 1) {
                if ($item->is_file == 1) {
                    $html = '<li class="clearfix upload-file">
                                <div class="chat-body clearfix">
                                    <div class="upload-file-box clearfix">
                                        <div class="left" style="height: auto;">
                                            <img src="' . HelperController::$mediaUrl . $item->message . '" alt="" />
                                        </div>
                                    </div>
                                    <div class="chat_time">' . $item->created_at . '</div>
                                </div>
                            </li>';
                } else {
                    $html = '<li class="clearfix">
                                <div class="chat-body clearfix">
                                    <p style="white-space: pre-line; margin: -20px 100px 5px 0;">' .
                        $item->message
                        . '</p>
                                    <div class="chat_time">' . $item->created_at . '</div>
                                </div>
                            </li>';
                }
            } else {
                if ($item->is_file == 1) {
                    $html = '<li class="clearfix upload-file admin_chat">
                                <div class="chat-body clearfix">
                                    <div class="upload-file-box clearfix">
                                        <div class="left" style="height: auto;">
                                            <img src="' . HelperController::$mediaUrl . $item->message . '" alt="" />
                                        </div>
                                    </div>
                                    <div class="chat_time">' . $item->created_at . '</div>
                                </div>
                            </li>';
                } else {
                    $html = '<li class="clearfix admin_chat">
                                <div class="chat-body clearfix">
                                    <p style="white-space: pre-line; margin: -20px 0 5px 100px;">' .
                        $item->message
                        . '</p>
                                    <div class="chat_time">' . $item->created_at . '</div>
                                </div>
                            </li>';
                }
            }

            $output .= $html;
        }

        return $output;
    }

    public function send_reply(Request $request)
    {

        $is_file = $request->get('is_file');

        $res = new ContactUs();
        $res->user_id = $request->get('user_id');
        $res->is_file = $is_file;
        $res->brand = "Crafty Art";

        if ($is_file == 1) {
            $photo_uri = $request->file('photo_uri');
            if ($photo_uri != null) {
                $height = Image::make($photo_uri)->height();
                $width = Image::make($photo_uri)->width();
                $bytes = random_bytes(20);
                $new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $photo_uri->getClientOriginalExtension();
                try {
                    unlink(storage_path("app/public/" . 'uploadedFiles/contact_ss/' . $new_name));
                } catch (\Exception $e) {
                }
                StorageUtils::storeAs($photo_uri, 'uploadedFiles/contact_ss', $new_name);
                $new_photo_uri = 'uploadedFiles/contact_ss/' . $new_name;
                $res->message = $new_photo_uri;
                $res->width = $width;
                $res->height = $height;
            }
        } else {
            $res->message = $request->get('message');
            $res->width = $request->get('width');
            $res->height = $request->get('height');
        }

        $res->from_user = 0;
        $res->save();


        return $this->getChatData($request);
    }
}