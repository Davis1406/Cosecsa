<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Examiners can log in with email or name
        $user = User::where(function ($q) use ($request) {
                $q->where('email', $request->login)
                  ->orWhere('name', $request->login);
            })
            ->whereHas('roles', fn($q) => $q->where('role_type', 9))
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $examiner = DB::table('examiners')->where('user_id', $user->id)->first();
        if (!$examiner) {
            return response()->json(['message' => 'Examiner profile not found.'], 403);
        }

        // Revoke old tokens and issue fresh one
        $user->tokens()->delete();
        $token = $user->createToken('examiner-app')->plainTextToken;

        $currentYearId = User::getCurrentYearId();
        $groups = $this->getExaminerGroups($examiner->id, $currentYearId);

        return response()->json([
            'token'         => $token,
            'examiner'      => [
                'id'          => $examiner->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'examiner_id' => $examiner->examiner_id,
                'specialty'   => $examiner->specialty,
            ],
            'groups'        => $groups,
            'exam_year_id'  => $currentYearId,
        ]);
    }

    public function me(Request $request)
    {
        $user     = $request->user();
        $examiner = DB::table('examiners')->where('user_id', $user->id)->first();
        if (!$examiner) {
            return response()->json(['message' => 'Examiner profile not found.'], 403);
        }

        $currentYearId = User::getCurrentYearId();
        $groups = $this->getExaminerGroups($examiner->id, $currentYearId);

        return response()->json([
            'examiner' => [
                'id'          => $examiner->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'examiner_id' => $examiner->examiner_id,
                'specialty'   => $examiner->specialty,
            ],
            'groups'       => $groups,
            'exam_year_id' => $currentYearId,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function getExaminerGroups(int $examinerId, ?int $yearId): \Illuminate\Support\Collection
    {
        return DB::table('examiners_groups')
            ->join('exams_groups', 'examiners_groups.id', '=', 'exams_groups.group_id')
            ->where('exams_groups.exm_id', $examinerId)
            ->where('exams_groups.year_id', $yearId)
            ->select('examiners_groups.id', 'examiners_groups.group_name')
            ->get();
    }
}
