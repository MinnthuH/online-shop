<?php

namespace App\Http\Controllers;

use File;
use Artisan;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // admin logout
    public function AdminDestroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $noti = [
            'message' => 'Logout Successfully',
            'alert-type' => 'info',
        ];

        return redirect('logout')->with($noti);
    } // End Method

    // amdin loutout page
    public function AdminLogoutPage()
    {
        return view('admin.admin_logout');
    } // End method

    // admin profile page
    public function AdminProfile()
    {
        $id = Auth::user()->id;
        $adminData = User::find($id);

        return view('admin.admin_profile', compact('adminData'));
    } //End method

    // admin profile stroe
    public function AdminProfileStore(Request $request)
    {
        $id = Auth::user()->id;
        $data = User::find($id);

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->photo = $request->photo;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/' . $data->photo)); // delete image form storage path
            $filename = date('YmdHi') . $file->getClientOriginalName(); // set unique id and name
            $file->move(public_path('upload/admin_images'), $filename); // store in path with filename
            $data['photo'] = $filename;
        }

        $data->save();
        $noti = [
            'message' => 'Admin Profile Updated Successfully',
            'alert-type' => 'success',
        ];
        return redirect()->back()->with($noti);
    } // End method

    // Admin Change Password
    public function AdminChangePassword()
    {
        return view('admin.change_password');
    } //End Method

    // Admin Upadate Password
    public function UpdatePassword(Request $request)
    {

        $validateData = $request->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required',
            'confirmPassowrd' => 'required|same:newPassword',
        ]);

        // Match Old Password and Update
        $hasedPassword = Auth::user()->password;
        if (Hash::check($request->oldPassword, $hasedPassword)) {
            $users = User::find(Auth::id());
            $users->password = bcrypt($request->newPassword);
            $users->save();

            $noti = [
                'message' => 'Password Change Successfully',
                'alert-type' => 'success',
            ];
            return redirect()->back()->with($noti);
        } else {
            $noti = [
                'message' => 'Old Password is not Match',
                'alert-type' => 'error',
            ];
            return redirect()->back()->with($noti);
        }
    } // End Method

    /////////////////// Admin User All Method /////////

    // all admin method
    public function AllAdmin()
    {
        $alladminuser = User::latest()->get();
        return view('backend.admin.all_admin', compact('alladminuser'));
    } // End Method

    // Add admin method
    public function AddAdmin()
    {
        $roles = Role::all();
        $shops = Shop::all();

        return view('backend.admin.add_admin', compact('roles','shops'));
    } // End Method

    // Store Admin Method
    public function StroeAdmin(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->shop_id = $request->shopId;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->roleId) {

            $user->assignRole($request->roleId);
        }
        $noti = [
            'message' => 'New Admin User Created Successfully',
            'alert-type' => 'success',
        ];
        return redirect()->route('all#admin')->with($noti);
    } // End Method

    // Edit Admin Method
    public function EditAdmin($id)
    {
        $shops = Shop::all();
        $roles = Role::all();
        $adminUser = User::findOrFail($id);
        return view('backend.admin.edit_admin', compact('roles', 'adminUser','shops'));
    } // End Method

    // Update Admin Method
    public function UpdateAdmin(Request $request)
    {

        $adminId = $request->id;

        $user = User::findOrFail($adminId);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->shop_id = $request->shopId;
        $user->save();

        $user->roles()->detach(); // previous role detach
        if ($request->roleId) {

            $user->assignRole($request->roleId);
        }
        $noti = [
            'message' => 'Admin User Updated Successfully',
            'alert-type' => 'success',
        ];
        return redirect()->route('all#admin')->with($noti);

    } // End Method

    // Delete Admin Method
    public function DeleteAdmin($id)
    {

        $user = User::findOrFail($id);
        if (!is_null($user)) {
            $user->delete();
        }
        $noti = [
            'message' => 'Admin User Deleted Successfully',
            'alert-type' => 'success',
        ];
        return redirect()->back()->with($noti);
    } // End Method

    //////////////////////Database Backup Method//////////////////////////
    public function DatabaseBackup()
    {
        return view('admin.db_backup')->with('files', File::allFiles(storage_path('/app/Pencil_POS')));
    } // End Method

    // Backup Now Method
    public function BackupNow()
    {
        Artisan::call('backup:run');

        $noti = [
            'message' => 'Database Backup Successfully',
            'alert-type' => 'success',
        ];
        return redirect()->back()->with($noti);

    } // End Method

    // Download Db
    public function DownloadDb($getFilename)
    {
        // Ensure the file exists before attempting to download
        $path = storage_path('app/Pencil_POS/' . $getFilename);
        if (!file_exists($path)) {
            $noti = [
                'message' => 'File not found.',
                'alert-type' => 'error',
            ];
            return redirect()->back()->with($noti);
        }

        return response()->download($path);
    }
    // Delete Database
    public function DeleteDb($getFilename)
    {
        // Ensure the file exists before attempting to delete
        $filePath = 'Pencil_POS/' . $getFilename;
        if (!Storage::exists($filePath)) {
            $noti = [
                'message' => 'File not found.',
                'alert-type' => 'error',
            ];
            return redirect()->back()->with($noti);
        }

        Storage::delete($filePath);

        $noti = [
            'message' => 'Database Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($noti);
    }
}
