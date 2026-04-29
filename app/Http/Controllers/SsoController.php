<?php

namespace App\Http\Controllers;

use App\Services\SsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    /**
     * OAuth-подобная точка: залогиненный пользователь получает редирект на фронт с ?code=
     */
    public function oauthAuthorize(Request $request, SsoService $sso): RedirectResponse
    {
        $validated = $request->validate([
            'redirect_uri' => ['required', 'string', 'max:2000'],
            'state' => ['nullable', 'string', 'max:512'],
        ]);

        if (! $sso->isRedirectUriAllowed($validated['redirect_uri'])) {
            abort(400, 'Недопустимый redirect_uri. Проверьте SSO_ALLOWED_REDIRECT_PREFIXES на сервере.');
        }

        $user = Auth::user();
        $code = $sso->issueCodeForUser($user);

        $target = $validated['redirect_uri'];
        $sep = str_contains($target, '?') ? '&' : '?';
        $target .= $sep.'code='.rawurlencode($code);
        if (isset($validated['state']) && $validated['state'] !== '') {
            $target .= '&state='.rawurlencode($validated['state']);
        }

        return redirect()->away($target);
    }
}
