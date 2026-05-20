<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Utils\ApiController;
use App\Http\Controllers\Utils\ResponseHandler;
use App\Http\Controllers\Utils\ResponseInterface;
use App\Models\ContactUsWeb;
use App\Models\Feedback;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;


class ContactUsController extends ApiController
{
    public function contactUs(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'email'       => 'required|email',
                'message'     => 'required|string',
                'system_info' => 'nullable|array',
            ]);

            $agent = new Agent();
            $systemInfo = [
                'ip'        => $request->ip(),
                'userAgent' => $request->header('User-Agent'),
                'device'    => $agent->device(),
                'platform'  => $agent->platform(),
                'browser'   => $agent->browser(),
                'desktop'   => $agent->isDesktop() ? 'Yes' : 'No',
                'mobile'    => $agent->isMobile() ? 'Yes' : 'No',
                'tablet'    => $agent->isTablet() ? 'Yes' : 'No',
            ];

            $frontendInfo = $request->input('system_info', []);
            if (!empty($frontendInfo)) {
                if (array_values($frontendInfo) === $frontendInfo) {
                    foreach ($frontendInfo as $item) {
                        if (is_array($item)) {
                            $systemInfo = array_merge($systemInfo, $item);
                        }
                    }
                } else {
                    $systemInfo = array_merge($systemInfo, $frontendInfo);
                }
            }

            // Save to DB
            ContactUsWeb::create([
                'user_id'      => $this->uid ?? null,
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'contact_no'   => $request->contact_no,
                'message'      => $validated['message'],
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->header('User-Agent'),
                'system_info'  => json_encode($systemInfo), // one column
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(200, true, "Your Request Received, We will contact as soon as possible")
            );

        } catch (ValidationException $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(422, false, "Validation Failed", $e->errors())
            );
        } catch (\Exception $e) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, "Something went wrong, please try again later.", [
                    "error" => $e->getMessage()
                ])
            );
        }
    }

    public function sendFeedback(Request $request): array|string  {

        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $message = $request->get('message');
        if (empty($message)) return $this->failed(msg: "Feedback required");

        $user_id = $this->uid;

        if ($user_id == null) $user_id = "Anonymous";

        $res = new Feedback();
        $res->user_id = $user_id;
        $res->feedback = $message;
        $res->save();

        return $this->successed(msg: 'Feedback sent successfully.');
    }
}
