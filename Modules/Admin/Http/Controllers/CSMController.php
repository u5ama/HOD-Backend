<?php

namespace Modules\Admin\Http\Controllers;

use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Modules\Admin\Models\CSM;
use File;
use Modules\User\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class CSMController extends Controller
{
    protected $data = []; // the information we send to the view
    protected $sessionService = '';

    protected $adminEntity = '';
    public function __construct()
    {
        $this->sessionService = new SessionService();
    }
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $userData = $this->sessionService->getAdminUserSession();

        $this->data['pageTitle'] = 'Dashboard';
        $this->data['userData'] = $userData;

        $this->data['records'] = CSM::all();
        return view('admin.csm.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $userData = $this->sessionService->getAdminUserSession();

        $this->data['pageTitle'] = 'Dashboard';
        $this->data['userData'] = $userData;
        $users = User::where('id','!=',1)->whereNull('deleted_at')->get();
        $this->data['users'] = $users;
        return view('admin.csm.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {
            $userData = $this->sessionService->getAdminUserSession();

            $this->data['userData'] = $userData;
            $this->data['userId'] = $userData['id'];

            if ($request->hasFile('user_image')) {
                $attachedFile = $request->user_image;
                $i = 0;

                foreach ($attachedFile as $file) {

                    $file = $attachedFile[$i];
                    $extension = $file->getClientOriginalExtension();

                    $file_size = $file->getSize();

                    $file_size = number_format($file_size / 1048576, 2);

                    $avatarName = 'avatar' . time() . '.' . $extension;

                    Storage::disk('local')->put($avatarName, File::get($file));

                    $url = URL::asset('storage/app').'/'.$avatarName;
                }

                $user = CSM::create([
                    'name' => $request->get('user_name'),
                    'image' => $url,
                    'email' => $request->get('email'),
                    'selected_user_id' => $request->get('select_user'),
                    'phone_number' => $request->get('phone_number'),
                ]);
                return response()->json(['success' => 'true'],200);
            }
        }
        catch(Exception $e)
        {
            Log::info($e->getMessage());
            return Redirect()->route('csm.create')->withInput()->withMessage('Problem in submission. Please try again.');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('admin::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $userData = $this->sessionService->getAdminUserSession();

        $this->data['pageTitle'] = 'Dashboard';
        $this->data['userData'] = $userData;
        $this->data['csm_id'] = $id;
        $users = User::where('id','!=',1)->whereNull('deleted_at')->get();
        $this->data['users'] = $users;
        $this->data['records'] = CSM::where('id', $id)->first();

        return view('admin.csm.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $userData = $this->sessionService->getAdminUserSession();

            $this->data['userData'] = $userData;
            $this->data['userId'] = $userData['id'];

            if ($request->hasFile('user_image')) {
                $attachedFile = $request->user_image;
                $i = 0;

                foreach ($attachedFile as $file) {

                    $file = $attachedFile[$i];
                    $extension = $file->getClientOriginalExtension();

                    $file_size = $file->getSize();

                    $file_size = number_format($file_size / 1048576, 2);

                    $avatarName = 'avatar' . time() . '.' . $extension;

                    Storage::disk('local')->put($avatarName, File::get($file));

                    $url = URL::asset('storage/app').'/'.$avatarName;
                }

                $user = CSM::where('id', $id)->update([
                    'name' => $request->get('user_name'),
                    'image' => $url,
                    'email' => $request->get('email'),
                    'selected_user_id' => $request->get('select_user'),
                    'phone_number' => $request->get('phone_number'),
                ]);
                return response()->json(['success' => 'true'],200);
            }
        }
        catch(Exception $e)
        {
            Log::info($e->getMessage());
            return Redirect()->route('csm.edit')->withInput()->withMessage('Problem in submission. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        CSM::where('id', $request->get('id'))->delete();
        return response()->json(['success' => 'true'],200);
    }

    public function getSupportData(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $response = CSM::where('selected_user_id', $userData['id'])->first();
        return response()->json(['success' => 'true','support' => $response],200);
    }
}
