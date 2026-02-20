<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Mail\Feedback\SendedFeedbackMail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

class FeedbackController extends Controller
{
    #[OA\Post(
        path: '/v1/client/send-feedback',
        summary: 'Отправить заявку / обратную связь',
        tags: ['Feedback'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'phone', 'consentPrivacy'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов', description: 'Имя заявителя'),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '+7 (999) 123-45-67',
                        description: 'Телефон в формате +7 (XXX) XXX-XX-XX'
                    ),
                    new OA\Property(property: 'consentPrivacy', type: 'boolean', example: true, description: 'Согласие на обработку персональных данных'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Заявка успешно отправлена'),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/',
            'consentPrivacy' => 'required|boolean',
        ]);

        $receiverEmail = config('mail.to.address');

        Mail::to($receiverEmail)
            ->send(new SendedFeedbackMail($data));

        return response('OK', 200);
    }
}
