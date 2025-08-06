<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Mail\Feedback\SendedFeedbackMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            // Обязательные поля
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/',
            'consentPrivacy' => 'required|boolean',
            //'url' => 'required|string|url',
        ]);

        // Получаем адрес из файла конфигурации
        $receiverEmail = config('mail.to.address');


        Mail::to($receiverEmail)
            ->send(new SendedFeedbackMail($data));

        return response('OK', 200);
    }
}
