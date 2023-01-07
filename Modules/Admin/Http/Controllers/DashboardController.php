<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Log;
use Modules\Admin\Entities\AdminEntity;
use App\Services\SessionService;
use Modules\User\Models\User;
use Redirect;

class DashboardController extends Controller
{
    protected $data = []; // the information we send to the view
    protected $sessionService = '';

    protected $adminEntity = '';

    public function __construct()
    {
        $this->sessionService = new SessionService();
        $this->adminEntity = new AdminEntity();
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboard()
    {
        $userData = $this->sessionService->getAdminUserSession();

        $this->data['pageTitle'] = 'Dashboard';
        $this->data['userData'] = $userData;

        $users = $this->adminEntity->index();
        $this->data['records'] = '';
        if ($users['_metadata']['outcomeCode'] == 200) {
            $this->data['records'] = $users['records'];
        }
        return view('admin.dashboard', $this->data);
    }

    public function deletedUsers()
    {
        $userData = $this->sessionService->getAdminUserSession();

        $this->data['pageTitle'] = 'Dashboard';
        $this->data['userData'] = $userData;

        $users = $this->adminEntity->deleted();
        $this->data['records'] = '';
        if ($users['_metadata']['outcomeCode'] == 200) {
            $this->data['records'] = $users['records'];
        }
        return view('admin.deleted-users', $this->data);
    }

    public function editUser(Request $request, $id){

        $userData = User::where('id', $id)->first();
        $this->data['userData'] = $userData;

        return view('admin.users.editUser', $this->data);
    }

    public function updateUser(Request $request, $id){

        $userData = $this->sessionService->getAdminUserSession();
        $this->data['userData'] = $userData;

        $responseData = User::where('id', $id)->update([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email')
        ]);
        if (!empty($responseData)) {
            return Redirect()->route('userEdit', $id)
                ->with('messageCode', 200)
                ->with('message', 'User Updated');
        } else {
            $errors = [];
            return Redirect()->route('admin.users.editUser', $id)->withInput()->withErrors($errors)->with('message', 'Error in user');
        }
    }
}
