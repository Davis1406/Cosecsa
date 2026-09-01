<?php

namespace App\Http\Controllers\Concerns;

// Shared helper for pulling an associate's notes log (see the API's
// AssociateNoteController) into a profile-view controller. Requires the
// using class to have an injected `ApiClient $api` property, same as every
// associate controller already does.
trait FetchesAssociateNotes
{
    protected function associateNotes(string $type, $id): array
    {
        $response = $this->api->get("associate-notes/{$type}/{$id}");

        return (array) ($response->object()->notes ?? []);
    }
}
