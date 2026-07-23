<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Models\Country;
use Illuminate\Http\Request;

class MembersController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('members/list-data');
        $data = $response->object();

        return view('admin.associates.members.list', [
            'getMembers'   => collect($data->members ?? []),
            'header_title' => 'Members',
        ]);
    }

    public function import()
    {
        return view('admin.associates.members.import_members', [
            'header_title' => 'Import members',
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("members/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/members/list')->with('error', 'Member not found');
        }
        $data = $response->object();

        return view('admin.associates.members.view', [
            'member'       => $data->member,
            'header_title' => 'View Member',
        ]);
    }

    public function importMembers(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('members/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/members/list')->with('success', 'Members imported successfully');
    }

    public function add()
    {
        return view('admin.associates.members.add', [
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Add New Member',
        ]);
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'password',
            'personal_email', 'gender', 'status', 'category_id',
            'member_id_number', 'country_id', 'admission_year', 'membership_year',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile('members/', $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post('members/', $fields);
        }

        return redirect('admin/associates/members/list')->with('success', 'Members added successfully');
    }

    public function edit($id)
    {
        $response = $this->api->get("members/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/members/list')->with('error', 'Member not found');
        }
        $data = $response->object();

        return view('admin.associates.members.edit', [
            'member'       => $data->member,
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Edit Members',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'password',
            'personal_email', 'gender', 'status', 'category_id',
            'member_id_number', 'country_id', 'admission_year', 'membership_year',
            'phone_number',
        ]);

        if ($request->hasFile('profile_image')) {
            $this->api->postWithFile("members/{$id}", $fields, ['profile_image' => $request->file('profile_image')]);
        } else {
            $this->api->post("members/{$id}", $fields);
        }

        return redirect('admin/associates/members/list')->with('success', 'Member updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("members/{$id}");

        return redirect('admin/associates/members/list')->with('success', 'Member information successfully deleted');
    }
}
