<?php

namespace App\Http\Middleware;

use App\Http\ApiResponse;
use App\Http\Requests\Profile\CreateRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadFilesMiddleware
{
    public function handle(Request $request, Closure $next,$type): Response
    {
        try {
            if (request()->hasFile('file')) {
                $fileMimeType = strtolower(request()->file('file')->extension()) ;
                $file=request()->file('file');
                $fileName="";
                if($fileMimeType == 'pdf' && $type !='avatar'){
                    $fileName=$file->store('/documents','upload_files');
                }else if($fileMimeType == 'jpg' || $fileMimeType == 'jpeg' || $fileMimeType == 'png') {
                    $fileName = $file->store('/images', 'upload_files');
                }else{
                    return  ApiResponse::error(t('unsupoted file'),422) ;
                }// end of if


                $request->merge([
                    'path'=>'files/'.$fileName,
                    'fileName'=>$fileName,
                    'extension' =>$fileMimeType,
                    'originalname'=>$file->getClientOriginalName(),
                    'type'=>$type
                ]) ;
            }//end of if

            return $next($request);
        }catch (\Exception $e){
            return  ApiResponse::error(t('wrong'),500);
        }
    }
}
