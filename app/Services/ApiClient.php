<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

// Thin wrapper around Laravel's HTTP client for server-to-server calls
// to api.cosecsamis.org. All requests carry the internal machine token.
class ApiClient
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.cosecsa_api.url'), '/');
        $this->token   = config('services.cosecsa_api.token');
    }

    public function get(string $path, array $query = []): Response
    {
        return Http::withToken($this->token)
            ->timeout(30)
            ->get($this->url($path), $query);
    }

    public function post(string $path, array $data = []): Response
    {
        return Http::withToken($this->token)
            ->timeout(30)
            ->post($this->url($path), $data);
    }

    public function put(string $path, array $data = []): Response
    {
        return Http::withToken($this->token)
            ->timeout(30)
            ->put($this->url($path), $data);
    }

    public function delete(string $path): Response
    {
        return Http::withToken($this->token)
            ->timeout(30)
            ->delete($this->url($path));
    }

    // Multipart upload: sends scalar $data fields + one or more $files.
    // $files values may be a single UploadedFile OR an array keyed by ID
    // (e.g. ['cv' => [123 => $file1, 456 => $file2]]) for bulk forms.
    public function postWithFile(string $path, array $data = [], array $files = []): Response
    {
        $request = Http::withToken($this->token)->timeout(60)->asMultipart();

        foreach ($data as $key => $value) {
            if ($value === null) continue;

            if (is_array($value)) {
                // Recursively attach nested arrays using bracket notation
                // e.g. examination_years[] or year_programme[2024][] or year_role[2024][Specialty]
                $request = $this->attachArray($request, $key, $value);
            } else {
                $request = $request->attach($key, (string) $value);
            }
        }

        foreach ($files as $fieldName => $file) {
            if ($file instanceof UploadedFile) {
                $request = $request->attach(
                    $fieldName,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            } elseif (is_array($file)) {
                foreach ($file as $key => $nestedFile) {
                    if ($nestedFile instanceof UploadedFile && $nestedFile->isValid()) {
                        $request = $request->attach(
                            "{$fieldName}[{$key}]",
                            file_get_contents($nestedFile->getRealPath()),
                            $nestedFile->getClientOriginalName()
                        );
                    }
                }
            }
        }

        return $request->post($this->url($path));
    }

    // Like get() but bypasses the internal token — for public API endpoints.
    public function getPublic(string $path, array $query = []): Response
    {
        return Http::timeout(30)->get($this->publicUrl($path), $query);
    }

    // Like post() but bypasses the internal token — for public API endpoints.
    public function postPublic(string $path, array $data = []): Response
    {
        return Http::timeout(30)->post($this->publicUrl($path), $data);
    }

    // Recursively attach a (possibly nested) array as multipart fields.
    // Scalar leaves become individual attach() calls with bracket-notation keys,
    // preserving the exact same structure PHP's $_POST would reconstruct on the
    // receiving end (e.g. year_role[2024][General Surgery] => 'Examiner').
    private function attachArray($request, string $key, array $value)
    {
        foreach ($value as $subKey => $subValue) {
            $fullKey = "{$key}[{$subKey}]";
            if (is_array($subValue)) {
                $request = $this->attachArray($request, $fullKey, $subValue);
            } elseif ($subValue !== null) {
                $request = $request->attach($fullKey, (string) $subValue);
            }
        }
        return $request;
    }

    private function publicUrl(string $path): string
    {
        return $this->baseUrl . '/api/' . ltrim($path, '/');
    }

    private function url(string $path): string
    {
        return $this->baseUrl . '/api/internal/' . ltrim($path, '/');
    }
}
