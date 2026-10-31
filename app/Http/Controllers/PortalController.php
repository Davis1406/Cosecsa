<?php

namespace App\Http\Controllers;

use App\Services\PortalData;
use Illuminate\Support\Facades\Auth;

/**
 * Landing pages for the roles that have no working area of their own in the MIS: Candidate, Programme
 * Director, Country Representative and Member. Each shows that person's own record and what they oversee.
 */
class PortalController extends Controller
{
    public function __construct(private PortalData $data) {}

    public function candidate()
    {
        return $this->show('portal.candidate', 'My Exam Entry', $this->data->candidate(Auth::id()));
    }

    public function member()
    {
        return $this->show('portal.member', 'My Membership', $this->data->member(Auth::id()));
    }

    public function programmeDirector()
    {
        return $this->show('portal.programme-director', 'My Hospital', $this->data->programmeDirector(Auth::id()));
    }

    public function countryRep()
    {
        return $this->show('portal.country-rep', 'My Country', $this->data->countryRep(Auth::id()));
    }

    private function show(string $view, string $title, array $data)
    {
        $user = Auth::user();

        return view($view, $data + [
            'header_title' => $title,
            'userRoles' => $user->getRoles(),
            'activeRole' => $user->getActiveRole(),
            'photo' => $user->photoUrl(),
        ]);
    }
}
