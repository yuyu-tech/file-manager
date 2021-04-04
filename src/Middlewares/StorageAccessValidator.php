<?php

namespace Yuyu\FileManager\Middlewares;

use Closure;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use DB;

class StorageAccessValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // decrypt token
        try {
            $query = 'SELECT AES_DECRYPT(FROM_BASE64(\''
            .$request->_token
            .'\'), \''
            .config('app.key', '8ofmRMK70z9QOc4qvhioF00ihd6gRCW7oHShSGzn')
            .'\') as token';

            $arrToken = json_decode(DB::select($query)[0]->token, 1);
        } catch (\Exception $e) {
            abort(404);
        }

        // Validation rule based on authentication type
        switch (strtoupper($arrToken['auth_type'])) {
            case 'GUEST':
                break;

            case 'USER':
                if (empty($request->user()->id)) {
                    abort('404');
                }

                $arrRule['user_id'] = ['required', 'integer', 'size:'.$request->user()->id];
                break;

            case 'SECURE':
                $arrRule['expire_at'] = ['required', 'gte:' .(Carbon::now()->getTimestamp())];
                break;
        }

        // Validate token data
        $arrRule['id'] = ['required', Rule::in([$request->route('attachmentId')])];
        $arrRule['type'] = ['required', Rule::in(explode('/', $request->path()))];

        $validator = Validator::make($arrToken, $arrRule);

        if ($validator->fails()) {
            abort(404);
        }

        return $next($request);
    }
}
