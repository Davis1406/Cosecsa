<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemLogsController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'logins');

        $data['tab'] = $tab;
        $data['header_title'] = 'System Logs';

        if ($tab === 'changes') {
            $q = DB::table('activity_logs')->orderByDesc('id');
            if ($request->filled('model_type')) $q->where('model_type', $request->model_type);
            if ($request->filled('action'))     $q->where('action', $request->action);
            if ($request->filled('q')) {
                $like = '%' . $request->q . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('summary', 'like', $like)->orWhere('user_name', 'like', $like);
                });
            }
            $data['records'] = $q->paginate(50)->withQueryString();
            $data['modelTypes'] = DB::table('activity_logs')->distinct()->orderBy('model_type')->pluck('model_type');
        } elseif ($tab === 'emails') {
            $q = DB::table('email_logs')->orderByDesc('id');
            if ($request->filled('q')) {
                $like = '%' . $request->q . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('to_address', 'like', $like)->orWhere('subject', 'like', $like);
                });
            }
            $data['records'] = $q->paginate(50)->withQueryString();
        } else {
            $tab = 'logins';
            $data['tab'] = 'logins';
            $q = DB::table('login_logs')->orderByDesc('id');
            if ($request->filled('q')) {
                $like = '%' . $request->q . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)->orWhere('email', 'like', $like);
                });
            }
            $data['records'] = $q->paginate(50)->withQueryString();
        }

        return view('admin.logs.index', $data);
    }
}
