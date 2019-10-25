<?php

namespace Yuyu\FileManager\Middleware;

use Closure;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;

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
        try{
            $arrToken = json_decode(decrypt($request->_token), 1);
        }
        catch(\Exception $e){
            abort(404);
        }
                
        // Validate token data
        $arrRule = [
            'id' => ['required', Rule::in([$request->route('attachmentId')])],
            'expire_at' => ['required', 'gte:' .(Carbon::now()->getTimestamp())],
            'type' => ['required', Rule::in(explode('/', $request->path()))]
        ];

        $validator = Validator::make($arrToken, $arrRule);

        if($validator->fails()){
            abort(404);
        }
        
        return $next($request);
    }
}
