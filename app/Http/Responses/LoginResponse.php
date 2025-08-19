<?php

namespace App\Http\Responses;
 
use App\Filament\Resources\TimeRecordResource;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
 
class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Here, you can define which resource and which page you want to redirect to
        return redirect()->to(TimeRecordResource::getUrl('index'));
    }
}
