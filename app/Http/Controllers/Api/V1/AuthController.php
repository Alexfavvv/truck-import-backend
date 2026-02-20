<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    #[OA\Post(
        path: '/v1/auth/register',
        summary: 'Регистрация нового заказчика (admin_customer)',
        description: 'Публичная регистрация. Создаёт пользователя с ролью **admin_customer**. После успешной регистрации возвращает Sanctum-токен для дальнейшей работы с API.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password', 'password_confirmation', 'phone'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'company_ivanov', description: 'Уникальный логин (буквы, цифры, дефис, подчёркивание)'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123', description: 'Минимум 8 символов'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'phone', type: 'string', example: '+7 (999) 123-45-67'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'ivanov@company.ru'),
                    new OA\Property(property: 'full_name', type: 'string', nullable: true, example: 'Иванов Иван Иванович'),
                    new OA\Property(property: 'company', type: 'string', nullable: true, example: 'ООО Ромашка'),
                    new OA\Property(property: 'position', type: 'string', nullable: true, example: 'Директор'),
                    new OA\Property(property: 'requisites', type: 'string', nullable: true, example: 'ИНН 7701234567, КПП 770101001'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Успешная регистрация',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации (логин занят, пароли не совпадают и т.д.)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|unique:users,email',
            'full_name'  => 'nullable|string|max:255',
            'company'    => 'nullable|string|max:255',
            'requisites' => 'nullable|string|max:2000',
            'position'   => 'nullable|string|max:255',
            'login'      => 'required|string|max:100|unique:users,login|alpha_dash',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $user = $this->userService->createAdminCustomer($validated);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token'       => $token,
            'token_type'  => 'Bearer',
            'user'        => $this->formatUser($user),
        ], 201);
    }

    #[OA\Post(
        path: '/v1/auth/login',
        summary: 'Вход в систему (все роли)',
        description: <<<'DESC'
Универсальный логин для всех ролей. Принимает логин или email + пароль, возвращает Sanctum-токен.

**Роли и права:**

| Роль | Значение | Описание |
|------|----------|---------|
| `admin_truck` | Администратор Трак Импорт | Полный доступ: пользователи, заказы, счета |
| `manager_truck` | Менеджер Трак Импорт | Заказы и счета; не может управлять пользователями |
| `admin_customer` | Администратор Заказчика | Свои заказы/счета; управляет своими менеджерами |
| `manager_customer` | Менеджер Заказчика | Видит заказы своей компании; не управляет пользователями |

Как назначается роль:
- `admin_truck` создаётся через seeder (один на систему)
- `manager_truck` создаётся через админ-панель пользователем с ролью `admin_truck`
- `admin_customer` регистрируется самостоятельно (`POST /v1/auth/register`)
- `manager_customer` создаётся через кабинет пользователем с ролью `admin_customer`
DESC,
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'admin', description: 'Логин или email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный вход',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Неверный логин или пароль / аккаунт деактивирован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Неверный логин или пароль'),
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверный логин или пароль'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Аккаунт деактивирован'], 401);
        }

        // Удаляем старые токены (опционально — один активный токен)
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $this->formatUser($user),
        ]);
    }

    #[OA\Post(
        path: '/v1/auth/logout',
        summary: 'Выход из системы (отзыв токена)',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Токен отозван',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Выход выполнен'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не авторизован', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Выход выполнен']);
    }

    #[OA\Get(
        path: '/v1/auth/me',
        summary: 'Текущий пользователь',
        description: 'Возвращает данные авторизованного пользователя, включая его роль.',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные пользователя',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthUser')
            ),
            new OA\Response(response: 401, description: 'Не авторизован', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'login'       => $user->login,
            'email'       => $user->email,
            'role'        => $user->role->value,
            'role_label'  => $user->role->label(),
            'full_name'   => $user->full_name,
            'company'     => $user->company,
            'phone'       => $user->phone,
            'customer_id' => $user->customer_id,
            'is_active'   => $user->is_active,
        ];
    }
}
