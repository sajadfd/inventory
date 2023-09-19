<?php

namespace App\Http\Controllers;

use App\Models\ApkVersion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApkVersionController extends Controller
{
    public function __construct()
    {
        app()->setLocale('en');
        $this->middleware(function (Request $request, Closure $next) {
            if (Session::get('AppVersionLoginKey') !== config('app.app_versions_page_key')) {
                return response()->redirectTo(route('apk-version.login-page'));
            }
            return $next($request);
        })->except(['loginPage', 'login', 'download', 'latest']);

        $this->middleware(function (Request $request, Closure $next) {
            if (Session::get('AppVersionLoginKey') === config('app.app_versions_page_key')) {
                return response()->redirectTo(route('apk-version.index'));
            }
            return $next($request);
        })->only(['loginPage', 'login']);
    }

    public function loginPage()
    {
        return view('pages.apk-versions-login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => ['required', function ($attr, $val, $fail) {
                if ($val !== config('app.app_versions_page_key')) {
                    $fail(__("Wrong Credentials"));
                }
            }]
        ]);
        if ($validator->fails()) {
            return response()->redirectTo(route('apk-version.login-page'))->withErrors($validator)->withInput();
        }

        Session::put('AppVersionLoginKey', $request->input('key'));

        return response()->redirectTo(route('apk-version.index'));
    }

    public function logout()
    {
        Session::forget('AppVersionLoginKey');
        return response()->redirectTo(route('apk-version.login-page'));
    }

    public function index()
    {
        return view('pages.apk-versions-index', ['apkVersions' => ApkVersion::paginate(1)]);
    }

    public function download(ApkVersion $apkVersion)
    {
        return Storage::download($apkVersion->file_path, $apkVersion->file_name);
    }

    public function latest(string $platform, string $channel)
    {
        return ApkVersion::where('platform', $platform)->where('channel', $channel)->orderByDesc('version')->firstOrFail();
    }

    public function destroy(ApkVersion $apkVersion)
    {
        Storage::delete($apkVersion->file_path);
        $apkVersion->delete();
        return response()->redirectTo(route('apk-version.index'))->with('success', 'Version Deleted')->withInput();
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'version' => 'numeric|gt:0',
            'channel' => 'required|in:stable,beta',
            'platform' => 'required|in:android,windows,ios',
            'file' => ['required', 'file', function ($attr, UploadedFile $val, $fail) {
                if (request('platform') === 'android' && $val->getClientOriginalExtension() !== 'apk') {
                    $fail(__('File must be of apk type'));
                }
            }],
            'notes' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->redirectTo(route('apk-version.index'))->withErrors($validator)->withInput();
        }
        $data = $validator->validated();


        $apkVersion = ApkVersion::updateOrCreate([
            'version' => $data['version'],
            'channel' => $data['channel'],
            'platform' => $data['platform'],
        ], [
            'version' => $data['version'],
            'notes' => $data['notes'],
            'channel' => $data['channel'],
            'platform' => $data['platform'],
            'file_path' => $request->file('file')->store('apk_versions'),
            'file_name' => $request->file('file')->getClientOriginalName()
        ]);
        return response()->redirectTo(route('apk-version.index'))->with('success', $apkVersion->wasRecentlyCreated ? 'Version Created' : 'Version Updated');
    }


}
